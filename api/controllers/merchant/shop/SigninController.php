<?php

namespace app\controllers\merchant\shop;

use app\models\merchant\system\OperationRecordModel;
use yii;
use yii\db\Exception;
use yii\web\MerchantController;
use app\models\shop\SignInModel;
use app\models\core\Base64Model;
use app\models\core\CosModel;
use app\models\shop\SignModel;
use app\models\core\TableModel;
use app\models\shop\SignPrizeModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class SigninController extends MerchantController {

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
            $model = new SignInModel();
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['`key`'] = $params['key'];
            unset($params['key']);

            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->findall($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new SignInModel();
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['`key`'] = $params['key'];
            unset($params['key']);

            $params['merchant_id'] = yii::$app->session['uid'];
            $params['id'] = $id;
            $array = $model->find($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new SignInModel();
            $base = new Base64Model();

            //设置类目 参数
            $must = ['name', 'pic_url_activity', 'pic_url_sign', 'start_time', 'end_time', 'integral', 'pic_url_sign', 'pic_url_activity'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $params['`key`'] = $params['key'];
            unset($params['key']);

//            if (isset($params['pic_url_activity'])) {
//                $str = creat_mulu("./uploads/shop/signin");
//                $params['pic_url_activity'] = $base->base64_image_content($params['pic_url_activity'], $str);
//                //将图片上传到cos
//                $cos = new CosModel();
//                $cosRes = $cos->putObject($params['pic_url_activity']);
//                if ($cosRes['status'] == '200') {
//                    $url = $cosRes['data'];
//                } else {
//                    unlink(Yii::getAlias('@webroot/') . $params['pic_url_activity']);
//                    return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
//                }
//                $params['pic_url_activity'] = $url;
//            }
//            if (isset($params['pic_url_sign'])) {
//                $str = creat_mulu("./uploads/shop/signin");
//                $params['pic_url_sign'] = $base->base64_image_content($params['pic_url_sign'], $str);
//                //将图片上传到cos
//                $cos = new CosModel();
//                $cosRes = $cos->putObject($params['pic_url_sign']);
//                if ($cosRes['status'] == '200') {
//                    $url = $cosRes['data'];
//                } else {
//                    unlink(Yii::getAlias('@webroot/') . $params['pic_url_sign']);
//                    return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
//                }
//                $params['pic_url_sign'] = $url;
//            }
            if ($params['continuous'] == 1) {
                $params['continuous_arr'] = json_encode($params['continuous_arr'], JSON_UNESCAPED_UNICODE);
            }

            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->add($params);
            if ($array['status'] == 200){
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['`key`'];
                $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                $operationRecordData['operation_type'] = '新增';
                $operationRecordData['operation_id'] = $array['data'];
                $operationRecordData['module_name'] = '签到';
                $operationRecordModel->do_add($operationRecordData);
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
            $model = new SignInModel();
            $base = new Base64Model();
            $params['id'] = $id;
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['`key`'] = $params['key'];
            unset($params['key']);

//            if (isset($params['pic_url_activity'])) {
//                if ($params['pic_url_activity'] == "") {
//                    unset($params['pic_url_activity']);
//                } else {
//                    if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $params['pic_url_activity'], $result)) {
//                        $str = creat_mulu("./uploads/shop/signin");
//                        $params['pic_url_activity'] = $base->base64_image_content($params['pic_url_activity'], $str);
//                        //将图片上传到cos
//                        $cos = new CosModel();
//                        $cosRes = $cos->putObject($params['pic_url_activity']);
//                        if ($cosRes['status'] == '200') {
//                            $url = $cosRes['data'];
//                        } else {
//                            unlink(Yii::getAlias('@webroot/') . $params['pic_url_activity']);
//                            return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
//                        }
//                        $params['pic_url_activity'] = $url;
//                    }
//                }
//            }
//            if (isset($params['pic_url_sign'])) {
//                if ($params['pic_url_sign'] == "") {
//                    unset($params['pic_url_sign']);
//                } else {
//                    if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $params['pic_url_activity'], $result)) {
//                        $str = creat_mulu("./uploads/shop/signin");
//                        $params['pic_url_sign'] = $base->base64_image_content($params['pic_url_sign'], $str);
//                        //将图片上传到cos
//                        $cos = new CosModel();
//                        $cosRes = $cos->putObject($params['pic_url_sign']);
//                        if ($cosRes['status'] == '200') {
//                            $url = $cosRes['data'];
//                        } else {
//                            unlink(Yii::getAlias('@webroot/') . $params['pic_url_sign']);
//                            return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
//                        }
//                        $params['pic_url_sign'] = $url;
//                    }
//                }
//            }
            if (isset($params['continuous'])) {
                if ($params['continuous'] == 1) {
                    $params['continuous_arr'] = json_encode($params['continuous_arr'], JSON_UNESCAPED_UNICODE);
                }
            }

            $params['merchant_id'] = yii::$app->session['uid'];
            if (!isset($params['id'])) {
                return result(500, "缺少参数 id");
            } else {
                $array = $model->update($params);
                if ($array['status'] == 200){
                    //添加操作记录
                    $operationRecordModel = new OperationRecordModel();
                    $operationRecordData['key'] = $params['`key`'];
                    $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                    $operationRecordData['operation_type'] = '更新';
                    $operationRecordData['operation_id'] = $id;
                    $operationRecordData['module_name'] = '签到';
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
            $model = new SignInModel();
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
                return result(500, "缺少参数 id");
            } else {
                $array = $model->delete($params);
            }
            if ($array['status'] == 200){
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['`key`'];
                $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                $operationRecordData['operation_type'] = '删除';
                $operationRecordData['operation_id'] = $id;
                $operationRecordData['module_name'] = '签到';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionTime() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new SignInModel();
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['fields'] = " id,end_time,name ";
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->finds($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSign($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数


            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $signinModel = new SignInModel();
            $res = $signinModel->find(['id' => $id]);

            if ($res['status'] != 200) {
                return $res;
            }
            if (!isset($params['time'])) {
                if (time() > $res['data']['end']) {
                    $start_time = strtotime($res['data']['end'] . " 00:00:00");
                    $end_time = strtotime($res['data']['end'] . " 23:59:59");
                } else {
                    $start_time = strtotime(date("Y-m-d") . " 00:00:00");
                    $end_time = time();
                }
            } else {
                if (strtotime($params['time'] . " 23:59:59") < $res['data']['end'] + (24 * 60 * 60)) {
                    $start_time = strtotime($params['time'] . " 00:00:00");
                    $end_time = strtotime($params['time'] . " 23:59:59");
                    unset($params['time']);
                } else {
                    return result('500', "请求失败，您选择的时间超过活动时间");
                }
            }

            $params["shop_sign.create_time >= {$start_time} and shop_sign.create_time <= {$end_time}"] = null;
            $params['fields'] = " user_id,nickname,avatar,shop_sign.create_time ";
            $params['join'] = " inner join shop_user on shop_user.id = shop_sign.user_id ";
            $params['orderby'] = " shop_sign.create_time desc ";
            $model = new SignModel();
            $params['sign_id'] = $id;
            unset($params['id']);
            $params['shop_sign.`key`'] = $params['key'];
            unset($params['key']);
            $params['shop_sign.merchant_id'] = yii::$app->session['uid'];
            $array = $model->findall($params);

            if ($array['status'] != 200) {
                return $array;
            }

            //连续签到次数计算
            $table = new TableModel();
            $model = new SignInModel();
            $res = $model->find(['id' => $id, 'merchant_id' => yii::$app->session['uid'], '`key`' => $params['shop_sign.`key`']]);
            $signModel = new SignModel();
            $sign = $signModel->findall(['merchant_id' => yii::$app->session['uid'], 'sign_id' => $id, 'orderby' => 'create_time asc ']);
            $merchant_id = yii::$app->session['uid'];
            $sql = "select user_id  from shop_sign inner join shop_user on shop_user.id =  shop_sign.user_id where shop_sign.`key` ='{$params['shop_sign.`key`']}' and shop_sign.merchant_id={$merchant_id}  and sign_id = {$id} and  shop_sign.create_time >= {$res['data']['start']} and shop_sign.create_time <= {$res['data']['end']} group by user_id ";
            $user = $table->querySql($sql);

            $number = 0;


            $arr = array();
            $num = array();

            if ($sign['status'] == 200) {
                for ($i = 0; $i < count($user); $i++) {
                    for ($j = 0; $j < count($sign['data']); $j++) {
                        if ($user[$i]['user_id'] == $sign['data'][$j]['user_id']) {
                            $arr[$i][] = $sign['data'][$j];
                        }
                    }
                }
                for ($i = 0; $i < count($user); $i++) {
                    for ($j = 0; $j < count($arr[$i]); $j++) {
                        if ($j != 0) {
                            if ($j + 1 < count($arr[$i])) {
                                if (date('Y-m-d', $arr[$i][$j]['time'] + (1 * 24 * 60 * 60)) == date('Y-m-d', $arr[$i][$j + 1]['time'])) {
                                    $number = $number + 1;
                                } else {
                                    $number = 1;
                                }
                            } else if ($j + 1 == count($arr[$i]) && date('Y-m-d', $arr[$i][$j]['time'] - (1 * 24 * 60 * 60)) == date('Y-m-d', $arr[$i][$j - 1]['time'])) {
                                $number = $number + 1;
                            } else if ($j + 1 == count($arr[$i]) && date('Y-m-d', $arr[$i][$j]['time'] - (1 * 24 * 60 * 60)) != date('Y-m-d', $arr[$i][$j - 1]['time'])) {
                                $number = 1;
                            }
                        } else {
                            $number = 1;
                        }
                    }

                    $num[$i]['num'] = $number;
                    $num[$i]['user_id'] = $user[$i]['user_id'];
                }
            }

            $sql = "select count(*)as num,shop_user.nickname,shop_user.avatar,user_id  from shop_sign inner join shop_user on shop_user.id =  shop_sign.user_id where shop_sign.`key` ='{$params['shop_sign.`key`']}' and shop_sign.merchant_id={$merchant_id}  and sign_id = {$id} and  shop_sign.create_time >= {$res['data']['start']} and shop_sign.create_time <= {$res['data']['end']} group by user_id order by num desc";
            $leiji = $table->querySql($sql);

            for ($i = 0; $i < count($array['data']); $i++) {
                for ($k = 0; $k < count($num); $k++) {
                    if ($array['data'][$i]['user_id'] == $num[$k]['user_id']) {
                        $array['data'][$i]['series'] = $num[$k]['num'];
                    }
                }
                for ($k = 0; $k < count($leiji); $k++) {
                    if ($array['data'][$i]['user_id'] == $leiji[$k]['user_id']) {
                        $array['data'][$i]['total'] = $leiji[$k]['num'];
                    }
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUsers($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['fields'] = " user_id,nickname,avatar,shop_sign.create_time ";
            $params['join'] = " inner join shop_user on shop_user.id = shop_sign.user_id ";
            $params['groupby'] = "  user_id  ";
            $params['orderby'] = "  shop_sign.create_time desc  ";
            $model = new SignModel();
            $params['sign_id'] = $id;
            unset($params['id']);
            $params['shop_sign.`key`'] = $params['key'];
            unset($params['key']);
            $params['shop_sign.merchant_id'] = yii::$app->session['uid'];
            $array = $model->findall($params);


            //连续签到次数计算
            $table = new TableModel();
            $model = new SignInModel();
            $res = $model->find(['id' => $id, 'merchant_id' => yii::$app->session['uid'], '`key`' => $params['shop_sign.`key`']]);
            $signModel = new SignModel();
            $sign = $signModel->findall(['merchant_id' => yii::$app->session['uid'], 'sign_id' => $id, 'orderby' => 'create_time asc ']);
            $merchant_id = yii::$app->session['uid'];
            $sql = "select user_id  from shop_sign inner join shop_user on shop_user.id =  shop_sign.user_id where shop_sign.`key` ='{$params['shop_sign.`key`']}' and shop_sign.merchant_id={$merchant_id}  and sign_id = {$id} and  shop_sign.create_time >= {$res['data']['start']} and shop_sign.create_time <= {$res['data']['end']} group by user_id ";
            $user = $table->querySql($sql);

            $number = 0;


            $arr = array();
            $num = array();

            if ($sign['status'] == 200) {
                for ($i = 0; $i < count($user); $i++) {
                    for ($j = 0; $j < count($sign['data']); $j++) {
                        if ($user[$i]['user_id'] == $sign['data'][$j]['user_id']) {
                            $arr[$i][] = $sign['data'][$j];
                        }
                    }
                }
                for ($i = 0; $i < count($user); $i++) {
                    $number = 1;
                    for ($j = 0; $j < count($arr[$i]); $j++) {
                        if ($j == 0) {
                            $number = 1;
                        } else {
                            if ($j + 1 < count($arr[$i])) {
                                if (date('Y-m-d', $arr[$i][$j]['time'] + (1 * 24 * 60 * 60)) == date('Y-m-d', $arr[$i][$j + 1]['time'])) {
                                    $number = $number + 1;
                                } else {
                                    $number = 1;
                                }
                            } else if ($j + 1 == count($arr[$i]) && date('Y-m-d', $arr[$i][$j]['time'] - (1 * 24 * 60 * 60)) == date('Y-m-d', $arr[$i][$j - 1]['time'])) {
                                $number = $number + 1;
                            } else if ($j + 1 == count($arr[$i]) && date('Y-m-d', $arr[$i][$j]['time'] - (1 * 24 * 60 * 60)) != date('Y-m-d', $arr[$i][$j - 1]['time'])) {
                                $number = 1;
                            }
                        }
                    }
                    $num[$i]['num'] = $number;
                    $num[$i]['user_id'] = $user[$i]['user_id'];
                }
            }

            $sql = "select count(*)as num,shop_user.nickname,shop_user.avatar,user_id  from shop_sign inner join shop_user on shop_user.id =  shop_sign.user_id where shop_sign.`key` ='{$params['shop_sign.`key`']}' and shop_sign.merchant_id={$merchant_id}  and sign_id = {$id} and  shop_sign.create_time >= {$res['data']['start']} and shop_sign.create_time <= {$res['data']['end']} group by user_id order by num desc";
            $leiji = $table->querySql($sql);

            for ($i = 0; $i < count($array['data']); $i++) {
                for ($k = 0; $k < count($num); $k++) {
                    if ($array['data'][$i]['user_id'] == $num[$k]['user_id']) {
                        $array['data'][$i]['series'] = $num[$k]['num'];
                    }
                }
                for ($k = 0; $k < count($leiji); $k++) {
                    if ($array['data'][$i]['user_id'] == $leiji[$k]['user_id']) {
                        $array['data'][$i]['total'] = $leiji[$k]['num'];
                    }
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUser($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数


            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['fields'] = " create_time ";
            $model = new SignModel();
            $params['sign_id'] = $id;
            unset($params['id']);
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['shop_sign.merchant_id'] = yii::$app->session['uid'];

            $array = $model->findall($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionPrize() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数


            $must = ['key', 'sign_id'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['fields'] = " user_id,nickname,avatar,shop_sign_prize.* ";
            $params['join'] = " inner join shop_user on shop_user.id = shop_sign_prize.user_id ";
            $model = new SignPrizeModel();
            $params['shop_sign_prize.`key`'] = $params['key'];
            unset($params['key']);
            $params['shop_sign_prize.merchant_id'] = yii::$app->session['uid'];
            $array = $model->findall($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdateprize($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new SignPrizeModel();
            if (isset($params['remark'])) {
                $data['remark'] = $params['remark'];
            }
            if (isset($params['status'])) {
                $data['status'] = $params['status'];
            }
            $data['merchant_id'] = yii::$app->session['uid'];
            $data['`key`'] = $params['key'];
            $data['id'] = $id;
            $array = $model->update($data);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

//    public function actionStatus() {
//        if (yii::$app->request->isPut) {
//            $request = yii::$app->request; //获取 request 对象
//            $params = $request->bodyParams; //获取body传参
//            $model = new UnitModel();
//            $params['`key`'] = $params['key'];
//            unset($params['key']);
//            $params['merchant_id'] = yii::$app->session['uid'];
//            $params['route'] = "signIn";
//            $data['status'] = $params['status'];
//            $params['config'] = json_encode($data);
//            unset($params['status']);
//            $array = $model->updateStatus($params);
//            return $array;
//        } else {
//            return result(500, "请求方式错误");
//        }
//    }
}
