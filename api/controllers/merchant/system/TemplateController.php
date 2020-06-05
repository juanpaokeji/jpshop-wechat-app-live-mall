<?php

namespace app\controllers\merchant\system;

use app\models\merchant\system\OperationRecordModel;
use yii;
use yii\web\MerchantController;
use EasyWeChat\Factory;
use app\models\system\SystemMiniTemplateModel;
use app\models\system\SystemMerchantMiniTemplateModel;

class TemplateController extends MerchantController
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
    public $config = [
        'app_id' => 'wx8df3a6f4a4f9ec54',
        'secret' => '7188287cd30aa902d5933654fed60559',
        'token' => 'juanPao',
        'aes_key' => '9ILejPm7rpu5kJykkY13oHMO80bYJkNbQfCvL3otaWA',
    ];

//    public function actionList() {
//        if (yii::$app->request->isGet) {
//            $request = yii::$app->request; //获取 request 对象
//            $params = $request->get(); //获取地址栏参数
//            $config = $this->getSystemConfig($params['key'], "miniprogram");
//            $openPlatform = Factory::openPlatform($this->config);
//            // 代小程序实现业务
//            $miniProgram = $openPlatform->miniProgram($config['app_id'], $config['refresh_token']);
//
//            $adminModel = new SystemMiniTemplateModel();
//            $adminTemp = $adminModel->do_select([]);
//
//            $merchantModel = new SystemMerchantMiniTemplateModel();
//
//            $minitemp = $miniProgram->template_message->getTemplates(0, 20);
//
//            for ($i = 0; $i < count($minitemp); $i++) {
//                $miniProgram->template_message->delete($minitemp[$i]['id']);
//            }
//            $merchantModel->do_del(['merchant_id' => yii::$app->session['uid']]);
//            for ($i = 0; $i < count($adminTemp); $i++) {
//                $list = explode(",", $adminTemp['data'][$i]['keyword_id_list']);
//                $res = $miniProgram->template_message->add($adminTemp['data'][$i]['keyword_list_id'], $list);
//                $mData['name'] = $adminTemp['data'][$i]['name'];
//                $mData['key'] = $params['key'];
//                $mData['merchant_id'] = yii::$app->session['uid'];
//                $mData['system_mini_template_id'] = $adminTemp['data'][$i]['id'];
//                $mData['template_id'] = $res['template_id'];
//                $mData['template_purpose'] = $adminTemp['data'][$i]['purpose'];
//                $mData['conten'] = "" . "\T" . "" . "\n";
//                $mData['status'] = 1;
//                $merchantModel->do_add(['template_id' => $res['template_id']]);
//            }
//            return $array;
//        } else {
//            return result(500, "请求方式错误");
//        }
//    }

    public function actionList()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $mTemp = new SystemMerchantMiniTemplateModel();
            $must = ['key', 'purpose'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $data['template_purpose'] = $params['purpose'];
            $data['merchant_id'] = yii::$app->session['uid'];
            $data['key'] = $params['key'];
            $array = $mTemp->do_select($data);

            $config = $this->getSystemConfig($params['key'], "miniprogram");
            if ($config == false) {
                return result(500, "小程序信息错误");
            }

            if ($params['purpose'] == "message") {
                if ($array['status'] == 200) {
                    for ($i = 0; $i < count($array['data']); $i++) {
                        $array['data'][$i]['miniprogram_name'] = $config['nick_name'];
                        $array['data'][$i]['head_img'] = $config['head_img'];
                        $content = json_decode($array['data'][$i]['content'], true);
                        $array['data'][$i]['template_params'] = $content['template_params'];
                    }
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


            $aTemp = new SystemMiniTemplateModel();
            $adminTemp = $aTemp->do_select(['status' => 1, 'purpose' => 'order']);
// 代小程序实现业务
            $config = $this->getSystemConfig($params['key'], "miniprogram");

            if ($config == false) {
                return result(500, "小程序信息错误");
            }
            $openPlatform = Factory::openPlatform($this->config);
            $miniProgram = $openPlatform->miniProgram($config['app_id'], $config['refresh_token']);


            $merchantModel = new SystemMerchantMiniTemplateModel();
            $mTemp = new SystemMerchantMiniTemplateModel();
            $temp = $mTemp->do_select(['merchant_id' => yii::$app->session['uid'], 'key' => $params['key']]);
            if ($temp['status'] == 200) {
                for ($i = 0; $i < count($temp['data']); $i++) {
                    $miniProgram->template_message->delete($temp['data'][$i]['template_id']);
                }
                $merchantModel->do_del(['merchant_id' => yii::$app->session['uid'], 'key' => $params['key']]);
            }
//
            if ($adminTemp['status'] == 200) {
                $num = count($adminTemp['data']);
                for ($i = 0; $i < $num; $i++) {
                    $mTemp = new SystemMerchantMiniTemplateModel();
                    $adminTemp['data'][$i]['keyword_id_list'] = substr($adminTemp['data'][$i]['keyword_id_list'], 0, strlen($adminTemp['data'][$i]['keyword_id_list']) - 1);
                    $list = explode(",", $adminTemp['data'][$i]['keyword_id_list']);

                    $res = $miniProgram->template_message->add($adminTemp['data'][$i]['keyword_list_id'], $list);
                    $mData['name'] = $adminTemp['data'][$i]['name'];
                    $mData['key'] = $params['key'];
                    $mData['merchant_id'] = yii::$app->session['uid'];
                    $mData['system_mini_template_id'] = $adminTemp['data'][$i]['id'];
                    $mData['template_id'] = $res['template_id'];
                    $mData['template_purpose'] = $adminTemp['data'][$i]['purpose'];
// $mData['conten'] = "" . "\T" . "" . "\n";
                    $mData['status'] = 1;
                    $res = $mTemp->do_add($mData);
                    if ($res['status'] == 200){
                        //添加操作记录
                        $operationRecordModel = new OperationRecordModel();
                        $operationRecordData['key'] = $params['key'];
                        $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                        $operationRecordData['operation_type'] = '新增';
                        $operationRecordData['operation_id'] = $res['data'];
                        $operationRecordData['module_name'] = '模板信息';
                        $operationRecordModel->do_add($operationRecordData);
                    }
                }
            }
            return result(200, "请求成功");
//            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAll()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $Temp = new SystemMiniTemplateModel();
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $config = $this->getSystemConfig($params['key'], "miniprogram");
            if ($config == false) {
                return result(500, "小程序信息错误");
            }


            $array = $Temp->do_select(['purpose' => 'message']);
            if ($array['status'] == 200) {
                for ($i = 0; $i < count($array['data']); $i++) {
                    $array['data'][$i]['miniprogram_name'] = $config['nick_name'];
                    $array['data'][$i]['head_img'] = $config['head_img'];
                    $array['data'][$i]['keyword_list'] = json_decode($array['data'][$i]['keyword_list'], true);
                    $num = explode(",", substr($array['data'][$i]['keyword_id_list'], 0, strlen($array['data'][$i]['keyword_id_list']) - 1));
                    $list = array();
                    for ($j = 0; $j < count($array['data'][$i]['keyword_list']); $j++) {
                        for ($k = 0; $k < count($num); $k++) {
                            if ($num[$k] == $array['data'][$i]['keyword_list'][$j]['keyword_id']) {
                                $list[] = $array['data'][$i]['keyword_list'][$j];
                            }
                        }
                    }
                    $array['data'][$i]['keyword_list'] = $list;
                }
            }
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
            $Temp = new SystemMerchantMiniTemplateModel();
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $config = $this->getSystemConfig($params['key'], "miniprogram");
            if ($config == false) {
                return result(500, "小程序信息错误");
            }

            $array = $Temp->do_one(['template_purpose' => 'message', 'id' => $id]);
            if ($array['status'] == 200) {
                $array['data']['miniprogram_name'] = $config['nick_name'];
                $array['data']['head_img'] = $config['head_img'];
                $content = json_decode($array['data']['content'], true);
                $array['data']['template_params'] = $content['template_params'];
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionOne()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $Temp = new SystemMiniTemplateModel();
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $config = $this->getSystemConfig($params['key'], "miniprogram");
            if ($config == false) {
                return result(500, "小程序信息错误");
            }

            $array = $Temp->do_one(['purpose' => 'message']);
            if ($array['status'] == 200) {
                $array['data']['miniprogram_name'] = $config['nick_name'];
                $array['data']['head_img'] = $config['head_img'];
                $array['data']['keyword_list'] = json_decode($array['data']['keyword_list'], true);
                $num = explode(",", substr($array['data']['keyword_id_list'], 0, strlen($array['data']['keyword_id_list']) - 1));
                $list = array();
                for ($j = 0; $j < count($array['data']['keyword_list']); $j++) {
                    for ($k = 0; $k < count($num); $k++) {
                        if ($num[$k] == $array['data']['keyword_list'][$j]['keyword_id']) {
                            $list[] = $array['data']['keyword_list'][$j];
                        }
                    }
                }
                $array['data']['keyword_list'] = $list;
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionMessage()
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $must = ['key', 'template_id'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $config = $this->getSystemConfig($params['key'], "miniprogram");
            $openPlatform = Factory::openPlatform($this->config);
            $miniProgram = $openPlatform->miniProgram($config['app_id'], $config['refresh_token']);

            if ($config == false) {
                return result(500, "小程序信息错误");
            }

            $aTemp = new SystemMiniTemplateModel();
            $adminTemp = $aTemp->do_one(['status' => 1, 'purpose' => 'message', 'id' => $params['id']]);
            if ($adminTemp['status'] != 200) {
                return $adminTemp;
            }
//            $mTemp = new SystemMerchantMiniTemplateModel();

            if ($params['template_id'] == 1) {
                $adminTemp['data']['keyword_id_list'] = substr($adminTemp['data']['keyword_id_list'], 0, strlen($adminTemp['data']['keyword_id_list']) - 1);
                $list = explode(",", $adminTemp['data']['keyword_id_list']);
                $res = $miniProgram->template_message->add($adminTemp['data']['keyword_list_id'], $list);
                if ($res['errcode'] == 45100) {
                    return result(500, "消息模板创建已超出限制，请去小程序平台删除");
                }
                $mData['template_id'] = $res['template_id'];
            } else {
                $mData['template_id'] = $params['template_id'];
            }
            //die();
            $mTemp = new SystemMerchantMiniTemplateModel();
            $mData['name'] = $params['title'];
            $mData['key'] = $params['key'];
            $mData['scope'] = $params['scope'];
            $mData['scope_type'] = $params['scope_type'];
            $mData['merchant_id'] = yii::$app->session['uid'];
            $mData['system_mini_template_id'] = $adminTemp['data']['id'];
            $mData['template_purpose'] = $adminTemp['data']['purpose'];
            $content['page'] = $params['page'];
            $content['template_params'] = $params['template_params'];
            $mData['content'] = json_encode($content, JSON_UNESCAPED_UNICODE);
            $mData['status'] = 1;
            $mRes = $mTemp->do_add($mData);
            if (isset($params['type'])) {
                if ($params['type'] == 1) {
                    $userModel = new \app\models\shop\UserModel;
                    $users = $userModel->findall(['status' => 1]);
                    if ($users['status'] != 200) {
                        return $users;
                    }
                    $tempAccess = new \app\models\system\SystemMerchantTemplateMiniAccessModel();
                    $taData = array(
                        'key' => $params['key'],
                        'merchant_id' => yii::$app->session['uid'],
                        'template_id' => $mData['template_id'],
                        'template_params' => json_encode($params['template_params'], JSON_UNESCAPED_UNICODE),
                        'page' => json_encode($params['page'], JSON_UNESCAPED_UNICODE),
                        'template_purpose' => 'message',
                        'status' => '-1',
                    );
                    $tempAccess->do_add($taData);
                }
                $mTemp->do_update(['id' => $mRes['data']], ['status' => 2]);
            }
            //添加操作记录
            $operationRecordModel = new OperationRecordModel();
            $operationRecordData['key'] = $params['key'];
            $operationRecordData['merchant_id'] = yii::$app->session['uid'];
            $operationRecordData['operation_type'] = '新增';
            $operationRecordData['operation_id'] = $mRes['data'];
            $operationRecordData['module_name'] = '模板信息';
            $operationRecordModel->do_add($operationRecordData);
            return result(200, "请求成功");
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSend()
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $mTemp = new SystemMerchantMiniTemplateModel();
            $mRes = $mTemp->do_one(['id' => $params['id'], 'status' => 1]);
            if ($mRes['status'] != 200) {
                return $mRes;
            }
            $userModel = new \app\models\shop\UserModel;
            $users = $userModel->findall(['status' => 1]);
            if ($users['status'] != 200) {
                return $users;
            }
            $content = json_decode($mRes['data']['content'], true);
            $tempAccess = new \app\models\system\SystemMerchantTemplateMiniAccessModel();
            $taData = array(
                'key' => $params['key'],
                'merchant_id' => yii::$app->session['uid'],
                'template_id' => $mRes['data']['template_id'],
                'template_params' => json_encode($content['template_params'], JSON_UNESCAPED_UNICODE),
                'page' => json_encode($content['page'], JSON_UNESCAPED_UNICODE),
                'template_purpose' => 'message',
                'status' => '-1',
            );
            $res = $tempAccess->do_add($taData);

            if ($res['status'] == 200){
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['key'];
                $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                $operationRecordData['operation_type'] = '新增';
                $operationRecordData['operation_id'] = $res['data'];
                $operationRecordData['module_name'] = '模板信息';
                $operationRecordModel->do_add($operationRecordData);
            }

            return $res;
        } else {
            return result(500, "请求方式错误");
        }


    }

    public function actionDelete($id)
    {
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new SystemMerchantMiniTemplateModel();
            $params['id'] = $id;
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->do_delete($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
