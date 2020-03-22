<?php

namespace app\controllers\shop;


use app\models\admin\app\AppAccessModel;
use app\models\admin\app\AppModel;
use app\models\core\TableModel;
use app\models\merchant\system\BargainModel;
use app\models\shop\GoodsModel;
use app\models\shop\OrderModel;
use app\models\shop\SaleGoodsModel;
use app\models\shop\ShopBargainInfoModel;
use app\models\shop\StockModel;
use yii;
use yii\db\Exception;
use yii\web\ShopController;
use app\models\shop\ScoreModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class BargainController extends ShopController
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors() {
        return [
            'token' => [
                'class' => 'yii\filters\ShopFilter', //调用过滤器
//                'only' => ['single'],//指定控制器应用到哪些动作
                'except' => ['goods'], //指定控制器不应用到哪些动作
            ]
        ];
    }

    public function actionList()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new ShopBargainInfoModel();
            $params['shop_bargain_info.key'] = yii::$app->session['key'];
            unset($params['key']);
            $params['shop_bargain_info.merchant_id'] = yii::$app->session['merchant_id'];
            $params['shop_bargain_info.user_id'] = yii::$app->session['user_id'];
            $params['is_promoter'] = 1;
            $params['join'][] = ['inner join', 'shop_goods', 'shop_goods.id=shop_bargain_info.goods_id'];
            $params['join'][] = ['inner join', 'shop_stock', 'shop_stock.id=shop_bargain_info.stock_id'];
            $params['field'] = "shop_bargain_info.id,shop_goods.name,shop_stock.property1_name,property2_name,shop_bargain_info.goods_id as goods_id,stock_id,shop_stock.pic_url,bargain_price,promoter_user_id,promoter_sn,shop_bargain_info.create_time,shop_bargain_info.end_time,shop_bargain_info.status";

            $array = $model->do_select($params);

            if ($array['status'] == 200) {
                for ($i = 0; $i < count($array['data']); $i++) {
                    $model = new ShopBargainInfoModel();
                    $one = $model->do_one(['orderby' => 'id desc', 'promoter_user_id' => $array['data'][$i]['promoter_user_id'], 'promoter_sn' => $array['data'][$i]['promoter_sn']]);

                    $array['data'][$i]['goods_price'] = sprintf("%.2f", $one['data']['goods_price']);
                    if (time() > $array['data'][$i]['end_time']) {
                        $array['data'][$i]['time'] = date('H:i:s', $array['data'][$i]['end_time'] - time());
                    } else {
                        $array['data'][$i]['time'] = "";
                    }

                }
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

            $goodsModel= new SaleGoodsModel();
            $params['is_bargain'] =1;
            $goods = $goodsModel->do_select($params);
            $tableModel = new TableModel();
            for($i=0;$i<count($goods['data']);$i++){
                $goods['data'][$i]['pic_urls'] =  explode(",",substr($goods['data'][$i]['pic_urls'],0,strlen($goods['data'][$i]['pic_urls'])-1));
                $goods['data'][$i]['format_bargain_start_time'] =date('Y-m-d H:i:s', $goods['data'][$i]['bargain_start_time']);
                $goods['data'][$i]['format_bargain_end_time'] =date('Y-m-d H:i:s', $goods['data'][$i]['bargain_end_time']);
                $sql = "select avatar  from shop_order inner join  shop_order_group on  shop_order_group.order_sn = shop_order.order_group_sn inner join shop_user on shop_order_group.user_id = shop_user.id where goods_id = {$goods['data'][$i]['id']} and is_bargain = 1";
                $res = $tableModel->querySql($sql);

                if(count($res)==0){
                    $sql1 = "select avatar  from shop_user order by rand()  limit 0,3";
                    $res1 = $tableModel->querySql($sql1);
                    $goods['data'][$i]['avatar'][] =$res1[0]['avatar'];
                    $goods['data'][$i]['avatar'][] =$res1[1]['avatar'];
                    $goods['data'][$i]['avatar'][] =$res1[2]['avatar'];
                }else{

                    $goods['data'][$i]['avatar'][] =$res[0]['avatar'];
                    $goods['data'][$i]['avatar'][] =isset($res[1])?$res[1]['avatar']:"";
                    $goods['data'][$i]['avatar'][] =isset($res[2])?$res[1]['avatar']:"";
                }
            }

            $appModel = new AppAccessModel();
            $app = $appModel->find(['`key`' => $params['key']]);//bargain_rotation

            if($goods['status']==200){
                $array['status']=200;
                $array['message']="请求成功";
                $array['data']['goods'] = $goods['data'];
                $array['data']['pic_url'] = explode(",",substr($app['data']['bargain_rotation'],0,strlen($app['data']['bargain_rotation'])-1));
            }
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
            $model = new ShopBargainInfoModel();
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['promoter_user_id'] = yii::$app->session['user_id'];
            $params['is_promoter'] = 1;
            $params['id'] = $id;
//            $array = $model->do_one($params);//非发起人查询不到数据
            $array = $model->do_one(['id'=>$id]);

            $appModel = new AppAccessModel();
            $app = $appModel->find(['`key`' => $params['`key`']]);


            $goodsModel = new GoodsModel();

            $goods = $goodsModel->find(['id' => $array['data']['goods_id']]);

            $stockModel = new StockModel();

            $stock = $stockModel->find(['id' => $array['data']['stock_id']]);


            $sql = "select count(id) as num from shop_order where merchant_id = {$params['merchant_id']} and `key`  ='{$params['`key`']}'";
            $num = $stockModel->querySql($sql);

            $sql = "select avatar  from shop_user where id = {$params['promoter_user_id']}";
            $avatar = $stockModel->querySql($sql);


            $bargin = $model->do_one(['orderby' => 'id desc', 'goods_id' => $array['data']['goods_id']]);

            $data['field'] = "avatar,nickname,price";
            $data['join'][] = ['inner join', 'shop_user', 'shop_user.id=shop_bargain_info.user_id'];
            $data['is_promoter'] = 0;
            $data['goods_id'] = $array['data']['goods_id'];
            $data['shop_bargain_info.merchant_id'] = $array['data']['merchant_id'];
            $data['shop_bargain_info.key'] = $array['data']['key'];
            $list = $model->do_select($data);

            $res['app_name'] = $app['data']['name'];
            $res['avatar'] = $avatar[0]['avatar'];
            $res['bargain_poster'] = $app['data']['bargain_poster'];
            $res['goods_name'] = $goods['data']['name'];
            $res['format_bargain_start_time'] =date('Y-m-d H:i:s', $goods['data']['bargain_start_time']);
            $res['format_bargain_end_time'] =date('Y-m-d H:i:s', $goods['data']['bargain_end_time']);
            $res['pic_url'] = $stock['data']['pic_url'];
            $res['number'] = $goods['data']['fictitious_help_bargain'] + $num[0]['num'];
            $res['stock_id'] = $stock['data']['id'];
            $res['property1_name'] = $stock['data']['property1_name'];
            $res['property2_name'] = $stock['data']['property2_name'];
            $res['price'] = $bargin['data']['goods_price'];
            $res['min_pirce'] = $goods['data']['bargain_price'];
            $res['cost_price'] = $array['data']['goods_price'];
            $res['end_time'] = $array['data']['end_time'];
            $res['goods_id'] = $array['data']['goods_id'];
            if ($list['status'] == 200) {
                $res['bargin'] = $list['data'];
            } else {
                $res['bargin'] = array();
            }
            return result(200, '请求成功', $res);
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd()
    {

        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new ShopBargainInfoModel();
            //设置类目 参数
//            $must = ['name'];
//            $rs = $this->checkInput($must, $params);
//            if ($rs != false) {
//                return $rs;
//            }
            $data['key'] = yii::$app->session['key'];
            $data['merchant_id'] = yii::$app->session['merchant_id'];
            $data['promoter_user_id'] = yii::$app->session['user_id'];
            $data['is_promoter'] = 1;
            $data['goods_id'] = $params['goods_id'];
            $data['status'] = 1;
            $array = $model->do_one($data);
            if ($array['status'] == 200) {
                return result(200, '请求成功', $array['data']['id']);
            }
            if ($array['status'] == 500) {
                return result(500, '内部错误');
            }
            $stockModel = new StockModel();
            $stock = $stockModel->find(['id' => $params['stock_id'], 'goods_id' => $params['goods_id']]);

            if ($stock['status'] == 204) {
                return result(500, '内部错误');
            }
            if ($stock['status'] == 500) {
                return result(500, '内部错误');
            }

            $goodsModel = new SaleGoodsModel();
            $goods = $goodsModel->do_one(['id' => $data['goods_id']]);

            if($goods['data']['bargain_start_time']>time()){
                return result(500, '砍价活动未开始');
            }
            if($goods['data']['bargain_end_time']<time()){
                return result(500, '砍价活动已结束');
            }

            $data['user_id'] = yii::$app->session['user_id'];
            $data['promoter_sn'] = order_sn();
            $data['price'] = 0;
            $data['stock_id'] = $params['stock_id'];
            $data['goods_price'] = $stock['data']['price'];
            $data['end_time'] = time() + $goods['data']['bargain_limit_time'] * 3600;
            $data['status'] = 1;
            $array = $model->do_add($data);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }


    public function actionBargain($id)
    {

        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new ShopBargainInfoModel();

            $data['key'] = yii::$app->session['key'];
            $data['merchant_id'] = yii::$app->session['merchant_id'];
            $data['user_id'] = yii::$app->session['user_id'];
            $params['id'] = $id;
            //砍价发起信息
            $info = $model->do_one(['id' => $params['id'], 'merchant_id' => $data['merchant_id'], 'key' => $data['key']]);

            if ($info['status'] == 204 || $info['status'] == 500) {
                return $info;
            }
            if ($info['data']['status'] == 0) {
                return result(500, "砍价已结束");
            }
            if ($info['data']['end_time'] <= time()) {
                return result(500, "已超过砍价时间");
            }

            $data['goods_id'] = $info['data']['goods_id'];
            $data['is_promoter'] = 0;
            //当前用户该商品砍了几次
            $list = $model->do_select($data);

            //查询最后一次砍价
            $one = $model->do_one(['orderby' => 'id desc', 'promoter_user_id' => $info['data']['promoter_user_id'], 'promoter_sn' => $info['data']['promoter_sn']]);

            $a = $model->do_one(['user_id' => $data['user_id'], 'is_promoter' => 0, 'promoter_user_id' => $info['data']['promoter_user_id'], 'promoter_sn' => $info['data']['promoter_sn']]);
            if ($a['status'] == 200) {
                return result(500, "您已经砍过价格", $a['data']['price']);
            }
            //查询商品砍价信息
            $goodsModel = new SaleGoodsModel();
            $goods = $goodsModel->do_one(['id' => $info['data']['goods_id']]);

            if ($list['status'] == 204 || count($list['data']) < $goods['data']['help_number']) {
                //{"bargain_price":["20","5","30"],"bargain_min":["3","1","2"],"bargain_max":["5","2","4"]}
                $json = json_decode($goods['data']['bargain_rule'], true);
                for ($i = 0; $i < count($json['bargain_price']); $i++) {
                    // 第二层为从$i+1的地方循环到数组最后
                    for ($j = $i + 1; $j < count($json['bargain_price']); $j++) {
                        // 比较数组中两个相邻值的大小
                        if ($json['bargain_price'][$i] < $json['bargain_price'][$j]) {
                            $tem = $json['bargain_price'][$i]; // 这里临时变量，存贮$i的值
                            $json['bargain_price'][$i] = $json['bargain_price'][$j]; // 第一次更换位置
                            $json['bargain_price'][$j] = $tem; // 完成位置互换

                            $tem1 = $json['bargain_min'][$i]; // 这里临时变量，存贮$i的值
                            $json['bargain_min'][$i] = $json['bargain_min'][$j]; // 第一次更换位置
                            $json['bargain_min'][$j] = $tem1; // 完成位置互换

                            $tem2 = $json['bargain_max'][$i]; // 这里临时变量，存贮$i的值
                            $json['bargain_max'][$i] = $json['bargain_max'][$j]; // 第一次更换位置
                            $json['bargain_max'][$j] = $tem2; // 完成位置互换
                        }
                    }
                }
                $num = 0;

                if ($goods['data']['bargain_price'] >= $one['data']['goods_price']) {

                    return result(500, "已看到最低价格");
                }

                for ($i = 0; $i < count($json['bargain_price']); $i++) {
                    if ($json['bargain_price'][$i] <= $one['data']['goods_price']) {

                        $num = sprintf("%.2f", rand($json['bargain_min'][$i], $json['bargain_max'][$i]));
                        if ($num != $json['bargain_max'][$i]) {
                            $num = sprintf("%.2f", $num + lcg_value());
                        }
                        break;
                    }
                }
                $data['price'] = $num;
                $data['promoter_sn'] = $info['data']['promoter_sn'];
                $data['promoter_user_id'] = $info['data']['promoter_user_id'];
                $data['goods_price'] = $one['data']['goods_price'] - $num;
                $data['status'] = 1;
                $array = $model->do_add($data);
                if ($array['status'] == 200) {
                    return result(200, "请求成功", $data['price']);
                }
                return $array;
            } else {
                return result(500, "你超过该商品砍价次数");
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate($id)
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new ShopBargainInfoModel();
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

    public function actionDelete($id)
    {
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new ShopBargainInfoModel();
            $params['id'] = $id;
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['user_id'] = yii::$app->session['user_id'];
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

}
