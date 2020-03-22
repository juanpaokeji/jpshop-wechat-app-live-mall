<?php

namespace app\controllers\merchant\shop;

use app\models\merchant\picture\PictureGroupModel;
use app\models\merchant\picture\PictureModel;
use app\models\merchant\vip\VipModel;
use yii;
use yii\web\MerchantController;

/**
 * 图片库 一个应用一个配置
 * @author  wmy
 * Class PictureController
 * @package app\controllers\merchant\vip
 */
class PictureController extends MerchantController
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * 商品库分类列表
     * @return array
     */
    public function actionList()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $model = new PictureGroupModel();
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->do_select($params);
            if($array['status'] == 200 && !empty($array['data'])){
                $pictureModel = new PictureModel();
                foreach ($array['data'] as $ke=>&$val){
                    $where['picture_group_id'] = $val['id'];
                    $number = (int)$pictureModel->get_count($where);
                    $val['number'] = $number == 0 ? 0: $number;
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 商品库分类查询单条
     * @return array
     */
    public function actionOne()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            if (!$params['id']) {
                return result(500, "缺少id");
            }
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $model = new PictureGroupModel();
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->one($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 商品库分类新增
     * @return array
     */
    public function actionAdd()
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new PictureGroupModel();
            //设置类目 参数
            $must = ['name', 'key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->add($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 商品库分类更新
     * @param $id
     * @return array
     */
    public function actionUpdate($id)
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            if (!$id) {
                return result(400, "缺少参数 id");
            }
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $where['key'] = $params['key'];
            unset($params['key']);
            $where['merchant_id'] = yii::$app->session['uid'];
            $model = new PictureGroupModel();
            $where['id'] = $id;
            $array = $model->do_update($where, $params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 商品库分类删除
     * @param $id
     * @return array
     */
    public function actionDelete($id)
    {
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new PictureGroupModel();
            $params['id'] = $id;
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['`key`'] = $params['key'];
            unset($params['key']);

            $params['merchant_id'] = yii::$app->session['uid'];
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $array = $model->do_delete($params);
                if ($array['status'] == 200) { // 删除分类将分类下的图片放到默认分类中
                    $pictureModel = new PictureModel();
                    $where['picture_group_id'] = $id;
                    $info = $pictureModel->one($where);
                    if ($info['status'] == 200) {
                        $up['picture_group_id'] = 0;
                        $array = $pictureModel->do_update($where, $up);
                    }
                }
                return $array;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 更具分类id查询商品图片列表
     * @param $id   分类id
     * @return array
     */
    public function actionPictureList($id)
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $model = new PictureModel();
            $params['merchant_id'] = yii::$app->session['uid'];
            $params['picture_group_id'] = (int)$id;
            unset($params['id']);
            $array = $model->do_select($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }
}
