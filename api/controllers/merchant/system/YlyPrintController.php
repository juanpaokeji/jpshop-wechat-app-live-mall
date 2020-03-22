<?php

namespace app\controllers\merchant\system;

use app\models\merchant\app\AppAccessModel;
use app\models\merchant\system\OperationRecordModel;
use app\models\merchant\system\YlyPrintModel;
use app\models\shop\OrderModel;
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

            $model = new YlyPrintModel();
            $orderModel = new OrderModel();
            $appModel = new AppAccessModel();
            $pWhere['key'] = $params['key'];
            $pWhere['merchant_id'] = yii::$app->session['uid'];
            $pWhere['status'] = 1;
            $array = $model->do_select($pWhere);
            if ($array['status'] != 200){
                return result(500, "打印机未启用");
            }

            $aWhere['`key`'] = $params['key'];
            $appInfo = $appModel->find($aWhere);
            if ($appInfo['status'] != 200){
                return $appInfo;
            }

            $oWhere['order_sn'] = $params['order_sn'];
            $orderList = $orderModel->one($oWhere);
            if ($orderList['status'] == 200){
                $order = $orderList['data'];
            } else {
                return result(204, "未查询到此订单");
            }

            $partner = $array['data'][0]['partner'];
            $machine_code = $array['data'][0]['machine_code'];
            $apiKey = $array['data'][0]['apikey'];
            $msign = $array['data'][0]['msign'];

            $content = "<FS2><center>". $appInfo['data']['name'] ."</center></FS2>";
            $content .= str_repeat('.', 32);
            $content .= "订单时间:". $order['create_time'] . "\n";
            $content .= "订单编号:". $params['order_sn'] ."\n";
            $content .= str_repeat('*', 14) . "商品" . str_repeat("*", 14);
            $content .= "<table>";
            $content .= "<tr><td>商品名称</td><td>数量</td><td>单价</td></tr>";
            foreach ($order['order'] as $k=>$v){
                if (strlen($v['name'])>36) {
                    $goodsname = substr($v['name'],0,36) . '...';
                } else {
                    $goodsname = $v['name'];
                }
                $content .= "<tr><td>". $goodsname ."</td><td>x". $v['number'] ."</td><td>". $v['price'] ."</td></tr>";
            }
            $content .= "</table>";
            $content .= str_repeat('.', 32);
            $content .= "小计:￥". $order['total_price'] ."\n";
            $content .= "运费:￥". $order['express_price'] ."\n";
            $content .= "折扣:￥". $order['voucher_price']['price'] ."\n";
            $content .= str_repeat('*', 32);
            $content .= "订单总价:￥". $order['payment_money'] ."\n";

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
                $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                $operationRecordData['operation_type'] = '更新';
                $operationRecordData['operation_id'] = $params['order_sn'];
                $operationRecordData['module_name'] = '订单管理';
                $operationRecordModel->do_add($operationRecordData);
                return result(200, "请求成功");
            } else {
                return result(500, "请求失败",$rs);
            }

        } else {
            return result(500, "请求方式错误");
        }
    }

    //自动推送打印订单接口
    public function  actionAutoPrint(){
        $redis =  \Yii::$app->redis;
        $paramsLen = $redis->llen('ylyprint');
        if ($paramsLen > 0){
            for ($i = 0; $i < $paramsLen;$i++){
                $paramsList[] = json_decode($redis->rpop('ylyprint'),true);
            }
            foreach ($paramsList as $k=>$v){
                $params = $v;
                if($params){
                    $model = new YlyPrintModel();
                    $orderModel = new OrderModel();
                    $appModel = new AppAccessModel();
                    $pWhere['key'] = $params['key'];
                    $pWhere['status'] = 1;
                    $array = $model->do_select($pWhere);
                    if ($array['status'] != 200){
                        file_put_contents(Yii::getAlias('@webroot/') . '/ylyPrint.text', date('Y-m-d H:i:s') . "打印机未启用" . PHP_EOL, FILE_APPEND);
                    }

                    $aWhere['`key`'] = $params['key'];
                    $appInfo = $appModel->find($aWhere);
                    if ($appInfo['status'] != 200){
                        file_put_contents(Yii::getAlias('@webroot/') . '/ylyPrint.text', date('Y-m-d H:i:s') . "未查询到应用" . PHP_EOL, FILE_APPEND);
                    }

                    if ($appInfo['status'] == 200 && $appInfo['data']['yly_print'] == '0'){
                        file_put_contents(Yii::getAlias('@webroot/') . '/ylyPrint.text', date('Y-m-d H:i:s') . "易联云自动推送未开启" . PHP_EOL, FILE_APPEND);
                        continue;
                    }

                    $oWhere['order_sn'] = $params['order_sn'];
                    $orderList = $orderModel->one($oWhere);
                    if ($orderList['status'] == 200){
                        $order = $orderList['data'];
                    } else {
                        file_put_contents(Yii::getAlias('@webroot/') . '/ylyPrint.text', date('Y-m-d H:i:s') . "未查询到此订单" . PHP_EOL, FILE_APPEND);
                    }

                    $partner = $array['data'][0]['partner'];
                    $machine_code = $array['data'][0]['machine_code'];
                    $apiKey = $array['data'][0]['apikey'];
                    $msign = $array['data'][0]['msign'];

                    $content = "<FS2><center>". $appInfo['data']['name'] ."</center></FS2>";
                    $content .= str_repeat('.', 32);
                    $content .= "订单时间:". $order['create_time'] . "\n";
                    $content .= "订单编号:". $params['order_sn'] ."\n";
                    $content .= str_repeat('*', 14) . "商品" . str_repeat("*", 14);
                    $content .= "<table>";
                    $content .= "<tr><td>商品名称</td><td>数量</td><td>单价</td></tr>";
                    foreach ($order['order'] as $k=>$v){
                        if (strlen($v['name'])>36) {
                            $goodsname = substr($v['name'],0,36) . '...';
                        } else {
                            $goodsname = $v['name'];
                        }
                        $content .= "<tr><td>". $goodsname ."</td><td>x". $v['number'] ."</td><td>". $v['price'] ."</td></tr>";
                    }
                    $content .= "</table>";
                    $content .= str_repeat('.', 32);
                    $content .= "小计:￥". $order['total_price'] ."\n";
                    $content .= "运费:￥". $order['express_price'] ."\n";
                    $content .= "折扣:￥". $order['voucher_price']['price'] ."\n";
                    $content .= str_repeat('*', 32);
                    $content .= "订单总价:￥". $order['payment_money'] ."\n";

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
            $where['limit'] = 10000;
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
                $operationRecordData['merchant_id'] = yii::$app->session['uid'];
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
            //开始事务
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if (isset($params['status']) && $params['status'] == 1){
                    $tWhere['key'] = $params['key'];
                    $tWhere['merchant_id'] = yii::$app->session['uid'];
                    $data['status'] = 0;
                    $model->do_update($tWhere,$data);

                }
                $where['id'] = $id;
                $array = $model->do_update($where,$params);
                $transaction->commit(); //提交
            } catch (\yii\base\Exception $e) {
                $transaction->rollBack(); //回滚
                return result(500, "更新失败");
            }
            if ($array['status'] == 200){
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['key'];
                $operationRecordData['merchant_id'] = yii::$app->session['uid'];
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
            $array = $model->do_delete($where);
            if ($array['status'] == 200){
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['key'];
                $operationRecordData['merchant_id'] = yii::$app->session['uid'];
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




}