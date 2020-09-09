<?php

namespace app\controllers\shop;

use app\models\merchant\system\ShopSolitaireModel;
use app\models\shop\ShopSolitaireCommentModel;
use yii;
use yii\web\ShopController;

class SolitaireCommentController extends ShopController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * 接龙评论列表
     */
    public function actionList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            //设置类目 参数
            $must = ['solitaire_id'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $solitaireModel = new ShopSolitaireModel();
            $info = $solitaireModel->do_one(['id'=>$params['solitaire_id']]);
            if ($info['status'] != 200){
                return result(204, "未查询到此活动信息");
            }

            $model = new ShopSolitaireCommentModel();
            $where['field'] = "shop_solitaire_comment.*,shop_user.nickname,shop_user.avatar";
            $where['shop_solitaire_comment.key'] = yii::$app->session['key'];
            $where['shop_solitaire_comment.merchant_id'] = yii::$app->session['merchant_id'];
            $where['shop_solitaire_comment.solitaire_id'] = $params['solitaire_id'];
            if ($info['data']['is_check'] == 1){
                $where['shop_solitaire_comment.is_check'] = 1;
            }
            $where['join'][] = ['left join','shop_user','shop_user.id = shop_solitaire_comment.user_id'];
            if (isset($params['limit'])){
                $where['limit'] = $params['limit'];
                $where['page'] = $params['page'];
            }else{
                $where['limit'] = false;
            }
            $array = $model->do_select($where);
            if ($array['status'] == 200){
                foreach ($array['data'] as $k=>$v){
                    $array['data'][$k]['pic_urls'] = explode(',',$v['pic_urls']);
                }
            }
            return $array;

        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd(){
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            //设置类目 参数
            $must = ['solitaire_id'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $solitaireModel = new ShopSolitaireModel();
            $info = $solitaireModel->do_one(['id'=>$params['solitaire_id']]);
            if ($info['status'] == 200 && $info['data']['is_comment'] == 0){
                return result(500, "该活动暂不支持评论");
            }

            $model = new ShopSolitaireCommentModel();
            $data['key'] = yii::$app->session['key'];
            $data['merchant_id'] = yii::$app->session['merchant_id'];
            $data['user_id'] = yii::$app->session['user_id'];
            $data['solitaire_id'] = $params['solitaire_id'];
            $data['content'] = $params['content'];
            $data['pic_urls'] = $params['pic_urls'];
            $array = $model->do_add($data);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }




}
