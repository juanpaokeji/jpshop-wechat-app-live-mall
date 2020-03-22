<?php

namespace app\controllers\common;

use yii;
use yii\db\Exception;
use yii\web\MerchantController;
use app\models\system\SystemSmsAccessModel;
use app\models\core\SMS\SMS;
use app\models\system\SystemAreaModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class SmsController extends MerchantController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function actionSms() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $sms = new SMS();
            if (!isset($params['phone'])) {
                return result(500, '缺少参数 手机号');
            }
            $rs = $sms->sendOne($params['phone']);
            $comboAccessModel = new \app\models\merchant\system\MerchantComboAccessModel();
            $comboAccessData = $comboAccessModel->do_one(['sms_remain_number' => ['<>', [0]],  'validity_time' => ['<', time()],'merchant_id' => yii::$app->session['merchant_id']]);

            if ($comboAccessData['data']['sms_count'] < 0) {
                return result(500, "商户短信套餐余额不足");
            }
            if ($rs['status'] == 200) {
                $data['phone'] = $params['phone'];
                $data['prefix'] = "merchant_reg";
                $data['code'] = $rs['data']['code'];
                $data['content'] = $rs['data']['content'];
                $data['status'] = 0;
                $systemSmsAccessModel = new SystemSmsAccessModel();
                $rs = $systemSmsAccessModel->add($data);
                $comboAccessModel->update(['sms_remain_number' => $comboAccessData['data']['sms_remain_number'] - 1, 'id' => $comboAccessData['data']['id']]);
                return $rs;
            } else {
                return result(200, $rs['message']);
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

}
