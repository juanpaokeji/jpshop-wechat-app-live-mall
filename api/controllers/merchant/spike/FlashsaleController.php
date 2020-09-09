<?php

namespace app\controllers\merchant\spike;

use app\models\merchant\app\AppAccessModel;
use app\models\merchant\system\OperationRecordModel;
use app\models\merchant\user\MerchantModel;
use app\models\shop\SaleGoodsModel;
use yii;
use yii\web\MerchantController;
use app\models\spike\FlashSaleModel;
use app\models\core\Base64Model;
use app\models\core\CosModel;
use app\models\spike\FlashSaleGroupModel;

class FlashsaleController extends MerchantController
{

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

    public function actionList()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数          

            if (isset($params['searchName'])) {
                if ($params['searchName'] != "") {
                    $params['name'] = ['like', "{$params['searchName']}"];
                }
                unset($params['searchName']);
            }
            if (isset($params['status'])) {
                if ($params['status'] == "") {
                    unset($params['status']);
                } else if ($params['status'] == 1) {
                    $params['status'] = 1;
                    $params['start_time'] = ['>=', time()];
                } else if ($params['status'] == 2) {
                    $params['status'] = 1;
                    $params['start_time'] = ['<=', time()];
                    $params['end_time'] = ['>=', time()];
                } else if ($params['status'] == 3) {
                    $params['status'] = 1;
                    $params['end_time'] = ['<=', time()];
                } else if ($params['status'] == 4) {
                    $params['status'] = 0;
                } else {
                    unset($params['status']);
                }
            }
            $groupModel = new FlashSaleGroupModel();
            $group = $groupModel->do_select($params);
            if ($group['status'] != 200) {
                return $group;
            }
            for ($i = 0; $i < count($group['data']); $i++) {
                $model = new FlashSaleModel();
                $array = $model->do_select(['flash_sale_group_id' => $group['data'][$i]['id'],'limit'=>100]);
                if ($array['status'] != 200) {
                    $array['data'] = array();
                }
//                if ($array['status'] == 200) {
//                    $group['data'][$i]['sale'] = $array['data'];
//                } else {
//                    $group['data'][$i]['sale'] = [];
//                }
                if ($group['data'][$i]['status'] == 0) {
                    $group['data'][$i]['state'] = 4;
                }
                if ($group['data'][$i]['status'] == 1 && $group['data'][$i]['start_time'] >= time()) {
                    $group['data'][$i]['state'] = 1;
                }
                if ($group['data'][$i]['status'] == 1 && $group['data'][$i]['end_time'] <= time()) {
                    $group['data'][$i]['state'] = 3;
                }
                if ($group['data'][$i]['status'] == 1 && $group['data'][$i]['end_time'] >= time() && $group['data'][$i]['start_time'] <= time()) {
                    $group['data'][$i]['state'] = 2;
                }
                $group['data'][$i]['start_time'] = date('Y-m-d H:i:s', $group['data'][$i]['start_time']);
                $group['data'][$i]['end_time'] = date('Y-m-d H:i:s', $group['data'][$i]['end_time']);
                $group['data'][$i]['send_time'] = date('Y-m-d H:i:s', $group['data'][$i]['send_time']);

                for ($j = 0; $j < count($array['data']); $j++) {
                    $shop_flash_saleModel = new FlashSaleModel();
                    $goods = $shop_flash_saleModel->do_one(['goods_id' => $array['data'][$j]['goods_id'], 'key' => $params['key'], 'merchant_id' => yii::$app->session['uid']]);

                    if ($goods['status'] != 200) {
                        return result(500, "系统错误！");
                    }

                    //copy_id 暂时不需要
                    //$group['data'][$i]['goods_list'][$j]['copy_id'] = $goods['data']['id'];
                    $group['data'][$i]['goods_list'][$j]['goods_id'] = $goods['data']['goods_id'];
                    $group['data'][$i]['goods_list'][$j]['pic_urls'] = $goods['data']['pic_url'];
                    $group['data'][$i]['goods_list'][$j]['name'] = $goods['data']['name'];
                    $group['data'][$i]['goods_list'][$j]['flash_number'] = $goods['data']['stocks'];
                    $group['data'][$i]['goods_list'][$j]['is_top'] = $goods['data']['is_top'];
                    $group['data'][$i]['goods_list'][$j]['line_price'] = $goods['data']['line_price'];
                    $group['data'][$i]['goods_list'][$j]['property'] = $array['data'][$j]['property'];
                    $property = explode("-", $array['data'][$j]['property']);
                    $propertys = array();
                    for ($k = 0; $k < count($property); $k++) {
                        $propertys[] = json_decode($property[$k], true);
                    }
                    $group['data'][$i]['goods_list'][$j]['property'] = $propertys;
                }
            }

            return $group;
        } else {
            return result(500, "请求方式错误");
        }
    }

//    public function actionSingle($id) {
//        if (yii::$app->request->isGet) {
//            $request = yii::$app->request; //获取 request 对象
//            $params = $request->get(); //获取地址栏参数
//            $groupModel = new FlashSaleGroupModel();
//            $params['id'] = $id;
//            $group = $groupModel->do_one($params);
//
//            $saleGoodsModel = new \app\models\shop\SaleGoodsModel();
//            $goods = $saleGoodsModel->do_select(['sale_id' => $group['data']['id'], 'key' => $params['key'], 'merchant_id' => yii::$app->session['uid']]);
//         
//
//            $group['data']['start_time'] = date('Y-m-d H:i:s', $group['data']['start_time']);
//            $group['data']['end_time'] = date('Y-m-d H:i:s', $group['data']['end_time']);
//            $group['data']['send_time'] = date('Y-m-d H:i:s', $group['data']['send_time']);
//            return $group;
//        } else {
//            return result(500, "请求方式错误");
//        }
//    }

    public function actionAdd()
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $must = ['name', 'detail_info', 'start_time', 'end_time'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $model = new FlashSaleGroupModel();

            $groupData = array(
                'name' => $params['name'],
                'detail_info' => $params['detail_info'],
                'start_time' => strtotime($params['start_time']),
                'end_time' => strtotime($params['end_time']),
                'key' => $params['key'],
                'merchant_id' => yii::$app->session['uid'],
                'send_time' => strtotime($params['send_time']),
                'status' => 1,
            );
            $str= "";
            for ($i = 0; $i < count($params['goods_list']); $i++) {
                if ($i == 0) {
                    $str = $params['goods_list'][$i]['id'];
                } else {
                    $str = $str . "," . $params['goods_list'][$i]['id'];
                }
            }
            $groupData['goods_ids'] = $str;
            if (!isset($params['goods_list'])) {
                return result(500, "请选择商品");
            }

            $sql = "SELECT id FROM shop_goods WHERE id IN ({$str}) AND (is_open_assemble = 1 OR is_bargain = 1)";
            $res = Yii::$app->db->createCommand($sql)->queryAll();
            if (count($res) > 0){
                $goodsIdStr = "";
                for ($i = 0; $i < count($res); $i++) {
                    if ($i == 0) {
                        $goodsIdStr = $res[$i]['id'];
                    } else {
                        $goodsIdStr = $goodsIdStr . "," . $res[$i]['id'];
                    }
                }
                return result(500, "ID为{$goodsIdStr}的商品，已开启拼团或砍价活动");
            }

            try {
                $model->begin();
                $group_id = $model->do_add($groupData);
                $sql = "update shop_goods  set is_flash_sale  =1 where id in({$groupData['goods_ids']})";
                Yii::$app->db->createCommand($sql)->execute();
                if ($group_id == false) {
                    throw new yii\db\Exception('新增失败');
                }
                for ($i = 0; $i < count($params['goods_list']); $i++) {
                    $property ="";
                    for ($j = 0; $j < count($params['goods_list'][$i]['flash_number']); $j++) {
                        $stock = 0;
                        $saleModel = new FlashSaleModel();
                        $array = $params['goods_list'][$i]['flash_price'];
                        sort($array);
                        $stock = $stock + $params['goods_list'][$i]['flash_number'][$j];
                        //属性1 property1_name 属性2 property2_name 规格的秒杀数量 stocks 规格的秒杀价 flash_price
                        $str = json_encode(array(
                            'property1_name' => $params['goods_list'][$i]['property1_name'][$j],
                            'property2_name' => $params['goods_list'][$i]['property2_name'][$j],
                            'flash_original_price' => $params['goods_list'][$i]['flash_original_price'][$j],
                            'stocks' => $params['goods_list'][$i]['flash_number'][$j],
                            'flash_price' => $params['goods_list'][$i]['flash_price'][$j],
                            'stock_id' => $params['goods_list'][$i]['flash_id'][$j],
                        ), JSON_UNESCAPED_UNICODE);
                        if ($j == 0) {
                            $property = $str;
                        } else {
                            $property = $property . "-" . $str;
                        }
                    }
                    $flash_price = $array[0];
                    $data = array(
                        'key' => $params['key'],
                        'merchant_id' => yii::$app->session['uid'],
                        'name' => $params['goods_list'][$i]['name'],
                        'is_top' => $params['goods_list'][$i]['is_top'],
                        'goods_id' => $params['goods_list'][$i]['id'],
                        'flash_sale_group_id' => $group_id['data'],
                        'line_price' => $params['goods_list'][$i]['line_price'],
                        'property' => $property,
                        'pic_url' => $params['goods_list'][$i]['pic_url'],
                        'flash_price' => $flash_price,
                        'stocks' => $stock,
                        'status' => 1,
                    );
                    $sale_id = $saleModel->do_add($data);
                    if ($sale_id == false) {
                        throw new yii\db\Exception('新增失败');
                    }
                }

                if ($group_id['status'] == 200) {
                    //添加操作记录
                    $operationRecordModel = new OperationRecordModel();
                    $operationRecordData['key'] = $params['key'];
                    if (isset(yii::$app->session['sid'])) {
                        $subModel = new \app\models\merchant\system\UserModel();
                        $subInfo = $subModel->find(['id'=>yii::$app->session['sid']]);
                        if ($subInfo['status'] == 200){
                            $operationRecordData['merchant_id'] = $subInfo['data']['username'];
                        }
                    } else {
                        $merchantModle = new MerchantModel();
                        $merchantInfo = $merchantModle->find(['id'=>yii::$app->session['uid']]);
                        if ($merchantInfo['status'] == 200) {
                            $operationRecordData['merchant_id'] = $merchantInfo['data']['name'];
                        }
                    }
                    $operationRecordData['operation_type'] = '新增';
                    $operationRecordData['operation_id'] = $group_id['data'];
                    $operationRecordData['module_name'] = '秒杀';
                    $operationRecordModel->do_add($operationRecordData);
                }

                $model->commit();
                return result(200, "请求成功");
            } catch (yii\db\Exception $e) {
                $model->rollback();
                return result(500, $e->getMessage());
            }
//
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate($id)
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $must = ['name', 'detail_info', 'start_time', 'end_time'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $model = new FlashSaleGroupModel();

            $groupData = array(
                'name' => $params['name'],
                'detail_info' => $params['detail_info'],
                'start_time' => strtotime($params['start_time']),
                'end_time' => strtotime($params['end_time']),
                'key' => $params['key'],
                'merchant_id' => yii::$app->session['uid'],
                'send_time' => strtotime($params['send_time']),
                'status' => 1,
            );
            $str = "";
            for ($i = 0; $i < count($params['goods_list']); $i++) {
                if ($i == 0) {
                    $str = $params['goods_list'][$i]['id'];
                } else {
                    $str = $str . "," . $params['goods_list'][$i]['id'];
                }
            }
            $groupData['goods_ids'] = $str;
            if (!isset($params['goods_list'])) {
                return result(500, "请选择商品");
            }

            $sql = "SELECT id FROM shop_goods WHERE id IN ({$str}) AND (is_open_assemble = 1 OR is_bargain = 1)";
            $res = Yii::$app->db->createCommand($sql)->queryAll();
            if (count($res) > 0){
                $goodsIdStr = "";
                for ($i = 0; $i < count($res); $i++) {
                    if ($i == 0) {
                        $goodsIdStr = $res[$i]['id'];
                    } else {
                        $goodsIdStr = $goodsIdStr . "," . $res[$i]['id'];
                    }
                }
                return result(500, "ID为{$goodsIdStr}的商品，已开启拼团或砍价活动");
            }

            try {
                $model->begin();

                $group = $model->do_one(['id'=>$id]);
                if ($group['status'] == 200) {
                    if ($group['data']['goods_ids'] != "") {
                        $sql = "update shop_goods set is_flash_sale = 0 where id in ({$group['data']['goods_ids'] })";
                        Yii::$app->db->createCommand($sql)->execute();
                    }
                }

                $group_id = $model->do_update(['id' => $id], $groupData);
                $saleModel = new FlashSaleModel();
                $sale_id = $saleModel->do_delete(['flash_sale_group_id' => $id]);
                $sql = "update shop_goods  set is_flash_sale  =1 where id in({$groupData['goods_ids']})";
                $res = Yii::$app->db->createCommand($sql)->execute();
                if ($group_id == false) {
                    throw new yii\db\Exception('更新失败');
                }
                for ($i = 0; $i < count($params['goods_list']); $i++) {
                    $property = "";
                    for ($j = 0; $j < count($params['goods_list'][$i]['flash_number']); $j++) {
                        $stock = 0;
                        $saleModel = new FlashSaleModel();
                        $array = $params['goods_list'][$i]['flash_price'];
                        sort($array);
                        $stock = $stock + $params['goods_list'][$i]['flash_number'][$j];
                        //属性1 property1_name 属性2 property2_name 规格的秒杀数量 stocks 规格的秒杀价 flash_price
                        $str = json_encode(array(
                            'property1_name' => $params['goods_list'][$i]['property1_name'][$j],
                            'property2_name' => $params['goods_list'][$i]['property2_name'][$j],
                            'stocks' => $params['goods_list'][$i]['flash_number'][$j],
                            'flash_price' => $params['goods_list'][$i]['flash_price'][$j],
                            'flash_original_price' => $params['goods_list'][$i]['flash_original_price'][$j],
                            'stock_id' => $params['goods_list'][$i]['flash_id'][$j],
                        ), JSON_UNESCAPED_UNICODE);
                        if ($j == 0) {
                            $property = $str;
                        } else {
                            $property = $property . "-" . $str;
                        }
                    }
                    $flash_price = $array[0];
                    $data = array(
                        'key' => $params['key'],
                        'merchant_id' => yii::$app->session['uid'],
                        'name' => $params['goods_list'][$i]['name'],
                        'is_top' => $params['goods_list'][$i]['is_top'],
                        'goods_id' => $params['goods_list'][$i]['id'],
                        'line_price' => $params['goods_list'][$i]['line_price'],
                        'flash_sale_group_id' => $id,
                        'property' => $property,
                        'pic_url' => $params['goods_list'][$i]['pic_url'],
                        'flash_price' => $flash_price,
                        'stocks' => $stock,
                        'status' => 1,
                    );
                    $sale_id = $saleModel->do_add($data);
                    if ($sale_id == false) {
                        throw new yii\db\Exception('更新失败');
                    }
                }

                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['key'];
                if (isset(yii::$app->session['sid'])) {
                    $subModel = new \app\models\merchant\system\UserModel();
                    $subInfo = $subModel->find(['id'=>yii::$app->session['sid']]);
                    if ($subInfo['status'] == 200){
                        $operationRecordData['merchant_id'] = $subInfo['data']['username'];
                    }
                } else {
                    $merchantModle = new MerchantModel();
                    $merchantInfo = $merchantModle->find(['id'=>yii::$app->session['uid']]);
                    if ($merchantInfo['status'] == 200) {
                        $operationRecordData['merchant_id'] = $merchantInfo['data']['name'];
                    }
                }
                $operationRecordData['operation_type'] = '更新';
                $operationRecordData['operation_id'] = $id;
                $operationRecordData['module_name'] = '秒杀';
                $operationRecordModel->do_add($operationRecordData);

                $model->commit();
                return result(200, "请求成功");
            } catch (yii\db\Exception $e) {
                $model->rollback();
                return result(500, $e->getMessage());
            }
//
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionDelete($id)
    {
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new FlashSaleModel();
            $groupModel = new FlashSaleGroupModel();
            $params['id'] = $id;
            $group = $groupModel->do_one(['id' => $id]);
            if ($group['status'] == 200) {

                $goods = explode(',', $group['data']['goods_ids']);
                $saleGoods = new SaleGoodsModel();
                $saleGoods->do_update(['id'=>$goods],['is_flash_sale'=>0]);
            }
            $group = $model->do_one(['id'=>$id]);
            if ($group['status'] == 200) {
                if ($group['data']['goods_ids'] != "") {
                    $sql = "update shop_goods set is_flash_sale = 0 where id in ({$group['data']['goods_ids'] })";
                    Yii::$app->db->createCommand($sql)->execute();
                }
            }
            $array = $groupModel->do_delete(['id' => $id]);
            $array = $model->do_delete(['flash_sale_group_id' => $id]);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 修改秒杀shop_flash_sale 表的状态
     * @params status
     * @params ids  json 串 ["11","12"]
     * @return array
     */
    public function actionUpdateshopflashsale()
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new FlashSaleModel();
            $ids = explode(",", $params['ids']);
            if (!is_array($ids) || !isset($params['status'])) {
                return result(404, "参数错误");
            }
            $data['status'] = $params['status'];
            $tr = Yii::$app->db->beginTransaction();
            try {
                // 修改group 表的状态
                foreach ($ids as $v) {
                    $where_sale['id'] = $v;
                    $res = $model->do_update($where_sale, $data);
                    if ($res['status'] != 200) {
                        $tr->rollBack();
                        return result(500, "请求失败1");
                    }
                }
                // 检测当前修改的shop_flash_sale 中状态status=$params['status'] 个数和flash_sale_group_id 的总数 相等则修改group
                $one_info = $model->do_one(['id' => $ids[0]]);
                $status_total_count = FlashSaleModel::find()->where(['flash_sale_group_id' => $one_info['data']['flash_sale_group_id'], 'status' => $params['status']])->count('id');
                $total_count = FlashSaleModel::find()->where(['flash_sale_group_id' => $one_info['data']['flash_sale_group_id']])->count('id');
                if ($total_count == $status_total_count) {
                    $where['id'] = $one_info['data']['flash_sale_group_id'];
                    $group_data['status'] = $params['status'];
                    $gropu_model = new FlashSaleGroupModel();
                    $array = $gropu_model->do_update($where, $group_data);
                    if ($array['status'] != 200) {
                        $tr->rollBack();
                        return result(500, "请求失败");
                    }
                    $tr->commit();
                    return result(200, "修改成功");
                }
                $tr->commit();
                return result(200, "修改成功");
            } catch (\Exception $e) {
                $tr->rollBack();
                return result(500, $e->getMessage());
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionConfiglist()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['merchant_id'] = yii::$app->session['uid'];
            $appAccessModel = new AppAccessModel();
            $appAccessInfo = $appAccessModel->find(['`key`' => $params['key'], 'merchant_id' => yii::$app->session['uid']]);
            if ($appAccessInfo['status'] == 200) {
                $array['id'] = $appAccessInfo['data']['id'];
                $array['is_open'] = $appAccessInfo['data']['spike'];
                return result(200, "请求成功", $array);
            } else {
                return $appAccessInfo;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    //秒杀开关
    public function actionConfig($id)
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $must = ['is_open', 'key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $appAccessModel = new AppAccessModel();
            $where['id'] = $id;
            $where['`key`'] = $params['key'];
            $where['merchant_id'] = yii::$app->session['uid'];
            $where['spike'] = $params['is_open'];
            $res = $appAccessModel->update($where);
            if ($res['status'] == 200) {
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['key'];
                if (isset(yii::$app->session['sid'])) {
                    $subModel = new \app\models\merchant\system\UserModel();
                    $subInfo = $subModel->find(['id'=>yii::$app->session['sid']]);
                    if ($subInfo['status'] == 200){
                        $operationRecordData['merchant_id'] = $subInfo['data']['username'];
                    }
                } else {
                    $merchantModle = new MerchantModel();
                    $merchantInfo = $merchantModle->find(['id'=>yii::$app->session['uid']]);
                    if ($merchantInfo['status'] == 200) {
                        $operationRecordData['merchant_id'] = $merchantInfo['data']['name'];
                    }
                }
                $operationRecordData['operation_type'] = '更新';
                $operationRecordData['operation_id'] = $id;
                $operationRecordData['module_name'] = '秒杀总开关';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $res;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
