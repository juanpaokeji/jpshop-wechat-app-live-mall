<?php
namespace app\controllers\admin\system;

use app\models\merchant\app\AppModel;
use yii;
use yii\web\CommonController;
use app\models\system\SystemPluginModel;
use app\models\core\Base64Model;
use app\models\core\CosModel;

class PluginController extends CommonController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function actionList()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $model = new SystemPluginModel();
            if (isset($params['searchName'])) {
                $data['name'] = $params['searchName'];
                $array = $model->do_select($data);
                unset($data['name']);
            } else {
                $array = $model->do_select($params);
            }
            $appmodel = new AppModel();
            foreach ($array['data'] as $k=>$v){
                $res = $appmodel->find(['id' => $v['app_id']]);
                if ($res['status'] == 200) {
                    $array['data'][$k]['app_name'] = $res['data']['name'];
                }
            }

            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionOne($id)
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $model = new SystemPluginModel();
            $params['id'] = $id;
            $array = $model->do_one($params);
            $appmodel = new AppModel();
            $res = $appmodel->find(['id' => $array['data']['app_id']]);
            if ($res['status'] == 200) {
                $array['data']['app_name'] = $res['data']['name'];
            }

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

            $model = new SystemPluginModel();
            $must = ['name', 'english_name', 'app_id', 'pic_url', 'type', 'status'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $piuginWhere['english_name'] = $params['english_name'];
            $piuginData = $model->do_one($piuginWhere);
            if ($piuginData['status'] == 200){
                return result(500, "该插件已存在");
            }

            $data = [];
            $appmodel = new AppModel();
            $res = $appmodel->findall($data);
            foreach ($res['data'] as $k=>$v){
                $appids[$k] = $v['id'];
            }
            if (!in_array($params['app_id'],$appids)) {
                return result(500, "应用id不存在");
            }
            if (!in_array($params['type'],[1,2,3])) {
                return result(500, "类型不存在");
            }

            if ($params['pic_url'] != "") {
                $base = new Base64Model();
                $params['pic_url'] = $base->base64_image_content($params['pic_url'], "./uploads/admin/plugin");
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

            $array = $model->do_add($params);
            return $array;

        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionDelete($id)
    {
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $model = new SystemPluginModel();
            $params['id'] = $id;
            $array = $model->do_delete($params);
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


            $model = new SystemPluginModel();
            if (isset($params['english_name'])){
                $piuginWhere['<>'] = ['id',$id];
                $piuginWhere['english_name'] = $params['english_name'];
                $piuginData = $model->do_one($piuginWhere);
                if ($piuginData['status'] == 200){
                    return result(500, "该插件已存在");
                }
            }

            $data = [];
            $appmodel = new AppModel();
            $res = $appmodel->findall($data);
            foreach ($res['data'] as $k=>$v){
                $appids[$k] = $v['id'];
            }
            if (isset($params['app_id'])) {
                if (!in_array($params['app_id'],$appids)) {
                    return result(500, "应用id不存在");
                }
            }
            if (isset($params['type'])) {
                if (!in_array($params['type'],[1,2,3])) {
                    return result(500, "类型不存在");
                }
            }

            $where['id'] = $id;

            if (isset($params['pic_url']) && $params['pic_url'] != "") {
                $base = new Base64Model();
                $params['pic_url'] = $base->base64_image_content($params['pic_url'], "./uploads/admin/plugin");
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
            } else {
                unset($params['pic_url']);
            }

            $array = $model->do_update($where, $params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }



}