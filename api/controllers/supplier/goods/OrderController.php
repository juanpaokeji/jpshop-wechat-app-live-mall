<?php

namespace app\controllers\supplier\goods;


use yii;
use yii\db\Exception;
use yii\web\SupplierController;
use app\models\shop\OrderModel;
use app\models\shop\StorePaymentModel;
use app\models\shop\UserModel;


/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class OrderController extends SupplierController
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors()
    {
        return [
            'token' => [
                'class' => 'yii\filters\SupplierFilter', //调用过滤器
                // 'only' => ['single'], //指定控制器应用到哪些动作
                'except' => ['order'], //指定控制器不应用到哪些动作
            ]
        ];
    }

    /**
     * 商城后台，订单管理，主订单列表
     * 地址:/admin/group/index 默认访问
     * @return array
     */
    public function actionList()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

//            $must = ['key'];
//            $rs = $this->checkInput($must, $params);
//            if ($rs != false) {
//                $rs;
//            }

            $model = new OrderModel();
            $params['shop_order_group.`key`'] = yii::$app->session['key'];
            unset($params['key']);
            $params['shop_order_group.merchant_id'] = yii::$app->session['uid'];
            $params['shop_order_group.supplier_id'] = yii::$app->session['sid'];
            $array = $model->findAll($params);

            if ($array['status'] == 200) {
                $areaModel = new \app\models\system\SystemAreaModel();
                for ($i = 0; $i < count($array['data']); $i++) {
                    if ($array['data'][$i]['province_code'] != null && $array['data'][$i]['city_code'] != null && $array['data'][$i]['area_code'] != null) {
                        $province = $areaModel->do_column(['field' => 'name', 'code' => $array['data'][$i]['province_code']]);
                        $city = $areaModel->do_column(['field' => 'name', 'code' => $array['data'][$i]['city_code']]);
                        $area = $areaModel->do_column(['field' => 'name', 'code' => $array['data'][$i]['area_code']]);
                        $array['data'][$i]['province'] = $province['data'][0];
                        $array['data'][$i]['city'] = $city['data'][0];
                        $array['data'][$i]['area'] = $area['data'][0];
                    } else {
                        $array['data'][$i]['province'] = "";
                        $array['data'][$i]['city'] = "";
                        $array['data'][$i]['area'] = "";
                    }
                }
            }

            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 子订单列表
     * @return array
     */
    public function actionSuborder()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
//            $must = ['key'];
//            $rs = $this->checkInput($must, $params);
//            if ($rs != false) {
//                return json_encode($rs, JSON_UNESCAPED_UNICODE);
//            }

            $model = new OrderModel();
            $params['shop_order.`key`'] = yii::$app->session['key'];

            $params['shop_order.merchant_id'] = yii::$app->session['uid'];
            $params['shop_order.sid'] = yii::$app->session['sid'];
            $data['merchant_id'] = yii::$app->session['uid'];
            $data['sid'] = yii::$app->session['sid'];
            $data['`key`'] = yii::$app->session['key'];
            $data['order_sn'] = $params['order_group_sn'];
            $res = $model->find($data);
            if ($res['status'] != 200) {
                return result(500, "找不到该订单");
            }
            unset($params['key']);
            $array = $model->findSuborder($params);
            if ($array['status'] == '200') {
                for ($i = 0; $i < $array['count']; $i++) {
                    $pic_urls = $array['data'][$i]['pic_url'];
                    $array['data'][$i]['pic_urls'] = $pic_urls;
                }
            }
            $array['refund'] = $res['data']['refund'];
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAll()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
//            $must = ['key'];
//            $rs = $this->checkInput($must, $params);
//            if ($rs != false) {
//                return json_encode($rs, JSON_UNESCAPED_UNICODE);
//            }
            $model = new OrderModel();
            $params['shop_order_group.`key`'] = yii::$app->session['key'];
            unset($params['key']);
            $params['shop_order_group.merchant_id'] = yii::$app->session['uid'];
            if (!isset($params['after_sale']) || $params['after_sale'] == "") {
                $params['after_sale !=-1'] = null;
            }
            $params['status'] = 5;
            $array = $model->findAll($params);
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
//            $must = ['key'];
//            $rs = $this->checkInput($must, $params);
//            if ($rs != false) {
//                return json_encode($rs, JSON_UNESCAPED_UNICODE);
//            }
            $category = new OrderModel();
            $params['id'] = $id;
            $array = $category->find($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 订单发货
     * @return array
     * @throws Exception
     */
    public function actionSend()
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参


//            $must = ['key'];
//            $rs = $this->checkInput($must, $params);
//            if ($rs != false) {
//                return json_encode($rs, JSON_UNESCAPED_UNICODE);
//            }
            $model = new OrderModel();

            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['uid'];
            $params['supplier_id'] = yii::$app->session['sid'];
            if (!isset($params['order_sn'])) {
                return result(400, "缺少参数 order_sn");
            }
            $data = $model->find($params);
            if ($data['status'] == 200) {
                if ($data['data']['is_tuan'] == 1 && ($data['data']['express_type'] == 1 || $data['data']['express_type'] == 2)) {
                    $params['express_id'] = 0;
                    $params['express_number'] = 0;
                }
            }
            if (!isset($params['express_id'])) {
                return result(400, "缺少参数 快递id");
            }
            if (!isset($params['express_number'])) {
                return result(400, "缺少参数 快递单号");
            }
            $data = $model->find($params);
            if ($data['status'] != 200) {
                return result(400, "缺少数据");
            }
            if ($data['data']['is_tuan'] == 1) {
                $type = 2;
            } else {
                $type = 1;
            }
            $array = $model->updateSend($params, $type);


            //数据库修改完成需要发送短信以及公众号信息
//            $sms = new SMS();
//            $phone = '15366669450';//单发
//            return $sms->sendOne($phone);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }
    
    
     public function actionPayment(){
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new UserModel();
            $params['merchant_id'] = yii::$app->session['uid'];
            $params['sid'] = yii::$app->session['sid'];
            if($params['time']<time()-7200){
                return result(500,'二维码已超时');
            }
            $array = $model->find(['`key`'=> yii::$app->session['key'],'merchant_id'=>yii::$app->session['uid'],'id'=>$params['user_id']]);
            if($array['status']!=200){
                return result(500,'找不到此用户');
            }
            if($array['data']['recharge_balance']==0){
            	 return result(500,'账户0元');
            }
            
            if($array['data']['recharge_balance']<$params['money']){
            	 return result(500,'余额不足');
            }
            
            $paymentModel = new StorePaymentModel();
            $res = $paymentModel->do_one(['order_sn'=>$params['order_sn']]); 
            if($res['status']==200){
            	if($res['data']['status']==1){
            		return result(500,'改二维码已失效');
            	}else{
            		 $udata['recharge_balance']=(float)$array['data']['recharge_balance']-(float)$params['money'];
			          $udata['id']=$params['user_id'];
			          $udata['`key`']=$params['key'];
			          $res =  $model->update($udata);
			          $paymentModel->do_update(['order_sn'=>$params['order_sn'],'status'=>1]); 
            	}
            }else{
            	$udata['recharge_balance']=(float)$array['data']['recharge_balance']-(float)$params['money'];
	            $udata['id']=$params['user_id'];
	            $udata['`key`']= yii::$app->session['key'];
	            $res =  $model->update($udata);
	            
	        	$subUserModel =new \app\models\merchant\system\UserModel();
	            $sid = yii::$app->session['sid'];
		        $sub = $subUserModel->one(['id'=>$sid]);
		        $res =  $subUserModel->updates(['id'=>$sid,'balance'=>$sub['data']['balance']+$params['money']]);
	            
	            
	            $paymentModel = new StorePaymentModel();
	            $data['order_sn'] = $params['order_sn'];
	            $data['user_id']= $params['user_id'];
	            $data['store'] = $sub['data']['real_name'];
	            $data['sid'] = yii::$app->session['sid'];
	            $data['money']= $params['money'];
	            $data['merchant_id']=yii::$app->session['uid'];
	            $data['key']= $params['key'];
	            $data['type']='门店余额付款';
	            $data['nickname'] = $array['data']['nickname'];
	            $data['status'] =1;
	            $paymentModel->do_add($data);
            }
            
            
            return $res;
        } else {
            return result(500, "请求方式错误");
        }
    }
    
    
    public function actionStoreList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new StorePaymentModel();
            //$params['merchant_id'] = yii::$app->session['uid'];
            if (isset($params['nickname'])) {
                if ($params['nickname'] != "") {
                    $params['nickname'] = ['like', "{$params['nickname']}"];
                }
                unset($params['nickname']);
            }
            if (isset($params['start_time'])) {
                if ($params['start_time'] != "") {
                    $params['>='] = ['shop_store_payment.create_time', strtotime($params['start_time'])];
                   // $params['<='] = ['shop_store_payment.create_time', $params['start_time']];
                }
               unset($params['start_time']);
            }
            if (isset($params['end_time'])) {
                if ($params['end_time'] != "") {
                    $params['<='] = ['shop_store_payment.create_time', strtotime($params['end_time'])];
                  //  $params['>='] = ['shop_store_payment.create_time', $params['end_time']];
                }
               unset($params['end_time']);
            }
            if (isset($params['order_sn'])) {
                if ($params['order_sn'] == "") {
                  unset($params['order_sn']);
                }
            }
            
            $key =  yii::$app->session['key'];
            unset($params['key']);
            $params['shop_store_payment.merchant_id'] = yii::$app->session['uid'];
             $params['shop_store_payment.sid'] = yii::$app->session['sid'];
            $params['shop_store_payment.key'] = $key;
            $params['field'] = "shop_store_payment.*,shop_user.avatar";
            $params['join'][] = ['left join','shop_user','shop_user.id=shop_store_payment.user_id'];
            $array = $model->do_select($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }
    
    public function actionUserInfo(){
    	if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $sid = yii::$app->session['sid'];
	        $subUserModel =new \app\models\merchant\system\UserModel();
		    $sub = $subUserModel->find(['id'=>$sid]);
            return $sub;
        } else {
            return result(500, "请求方式错误");
        }
    }


}
