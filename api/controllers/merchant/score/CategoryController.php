<?php

namespace app\controllers\merchant\score;

use app\models\merchant\system\OperationRecordModel;
use app\models\merchant\user\MerchantModel;
use yii;
use yii\web\MerchantController;
use yii\db\Exception;
use app\models\score\ScoreGoodsCategoryModel;
use app\models\core\Base64Model;
use app\models\core\CosModel;

class CategoryController extends MerchantController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

//    public function behaviors() {
//        return [
//            'token' => [
//                'class' => 'yii\filters\MerchantFilter', //调用过滤器
////                'only' => ['single'],//指定控制器应用到哪些动作
//                'except' => ['sms', 'register', 'password', 'all'], //指定控制器不应用到哪些动作
//            ]
//        ];
//    }

    public function actionList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数          
            $model = new ScoreGoodsCategoryModel();
            $params['merchant_id'] = yii::$app->session['uid'];
            if (isset($params['searchName'])) {
                if ($params['searchName'] != "") {
                    $params['name'] = ['like', "{$params['searchName']}"];
                }
                unset($params['searchName']);
            }
            $array = $model->do_select($params);
            if ($array['status'] != 200) {
                return $array;
            }
            $data = array();
            for ($i = 0; $i < count($array['data']); $i++) {
                if ($array['data'][$i]['parent_id'] == 0) {
                    $data[] = $array['data'][$i];
                }
            }

            for ($i = 0; $i < count($data); $i++) {
                $data[$i]['data'] = array();
                for ($j = 0; $j < count($array['data']); $j++) {
                    if ($data[$i]['id'] == $array['data'][$j]['parent_id']) {
                        $data[$i]['data'][] = $array['data'][$j];
                    }
                }
            }
            return ['status' => 200, 'message' => '请求成功', 'data' => $data, 'count' => count($data)];
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAll() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数          
            $model = new ScoreGoodsCategoryModel();
            $params['merchant_id'] = yii::$app->session['uid'];
            if (isset($params['parent_id'])) {
                if ($params['parent_id'] == -1) {
                    $params['<>'] = ['parent_id', 0];
                    unset($params['parent_id']);
                }
            }
            $array = $model->do_select($params);

            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new ScoreGoodsCategoryModel();
            $params['id'] = $id;
            $array = $model->do_one($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new ScoreGoodsCategoryModel();
            $params['merchant_id'] = yii::$app->session['uid'];
//            if ($params['pic_url'] != "") {
//                $base = new Base64Model();
//                $params['pic_url'] = $base->base64_image_content($params['pic_url'], "./uploads/merchant/score/category");
//                $cos = new CosModel();
//                $cosRes = $cos->putObject($params['pic_url']);
//                if ($cosRes['status'] == '200') {
//                    $url = $cosRes['data'];
//                } else {
//                    unlink(Yii::getAlias('@webroot/') . $params['pic_url']);
//                    return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
//                }
//                $params['pic_url'] = $url;
//            }
//            if ($params['img_url'] != "") {
//                $params['img_url'] = $base->base64_image_content($params['img_url'], "./uploads/merchant/score/category");
//                $cos = new CosModel();
//                $cosRes = $cos->putObject($params['img_url']);
//                if ($cosRes['status'] == '200') {
//                    $url = $cosRes['data'];
//                } else {
//                    unlink(Yii::getAlias('@webroot/') . $params['img_url']);
//                    return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
//                }
//                $params['img_url'] = $url;
//            }
            $array = $model->do_add($params);
            if ($array['status'] == 200){
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['key'];
                if (isset(yii::$app->session['sid'])) {
                    $subModel = new \app\models\merchant\system\UserModel();
                    $subInfo = $subModel->find(['id'=>yii::$app->session['sid']]);
                    if ($subInfo['status'] == 200){
                        $operationRecordData['merchant_id'] = $subInfo['data']['username'];
                    }
                } else {
                    $merchantModle = new MerchantModel();
                    $merchantInfo = $merchantModle->find(['id'=>yii::$app->session['uid']]);
                    if ($merchantInfo['status'] == 200) {
                        $operationRecordData['merchant_id'] = $merchantInfo['data']['name'];
                    }
                }
                $operationRecordData['operation_type'] = '新增';
                $operationRecordData['operation_id'] = $array['data'];
                $operationRecordData['module_name'] = '积分商品分组';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new ScoreGoodsCategoryModel();
            $where['id'] = $id;
            $where['merchant_id'] = yii::$app->session['uid'];
            $where['key'] = $params['key'];
//            if ($params['pic_url'] != "") {
//                $base = new Base64Model();
//                $params['pic_url'] = $base->base64_image_content($params['pic_url'], "./uploads/merchant/shop/category");
//                $cos = new CosModel();
//                $cosRes = $cos->putObject($params['pic_url']);
//                if ($cosRes['status'] == '200') {
//                    $url = $cosRes['data'];
//                } else {
//                    unlink(Yii::getAlias('@webroot/') . $params['pic_url']);
//                    return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
//                }
//                $params['pic_url'] = $url;
//            } else {
//                unset($params['pic_url']);
//            }
//            if ($params['img_url'] != "") {
//                $params['img_url'] = $base->base64_image_content($params['img_url'], "./uploads/merchant/shop/category");
//                $cos = new CosModel();
//                $cosRes = $cos->putObject($params['img_url']);
//                if ($cosRes['status'] == '200') {
//                    $url = $cosRes['data'];
//                } else {
//                    unlink(Yii::getAlias('@webroot/') . $params['img_url']);
//                    return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
//                }
//                $params['img_url'] = $url;
//            } else {
//                unset($params['img_url']);
//            }

            $array = $model->do_update($where, $params);
            if ($array['status'] == 200){
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['key'];
                if (isset(yii::$app->session['sid'])) {
                    $subModel = new \app\models\merchant\system\UserModel();
                    $subInfo = $subModel->find(['id'=>yii::$app->session['sid']]);
                    if ($subInfo['status'] == 200){
                        $operationRecordData['merchant_id'] = $subInfo['data']['username'];
                    }
                } else {
                    $merchantModle = new MerchantModel();
                    $merchantInfo = $merchantModle->find(['id'=>yii::$app->session['uid']]);
                    if ($merchantInfo['status'] == 200) {
                        $operationRecordData['merchant_id'] = $merchantInfo['data']['name'];
                    }
                }
                $operationRecordData['operation_type'] = '更新';
                $operationRecordData['operation_id'] = $id;
                $operationRecordData['module_name'] = '积分商品分组';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionDelete($id) {
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new ScoreGoodsCategoryModel();
            $params['id'] = $id;
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->do_delete($params);
            if ($array['status'] == 200){
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['key'];
                if (isset(yii::$app->session['sid'])) {
                    $subModel = new \app\models\merchant\system\UserModel();
                    $subInfo = $subModel->find(['id'=>yii::$app->session['sid']]);
                    if ($subInfo['status'] == 200){
                        $operationRecordData['merchant_id'] = $subInfo['data']['username'];
                    }
                } else {
                    $merchantModle = new MerchantModel();
                    $merchantInfo = $merchantModle->find(['id'=>yii::$app->session['uid']]);
                    if ($merchantInfo['status'] == 200) {
                        $operationRecordData['merchant_id'] = $merchantInfo['data']['name'];
                    }
                }
                $operationRecordData['operation_type'] = '删除';
                $operationRecordData['operation_id'] = $id;
                $operationRecordData['module_name'] = '积分商品分组';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
