<?php

namespace app\controllers\shop;

use yii;
use yii\db\Exception;
use yii\web\ShopController;
use app\models\shop\BalanceModel;
use app\models\shop\UserModel;
use app\models\tuan\ConfigModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class BalanceController extends ShopController
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * 地址:/admin/group/index 默认访问
     * @throws Exception if the model cannot be found
     * @return array
     */

    public function actionList()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数          
            $model = new BalanceModel();
            $params['field'] = " money,remain_money,fee,send_type,status,create_time,update_time ";
            $params['uid'] = yii::$app->session['user_id'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['is_send'] = 1;
            $array = $model->do_select($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAll()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数          
            $model = new BalanceModel();
            $params['field'] = " shop_user_balance.order_sn,shop_user_balance.money,shop_user.avatar,shop_user.nickname,shop_user_balance.status,shop_order_group.payment_money as payment_money,shop_user_balance.create_time,shop_user_balance.update_time ";
            $params['uid'] = yii::$app->session['user_id'];
            $params['shop_user_balance.merchant_id'] = yii::$app->session['merchant_id'];
            $params['<>'] = ["shop_user_balance.order_sn", "0"];
            $params['join'][] = ['inner join ', 'shop_order_group', 'shop_order_group.order_sn = shop_user_balance.order_sn'];
            $params['join'][] = ['inner join ', 'shop_user', 'shop_user.id = shop_order_group.user_id'];
            $array = $model->do_select($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionBalance()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $user = new UserModel();
            $u = $user->find(['id' => yii::$app->session['user_id']]);
            if ($params['type'] == 1) {
                $array['data']['balance'] = $u['data']['balance'];
            } else {
                $array['data']['balance'] = $u['data']['withdrawable_commission'];
            }
            $configM = new ConfigModel();
            $config = $configM->do_one(['merchant_id' => yii::$app->session['merchant_id']]);

            if ($config['status'] != 200) {
                return $config;
            }
            $array['data']['withdraw_fee_ratio'] = $config['data']['withdraw_fee_ratio'];
            $array['data']['min_withdraw_money'] = $config['data']['min_withdraw_money'];
            $array['status'] = 200;
            $array['message'] = "请求成功";
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

            $must = ['money'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $user = new UserModel();
            $u = $user->find(['id' => yii::$app->session['user_id']]);
            if ($params['type'] == 1) {
                $content = "团长佣金提现";
                if ((float)$u['data']['balance'] == 0.00) {
                    return result(500, '余额为0');
                }

                if ($u['data']['balance'] < (float)$params['money']) {
                    return result(500, '余额为不足');
                }

            }else{
                $content = "分销佣金提现";
                if ((float)$u['data']['withdrawable_commission'] == 0.00) {
                    return result(500, '余额为0');
                }

                if ($u['data']['withdrawable_commission'] < (float)$params['money']) {
                    return result(500, '余额为不足');
                }
            }


            $configM = new ConfigModel();
            $config = $configM->do_one(['merchant_id' => yii::$app->session['merchant_id']]);
            if ($config['status'] != 200) {
                return $config;
            }
            if ((float)$config['data']['withdraw_fee_ratio'] > (float)$params['money']) {
                return result(500, '提现金额小于最低体现金额');
            }


            $data = array(
                'uid' => yii::$app->session['user_id'],
                'balance_sn' => order_sn(),
                'order_sn' => 0,
                'fee' => (float)$params['money'] * (float)$config['data']['withdraw_fee_ratio'],
                'money' => (float)$params['money'],
                'remain_money' => (float)$params['money'] - ((float)$params['money'] * (float)$config['data']['withdraw_fee_ratio']),
                'content' => $content,
                'send_type' => $params['send_type'],
                'is_send' => 1,
                'type' => 0,
                'status' => 0
            );
            if ($params['send_type'] == 2 || $params['send_type'] == 3) {
                $data['realname'] = $params['realname'];
                $data['pay_number'] = $params['pay_number'];
            }

            $model = new BalanceModel();
            $data['key'] = yii::$app->session['key'];
            $data['merchant_id'] = yii::$app->session['merchant_id'];
            $array = $model->do_add($data);

            if ($array['status'] == 200) {
                if($params['type']==1){
                    $user->update(['id' => yii::$app->session['user_id'], '`key`' => yii::$app->session['key'], 'balance' => (float)$u['data']['balance'] - (float)$params['money']]);
                }else{
                    $user->update(['id' => yii::$app->session['user_id'], '`key`' => yii::$app->session['key'], 'balance' => (float)$u['data']['withdrawable_commission'] - (float)$params['money']]);
                }

            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

//
//    public function actionUpdate($id) {
//        if (yii::$app->request->isPut) {
//            $request = yii::$app->request; //获取 request 对象
//            $params = $request->bodyParams; //获取body传参
//            $model = new BalanceModel();
//            $where['id'] = $id;
//            $where['merchant_id'] = yii::$app->session['uid'];
//            $where['key'] = $params['key'];
//            $array = $model->do_update($where, $params);
//            return $array;
//        } else {
//            return result(500, "请求方式错误");
//        }
//    }
//
//    public function actionDelete($id) {
//        if (yii::$app->request->isDelete) {
//            $request = yii::$app->request; //获取 request 对象
//            $params = $request->bodyParams; //获取body传参
//            $model = new BalanceModel();
//            $params['id'] = $id;
//            $params['merchant_id'] = yii::$app->session['uid'];
//            $array = $model->do_delete($params);
//            return $array;
//        } else {
//            return result(500, "请求方式错误");
//        }
//    }
}
