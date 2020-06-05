<?php

namespace app\controllers\merchant\shop;

use app\models\merchant\system\OperationRecordModel;
use app\models\merchant\system\ShopRecommendGoodsModel;
use app\models\shop\ShopGoodsModel;
use yii;
use yii\db\Exception;
use yii\web\MerchantController;
use app\models\shop\ShopExpressTemplateModel;
use app\models\shop\ShopExpressTemplateDetailsModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class RecommendGoodsController extends MerchantController
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * 地址:/admin/group/index 默认访问
     * @throws Exception if the model cannot be found
     * @return array
     */

    public function actionGoods(){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new ShopGoodsModel();
            $where['status'] = 1;
            if (isset($params['searchName'])) {
                if ($params['searchName'] != "") {
                    $where['name'] = ['like', "{$params['searchName']}"];
                }
            }
            $where['field'] = "id,name,pic_urls,price";
            $where['limit'] = $params['limit'];
            $where['page'] = $params['page'];
            $array = $model->do_select($where);
            if ($array['status'] == 200){
                foreach ($array['data'] as $k=>$v){
                    $pic = explode(',',$v['pic_urls']);
                    $array['data'][$k]['pic_urls'] = $pic[0];
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionList(){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new ShopRecommendGoodsModel();
            $where['key'] = $params['key'];
            $where['merchant_id'] = yii::$app->session['uid'];
            $where['supplier_id'] = 0;
            $info = $model->do_one($where);
            if ($info['status'] == 200){
                $config = json_decode($info['data']['config'],true);
                $data['centre_show'] = $config['centre_show'];
                $data['bottom_show'] = $config['bottom_show'];
                $data['pay_finish_show'] = $config['pay_finish_show'];
                $goodsModel = new ShopGoodsModel();
                $gWhere['field'] = "id,name,pic_urls,price,is_open_assemble,is_advance_sale,is_bargain,is_flash_sale";
                $gWhere['in'] = ['id',$config['goods_ids']];
                $goodsInfo = $goodsModel->do_select($gWhere);

                if ($goodsInfo['status'] == 200){
                    foreach ($goodsInfo['data'] as $k=>$v){
                        $temp['id'] = $v['id'];
                        $temp['name'] = $v['name'];
                        $pic = explode(',',$v['pic_urls']);
                        $temp['pic_urls'] = $pic[0];
                        $temp['price'] = $v['price'];
                        if ($v['is_open_assemble'] == 1 || $v['is_advance_sale'] == 1 || $v['is_bargain'] == 1 || $v['is_flash_sale'] == 1){
                            //有活动的商品
                            $activity[] = $temp;
                        }else{
                            //无活动的商品
                            $ordinary[] = $temp;
                        }
                    }
                    //将有活动的商品排在前面
                    if (isset($ordinary) && isset($activity)){
                        $arrayMerge = array_merge_recursive($activity,$ordinary);
                    }elseif (isset($ordinary) && !isset($activity)){
                        $arrayMerge = $ordinary;
                    }elseif (!isset($ordinary) && isset($activity)){
                        $arrayMerge = $activity;
                    }else{
                        $arrayMerge = [];
                    }
                    $data['goods_info'] = $arrayMerge;
                }
                return result(200, "请求成功",$data);
            }else{
                return result(500, "查询失败");
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd(){
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $must = ['key','goods_ids','centre_show','bottom_show','pay_finish_show'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $model = new ShopRecommendGoodsModel();
            $where['key'] = $params['key'];
            $where['supplier_id'] = 0;
            $info = $model->do_one($where);
            $temp['goods_ids'] = $params['goods_ids'];
            $temp['centre_show'] = $params['centre_show'];
            $temp['bottom_show'] = $params['bottom_show'];
            $temp['pay_finish_show'] = $params['pay_finish_show'];
            $data['config'] = json_encode($temp, JSON_UNESCAPED_UNICODE);
            if ($info['status'] == 200){
                $array = $model->do_update(['id'=>$info['data']['id']],$data);
            }else{
                $data['key'] = $params['key'];
                $data['merchant_id'] = yii::$app->session['uid'];
                $array = $model->do_add($data);
            }
            //添加操作记录
            $operationRecordModel = new OperationRecordModel();
            $operationRecordData['key'] = $params['key'];
            $operationRecordData['merchant_id'] = yii::$app->session['uid'];
            $operationRecordData['operation_type'] = '更新';
            $operationRecordData['operation_id'] = 1;
            $operationRecordData['module_name'] = '推荐商品';
            $operationRecordModel->do_add($operationRecordData);

            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }


}
