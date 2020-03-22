<?php

namespace app\controllers\admin\message;

use Yii;
use yii\web\Controller;
use yii\db\Exception;
use yii\web\Response;
use app\models\core\SMS\SMS;
use app\models\core\SMS\SmsSign;
use app\models\core\CosModel;
use app\models\core\UploadsModel;
use app\models\admin\message\SignatureModel;
use app\models\core\Base64Model;

header('Content-Type:application/x-www-form-urlencoded');

class SignatureController extends Controller
{
    public $enableCsrfValidation = false;//禁用CSRF令牌验证，可以在基类中设置
    private $requestMethodErrorMsg = '恶意请求!';

    public function behaviors()
    {
        return [
            'token' => [
                'class' => 'yii\filters\TokenFilter',//调用过滤器
//                'only' => ['single'],//指定控制器应用到哪些动作
//                'except' => ['single'],//指定控制器不应用到哪些动作
            ]
        ];
    }

    /**
     * 地址:/admin/message/signature
     * @return array
     */
//    public function actionIndex()
//    {
////        $uid = $_SESSION['uid'];//获取当前登录的用户 id，如果需要的话
//        $request = yii::$app->request;//获取 request 对象
//        $method = $request->getMethod();//获取请求方式 GET POST PUT DELETE
//        if ($method == 'GET') {
//            $params = $request->get();//获取地址栏参数
//            if (!isset($params['searchName'])) {
//                $array = [
//                    'status' => 400,
//                    'message' => '缺少参数 searchName',
//                ];
//                return json_encode($array, JSON_UNESCAPED_UNICODE);
//            }
//        } else {
//            $params = $request->bodyParams;//获取body传参
//        }
//        $table = new SignatureModel();
//        switch ($method) {
//            case 'GET':
//                try {
//                    if ($params['searchName'] == "list") {
//                        //腾讯云短信签名列表
//                        $sms = new SmsSign(yii::$app->params['APP_ID'],yii::$app->params['APP_KEY']);
//                        $res = json_decode($sms->pullSignStatus([]));
//                        //返回参考 {"result":0,"msg":"","count":1,"data":[{"id":40594,"international":0,"text":"\u5377\u6CE1","status":0,"reply":"","apply_time":"2018-03-27 11:32:18"}]}
//                        Yii::$app->response->format=Response::FORMAT_JSON;//允许返回数组
//                        if ($res->result == 0) {
////                            //将腾讯云服务器数据存入本地数据库，该代码只执行一次，用于初始化
////                            foreach ($res->data as $k => $v) {
////                                $save['name'] = $v->text;//签名名称
////                                $save['qcloud_sign_id'] = $v->id;//签名 id
////                                $save['merchant_id'] = 1;//商户 id
////                                $save['create_time'] = time();//创建时间
////                                $table->tableAdd('system_sms_sign', $save);
////                            }
//                            $data = [];
//                            foreach ($res->data as $k => $v) {
//                                $data[$k] = [
//                                    'id' => $v->id,
//                                    'name' => $v->text,
//                                    'reply' => $v->reply,
//                                    'status' => $v->status
//                                ];
//                            }
//                            $array = [
//                                'status' => 200,
//                                'message' => '请求成功',
//                                'data' => $data
//                            ];
//                        } else {
//                            $array = [
//                                'status' => 500,
//                                'result' => $res->result,
//                                'message' => $res->errmsg,
//                            ];
//                            return json_encode($array, JSON_UNESCAPED_UNICODE);
//                        }
//                        //请求成功示例 {"status":"200","message":"请求成功","data":[{"id":40594,"name":"卷泡","reply":"","status":0}]}
//                        //请求失败示例 {"status":"500","result":1019,"message":"sdkappid illegal"}
//                    } elseif ($params['searchName'] == "single") {
//                        if (!isset($params['id']) || trim($params['id']) == '' || (int)($params['id']) == 0) {
//                            $array = [
//                                'status' => 400,
//                                'message' => '缺少参数 id 或 id 类型错误',
//                            ];
//                            return json_encode($array, JSON_UNESCAPED_UNICODE);
//                        }
//                        //腾讯云短信签名单条
//                        $sms = new SmsSign(yii::$app->params['APP_ID'],yii::$app->params['APP_KEY']);
//                        $res = json_decode($sms->pullSignStatus([(int)$params['id']]));
//                        //返回参考 {"result":0,"msg":"","count":1,"data":[{"id":40594,"international":0,"text":"\u5377\u6CE1","status":0,"reply":"","apply_time":"2018-03-27 11:32:18"}]}
//                        if ($res->result != 0) {
//                            $array = [
//                                'status' => 500,
//                                'result' => $res->result,
//                                'message' => $res->errmsg,
//                            ];
//                            return json_encode($array, JSON_UNESCAPED_UNICODE);
//                        }
//                        $data = $res->data;
//                        if (count($data) == 0) {
//                            $array = [
//                                'status' => 500,
//                                'message' => '未找到对应数据',
//                            ];
//                            return json_encode($array, JSON_UNESCAPED_UNICODE);
//                        }
//                        $data = [
//                            'id' => $data[0]->id,
//                            'name' => $data[0]->text,
//                            'reply' => $data[0]->reply,
//                            'status' => $data[0]->status
//                        ];
//                        $array = [
//                            'status' => 200,
//                            'message' => '请求成功',
//                            'data' => $data
//                        ];
//                        return json_encode($array, JSON_UNESCAPED_UNICODE);
//                        //请求成功示例 {"status":"200","message":"请求成功","data":{"id":40594,"name":"卷泡","reply":"","status":0}}
//                        //请求失败示例 {"status":"500","result":1019,"message":"sdkappid illegal"}
//                    } else {
//                        $array = [
//                            'status' => 501,
//                            'message' => '未找到该请求',
//                        ];
//                        return json_encode($array, JSON_UNESCAPED_UNICODE);
//                    }
//                } catch (\Exception $e) {
//                    $array = [
//                        'status' => 500,
//                        'message' => '内部错误',
//                    ];
//                    return json_encode($array, JSON_UNESCAPED_UNICODE);
//                }
//                break;
//            /**
//             * 新增
//             * 创建用户的时候，随机生成一个32位随机数然后md5获取salt值，将用户输入的md5(password+salt)存入password
//             */
//            case 'POST':
//                $must = ['text', 'remark'];
//                $checkRes = $this->checkInput($must, $params);
//                if ($checkRes != false) {
//                    return json_encode($checkRes);
//                }
//                //上传图片，将页面提交过来的图片存到本地
//                $model = new Base64Model();
//                $path = $this->creat_mulu();
//                $localRes = $model->base64_image_content($params['img'], $path);
////                $model = new UploadsModel($params['fileName']);
////                $localRes = $model->upload();
//                $fileName = substr($localRes,10);
//                $url = Yii::$app->request->hostInfo . $localRes;//获取当前域名并加上图片保存地址
////                //将图片上传到cos
////                $cos = new CosModel();
////                $cosRes = $cos->putObject($localRes);
////                if ($cosRes['status'] == '200') {
////                    $url = $cosRes['data'];
////                } else {
////                    unlink (Yii::getAlias('@webroot/') . $localRes);
////                    return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
////                }
//                //腾讯云短信签名添加
//                $sms = new SmsSign(yii::$app->params['APP_ID'],yii::$app->params['APP_KEY']);
//                $res = json_decode($sms->addSign($params['text'],$params['img'],$params['remark']));
//                //返回参考  {"status":"200","message":"请求成功","data":{"id":44252,"international":0,"text":"卷泡科技","status":1}}
//                if ($res->result == 0) {
//                    $data = $res->data;
//                    //请求成功时将数据存入本地数据库
//                    $save['name'] = $data->text;//签名名称
//                    $save['qcloud_sign_id'] = $data->id;//签名 id
//                    $save['merchant_id'] = 1;//商户 id签名名称
//                    $save['remark'] = $params['remark'];//备注
//                    $save['pic_str'] = $params['img'];//base64图片信息
//                    $save['create_time'] = time();//创建时间
//                    $save['file_name'] = $fileName;//腾讯云对象存储地址key
//                    $save['url'] = $url;//腾讯云对象存储地址
//                    $result = $table->add($save);
//                    if (!$result) {
//                        unlink (Yii::getAlias('@webroot/') . $localRes);
//                        $array = [
//                            'status' => 500,
//                            'message' => '添加失败',
//                        ];
//                        return json_encode($array, JSON_UNESCAPED_UNICODE);
//                    }
//                    $array = [
//                        'status' => 200,
//                        'message' => '请求成功',
//                        'data' => $data->id
//                    ];
//                } else {
//                    unlink (Yii::getAlias('@webroot/') . $localRes);
//                    $array = [
//                        'status' => 500,
//                        'message' => $res->errmsg,
//                    ];
//                    return json_encode($array, JSON_UNESCAPED_UNICODE);
//                }
//                //请求成功示例 {"status":"200","message":"请求成功","data":138498}
//                //请求失败示例 {"status":"500","message":"sdkappid illegal"}
//                break;
//            case 'DELETE':
//                if (!isset($params['id'])) {
//                    $array = [
//                        'status' => 400,
//                        'message' => '缺少参数 id',
//                    ];
//                    return json_encode($array, JSON_UNESCAPED_UNICODE);
//                }
//                //腾讯云短信签名删除，对象存储图片不删除
//                $sms = new SmsSign(yii::$app->params['APP_ID'],yii::$app->params['APP_KEY']);
//                $res = json_decode($sms->delSign((int)$params['id']));
//                //返回参考  {"status":"200","message":"请求成功"}
//                if ($res->result == 0) {
//                    //修改记录中的 delete_time 为当前时间 软删除
//                    $result = $table->delete($params);
//                    if (!$result) {
//                        $array = [
//                            'status' => 500,
//                            'message' => '删除失败',
//                        ];
//                        return json_encode($array, JSON_UNESCAPED_UNICODE);
//                    }
//                    $array = [
//                        'status' => 200,
//                        'message' => '请求成功'
//                    ];
//                } else {
//                    $array = [
//                        'status' => 500,
//                        'message' => $res->errmsg,
//                    ];
//                    return json_encode($array, JSON_UNESCAPED_UNICODE);
//                }
//                //请求成功示例 {"status":"200","message":"请求成功"}
//                //请求失败示例 {"status":"500","message":"1002 删除失败"}
//                break;
//            case 'PUT':
//                $must = ['id', 'text', 'remark'];
//                $checkRes = $this->checkInput($must, $params);
//                if ($checkRes != false) {
//                    return json_encode($checkRes);
//                }
//                /**
//                 * 分为两种情况
//                 * 1，$_FILES 不为空重新上传图片，2为空则获取本地数据
//                 * 不管是否上传图片，都需要将图片base64获取到，并请求短信签名更新接口
//                 */
//                $save = [];//最后存到数据库表中的数组
//                //获取数据库base64
//                $picRes = $table->find(['qcloud_sign_id'=>$params['id']]);
//                if ($picRes['status'] != 200) {
//                    return json_encode($picRes, JSON_UNESCAPED_UNICODE);
//                }
//                if (count($picRes['data']) == 0) {
//                    $array = [
//                        'status' => 500,
//                        'message' => '获取数据失败',
//                    ];
//                    return $array;
//                }
//                $save['id'] = $params['id'];
//                $save['pic_str'] = $picRes['data']['pic_str'];
//                if (isset($params['img'])) {
//                    $model = new Base64Model();
//                    $localRes = $model->base64_image_content($params['img']);
//                    $save['file_name'] = substr($localRes,10);
//                    $save['pic_str'] = $params['img'];
//                    $save['url'] = Yii::$app->request->hostInfo . $localRes;//获取当前域名并加上图片保存地址
////                    //将图片上传到cos
////                    $cos = new CosModel();
////                    $cosRes = $cos->putObject($localRes);
////                    if ($cosRes['status'] == 200) {
////                        $save['url'] = $cosRes['data'];
////                    } else {
////                        unlink (Yii::getAlias('@webroot/') . $localRes);
////                        return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
////                    }
//                }
//                //腾讯云短信签名更新
//                $sms = new SmsSign(yii::$app->params['APP_ID'], yii::$app->params['APP_KEY']);
//                $res = json_decode($sms->modSign((int)$params['id'],$params['text'],$save['pic_str'],$params['remark']));
//                //返回参考  {"status":"200","message":"请求成功","data":{"id":44255,"international":0,"text":"卷泡科技","status":1}}
//                if ($res->result == 0) {
//                    //更新
//                    $save['name'] = $params['text'];
//                    $save['remark'] = $params['remark'];
//                    $save['update_time'] = time();
//                    $result = $table->update($save);
//                    if (!$result) {
//                        $array = [
//                            'status' => 500,
//                            'message' => '更新失败',
//                        ];
//                        return json_encode($array, JSON_UNESCAPED_UNICODE);
//                    }
//                    $array = [
//                        'status' => 200,
//                        'message' => '请求成功',
//                    ];
//                } else {
//                    $array = [
//                        'status' => 500,
//                        'message' => $res->errmsg,
//                    ];
//                    return json_encode($array, JSON_UNESCAPED_UNICODE);
//                }
//                //请求成功示例 {"status":"200","message":"请求成功"}
//                //请求失败示例 {"status":"500","message":"1002 删除失败"}
//                break;
//            default:
//                $array = [
//                    'status' => 404,
//                    'message' => 'ajax请求类型错误，找不到该请求',
//                ];
//                return json_encode($array, JSON_UNESCAPED_UNICODE);
//        }
//        return $array;
//    }

    /**
     * 新增
     * @return array
     */
    public function actionAdd()
    {
//        $uid = $_SESSION['uid'];//获取当前登录的用户 id，如果需要的话
        $request = request();//获取 request 对象 及方法
        $method = $request['method'];
        if ($method != 'POST') {
            return result(404, $this->requestMethodErrorMsg);
        }
        $params = $request['params'];

        $table = new SignatureModel();
        $must = ['text', 'remark', 'img'];
        $checkRes = $this->checkInput($must, $params);
        if ($checkRes != false) {
            return $checkRes;
        }
        //上传图片，将页面提交过来的图片存到本地
        $model = new Base64Model();
        $path = $this->creat_mulu();
        $localRes = $model->base64_image_content($params['img'], $path);
//                $model = new UploadsModel($params['fileName']);
//                $localRes = $model->upload();
        $fileName = substr($localRes, 10);
        $url = Yii::$app->request->hostInfo . $localRes;//获取当前域名并加上图片保存地址
//                //将图片上传到cos
//                $cos = new CosModel();
//                $cosRes = $cos->putObject($localRes);
//                if ($cosRes['status'] == '200') {
//                    $url = $cosRes['data'];
//                } else {
//                    unlink (Yii::getAlias('@webroot/') . $localRes);
//                    return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
//                }
        //腾讯云短信签名添加
        $sms = new SmsSign(yii::$app->params['APP_ID'], yii::$app->params['APP_KEY']);
        $res = json_decode($sms->addSign($params['text'], $params['img'], $params['remark']));
        //返回参考  {"status":"200","message":"请求成功","data":{"id":44252,"international":0,"text":"卷泡科技","status":1}}
        if ($res->result == 0) {
            $data = $res->data;
            //请求成功时将数据存入本地数据库
            $save['name'] = $data->text;//签名名称
            $save['qcloud_sign_id'] = $data->id;//签名 id
            $save['merchant_id'] = 0;//商户 id签名名称 超级管理员默认为0
            $save['remark'] = $params['remark'];//备注
            $save['status'] = $params['status'];//开启状态
            $save['pic_str'] = $params['img'];//base64图片信息
            $save['create_time'] = time();//创建时间
            $save['file_name'] = $fileName;//腾讯云对象存储地址key
            $save['url'] = $url;//腾讯云对象存储地址
            $result = $table->add($save);
            if (!$result) {
                unlink(Yii::getAlias('@webroot/') . $localRes);
                return result(500, '添加失败');
            }
            return result(200, '请求成功', $data->id);
        } else {
            unlink(Yii::getAlias('@webroot/') . $localRes);
            return result(500, $res->errmsg);
        }
        //请求成功示例 {"status":"200","message":"请求成功","data":138498}
        //请求失败示例 {"status":"500","message":"sdkappid illegal"}
    }

    /**
     * 删除
     * @param $id
     * @return array
     */
    public function actionDelete($id)
    {
//        $uid = $_SESSION['uid'];//获取当前登录的用户 id，如果需要的话
        $request = request();//获取 request 对象 及方法
        $method = $request['method'];
        if ($method != 'DELETE') {
            return result(404, $this->requestMethodErrorMsg);
        }

        $table = new SignatureModel();
        //腾讯云短信签名删除，对象存储图片不删除，现在不存cos，不用删除
        $sms = new SmsSign(yii::$app->params['APP_ID'], yii::$app->params['APP_KEY']);
        $res = json_decode($sms->delSign((int)$id));
        //返回参考  {"status":"200","message":"请求成功"}
        if ($res->result == 0) {
            //修改记录中的 delete_time 为当前时间 软删除
            $result = $table->delete(['id' => $id]);
            if (!$result || $result['status'] != 200) {
                return result(500, '删除失败');
            }
            return result(200, '请求成功');
        } else {
            return result(500, $res->msg);
        }
        //请求成功示例 {"status":"200","message":"请求成功"}
        //请求失败示例 {"status":"500","message":"1002 删除失败"}
    }

    /**
     * 只更新启用状态
     * @param $id
     * @return array|string
     */
    public function actionStatus($id)
    {
//        $uid = $_SESSION['uid'];//获取当前登录的用户 id，如果需要的话
        $request = request();//获取 request 对象 及方法
        $method = $request['method'];
        if ($method != 'PUT') {
            return result(404, $this->requestMethodErrorMsg);
        }
        $params = $request['params'];
        $params['update_time'] = time();
        $params['id'] = $id;

        $table = new SignatureModel();
        $result = $table->update($params);
        if (!$result || $result['status'] != 200) {
            return result(500, '更新失败');
        }
        return result(200, '请求成功');
    }

    /**
     * 更新
     * @param $id
     * @return array|string
     */
    public function actionUpdate($id)
    {
//        $uid = $_SESSION['uid'];//获取当前登录的用户 id，如果需要的话
        $request = request();//获取 request 对象 及方法
        $method = $request['method'];
        if ($method != 'PUT') {
            return result(404, $this->requestMethodErrorMsg);
        }
        $params = $request['params'];

        $must = ['text', 'remark', 'img'];
        $checkRes = $this->checkInput($must, $params);
        if ($checkRes != false) {
            return $checkRes;
        }
        /**
         * 分为两种情况
         * 1，$_FILES 不为空重新上传图片，2为空则获取本地数据
         * 不管是否上传图片，都需要将图片base64获取到，并请求短信签名更新接口
         */
        $table = new SignatureModel();
        $save = [];//最后存到数据库表中的数组
        //获取数据库base64
        $picRes = $table->find(['qcloud_sign_id' => $id]);
        if ($picRes['status'] != 200) {
            return json_encode($picRes, JSON_UNESCAPED_UNICODE);
        }
        if (count($picRes['data']) == 0) {
            return result(500, '获取数据失败');
        }
        $save['id'] = $id;
        $save['pic_str'] = $picRes['data']['pic_str'];
        if (isset($params['img'])) {
            $model = new Base64Model();
            $path = $this->creat_mulu();
            $localRes = $model->base64_image_content($params['img'], $path);
            $save['file_name'] = substr($localRes, 10);
            $save['pic_str'] = $params['img'];
            $save['url'] = Yii::$app->request->hostInfo . $localRes;//获取当前域名并加上图片保存地址
//                    //将图片上传到cos
//                    $cos = new CosModel();
//                    $cosRes = $cos->putObject($localRes);
//                    if ($cosRes['status'] == 200) {
//                        $save['url'] = $cosRes['data'];
//                    } else {
//                        unlink (Yii::getAlias('@webroot/') . $localRes);
//                        return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
//                    }
        }
        //腾讯云短信签名更新
        $sms = new SmsSign(yii::$app->params['APP_ID'], yii::$app->params['APP_KEY']);
        $res = json_decode($sms->modSign((int)$id, $params['text'], $save['pic_str'], $params['remark']));
        //返回参考  {"status":"200","message":"请求成功","data":{"id":44255,"international":0,"text":"卷泡科技","status":1}}
        if ($res->result == 0) {
            //更新
            $save['name'] = $params['text'];
            $save['remark'] = $params['remark'];
            $save['status'] = $params['status'];
            $save['update_time'] = time();
            $result = $table->update($save);
            if (!$result || $result['status'] != 200) {
                return result(500, '更新失败');
            }
            return result(200, '请求成功');
        } else {
            return result(500, $res->msg);
        }
        //请求成功示例 {"status":"200","message":"请求成功"}
        //请求失败示例 {"status":"500","message":"1002 删除失败"}
    }

    /**
     * 查询所有（可扩展条件查询）
     * @return array
     */
    public function actionFinds()
    {
//        $uid = $_SESSION['uid'];//获取当前登录的用户 id，如果需要的话
        $request = request();//获取 request 对象 及方法
        $method = $request['method'];
        if ($method != 'GET') {
            return result(404, $this->requestMethodErrorMsg);
        }
        $params = $request['params'];
        //腾讯云短信签名列表
        $sms = new SmsSign(yii::$app->params['APP_ID'], yii::$app->params['APP_KEY']);
        $res = json_decode($sms->pullSignStatus([]));
        //返回参考 {"result":0,"msg":"","count":1,"data":[{"id":40594,"international":0,"text":"\u5377\u6CE1","status":0,"reply":"","apply_time":"2018-03-27 11:32:18"}]}
        if ($res->result == 0) {
//            //将腾讯云服务器数据存入本地数据库，该代码只执行一次，用于初始化
//            foreach ($res->data as $k => $v) {
//                $save['name'] = $v->text;//签名名称
//                $save['qcloud_sign_id'] = $v->id;//签名 id
//                $save['merchant_id'] = 1;//商户 id
//                $save['create_time'] = time();//创建时间
//                $table->tableAdd('system_sms_sign', $save);
//            }
            $data = [];
            foreach ($res->data as $k => $v) {
                if (isset($params['searchName']) && trim($params['searchName']) != '') {
                    $where = [
                        'qcloud_sign_id' => $v->id,
                        "name like '%{$params['searchName']}%'" => null
                    ];
                } else {
                    $where = [
                        'qcloud_sign_id' => $v->id,
                    ];
                }
                $signModel = new SignatureModel();
                $res = $signModel->find($where);
                if ($res['status'] != 200) {
                    return json_encode($res, JSON_UNESCAPED_UNICODE);
                }
                if (count($res['data']) == 0) {
                    continue;
                }
                if ($v->status == 0) {
                    $auditStatus = '已通过';
                } else if ($v->status == 1) {
                    $auditStatus = '待审核';
                } else if ($v->status == 2) {
                    $auditStatus = '已拒绝';
                } else {
                    $auditStatus = '状态异常';
                }
                $data[$k] = [
                    'id' => $v->id,
                    'name' => $v->text,
                    'reply' => $v->reply,
                    'auditStatus' => $auditStatus,
                    'status' => $res['data']['status'],
                    'remark' => $res['data']['remark']
                ];
            }
            //通过这种方式完成分页效果
            $return = array_slice($data, ($params['page'] - 1) * $params['limit'], $params['limit']);
            return [
                'status' => 200,
                'message' => '请求成功',
                'data' => $return,
                'count' => count($data),
            ];
        } else {
            return result(500, $res->errmsg);
        }
        //请求成功示例 {"status":"200","message":"请求成功","data":[{"id":40594,"name":"卷泡","reply":"","status":0}]}
        //请求失败示例 {"status":"500","result":1019,"message":"sdkappid illegal"}
    }

    /**
     * 查询单条
     * @param $id
     * @return array|string
     */
    public function actionFind($id)
    {
//        $uid = $_SESSION['uid'];//获取当前登录的用户 id，如果需要的话
        if (!isset($id) || trim($id) == '' || (int)($id) == 0) {
            return result(400, '缺少参数 id 或 id 类型错误');
        }
        //腾讯云短信签名单条
        $sms = new SmsSign(yii::$app->params['APP_ID'], yii::$app->params['APP_KEY']);
        $res = json_decode($sms->pullSignStatus([(int)$id]));
        //返回参考 {"result":0,"msg":"","count":1,"data":[{"id":40594,"international":0,"text":"\u5377\u6CE1","status":0,"reply":"","apply_time":"2018-03-27 11:32:18"}]}
        if ($res->result != 0) {
            return result(500, $res->errmsg);
        }
        $data = $res->data;
        if (count($data) == 0) {
            return result(500, '未找到对应数据');
        }
        //本地数据库获取备注
        $table = new SignatureModel();
        $dbRes = $table->find(['qcloud_sign_id' => $id]);
        if ($dbRes['status'] != 200) {
            return json_encode($dbRes, JSON_UNESCAPED_UNICODE);
        }
        if (count($dbRes['data']) == 0) {
            return result(500, '获取数据失败');
        }
        $data = [
            'id' => $data[0]->id,
            'name' => $data[0]->text,
            'remark' => $dbRes['data']['remark'],
            'pic_str' => $dbRes['data']['pic_str'],
            'reply' => $data[0]->reply,
            'status' => $dbRes['data']['status']
        ];
        return result(200, '请求成功', $data);
        //请求成功示例 {"status":"200","message":"请求成功","data":{"id":40594,"name":"卷泡","reply":"","status":0}}
        //请求失败示例 {"status":"500","result":1019,"message":"sdkappid illegal"}
    }

    //建立以年月日为文件夹名
    function creat_mulu()
    {
        $this->creatFolder("./uploads/" . date('Y'));
        $this->creatFolder("./uploads/" . date('Y') . "/" . date('m'));
        $this->creatFolder("./uploads/" . date('Y') . "/" . date('m') . "/" . date('d'));
        return "./uploads/" . date('Y') . "/" . date('m') . "/" . date('d');
    }

    //如果文件夹不存在则创建文件夹
    function creatFolder($f_path)
    {
        if (!file_exists($f_path)) {
            mkdir($f_path, 0777);
        }
    }

    /**
     * 其他调试
     * 地址:/api/messageSignature/test
     * @throws Exception if the model cannot be found
     * @return array
     */
    public function actionTest()
    {
        $sms = new SMS();
//        $phone = '15366669450';//单发
//        return $sms->sendOne($phone);

//        $phone = ['15366669450','15195729049'];//群发 暂时不需要
//        return $sms->sendMulti($phone);

//        $phone = '15366669450';//指定模板单发
//        $templateId = '91145';
//        $params = ['123456','15'];
//        $smsSign = '卷泡';
//        return $sms->sendSpecifiedTemplateOne($phone,$templateId,$params,$smsSign);

//        $phone = '15195729049';//语音验证码单发
//        return $sms->sendVoiceOne($phone);

        $begin_date = 2018032812;//开始时间
        $end_date = 2018040412;//截止时间
//        return $sms->pullCallbackStatus($begin_date, $end_date);//发送数据统计
        return $sms->pullReplyStatus($begin_date, $end_date);//回执数据统计
    }
}
