<?php

namespace app\controllers\common;

use yii;
use yii\db\Exception;
use yii\web\Controller;
use app\models\system\SystemSmsAccessModel;
use app\models\core\SMS\SMS;
use app\models\system\SystemAreaModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class BaseController extends Controller {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * 地址:/admin/group/index 默认访问
     * @throws Exception if the model cannot be found
     * @return array
     */

    public function actionAddress() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $url = "https://restapi.amap.com/v3/config/district?key=bc55956766e813d3deb1f95e45e97d73&subdistrict=1";
            if (isset($params['keywords'])) {
                $url .= "&keywords=" . $params['keywords'];
            }
            $array = json_decode(curlGet($url), true);
//
//            for ($i = 0; $i < count($array['districts'][0]['districts']); $i++) {
//                $model = new SystemAreaModel();
//                $data['code'] = $array['districts'][0]['districts'][$i]['adcode'];
//                $data['name'] = $array['districts'][0]['districts'][$i]['name'];
//                $data['parent_id'] = 100000;
//                $data['level'] = 1;
//                $data['status'] = 1;
//                $model->do_add($data);
//                $url = "https://restapi.amap.com/v3/config/district?key=bc55956766e813d3deb1f95e45e97d73&subdistrict=1&keywords=" . $array['districts'][0]['districts'][$i]['adcode'];
//                $city = json_decode(curlGet($url), true);
//                for ($j = 0; $j < count($city['districts'][0]['districts']); $j++) {
//                    $model = new SystemAreaModel();
//                    $data['code'] = $city['districts'][0]['districts'][$j]['adcode'];
//                    $data['name'] = $city['districts'][0]['districts'][$j]['name'];
//                    $data['parent_id'] = $array['districts'][0]['districts'][$i]['adcode'];
//                    $data['level'] = 2;
//                    $data['status'] = 1;
//                    $model->do_add($data);
////                    $url = "https://restapi.amap.com/v3/config/district?key=bc55956766e813d3deb1f95e45e97d73&subdistrict=1&keywords=" . $city['districts'][0]['districts'][$j]['adcode'];
////                    $area = json_decode(curlGet($url), true);
////                    for ($k = 0; $k < count($area['districts'][0]['districts']); $k++) {
////                        $model = new SystemAreaModel();
////                        $data['code'] = $area['districts'][0]['districts'][$k]['adcode'];
////                        $data['name'] = $area['districts'][0]['districts'][$k]['name'];
////                        $data['parent_id'] = $city['districts'][0]['districts'][$j]['adcode'];
////                        $data['level'] = 1;
////                        $data['status'] = 1;
////                        $model->do_add($data);
////                    }
//                }
//            }
//            $model = new SystemAreaModel();
//            $list = $model->do_select(['level' => 2]);
////         
////////            
////            //394
//            for ($i = 350; $i < 394; $i++) {
//                $url = "https://restapi.amap.com/v3/config/district?key=bc55956766e813d3deb1f95e45e97d73&subdistrict=1&keywords=" . $list['data'][$i]['code'];
//                $area = json_decode(curlGet($url), true);
//                for ($k = 0; $k < count($area['districts'][0]['districts']); $k++) {
//                    $model = new SystemAreaModel();
//                    if ($area['districts'][0]['districts'][$k]['level'] == "street") {
//                        
//                    } else {
//                        $data['code'] = $area['districts'][0]['districts'][$k]['adcode'];
//                        $data['name'] = $area['districts'][0]['districts'][$k]['name'];
//                        $data['parent_id'] = $list['data'][$i]['code'];
//                        $data['level'] = 3;
//                        $data['status'] = 1;
//                        $model->do_add($data);
//                    }
//                }
//            }
//
//           
            return result(200, "请求成功", $array);
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSms() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $sms = new SMS();
            if (!isset($params['phone'])) {
                return result(500, '缺少参数 手机号');
            }
            $rs = $sms->sendOne($params['phone']);

            if ($rs['status'] == 200) {
                $data['phone'] = $params['phone'];
                $data['prefix'] = "merchant_reg";
                $data['code'] = $rs['data']['code'];
                $data['content'] = $rs['data']['content'];
                $data['status'] = 0;
                $systemSmsAccessModel = new SystemSmsAccessModel();
                $rs = $systemSmsAccessModel->add($data);
                return $rs;
            } else {
                return result(200, $rs['message']);
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAddr() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new SystemAreaModel();
            $list = $model->do_select(['limit' => false]);

            $data = array();
            for ($i = 0; $i < count($list['data']); $i++) {
                if ($list['data'][$i]['level'] == 1) {
                    $data[] = $list['data'][$i];
                }
            }
            for ($i = 0; $i < count($data); $i++) {
                $data[$i]['city'] = array();
                for ($j = 0; $j < count($list['data']); $j++) {
                    if ($list['data'][$j]['level'] == 2 && $list['data'][$j]['parent_id'] == $data[$i]['code']) {
                        $data[$i]['city'][] = $list['data'][$j];
                    }
                }
            }
            for ($i = 0; $i < count($data); $i++) {
                for ($j = 0; $j < count($data[$i]['city']); $j++) {
                    for ($k = 0; $k < count($list['data']); $k++) {
                        if ($list['data'][$k]['level'] == 3 && $list['data'][$k]['parent_id'] == $data[$i]['city'][$j]['code']) {
                            $data[$i]['city'][$j]['area'][] = $list['data'][$k];
                        }
                    }
                }
            }
            return result(200, "请求成功", $data);
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionExpress() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $must = ['express_number', 'simple_name'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $shopExpress = logistics($params['express_number'], $params['simple_name']);
            return result(200, "请求成功", $shopExpress);
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionQrcode() {

        return Qrcode::png("baidu.com");
    }

}
