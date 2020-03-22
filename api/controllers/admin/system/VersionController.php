<?php

namespace app\controllers\admin\system;

use app\models\merchant\partnerUser\PartnerUserModel;
use app\models\merchant\system\MerchantComboAccessModel;
use yii;
use yii\db\Exception;
use yii\web\CommonController;
use app\models\system\SystemAppVersionModel;
use app\models\system\SystemAppAccessVersionModel;
use app\models\merchant\app\AppAccessModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class VersionController extends CommonController {

    public function behaviors() {
        return [
            'token' => [
                'class' => 'yii\filters\TokenFilter', //调用过滤器
                //    'only' => ['single'], //指定控制器应用到哪些动作
                'except' => ['all','up-partner-number'], //指定控制器不应用到哪些动作
            ]
        ];
    }

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function actionList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new SystemAppVersionModel();
            $array = $model->findall($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new SystemAppVersionModel();
            $params['id'] = $id;
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $array = $model->find($params);
                return $array;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new SystemAppVersionModel();
            //设置类目 参数
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
            $model = new SystemAppVersionModel();
            $params['id'] = $id;
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $array = $model->update($params);
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
            $model = new SystemAppVersionModel();
            $params['id'] = $id;
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $array = $model->delete($params);
                return $array;
            }
            return result(200, "请求成功");
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAll() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new SystemAppAccessVersionModel();
            $params['merchant_id'] = $params['merchant_id'];
            $version = $model->findalls($params);

            $appModel = new AppAccessModel();
            $comboAccess = new MerchantComboAccessModel();
            $app = $appModel->findall(['merchant_id' => $params['merchant_id'], 'fields' => 'id,+app_id,name,`key`,pic_url,merchant_id,copyright,create_time,update_time,open_partner,partner_number ']);
//            var_dump($app);die;
            if ($version['status'] == 200 && $app['status'] == 200) {
                for ($i = 0; $i < count($app['data']); $i++) {
                    $app['data'][$i]['number'] = "";

                    $app['data'][$i]['is_release'] = $this->getSystemConfig($app['data'][$i]['key'], "miniprogram");
                    if ($app['data'][$i]['is_release'] != false) {
                        $app['data'][$i]['is_release'] = true;
                    }
                    //   $app['data'][$i]['is_release'] = false;
                    $comboData['key'] = $app['data'][$i]['key'];
                    $comboData['limit'] = 1;
                    $comboData['orderby'] = "id desc";
                    $comboInfo = $comboAccess->do_one($comboData);
                    if ($comboInfo['status'] == 200){
                        $app['data'][$i]['validity_time'] = date("Y-m-d",$comboInfo['data']['validity_time']);
                    } else {
                        $app['data'][$i]['validity_time'] = '';
                    }
                    for ($j = 0; $j < count($version['data']); $j++) {
                        if ($app['data'][$i]['key'] == $version['data'][$j]['key'] && $version['data'][$j]['status'] == 6) {
                            $app['data'][$i]['number'] = $version['data'][$j]['number'];
                        }
                        if ($app['data'][$i]['key'] == $version['data'][$j]['key']) {
                            $app['data'][$i]['version'] = $version['data'][$j]['number'];
                        }
                    }
                }
            }
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return $app;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpd($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $app = new \app\models\admin\app\AppAccessModel();
            $data['id'] = $id;
            if (!isset($data['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $data['copyright'] = $params['copyright'];
                $array = $app->update($data);
                return $array;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 修改应用可以创建合伙人数量
     * @param $id
     * @return array
     * @throws Exception
     */
    public function actionUpPartnerNumber($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $app = new \app\models\admin\app\AppAccessModel();
            $data['id'] = $id;
            if (!isset($data['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $info = $app->find(['id' => $id, 'open_partner' => 1]);
                if($info['status'] != 200){
                    return result(400, "商户已关闭合伙人设置");
                }
                $data['partner_number'] = $params['partner_number'];
                $array = $app->update($data);
                return $array;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

}
