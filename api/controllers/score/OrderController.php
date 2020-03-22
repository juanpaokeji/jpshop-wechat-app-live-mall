<?php

namespace app\controllers\score;

use yii;
use yii\web\ShopController;
use yii\db\Exception;
use app\models\score\ScoreGoodsOrderModel;

class OrderController extends ShopController {

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
            $model = new ScoreGoodsOrderModel();
            $params['shop_score_order.merchant_id'] = yii::$app->session['merchant_id'];
            $params['shop_score_order.key'] = yii::$app->session['key'];
            $params['shop_score_order.user_id'] = yii::$app->session['user_id'];
            if (isset($params['searchName'])) {
                if ($params['searchName'] != "") {
                    $params['name'] = ['like', "{$params['searchName']}"];
                }
                unset($params['searchName']);
            }
            if (isset($params['status'])) {
                if ($params['status'] != "") {
                    $params['shop_score_order.status'] = $params['status'];
                }
                unset($params['status']);
            }
            $params['field'] = "shop_score_order.*,system_express.name as express_name,phone,simple_name ";
            $params['join'][] = ['left join', 'system_express', 'system_express.id = shop_score_order.express_id'];
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
            $model = new ScoreGoodsOrderModel();
            unset($params['id']);
            $params['order_sn'] = $id;
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['key'] = yii::$app->session['key'];
            $params['user_id'] = yii::$app->session['user_id'];
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

            $data['merchant_id'] = yii::$app->session['merchant_id'];
            $must = ['score_goods_id', 'user_contact_id'];

            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['number'] = 1;
            $scoreGoodsModel = new \app\models\score\ScoreGoodsModel();
            $goods = $scoreGoodsModel->do_one(['id' => $params['score_goods_id']]);
            if ($goods['status'] != 200) {
                return $goods;
            }

            $userModel = new \app\models\shop\UserModel();
            $user = $userModel->find(['id' => yii::$app->session['user_id'], '`key`' => yii::$app->session['key'], 'merchant_id' => yii::$app->session['merchant_id']]);

            if ($user['status'] != 200) {
                return $user;
            }
            if ($goods['data']['stocks'] < $params['number']) {
                return result(500, '库存不足');
            }
            if ($user['data']['score'] < $goods['data']['score'] * (int) $params['number']) {
                return result(500, '积分不足');
            }
            //收货地址
            $contactModel = new \app\models\shop\ContactModel();
            if (!isset($params['user_contact_id'])) {
                return result(500, '请填写收货地址');
            }
            $contactParams['id'] = $params['user_contact_id'];
            $contactParams['user_id'] = yii::$app->session['user_id'];
            $contactData = $contactModel->find($contactParams);
            if ($contactData['status'] != 200) {
                return result(500, '未找到该收货地址');
            }
            $user_contact_id = $contactData['data']['id'];
            $order_sn = order_sn();
            $pic_url = explode(",", $goods['data']['pic_urls']);
            $data = array(
                'merchant_id' => yii::$app->session['merchant_id'],
                'key' => yii::$app->session['key'],
                'user_id' => yii::$app->session['user_id'],
                'name' => $goods['data']['name'],
                'order_sn' => $order_sn,
                'pic_url' => $pic_url[0],
                'score_goods_id' => $params['score_goods_id'],
                'number' => 1,
                'user_contact_id' => $user_contact_id,
                'score' => $goods['data']['score'] * (int) $params['number'],
                'status' => 0,
            );
            $model = new ScoreGoodsOrderModel();
            $tr = Yii::$app->db->beginTransaction();
            try {
                $array = $model->do_add($data);
                $scoreGoodsModel->do_update(['id' => $params['score_goods_id']], ['stocks' => $goods['data']['stocks'] - $params['number']]);
                $userModel->update(['id' => yii::$app->session['user_id'], '`key`'=>yii::$app->session['user_id'],'score' => $user['data']['score'] - ($goods['data']['score'] * (int) $params['number'])]);
                $tr->commit();
                return result(200, "请求成功");
            } catch (\Exception $e) {
                $tr->rollBack();
                return result(500, "请求异常");
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
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
