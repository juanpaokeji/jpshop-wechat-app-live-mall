<?php

namespace app\controllers\shop;

use yii;
use yii\web\ShopController;
use yii\db\Exception;
use app\models\spike\FlashSaleGroupModel;
use app\models\spike\FlashSaleModel;
use app\models\shop\GoodsModel;

class FlashController extends ShopController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

//    public function behaviors() {
//        return [
//            'token' => [
//                'class' => 'yii\filters\MerchantFilter', //调用过滤器
////                'only' => ['single'],//指定控制器应用到哪些动作
//                'except' => ['sms', 'register', 'password', 'all'], //指定控制器不应用到哪些动作
//            ]
//        ];
//    }
    /**
     * 秒杀活动组
     */

    public function actionGroup() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new FlashSaleGroupModel();
            $data['merchant_id'] = yii::$app->session['merchant_id'];
            $data['key'] = yii::$app->session['key'];
            $s = 24 * 3600;
            $data['<='] = ['start_time', time() + $s];
            $data['>='] = ['end_time', time()];
        
            $array = $model->do_select($data);

            if ($array['status'] != 200) {
                return $array;
            }
            for ($i = 0; $i < count($array['data']); $i++) {


                if ($array['data'][$i]['status'] == 0) {
                    $array['data'][$i]['state'] = 4;
                }
                if ($array['data'][$i]['status'] == 1 && $array['data'][$i]['start_time'] >= time()) {
                    $array['data'][$i]['state'] = 1;
                }
                if ($array['data'][$i]['status'] == 1 && $array['data'][$i]['end_time'] <= time()) {
                    $array['data'][$i]['state'] = 3;
                }
                if ($array['data'][$i]['status'] == 1 && $array['data'][$i]['end_time'] >= time() && $array['data'][$i]['start_time'] <= time()) {
                    $array['data'][$i]['state'] = 2;
                }

                $array['data'][$i]['start_time_month'] = date('m', $array['data'][$i]['start_time']);
                $array['data'][$i]['end_time_month'] = date('m', $array['data'][$i]['end_time']);
                $array['data'][$i]['start_time_day'] = date('d', $array['data'][$i]['start_time']);
                $array['data'][$i]['end_time_day'] = date('d', $array['data'][$i]['end_time']);
                $array['data'][$i]['send_time'] = date('Y-m-d H:i:s', $array['data'][$i]['send_time']);

                $array['data'][$i]['start_time2'] = date('H:i', $array['data'][$i]['start_time']);
                $array['data'][$i]['end_time2'] = date('H:i', $array['data'][$i]['end_time']);
            }

            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 秒杀活动商品列表
     */
    public function actionSingle($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $groupModel = new FlashSaleGroupModel();
            $params['id'] = $id;
            $group = $groupModel->do_one($params);


            $model = new FlashSaleModel();
            $array = $model->do_select(['flash_sale_group_id' => $group['data']['id']]);
//                if ($array['status'] == 200) {
//                    $group['data'][$i]['sale'] = $array['data'];
//                } else {
//                    $group['data'][$i]['sale'] = [];
//                }
            if ($group['data']['status'] == 0) {
                $group['data']['state'] = 4;
            }
            if ($group['data']['status'] == 1 && $group['data']['start_time'] >= time()) {
                $group['data']['state'] = 1;
            }
            if ($group['data']['status'] == 1 && $group['data']['end_time'] <= time()) {
                $group['data']['state'] = 3;
            }
            if ($group['data']['status'] == 1 && $group['data']['end_time'] >= time() && $group['data']['start_time'] <= time()) {
                $group['data']['state'] = 2;
            }
            $start_time = $group['data']['start_time'];
            $end_time = $group['data']['start_time'];
            $group['data']['start_time'] = date('Y-m-d H:i:s', $group['data']['start_time']);
            $group['data']['end_time'] = date('Y-m-d H:i:s', $group['data']['end_time']);
            $group['data']['send_time'] = date('Y-m-d H:i:s', $group['data']['send_time']);


            for ($j = 0; $j < count($array['data']); $j++) {
                $saleGoodsModel = new \app\models\shop\SaleGoodsModel();
                $goods = $saleGoodsModel->do_one(['id' => $array['data'][$j]['goods_id'], 'key' => yii::$app->session['key'], 'merchant_id' => yii::$app->session['merchant_id']]);

                //   $group['data'][$i]['sale'] = $goods['data'];
                if ($goods['status'] != 200) {
                    return result(500, "系统错误！");
                }
                $pic = explode(",", $goods['data']['pic_urls']);
                //copy_id 暂时不需要
                $group['data']['goods'][$j]['goods_id'] = $goods['data']['id'];
                $group['data']['goods'][$j]['pic_urls'] = $pic[0];
                $group['data']['goods'][$j]['name'] = $goods['data']['name'];
                $group['data']['goods'][$j]['short_name'] = $goods['data']['short_name'];
                $group['data']['goods'][$j]['price'] = $goods['data']['line_price'];
                $group['data']['goods'][$j]['stocks'] = 0;
                $group['data']['goods'][$j]['is_top'] = $goods['data']['is_top'];
                $group['data']['goods'][$j]['property'] = $array['data'][$j]['property'];
                $property = explode("-", $array['data'][$j]['property']);
                $propertys = array();
                for ($k = 0; $k < count($property); $k++) {
                    $a = json_decode($property[$k], true);
                    $propertys[$k] = $a;
                    $group['data']['goods'][$j]['property'] = $propertys;
                    $group['data']['goods'][$j]['stocks'] = $group['data']['goods'][$j]['stocks'] + $a['stocks'];
                    $group['data']['goods'][$j]['price'] = $a['flash_price'];
                }
                $sql = "select sum(number)as number  from shop_order where goods_id = " . $array['data'][$j]['goods_id'] . " and  create_time >= {$start_time} and  create_time <= {$end_time} ";
                $number = yii::$app->db->createCommand($sql)->execute();
                $group['data']['goods'][$j]['sold'] = $number['number'] == null ? 0 : $number['number'];
                if ($group['data']['goods'][$j]['sold'] == 0) {
                    $group['data']['goods'][$j]['percentage'] = 100;
                } else {
                    if ($goods['data'][$j]['number'] - $goods['data'][$j]['sold'] == 0) {
                        $group['data']['goods'][$j]['percentage'] = 0;
                    } else {
                        $group['data']['goods'][$j]['percentage'] = floor((($array['data'][$j]['stocks'] - $goods['data'][$j]['sold']) / $goods['data'][$j]['number']) * 100);
                    }
                }
            }

//  
//            $group['data']['goods'] = $goods['data'];
//            $group['data']['start_time'] = date('m-d ', $group['data']['start_time']);
//            $group['data']['end_time'] = date('m-d', $group['data']['end_time']);
//            $group['data']['send_time'] = date('Y-m-d H:i:s', $group['data']['send_time']);
            return $group;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 秒杀活动商品列表
     */
    public function actionGoods($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数


            $saleGoodsModel = new \app\models\shop\SaleGoodsModel();
            $goods = $saleGoodsModel->do_select(['flash_id' => $id, 'is_top' => 1, 'key' => yii::$app->session['key'], 'merchant_id' => yii::$app->session['merchant_id'], 'field' => 'id,pic_urls,name,short_name,price,line_price,stocks']);

            //   $group['data'][$i]['sale'] = $goods['data'];
            if ($goods['status'] != 200) {
                return $goods;
            }
            for ($j = 0; $j < count($goods['data']); $j++) {
                $pic = explode(",", $goods['data'][$j]['pic_urls']);
                $goods['data'][$j]['pic_url'] = $pic[0];
                unset($goods['data'][$j]['pic_urls']);
            }
            return $goods;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $model = new FlashSaleGroupModel();
            $data['merchant_id'] = yii::$app->session['merchant_id'];
            $data['key'] = yii::$app->session['key'];
            $data['<='] = ['start_time', time()];
            $data['>='] = ['end_time', time()];
            $array = $model->do_select($data);
            if ($data['status'] == 200) {


                for ($i = 0; $i < count($array['data']); $i++) {
                    $id[$i] = $array['data'][$i]['id'];
                }

                $saleGoodsModel = new \app\models\shop\SaleGoodsModel();

                $goods = $saleGoodsModel->do_select(['in' => ['flash_id', $id], 'is_top' => 1, 'key' => yii::$app->session['key'], 'merchant_id' => yii::$app->session['merchant_id'], 'field' => 'id,pic_urls,name,short_name,price,line_price,stocks']);

                //   $group['data'][$i]['sale'] = $goods['data'];
                if ($goods['status'] != 200) {
                    return $goods;
                }
                for ($j = 0; $j < count($goods['data']); $j++) {
                    $pic = explode(",", $goods['data'][$j]['pic_urls']);
                    $goods['data'][$j]['pic_url'] = $pic[0];
                    unset($goods['data'][$j]['pic_urls']);
                }
                return $goods;
            } else {
                return $data;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionConfig() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new ConfigModel();
            $data['merchant_id'] = yii::$app->session['uid'];
            $data['key'] = $params['key'];
            $array = $model->do_one($data);

            $must = ['is_open', 'open_time', 'close_time', 'close_pic_url', 'is_express', 'is_site', 'is_tuan_express', 'min_withdraw_money', 'withdraw_fee_ratio', 'commission_leader_ratio', 'commission_user_ratio', 'leader_range'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }


            if ($array['status'] == 204) {
                $params['merchant_id'] = yii::$app->session['uid'];
                $array = $model->do_add($params);
            } else if ($array['status'] == 200) {
                $where['id'] = $array['data']['id'];
                $where['merchant_id'] = yii::$app->session['uid'];
                $where['key'] = $params['key'];
                $array = $model->do_update($where, $params);
            } else {
                return result(500, "请求失败");
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
