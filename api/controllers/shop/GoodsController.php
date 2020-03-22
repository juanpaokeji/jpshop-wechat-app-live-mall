<?php

namespace app\controllers\shop;

use app\models\core\TableModel;
use app\models\merchant\app\AppAccessModel;
use app\models\shop\OrderModel;
use app\models\shop\ShopAssembleAccessModel;
use app\models\shop\ShopAssembleModel;
use app\models\shop\ShopBargainInfoModel;
use app\models\shop\SubOrderModel;
use app\models\shop\SubOrdersModel;
use app\models\shop\UserModel;
use yii;
use yii\db\Exception;
use yii\web\ShopController;
use app\models\shop\GoodsModel;
use app\models\shop\ShopExpressTemplateDetailsModel;
use app\models\shop\CartModel;
use app\models\shop\StockModel;
use app\models\shop\CommentModel;
use app\models\shop\ShopExpressTemplateModel;
use EasyWeChat\Factory;
use app\models\core\CosModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class GoodsController extends ShopController
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors()
    {
        return [
            'token' => [
                'class' => 'yii\filters\ShopFilter', //调用过滤器
//                'only' => ['single'],//指定控制器应用到哪些动作
                'except' => ['list', 'sinleinfo', 'single', 'istop', 'stock', 'property', 'goods', 'buy-info'], //指定控制器不应用到哪些动作
            ]
        ];
    }

    public $config = [
        'app_id' => 'wx8df3a6f4a4f9ec54',
        'secret' => '7188287cd30aa902d5933654fed60559',
        'token' => 'juanPao',
        'aes_key' => '9ILejPm7rpu5kJykkY13oHMO80bYJkNbQfCvL3otaWA',
    ];

    public function actionList()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new GoodsModel();
            $params['`key`'] = $params['key'];
            unset($params['key']);
            //$params['merchant_id'] = yii::$app->session['merchant_id'];
            //   $params['is_flash_sale'] = 0;
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

            $appModel = new AppAccessModel();
            $app = $appModel->find(['`key`'=>$params['`key`']]);
            if($app['status']==200){
                if($app['data']['is_recruits']==1){
                    if($app['data']['is_recruits_show']==0){
                        $params['is_recruits']=0;

                    }
                }
            }
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

    public function actionAll()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new GoodsModel();
            $params['`key`'] = $params['key'];
            unset($params['key']);
            //$params['merchant_id'] = yii::$app->session['merchant_id'];
            //   $params['is_flash_sale'] = 0;
            $model->goodsOut($params); //查询商品数量是否为0  为0下架

            $time = time();
            $params["start_time < {$time}"] = null;
            $params["status"] = 1;
            $array = $model->finds($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 根据购物车选择的商品返回商品信息
     */
    public function actionCartinfo()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $model = new GoodsModel();
            $cartModel = new CartModel();
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['is_flash_sale'] = 0;
            $params['user_id'] = yii::$app->session['user_id'];
            $id = "";
            for ($i = 0; $i < count($params['data']); $i++) {
                if ($i == 0) {
                    $id = $params['data'][$i];
                } else {
                    $id = $id . "," . $params['data'][$i];
                }
            }
            $cartData = $cartModel->finds(["id in ({$id})" => null]);

            $goods_id = "";
            for ($i = 0; $i < count($cartData['data']); $i++) {
                if ($i == 0) {
                    $goods_id = $cartData['data'][$i]['goods_id'];
                } else {
                    $goods_id = $goods_id . "," . $cartData['data'][$i]['goods_id'];
                }
            }
            unset($params['data']);
            $params["id in ({$goods_id})"] = null;
            $array = $model->finds($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionIstop()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new GoodsModel();
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['is_top'] = 1;
            $params['is_flash_sale'] = 0;
            $array = $model->finds($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle($id)
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new GoodsModel();

            $params['`key`'] = $params['key'];
            unset($params['key']);

            //查询团购是否开启
            $tuanConfigModel = new \app\models\tuan\ConfigModel();
            $tuanConfig = $tuanConfigModel->do_one(['key' => $params['`key`']]);

            if ($tuanConfig['status'] == 500) {
                return $tuanConfig;
            }
            $array = [];
            $groupInfo = [];
            if ($tuanConfig['status'] == 204 || $tuanConfig['data']['status'] == 0) {
                $params['id'] = $id;
                //  $params['merchant_id'] = yii::$app->session['uid'];
                $params['fields'] = " id,name,pic_urls,unit,attribute,type,detail_info,simple_info,shop_express_template_id,bargain_start_time,bargain_end_time";
                $array = $model->find($params);

                if ($array['status'] == 200) {
                    $array['data']['attribute'] =json_decode($array['data']['attribute'],true) ;
                    $array['data']['pic_urls'] = explode(",", $array['data']['pic_urls']);
                    $array['data']['pic_urls'] = array_filter($array['data']['pic_urls']);
                } else {
                    return $array;
                }

                $ip = yii::$app->request->getUserIP();
                $rs = $this->ipAddress($ip);

                $tempModel = new ShopExpressTemplateModel();
                //$data['merchant_id'] = yii::$app->session['merchant_id'];
                $data['`key`'] = $params['`key`'];
                $data['status'] = 1;
                $temp = $tempModel->find($data);
                if ($temp['status'] != 200) {
                    return result(500, "内部错误");
                }
                if ($rs['status'] == 0) {
                    $params['searchName'] = $rs['result']['ad_info']['province'];
                } else {
                    $params['searchName'] = "全国统一运费";
                }
                $kdmb = new ShopExpressTemplateDetailsModel();
                $params['shop_express_template_id'] = $temp['data']['id'];
                unset($params['id']);
                $kdf = $kdmb->find($params);

                if ($kdf['status'] == 200) {
                    $array['data']['kdf'] = $kdf['data']['expand_price'];
                } else {
                    $params['searchName'] = "全国统一运费";
                    $kdf = $kdmb->find($params);
                    $array['data']['kdf'] = $kdf['data']['expand_price'];
                }
                $month = $model->MonthSale($id);
                $total = $model->TotalSale($id);

                if ($month['status'] != 200) {
                    return result(500, "查询失败");
                }
                if ($total['status'] != 200) {
                    return result(500, "查询失败");
                }

                $array['data']['monthSale'] = $month['data'];
                $array['data']['totalSale'] = $total['data'];
                $array['data']['totalSale']['total'] = $array['data']['totalSale']['total'] + $array['data']['sales_number'];
                $model = new CommentModel();
                $commentData['shop_user_comment.`key`'] = $params['`key`'];
                // $commentData['shop_user_comment.merchant_id'] = yii::$app->session['merchant_id'];
                $commentData['so.goods_id'] = $id;
                $comments = $model->findComment($commentData);
                $array['data']['comment'] = $comments['status'] == 200 ? $comments['data'] : "";
                $array['data']['is_flash_sale'] = '0';
                $array['data']['is_group'] = '0';
                $array['data']['is_self'] = "0";
                $array['data']['group'] = [];
                $res = $this->flash($id);
                if ($res != false) {
                    $property = explode("-", $res['data']['property']);
                    for ($i = 0; $i < count($property); $i++) {
                        $a = json_decode($property[$i], true);
                        for ($j = 0; $j < count($array['data']['stock']); $j++) {
                            if ($a['stock_id'] == $array['data']['stock'][$j]['id']) {

                                $array['data']['stock'][$j]['number'] = $a['stocks'];
                                $array['data']['stock'][$j]['price'] = $a['flash_price'];
                            }
                        }
                    }
                    $array['data']['is_flash_sale'] = '1';
                    $array['data']['start_time'] = $res['data']['start_time'];
                    $array['data']['end_time'] = $res['data']['end_time'];
                    $array['data']['send_time'] = $res['data']['send_time'];
                } else {
                    //判断商品是开启拼团
                    if ($array['data']['is_open_assemble'] == 1) {
                        $groupModel = new ShopAssembleModel();
                        $where['key'] = $params['`key`'];
                        $where['goods_id'] = $id;
                        $where['status'] = 1;
                        $groupInfo = $groupModel->one($where);
                        if ($groupInfo['status'] == 200) {
                            $array['data']['is_group'] = '1';
                            $array['data']['is_self'] = $groupInfo['data']['is_self'];
                            $array['data']['group'] = $groupInfo['data'];
                        }
                    }
                }
                $len = count($array['data']['stock']); //  5
//                for ($i = 0; $i < $len; $i++) {
//                    for ($j = $i + 1; $j < count($len); $j++) {
//                        if ($array['data']['stock'][$i]['price'] < $array['data']['stock'][$j]['price']) {
//                            $array['data']['max_price'] = $array['data']['stock'][$j]['price'];
//                        }
//                        if ($array['data']['stock'][$i]['price'] > $array['data']['stock'][$j]['price']) {
//                            $array['data']['min_price'] = $array['data']['stock'][$i]['price'];
//                        }
//                    }
//                }
//                $array['data']['avatar'] = $this->avatar($array['data']['id']);
            }
            if ($tuanConfig['status'] == 200 && $tuanConfig['data']['status']) {
                $params['id'] = $id;
                //  $params['merchant_id'] = yii::$app->session['uid'];
                $params['fields'] = " id,name,pic_urls,type,detail_info,simple_info,shop_express_template_id";
                $array = $model->find($params);
                if ($array['status'] == 200) {
                    $array['data']['pic_urls'] = explode(",", $array['data']['pic_urls']);
                    $array['data']['pic_urls'] = array_filter($array['data']['pic_urls']);
                } else {
                    return $array;
                }

                $ip = yii::$app->request->getUserIP();
                $rs = $this->ipAddress($ip);

               // $tempModel = new ShopExpressTemplateModel();
                //$data['merchant_id'] = yii::$app->session['merchant_id'];
               // $data['`key`'] = $params['`key`'];
              //  $data['status'] = 1;
              //  $temp = $tempModel->find($data);
             //   if ($temp['status'] != 200) {
             //       return result(500, "内部错误");
              //  }
              //  if ($rs['status'] == 0) {
               //     $params['searchName'] = $rs['result']['ad_info']['province'];
               // } else {
               //     $params['searchName'] = "全国统一运费";
               // }
               // $kdmb = new ShopExpressTemplateDetailsModel();
               // $params['shop_express_template_id'] = $temp['data']['id'];
               // unset($params['id']);
             //   $kdf = $kdmb->find($params);
				//if($kdf['status']==204){
				//	 return result(500, '未设置快递');
			//	}
              //  if ($kdf['status'] == 200) {
              //      $array['data']['kdf'] = $kdf['data']['expand_price'];
              //  } else {
               //     $params['searchName'] = "全国统一运费";
               //     $kdf = $kdmb->find($params);
              //      $array['data']['kdf'] = $kdf['data']['expand_price'];
              //  }
                $month = $model->MonthSale($id);
                $total = $model->TotalSale($id);

                if ($month['status'] != 200) {
                    return result(500, "查询失败");
                }
                if ($total['status'] != 200) {
                    return result(500, "查询失败");
                }
                $array['data']['monthSale'] = $month['data'];
                $array['data']['totalSale'] = $total['data'];
                $array['data']['totalSale']['total'] = $array['data']['totalSale']['total'] + $array['data']['sales_number'];
                $model = new CommentModel();
                $commentData['shop_user_comment.`key`'] = $params['`key`'];
                // $commentData['shop_user_comment.merchant_id'] = yii::$app->session['merchant_id'];
                $commentData['so.goods_id'] = $id;
                $comments = $model->findComment($commentData);
                $array['data']['comment'] = $comments['status'] == 200 ? $comments['data'] : "";
                $array['data']['is_flash_sale'] = '0';
                $array['data']['is_group'] = '0';
                $array['data']['is_self'] = "0";
                $array['data']['group'] = [];
                if (isset($params['leader_id'])) {
                    if ($params['leader_id'] != 0) {
                        $leaderModel = new \app\models\tuan\LeaderModel();
                        $leader = $leaderModel->do_one(['uid' => $params['leader_id'], 'status' => 1]);

                        $array['data']['leader'] = $leader['status'] == 200 ? $leader['data'] : "";
                        if ($leader['status'] != 200) {
                            return result(500, '未查询到团长信息');
                        }
                        $array['data']['tuan_express_fee'] = $leader['data']['tuan_express_fee'];
                        $array['data']['is_self'] = 0;
                    }

                    $res = $this->flash($id);

                    if ($res != false) {
                        $property = explode("-", $res['data']['property']);
                        for ($i = 0; $i < count($property); $i++) {
                            $a = json_decode($property[$i], true);
                            for ($j = 0; $j < count($array['data']['stock']); $j++) {
                                if ($a['stock_id'] == $array['data']['stock'][$j]['id']) {
                                    $array['data']['stock'][$j]['number'] = $a['stocks'];
                                    $array['data']['stock'][$j]['price'] = $a['flash_price'];
                                }
                            }
                        }

                        $array['data']['is_flash_sale'] = '1';
                        $array['data']['start_time'] = $res['data']['start_time'];
                        $array['data']['end_time'] = $res['data']['end_time'];
                        $array['data']['send_time'] = $res['data']['send_time'];
                    } else {
                        //判断商品是开启拼团
                        if ($array['data']['is_open_assemble'] == 1) {
                            $groupModel = new ShopAssembleModel();
                            $where['key'] = $params['`key`'];
                            $where['goods_id'] = $id;
                            $where['status'] = 1;
                            $groupInfo = $groupModel->one($where);
                            if ($groupInfo['status'] == 200) {
                                $array['data']['is_group'] = '1';
                                $array['data']['is_self'] = $groupInfo['data']['is_self'];
                                $array['data']['group'] = $groupInfo['data'];
                            }
                        }
                    }
                    $array['data']['format_bargain_start_time'] =date('Y-m-d H:i:s', $array['data']['bargain_start_time']);
                    $array['data']['format_bargain_end_time'] =date('Y-m-d H:i:s', $array['data']['bargain_end_time']);
                    $len = count($array['data']['stock']); //  5
                    $arr_pice = array();
                    for ($i = 0; $i < $len; $i++) {
                        $arr_pice[] = $array['data']['stock'][$i]['price'];
                    }
                    sort($arr_pice);
                    $array['data']['min_price'] = $arr_pice[0];
                    if($array['data']['is_bargain']==1){
                        $array['data']['min_price'] = $array['data']['bargain_price'];
                    }

                    $array['data']['max_price'] = $arr_pice[$len - 1];
                    $array['data']['avatar'] = $this->avatar($array['data']['id']);
                    $array['data']['attribute'] = json_decode($array['data']['attribute'], true);
                } else {
                    return result(500, '请选择团长');
                }
            }
            //获取拼团信息
            $array['data']['property'] = '';
            if (!empty($groupInfo) && $groupInfo['status'] == 200) {
                $property_arr = json_decode($groupInfo['data']['property'], true);
                $array['data']['property'] = $property_arr;
                if($groupInfo['data']['type'] == 1){
                    if($property_arr){
                        foreach ($property_arr as $pro_key=>$pro_val){
                            $array['data']['max_group_price'] = max(array_column($pro_val, "price"));
                        }
                    }
                }else{
                    $array['data']['max_group_price'] = $groupInfo['data']['min_price'];
                }
                $array['data']['min_group_price'] = $groupInfo['data']['min_price'];
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    //秒杀商品属性
    public function flash($goods_id)
    {

        $time = time();
        $stime = time() + 24 * 3600;
        $sql = "SELECT * FROM `shop_flash_sale_group` where FIND_IN_SET({$goods_id},goods_ids) and start_time <={$stime} and end_time >={$time};";
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

    //购买人头像
    public function avatar($id)
    {
        $sql = "select DISTINCT avatar from shop_order_group inner join shop_user on shop_user.id = shop_order_group.user_id inner join shop_order on shop_order.order_group_sn = shop_order_group.order_sn where shop_order.goods_id = {$id} and shop_order_group.status not in  (0,2,8)group by  shop_order.order_group_sn";
        $res = yii::$app->db->createCommand($sql)->queryAll();
        if(count($res)==0){
            $sql  = "select avatar from shop_user ORDER BY RAND() limit 7";
            $res = yii::$app->db->createCommand($sql)->queryAll();
        }
        return $res;
    }

    public function actionSinleinfo($id)
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new GoodsModel();

            $params['id'] = $id;
            $params['fields'] = " detail_info ";
            $array = $model->findinfo($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd()
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new GoodsModel();

            //设置类目 参数
            $must = ['name'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->add($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate($id)
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new GoodsModel();
            $params['id'] = $id;
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['uid'];
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $array = $model->update($params);
                return $array;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionDelete($id)
    {
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new GoodsModel();
            $params['id'] = $id;
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['uid'];
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $array = $model->delete($params);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function ipAddress($ip)
    {

        $url = "https://apis.map.qq.com/ws/location/v1/ip?ip={$ip}&key=N6CBZ-NIMKQ-IQ55X-GZXLL-C7HDH-NWBNZ&get_poi=0";
        $array = curlGet($url);
        $rs = jsonDecode($array);
        return $rs;
    }

    public function actionStock($id)
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new StockModel();
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['goods_id'] = $id;
            unset($params['id']);
            $wheredata['number'] = 0;
            if (isset($params['number'])) {
                $wheredata['number'] = $params['number'];
                unset($params['number']);
            }
            $array = $model->find($params);

            if ($array['status'] == 200) {
                $res = $this->flash($id);

                if ($res != false) {
                    $property = explode("-", $res['data']['property']);
                    for ($i = 0; $i < count($property); $i++) {
                        $a = json_decode($property[$i], true);
                        if ($a['stock_id'] == $array['data']['id']) {
                            $array['data']['number'] = $a['stocks'];
                            $array['data']['price'] = $a['flash_price'];
                        }
                    }

                    $array['data']['is_flash_sale'] = '1';
                }

                $array['data']['group_price'] = "0";
                $array['data']['leader_price'] = "0";
                if ($wheredata['number'] && $array) { // 去查询拼团信息
                    //判断商品是否是拼团商品
                    $goodsModel = new GoodsModel();
                    $goodsInfo = $goodsModel->find(['id' => $array['data']['goods_id']]);
                    if ($goodsInfo['status'] == 200 && $goodsInfo['data']['is_open_assemble'] == 1) {
                        $groupModel = new ShopAssembleModel();
                        $where['goods_id'] = $id;
                        $where['key'] = $params['`key`'];
                        $where['status'] = 1;
                        $groupinfo = $groupModel->one($where);
                        if ($groupinfo['status'] == 200) {
                            $wheredata['property1_name'] = $params['property1_name'];
                            $wheredata['property2_name'] = $params['property2_name'];
                            $leader_price = $groupModel::searchGroupPrice($groupinfo['data']['property'], $wheredata, 1);
                            $group_price = $groupModel::searchGroupPrice($groupinfo['data']['property'], $wheredata, 0);
                            if (!$leader_price || !$group_price) {
                                return result(500, "数据出错了");
                            }
                            $array['data']['leader_price'] = (string)$leader_price;
                            $array['data']['group_price'] = (string)$group_price;
                        }
                    }
                };
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionProperty($id)
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new StockModel();
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['goods_id'] = $id;
            unset($params['id']);
            $params['fields'] = " id,property1_name,property2_name,weight,create_time,update_time ";
            $array = $model->findall($params);
            if ($array['status'] == 200) {
                return result(200, '请求成功', $array['data']);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionGoods()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new GoodsModel();
            $must = ['id'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            //$params['id'] = json_decode($params['id'], true);
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params["id in ({$params['id']})"] = null;
            $params['fields'] = " id,pic_urls,name,short_name,price,stocks,status,create_time,update_time ";
            $params['stock'] = false;
            unset($params['id']);
            $array = $model->finds($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionBargain()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new GoodsModel();
            $must = ['id'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            //$params['id'] = json_decode($params['id'], true);
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params["id in ({$params['id']})"] = null;
            $params['fields'] = " id,pic_urls,name,short_name,price,stocks,status,create_time,update_time ";
            $params['stock'] = false;
            $params['is_bargain'] = 1;
            unset($params['id']);
            $array = $model->finds($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionBargainInfo(){
        {
            if (yii::$app->request->isGet) {
                $request = yii::$app->request; //获取 request 对象
                $params = $request->get(); //获取地址栏参数
                $model = new ShopBargainInfoModel();
                $must = ['id'];
                $rs = $this->checkInput($must, $params);
                if ($rs != false) {
                    return $rs;
                }
                $data['key'] = "";
                $array = $model->do_select($params);
                return $array;
            } else {
                return result(500, "请求方式错误");
            }
        }
    }




    public function actionQcode()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $model = new GoodsModel();
            $params['`key`'] = yii::$app->session['key'];
            $params['is_flash_sale'] = 0;
            $res = $model->goodsOut($params); //查询商品数量是否为0  为0下架
            if ($res === 0) {
                $array = $model->findOne($params);
            }
            if ($array['status'] == 200) {
                $data['pic_urls'] = explode(",", rtrim($array['data']['pic_urls'], ","));
                $data['name'] = $array['data']['name'];
                $data['price'] = $array['data']['price'];
            }

            $config = $this->getSystemConfig(yii::$app->session['key'], "miniprogram");

            $openPlatform = Factory::openPlatform($this->config);
            // 代小程序实现业务
            $miniProgram = $openPlatform->miniProgram($config['app_id'], $config['refresh_token']);
            $response = $miniProgram->app_code->getUnlimit($params['id'], ['width' => 280, "page" => $params['path']]);
            $url = "";
            if ($response instanceof \EasyWeChat\Kernel\Http\StreamResponse) {

                $filename = $response->saveAs(yii::getAlias('@webroot/') . "/uploads/qcode/" . date('Y') . "/" . date('m') . "/" . date('d') . "/", time() . $config['app_id'] . rand(1000, 9999) . ".png");
                $localRes = "./uploads/qcode/" . date('Y') . "/" . date('m') . "/" . date('d') . "/" . $filename;
                $cos = new CosModel();
                $cosRes = $cos->putObject($localRes);

                if ($cosRes['status'] == '200') {
                    $data['url'] = $cosRes['data'];
//                unlink(Yii::getAlias('@webroot/') . $localRes);
                } else {
                    unlink(Yii::getAlias('@webroot/') . $localRes);
                    return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
                }
            }

            return result(200, '请求成功', $data);
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     *  获取拼团信息，type=1 id 是开团人id，type=2 是商品id
     * @param $id
     * @return array
     * @throws Exception
     */
    public function actionGroupList($id)
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            if (empty($id)) {
                return result(500, "缺少参数");
            }
            $arr = [];
            $groupModel = new ShopAssembleAccessModel();
            $userModel = new UserModel();
            if (isset($params['type']) && $params['type'] == 1) { // 开团id
                //当前拼团信息
                $where['key'] = yii::$app->session['key'];
                $where['id'] = $id;
                $where['leader_id'] = 0;
                $leaderInfo = $groupModel->one($where); //开团人信息
                if ($leaderInfo['status'] != 200) {
                    return result(500, "出错了");
                }
                $orderModel = new OrderModel();
                $or_where['order_sn'] = $leaderInfo['data']['order_sn'];
                $or_where['key'] = $leaderInfo['data']['key'];
                $or_where['status'] = 11;
                $info = $orderModel->find($or_where);
                if ($info['status'] != 200) {
                    return result(500, "团已成功或已取消");
                }
                //查询当前拼团人的订单
                /*   $nowBuyOrder = $groupModel->one(['uid'=>yii::$app->session['user_id'],'order_sn'=>$params['order_sn']]);
                  if($nowBuyOrder['status'] != 200){
                  return result(500, "你买了吗");
                  } */
                $list = $groupModel->do_select(['key' => $leaderInfo['data']['key'], 'goods_id' => $leaderInfo['data']['goods_id'], 'leader_id' => $leaderInfo['data']['id']]);
                $userInfo = $userModel->find(['id' => $leaderInfo['data']['uid'], 'key' => $leaderInfo['data']['key']]);
                if ($userInfo['status'] == 200) {
                    $leaderInfo['data']['nickname'] = $userInfo['data']['nickname'];
                    $leaderInfo['data']['avatar'] = $userInfo['data']['avatar'];
                }
                // 差几人团
                $total = $groupModel->get_count(['leader_id' => $id, 'key' => $leaderInfo['data']['key']]);
                $arr['poor'] = bcsub($leaderInfo['data']['number'], $total + 1);
                $arr['list'] = $leaderInfo['data'];
                if ($list['status'] == 200) {
                    foreach ($list['data'] as &$val) {
                        $userInfo = $userModel->find(['id' => $val['uid'], 'key' => $val['key']]);
                        if ($userInfo['status'] == 200) {
                            $val['nickname'] = $userInfo['data']['nickname'];
                            $val['avatar'] = $userInfo['data']['avatar'];
                        }
                    }
                    array_push($list['data'], $leaderInfo['data']);
                } else {
                    $list['data'][] = $leaderInfo['data'];
                }
                $arr['list'] = $list['data'];
                $subOrderModel = new SubOrderModel();
                $subOrderInfo = $subOrderModel->find(['order_sn' => $params['order_sn']]);
                $arr['goods_info'] = [];
                if ($subOrderInfo['status'] == 200) {
                    $arr['goods_info'] = $subOrderInfo['data'];
                }
            } else { // id 商品id
                $where['key'] = yii::$app->session['key'];
                $where['goods_id'] = $id;
                $total = $groupModel->get_count($where);
                $arr['total'] = $total;
                $arr['list'] = [];
                $user_id = yii::$app->session['user_id'];
                //拼团列表
                $sql = "SELECT
                            saa.* 
                        FROM
                            shop_assemble_access AS saa
                            LEFT JOIN shop_order_group AS sog ON saa.order_sn = sog.order_sn
                        WHERE
                            saa.`key` = '{$where['key']}' 
                            AND saa.goods_id = '{$id}'
                            AND saa.`status` =1
                       	    AND sog.`status`=11
                       	    AND saa.`uid`!= {$user_id}
                       	    AND saa.`leader_id`=0
                       	    ORDER BY saa.id DESC
                            LIMIT 0,3";
                $list = yii::$app->db->createCommand($sql)->queryAll();
                $newArr = [];
                if (count($list) > 0) {
                    foreach ($list as &$val) {
                        $userInfo = $userModel->find(['id' => $val['uid'], 'key' => $val['key']]);
                        if ($userInfo['status'] == 200) {
                            $val['nickname'] = $userInfo['data']['nickname'];
                            $val['avatar'] = $userInfo['data']['avatar'];
                        }
                        // 差几人团
                        $total = $groupModel->get_count(['leader_id' => $val['id'], 'key' => $val['key']]);
                        $val['poor'] = bcsub($val['number'], $total + 1);
                        $newArr[] = $val;
                    }
                    $arr['list'] = $newArr;
                }
            }
            return result(200, "请求成功", $arr);
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionBuyInfo($id)
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $subOrderModel = new SubOrdersModel();
            $data['goods_id'] = $id;
            $data['page'] = $params['page'];
            $data['field'] = "shop_order.number,shop_user.avatar,shop_user.nickname,shop_goods.sales_number,shop_order_group.create_time";
            $data['join'][] = ['inner join', 'shop_order_group', 'shop_order_group.order_sn = shop_order.order_group_sn'];
            $data['join'][] = ['inner join', 'shop_user', 'shop_user.id = shop_order_group.user_id'];
            $data['join'][] = ['inner join', 'shop_goods', 'shop_goods.id = shop_order.goods_id'];
            $data['groupBy'] = 'order_group_sn';
            $res = $subOrderModel->do_select($data);
            if ($res['status'] == 200) {
                $table = new TableModel();
                $sql = "select sum(number) as number from shop_order where goods_id = {$data['goods_id']}";
                $sql1 = "select count(id) as number from shop_order where goods_id = {$data['goods_id']}";
                $a = $table->querySql($sql);
                $b = $table->querySql($sql1);
                $res['people'] = $b[0]['number'] + $res['data'][0]['sales_number'];
                $res['number'] = $a[0]['number'];
            }
            return $res;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
