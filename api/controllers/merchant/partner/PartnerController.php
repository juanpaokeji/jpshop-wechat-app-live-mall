<?php

namespace app\controllers\merchant\partner;

use app\models\merchant\app\AppAccessModel;
use app\models\merchant\partnerUser\PartnerUserModel;
use app\models\merchant\partnerUser\WithdrawModel;
use app\models\merchant\system\OperationRecordModel;
use app\models\system\SystemAreaModel;
use yii;
use yii\web\MerchantController;

/**
 *
 * @author  wmy
 * Class PartnerController
 * @package app\controllers\merchant\partner
 */
class PartnerController extends MerchantController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors()
    {
        return [
            'token' => [
                'class' => 'yii\filters\MerchantFilter', //调用过滤器
                'except' => ['areas'], //指定控制器不应用到哪些动作
            ]
        ];
    }

    /**
     * 查询列表
     * @return array
     * @throws yii\db\Exception
     */
    public function actionList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            if (isset($params['searchName'])) {
                if ($params['searchName'] != "") {
                    $params['name'] = ['like', "{$params['searchName']}"];
                }
                unset($params['searchName']);
            }
            $model = new PartnerUserModel();
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->do_select($params);
            if($array['status'] == 200){
                foreach ($array['data'] as &$val){
                    if($val['expired_time']){
                        $val['expired_time'] = date('Y-m-d', $val['expired_time']);
                    }
                }
                $array['partner_number'] = 0;
                $array['open_partner'] = 0;
                $appModel = new AppAccessModel();
                $info = $appModel->find(['key'=>$params['key']]);
                if($info['status'] == 200){
                    $array['partner_number'] = $info['data']['partner_number'];
                    $array['open_partner'] = $info['data']['open_partner'];
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 获取有效的合伙人列表
     * @return array
     * @throws yii\db\Exception
     */
    public function actionPartnerList(){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            //校验商户是否关闭合伙人设置
            $app = new \app\models\merchant\app\AppAccessModel();
            $appInfo = $app->find(['key' => Yii::$app->session['key'], 'open_partner' => 1]);
            if($appInfo['status'] == 200){
                $partnerModel = new PartnerUserModel();
                $wherePar['or'] = ['or',['>=', 'expired_time', time()],['=', 'expired_time', 0]];
                $wherePar['key'] = $params['key'];
                $wherePar['status'] = 1;
                $wherePar['limit'] = 50;
                $partnerInfo = $partnerModel->do_select($wherePar);
                if($partnerInfo['status'] == 200){
                    return result(200, "请求成功",$partnerInfo['data']);
                }
            }
            return result(500, "请求失败");
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 查询单条
     * @param $id
     * @return array
     * @throws yii\db\Exception
     */
    public function actionOne($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            if(!$id){
                return result(500, "缺少id");
            }
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $model = new PartnerUserModel();
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->one($params);
            if($array['status'] == 200){
                if($array['data']['expired_time']){
                    $array['data']['expired_time'] = date('Y-m-d', $array['data']['expired_time']);
                }
                $array['data']['partner_number'] = 0;
                $array['data']['open_partner'] = 0;
                $appModel = new AppAccessModel();
                $info = $appModel->find(['key'=>$params['key']]);
                if($info['status'] == 200){
                    $array['data']['partner_number'] = $info['data']['partner_number'];
                    $array['data']['open_partner'] = $info['data']['open_partner'];
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * @return array
     * @throws yii\db\Exception
     */
    public function actionAdd() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new PartnerUserModel();
            //设置类目 参数
            $must = ['account', 'key', 'location'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            //校验账号是否被注册过
            $info = $model->one(['account' => $params['account']]);
            if($info['status'] == 200){
                return result(500, "此账号已被使用！");
            }
            $params['merchant_id'] = yii::$app->session['uid'];
            //解析地址获取市和区
            $result = $model->getAddrGD($params['location'],0);
            if(!$result){
                return result(500, "地址解析失败！");
            }
            //检验该地址是否已经被注册
            $arr = $model->one(['adcode' => $result,'key'=>$params['key']]);
            if($arr['status'] == 200){
                return result(500, "此区域已被注册！");
            }
            //校验当前数量已被创建多少个是否超出限制
            $total = $model->get_count(['key' => $params['key']]);
            $appModel = new AppAccessModel();
            $appInfo = $appModel->find(['`key`' => $params['key']]);
            if($appInfo['status'] != 200){
                return result(500, "数据出错了！");
            }
            if($total >= $appInfo['data']['partner_number']){
                return result(500, "合伙人数量已超出限制请联系卷泡！");
            }
            if(isset($params['expired_time']) && !empty($params['expired_time'])){
                $params['expired_time'] = strtotime($params['expired_time']);
            }else{
                $params['expired_time'] = 0;
            }
            $params['salt'] = $this->get_randomstr(32);
            $params['password'] = md5($params['account'] . $params['salt']);
            $params['adcode'] = $result;
            $array = $model->add($params);
            if ($array['status'] == 200){
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['key'];
                $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                $operationRecordData['operation_type'] = '新增';
                $operationRecordData['operation_id'] = $array['data'];
                $operationRecordData['module_name'] = '合伙人列表';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 更新密码
     * @return array
     */
    public function actionRestPassword() {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取地址栏参数
            $must = ['account', 'key','password'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $model = new PartnerUserModel();
            $params['salt'] = $this->get_randomstr(32);
            $data = [
                'password' => md5($params['password'] . $params['salt']),
                'salt' => $params['salt'],
            ];
            $array = $model->do_update(['account'=>$params['account'],'key'=>$params['key']],$data);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 更新合伙人信息
     * @param $id
     * @return array
     */
    public function actionUpdate($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取地址栏参数
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            if(isset($params['expired_time']) && !empty($params['expired_time'])){
                $params['expired_time'] = strtotime($params['expired_time']);
            }else{
                $params['expired_time'] = 0;
            }
            $model = new PartnerUserModel();
            $array = $model->do_update(['id'=>$id],$params);
            if ($array['status'] == 200){
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['key'];
                $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                $operationRecordData['operation_type'] = '更新';
                $operationRecordData['operation_id'] = $id;
                $operationRecordData['module_name'] = '合伙人列表';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 开启合伙人设置
     * @return array
     * @throws yii\db\Exception
     */
    public function actionOpenPartner() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取地址栏参数
            $must = ['key','open_partner'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $model = new AppAccessModel();
            $where['`key`'] = $params['key'];
            $where['open_partner'] = $params['open_partner'];
            $where['partner_handling_fee'] = $params['partner_handling_fee'];
            $array = $model->update($where);
            if ($array['status'] == 200){
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['key'];
                $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                $operationRecordData['operation_type'] = '更新';
                $operationRecordData['operation_id'] = $params['key'];
                $operationRecordData['module_name'] = '合伙人设置';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * @return array
     * @throws yii\db\Exception
     */
    public function actionAreas() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            if(!isset($params['name']) || empty($params['name'])){
                return result(500, "缺少参数");
            }
            $model = new SystemAreaModel();
            $array = $model->do_select(['level'=>3,'name'=>10000]);
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 提现记录
     * @return array
     */
    public function actionWithdrawList(){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $withdrawModel = new WithdrawModel();
            $list = $withdrawModel->do_select(['key'=>$params['key'],'merchant_id'=>yii::$app->session['uid']]);
            if($list['status'] == 200){
                foreach ($list['data'] as &$val){
                    $val['account'] = '';
                    $val['partner_name'] = '';
                    if($val['partner_id']){
                        $partnerModel = new PartnerUserModel();
                        $partnerInfo = $partnerModel->one(['id' => $val['partner_id']]);
                        if($partnerInfo['status'] == 200){
                            $val['account'] = $partnerInfo['data']['account'];
                            $val['partner_name'] = $partnerInfo['data']['name'];
                        }
                    }
                }
            }
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return $list;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 审核提现记录
     * @param $id
     * @return array
     * @throws yii\db\Exception
     */
    public function actionWithdraw($id){
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取地址栏参数
            if(!$id){
                return result(500, "缺少参数");
            }
            $withdrawModel = new WithdrawModel();
            $info = $withdrawModel->one(['id' => $id, 'status' => 0]);
            if($info['status'] != 200){
                return result(500, "数据错误！");
            }
            $must = ['status'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
           // $data['review_name'] = $params['review_name'] ?? '';
            $data['status'] = $params['status'];
            $data['remark'] = $params['remark'] ?? '';
            $appModel = new AppAccessModel();
            $appInfo = $appModel->find(['`key`' => $params['key']]);
            if($appInfo['status'] != 200){
                return result(500, "数据出错了！");
            }
            $data['real_money'] = bcsub($info['data']['apply_money'],bcmul($info['data']['apply_money'],$appInfo['data']['partner_handling_fee'],2),2);
            $transaction = yii::$app->db->beginTransaction();
            $res =  $withdrawModel->do_update(['id' => $id], $data);
            if($res['status'] == 200){
                $ids = $info['data']['ids'];
                if($params['status'] == 1){
                    $is_partner_withdraw = 2;
                }else{
                    $is_partner_withdraw = 0;
                }
                $sql = "update shop_order_group set is_partner_withdraw = {$is_partner_withdraw} where id in ($ids)";
                $res = Yii::$app->db->createCommand($sql)->execute();
                if($res){
                    $transaction->commit();
                    return result(500, "请求成功");
                };
            }
        } else {
            return result(500, "请求方式错误");
        }
    }
}
