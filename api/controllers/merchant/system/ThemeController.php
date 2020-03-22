<?php

namespace app\controllers\merchant\system;

use app\models\merchant\system\OperationRecordModel;
use yii;
use yii\web\MerchantController;
use yii\db\Exception;
use app\models\merchant\system\ThemeModel;
use app\models\core\Base64Model;
use app\models\core\CosModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class ThemeController extends MerchantController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

//    public function behaviors() {
//        return [
//            'token' => [
//                'class' => 'yii\filters\MerchantFilter', //调用过滤器
////                'only' => ['single'],//指定控制器应用到哪些动作
//                'except' => ['notify'], //指定控制器不应用到哪些动作
//            ]
//        ];
//    }

    public function actionSingle() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new ThemeModel();
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['uid'];
            if (!isset($params['type'])) {
                return result(500, "缺少参数 type ");
            }
            $res = $model->find($params);
            if ($res['status'] == 204) {
                $res = yii::$app->params['theme'];
                $res['merchant_id'] = yii::$app->session['uid'];
                $res['`key`'] = $params['`key`'];
                $model->add($res);
                return result(200, "请求成功", $res);
            }
            if ($res['status'] == 200) {
                if ($res['data']['navigation'] != "") {
                    $res['data']['navigation'] = json_decode($res['data']['navigation'], true);
                }
            }
            return $res;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionLink() {
        return result(200, "请求成功", yii::$app->params['program_link']);
    }

    public function actionCopyright() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $appModel = new \app\models\admin\app\AppAccessModel();
            $app = $appModel->find(['merchant_id' => yii::$app->session['uid'], '`key`' => $params['key']]);
            return $app;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate() {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new ThemeModel();
            if(isset($params['id'])){
            	unset($params['id']);
            }
            if(isset($params['create_time'])){
            	unset($params['create_time']);
            }
            if(isset($params['update_time'])){
            	unset($params['update_time']);
            }
            if(isset($params['delete_time'])){
            	unset($params['delete_time']);
            }
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['uid'];
            if (!isset($params['type'])) {
                return result(500, "缺少参数 type ");
            }
            
            for ($i = 0; $i < count($params['navigation']); $i++) {
            	
                $params['navigation'][$i] = json_decode($params['navigation'][$i], true);
                if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $params['navigation'][$i]['filePut'], $result)) {
                    $base64 = new Base64Model();
                    $path = creat_mulu('./uploads/system/theme/' . $params['merchant_id']);
                    $localRes = $base64->base64_image_content($params['navigation'][$i]['filePut'], $path);
                    $cos = new CosModel();
                    $cosRes = $cos->putObject($localRes);
                    if ($cosRes['status'] == '200') {
                        $url = $cosRes['data'];
                        unlink(Yii::getAlias('@webroot/') . $localRes);
                    } else {
                        $url ="http://".$_SERVER['HTTP_HOST']."/api/web/".$localRes;
                    }
                    $params['navigation'][$i]['filePut'] = $url;
                }
                if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $params['navigation'][$i]['filePutSelection'], $result)) {
                    $base64 = new Base64Model();
                    $path = creat_mulu('./uploads/system/theme/' . $params['merchant_id']);
                    $localRes = $base64->base64_image_content($params['navigation'][$i]['filePutSelection'], $path);
                    $cos = new CosModel();
                    $cosRes = $cos->putObject($localRes);
                    if ($cosRes['status'] == '200') {
                        $url = $cosRes['data'];
                        unlink(Yii::getAlias('@webroot/') . $localRes);
                    } else {
                        $url ="http://".$_SERVER['HTTP_HOST']."/api/web/".$localRes;
                    }
                    $params['navigation'][$i]['filePutSelection'] = $url;
                }
            }
            $params['navigation'] = json_encode($params['navigation'], JSON_UNESCAPED_UNICODE);
            $array = $model->update($params);
            if ($array['status'] == 200){
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['`key`'];
                $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                $operationRecordData['operation_type'] = '更新';
                $operationRecordData['operation_id'] = $params['`key`'];
                $operationRecordData['module_name'] = '主题配色';
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
            $model = new ThemeModel();
            $params['id'] = $id;
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['uid'];
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $array = $model->delete($params);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
