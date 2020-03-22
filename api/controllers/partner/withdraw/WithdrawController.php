<?php

namespace app\controllers\partner\withdraw;

use app\models\merchant\partnerUser\PartnerUserModel;
use app\models\merchant\partnerUser\WithdrawModel;
use yii;

class WithdrawController extends yii\web\PartnerController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * 申请提现 每次只能有一次申请，拒绝或者同意后可再次申请
     * @return array
     * @throws yii\db\Exception
     */
    public function actionAdd()
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            //检测当前是否有待审核的提现
            $withdrawModel = new WithdrawModel();
            $where['key'] = yii::$app->session['key'];
            $where['merchant_id'] = yii::$app->session['m_id'];
            $where['partner_id'] = yii::$app->session['partner_id'];
            $where['status'] = 0;
            $info = $withdrawModel->one($where);
            if($info['status'] == 200){
                return result(500, "您已申请提现请等待商家审核");
            }
            $must = ['apply_money', 'ids'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
           /* $partnerModel = new PartnerUserModel();
            $partnerInfo = $partnerModel->one(['id' => yii::$app->session['partner_id']]);
            if($partnerInfo['status'] != 200){
                return result(500, "您是谁！");
            }*/
           if(empty($params['ids']) || $params['apply_money'] <= 0){
               return result(500, "金额不能为0");
           }
            $data['apply_money'] = $params['apply_money'];
           // $data['apply_name'] = $partnerInfo['data']['account'];
            $data['partner_id'] = yii::$app->session['partner_id'];
            $data['merchant_id'] = yii::$app->session['m_id'];
            $ids = rtrim($params['ids'],',');
            $data['ids'] = $ids;
            $data['key'] = yii::$app->session['key'];
            $transaction = yii::$app->db->beginTransaction();
            $res =  $withdrawModel->add($data);
            if($res['status'] == 200){
                $sql = "update shop_order_group set is_partner_withdraw = 1 where id in ($ids)";
                $res = Yii::$app->db->createCommand($sql)->execute();
                if($res){
                    $transaction->commit();
                    return result(500, "请求成功");
                };
            }
            $transaction->rollBack();
            return result(500, "请求失败");
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 提现记录
     * @return array
     */
    public function actionList(){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取地址栏参数
            $withdrawModel = new WithdrawModel();
            $list = $withdrawModel->do_select(['key'=>yii::$app->session['key'],'merchant_id'=>yii::$app->session['m_id'],'partner_id'=>yii::$app->session['partner_id']]);
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return $list;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * @return array
     * @throws yii\db\Exception
     */
    public function actionBalance()
    {
        if (yii::$app->request->isGet) {
            //检测当前是否有待审核的提现
            $withdrawModel = new WithdrawModel();
            $where['key'] = yii::$app->session['key'];
            $where['merchant_id'] = yii::$app->session['m_id'];
            $where['partner_id'] = yii::$app->session['partner_id'];
            $data = $withdrawModel->getWithdrawMoney($where['key'], $where['merchant_id'], $where['partner_id']);
            if(!$data){
                return result(500, "缺少参数");
            }
            return result(200, "请求成功",$data);
        } else {
            return result(500, "请求方式错误");
        }
    }
}
