<?php

namespace app\controllers\merchant\shop;

use app\models\merchant\system\OperationRecordModel;
use app\models\merchant\user\MerchantModel;
use yii;
use yii\db\Exception;
use yii\web\MerchantController;
use app\models\shop\DecorationModel;
use app\models\core\Base64Model;
use app\models\core\CosModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class DecorationController extends MerchantController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * 地址:/admin/group/index 默认访问
     * @throws Exception if the model cannot be found
     * @return array
     */

    public function actionList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new DecorationModel();
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['`key`'] = $params['key'];
            unset($params['key']);

            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->findall($params);
            if ($array['status'] === 200) {
                for ($i = 0; $i < count($array['data']); $i++) {
                    $array['data'][$i]['info'] = json_decode($array['data'][$i]['info']);
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $category = new DecorationModel();
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['`key`'] = $params['key'];
            unset($params['key']);

            $params['merchant_id'] = yii::$app->session['uid'];
            $params['id'] = $id;
            $array = $category->find($params);
            if ($array['status'] === 200) {
                $array['data']['info'] = json_decode($array['data']['info']);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new DecorationModel();

            //设置类目 参数
            $must = ['name', 'pic_url', 'info', 'is_edit', 'key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $params['`key`'] = $params['key'];
            unset($params['key']);

            if (isset($params['pic_url'])) {
                $base = new Base64Model();
                $params['pic_url'] = $base->base64_image_content($params['pic_url'], "./uploads/shop/decoration");
                //将图片上传到cos
                $cos = new CosModel();
                $cosRes = $cos->putObject($params['pic_url']);
               
                if ($cosRes['status'] == 200) {
                    $url = $cosRes['data'];
                    unlink(Yii::getAlias('@webroot/') . $params['pic_url']);
                } else {
                  //  unlink(Yii::getAlias('@webroot/') . $params['pic_url']);
                  //  return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
                   $url = "http://".$_SERVER['HTTP_HOST']."/api/web/".$params['pic_url'];
                }
                $params['pic_url'] = $url;
            }
            $params['merchant_id'] = yii::$app->session['uid'];
           // $params['info'] = json_encode($params['info'], JSON_UNESCAPED_UNICODE);
            $array = $model->add($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new DecorationModel();
            $base = new Base64Model();
            $params['id'] = $id;
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['`key`'] = $params['key'];
            unset($params['key']);

            if (isset($params['pic_url'])) {
                if ($params['pic_url'] == "") {
                    unset($params['pic_url']);
                } else {
                    $str = creat_mulu("./uploads/shop/banner");
                    $params['pic_url'] = $base->base64_image_content($params['pic_url'], $str);
                    //将图片上传到cos
                    $cos = new CosModel();
                    $cosRes = $cos->putObject($params['pic_url']);
                    if ($cosRes['status']==200) {
                        $url = $cosRes['data'];
                    } else {
                        //unlink(Yii::getAlias('@webroot/') . $params['pic_url']);
                        //return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
                        $url = "http://".$_SERVER['HTTP_HOST']."/api/web/".$params['pic_url'];
                    }
                    $params['pic_url'] = $url;
                }
            }

            $params['merchant_id'] = yii::$app->session['uid'];
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
              //  $params['info'] = json_encode($params['info'], JSON_UNESCAPED_UNICODE);
                $array = $model->update($params);
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
                    $operationRecordData['module_name'] = '店铺装修';
                    $operationRecordModel->do_add($operationRecordData);
                }
                return $array;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionDelete($id) {
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new DecorationModel();
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
                $array = $model->delete($params);
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
                $operationRecordData['module_name'] = '店铺装修';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 更新状态，当前id设置未启用，其余都设置为未启用
     * @param type $id
     * @return type
     */
    public function actionIsenable($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new DecorationModel();
            $base = new Base64Model();
            $params['id'] = $id;
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['`key`'] = $params['key'];
            unset($params['key']);

            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $array = $model->isEnable($params);
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
                    $operationRecordData['module_name'] = '店铺装修';
                    $operationRecordModel->do_add($operationRecordData);
                }
                return $array;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 商户后台显示的系统模板库 只显示 status=1 和 is_enable=1 的数据
     * @return type
     */
    public function actionSystemlist() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数     
            if (isset($params['searchName'])) {
                if ($params['searchName'] != "") {
                    $params['name'] = ['like', "{$params['searchName']}"];
                }
                unset($params['searchName']);
            }
            $model = new \app\models\system\DecorationModel();
            $params['is_edit'] = 0; //未编辑
            $params['status'] = 1; //启用
            unset($params['key']);
            $array = $model->do_select($params);
            if ($array['status'] === 200) {
                for ($i = 0; $i < count($array['data']); $i++) {
                    $array['data'][$i]['info'] = json_decode($array['data'][$i]['info']);
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 选择系统模板库并添加到我的模板库
     * @return array
     * @throws Exception
     */
    public function actionAddsystem() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $must = ['key', 'template_id'];
            $model = new DecorationModel();
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['`key`'] = $params['key'];
            unset($params['key']);

            //首先，通过 id 获取系统模板库对应的数据，其次将获取到数据添加到 merchant_design 表中
            $sys_model = new \app\models\system\DecorationModel();
            $array = $sys_model->do_one(['id' => $params['template_id']]);
            if (!$array['status'] || $array['status'] !== 200) {
                return $array;
            }
            $params['merchant_id'] = yii::$app->session['uid'];
            //获取系统模板库的内容
            $data = $array['data'];
            $params['name'] = $data['name'];
            $params['pic_url'] = $data['pic_url'];
            $params['info'] = \GuzzleHttp\json_decode($data['info']);
            $array = $model->add($params);
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
                $operationRecordData['operation_id'] = $params['template_id'];
                $operationRecordData['module_name'] = '店铺装修';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
