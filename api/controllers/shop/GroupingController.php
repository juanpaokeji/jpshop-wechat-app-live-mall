<?php

namespace app\controllers\shop;

use app\models\core\TableModel;
use app\models\merchant\app\AppAccessModel;
use app\models\merchant\system\ShopGroupingModel;
use app\models\shop\GoodsModel;
use app\models\shop\ShopAssembleModel;
use app\models\shop\SubOrdersModel;
use yii;
use yii\web\ShopController;

class GroupingController extends ShopController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置


    /**
     * 集团分组商品列表
     */
    public function actionGoods() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $model = new GoodsModel();
            $params['`key`'] = $params['key'];
            unset($params['key']);
            //$params['merchant_id'] = yii::$app->session['merchant_id'];
            //   $params['is_flash_sale'] = 0;
            $model->goodsOut($params); //查询商品数量是否为0  为0下架
            //type = 1 即将开始商品
            if (isset($params['type']) && $params['type'] == 1) {
                $time = time();
                $params["start_time > {$time}"] = null;
                $params["status"] = 0;
                unset($params['type']);
            } else {
                $time = time();
                $params["start_time <= {$time}"] = null;
                $params["status"] = 1;
            }

            $appModel = new AppAccessModel();
            $app = $appModel->find(['`key`' => $params['`key`']]);
//            if ($app['status'] == 200) {
//                if ($app['data']['is_recruits'] == 1) {
//                    if ($app['data']['is_recruits_show'] == 0) {
//                        $params['is_recruits'] = 0;
//                    }
//                }
//            }
            if (isset($params['supplier'])) {
                if ($params['supplier'] == 0) {
                    $params['supplier_id'] = 0;
                    unset($params['supplier']);
                }
                unset($params['supplier']);
            } else {
                $appModel = new AppAccessModel();
                $app = $appModel->find(['`key`' => 'ccvWPn', 'id' => 331]);
                if ($app['data']['is_show_supplier_goods'] == 1) {

                } else {
                    $params['supplier_id'] = 0;
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
                        if ($array['data'][$i]['sales_number'] != 0) {
                            if (count($array['data'][$i]['avatar']) < 3) {
                                $t = 3 - count($array['data'][$i]['avatar']);
                                $sql = "select avatar from shop_user ORDER BY RAND() LIMIT {$t}";
                                $table = new TableModel();
                                $rs = $table->querySql($sql);
                                for ($k = 0; $k < $t; $k++) {
                                    $array['data'][$i]['avatar'][] = $rs[$k]['avatar'];
                                }
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

    /**
     * 集团分组列表
     */
    public function actionList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            //设置类目 参数
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $model = new ShopGroupingModel();
            $params['field'] = "id,name";
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['status'] = 1;
            $array = $model->do_select($params);
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

}
