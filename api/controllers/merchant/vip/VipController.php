<?php

namespace app\controllers\merchant\vip;

use app\models\merchant\app\AppAccessModel;
use app\models\merchant\system\OperationRecordModel;
use app\models\merchant\vip\UnpaidVipModel;
use app\models\merchant\vip\VipConfigModel;
use app\models\merchant\vip\VipModel;
use app\models\shop\VipAccessModel;
use yii;
use yii\base\Exception;
use yii\web\MerchantController;

/**
 * 会员卡配置 一个应用一个配置
 * @author  wmy
 * Class VipController
 * @package app\controllers\merchant\vip
 */
class VipController extends MerchantController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * 查询列表
     * @return array
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
            $model = new VipModel();
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->do_select($params);
            if($array['status'] == 200 ){
                foreach ($array['data'] as $key=>&$val){
                    if((int)$val['validity_time'] == 86400*7){
                        $val['validity_time'] = 1;
                        $val['validity_time_text'] = '一周';
                    }elseif ((int)$val['validity_time'] == 86400*30){
                        $val['validity_time'] = 2;
                        $val['validity_time_text'] = '一个月';
                    }elseif ((int)$val['validity_time'] == 86400*90){
                        $val['validity_time'] = 3;
                        $val['validity_time_text'] = '一个季度';
                    }elseif ((int)$val['validity_time'] == 86400*365){
                        $val['validity_time'] = 4;
                        $val['validity_time_text'] = '一年';
                    }
                }
                $accessModel = new VipAccessModel();
                $accessWhere['shop_vip_access.key'] = $params['key'];
                $accessWhere['shop_vip_access.merchant_id'] = yii::$app->session['uid'];
                $accessWhere['shop_vip_access.status'] = 1; //已付款
                $accessWhere['field'] = "shop_vip_access.vip_id,shop_vip_access.create_time,shop_user.nickname,shop_user.avatar,shop_user.phone,shop_user.vip_validity_time";
                $accessWhere['join'][] = ['left join', 'shop_user', 'shop_user.id = shop_vip_access.user_id'];
                $accessWhere['groupBy'] = "shop_vip_access.user_id";
                $accessWhere['limit'] = false;
                $userInfo = $accessModel->do_select($accessWhere);
                if ($userInfo['status'] == 200){
                    foreach ($array['data'] as $key=>&$val){
                        foreach ($userInfo['data'] as $k=>$v){
                            $v['vip_validity_time'] = date('Y-m-d H:i:s',$v['vip_validity_time']);
                            if ($v['vip_id'] == $val['id']){
                                $val['user_list'][] = $v;
                            }
                        }
                    }
                }
            }

            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 查询单条
     * @return array
     */
    public function actionOne() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            if(!$params['id']){
                return result(500, "缺少id");
            }
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $model = new VipModel();
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->one($params);
            if($array['status'] == 200){
                if((int)$array['data']['validity_time'] == 86400*7){
                    $array['data']['validity_time'] = 1;
                    $array['data']['validity_time_text'] = '一周';
                }elseif ((int)$array['data']['validity_time'] == 86400*30){
                    $array['data']['validity_time'] = 2;
                    $array['data']['validity_time_text'] = '一个月';
                }elseif ((int)$array['data']['validity_time'] == 86400*90){
                    $array['data']['validity_time'] = 3;
                    $array['data']['validity_time_text'] = '一个季度';
                }elseif ((int)$array['data']['validity_time'] == 86400*365){
                    $array['data']['validity_time'] = 4;
                    $array['data']['validity_time_text'] = '一年';
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 新增
     * @return array
     */
    public function actionAdd() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new VipModel();
            //设置类目 参数
            $must = ['validity_time', 'name', 'key', 'money'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            //校验配置是否创建
            $vip_config_model = new VipConfigModel();
            $where['key'] = $params['key'];
            $where['merchant_id'] = yii::$app->session['uid'];
            $info = $vip_config_model->one($where);
            if($info['status'] != 200){
                return result(500, "缺少VIP配置");
            }
            $params['merchant_id'] = yii::$app->session['uid'];
            if($params['validity_time'] == 1){ //一周
                $params['validity_time'] = 86400*7;
            }else if($params['validity_time'] == 2){ //一个月
                $params['validity_time'] = 86400*30;
            }else if($params['validity_time'] == 3){ // 三个月
                $params['validity_time'] = 86400*90;
            }else{
                $params['validity_time'] = 86400*365;
            }
            $array = $model->add($params);

            if ($array['status'] == 200){
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['key'];
                $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                $operationRecordData['operation_type'] = '新增';
                $operationRecordData['operation_id'] = $array['data'];
                $operationRecordData['module_name'] = '会员卡(付费)';
                $operationRecordModel->do_add($operationRecordData);
            }

            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 更新
     * @param $id
     * @return array
     */
    public function actionUpdate($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new VipModel();
            if(!$id){
                return result(400, "缺少参数 id");
            }
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $where['key'] = $params['key'];
            unset($params['key']);
            $where['merchant_id'] = yii::$app->session['uid'];
            //校验配置是否创建
            $vip_config_model = new VipConfigModel();
            $info = $vip_config_model->one($where);
            if($info['status'] != 200){
                return result(500, "缺少VIP配置");
            }
            $where['id'] = $id;
            if(isset($params['validity_time'])){
                if($params['validity_time'] == 1){ //一周
                    $params['validity_time'] = 86400*7;
                }else if($params['validity_time'] == 2){ //一个月
                    $params['validity_time'] = 86400*30;
                }else if($params['validity_time'] == 3){ // 三个月
                    $params['validity_time'] = 86400*90;
                }else{
                    $params['validity_time'] = 86400*365;
                }
            }
            $array = $model->do_update($where,$params);

            if ($array['status'] == 200) {
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $where['key'];
                $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                $operationRecordData['operation_type'] = '更新';
                $operationRecordData['operation_id'] = $id;
                $operationRecordData['module_name'] = '会员卡(付费)';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 删除
     * @param $id
     * @return array
     */
    public function actionDelete($id) {
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new VipModel();
            $params['id'] = $id;
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
               return $rs;
            }
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['uid'];
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $array = $model->do_delete($params);
            }

            if ($array['status'] == 200){
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['`key`'];
                $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                $operationRecordData['operation_type'] = '删除';
                $operationRecordData['operation_id'] = $id;
                $operationRecordData['module_name'] = '会员卡(付费)';
                $operationRecordModel->do_add($operationRecordData);
            }

            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 查询列表
     * @return array
     */
    public function actionUnpaidlist() {
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
            $model = new UnpaidVipModel();
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->do_select($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 查询单条
     * @return array
     */
    public function actionUnpaidone() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            if(!$params['id']){
                return result(500, "缺少id");
            }
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $model = new UnpaidVipModel();
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->do_one($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 新增
     * @return array
     */
    public function actionUnpaidadd() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new UnpaidVipModel();
            //设置类目 参数
            $must = ['key', 'name', 'min_score', 'discount_ratio', 'voucher_count', 'voucher_type_id', 'score_times'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['merchant_id'] = yii::$app->session['uid'];
            $where['key'] = $params['key'];
            $where['merchant_id'] = yii::$app->session['uid'];
            $where['min_score'] = $params['min_score'];
            $info = $model->do_one($where);
            if ($info['status'] == 200){
                return result(500, "该会员等级已存在");
            }
            $array = $model->do_add($params);
            if ($array['status'] == 200){
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['key'];
                $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                $operationRecordData['operation_type'] = '新增';
                $operationRecordData['operation_id'] = $array['data'];
                $operationRecordData['module_name'] = '会员卡(积分)';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 更新
     * @param $id
     * @return array
     */
    public function actionUnpaidupdate($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new UnpaidVipModel();
            if(!$id){
                return result(400, "缺少参数 id");
            }
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $where['key'] = $params['key'];
            unset($params['key']);
            $where['merchant_id'] = yii::$app->session['uid'];
            $where['id'] = $id;

            $transaction = yii::$app->db->beginTransaction();
            try {
                $array = $model->do_update($where,$params);

                if ($array['status'] == 200) {
                    //添加操作记录
                    $operationRecordModel = new OperationRecordModel();
                    $operationRecordData['key'] = $where['key'];
                    $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                    $operationRecordData['operation_type'] = '更新';
                    $operationRecordData['operation_id'] = $id;
                    $operationRecordData['module_name'] = '会员卡(积分)';
                    $operationRecordModel->do_add($operationRecordData);
                    $transaction->commit(); //只有执行了commit(),对于上面数据库的操作才会真正执行
                } else {
                    $transaction->rollBack(); //回滚
                    return result(500, "更新失败");
                }
                return $array;
            } catch (\yii\db\Exception $e) {
                $transaction->rollBack(); //回滚
                return result(500, "更新失败");
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 删除
     * @param $id
     * @return array
     */
    public function actionUnpaiddelete($id) {
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new UnpaidVipModel();
            $params['id'] = $id;
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $array = $model->do_delete($params);
            }
            if ($array['status'] == 200){
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['key'];
                $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                $operationRecordData['operation_type'] = '删除';
                $operationRecordData['operation_id'] = $id;
                $operationRecordData['module_name'] = '会员卡(积分)';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 查询积分、付费会员插件开关
     * @return array
     */
    public function actionPlugin() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $must = ['key','english_name'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $appAccessModel = new AppAccessModel();
            $appAccessInfo = $appAccessModel->find(['`key`' => $params['key'], 'merchant_id' => yii::$app->session['uid']]);
            if ($appAccessInfo['status'] == 200){
                $array['id'] = $appAccessInfo['data']['id'];
                if ($params['english_name'] == 'Vip_payment'){
                    if ($appAccessInfo['data']['user_vip'] == 1){
                        $array['is_open'] = '1';
                    }else{
                        $array['is_open'] = '0';
                    }
                } elseif ($params['english_name'] == 'Vip_integral'){
                    if ($appAccessInfo['data']['user_vip'] == 2){
                        $array['is_open'] = '1';
                    }else{
                        $array['is_open'] = '0';
                    }
                }
                return result(200, "请求成功",$array);
            } else {
                return $appAccessInfo;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 更新积分、付费会员插件开关
     * @return array
     */
    public function actionUpdateplugin($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $must = ['key','is_open','english_name'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $appAccessModel = new AppAccessModel();
            $where['id'] = $id;
            if ($params['english_name'] == 'Vip_payment'){
                if ($params['is_open'] == 1){
                    $where['user_vip'] = 1;
                }else{
                    $where['user_vip'] = 0;
                }
            } elseif ($params['english_name'] == 'Vip_integral'){
                if ($params['is_open'] == 1){
                    $where['user_vip'] = 2;
                }else{
                    $where['user_vip'] = 0;
                }
            }
            $res = $appAccessModel->update($where);

            if ($res['status'] == 200) {
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['key'];
                $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                $operationRecordData['operation_type'] = '更新';
                $operationRecordData['operation_id'] = $id;
                $operationRecordData['module_name'] = '会员总开关';
                $operationRecordModel->do_add($operationRecordData);
            }

            return $res;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
