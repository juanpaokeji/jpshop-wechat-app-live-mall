<?php

namespace app\controllers\shop;

use app\models\core\TableModel;
use app\models\merchant\app\AppAccessModel;
use app\models\merchant\system\UserModel;
use app\models\shop\GoodsModel;
use app\models\shop\MerchantCategoryModel;
use app\models\shop\ShopAssembleModel;
use app\models\shop\SubOrdersModel;
use app\models\tuan\ConfigModel;
use yii;
use yii\db\Exception;
use yii\web\ShopController;
use app\models\shop\ShopSuppliersModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class SuppliersController extends ShopController
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors()
    {
        return [
            'token' => [
                'class' => 'yii\filters\ShopFilter', //调用过滤器
//                'only' => ['single'],//指定控制器应用到哪些动作
                'except' => ['img'], //指定控制器不应用到哪些动作
            ]
        ];
    }

    public function actionAdd()
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new ShopSuppliersModel();
            $must = ['brand', 'mold', 'city', 'brand_type', 'introduce', 'pic_urls', 'realname', 'phone', 'position'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['key'] = yii::$app->session['key'];
            $params['uid'] = yii::$app->session['user_id'];
            //$params['pic_urls'] = json_decode($params['pic_urls'], true);
//            $str = "";
//            for ($i = 0; $i < count($params['pic_urls']); $i++) {
//                if ($i == 0) {
//                    $str = $params['pic_urls'][$i];
//                } else {
//                    $str = $str . "," . $params['pic_urls'][$i];
//                }
//            }
            $array = $model->do_add($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionImg()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $data['key'] = $params['key'];
            $model = new ConfigModel();
            $array = $model->do_one(['field' => 'pic_url,create_time,update_time', 'key' => $data['key']]);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionList()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $params['key'] = yii::$app->session['key'];
            $suplier = new UserModel();
            $md['type'] = 1;
            $md['`key`']= $params['key'];
            if(isset($params['limit'])){
                $md['limit'] = $params['limit'];
            }
            if(isset($params['page'])){
                $md['page'] = $params['page'];
            }
            $md['status'] =1;
            $list = $suplier->more($md);
            if ($list['status'] == 200) {
                for ($y = 0; $y< count($list['data']); $y++) {
                    unset($list['data'][$y]['password']);
                    unset($list['data'][$y]['salt']);
                    $list['data'][$y]['leader'] = json_decode($list['data'][$y]['leader'], true);
                    $goods = new GoodsModel();
                    $array = $goods->finds(['supplier_id' => $list['data'][$y]['id'],'is_check'=>1 , 'limit' => 3]);
                    $goods_id = array();
                    $groupModel = new ShopAssembleModel();
                    $where['key'] = $params['key'];
                    if($array['status']==200){
                        foreach ($array['data'] as $k => &$val) {
                            $goods_id[] = $val['id'];
                            $where['goods_id'] = $val['id'];
                            $where['status'] = 1;
                            $groupInfo = $groupModel->one($where);
                            $val['is_group'] = "0";
                            $val['is_self'] = "0";
                            if ($groupInfo['status'] == 200 && $val['is_open_assemble'] == 1) {
                                $val['is_group'] = "1";
                                $val['is_self'] = $groupInfo['data']['is_self'];
                                //获取拼团信息
                                if ($groupInfo['data']['type'] == 1) {
                                    $property_arr = json_decode($groupInfo['data']['property'], true);
                                    if ($property_arr) {
                                        foreach ($property_arr as $pro_key => $pro_val) {
                                            $val['max_group_price'] = max(array_column($pro_val, "price"));
                                        }
                                    }
                                } else {
                                    $val['max_group_price'] = $groupInfo['data']['min_price'];
                                }
                                $val['min_group_price'] = $groupInfo['data']['min_price'];
                            }
                            $res = $this->flash($val['id']);
                            if ($res != false) {
                                $property = explode("-", $res['data']['property']);
                                for ($i = 0; $i < count($property); $i++) {
                                    $a = json_decode($property[$i], true);
                                    for ($j = 0; $j < count($val['stock']); $j++) {
                                        if ($a['stock_id'] == $val['stock'][$j]['id']) {
                                            $val['stock'][$j]['number'] = $a['stocks'];
                                            $val['stock'][$j]['price'] = $a['flash_price'];
                                            $val['price'] = $res['data']['flash_price'];
                                        }
                                    }
                                }
                                $val['is_flash_sale'] = '1';
                                $val['start_time'] = $res['data']['start_time'];
                                $val['end_time'] = $res['data']['end_time'];
                                $val['send_time'] = $res['data']['send_time'];
                            }
                        }
                    }else{
                        $array['data']=array();
                    }
                    $list['data'][$y]['goods'] = $array['data'];
                }
            }
            return $list;
        } else {
            return result(500, "请求方式错误");
        }
    }

    //秒杀商品属性
    public function flash($goods_id)
    {

        $time = time();
        $stime = time() + 24 * 3600;
        $sql = "SELECT * FROM `shop_flash_sale_group` where FIND_IN_SET({$goods_id},goods_ids) and start_time <={$stime} and end_time >={$time} and delete_time is null;";
        $res = yii::$app->db->createCommand($sql)->queryAll();

        if (count($res) == 0) {
            return false;
        } else {

            $flashSale = new \app\models\spike\FlashSaleModel();
            $stock = $flashSale->do_one(['goods_id' => $goods_id]);
            $stock['data']['start_time'] = $res[0]['start_time'];
            $stock['data']['end_time'] = $res[0]['end_time'];
            $stock['data']['send_time'] = $res[0]['send_time'];
            return $stock;
        }

    }

    public function actionInfo($id){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $suplier = new UserModel();
            $info = $suplier->find(['id'=>$id]);
            $info['data']['leader'] = json_decode($info['data']['leader'],true);
            $locations = $info['data']['leader']['longitude'].','.$info['data']['leader']['latitude'];
            $locations = bd_amap($locations);
            $locations = explode(',',$locations);
            $info['data']['leader']['longitude'] = $locations[0];
            $info['data']['leader']['latitude'] = $locations[1];
            unset($info['data']['password']);
            unset($info['data']['salt']);
            return $info;
        } else {
            return result(500, "请求方式错误");
        }
    }
    public function actionGoods($id){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new GoodsModel();
            $params['`key`'] = yii::$app->session['key'];
            unset($params['key']);
            $model->goodsOut($params); //查询商品数量是否为0  为0下架
            if (isset($params['type']) && $params['type'] == 1) {
                $time = time();
                $params["start_time > {$time}"] = null;
                $params["status"] = 1;
            } else {
                $time = time();
                $params["start_time <= {$time}"] = null;
                $params["status"] = 1;
            }
            $params['supplier_id'] = $id;
            $appModel = new AppAccessModel();
            $app = $appModel->find(['`key`'=>$params['`key`']]);
            if($app['status']==200){
                if($app['data']['is_recruits']==1){
                    if($app['data']['is_recruits_show']==0){
                        $params['is_recruits']=0;

                    }
                }
            }
            unset($params['id']);
            $params['is_check'] = 1;
            $array = $model->finds($params);
            $goods_id = array();
            if ($array['status'] == 200) { //判断商品中是否有配置拼团信息
                $groupModel = new ShopAssembleModel();
                $where['key'] = $params['`key`'];
                foreach ($array['data'] as $k => &$val) {
                    $goods_id[] = $val['id'];
                    $where['goods_id'] = $val['id'];
                    $where['status'] = 1;
                    $groupInfo = $groupModel->one($where);
                    $val['is_group'] = "0";
                    $val['is_self'] = "0";
                    if ($groupInfo['status'] == 200 && $val['is_open_assemble'] == 1) {
                        $val['is_group'] = "1";
                        $val['is_self'] = $groupInfo['data']['is_self'];
                        //获取拼团信息
                        if($groupInfo['data']['type'] == 1){
                            $property_arr = json_decode($groupInfo['data']['property'], true);
                            if($property_arr){
                                foreach ($property_arr as $pro_key=>$pro_val){
                                    $val['max_group_price'] = max(array_column($pro_val, "price"));
                                }
                            }
                        }else{
                            $val['max_group_price'] = $groupInfo['data']['min_price'];
                        }
                        $val['min_group_price'] = $groupInfo['data']['min_price'];
                    }
                    $res = $this->flash($val['id']);
                    if ($res != false) {
                        $property = explode("-", $res['data']['property']);
                        for ($i = 0; $i < count($property); $i++) {
                            $a = json_decode($property[$i], true);
                            for ($j = 0; $j < count($val['stock']); $j++) {
                                if ($a['stock_id'] == $val['stock'][$j]['id']) {

                                    $val['stock'][$j]['number'] = $a['stocks'];
                                    $val['stock'][$j]['price'] = $a['flash_price'];
                                    $val['price'] = $a['flash_price'];
                                }
                            }
                        }
                        $val['is_flash_sale'] = '1';
                        $val['start_time'] = $res['data']['start_time'];
                        $val['end_time'] = $res['data']['end_time'];
                        $val['send_time'] = $res['data']['send_time'];
                    }
                }
                $orderModel = new SubOrdersModel();
                $ordersParams['in'] = ['goods_id', $goods_id];
                $ordersParams['join'][] = ['inner join', 'shop_user', 'shop_user.id=shop_order.user_id'];
                $ordersParams['field'] = "shop_order.*,shop_user.avatar";
                $ordersParams['groupBy'] = "user_id";
                $orders = $orderModel->do_select($ordersParams);
                if ($orders['status'] == 200) {
                    for ($i = 0; $i < count($array['data']); $i++) {
                        $array['data'][$i]['avatar'] = array();
                        for ($k = 0; $k < count($orders['data']); $k++) {
                            if ($array['data'][$i]['id'] == $orders['data'][$k]['goods_id']) {
                                $array['data'][$i]['avatar'][] = $orders['data'][$k]['avatar'];
                            }
                        }
                        if(count($array['data'][$i]['avatar'])<3){

                            $t = 3-count($array['data'][$i]['avatar']);
                            $sql = "select avatar from shop_user ORDER BY RAND() LIMIT {$t}";
                            $table  = new TableModel();
                            $rs  = $table->querySql($sql);
                            for($k = 0;$k<$t;$k++){
                                $array['data'][$i]['avatar'][] = $rs[$k]['avatar'];
                            }
                        }
                    }
                } else {
                    for ($i = 0; $i < count($array['data']); $i++) {
                        $array['data'][$i]['avatar'] = null;
                    }
                }
            }

            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionCategory($id){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new MerchantCategoryModel();
            $params['`key`'] = yii::$app->session['key'];
            unset($params['key']);
            unset($params['id']);
            $params['parent_id'] = 0;
            $params['supplier_id'] =$id;
            $params['status'] = 1;
            $array = $model->findall($params);
            if ($array['status'] == 200) {
                $array = array_chunk($array['data'], 8);
                return result(200, "请求成功", $array);
            } else {
                return $array;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }
}
