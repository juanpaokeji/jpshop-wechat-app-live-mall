<?php

namespace app\controllers\task;

use app\models\merchant\app\AppAccessModel;
use app\models\merchant\distribution\AgentModel;
use app\models\merchant\distribution\DistributionAccessModel;
use app\models\merchant\distribution\OperatorModel;
use app\models\merchant\distribution\SuperModel;
use app\models\merchant\vip\UnpaidVipModel;
use app\models\shop\AdvanceOrderModel;
use app\models\shop\BalanceModel;
use app\models\shop\GroupOrderModel;
use app\models\shop\OrderModel;
use app\models\shop\ScoreModel;
use app\models\shop\ShopAssembleAccessModel;
use app\models\shop\SubOrderModel;
use app\models\shop\SubOrdersModel;
use app\models\shop\UserModel;
use app\models\system\SystemMerchantMiniSubscribeTemplateAccessModel;
use app\models\system\SystemMerchantMiniSubscribeTemplateModel;
use yii;
use yii\db\Exception;
use yii\web\Controller;
use EasyWeChat\Factory;
use app\models\core\TableModel;
use app\models\system\SystemMerchantMiniAccessModel;
use app\models\system\SystemFormModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class RedisController extends Controller
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置


    //订单付款
    public function actionOrder()
    {
        $paramsLen = llenRedis('leaderMoney');
        if ($paramsLen > 0) {
            for ($i = 0; $i < $paramsLen; $i++) {
                $paramsList[] = rpopRedis('leaderMoney');
            }
            $groupOrder = new GroupOrderModel();
            $subOrderModel = new SubOrdersModel();
            for ($i = 0; $i < count($paramsList); $i++) {

                $group = $groupOrder->one(['order_sn' => $paramsList[$i]]);

                $sub = $subOrderModel->do_select(['order_group_sn' => $paramsList[$i]]);

                if ($group['status'] != 200) {
                    file_put_contents(Yii::getAlias('@webroot/') . '/ylyPrint.text', date('Y-m-d H:i:s') . "正在打印_" . $paramsList[$i] . PHP_EOL, FILE_APPEND);
                    continue;
                }
                if ($group['data']['express_type'] == 2) {
                    $balanceModel = new BalanceModel();
                    $data = array(
                        'key' => $group['data']['key'],
                        'merchant_id' => $group['data']['merchant_id'],
                        'user_id' => $group['data']['leader_uid'],
                        'order_sn' => $group['data']['order_sn'],
                        'money' => $group['data']['express_price'],
                        'type' => 1,
                        'status' => 0,
                        'content' => '配送费佣金',
                    );
                    $balanceModel->do_add($data);
                }

                for ($k = 0; $k < count($sub['data']); $k++) {
                    $balanceModel = new BalanceModel();
                    $data = array(
                        'key' => $sub['data'][$k]['key'],
                        'merchant_id' => $sub['data'][$k]['merchant_id'],
                        'user_id' => $group['data']['leader_uid'],
                        'order_sn' => $sub['data'][$k]['order_group_sn'],
                        'balance_sn' => $sub['data'][$k]['id'],
                        'money' => $sub['data'][$k]['leader_money'],
                        'type' => 1,
                        'status' => 0,
                        'content' => '团员消费',
                    );
                    $balanceModel->do_add($data);
                }

            }


        }
    }

}
