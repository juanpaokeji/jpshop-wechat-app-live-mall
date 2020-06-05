<?php

namespace app\controllers\merchant\system;

use app\models\admin\app\AppAccessModel;
use yii;
use yii\web\MerchantController;

class ThumbnailController extends MerchantController
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

            $must = ['key','thum_is_open'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $model = new AppAccessModel();
            $where['`key`'] = $params['key'];
            $appInfo = $model->find($where);
            if ($appInfo['status'] == 200){
                $data['id'] = $appInfo['data']['id'];
                $data['thum_is_open'] = $params['thum_is_open'];
                $data['thum_width'] = $params['thum_width'] ?? 0;
                $array = $model->update($data);
                return $array;
            }else{
                return result(204, "未查询到该应用信息");
            }

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

            $must = ['key'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $model = new AppAccessModel();
            $where['`key`'] = $params['key'];
            $array = $model->find($where);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }


}
