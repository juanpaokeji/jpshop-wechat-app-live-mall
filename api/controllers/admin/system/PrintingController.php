<?php
namespace app\controllers\admin\system;

use Yii;
use yii\base\Exception;
use yii\web\CommonController;
use app\models\system\PrintingModel;
use app\models\system\PrintingKeyModel;
use app\models\admin\app\AppModel;
use app\models\system\PrintingTempModel;
use app\models\core\Base64Model;
use app\models\core\CosModel;

class PrintingController extends CommonController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function actionList()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new PrintingModel();

            if (isset($params['searchName'])) {
                if ($params['searchName'] != "") {
                    $params['name'] = ['like', "{$params['searchName']}"];
                }
                unset($params['searchName']);
            }
            $array = $model->do_select($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionOne($id)
    {
        if (yii::$app->request->isGet) {
//            $request = yii::$app->request; //获取 request 对象
//            $params = $request->get(); //获取地址栏参数
            $model = new PrintingModel();
            $params['id'] = $id;
            $array = $model->do_one($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd()
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $must = ['name', 'english_name'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $model = new PrintingModel();
            if (isset($params['type']) && $params['type'] == 1 || $params['type'] == 2) {
                $data['type'] = $params['type'];
                $res = $model->do_select($data);
                if ($res['status'] == 200) {
                    return result(500, "已存在该类型分组！");
                }
            }

            if (isset($params['sort']) && $params['sort'] == '') {
                $params['sort'] = 0;
            }

            $array = $model->do_add($params);
            return $array;

        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionDelete($id)
    {
        if (yii::$app->request->isDelete) {
//            $request = yii::$app->request; //获取 request 对象
//            $params = $request->bodyParams; //获取body传参

            $model = new PrintingModel();
            $keyModel = new PrintingKeyModel();
            $params['id'] = $id;
            $where['category_id'] = $id;
            $res = $keyModel->do_select(['category_id'=>$id]);

            if ($res['status'] == 200) {
                //开始事务
                $transaction = Yii::$app->db->beginTransaction();
                try{
                    $keyModel->do_delete($where);
                    $array = $model->do_delete($params);
                    $transaction->commit();
                } catch (Exception $e) {
                    $transaction->rollBack(); //回滚
                    return result(500, "删除失败");
                }
            } else {
                $array = $model->do_delete($params);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate($id)
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $model = new PrintingModel();
            if (isset($params['type']) && $params['type'] == 1 || $params['type'] == 2) {
                $data['type'] = $params['type'];
                $res = $model->do_select($data);
                if ($res['status'] == 200) {
                    return result(500, "已存在该类型分组！");
                }
            }
            $where['id'] = $id;
            $array = $model->do_update($where, $params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionKeylist()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            if (isset($params['searchName'])) {
                if ($params['searchName'] != "") {
                    $params['system_express_keyword.name'] = ['like', "{$params['searchName']}"];
                }
                unset($params['searchName']);
            }
            $params['field'] = "system_express_keyword.*,system_express_keyword_category.name as category_name,system_express_keyword_category.type ";
            $params['join'][] = ['inner join', 'system_express_keyword_category', 'system_express_keyword.category_id = system_express_keyword_category.id'];

            $model = new PrintingKeyModel();
            $array = $model->do_select($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionKeyone($id)
    {
        if (yii::$app->request->isGet) {
//            $request = yii::$app->request; //获取 request 对象
//            $params = $request->get(); //获取地址栏参数
            $model = new PrintingKeyModel();
            $params['id'] = $id;
            $array = $model->do_one($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionKeyadd()
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $must = ['name', 'category_id', 'english_name'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            if (isset($params['sort']) && $params['sort'] == '') {
                $params['sort'] = 0;
            }
            if ($params['pic_url'] != "") {
                $base = new Base64Model();
                $params['pic_url'] = $base->base64_image_content($params['pic_url'], "./uploads/admin/print");
                $cos = new CosModel();
                $cosRes = $cos->putObject($params['pic_url']);
                if ($cosRes['status'] == '200') {
                    $url = $cosRes['data'];
                    unlink(Yii::getAlias('@webroot/') . $params['pic_url']);
                } else {
                    unlink(Yii::getAlias('@webroot/') . $params['pic_url']);
                    return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
                }
                $params['pic_url'] = $url;
            }
            $model = new PrintingKeyModel();
            $array = $model->do_add($params);
            return $array;

        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionKeydelete($id)
    {
        if (yii::$app->request->isDelete) {
//            $request = yii::$app->request; //获取 request 对象
//            $params = $request->bodyParams; //获取body传参

            $model = new PrintingKeyModel();
            $params['id'] = $id;
            $array = $model->do_delete($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionKeyupdate($id)
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            if ($params['pic_url'] != "") {
                $base = new Base64Model();
                $params['pic_url'] = $base->base64_image_content($params['pic_url'], "./uploads/admin/print");
                $cos = new CosModel();
                $cosRes = $cos->putObject($params['pic_url']);
                if ($cosRes['status'] == '200') {
                    $url = $cosRes['data'];
                    unlink(Yii::getAlias('@webroot/') . $params['pic_url']);
                } else {
                    unlink(Yii::getAlias('@webroot/') . $params['pic_url']);
                    return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
                }
                $params['pic_url'] = $url;
            }
            $model = new PrintingKeyModel();
            $where['id'] = $id;
            $array = $model->do_update($where, $params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionTemplist()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            if (isset($params['searchName'])) {
                if ($params['searchName'] != "") {
                    $params['name'] = ['like', "{$params['searchName']}"];
                }
                unset($params['searchName']);
            }
            $model = new PrintingTempModel();
            $array = $model->do_select($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionTempone($id)
    {
        if (yii::$app->request->isGet) {
//            $request = yii::$app->request; //获取 request 对象
//            $params = $request->get(); //获取地址栏参数
            $model = new PrintingTempModel();
            $params['id'] = $id;
            $array = $model->do_one($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionTempadd()
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $must = ['name', 'english_name', 'appid', 'keywords_ids', 'info', 'width', 'height'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $data['english_name'] = $params['english_name'];
            $model = new PrintingTempModel();
            $res = $model->do_select($data);
            if ($res['status'] == 200) {
                return result(500, "已有该类型模板");
            } else {
                $array = $model->do_add($params);
                return $array;
            }

        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionTempdelete($id)
    {
        if (yii::$app->request->isDelete) {
//            $request = yii::$app->request; //获取 request 对象
//            $params = $request->bodyParams; //获取body传参
            $model = new PrintingTempModel();
            $params['id'] = $id;
            $array = $model->do_delete($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionTempupdate($id)
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $model = new PrintingTempModel();
            $conut = count($params);
            $where['id'] = $id;
            if ($conut == 1 && isset($params['status'])) {
                $array = $model->do_update($where, $params);
            } else {
                $data['english_name'] = $params['english_name'];
                $data['<>'] = ['id',$id];
                $res = $model->do_select($data);
                if ($res['status'] == 200) {
                    return result(500, "已有该类型模板");
                } else {
                    $array = $model->do_update($where, $params);
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    //模板添加分组、字段信息
    public function actionPulldownlist()
    {
        if (yii::$app->request->isGet) {
            $model = new PrintingModel();
            $keyModel = new PrintingKeyModel();
            $data['field'] = "system_express_keyword_category.type,system_express_keyword_category.id,system_express_keyword_category.name,system_express_keyword_category.english_name";
            $data['status'] = 1;
            $res = $model->do_select($data);

            
            $params['field'] = "system_express_keyword.id,system_express_keyword.name,system_express_keyword.english_name,system_express_keyword.category_id,system_express_keyword.pic_url";
            $params['status'] = 1;
            $params['limit'] = 1000; //CommonModel limit有默认值
            $params['page'] = 1;
            $array = $keyModel->do_select($params);

            $appModel = new AppModel();
            $appInfo = $appModel->findall([]);

            foreach ($res['data'] as $key=>$val) {
                foreach ($array['data'] as $k=>$v) {
                    if ($val['id'] == $v['category_id']) {
                        $res['data'][$key]['subclass'][] = $v;
                    }
                }
            }
            $res['app_info'] = $appInfo['data'];

            return $res;
        } else {
            return result(500, "请求方式错误");
        }
    }
}