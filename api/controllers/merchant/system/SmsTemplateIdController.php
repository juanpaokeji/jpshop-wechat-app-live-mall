<?php

namespace app\controllers\merchant\system;

use app\models\system\SystemSmsTemplateIdModel;
use yii;
use yii\web\MerchantController;

class SmsTemplateIdController extends MerchantController
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * 新增banner
     * @return array
     */
    public function actionAdd()
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $must = ['config'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $model = new SystemSmsTemplateIdModel();
            $info = $model->do_one([]);
            $data['config'] = json_encode($params['config'], JSON_UNESCAPED_UNICODE);
            if ($info['status'] == 200){
                $where['id'] = $info['data']['id'];
                $array = $model->do_update($where,$data);
            }else{
                $array = $model->do_add($data);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }


    /**
     * 查询单条数据
     * @param $id
     * @return array
     */
    public function actionList()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $model = new SystemSmsTemplateIdModel();
            $array = $model->do_one([]);
            if ($array['status'] == 200){
                $array['data']['config'] = json_decode($array['data']['config'],true);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }


}
