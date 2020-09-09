<?php

namespace app\controllers\shop;

use yii;
use yii\web\ShopController;
use yii\db\Exception;
use app\models\merchant\system\ThemeModel;
use app\models\merchant\system\UnitModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class ThemeController extends ShopController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors() {
        return [
            'token' => [
                'class' => 'yii\filters\MerchantFilter', //调用过滤器
//                'only' => ['single'],//指定控制器应用到哪些动作
                'except' => ['single', 'one'], //指定控制器不应用到哪些动作
            ]
        ];
    }

    public function actionSingle() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new ThemeModel();
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $res = $model->find($params);
            if ($res['status'] == 204) {
                $res = yii::$app->params['theme'];
                $res['navigation'] = json_decode(yii::$app->params['theme']['navigation'], true);
                $res['copyright']['bottom_url'] = yii::$app->params['unit']['copyright']['pic_url'];
                $res['copyright']['mini_name'] = '卷泡科技提供技术支持';
                return result(200, "请求成功", $res);
            }
            if ($res['status'] == 200) {
                if ($res['data']['navigation'] != "") {
                    $res['data']['navigation'] = json_decode($res['data']['navigation'], true);
                }
            }

            $unitModel = new UnitModel();
            //   $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['fields'] = " title,route,expire_time,config ";
            $params['route'] = "copyright";
            $array = $unitModel->findall($params);
            if ($array['status'] == 200) {
                $res['data']['copyright'] = json_decode($array['data'][0]['config'], true);
				if($res['data']['copyright']==="https://juanpao999-1255754174.cos.ap-guangzhou.myqcloud.com/ui/%E6%B0%B4%E5%8D%B0.png"){
						$res['data']['copyright'] = array();
						$res['data']['copyright']['bottom_url'] = 'https://juanpao999-1255754174.cos.ap-guangzhou.myqcloud.com/copyright2.png';
						$res['data']['copyright']['mini_name'] = '卷泡科技提供技术支持';
					
				}
				
            } else {
                $res['data']['copyright']['bottom_url'] = yii::$app->params['unit']['copyright']['pic_url'];
				$res['data']['copyright']['mini_name'] = '卷泡科技提供技术支持';
            }
			
            return $res;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionOne() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new ThemeModel();
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $res = $model->find($params);
            if ($res['status'] == 204) {
                $res = yii::$app->params['theme'];
                unset($res['navigation']);
                return result(200, "请求成功", $res);
            }
            if ($res['status'] == 200) {
                unset($res['data']['navigation']);
                unset($res['data']['merchant_id']);
                unset($res['data']['key']);
                unset($res['data']['create_time']);
                unset($res['data']['update_time']);
                unset($res['data']['delete_time']);
                unset($res['data']['status']);
            }
            return $res;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
