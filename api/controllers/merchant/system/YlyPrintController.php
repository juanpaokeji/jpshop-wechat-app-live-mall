<?php

namespace app\controllers\merchant\system;

use app\models\merchant\app\AppAccessModel;
use app\models\merchant\app\SystemAppAccessModel;
use app\models\merchant\system\OperationRecordModel;
use app\models\merchant\system\YlyPrintModel;
use app\models\merchant\system\YlyPrintTemplateModel;
use app\models\merchant\user\MerchantModel;
use app\models\shop\OrderModel;
use app\models\tuan\LeaderModel;
use yii;
use yii\web\MerchantController;


class YlyPrintController extends MerchantController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors() {
        return [
            'token' => [
                'class' => 'yii\filters\MerchantFilter', //调用过滤器
//                'only' => ['single'],//指定控制器应用到哪些动作
                'except' => ['auto-print'],//指定控制器不应用到哪些动作
            ]
        ];
    }

    /**
     * 生成签名sign
     * @param  array $params 参数
     * @param  string $apiKey API密钥
     * @param  string $msign 打印机密钥
     * @return   string sign
     */
    public function generateSign($params, $apiKey,$msign){
        //所有请求参数按照字母先后顺序排
        ksort($params);
        //定义字符串开始所包括的字符串
        $stringToBeSigned = $apiKey;
        //把所有参数名和参数值串在一起
        foreach ($params as $k => $v)
        {
            $stringToBeSigned .= urldecode($k.$v);
        }
        unset($k, $v);
        //定义字符串结尾所包括的字符串
        $stringToBeSigned .= $msign;
        //使用MD5进行加密，再转化成大写
        return strtoupper(md5($stringToBeSigned));
    }
    /**
     * 生成字符串参数
     * @param array $param 参数
     * @return  string        参数字符串
     */
    public function getStr($param){
        $str = '';
        foreach ($param as $key => $value) {
            $str=$str.$key.'='.$value.'&';
        }
        $str = rtrim($str,'&');
        return $str;
    }

    //商户后台手动点击打印接口
    public function  actionPrint(){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $must = ['key', 'order_sn'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            //查询打印机信息
            $model = new YlyPrintModel();
            $pWhere['key'] = $params['key'];
            $pWhere['merchant_id'] = yii::$app->session['uid'];
            $pWhere['supplier_id'] = 0;
            $pWhere['status'] = 1;
            $array = $model->do_one($pWhere);
            if ($array['status'] != 200){
                return result(500, "未查询到打印机配置");
            }
            //查询应用信息
            $appModel = new AppAccessModel();
            $aWhere['`key`'] = $params['key'];
            $appInfo = $appModel->find($aWhere);
            if ($appInfo['status'] != 200){
                return result(500, "未查询到应用信息");
            }
            //查询订单信息
            $orderModel = new OrderModel();
            $oWhere['order_sn'] = $params['order_sn'];
            $orderList = $orderModel->one($oWhere);
            if ($orderList['status'] != 200){
                return result(204, "未查询到此订单");
            }
            //查询模板启用情况
            $templateModle = new YlyPrintTemplateModel();
            $templateWhere['field'] = "id,name,sign,status";
            $templateWhere['key'] = $params['key'];
            $templateWhere['limit'] = false;
            $templateInfo = $templateModle->do_select($templateWhere);
            if ($templateInfo['status'] != 200){
                return result(500, "易联云小票模板有误");
            }
            foreach ($templateInfo['data'] as $k=>$v){
                $template[$v['sign']] = $v['status'];
            }
            $order = $orderList['data'];
            $order['leader_name'] = '';
            $order['leader_phone'] = '';
            $order['leader_address'] = '';
            $order['leader_area'] = '';
            if ($orderList['data']['leader_self_uid'] != 0){
                $leaderModel = new LeaderModel();
                $leaderWhere['key'] = $orderList['data']['key'];
                $leaderWhere['uid'] = $orderList['data']['leader_self_uid'];
                $leaderInfo = $leaderModel->do_one($leaderWhere);
                if ($leaderInfo['status'] == 200){
                    $order['leader_name'] = $leaderInfo['data']['realname'];
                    $order['leader_phone'] = $leaderInfo['data']['phone'];
                    $order['leader_address'] = $leaderInfo['data']['area_name'];
                    $order['leader_area'] = $leaderInfo['data']['addr'];
                }
            }
            if ($array['data']['type'] == '1'){
                $partner = $array['data']['partner'];
                $machine_code = $array['data']['machine_code'];
                $apiKey = $array['data']['apikey'];
                $msign = $array['data']['msign'];

                $content = "";
                if ($template['shop_name'] == '1'){
                    $content .= "<FS2><center>". $appInfo['data']['name'] ."</center></FS2>";
                }
                $content .= str_repeat('.', 32);
                if ($template['order_time'] == '1'){
                    $content .= "订单时间:". $order['create_time'] . "\n";
                }
                if ($template['order_sn'] == '1'){
                    $content .= "订单编号:". $params['order_sn'] ."\n";
                }
                $content .= str_repeat('*', 14) . "商品" . str_repeat("*", 14);
                if ($template['goods'] == '1'){
                    $content .= "<table>";
                    $content .= "<tr><td>商品名称</td><td>规格</td><td>数量</td><td>单价</td></tr>";
                    foreach ($order['order'] as $k=>$v){
                        if (strlen($v['name'])>15) {
                            $goodsname = substr($v['name'],0,15) . '...';
                        } else {
                            $goodsname = $v['name'];
                        }
                        $content .= "<tr><td>". $goodsname ."</td><td>". $v['property1_name'] . ";" . $v['property2_name'] ."</td><td>x". $v['number'] ."</td><td>". $v['price'] ."</td></tr>";
                    }
                    $content .= "</table>";
                }
                $content .= str_repeat('.', 32);
                if ($template['total_price'] == '1'){
                    $content .= "小计:￥". $order['total_price'] ."\n";
                }
                if ($template['express_price'] == '1'){
                    $content .= "运费:￥". $order['express_price'] ."\n";
                }
                if ($template['voucher_price'] == '1'){
                    $content .= "折扣:￥". $order['voucher_price']['price'] ."\n";
                }
                if ($template['payment_money'] == '1'){
                    $content .= "订单总价:￥". $order['payment_money'] ."\n";
                }
                $content .= str_repeat('*', 32);
                if ($template['buyer_name'] == '1'){
                    $content .= "买家姓名:". $order['name'] ."\n";
                }
                if ($template['buyer_phone'] == '1'){
                    $content .= "买家电话:". $order['phone'] ."\n";
                }
                if ($template['buyer_address'] == '1'){
                    $content .= "买家地址:". $order['address'] ."\n";
                }
                if ($template['leader_name'] == '1'){
                    $content .= "团长姓名:". $order['leader_name'] ."\n";
                }
                if ($template['leader_phone'] == '1'){
                    $content .= "团长电话:". $order['leader_phone'] ."\n";
                }
                if ($template['leader_address'] == '1'){
                    $content .= "团长地址:". $order['leader_address'] ."\n";
                }
                if ($template['leader_area'] == '1'){
                    $content .= "团长小区:". $order['leader_area'] ."\n";
                }
                $content .= str_repeat('*', 32);
                if ($template['buyer_remark'] == '1'){
                    $content .= "<FS2>买家:". $order['remark'] ."</FS2>\n";
                }
                if ($template['merchant_remark'] == '1'){
                    $content .= "<FS2>商家:". $order['admin_remark'] ."</FS2>\n";
                }
                $param = array(
                    "partner"=>$partner,
                    'machine_code'=>$machine_code,
                    'time'=>time(),
                );
                //获取签名
                $param['sign'] = $this->generateSign($param,$apiKey,$msign);
                $param['content'] = $content;
                $str = $this->getStr($param);
                $rs = json_decode(curlPost('http://open.10ss.net:8888',$str),true);
                if ($rs['state'] == 1){
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
                    $operationRecordData['operation_id'] = $params['order_sn'];
                    $operationRecordData['module_name'] = '订单管理';
                    $operationRecordModel->do_add($operationRecordData);
                    return result(200, "请求成功");
                } else {
                    return result(500, "请求失败",$rs);
                }
            }elseif ($array['data']['type'] == '2'){
                $content = '';
                if ($template['shop_name'] == '1'){
                    $content .= "<CB>{$appInfo['data']['name']}</CB><BR>";
                }
                $content .= str_repeat('.', 32);
                if ($template['order_time'] == '1'){
                    $content .= "订单时间:". $order['create_time'] . "<BR>";
                }
                if ($template['order_sn'] == '1'){
                    $content .= "订单编号:". $params['order_sn'] ."<BR>";
                }
                if ($template['goods'] == '1'){
                    $content .= str_repeat('*', 14) . "商品" . str_repeat("*", 14);
                    $content .= '商品名称　　　规格　数量　单价<BR>';
                    foreach ($order['order'] as $k=>$v){
                        //排版商品长度
                        if (strlen($v['name'])>15) {
                            $goodsname = substr($v['name'],0,15) . '...';
                        } else {
                            $goodsname = $v['name'];
                        }
                        if(strlen($goodsname) < 17){
                            $k1 = 17 - strlen($goodsname);
                            $kw1 = '';
                            for($q=0;$q<$k1;$q++){
                                $kw1 .= ' ';
                            }
                            $goodsname = $goodsname.$kw1;
                        }
                        //排版规格长度
                        $specs = $v['property1_name'] . ";" . $v['property2_name'];
                        if (strlen($specs) < 9){
                            $k2 = 9 - strlen($specs);
                            $kw2 = '';
                            for($q=0;$q<$k2;$q++){
                                $kw2 .= ' ';
                            }
                            $specs = $specs.$kw2;
                        }
                        //排版数量长度
                        if(strlen($v['number']) < 4){
                            $k2 = 4 - strlen($v['number']);
                            $kw2 = '';
                            for($q=0;$q<$k2;$q++){
                                $kw2 .= ' ';
                            }
                            $v['number'] = $v['number'].$kw2;
                        }
                        $content .= $goodsname.$specs.$v['number'].$v['price']."<BR>";
                    }
                }
                $content .= str_repeat('.', 32);
                if ($template['total_price'] == '1'){
                    $content .= "小计:￥". $order['total_price'] ."\n";
                }
                if ($template['express_price'] == '1'){
                    $content .= "运费:￥". $order['express_price'] ."\n";
                }
                if ($template['voucher_price'] == '1'){
                    $content .= "折扣:￥". $order['voucher_price']['price'] ."\n";
                }
                if ($template['payment_money'] == '1'){
                    $content .= "订单总价:￥". $order['payment_money'] ."\n";
                }
                $content .= str_repeat('*', 32);
                if ($template['buyer_name'] == '1'){
                    $content .= "买家姓名:". $order['name'] ."<BR>";
                }
                if ($template['buyer_phone'] == '1'){
                    $content .= "买家电话:". $order['phone'] ."<BR>";
                }
                if ($template['buyer_address'] == '1'){
                    $content .= "买家地址:". $order['address'] ."<BR>";
                }
                if ($template['leader_name'] == '1'){
                    $content .= "团长姓名:". $order['leader_name'] ."<BR>";
                }
                if ($template['leader_phone'] == '1'){
                    $content .= "团长电话:". $order['leader_phone'] ."<BR>";
                }
                if ($template['leader_address'] == '1'){
                    $content .= "团长地址:". $order['leader_address'] ."<BR>";
                }
                if ($template['leader_area'] == '1'){
                    $content .= "团长小区:". $order['leader_area'] ."<BR>";
                }
                $content .= str_repeat('*', 32);
                if ($template['buyer_remark'] == '1'){
                    $content .= "<B>买家:". $order['remark'] ."</B><BR>";
                }
                if ($template['merchant_remark'] == '1'){
                    $content .= "<B>商家:". $order['admin_remark'] ."</B><BR>";
                }
                $time = time();
                $user = $array['data']['partner'];
                $ukey = $array['data']['apikey'];
                $sn = $array['data']['machine_code'];
                $url = "http://api.feieyun.cn/Api/Open/";
                $data = array(
                    'user'=>$user,
                    'stime'=>$time,
                    'sig'=>sha1($user.$ukey.$time),
                    'apiname'=>'Open_printMsg',
                    'sn'=>$sn,
                    'content'=>$content
                );
                $res = json_decode(curlPost($url,$data),true);

                if ($res['ret'] == 0){
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
                    $operationRecordData['operation_id'] = $params['order_sn'];
                    $operationRecordData['module_name'] = '订单管理';
                    $operationRecordModel->do_add($operationRecordData);
                    return result(200, "请求成功");
                }else{
                    return result(500, "请求失败",$res);
                }
            }else{
                return result(500, "打印机类型有误");
            }

        } else {
            return result(500, "请求方式错误");
        }
    }

    //自动推送打印订单接口
    public function  actionAutoPrint(){
        $paramsLen = llenRedis('ylyprint');
        if ($paramsLen > 0){
            for ($i = 0; $i < $paramsLen;$i++){
                $paramsList[] = rpopRedis('ylyprint');
            }
            foreach ($paramsList as $k=>$v){
                $params = [];
                $params = $v;
                file_put_contents(Yii::getAlias('@webroot/') . '/ylyPrint.text', date('Y-m-d H:i:s') . "正在打印_" . json_encode($params) . PHP_EOL, FILE_APPEND);
                if($params){
                    //查询打印机信息
                    $model = new YlyPrintModel();
                    $pWhere = [];
                    $pWhere['key'] = $params['key'];
                    $pWhere['supplier_id'] = 0;
                    $pWhere['status'] = 1;
                    $array = $model->do_one($pWhere);
                    if ($array['status'] != 200){
                        file_put_contents(Yii::getAlias('@webroot/') . '/ylyPrint.text', date('Y-m-d H:i:s') . "打印机未启用" . PHP_EOL, FILE_APPEND);
                        continue;
                    }
                    //查询应用信息
                    $appModel = new AppAccessModel();
                    $aWhere = [];
                    $aWhere['`key`'] = $params['key'];
                    $appInfo = $appModel->find($aWhere);
                    if ($appInfo['status'] != 200){
                        file_put_contents(Yii::getAlias('@webroot/') . '/ylyPrint.text', date('Y-m-d H:i:s') . "未查询到应用" . PHP_EOL, FILE_APPEND);
                        continue;
                    }
                    if ($appInfo['status'] == 200 && $appInfo['data']['yly_print'] == '0'){
                        file_put_contents(Yii::getAlias('@webroot/') . '/ylyPrint.text', date('Y-m-d H:i:s') . "易联云自动推送未开启" . PHP_EOL, FILE_APPEND);
                        continue;
                    }
                    //查询订单信息
                    $orderModel = new OrderModel();
                    $oWhere = [];
                    $oWhere['order_sn'] = $params['order_sn'];
                    $orderList = $orderModel->one($oWhere);
                    if ($orderList['status'] == 200){
                        $order = $orderList['data'];
                        $order['leader_name'] = '';
                        $order['leader_phone'] = '';
                        $order['leader_address'] = '';
                        $order['leader_area'] = '';
                        if ($orderList['data']['leader_self_uid'] != 0){
                            $leaderModel = new LeaderModel();
                            $leaderWhere = [];
                            $leaderWhere['key'] = $orderList['data']['key'];
                            $leaderWhere['uid'] = $orderList['data']['leader_self_uid'];
                            $leaderInfo = $leaderModel->do_one($leaderWhere);
                            if ($leaderInfo['status'] == 200){
                                $order['leader_name'] = $leaderInfo['data']['realname'];
                                $order['leader_phone'] = $leaderInfo['data']['phone'];
                                $order['leader_address'] = $leaderInfo['data']['area_name'];
                                $order['leader_area'] = $leaderInfo['data']['addr'];
                            }
                        }
                    } else {
                        file_put_contents(Yii::getAlias('@webroot/') . '/ylyPrint.text', date('Y-m-d H:i:s') . "未查询到此订单_" . $params['order_sn'] . PHP_EOL, FILE_APPEND);
                        continue;
                    }
                    //查询模板启用情况
                    $templateModle = new YlyPrintTemplateModel();
                    $templateWhere['field'] = "id,name,sign,status";
                    $templateWhere['key'] = $params['key'];
                    $templateWhere['limit'] = false;
                    $templateInfo = $templateModle->do_select($templateWhere);
                    if ($templateInfo['status'] != 200){
                        file_put_contents(Yii::getAlias('@webroot/') . '/ylyPrint.text', date('Y-m-d H:i:s') . "易联云小票模板有误_" . $params['order_sn'] . PHP_EOL, FILE_APPEND);
                        continue;
                    }
                    foreach ($templateInfo['data'] as $k=>$v){
                        $template[$v['sign']] = $v['status'];
                    }
                    if ($array['data']['type'] == '1'){
                        $partner = $array['data']['partner'];
                        $machine_code = $array['data']['machine_code'];
                        $apiKey = $array['data']['apikey'];
                        $msign = $array['data']['msign'];
                        $content = "";
                        if ($template['shop_name'] == '1'){
                            $content .= "<FS2><center>". $appInfo['data']['name'] ."</center></FS2>";
                        }
                        $content .= str_repeat('.', 32);
                        if ($template['order_time'] == '1'){
                            $content .= "订单时间:". $order['create_time'] . "\n";
                        }
                        if ($template['order_sn'] == '1'){
                            $content .= "订单编号:". $params['order_sn'] ."\n";
                        }
                        $content .= str_repeat('*', 14) . "商品" . str_repeat("*", 14);
                        if ($template['goods'] == '1'){
                            $content .= "<table>";
                            $content .= "<tr><td>商品名称</td><td>规格</td><td>数量</td><td>单价</td></tr>";
                            foreach ($order['order'] as $k=>$v){
                                if (strlen($v['name'])>15) {
                                    $goodsname = substr($v['name'],0,15) . '...';
                                } else {
                                    $goodsname = $v['name'];
                                }
                                $content .= "<tr><td>". $goodsname ."</td><td>". $v['property1_name'] . ";" . $v['property2_name'] ."</td><td>x". $v['number'] ."</td><td>". $v['price'] ."</td></tr>";
                            }
                            $content .= "</table>";
                        }
                        $content .= str_repeat('.', 32);
                        if ($template['total_price'] == '1'){
                            $content .= "小计:￥". $order['total_price'] ."\n";
                        }
                        if ($template['express_price'] == '1'){
                            $content .= "运费:￥". $order['express_price'] ."\n";
                        }
                        if ($template['voucher_price'] == '1'){
                            $content .= "折扣:￥". $order['voucher_price']['price'] ."\n";
                        }
                        if ($template['payment_money'] == '1'){
                            $content .= "订单总价:￥". $order['payment_money'] ."\n";
                        }
                        $content .= str_repeat('*', 32);
                        if ($template['buyer_name'] == '1'){
                            $content .= "买家姓名:". $order['name'] ."\n";
                        }
                        if ($template['buyer_phone'] == '1'){
                            $content .= "买家电话:". $order['phone'] ."\n";
                        }
                        if ($template['buyer_address'] == '1'){
                            $content .= "买家地址:". $order['address'] ."\n";
                        }
                        if ($template['leader_name'] == '1'){
                            $content .= "团长姓名:". $order['leader_name'] ."\n";
                        }
                        if ($template['leader_phone'] == '1'){
                            $content .= "团长电话:". $order['leader_phone'] ."\n";
                        }
                        if ($template['leader_address'] == '1'){
                            $content .= "团长地址:". $order['leader_address'] ."\n";
                        }
                        if ($template['leader_area'] == '1'){
                            $content .= "团长小区:". $order['leader_area'] ."\n";
                        }
                        $content .= str_repeat('*', 32);
                        if ($template['buyer_remark'] == '1'){
                            $content .= "<FS2>买家:". $order['remark'] ."</FS2>\n";
                        }
                        if ($template['merchant_remark'] == '1'){
                            $content .= "<FS2>商家:". $order['admin_remark'] ."</FS2>\n";
                        }
                        $param = array(
                            "partner"=>$partner,
                            'machine_code'=>$machine_code,
                            'time'=>time(),
                        );
                        //获取签名
                        $param['sign'] = $this->generateSign($param,$apiKey,$msign);
                        $param['content'] = $content;
                        $str = $this->getStr($param);
                        $rs = json_decode(curlPost('http://open.10ss.net:8888',$str),true);
                        if ($rs['state'] != 1){
                            file_put_contents(Yii::getAlias('@webroot/') . '/ylyPrint.text', date('Y-m-d H:i:s') . json_encode($rs) . PHP_EOL, FILE_APPEND);
                            continue;
                        }
                    }elseif ($array['data']['type'] == '2'){
                        $content = '';
                        if ($template['shop_name'] == '1'){
                            $content .= "<CB>{$appInfo['data']['name']}</CB><BR>";
                        }
                        $content .= str_repeat('.', 32);
                        if ($template['order_time'] == '1'){
                            $content .= "订单时间:". $order['create_time'] . "<BR>";
                        }
                        if ($template['order_sn'] == '1'){
                            $content .= "订单编号:". $params['order_sn'] ."<BR>";
                        }
                        if ($template['goods'] == '1'){
                            $content .= str_repeat('*', 14) . "商品" . str_repeat("*", 14);
                            $content .= '商品名称　　　规格　数量　单价<BR>';
                            foreach ($order['order'] as $k=>$v){
                                //排版商品长度
                                if (strlen($v['name'])>15) {
                                    $goodsname = substr($v['name'],0,15) . '...';
                                } else {
                                    $goodsname = $v['name'];
                                }
                                if(strlen($goodsname) < 17){
                                    $k1 = 17 - strlen($goodsname);
                                    $kw1 = '';
                                    for($q=0;$q<$k1;$q++){
                                        $kw1 .= ' ';
                                    }
                                    $goodsname = $goodsname.$kw1;
                                }
                                //排版规格长度
                                $specs = $v['property1_name'] . ";" . $v['property2_name'];
                                if (strlen($specs) < 9){
                                    $k2 = 9 - strlen($specs);
                                    $kw2 = '';
                                    for($q=0;$q<$k2;$q++){
                                        $kw2 .= ' ';
                                    }
                                    $specs = $specs.$kw2;
                                }
                                //排版数量长度
                                if(strlen($v['number']) < 4){
                                    $k2 = 4 - strlen($v['number']);
                                    $kw2 = '';
                                    for($q=0;$q<$k2;$q++){
                                        $kw2 .= ' ';
                                    }
                                    $v['number'] = $v['number'].$kw2;
                                }
                                $content .= $goodsname.$specs.$v['number'].$v['price']."<BR>";
                            }
                        }
                        $content .= str_repeat('.', 32);
                        if ($template['total_price'] == '1'){
                            $content .= "小计:￥". $order['total_price'] ."\n";
                        }
                        if ($template['express_price'] == '1'){
                            $content .= "运费:￥". $order['express_price'] ."\n";
                        }
                        if ($template['voucher_price'] == '1'){
                            $content .= "折扣:￥". $order['voucher_price']['price'] ."\n";
                        }
                        if ($template['payment_money'] == '1'){
                            $content .= "订单总价:￥". $order['payment_money'] ."\n";
                        }
                        $content .= str_repeat('*', 32);
                        if ($template['buyer_name'] == '1'){
                            $content .= "买家姓名:". $order['name'] ."<BR>";
                        }
                        if ($template['buyer_phone'] == '1'){
                            $content .= "买家电话:". $order['phone'] ."<BR>";
                        }
                        if ($template['buyer_address'] == '1'){
                            $content .= "买家地址:". $order['address'] ."<BR>";
                        }
                        if ($template['leader_name'] == '1'){
                            $content .= "团长姓名:". $order['leader_name'] ."<BR>";
                        }
                        if ($template['leader_phone'] == '1'){
                            $content .= "团长电话:". $order['leader_phone'] ."<BR>";
                        }
                        if ($template['leader_address'] == '1'){
                            $content .= "团长地址:". $order['leader_address'] ."<BR>";
                        }
                        if ($template['leader_area'] == '1'){
                            $content .= "团长小区:". $order['leader_area'] ."<BR>";
                        }
                        $content .= str_repeat('*', 32);
                        if ($template['buyer_remark'] == '1'){
                            $content .= "<B>买家:". $order['remark'] ."</B><BR>";
                        }
                        if ($template['merchant_remark'] == '1'){
                            $content .= "<B>商家:". $order['admin_remark'] ."</B><BR>";
                        }
                        $time = time();
                        $user = $array['data']['partner'];
                        $ukey = $array['data']['apikey'];
                        $sn = $array['data']['machine_code'];
                        $url = "http://api.feieyun.cn/Api/Open/";
                        $data = array(
                            'user'=>$user,
                            'stime'=>$time,
                            'sig'=>sha1($user.$ukey.$time),
                            'apiname'=>'Open_printMsg',
                            'sn'=>$sn,
                            'content'=>$content
                        );
                        $res = json_decode(curlPost($url,$data),true);

                        if ($res['ret'] != 0){
                            file_put_contents(Yii::getAlias('@webroot/') . '/ylyPrint.text', date('Y-m-d H:i:s') . json_encode($res) . PHP_EOL, FILE_APPEND);
                            continue;
                        }
                    }else{
                        file_put_contents(Yii::getAlias('@webroot/') . '/ylyPrint.text', date('Y-m-d H:i:s') . "打印机类型有误_" . $params['order_sn'] . PHP_EOL, FILE_APPEND);
                        continue;
                    }
                }
            }
        }

    }

    public function actionList(){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            if (isset($params['searchName'])) {
                if ($params['searchName'] != "") {
                    $params['name'] = ['like', "{$params['searchName']}"];
                }
                unset($params['searchName']);
            }

            $model = new YlyPrintModel();
            $params['merchant_id'] = yii::$app->session['uid'];
            $params['supplier_id'] = 0;
            $array = $model->do_select($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionOne($id){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $model = new YlyPrintModel();
            $where['id'] = $id;
            $where['supplier_id'] = 0;
            $array = $model->do_one($where);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd(){
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $must = ['key', 'name', 'partner', 'machine_code', 'apikey', 'msign'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $params['merchant_id'] = yii::$app->session['uid'];

            $model = new YlyPrintModel();
            $where['key'] = $params['key'];
            $where['limit'] = false;
            $where['merchant_id'] = $params['merchant_id'];
            $where['machine_code'] = $params['machine_code'];
            $res = $model->do_select($where);
            if ($res['status'] == 200){
                return result(500, "打印机已存在");
            }
            $array = $model->do_add($params);
            if ($array['status'] == 200){
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
                $operationRecordData['operation_id'] = $array['data'];
                $operationRecordData['module_name'] = '易联云';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate($id){
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $model = new YlyPrintModel();
            //若是修改状态，先关闭所有非门店打印机，修改当前编辑的打印机状态
            if (isset($params['status']) && $params['status'] == 1){
                $tWhere['key'] = $params['key'];
                $tWhere['merchant_id'] = yii::$app->session['uid'];
                $tWhere['supplier_id'] = 0;
                $data['status'] = 0;
                $model->do_update($tWhere,$data);
            }
            //修改
            $where['id'] = $id;
            $array = $model->do_update($where,$params);
            if ($array['status'] == 200){
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
                $operationRecordData['module_name'] = '易联云';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionDelete($id){
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $model = new YlyPrintModel();
            $where['id'] = $id;
            $where['supplier_id'] = 0;
            $array = $model->do_delete($where);
            if ($array['status'] == 200){
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
                $operationRecordData['operation_type'] = '删除';
                $operationRecordData['operation_id'] = $id;
                $operationRecordData['module_name'] = '易联云';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAutoUpdate($id){
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $must = ['key','yly_print'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $model = new SystemAppAccessModel();
            $where['id'] = $id;
            $data['yly_print'] = $params['yly_print'];
            $array = $model->do_update($where,$data);

            if ($array['status'] == 200){
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
                $operationRecordData['module_name'] = '易联云';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAutoSwitch($id){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $model = new SystemAppAccessModel();
            $where['field'] = "id,yly_print";
            $where['id'] = $id;
            $array = $model->do_one($where);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}