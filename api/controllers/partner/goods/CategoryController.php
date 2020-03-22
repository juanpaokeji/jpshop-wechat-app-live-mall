<?php

namespace app\controllers\partner\goods;

use yii;
use yii\db\Exception;
use app\models\shop\MerchantCategoryModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class CategoryController extends yii\web\PartnerController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置
    /**
     * 商户商城商户商品分类
     */
    public function actionMerchantType() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new MerchantCategoryModel();
            $data['fields'] = " id,name,parent_id ";
            $data['parent_id'] = 0;
            $data['`key`'] = yii::$app->session['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['m_id'];
            $array = $model->finds($data);
            if ($array['status'] != 200) {
                return result(204, "查询失败");
            }
            unset($data['parent_id']);
            $data['parent_id !=0'] = null;
            $list = $model->finds($data);
            if ($list['status'] != 200) {
                return result(204, "查询失败");
            }
            for ($i = 0; $i < count($array['data']); $i++) {
                for ($j = 0; $j < count($list['data']); $j++) {
                    if ($array['data'][$i]['id'] == $list['data'][$j]['parent_id']) {
                        $array['data'][$i]['sub'][] = $list['data'][$j];
                    }
                }
            }
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
