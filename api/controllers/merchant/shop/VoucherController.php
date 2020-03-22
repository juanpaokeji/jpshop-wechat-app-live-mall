<?php

namespace app\controllers\merchant\shop;

use app\controllers\common\CommonController;
use app\models\shop\UserModel;
use yii;
use yii\web\MerchantController;
use yii\base\Exception;
use app\models\shop\VoucherModel;
use app\models\shop\VoucherTypeModel;
use app\models\core\TableModel;

/**
 * 抵用卷控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class VoucherController extends MerchantController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * 地址:/admin/group/index 默认访问
     * @throws Exception if the model cannot be found
     * @return array
     */

    public function generateCode($nums = 1, $exist_array = '', $code_length = 32, $prefix = '') {

        $characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnpqrstuvwxyz";
        $promotion_codes = array(); //这个数组用来接收生成的优惠码
        for ($j = 0; $j < $nums; $j++) {
            $code = '';
            for ($i = 0; $i < $code_length; $i++) {

                $code .= $characters[mt_rand(0, strlen($characters) - 1)];
            }
            //如果生成的4位随机数不再我们定义的$promotion_codes数组里面
            if (!in_array($code, $promotion_codes)) {
                if (is_array($exist_array)) {
                    if (!in_array($code, $exist_array)) {//排除已经使用的优惠码
                        $promotion_codes[$j] = $prefix . $code; //将生成的新优惠码赋值给promotion_codes数组
                    } else {
                        $j--;
                    }
                } else {
                    $promotion_codes[$j] = $prefix . $code; //将优惠码赋值给数组
                }
            } else {
                $j--;
            }
        }
        return $promotion_codes[0];
    }

    public function actionList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $voucher = new VoucherModel();
            if (isset($params['key'])) {
                $params['`key`'] = $params['key'];
                unset($params['key']);
            }
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $voucher->findall($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $voucher = new VoucherModel();
            $params['cdkey'] = $id;
            $params['`key`'] = $params['key'];
            unset($params['key']);
            unset($params['id']);
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $voucher->find($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $voucher = new VoucherModel();
            $must = ['type_id'];
            $params['cdkey'] = $this->generateCode();
//            $rs = $this->checkInput($must, $params);
//            if ($rs != false) {
//                return json_encode($rs, JSON_UNESCAPED_UNICODE);
//            }
            if (isset($params['key'])) {
                $params['`key`'] = $params['key'];
                unset($params['key']);
            }
            $params['merchant_id'] = yii::$app->session['uid'];
            //获取优惠券类型
            $type = new VoucherTypeModel();
            $typedata['id'] = $params['type_id'];
            $voutype = $type->find($typedata);
            if ($voutype['status'] == 204) {
                $array = ['status' => 400, 'message' => '该type_id不存在',];
                return json_encode($rs, JSON_UNESCAPED_UNICODE);
            }

            //优惠券新增参数
            $vdata['cdkey'] = $params['cdkey'];
            $vdata['type_id'] = $params['type_id'];
            $vdata['type_name'] = $voutype['data']['name'];
            $vdata['status'] = $params['status'];
            $vdata['start_time'] = time();
            $vdata['end_time'] = ($voutype['data']['days'] * 24 * 60 * 60) + ($vdata['start_time']);
            $vdata['is_exchange'] = 0;
            $vdata['merchant_id'] = $params['merchant_id'];
            try {
                $vdata['`key`'] = $params['`key`'];
                $vdata['is_used'] = 0;
                $vdata['price'] = $voutype['data']['price'];
                $vdata['full_price'] = $voutype['data']['full_price'];
                //开始事务
                $transaction = Yii::$app->db->beginTransaction();
                $array = $voucher->add($vdata);
                //更新优惠券个数
                $typeparams['send_count'] = $voutype['data']['send_count'] + 1;
                $typeparams['id'] = $params['type_id'];
                $type->update($typeparams);
                $transaction->commit(); //只有执行了commit(),对于上面数据库的操作才会真正执行
            } catch (Exception $e) {
                $transaction->rollBack(); //回滚
                return result(500, "添加失败");
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $params['cdkey'] = $id;
            $voucher = new VoucherModel();
            if (isset($params['key'])) {
                $params['`key`'] = $params['key'];
                unset($params['key']);
            }
            $params['merchant_id'] = yii::$app->session['uid'];
            if (!isset($params['cdkey'])) {
                return result(400, "缺少参数 cdkey");
            } else {
                if (isset($params['is_used'])) {
                    //params 参数值设置
                    $params['update_time'] = time();
                }
                if (isset($params['type_id'])) {
                    //获取优惠券类型
                    //  if($params['type_id']){}
                    $type = new VoucherTypeModel();
                    $typedata['id'] = $params['type_id'];
                    $voutype = $type->find($typedata);
                    $params['price'] = $voutype['data']['price'];
                    $params['full_price'] = $voutype['data']['full_price'];
                    $params['type_name'] = $voutype['data']['name'];
                }
                $array = $voucher->update($params);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionDelete($id) {
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $voucher = new VoucherModel();
            if (isset($params['key'])) {
                $params['`key`'] = $params['key'];
                unset($params['key']);
            }
            $params['merchant_id'] = yii::$app->session['uid'];
            $params['cdkey'] = $id;
            if (!isset($params['cdkey'])) {
                return result(400, "缺少参数 id");
            } else {
                $array = $voucher->delete($params);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }


    public function actionPack(){
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            
            
            $must = ['list', 'key'];
            $rs = $this->checkInput($must, $params);
            $params['status'] = 1;

            if ($rs != false) {
                return $rs;
            }
            
            $shopUserModel = new UserModel();
            if(isset($params['money1'])){
            	if($params['money1']!=""){
            		$data["money>={$params['money1']}"] = null;
            	}
                unset($params['money1']);
            }
            if(isset($params['money2'])){
            	if($params['money2']!=""){
            		$data["money<={$params['money2']}"] = null;
            	}
                unset($params['money2']);
            }
            $data['`key`']=$params['key'];
            $data['merchant_id']=yii::$app->session['uid'];
            if($params['all']==1){
                unset($data["money1>={$params['money1']}"]);
                unset($data["money2<={$params['money2']}"]);
            }
           
            $shopUser = $shopUserModel->findall($data);
            
            if($shopUser['status']!=200){
                return $shopUser;
            }
            
          //  $params['list'] = [27,40];
            //查询所有满足条件的用户
             for($j=0;$j<count($params['list']);$j++){
                    $voucherTypeModel = new VoucherTypeModel();
                    $data = $voucherTypeModel->find(['id'=>$params['voucher_type'][$j],'key'=>$params['key'],'merchant_id'=>yii::$app->session['uid']]);
                    if($data['status']!=200){
                        return result(500,'系统错误');
                    }
                	if($data['data']['days']==""||$data['data']['days']==0){
                		$data['data']['days'] = 30;
                	}
                    $vdata[$j]['type_id'] = $data['data']['id'];
                    $vdata[$j]['type_name'] = $data['data']['name'];
                    $vdata[$j]['status'] = 1;
                    $vdata[$j]['start_time'] = time();
                    $vdata[$j]['end_time'] = ($data['data']['days'] * 24 * 60 * 60) + ($vdata[$j]['start_time']);
                    $vdata[$j]['is_exchange'] = 0;
                    $vdata[$j]['merchant_id'] = yii::$app->session['uid'];
                    $vdata[$j]['`key`'] = $params['key'];
                    $vdata[$j]['is_used'] = 0;
                    $vdata[$j]['price'] = $data['data']['price'];
                    $vdata[$j]['full_price'] = $data['data']['full_price'];

             }
             $sql = "";
             $time = time();
               // $table = new TableModel();
            for($i=0;$i<count($shopUser['data']);$i++){
            	
            	for($j=0;$j<count($vdata);$j++){
            		$voucherModel = new VoucherModel();
            		$vdata[$j]['cdkey'] =$this->generateCode();
                    $vdata[$j]['user_id'] = $shopUser['data'][$i]['id'];
                    $sql = $sql."INSERT shop_voucher set type_id='{$vdata[$j]['type_id']}',type_name='{$vdata[$j]['type_name']}',status='1',start_time='{$vdata[$j]['start_time']}',end_time='{$vdata[$j]['end_time']}',is_exchange='0',merchant_id='{$vdata[$j]['merchant_id']}',`key`='{$vdata[$j]['`key`']}',is_used='0',price=' {$vdata[$j]['price']}',full_price='{$vdata[$j]['full_price']}',user_id='{$vdata[$j]['user_id']}',cdkey='{$vdata[$j]['cdkey']}',create_time='{$time}';";

            	}
            }
			Yii::$app->db->createCommand($sql)->execute();
            return result(200, "请求成功");
        } else {
            return result(500, "请求方式错误");
        }
    }

}
