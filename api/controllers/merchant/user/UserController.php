<?php

namespace app\controllers\merchant\user;

use app\models\merchant\distribution\DistributionAccessModel;
use app\models\shop\BalanceModel;
use yii;
use yii\web\MerchantController;
use yii\db\Exception;
use app\models\merchant\forum\UserModel;
use app\models\core\SMS\SMS;
use app\models\admin\user\MerchantModel;
use app\models\system\SystemSmsAccessModel;
use app\models\wolive\ServiceModel;

class UserController extends MerchantController
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors()
    {
        return [
            'token' => [
                'class' => 'yii\filters\MerchantFilter', //调用过滤器
//                'only' => ['single'],//指定控制器应用到哪些动作
                'except' => ['sms', 'register', 'password', 'all'], //指定控制器不应用到哪些动作
            ]
        ];
    }

    /**
     * 删除
     * @param $id
     * @return array
     * @throws Exception
     */
    public function actionDelete($id)
    {
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取地址栏参数
            $model = new UserModel();
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['uid'];
            $params['id'] = $id;
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $array = $model->delete($params);
                return $array;
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 更新
     * @param $id
     * @return array|string
     * @throws Exception
     */
    public function actionUpdate($id)
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取地址栏参数
            $model = new UserModel();
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['uid'];
            $params['id'] = $id;
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $array = $model->update($params);
                return $array;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 查询所有（可扩展条件查询）
     * @return array
     */
    public function actionList()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $table = new UserModel();
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['uid'];
            $params['is_admin !=9'] = null;
            $array = $table->findall($params);
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
            $table = new UserModel();
            $params['fields'] = " id,phone ";
            $array = $table->finds($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 查询单条
     * @param $id
     * @return array|string
     */
    public function actionSingle($id)
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $table = new UserModel();
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['uid'];
            $params['id'] = $id;
            $array = $table->find($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSms()
    {
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

    public function actionRegister()
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取地址栏参数
            $model = new SystemSmsAccessModel();
            $data['phone'] = $params['phone'];
            $rs = $model->find($data['phone']);

            if ($rs['status'] != 200) {
                return result(500, "未查询到验证码!");
            }
            if ($rs['data']['code'] != $params['vercode']) {
                return result(500, "验证码不正确!");
            }
            $params['salt'] = $this->get_randomstr(32);
            $data = [
                'password' => md5($params['password'] . $params['salt']),
                'salt' => $params['salt'],
                'phone' => $params['phone'],
                'status' => 1,
                'create_time' => time(),
            ];
            $merchantModel = new MerchantModel();
            $array = $merchantModel->add($data);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionPassword()
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取地址栏参数
            $model = new SystemSmsAccessModel();
            $data['phone'] = $params['phone'];
            $rs = $model->find($data['phone']);
            if ($rs['status'] != 200) {
                return result(200, "未查询到验证码!");
            }
            if ($rs['data']['code'] != $params['vercode']) {
                return result(200, "验证码不正确!");
            }
            $params['salt'] = $this->get_randomstr(32);
            $data = [
                'password' => md5($params['password'] . $params['salt']),
                'salt' => $params['salt'],
                'phone' => $params['phone']
            ];
            $merchantModel = new MerchantModel();
            $array = $merchantModel->update($data);
            if ($array['status'] != 200) {
                return result(200, $array['message']);
            }
            $serviceModel = new ServiceModel();
            $sdata = array(
                'password' => md5($params['password'] . $params['salt']),
                'salt' => $params['salt'],
                'phone' => $params['phone']
            );
            $serviceModel->update($sdata);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 更改手机号
     * @param $id
     * @return array
     * @throws Exception
     */
    public function actionBindPhone()
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取地址栏参数
            $model = new SystemSmsAccessModel();
            $data['phone'] = $params['phone'];
            $rs = $model->find($data['phone']);

            if ($rs['status'] != 200) {
                return result(500, "未查询到验证码!");
            }
            if ($rs['data']['code'] != $params['code']) {
                return result(500, "验证码不正确!");
            }
            $data = [
                'phone' => $params['phone'],
                'id' => yii::$app->session['uid'],
            ];
            $merchantModel = new MerchantModel();
            $array = $merchantModel->updatePhone($data);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 校验登录账号是否是手机号
     * @return array
     */
    public function actionCheckPhone()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            if (!isset($params['phone']) || empty($params['phone'])) {
                return result(500, "缺少参数");
            }
            $check = '/^(1(([23456789][0-9])|(47)))\d{8}$/';
            if (!preg_match($check, $params['phone'])) {
                return result(500, "不是手机号");
            }
            return result(200, "请求成功");
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionDistributionUser()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $userModel = new \app\models\shop\UserModel();
            if (!isset($params['level'])) {
                $params['level'] = 1;
            }
            if (isset($params['key'])) {
                $params['`key`'] = $params['key'];
                unset($params['key']);
            }
            $array = $userModel->findall($params);
            if ($array['status'] == 200) {
                //总佣金
                $model = new DistributionAccessModel();
                $where['field'] = 'shop_distribution_access.uid,sum(shop_distribution_access.money) as total_money';
                $where['shop_distribution_access.key'] = $params['`key`'];
                $where['join'][] = ['inner join', 'shop_user', 'shop_user.id = shop_distribution_access.uid'];
                $where['groupBy'] = 'uid';
                $totalInfo = $model->do_select($where);
                //已提现佣金
                $balanceModel = new BalanceModel();
                $balanceWhere['field'] = 'shop_user_balance.uid,sum(shop_user_balance.money) as cash_out';
                $balanceWhere['shop_user_balance.type'] = 0;
                $balanceWhere['shop_user_balance.order_sn'] = 0;
                $balanceWhere['shop_user_balance.content'] = '分销佣金提现';
                $balanceWhere['join'][] = ['inner join', 'shop_user', 'shop_user.id = shop_user_balance.uid'];
                $cashOutInfo = $balanceModel->do_select($balanceWhere);
                for ($i = 0; $i < count($array['data']); $i++) {
                    $array['data'][$i]['reg_time'] = $array['data'][$i]['reg_time'] == 0 ? "" : date('Y-m-d H:i:s', $array['data'][$i]['reg_time']);
                    $array['data'][$i]['check_time'] = $array['data'][$i]['check_time'] == 0 ? "" : date('Y-m-d H:i:s', $array['data'][$i]['check_time']);
                    $array['data'][$i]['total_money'] = 0;
                    $array['data'][$i]['cash_out'] = 0;
                    if ($totalInfo['status'] == 200){
                        foreach ($totalInfo['data'] as $k=>$v){
                            if ($v['uid'] == $array['data'][$i]['id']){
                                $array['data'][$i]['total_money'] = $v['total_money'];
                            }
                        }
                    }
                    if ($cashOutInfo['status'] == 200){
                        foreach ($cashOutInfo['data'] as $k=>$v){
                            if ($v['uid'] == $array['data'][$i]['id']){
                                $array['data'][$i]['cash_out'] = $v['cash_out'];
                            }
                        }
                    }
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSubordinate($id){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $must = ['key'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $userModel = new \app\models\shop\UserModel();
            if (isset($params['key'])) {
                $params['`key`'] = $params['key'];
                unset($params['key']);
            }
            $params['parent_id'] = $id;
            unset($params['id']);
            $array = $userModel->findall($params);

            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
