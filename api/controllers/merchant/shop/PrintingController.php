<?php

namespace app\controllers\merchant\shop;

use app\models\tuan\WarehouseModel;
use Yii;
use yii\web\MerchantController;
use app\models\shop\PrintingTempModel;
use app\models\shop\OrderModel;
use app\models\admin\user\MerchantModel;

class PrintingController extends MerchantController
{
    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function actionList()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $model = new PrintingTempModel();
            $params['field'] = "shop_diy_express_template.*,system_diy_express_template.keywords_ids,system_diy_express_template.name,system_diy_express_template.english_name";
            $params['join'][] = ['inner join', 'system_diy_express_template', 'system_diy_express_template.id = shop_diy_express_template.system_express_template_id'];

            $array = $model->do_select($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionOne($id)
    {
        if (yii::$app->request->isGet) {
//            $request = yii::$app->request; //获取 request 对象
//            $params = $request->get(); //获取地址栏参数

            $model = new PrintingTempModel();
            $params['id'] = $id;
            $array = $model->do_one($params);
            if ($array['status'] == 200) {
                $systemModel = new \app\models\system\PrintingTempModel();
                $where['id'] = $array['data']['system_express_template_id'];
                $res = $systemModel->do_one($where);
                if ($res['status'] == 200) {
                    $array['data']['keywords_ids'] = $res['data']['keywords_ids'];
                } else {
                    return $res;
                }
            }
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
            $params['merchant_id'] = yii::$app->session['uid'];

            $must = ['key', 'id'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $systemModel = new \app\models\system\PrintingTempModel();
            $model = new PrintingTempModel();
            $where['id'] = $params['id'];
            $res = $systemModel->do_one($where);

            if ($res['status'] == 200) {
                $data['system_express_template_id'] = $params['id'];
                $data['key'] = $params['key'];
                $tempInfo = $model->do_select($data);
                if ($tempInfo['status'] == 200) {
                    return result(500, "模板已存在");
                }
                $params['keywrod_info'] = $res['data']['keywrod_info'];
                $params['system_express_template_id'] = $params['id'];
                $params['info'] = $res['data']['info'];
                $params['width'] = $res['data']['width'];
                $params['height'] = $res['data']['height'];
                $array = $model->do_add($params);
            } else {
                return $res;
            }

            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionDelete($id)
    {
        if (yii::$app->request->isDelete) {
//            $request = yii::$app->request; //获取 request 对象
//            $params = $request->bodyParams; //获取body传参
            $model = new PrintingTempModel();
            $params['id'] = $id;
            $array = $model->do_delete($params);
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

            $model = new PrintingTempModel();
            $where['id'] = $id;
            $array = $model->do_update($where, $params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdminlist()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $model = new \app\models\system\PrintingTempModel();
            $array = $model->do_select($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionTuanordertemp()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $must = ['key', 'id', 'order_ids'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $model = new OrderModel();
            $merchantModel = new MerchantModel();
            $tempModel = new PrintingTempModel();
            $where['field'] = "shop_diy_express_template.*,system_diy_express_template.english_name";
            $where['join'][] = ['inner join', 'system_diy_express_template', 'system_diy_express_template.id = shop_diy_express_template.system_express_template_id'];
            $where['shop_diy_express_template.system_express_template_id'] = $params['id'];
            $where['shop_diy_express_template.key'] = $params['key'];
            $res = $tempModel->do_select($where);
            if ($res['status'] == 200) {
                $keyInfo = json_decode($res['data'][0]['keywrod_info'],TRUE);
                $tempType = $res['data'][0]['english_name'];
                $html = $res['data'][0]['info'];
            } else {
                $systemModel = new \app\models\system\PrintingTempModel();
                $systemWhere['id'] = $params['id'];
                $systemInfo = $systemModel->do_one($systemWhere);
                if($systemInfo['status'] != 200){
                    return $systemInfo;
                }
                $keyInfo = json_decode($systemInfo['data']['keywrod_info'],TRUE);
                $tempType = $systemInfo['data']['english_name'];
                $html = $systemInfo['data']['info'];
            }
            $head=<<<"ETO"
<!doctype html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0"><meta http-equiv="X-UA-Compatible" content="ie=edge"><title>Document</title></head><body>
ETO;
            $html = $head.$html.'</body></html>';


            //根据订单号查询所有相关信息
            $order_ids = str_replace(",","','",$params['order_ids']);
            $key = $params['key'];
            //商户信息
            $merchantId = yii::$app->session['uid'];
            $merchantSql = "";
            $merchantSql .= "SELECT sai.user_name AS merchant_name,sai.after_phone AS merchant_phone,sai.after_addr AS merchant_addr";
            $merchantSql .= ",saa.NAME AS widget_name,saa.pic_url AS logo";
            $merchantSql .= " FROM `shop_after_info` sai";
            $merchantSql .= " LEFT JOIN system_app_access saa ON saa.merchant_id = sai.merchant_id AND saa.`key` = sai.`key`";
            $merchantSql .= " WHERE sai.merchant_id = '$merchantId' and sai.`key` = '$key'";

            //团长单表格数据,根据商品分组
            $orderSql = "";
            $orderSql .= "SELECT sog.leader_uid,sog.leader_self_uid"; //shop_order_group表字段
            $orderSql .= ",so.name as goodsname,so.goods_id,CONCAT(so.property1_name,so.property2_name) as property,sum(so.number) as number,so.price"; //shop_order表字段
            $orderSql .= ",sg.code as goods_code,sg.label,sg.short_name";//商品表字段
            $orderSql .= " FROM `shop_order_group` sog";
            $orderSql .= " LEFT JOIN `shop_order` so ON so.order_group_sn = sog.order_sn";
            $orderSql .= " LEFT JOIN `shop_goods` sg ON so.goods_id = sg.id";
            $orderSql .= " WHERE sog.order_sn IN ('$order_ids') AND sog.`key` = '$key'";
            if ($tempType != 'purchasing_order') {
                $orderSql .= " AND sog.is_tuan = 1"; //采购单不需要区分是否为团购单
            }
            $orderSql .= " GROUP BY goods_id,property";

            //团长信息
            $leaderSql = "";
            $leaderSql .= "SELECT sog.leader_self_uid,sog.leader_uid,sog.express_type";
            $leaderSql .= ",su.nickname as leader_nickname,su.phone as leader_phone,su.city as leader_city";
            $leaderSql .= ",stl.id as tuan_leader_id,stl.area_name as leader_area_name,stl.addr as leader_addr,stl.realname as leader_name";
            $leaderSql .= " FROM `shop_order_group` sog";
            $leaderSql .= " LEFT JOIN shop_user su ON su.id = sog.leader_self_uid";
            $leaderSql .= " LEFT JOIN shop_tuan_leader stl ON stl.uid = sog.leader_self_uid";
            $leaderSql .= " WHERE sog.order_sn IN ('$order_ids') AND sog.`key` = '$key' AND sog.is_tuan = 1";
            $leaderSql .= " GROUP BY sog.leader_self_uid";

            //发货单信息
            $buyerSql = "";
            $buyerSql .= "SELECT su.nickname as buyer_nickname";
            $buyerSql .= ",suc.city as buyer_city,suc.area as buyer_area";
            $buyerSql .= ",sog.name,sog.phone,sog.address,sog.order_sn,sog.payment_money,sog.remark,sog.express_type,sog.leader_uid,sog.estimated_service_time";
            $buyerSql .= ",so.name as goodsname,so.goods_id,CONCAT(so.property1_name,so.property2_name) as property,so.number,so.price";
            $buyerSql .= ",sg.code as goods_code,sg.label,sg.short_name";
            $buyerSql .= ",sp.pay_time";
            $buyerSql .= ",stl.area_name as leader_area_name,stl.addr as leader_addr,stl.realname as leader_name";
            $buyerSql .= ",shu.nickname AS leader_nickname,shu.phone AS leader_phone,shu.city AS leader_city";
            $buyerSql .= " FROM `shop_order_group` sog";
            $buyerSql .= " LEFT JOIN shop_user_contact suc ON sog.user_contact_id = suc.id";
            $buyerSql .= " LEFT JOIN shop_user su ON suc.user_id = su.id";
            $buyerSql .= " LEFT JOIN system_pay sp ON sp.order_id = sog.order_sn ";
            $buyerSql .= " LEFT JOIN shop_order so ON so.order_group_sn = sog.order_sn ";
            $buyerSql .= " LEFT JOIN shop_goods sg ON sg.id = so.goods_id";
            $buyerSql .= " LEFT JOIN shop_user shu ON shu.id = sog.leader_uid ";
            $buyerSql .= " LEFT JOIN shop_tuan_leader stl ON stl.uid = sog.leader_uid";
            $buyerSql .= " WHERE sog.order_sn IN ('$order_ids') AND sog.`key` = '$key' GROUP BY sog.order_sn";

            //发货单商品
            $goodsSql = "";
            $goodsSql .= "SELECT sog.order_sn,so.name as goodsname,so.goods_id,CONCAT(so.property1_name,so.property2_name) as property,so.number,so.price";
            $goodsSql .= ",sg.code as goods_code,sg.label,sg.short_name";
            $goodsSql .= " FROM `shop_order_group` sog";
            $goodsSql .= " LEFT JOIN shop_order so ON so.order_group_sn = sog.order_sn ";
            $goodsSql .= " LEFT JOIN shop_goods sg ON sg.id = so.goods_id";
            $goodsSql .= " WHERE sog.order_sn IN ('$order_ids') AND sog.`key` = '$key'";

            //配货单信息
            $distributionSql = "";
            $distributionSql .= "SELECT sog.leader_uid,sog.leader_self_uid"; //shop_order_group表字段
            $distributionSql .= ",so.name as goodsname,so.goods_id,CONCAT(so.property1_name,so.property2_name) as property,sum(so.number) as number,so.price"; //shop_order表字段
            $distributionSql .= ",sg.code as goods_code,sg.label,sg.short_name";//商品表字段
            $distributionSql .= " FROM `shop_order_group` sog";
            $distributionSql .= " LEFT JOIN `shop_order` so ON so.order_group_sn = sog.order_sn";
            $distributionSql .= " LEFT JOIN `shop_goods` sg ON so.goods_id = sg.id";
            $distributionSql .= " WHERE sog.order_sn IN ('$order_ids') AND sog.`key` = '$key' AND sog.is_tuan = 1";
            $distributionSql .= " GROUP BY goods_id,property,leader_self_uid";

            try{
                $orderInfo = $model->querySql($orderSql);
                $leaderInfo = $model->querySql($leaderSql);
                $merchantInfo = $model->querySql($merchantSql);
                $buyerInfo = $model->querySql($buyerSql);
                $goodsInfo = $model->querySql($goodsSql);
                $distributionInfo = $model->querySql($distributionSql);
            } catch (\Exception $e) {
                return result(500, "请求失败");
            }

            if (count($merchantInfo) > 0 && count($buyerInfo) > 0 ) {
                //团长配送方式
                foreach ($leaderInfo as $k=>$v){
                    if ($v['express_type'] === '0'){
                        $leaderInfo[$k]['express_type'] = '快递';
                    }elseif ($v['express_type'] === '1'){
                        $leaderInfo[$k]['express_type'] = '自提';
                    }elseif ($v['express_type'] === '2'){
                        $leaderInfo[$k]['express_type'] = '团长送货';
                    }
                }
                //匹配HTML中的图片
                $preg = '/<img.*?src=[\"|\']?(.*?)[\"|\']?\s.*?>/i';
                preg_match_all($preg, $html, $imgArr);
                //拼装数据
                switch ($tempType)
                {
                    case 'leader_order':  //团长单
                        if (count($orderInfo) <= 0 && count($leaderInfo) <= 0){
                            return result(500, "订单无团长");
                        }
                        foreach ($leaderInfo as $lk=>$lv){
                            foreach ($merchantInfo[0] as $mk=>$mv){
                                $leaderInfo[$lk][$mk] = $mv;
                            }
                            foreach ($orderInfo as $ok=>$ov){
                                if ($lv['leader_self_uid'] == $ov['leader_self_uid']){
                                    $leaderInfo[$lk]['table'][] = $ov;
                                }
                            }
                        }
                        $dataInfo = $leaderInfo;
                        break;
                    case 'Invoice':  //发货单
                        foreach ($buyerInfo as $key=>$val){
                            if ($val['express_type'] === '0'){
                                $buyerInfo[$key]['express_type'] = '快递';
                            }elseif ($val['express_type'] === '1'){
                                $buyerInfo[$key]['express_type'] = '自提';
                            }elseif ($val['express_type'] === '2'){
                                $buyerInfo[$key]['express_type'] = '团长送货';
                            }
                            foreach ($merchantInfo[0] as $mk=>$mv){
                                $buyerInfo[$key][$mk] = $mv;
                            }
                            foreach ($goodsInfo as $gk=>$gv){
                                if ($gv['order_sn'] == $val['order_sn']){
                                    $buyerInfo[$key]['table'][] = $gv;
                                }
                            }
                        }
                        $dataInfo = $buyerInfo;
                        break;
                    case 'purchasing_order':  //采购单
                        $dataInfo[0]['table'] = $orderInfo;
                        break;
                    case 'distribution_bill':  //配货单
                        if (count($orderInfo) <= 0 && count($leaderInfo) <= 0){
                            return result(500, "订单无团长");
                        }
                        foreach ($orderInfo as $key=>$val){
                            foreach ($distributionInfo as $dk=>$dv){
                                if ($val['goods_id'] == $dv['goods_id']){
                                    $orderInfo[$key]['temp'][] = $dv['leader_self_uid'];
                                }
                            }

                            foreach ($leaderInfo as $k=>$v){
                                if (isset($orderInfo[$key]['temp'])){
                                    foreach ($orderInfo[$key]['temp'] as $tk=>$tv){
                                        if ($v['leader_self_uid'] == $tv){
                                            $orderInfo[$key]['table'][] = $v;
                                        }
                                    }
                                }
                            }
                        }
                        $dataInfo = $orderInfo;
                        break;
                    case 'leader_route':  //团长路线单
                        if (count($leaderInfo) <= 0){
                            return result(500, "订单无团长");
                        }
                        $warehouseModel = new WarehouseModel();
                        $warehouseWhere['limit'] = false;
                        $warehouseWhere['key'] = $params['key'];
                        $warehouseWhere['status'] = 1;
                        $warehouseInfo = $warehouseModel->do_select($warehouseWhere);
                        if ($warehouseInfo['status'] != 200){
                            return result(500, "未查询到路线信息");
                        }
                        foreach ($warehouseInfo['data'] as $k=>$v){
                            if ($v['leaders'] != ''){
                                $result['route'] = $v['name'];
                                $result['merchant_name'] = $merchantInfo[0]['merchant_name'];
                                $result['merchant_phone'] = $merchantInfo[0]['merchant_phone'];
                                $result['widget_name'] = $merchantInfo[0]['widget_name'];
                                $result['logo'] = $merchantInfo[0]['logo'];
                                $result['merchant_addr'] = $merchantInfo[0]['merchant_addr'];
                                $result['table'] = [];
                                $temp = false;
                                //当订单中的团长在路线中，才打印此路线单
                                $leaderIds = json_decode($v['leaders'],true);
                                foreach ($leaderInfo as $key=>$val){
                                    foreach ($leaderIds as $lk=>$lv){
                                        if ($lv == $val['tuan_leader_id']){
                                            $result['table'][] = $val;
                                            $temp = true;
                                        }
                                    }
                                }
                                if ($temp){
                                    $dataInfo[] = $result;
                                }
                            }
                        }
                        if (!isset($dataInfo)){
                            return result(500, "订单中的团长不在任何路线内");
                        }
                        break;
                    case 'warehouse_route':  //仓库路线单
                        return result(500, "暂无仓库路线单");
                        break;
                    default:
                        return result(500, "模板类型有误");
                }
//                var_dump($dataInfo);die;
                foreach ($dataInfo as $datak=>$datav){
                    $tmpHtml = $html;
                    foreach ($keyInfo as $ki=>$kv){
                        if ($kv['type'] == 1){
                            $str = '';
                            if (isset($datav['table'])){
                                foreach ($datav['table'] as $key=>$val){
                                    $str .= '<tr>';
                                    foreach ($kv['child'] as $k=>$v){
                                        //判断数据中是否有表格字段，没有空着跳过
                                        if (array_key_exists($v['english_name'],$val)){
                                            foreach ($val as $orderkey=>$orderval){
                                                if ($v['english_name'] == $orderkey){
                                                    $str .= '<td style="padding: 10px;text-align: center;">'.$orderval;
                                                    $str .= '</td>';
                                                }
                                            }
                                        } else {
                                            $str .= '<td style="padding: 10px;text-align: center;"></td>';
                                        }
                                    }
                                    $str .= '</tr>';
                                }
                                $start = strpos($tmpHtml,"</tr>");
                                $tmpHtml = substr_replace($tmpHtml,$str,$start,0);
                            }
                        }
                        if ($kv['type'] == 0) {
                            foreach ($datav as $key=>$val){
                                foreach ($kv['child'] as $k=>$v){
                                    if ($v['english_name'] == $key){
                                        $tmpHtml = str_replace('$'.$key, $val, $tmpHtml);
                                    }
                                }
                            }
                        }
                        if ($kv['type'] == 2){
                            foreach ($datav as $key=>$val){
                                foreach ($kv['child'] as $k=>$v){
                                    if ($v['english_name'] == $key){
                                        foreach ($imgArr[0] as $imgk=>$imgv){
                                            if (stristr($imgv,$key)){
                                                $tmpHtml = str_replace($imgArr[1][$imgk], $val, $tmpHtml);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $array[] = $tmpHtml;
                }
                return result(200, "请求成功", $array);
            } else {
                return result(500, "查询失败");
            }
        } else {
            return result(500, "请求方式错误");
        }
    }



}