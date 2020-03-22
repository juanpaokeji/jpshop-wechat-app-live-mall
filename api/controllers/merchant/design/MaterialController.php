<?php

namespace app\controllers\merchant\design;


use yii;
use yii\web\MerchantController;
use yii\db\Exception;
use app\models\design\MaterialModel;
use app\models\core\Base64Model;
use app\models\core\CosModel;
use app\models\core\UploadsModel;
use yii\redis\Cache;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class MaterialController extends MerchantController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * 地址:/admin/group/index 默认访问
     * @throws Exception if the model cannot be found
     * @return array
     */

    public function behaviors() {
        return [
            'token' => [
                'class' => 'yii\filters\MerchantFilter', //调用过滤器
                // 'only' => ['single'], //指定控制器应用到哪些动作
                'except' => ['all','alls','test'], //指定控制器不应用到哪些动作
            ]
        ];
    }

    public function actionList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $materialModel = new MaterialModel();

            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $materialModel->lists($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAll() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $materialModel = new MaterialModel();
            $array = $materialModel->findall($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAlls() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $materialModel = new MaterialModel();
            $array = $materialModel->alls($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $materialModel = new MaterialModel();
            $params['id'] = $id;

            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $materialModel->find($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $materialModel = new MaterialModel();
            //   $base = new Base64Model();
            //设置类目 参数
            $must = ['name'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return json_encode($rs, JSON_UNESCAPED_UNICODE);
            }
            $upload = new UploadsModel('pic_url', "./uploads/material");
            $str = $upload->upload();
            if (!$str) {
                return "上传文件错误";
            }
//            //将图片上传到cos
//            $cos = new CosModel();
//            $cosRes = $cos->putObject($str);
//            if ($cosRes['status'] == '200') {
//                $url = $cosRes['data'];
//            } else {
//                unlink(Yii::getAlias('@webroot/') . $localRes);
//                return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
//            }
            $url = "https://api2.juanpao.com/" . $str;

            $params['merchant_id'] = yii::$app->session['uid'];
            $params['pic_url'] = $url;
            $array = $materialModel->add($params);
            if ($params['type'] == 4) {
                $where['merchant_id'] = yii::$app->session['uid'];
                $where['type'] = 4;
                $data = $materialModel->findalls($where);
                save_file($data['data']);
            }

            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate($id) {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $materialModel = new MaterialModel();
            $params['id'] = $id;

            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                if ($_FILES) {
                    $upload = new UploadsModel('pic_url', "./uploads/material");
                    $params['pic_url'] = $upload->upload();
                    if (!$params['pic_url']) {
                        return "上传文件错误";
                    }
//                    //将图片上传到cos
//                    $cos = new CosModel();
//                    $cosRes = $cos->putObject($params['pic_url']);
//                    if ($cosRes['status'] == '200') {
//                        $url = $cosRes['data'];
//                    } else {
//                        unlink(Yii::getAlias('@webroot/') . $params['pic_url']);
//                        return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
//                    }
                }
                $params['merchant_id'] = yii::$app->session['uid'];
                $array = $materialModel->update($params);
                if ($params['type'] == 4) {
                    $where['merchant_id'] = yii::$app->session['uid'];
                    $where['type'] = 4;
                    $data = $materialModel->findalls($where);
                    save_file($data['data']);
                }
                return $array;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionDelete($id) {
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $materialModel = new MaterialModel();

            $params['merchant_id'] = yii::$app->session['uid'];
            $params['id'] = $id;
            $array = $materialModel->delete($params);
            if ($params['type'] == 4) {
                $where['merchant_id'] = yii::$app->session['uid'];
                $where['type'] = 4;
                $data = $materialModel->findalls($where);
                save_file($data['data']);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
