<?php

namespace app\controllers\merchant\shop;

use app\models\core\Base64Model;
use app\models\shop\GoodsModel;
use app\models\shop\ShopAssembleModel;
use app\models\shop\StockModel;
use Yii;
use app\models\shop\AssembleRecordModel;
use app\models\shop\ShopAssembleAccessModel;
use app\controllers\merchant\design\MaterialController;
use yii\web\Response;

class AssembleController extends MaterialController{
    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function actionGoods()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $goodsModel = new GoodsModel();
            if (isset($params['searchName'])) {
                $goodsWhere['searchName'] = $params['searchName'];
            }
            $goodsWhere['fields'] = "id,name,pic_urls,status,m_category_id,create_time,update_time";
            $goodsWhere['`key`'] = $params['key'];
            $goodsWhere['merchant_id'] = yii::$app->session['uid'];
            $goodsWhere['delete_time'] = 1;
            $array = $goodsModel->findall($goodsWhere);
            if ($array['status'] == 200){
                //将已设置过拼团信息的商品排除
                $assembleModel = new ShopAssembleModel();
                $assembleWhere = array(
                    'key'=>$params['key'],
                    'merchant_id'=>yii::$app->session['uid'],
                    'limit'=>false
                );
                $assembleInfo = $assembleModel->do_select($assembleWhere);
                if ($assembleInfo['status'] == 200){
                    foreach ($array['data'] as $k=>$v){
                        foreach ($assembleInfo['data'] as $key=>$val){
                            if ($v['id'] == $val['goods_id']){
                                unset($array['data'][$k]);
                                $array['count'] = $array['count'] - 1;
                            }
                        }
                    }
                    array_multisort($array['data']);
                }
                //查询各规格信息
                $stockModel = new StockModel();
                $stockWhere = array(
                    '`key`'=>$params['key'],
                    'merchant_id'=>yii::$app->session['uid'],
                );
                $stockInfo = $stockModel->findall($stockWhere);
                if ($stockInfo['status'] != 200){
                    return result(500, "各规格库存信息有误");
                }
                foreach ($array['data'] as $k=>$v){
                    foreach ($stockInfo['data'] as $key=>$val){
                        if ($v['id'] == $val['goods_id']){
                            $array['data'][$k]['stock'][] = $val;
                        }
                    }
                }
            }

            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionOrder()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $model = new ShopAssembleAccessModel();
            if (isset($params['searchName'])) {
                if ($params['searchName'] != "") {
                    $where['or'] = ['or',['like', 'shop_user.nickname', $params['searchName']],['like', 'shop_order_group.goodsname', $params['searchName']]];
                }
            }
            $where['limit'] = $params['limit'];
            $where['page'] = $params['page'];
            $where['shop_assemble_access.key'] = $params['key'];
            $where['shop_assemble_access.merchant_id'] = yii::$app->session['uid'];
            $where['field'] = "shop_user.nickname,shop_assemble_access.id,shop_assemble_access.key,shop_assemble_access.order_sn,shop_assemble_access.type,shop_assemble_access.expire_time,shop_order_group.goodsname,shop_order_group.status as assemble_status,shop_order_group.create_time";
            $where['join'][] = ['left join', 'shop_order_group', 'shop_assemble_access.order_sn = shop_order_group.order_sn'];
            $where['join'][] = ['left join', 'shop_user', 'shop_assemble_access.uid = shop_user.id'];

            $array = $model->do_select($where);

            if ($array['status'] == 200){
                foreach ($array['data'] as $k=>$v){
                    $array['data'][$k]['format_expire_time'] = date( "Y-m-d H:i:s",$v['expire_time']);
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAssemble()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            if (isset($params['searchName'])) {
                if ($params['searchName'] != "") {
                    $where['or'] = ['or',['like', 'shop_user.nickname', $params['searchName']],['like', 'shop_order_group.goodsname', $params['searchName']]];
                }
            }

            $model = new ShopAssembleAccessModel();
            $where['limit'] = $params['limit'];
            $where['page'] = $params['page'];
            $where['shop_assemble_access.key'] = $params['key'];
            $where['shop_assemble_access.merchant_id'] = yii::$app->session['uid'];
            $where['field'] = "shop_user.nickname,shop_assemble_access.id,shop_assemble_access.key,shop_assemble_access.order_sn,shop_assemble_access.type,shop_assemble_access.expire_time,shop_assemble_access.number,shop_order_group.goodsname,shop_order_group.status as assemble_status,shop_order_group.create_time";
            $where['join'][] = ['left join', 'shop_order_group', 'shop_assemble_access.order_sn = shop_order_group.order_sn'];
            $where['join'][] = ['left join', 'shop_user', 'shop_assemble_access.uid = shop_user.id'];
            $where['shop_assemble_access.leader_id'] = 0;
            $where['shop_assemble_access.is_leader'] = 1;

            $array = $model->do_select($where);

            $sonWhere['limit'] = false; //需要查询全部，不传值默认查10条
            $sonWhere['page'] = 1;
            $sonWhere['shop_assemble_access.key'] = $params['key'];
            $sonWhere['shop_assemble_access.merchant_id'] = yii::$app->session['uid'];
            $sonWhere['field'] = "shop_assemble_access.id,shop_assemble_access.leader_id,shop_order_group.status as assemble_status";
            $sonWhere['join'][] = ['left join', 'shop_order_group', 'shop_assemble_access.order_sn = shop_order_group.order_sn'];
            $sonWhere['join'][] = ['left join', 'shop_user', 'shop_assemble_access.uid = shop_user.id'];
            $sonWhere['<>'] = ['shop_assemble_access.leader_id',0];
            $sonWhere['shop_assemble_access.is_leader'] = 0;

            $res = $model->do_select($sonWhere);

            if ($array['status'] == 200 && $res['status'] == 200){
                foreach ($array['data'] as $key=>$val){
                    if ($val['assemble_status'] != '2' && $val['assemble_status'] != '4' && $val['assemble_status'] != '5' && $val['assemble_status'] != '9' && $val['assemble_status'] != '8'){
                        $son_num = 1;
                    } else {
                        $son_num = 0;
                    }
                    foreach ($res['data'] as $k=>$v){
                        if ($val['id'] == $v['leader_id'] && $v['assemble_status'] != '2' && $v['assemble_status'] != '4' && $v['assemble_status'] != '5' && $v['assemble_status'] != '9' && $v['assemble_status'] != '8'){
                            $son_num++;
                        }
                    }
                    $array['data'][$key]['son_num'] = $son_num;
                }
            }
            return $array;

        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAssembleone($id)
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $model = new ShopAssembleAccessModel();

            $where['shop_assemble_access.key'] = $params['key'];
            $where['shop_assemble_access.merchant_id'] = yii::$app->session['uid'];
            $where['field'] = "shop_user.nickname,shop_user_contact.name,shop_user_contact.phone,shop_order_group.status as assemble_status,shop_order_group.create_time";
            $where['join'][] = ['left join', 'shop_order_group', 'shop_assemble_access.order_sn = shop_order_group.order_sn'];
            $where['join'][] = ['left join', 'shop_user', 'shop_assemble_access.uid = shop_user.id'];
            $where['join'][] = ['left join', 'shop_user_contact', 'shop_user_contact.user_id = shop_user.id'];
            $where['or'] = ['or',['=', 'shop_assemble_access.id', $id],['=', 'shop_assemble_access.leader_id', $id]];
            $where['orderby'] = "create_time asc";
            $array = $model->do_select($where);

            if ($array['status'] == 200){
                foreach ($array['data'] as $k=>$v){
                    if ($v['assemble_status'] == '2' || $v['assemble_status'] == '4' || $v['assemble_status'] == '5' || $v['assemble_status'] == '9' || $v['assemble_status'] == '8'){
                        unset($array['data'][$k]);
                        $array['count']--;
                    }
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

            $must = ['key'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $assembleModel = new ShopAssembleModel();
            $assembleWhere['key'] = $params['key'];
            $assembleWhere['merchant_id'] = yii::$app->session['uid'];
            if (isset($params['limit'])){
                $assembleWhere['limit'] = $params['limit'];
                $assembleWhere['page'] = $params['page'];
            }
            $assembleInfo = $assembleModel->do_select($assembleWhere);


        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate($id){
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $must = ['key','stock'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $stockModel = new StockModel();
            $delData['goods_id'] = $params['id'];
            $stockModel->del($delData);
            //循环处理规格数据
            foreach ($params['stock'] as $k=>$v){
                //规格表数据保存
                $data['`key`'] = $params['key'];
                $data['merchant_id'] = yii::$app->session['uid'];
                $data['storehouse_id'] =  $v['storehouse_id'];
                $data['goods_id'] = $v['goods_id'];
                $data['property1_name'] = $v['property1_name'];
                $data['property2_name'] = $v['property2_name'];
                $data['name'] = $v['name'];
                $data['code'] = $v['code'];
                $data['weight'] = $v['weight'];
                $data['number'] = $v['number'];
                $data['price'] = $v['price'];
                $data['cost_price'] = $v['cost_price'];
                $data['assemble_price'] = $v['assemble_price'];
                $data['storehouse_number'] = $v['storehouse_number'];
                $data['outbound_number'] = $v['outbound_number'];
                $data['incoming_number'] = $v['incoming_number'];
                $data['pic_url'] = $v['pic_url'];
                $data['status'] = 1;
                $stockModel->add($data);
                //组装拼团表所需的数据
                $stock['pic_url'][] = $v['pic_url'];
                $stock['property1_name'][] = $v['property1_name'];
                $stock['property2_name'][] = $v['property2_name'];
                $stock['price'][] = $v['price'];
                $stock['weight'][] = $v['weight'];
                $stock['number'][] = $v['number'];
                $stock['code'][] = $v['code'];
                $stock['cost_price'][] = $v['cost_price'];
                $stock['assemble_price'][] = $v['assemble_price'];
                $stock['assemble_price'][] = $v['assemble_price'];
            }
            $params['stock'] = $stock;
            $params['assemble_price'] = min($stock['assemble_price']);


            //添加拼团配置
            $groupModel = new ShopAssembleModel();
            $groupInfo = $groupModel->one(['goods_id' => $id, 'key' => $params['`key`']]);
            $group_number = max($params['assemble_number']); //计算拼团人数
            $new_group_arr = [];
            $assemble_price = $params['stock']['assemble_price'];
            foreach ($params['assemble_number'] as $ass_key => $ass_number) {
                if (empty($ass_number)) {
                    return result(500, "平团人数错误");
                }
                foreach ($assemble_price as $price_key => $price_val) {
                    $new_group_arr[$ass_number][$price_key]['property1_name'] = $params['stock']['property1_name'][$price_key];
                    $new_group_arr[$ass_number][$price_key]['property2_name'] = $params['stock']['property2_name'][$price_key];
                    $new_group_arr[$ass_number][$price_key]['price'] = $price_val;
                    if ($params['is_leader_discount']) {
                        $new_group_arr[$ass_number][$price_key]['tuan_price'] = $params['assemble_group_discount'][$ass_key];
                    } else {
                        $new_group_arr[$ass_number][$price_key]['tuan_price'] = 0;
                    }
                    if (isset($params['group_price_discount']) && $params['group_price_discount']) {
                        $new_group_arr[$ass_number][$price_key]['price'] = bcmul($params['group_price_discount'][$ass_key] / 100, $price_val, 2);
                    }
                }
            }

            $group = array(
                'key'=>$params['key'],
                'merchant_id'=>yii::$app->session['uid'],
                'goods_id'=>$id,
                'is_self'=>$params['is_self'] ?? 0,
                'older_with_newer'=>$params['older_with_newer'] ?? 0,
                'is_automatic'=>$params['is_automatic'] ?? 0,
                'is_leader_discount'=>$params['is_leader_discount'] ?? 0,
                'type'=>$params['tuan_type'] ?? 0,
                'number'=>$group_number ?? 0,
                'property'=>json_encode($new_group_arr),
                'min_price'=>$params['assemble_price'] ?? 0,
                'group_price_discount'=>isset($params['group_price_discount']) ? json_encode($params['group_price_discount']) : '',
                'is_show'=>$params['is_show'] ?? 0,
                'status'=>1,
            );

            if ($groupInfo['status'] == 200) {
                $array = $groupModel->do_update(['id' => $id], $group);
            } else {
                $array = $groupModel->add($group);
            }

            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }


}