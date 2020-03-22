<?php

namespace app\controllers\admin\app;

use yii;
use yii\web\CommonController;
use yii\db\Exception;
use app\models\admin\app\ComboModel;
use app\models\core\Base64Model;
use app\models\core\CosModel;

/**
 * 应用套餐表控制器
 * 地址:/admin/combo
 * @throws Exception if the model cannot be found
 * @return array
 */
class ComboController extends CommonController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * 地址:/admin/combo/index 默认访问
     * @throws Exception if the model cannot be found
     * @return array
     */

    public function actionIndex() {
//        $uid = $_SESSION['uid'];//获取当前登录的用户 id，如果需要的话
        $request = yii::$app->request; //获取 request 对象
        $method = $request->getMethod(); //获取请求方式 GET POST PUT DELETE
        if ($method == 'GET') {
            $params = $request->get(); //获取地址栏参数
        } else {
            $params = $request->bodyParams; //获取body传参
        }

        $combo = new ComboModel();
        $base = new Base64Model();
        switch ($method) {
            case 'GET':
                if (!isset($params['searchName'])) {
                    $array = ['status' => 400, 'message' => '缺少参数 searchName',];
                    return json_encode($array, JSON_UNESCAPED_UNICODE);
                }
                if ($params['searchName'] == "list") {
                    $array = $combo->findall($params);
                } else if ($params['searchName'] == "single") {
                    $array = $combo->find($params);
                } else {
                    $array = ['status' => 501, 'message' => '无该 searchName 请求',];
                    return json_encode($array, JSON_UNESCAPED_UNICODE);
                }
                break;
            case 'POST':
                $params['pic_url'] = $base->base64_image_content($params['pic_url'], "./uploads/app/");
                $must = ['name', 'app_id', 'pic_url', 'level', 'money'];
                $rs = $this->checkInput($must, $params);
                if ($rs != false) {
                    return json_encode($rs, JSON_UNESCAPED_UNICODE);
                }
                $array = $combo->add($params);
                break;
            case 'PUT':
                if (!isset($params['id'])) {
                    $array = ['status' => 400, 'message' => '缺少参数 id',];
                } else {
                    if (isset($params['pic_url'])) {
                        $params['pic_url'] = $base->base64_image_content($params['pic_url'], "./uploads/app/");
                    }
                    $array = $combo->update($params);
                }
                break;
            case 'DELETE':
                if (!isset($params['id'])) {
                    $array = ['status' => 400, 'message' => '缺少参数 id',];
                } else {
                    $array = $combo->delete($params);
                }
                break;
            default:
                return json_encode(['status' => 404, 'message' => 'ajax请求类型错误，找不到该请求',], JSON_UNESCAPED_UNICODE);
        }
        return $array;
    }

    public function actionList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $combo = new ComboModel();
            $array = $combo->findall($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $combo = new ComboModel();
            $params['id'] = $id;
            $array = $combo->find($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $combo = new ComboModel();
            $base = new Base64Model();
            $must = ['name', 'app_id', 'pic_url', 'level'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return json_encode($rs, JSON_UNESCAPED_UNICODE);
            }

            $str = creat_mulu("./uploads/combo");
            $params['pic_url'] = $base->base64_image_content($params['pic_url'], $str);
            if (!$params['pic_url']) {
                return result(500, "图片格式错误");
            }
            //将图片上传到cos
            $cos = new CosModel();
            $cosRes = $cos->putObject($params['pic_url']);
            if ($cosRes['status'] == '200') {
                $url = $cosRes['data'];
            } else {
                unlink(Yii::getAlias('@webroot/') . $params['pic_url']);
                return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
            }
            $params['pic_url'] = $url;
            $array = $combo->add($params);

            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $combo = new ComboModel();
            $base = new Base64Model();
            $params['id'] = $id;
            if (!isset($params['id'])) {
                $array = ['status' => 400, 'message' => '缺少参数 id',];
            } else {
                if (isset($params['pic_url'])) {
                    if ($params['pic_url'] != "") {
                        $str = creat_mulu("./uploads/combo");
                        $params['pic_url'] = $base->base64_image_content($params['pic_url'], $str);
                        //将图片上传到cos
                        $cos = new CosModel();
                        $cosRes = $cos->putObject($params['pic_url']);
                        if ($cosRes['status'] == '200') {
                            $url = $cosRes['data'];
                        } else {
                            unlink(Yii::getAlias('@webroot/') . $params['pic_url']);
                            return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
                        }
                        $params['pic_url'] = $url;
                    }
                }
                $array = $combo->update($params);
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
            $combo = new ComboModel();
            $params['id'] = $id;
            if (!isset($params['id'])) {
                $array = ['status' => 400, 'message' => '缺少参数 id',];
            } else {
                $array = $combo->delete($params);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
