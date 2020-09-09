<?php

namespace app\controllers\merchant\system;

use app\models\merchant\system\OperationRecordModel;
use app\models\merchant\user\MerchantModel;
use yii;
use yii\web\MerchantController;
use yii\db\Exception;
use app\models\merchant\system\GroupModel;
use app\models\admin\user\RuleModel;

/**
 * 角色接口控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class GroupController extends MerchantController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * 地址:/admin/group/index 默认访问
     * @throws Exception if the model cannot be found
     * @return array
     */

    public function actionAll() {
        $request = yii::$app->request; //获取 request 对象
        $params = $request->get(); //获取地址栏参数

        $rule = new RuleModel();
        unset($params['key']);
        $params['type'] = 2;
        $array = $rule->findall($params);
        return $array;
    }

    public function actionList() {

        if (yii::$app->request->isGet) {
            $request = yii::$app->request;
            $params = $request->get(); //获取地址栏参数
            $group = new GroupModel();
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $group->findall($params);
            return $array;
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    public function actionRules() {
        $request = yii::$app->request; //获取 request 对象
        $params = $request->get(); //获取地址栏参数
        $rule = new RuleModel();
        unset($params['key']);
        $params['type'] = 2;
        $params['pid'] = 0;
        $array = $rule->findall($params);
        $list = [];
        if($array['status'] == 200){
            foreach ($array['data'] as $key=>$val){
                $list[$key]['id'] = $val['id'];
                $list[$key]['title'] = $val['title'];
                $list[$key]['one_list'] = [];
                $one_list = $rule->findall(['pid'=>$val['id'],'type'=>2]);
                if($one_list['status'] == 200){
                    foreach ($one_list['data'] as $one_key=>$one_val){
                        $list[$key]['one_list'][$one_key]['id'] = $one_val['id'];
                        $list[$key]['one_list'][$one_key]['title'] = $one_val['title'];
                        $list[$key]['one_list'][$one_key]['two_list'] = [];
                        $two_list = $rule->findall(['pid'=>$one_val['id'],'type'=>2]);
                        if($two_list['status'] == 200){
                            foreach ($two_list['data'] as $two_key=>$two_val){
                                $list[$key]['one_list'][$one_key]['two_list'][$two_key]['id'] = $two_val['id'];
                                $list[$key]['one_list'][$one_key]['two_list'][$two_key]['title'] = $two_val['title'];
                            }
                        }
                    }
                }
            }
        }
        if($list){
            return result(200, "请求成功",$list);
        }else{
            return result(500, "请求失败");
        }
    }

    public function actionSingle($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request;
            $params = $request->get(); //获取地址栏参数

            $group = new GroupModel();
            $params['id'] = $id;
            if (!isset($params['id'])) {
                $array = ['status' => 400, 'message' => '缺少参数 id',];
            } else {
                $array = $group->find($params);
            }
            return $array;
        }
    }

    public function actionRule($id) {

        if (yii::$app->request->isGet) {
            $request = yii::$app->request;
            $params = $request->get(); //获取地址栏参数

            $group = new GroupModel();
            $params['id'] = $id;
            if (!isset($params['id'])) {
                $array = ['status' => 400, 'message' => '缺少参数 角色id  group_id',];
            } else {
                $array = $group->rule($params);
            }
            return $array;
        }
    }

    public function actionUsers($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request;
            $params = $request->get(); //获取地址栏参数

            $group = new GroupModel();
            $params['group_id'] = $id;
            if (!isset($params['group_id'])) {
                $array = ['status' => 400, 'message' => '缺少参数 group_id',];
            } else {
                $array = $group->users($params);
            }
            return $array;
        }
    }

    public function actionAdd() {
        $request = yii::$app->request; //获取 request 对象
        $params = $request->bodyParams; //获取body传参
        $group = new GroupModel();
        $must = ['title'];
        $rs = $this->checkInput($must, $params);
        $params['merchant_id'] = yii::$app->session['uid'];
        if ($rs != false) {
            return $rs;
        }
        $array = $group->add($params);
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
            $operationRecordData['module_name'] = '角色管理';
            $operationRecordModel->do_add($operationRecordData);
        }
        return $array;
    }

    public function actionUpdate($id) {
        $request = yii::$app->request; //获取 request 对象
        $params = $request->bodyParams; //获取body传参
        $group = new GroupModel();
        $params['id'] = $id;
        $params['`key`'] = $params['key'];
        unset($params['key']);
        $params['merchant_id'] = yii::$app->session['uid'];
        $array = $group->update($params);
        if ($array['status'] == 200){
            //添加操作记录
            $operationRecordModel = new OperationRecordModel();
            $operationRecordData['key'] = $params['`key`'];
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
            $operationRecordData['module_name'] = '角色管理';
            $operationRecordModel->do_add($operationRecordData);
        }
        return $array;
    }

    public function actionDelete($id) {
        $request = yii::$app->request; //获取 request 对象
        $params = $request->bodyParams; //获取body传参

        $group = new GroupModel();
        $params['id'] = $id;
        $params['`key`'] = $params['key'];
        unset($params['key']);

        if (!isset($params['id'])) {
            $array = ['status' => 400, 'message' => '缺少参数 id',];
        } else {
            $array = $group->delete($params);
        }
        if ($array['status'] == 200){
            //添加操作记录
            $operationRecordModel = new OperationRecordModel();
            $operationRecordData['key'] = $params['`key`'];
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
            $operationRecordData['module_name'] = '角色管理';
            $operationRecordModel->do_add($operationRecordData);
        }
        return $array;
    }

}
