<?php

namespace app\controllers\merchant\app;

use yii;
use yii\web\MerchantController;
use yii\db\Exception;
use app\models\merchant\app\AppAccessModel;
use app\models\merchant\app\AppModel;
use app\models\core\Base64Model;
use app\models\core\CosModel;
use app\models\forum\PostModel;
use app\models\merchant\user\UserModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class AppController extends MerchantController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * 地址:/admin/group/index 默认访问
     * @throws Exception if the model cannot be found
     * @return array
     */
//    public function generateCode($nums = 1, $exist_array = '', $code_length = 6, $prefix = '') {
//
//        $characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnpqrstuvwxyz";
//        $promotion_codes = array(); //这个数组用来接收生成的优惠码
//        for ($j = 0; $j < $nums; $j++) {
//            $code = '';
//            for ($i = 0; $i < $code_length; $i++) {
//
//                $code .= $characters[mt_rand(0, strlen($characters) - 1)];
//            }
//            //如果生成的4位随机数不再我们定义的$promotion_codes数组里面
//            if (!in_array($code, $promotion_codes)) {
//                if (is_array($exist_array)) {
//
//                    if (!in_array($code, $exist_array)) {//排除已经使用的优惠码
//                        $promotion_codes[$j] = $prefix . $code; //将生成的新优惠码赋值给promotion_codes数组
//                    } else {
//
//                        $j--;
//                    }
//                } else {
//                    $promotion_codes[$j] = $prefix . $code; //将优惠码赋值给数组
//                }
//            } else {
//                $j--;
//            }
//        }
//        return $promotion_codes[0];
//    }

    public function actionList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $app = new AppAccessModel();
            $data['mid'] = yii::$app->session['uid'];
            $array = $app->finds($data);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionOne() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $app = new \app\models\merchant\user\MerchantModel();
            $data['id'] = yii::$app->session['uid'];
            $array = $app->one($data);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAll() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $app = new AppModel();
            $array = $app->findall($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $app = new AppModel();
            $params['id'] = $id;
            $array = $app->find($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $app = new AppModel();
            $base = new Base64Model();
            //设置类目 参数
            $must = ['category_id', 'name', 'pic_url', 'type'];

//            $params['key'] = $this->generateCode();
//
//            $arr = $app->findall($params);
//
//            if ($arr['status'] == 200) {
//                for ($i = 0; $i < count($arr['data']); $i++) {
//                    $list[$i] = $arr['data'][$i]['key'];
//                }
//                $params['key'] = $this->generateCode(1, $list, 6, '');
//            } else if ($arr['status'] == 204) {
//                $params['key'] = $this->generateCode(1, '', 6, '');
//            }

            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return json_encode($rs, JSON_UNESCAPED_UNICODE);
            }
            $str = creat_mulu("./uploads/app");

            $localRes = $base->base64_image_content($params['pic_url'], $str);
            if (!$localRes) {
                return result(500, "图片格式错误");
            }
            //将图片上传到cos
            $cos = new CosModel();
            $cosRes = $cos->putObject($localRes);
            if ($cosRes['status'] == '200') {
                $url = $cosRes['data'];
            } else {
                unlink(Yii::getAlias('@webroot/') . $localRes);
                return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
            }
            //设置app 参数
            $data2 = [
                'name' => $params['name'],
                'category_id' => $params['category_id'],
                'pic_url' => $url,
                'detail_info' => isset($params['detail_info']) ? $params['detail_info'] : "",
                //   '`key`' => $params['key'],
                'type' => $params['type'],
                'parent_id' => isset($params['parent_id']) ? $params['parent_id'] : "",
                'status' => isset($params['status']) ? $params['status'] : "",
                'create_time' => time(),
            ];

            $array = $app->add($data2);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $app = new AppModel();
            $base = new Base64Model();
            $params['id'] = $id;
            if (!isset($params['id'])) {
                result(400, "缺少参数 id");
            } else {
//                $data = [
//                    'id' => $params['id'],
//                    'name' => $params['name'],
//                    'category_id' => $params['category_id'],
//                    'pic_url' => '',
//                    'detail_info' => isset($params['detail_info']) ? $params['detail_info'] : "",
//                    'type' => $params['type'],
//                    'parent_id' => isset($params['parent_id']) ? $params['parent_id'] : "",
//                    'status' => isset($params['status']) ? $params['status'] : "",
//                    'update_time' => time(),
//                ];
                if (isset($params['pic_url'])) {
                    $str = creat_mulu("./uploads/app");
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
                $array = $app->update($params);
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
            $app = new AppModel();
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $array = $app->delete($params);
                return $array;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    //应用概况
    public function actionAppinfo($id) {
        if (yii::$app->request->isGet) {

            $request = request(); //获取地址栏参数
            $params = $request['params'];
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['uid'];
            $postModel = new PostModel();
            $time = date("Y-m-d", strtotime("-1 day"));
            $startTime = strtotime($time . " 00:00:00");
            $params["create_time >={$startTime}"] = null;
            $endTime = strtotime($time . " 23:59:59");
            $params["create_time <={$endTime}"] = null;
            unset($params['id']);
            $array = $postModel->findall($params);

            $app = new AppAccessModel();
            $data['id'] = $id;
            $appinfo = $app->find($data);

            $userModel = new UserModel();
            $udata["update_time >={$startTime}"] = null;
            $udata["update_time <={$endTime}"] = null;
            $udata['`key`'] = $params['`key`'];
            $userinfo = $userModel->finds($udata);

            $userData['`key`'] = $params['`key`'];
            $userData['merchant_id'] = yii::$app->session['uid'];
            $users = $userModel->finds($userData);
            if ($appinfo['status'] != 200) {
                $rs['app'] = 0;
            } else {
                $rs['app'] = $appinfo['data'];
            }
            if ($array['status'] != 200) {
                $rs['post'] = 0;
            } else {
                $rs['post'] = count($array['data']);
            }
            if ($userinfo['status'] != 200) {
                $rs['user'] = 0;
            } else {
                $rs['user'] = count($userinfo['data']);
            }
            if ($users['status'] != 200) {
                $rs['userAll'] = 0;
            } else {
                $rs['userAll'] = count($users['data']);
            }




            return result(200, "请求成功", $rs);
        } else {
            return result(500, "请求方式错误");
        }
    }

}
