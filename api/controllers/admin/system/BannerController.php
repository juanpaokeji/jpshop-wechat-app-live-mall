<?php

namespace app\controllers\admin\system;

use yii\web\CommonController;
use app\models\system\SystemBannerModel;
use yii;

class BannerController extends CommonController
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
            $model = new SystemBannerModel();
            $data['app_id'] = isset($params['app_id']) ? $params['app_id'] : '';
            $data['name'] = isset($params['name']) ? $params['name'] : '';
            $data['purpose'] = isset($params['purpose']) ? $params['purpose'] : '';
            $data['pic_url'] = isset($params['pic_url']) ? $params['pic_url'] : '';
            $data['jump_url'] = isset($params['jump_url']) ? $params['jump_url'] : '';
            $data['type'] = isset($params['type']) ? $params['type'] : 1;
            $data['status'] = isset($params['status']) ? $params['status'] : 1;
            $array = $model->do_add($data);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 查询列表banner
     * @return array
     */
    public function actionList()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new SystemBannerModel();
            $array = $model->do_select($params);
            return result(200, "请求成功", $array);
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 查询单条数据
     * @param $id
     * @return array
     */
    public function actionOne($id)
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new SystemBannerModel();
            $params['id'] = $id;
            $array = $model->do_one($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 修改banner
     * @param $id
     * @return array
     */
    public function actionUpdate($id)
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new SystemBannerModel();
            $where['id'] = $id;
            $data['status'] = $params['status'];
            $array = $model->do_update($where, $data);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 删除banner
     * @param $id
     * @return array
     */
    public function actionDelete($id)
    {
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new SystemBannerModel();
            $params['id'] = $id;
            $array = $model->do_delete($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }
}
