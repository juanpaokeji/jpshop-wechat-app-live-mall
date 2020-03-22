<?php

namespace app\controllers\shop;

use yii;
use yii\db\Exception;
use yii\web\ShopController;
use app\models\system\SystemDiyConfigModel;
use app\models\merchant\system\MerchantDiyConfigModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class DiyController extends ShopController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * 地址:/admin/group/index 默认访问
     * @throws Exception if the model cannot be found
     * @return array
     */

    public function actionSingle() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数       
            $must = ['key'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $model = new SystemDiyConfigModel();
            $array = $model->do_one($params);
            if ($array['status'] == 200) {
                $merchantModel = new MerchantDiyConfigModel();
                $res = $merchantModel->do_one(['system_diy_config_id' => $array['data']['id'], 'merchant_id' => yii::$app->session['merchant_id'], 'key' => yii::$app->session['key']]);
                if ($res['status'] == 200) {
                    return result(200, '请求成功', $res['data']['value']);
                } else {
                    return result(200, '请求成功', $array['data']['value']);
                }
            }else{
                return $array;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

}
