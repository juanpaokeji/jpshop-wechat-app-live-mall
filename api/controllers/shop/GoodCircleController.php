<?php

namespace app\controllers\shop;

use app\models\merchant\app\AppAccessModel;
use Yii;
use yii\db\Exception;
use yii\web\ShopController;
use app\models\shop\GoodsModel;
use app\models\shop\MerchantCategoryModel;
use app\models\shop\OrderModel;
use EasyWeChat\Factory;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class GoodCircleController extends ShopController{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors() {
        return [
            'token' => [
                'class' => 'yii\filters\ShopFilter', //调用过滤器
//                'only' => ['single'],//指定控制器应用到哪些动作
                'except' => ['goods','order','update','historyorder','single'],//指定控制器不应用到哪些动作
            ]
        ];
    }

    //好物圈更新或导入物品信息
    public function actionGoods()
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->post(); //获取地址栏参数
            $must = ['key','goods_id'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            //获取小程序信息配置
            $config = $this->getSystemConfig($params['key'], "miniprogram");
            if ($config == false) {
                return result(500, "未配置小程序信息");
            }
            $openPlatform = Factory::openPlatform($this->config);
            $miniProgram = $openPlatform->miniProgram($config['app_id'],$config['refresh_token']);
            $AuthorizerToken = $miniProgram->access_token->getToken();
            $access_token = $AuthorizerToken['authorizer_access_token'];

            $url = "https://api.weixin.qq.com/mall/importproduct?access_token={$access_token}";

            $model = new GoodsModel();
            $categoryModel = new MerchantCategoryModel();
            //订单商品
            $sql = "SELECT * FROM `shop_goods` WHERE `key` = '".$params['key']."' AND id = ".$params['goods_id'];
            $goodsData = $model->querySql($sql);

            //商品分类
            $sql = "SELECT * FROM `shop_marchant_category` WHERE `key` = '".$params['key']."'";
            $categoryData = $categoryModel->querySql($sql);

            foreach ($goodsData as $key=>$val) {
                $categoryList = [];
                foreach ($categoryData as $ck => $cv) {
                    if ($val['m_category_id'] == $cv['id']) {
                        $categoryList[] = $cv['name'];
                        foreach ($categoryData as $pk => $pv) {
                            if ($pv['id'] == $cv['parent_id']) {
                                $categoryList[] = $pv['name'];
                            }
                        }
                    }
                }
                if ($val['status'] == 1){
                    $status = 1;
                } elseif ($val['status'] == 0){
                    $status = 2;
                }

                $data['product_list'][$key]['item_code'] = $val['id'];  //物品ID（SPU ID），要求appid下全局唯一
                $data['product_list'][$key]['title'] = $val['name'];       //物品名称
                $data['product_list'][$key]['category_list'] = $categoryList;     //物品类目列表，用于搜索排序
                $data['product_list'][$key]['can_be_search'] = true;     //物品能否被搜索
                $data['product_list'][$key]['image_list'] = explode(",", trim($val['pic_urls'], ','));   //物品图片链接列表，图片宽度必须大于750px，宽高比建议4:3 - 1:1之间
                $data['product_list'][$key]['src_wxapp_path'] = 'pages/goodsItem/goodsItem/goodsItem?id='.$val['id'];    //物品来源小程序路径
                $data['product_list'][$key]['sku_list'][0] = array(
                    'sku_id' => $val['id'],  //物品sku_id，特殊情况下可以填入与item_code一致
                    'price' => $val['price'] * 100,  //物品价格，以分为单位
                    'status' => $status,  //物品状态，1：在售，2：停售，3：售罄
                );    //物品SKU列表，单次导入不超过16个SKU，微信后台会合并多次导入的SKU
            }

            $array = json_encode($data, JSON_UNESCAPED_UNICODE);

            $rs = json_decode(curlPost($url, $array), true);
            if ($rs['errcode'] == 0){
                return result(200, "商品已导入/更新至好物圈");
            } else {
                return $rs;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    //好物圈导入订单
    public function actionOrder()
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->post(); //获取地址栏参数
            $must = ['order_id'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
//            $params['key'] = yii::$app->session['key'];
            //查询微信支付订单
            $orderModel = new OrderModel();
            $categoryModel = new MerchantCategoryModel();
            $sql = "SELECT sog.*,sp.transaction_id,sp.pay_time,su.mini_open_id FROM `shop_order_group` sog LEFT JOIN `system_pay` sp ON sp.order_id = sog.order_sn LEFT JOIN `shop_user` su ON su.id = sog.user_id where sog.`key` = '".$params['key']."' AND sog.order_sn = '".$params['order_id']."' and sp.type = 3 and sp.transaction_id != ''";
            $orderData = $orderModel->querySql($sql);
            //订单商品
            $sql = "SELECT so.*,sg.m_category_id FROM `shop_order_group` sog LEFT JOIN `system_pay` sp ON sp.order_id = sog.order_sn LEFT JOIN `shop_order` so ON so.order_group_sn = sog.order_sn LEFT JOIN `shop_goods` sg ON sg.id = so.goods_id WHERE sog.`key` = '".$params['key']."' AND sog.order_sn = '".$params['order_id']."' AND sp.type = 3 AND sp.transaction_id != ''";
            $goodsData = $orderModel->querySql($sql);
            //商品分类
            $sql = "SELECT * FROM `shop_marchant_category` WHERE `key` = '".$params['key']."'";
            $categoryData = $categoryModel->querySql($sql);

            if (count($orderData)<=0 || count($goodsData)<=0 || count($categoryData)<=0  ){
                return result(204, "查询失败");
            }

            //获取小程序信息配置
            $config = $this->getSystemConfig($params['key'], "miniprogram");

            if ($config == false) {
                return result(500, "未配置小程序信息");
            }

            $openPlatform = Factory::openPlatform($this->config);
            $miniProgram = $openPlatform->miniProgram($config['app_id'],$config['refresh_token']);
            $AuthorizerToken = $miniProgram->access_token->getToken();
            $access_token = $AuthorizerToken['authorizer_access_token'];

            $url = "https://api.weixin.qq.com/mall/importorder?action=add-order&is_history=1&access_token={$access_token}";

            //组装参数
            foreach ($orderData as $key=>$val){
                $data[$key]['order_id'] = $val['order_sn'];  //订单id，需要保证唯一性
                $data[$key]['create_time'] = $val['create_time'];  //订单创建时间，unix时间戳
                $data[$key]['pay_finish_time'] = $val['pay_time'];  //支付完成时间，unix时间戳
                $data[$key]['trans_id'] = $val['transaction_id'];  //微信支付订单id，对于使用微信支付的订单，该字段必填
                $data[$key]['fee'] = $val['total_price']*100;  //订单金额，单位：分
                if ($val['status'] == 1) {
                    $data[$key]['status'] = 3;//订单状态，3：支付完成 4：已发货 5：已退款 100: 已完成
                } elseif ($val['status'] == 3) {
                    $data[$key]['status'] = 4;  //订单状态，3：支付完成 4：已发货 5：已退款 100: 已完成
                } elseif ($val['status'] == 4 || $val['status'] == 9) {
                    $data[$key]['status'] = 5;  //订单状态，3：支付完成 4：已发货 5：已退款 100: 已完成
                } elseif ($val['status'] == 6 || $val['status'] == 7) {
                    $data[$key]['status'] = 100;  //订单状态，3：支付完成 4：已发货 5：已退款 100: 已完成
                } else {
                    return result(500, "订单状态异常");
                }
                $data[$key]['ext_info'] = [
                    'express_info'=>[
                        'price'=>$val['express_price']*100 //运费，单位：分
                    ],  //快递信息
                    'brand_info'=>[
                        'contact_detail_page'=>[
                            'kf_type'=>1   //在线客服类型 1 没有在线客服; 2 微信客服消息; 3 小程序自有客服; 4 公众号h5自有客服
                        ]  //联系商家页面
                    ],  //商家信息
                    'payment_method'=>1,  //订单支付方式，0：未知方式 1：微信支付 2：其他支付方式
                    'user_open_id'=>$val['mini_open_id'],  //用户的openid，参见openid说明
                    'order_detail_page'=>[
                        'path'=>'pages/orderItem/orderItem/orderItem?order_sn='.$val['order_sn']  //小程序订单详情页跳转链接
                    ],  //订单详情页（小程序页面）
                ];  //订单扩展信息
                foreach ($goodsData as $gk=>$gv){
                    if ($gv['order_group_sn'] == $val['order_sn']){
                        $categoryList = [];
                        foreach ($categoryData as $ck=>$cv){
                            if ($gv['m_category_id'] == $cv['id']){
                                $categoryList[] = $cv['name'];
                                foreach ($categoryData as $pk => $pv){
                                    if ($pv['id'] == $cv['parent_id']){
                                        $categoryList[] = $pv['name'];
                                    }
                                }
                            }
                        }
                        $categoryList = array_reverse($categoryList);
                        $data[$key]['ext_info']['product_info']['item_list'][] = [
                            'item_code'=>$gv['goods_id'],  //物品ID（SPU ID），要求appid下全局唯一
                            'sku_id'=>$gv['goods_id'],  //sku_id
                            'amount'=>$gv['number'],  //物品数量
                            'total_fee'=>$gv['total_price']*100,  //物品总价，单位：分
                            'thumb_url'=>$gv['pic_url'],  //物品图片，图片宽度必须大于750px，宽高比建议4:3 - 1:1之间
                            'title'=>$gv['name'],  //物品名称
                            'unit_price'=>$gv['price']*100,  //物品单价（实际售价），单位：分
                            'original_price'=>$gv['price']*100,  //物品原价，单位：分
                            'category_list'=>$categoryList,  //物品类目列表
                            'item_detail_page'=>['path'=>'pages/goodsItem/goodsItem/goodsItem?id='.$gv['goods_id']],  //小程序物品详情页跳转链接
                            'can_be_search'=>true
                        ];  //物品相关信息
                    }
                }
            }

            //每次最多请求10个订单
            for ($i=0;$i<=count($data);$i+=10){
                $orderList['order_list'] = array_slice($data,$i,10);
                $array = json_encode($orderList, JSON_UNESCAPED_UNICODE);
                $rs = json_decode(curlPost($url, $array), true);
                if ($rs['errcode'] == 0){
                    return result(200, "订单已导入好物圈");
                } else {
                    return result(204, "订单导入好物圈失败",$rs);;
                }
            }

        } else {
            return result(500, "请求方式错误");
        }
    }

    //好物圈订单更新
    public function actionUpdate($id)
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取地址栏参数
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            //查询好物圈插件是否开启
            $appAccessModel = new AppAccessModel();
            $appAccessInfo = $appAccessModel->find(['`key`' => $params['key']]);
            if ($appAccessInfo['status'] != 200){
                return $appAccessInfo;
            }

            if ($appAccessInfo['data']['good_phenosphere'] == 1){
                //查询微信支付订单
                $orderModel = new OrderModel();
                $sql = "SELECT sog.*,sp.transaction_id,sp.pay_time,su.mini_open_id FROM `shop_order_group` sog LEFT JOIN `system_pay` sp ON sp.order_id = sog.order_sn LEFT JOIN `shop_user` su ON su.id = sog.user_id where sog.`key` = '".$params['key']."' AND sog.order_sn = '".$id."' and sp.type = 3 and sp.transaction_id != ''";
                $orderData = $orderModel->querySql($sql);
                if (empty($orderData)){
                    return result(204, "目前好物圈只支持微信支付订单导入");
                }

                //获取小程序上传信息配置
                $config = $this->getSystemConfig($params['key'], "miniprogram");

                if ($config == false) {
                    return result(500, "未配置小程序信息");
                }
                $openPlatform = Factory::openPlatform($this->config);
                $miniProgram = $openPlatform->miniProgram($config['app_id'],$config['refresh_token']);
                $AuthorizerToken = $miniProgram->access_token->getToken();
                $access_token = $AuthorizerToken['authorizer_access_token'];

                $url = "https://api.weixin.qq.com/mall/importorder?action=update-order&is_history=0&access_token={$access_token}";

                foreach ($orderData as $key=>$val){
                    $data['order_list'][$key]['order_id'] = $val['order_sn'];  //订单id，需要保证唯一性
                    $data['order_list'][$key]['trans_id'] = $val['transaction_id'];  //微信支付订单id，对于使用微信支付的订单，该字段必填
                    if ($val['status'] == 1) {
                        $data['order_list'][$key]['status'] = 3;//订单状态，3：支付完成 4：已发货 5：已退款 100: 已完成
                    } elseif ($val['status'] == 3) {
                        $data['order_list'][$key]['status'] = 4;  //订单状态，3：支付完成 4：已发货 5：已退款 100: 已完成
                    } elseif ($val['status'] == 4 || $val['status'] == 9) {
                        $data['order_list'][$key]['status'] = 5;  //订单状态，3：支付完成 4：已发货 5：已退款 100: 已完成
                    } elseif ($val['status'] == 6 || $val['status'] == 7) {
                        $data['order_list'][$key]['status'] = 100;  //订单状态，3：支付完成 4：已发货 5：已退款 100: 已完成
                    } else {
                        return result(500, "订单状态异常");
                    }
                    $data['order_list'][$key]['ext_info'] = [
                        'user_open_id'=>$val['mini_open_id'],  //用户的openid，参见openid说明
                    ];  //订单扩展信息
                }

                $array = json_encode($data, JSON_UNESCAPED_UNICODE);

                $rs = json_decode(curlPost($url, $array), true);
                if ($rs['errcode'] == 0){
                    return result(200, "好物圈订单已更新");
                } else {
                    return $rs;
                }
            } else {
                return result(204, "未启用好物圈");
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    //好物圈导入历史订单
    public function actionHistoryorder()
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->post(); //获取地址栏参数
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            //查询当前小程序所有微信支付订单
            $orderModel = new OrderModel();
            $categoryModel = new MerchantCategoryModel();
            $sql = "SELECT sog.*,sp.transaction_id,sp.pay_time,su.mini_open_id FROM `shop_order_group` sog LEFT JOIN `system_pay` sp ON sp.order_id = sog.order_sn LEFT JOIN `shop_user` su ON su.id = sog.user_id where sog.`key` = '".$params['key']."' and sp.type = 3 and sp.transaction_id != ''";
            $orderData = $orderModel->querySql($sql);
            //订单商品
            $sql = "SELECT so.*,sg.m_category_id FROM `shop_order_group` sog LEFT JOIN `system_pay` sp ON sp.order_id = sog.order_sn LEFT JOIN `shop_order` so ON so.order_group_sn = sog.order_sn LEFT JOIN `shop_goods` sg ON sg.id = so.goods_id WHERE sog.`key` = '".$params['key']."' AND sp.type = 3 AND sp.transaction_id != ''";
            $goodsData = $orderModel->querySql($sql);
            //商品分类
            $sql = "SELECT * FROM `shop_marchant_category` WHERE `key` = '".$params['key']."'";
            $categoryData = $categoryModel->querySql($sql);

            if (count($orderData)<=0 || count($goodsData)<=0 || count($categoryData)<=0  ){
                return result(204, "查询失败");
            }

            //获取小程序上传信息配置
            $config = $this->getSystemConfig($params['key'], "miniprogram");

            if ($config == false) {
                return result(500, "未配置小程序信息");
            }
            $openPlatform = Factory::openPlatform($this->config);
            $miniProgram = $openPlatform->miniProgram($config['app_id'],$config['refresh_token']);
            $AuthorizerToken = $miniProgram->access_token->getToken();
            $access_token = $AuthorizerToken['authorizer_access_token'];

            $url = "https://api.weixin.qq.com/mall/importorder?action=add-order&is_history=1&access_token={$access_token}";

            //组装参数
            foreach ($orderData as $key=>$val){
                $data[$key]['order_id'] = $val['order_sn'];  //订单id，需要保证唯一性
                $data[$key]['create_time'] = $val['create_time'];  //订单创建时间，unix时间戳
                $data[$key]['pay_finish_time'] = $val['pay_time'];  //支付完成时间，unix时间戳
                $data[$key]['trans_id'] = $val['transaction_id'];  //微信支付订单id，对于使用微信支付的订单，该字段必填
                $data[$key]['fee'] = $val['total_price']*100;  //订单金额，单位：分
                if ($val['status'] == 1) {
                    $data[$key]['status'] = 3;//订单状态，3：支付完成 4：已发货 5：已退款 100: 已完成
                } elseif ($val['status'] == 3) {
                    $data[$key]['status'] = 4;  //订单状态，3：支付完成 4：已发货 5：已退款 100: 已完成
                } elseif ($val['status'] == 4 || $val['status'] == 9) {
                    $data[$key]['status'] = 5;  //订单状态，3：支付完成 4：已发货 5：已退款 100: 已完成
                } elseif ($val['status'] == 6 || $val['status'] == 7) {
                    $data[$key]['status'] = 100;  //订单状态，3：支付完成 4：已发货 5：已退款 100: 已完成
                } else {
                    return result(500, "订单状态异常");
                }
                $data[$key]['ext_info'] = [
                    'express_info'=>[
                        'price'=>$val['express_price']*100 //运费，单位：分
                    ],  //快递信息
                    'brand_info'=>[
                        'contact_detail_page'=>[
                            'kf_type'=>1   //在线客服类型 1 没有在线客服; 2 微信客服消息; 3 小程序自有客服; 4 公众号h5自有客服
                        ]  //联系商家页面
                    ],  //商家信息
                    'payment_method'=>1,  //订单支付方式，0：未知方式 1：微信支付 2：其他支付方式
                    'user_open_id'=>$val['mini_open_id'],  //用户的openid，参见openid说明
                    'order_detail_page'=>[
                        'path'=>'pages/orderItem/orderItem/orderItem?order_sn='.$val['order_sn']  //小程序订单详情页跳转链接
                    ],  //订单详情页（小程序页面）
                ];  //订单扩展信息
                foreach ($goodsData as $gk=>$gv){
                    if ($gv['order_group_sn'] == $val['order_sn']){
                        $categoryList = [];
                        foreach ($categoryData as $ck=>$cv){
                            if ($gv['m_category_id'] == $cv['id']){
                                $categoryList[] = $cv['name'];
                                foreach ($categoryData as $pk => $pv){
                                    if ($pv['id'] == $cv['parent_id']){
                                        $categoryList[] = $pv['name'];
                                    }
                                }
                            }
                        }
                        $categoryList = array_reverse($categoryList);
                        $data[$key]['ext_info']['product_info']['item_list'][] = [
                            'item_code'=>$gv['goods_id'],  //物品ID（SPU ID），要求appid下全局唯一
                            'sku_id'=>$gv['goods_id'],  //sku_id
                            'amount'=>$gv['number'],  //物品数量
                            'total_fee'=>$gv['total_price']*100,  //物品总价，单位：分
                            'thumb_url'=>$gv['pic_url'],  //物品图片，图片宽度必须大于750px，宽高比建议4:3 - 1:1之间
                            'title'=>$gv['name'],  //物品名称
                            'unit_price'=>$gv['price']*100,  //物品单价（实际售价），单位：分
                            'original_price'=>$gv['price']*100,  //物品原价，单位：分
                            'category_list'=>$categoryList,  //物品类目列表
                            'item_detail_page'=>['path'=>'pages/goodsItem/goodsItem/goodsItem?id='.$gv['goods_id']],  //小程序物品详情页跳转链接
                            'can_be_search'=>true
                        ];  //物品相关信息
                    }
                }
            }

            //每次最多请求10个订单
            for ($i=0;$i<=count($data);$i+=10){
                $orderList['order_list'] = array_slice($data,$i,10);
                $array = json_encode($orderList, JSON_UNESCAPED_UNICODE);
                $rs = json_decode(curlPost($url, $array), true);
                if ($rs['errcode'] == 0){
                    return result(200, "订单已导入好物圈");
                } else {
                    return result(500, "订单导入好物圈失败",$rs);;
                }
            }

        } else {
            return result(500, "请求方式错误");
        }
    }

    //好物圈商品查询
    public function actionSingle($id)
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            //获取小程序上传信息配置
            $config = $this->getSystemConfig($params['key'], "miniprogram");
            if ($config == false) {
                return result(500, "未配置小程序信息");
            }
            $openPlatform = Factory::openPlatform($this->config);
            $miniProgram = $openPlatform->miniProgram($config['app_id'],$config['refresh_token']);
            $AuthorizerToken = $miniProgram->access_token->getToken();
            $access_token = $AuthorizerToken['authorizer_access_token'];

            $url = "https://api.weixin.qq.com/mall/queryproduct?access_token={$access_token}&type=batchquery";

            $data['key_list'][0] = ['item_code'=>$id];

            $array = json_encode($data, JSON_UNESCAPED_UNICODE);

            $rs = json_decode(curlPost($url, $array), true);

            if ($rs['errcode'] == 0){
                return result(200, "查询成功",$rs['product_list']);
            } else {
                return result(500, "查询失败");
            }
        } else {
            return result(500, "请求方式错误");
        }
    }



}