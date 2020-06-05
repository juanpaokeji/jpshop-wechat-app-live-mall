<?php

namespace app\controllers\shop;

use app\models\admin\app\AppAccessModel;
use app\models\merchant\system\UserModel;
use app\models\shop\GoodsModel;
use app\models\shop\ShopAssembleModel;
use app\models\tuan\LeaderModel;
use yii;
use yii\db\Exception;
use yii\web\ShopController;
use app\models\shop\CartModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class CartController extends ShopController
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function actionList()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new CartModel();
            $params['shop_user_cart.`key`'] = yii::$app->session['key'];
            $params['shop_user_cart.merchant_id'] = yii::$app->session['merchant_id'];
            $params['shop_user_cart.user_id'] = yii::$app->session['user_id'];
            $array = $model->queryAll($params);

            if ($array['status'] == 200) {
                for ($i = 0; $i < count($array['data']); $i++) {
                    $res = $this->flash($array['data'][$i]['goods_id']);
                    if ($res != false) {
                        $property = explode("-", $res['data']['property']);
                        for ($j = 0; $j < count($property); $j++) {
                            $a = json_decode($property[$j], true);
                            if ($a['stock_id'] == $array['data'][$i]['stock_id']) {
                                $array['data'][$i]['price'] = $a['flash_price'];
                                $array['data'][$i]['total_price'] = $a['flash_price'] * $array['data'][$i]['number'];
                            }
                        }
                    }
                }

//                $subAdminModel = new UserModel();
//                $subadmin = $subAdminModel->findAll(['`key`'=>yii::$app->session['key'],'merchant_id'=>yii::$app->session['merchant_id']]);
//                $data = array();
//                $len = count($array['data']);
//                for ($i = 0; $i<$len;$i++) {
//                    if($array['data'][$i]['supplier_id']==0){
//                        $data['0'][] = $array['data'][$i];
//                        unset($array['data'][$i]);
//                    }else{
//                        $str = $array['data'][$i]['supplier_id'];
//                        if($subadmin['status']==200){
//                            for($j=0;$j<count($subadmin['data']);$j++){
//                                $data[$str]['supplier'] = $subadmin['data'][$i];
//                            }
//                        }
//                        $data[$str][] = $array['data'][$i];
//                    }
//                }
//                $data = array_values($data);
            }
            return $array;
            //   return result(200,'请求成功',$array);
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionCart()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new CartModel();
            $params['shop_user_cart.`key`'] = yii::$app->session['key'];
            $params['shop_user_cart.merchant_id'] = yii::$app->session['merchant_id'];
            $params['shop_user_cart.user_id'] = yii::$app->session['user_id'];
            $array = $model->queryAll($params);

            if ($array['status'] == 200) {
                for ($i = 0; $i < count($array['data']); $i++) {
                    $res = $this->flash($array['data'][$i]['goods_id']);
                    if ($res != false) {
                        $property = explode("-", $res['data']['property']);
                        for ($j = 0; $j < count($property); $j++) {
                            $a = json_decode($property[$j], true);
                            if ($a['stock_id'] == $array['data'][$i]['stock_id']) {
                                $array['data'][$i]['price'] = $a['flash_price'];
                                $array['data'][$i]['total_price'] = $a['flash_price'] * $array['data'][$i]['number'];
                            }
                        }
                    }
                }

                $leaderModel = new LeaderModel();
                $leader = $leaderModel->do_select(['key' => yii::$app->session['key'], 'merchant_id' => yii::$app->session['merchant_id'], '<>' => ['supplier_id', 0]]);


                $app = new AppAccessModel();
                $apps = $app->find(['`key`' => yii::$app->session['key']]);

                $data = array();
                $len = count($array['data']);
                for ($i = 0; $i < $len; $i++) {
                    if ($array['data'][$i]['supplier_id'] == 0) {
                        $array['data'][$i]['supplier_name'] = $apps['data']['name'];
                        $data['0'][] = $array['data'][$i];
                        unset($array['data'][$i]);
                    } else {
                        $str = $array['data'][$i]['supplier_id'];
                        for ($k = 0; $k < count($leader['data']); $k++) {
                            if ($leader['data'][$k]['supplier_id'] == $str) {
                                $array['data'][$i]['supplier_name'] = $leader['data'][$k]['realname'];
                            }
                        }

                        $data[$str][] = $array['data'][$i];
                    }
                }

                $data = array_values($data);
                return result(200, '请求成功', $data);
            } else if ($array['status'] == 204) {
                return $array;
            } else {
                return result(500, '请求失败');
            }

        } else {
            return result(500, "请求方式错误");
        }
    }


    //秒杀商品属性
    public function flash($goods_id)
    {
        $time = time();
        $sql = "SELECT * FROM `shop_flash_sale_group` where FIND_IN_SET({$goods_id},goods_ids) and start_time <={$time} and end_time >={$time} and delete_time is null;";
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

    public function actionAdd()
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new CartModel();

            $must = ['goods_id', 'stock_id', 'number'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            //设置类目 参数
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['user_id'] = yii::$app->session['user_id'];

            $stockModel = new \app\models\shop\StockModel();
            $stock = $stockModel->find(['`key`' => yii::$app->session['key'], 'merchant_id' => yii::$app->session['merchant_id'], 'goods_id' => $params['goods_id'], 'id' => $params['stock_id']]);
            if ($stock['status'] != 200) {
                return $stock;
            }
            $goodsModel = new GoodsModel();
            $goodsinfo = $goodsModel->find(['id' => $params['goods_id']]);

            if ($goodsinfo['status'] == 200 && $goodsinfo['data']['is_cart'] == 0){
                return result(500, "此商品不能添加购物车！");
            }

            //检测当前商品是否是拼团商品
            if ($goodsinfo['status'] == 200 && $goodsinfo['data']['is_open_assemble'] == 1) {
                $groupModel = new ShopAssembleModel();
                $where['key'] = yii::$app->session['key'];
                $where['merchant_id'] = yii::$app->session['merchant_id'];
                $where['goods_id'] = $params['goods_id'];
                $where['status'] = 1;
                $groupInfo = $groupModel->one($where);
                if ($groupInfo['status'] == 200) {
                    return result(500, "此商品是拼团商品不能添加购物车呦！");
                }
            }
            //检测商品是否是服务商品、下架商品不能加入购物车
            if ($goodsinfo['status'] == 200 && ($goodsinfo['data']['type'] == 3 || $goodsinfo['data']['status'] == 0)) {
                return result(500, "此商品是服务商品不能添加购物车呦！");
            }
            if($goodsinfo['status']==200&&$goodsinfo['data']['is_cart'] == 0){
                return result(500, "此商品不能添加购物车呦！");
            }
            $params['total_price'] = $stock['data']['price'] * $params['number'];
            $params['pic_url'] = $stock['data']['pic_url'];
            $params['price'] = $stock['data']['price'];
            $params['property1_name'] = $stock['data']['property1_name'];
            $params['property2_name'] = $stock['data']['property2_name'];
            $res = $model->find($params);
            if ($res['status'] != 200) {
                if ($params['number'] > 0) {
                    $array = $model->add($params);
                } else {
                    return result(204, "请求失败");
                }
            } else {
                $params['number'] = $params['number'] + $res['data']['number'];
                $params['total_price'] = $params['total_price'] + $res['data']['total_price'];
                $params['id'] = $res['data']['id'];
                if ($params['number'] <= 0) {
                    $params['delete_time'] = time();
                    $params['number'] = 0;
                }
                $array = $model->update($params);
            }
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
            $model = new CartModel();
            $params['id'] = $id;
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['user_id'] = yii::$app->session['user_id'];
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

    public function actionDelete()
    {
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参、

            $model = new CartModel();
            $data['`key`'] = yii::$app->session['key'];
            $data['merchant_id'] = yii::$app->session['merchant_id'];
            $data['user_id'] = yii::$app->session['user_id'];
            if ($params['ids'] != "") {
                $ids = explode(",", $params['ids']);
                for ($i = 0; $i < count($ids); $i++) {
                    $data['id'] = $ids[$i];
                    $model->delete($data);
                }
            } else {
                return result(500, "参数错误");
            }
            return result(200, "请求成功");
        } else {
            return result(500, "请求方式错误");
        }
    }

}
