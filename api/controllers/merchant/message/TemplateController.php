<?php

namespace app\controllers\merchant\message;

use app\models\admin\message\TemplateModel;
use Yii;
use yii\web\Controller;
use yii\db\Exception;
use yii\web\Response;
use app\models\core\TableModel;
use app\models\core\SMS\SmsTemplate;

class TemplateController extends Controller {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置
    private $requestMethodErrorMsg = '恶意请求!';

    public function behaviors() {
        return [
            'token' => [
                'class' => 'yii\filters\MerchantFilter', //调用过滤器
//                'only' => ['single'],//指定控制器应用到哪些动作
//                'except' => ['single'],//指定控制器不应用到哪些动作
            ]
        ];
    }

    /**
     * 地址:/merchant/message/template
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
//        $table = new TemplateModel();
//        switch ($method) {
//            case 'GET':
//                try {
//                    if ($params['searchName'] == "list") {
//                        //腾讯云短信模板列表
//                        $sms = new SmsTemplate(yii::$app->params['APP_ID'], yii::$app->params['APP_KEY']);
//                        $res = json_decode($sms->pullTemplateStatus([]));
//                        //返回参考 {"result":0,"msg":"","count":1,"data":[{"id":91149,"international":0,"text":"您的修改密码手机验证码： {1}，请于{2}分钟内填写。如非本人操作，请忽略。","status":0,"type":0,"reply":"","title":"修改密码","apply_time":"2018-03-27 14:47:10"}]}
//                        Yii::$app->response->format=Response::FORMAT_JSON;//允许返回数组
//                        if ($res->result == 0) {
////                            //将腾讯云服务器数据存入本地数据库，该代码只执行一次，用于初始化
////                            foreach ($data as $k => $v) {
////                                $save['name'] = $v->text;//模版内容
////                                $save['module'] = $v->title;//模块名称
////                                $save['qcloud_template_id'] = $v->id;//模板 id
////                                $save['merchant_id'] = 1;//商户 id
////                                $save['create_time'] = time();//创建时间
////                                $table->tableAdd('system_sms_template', $save);
////                            }
//                            $data = [];
//                            foreach ($res->data as $k => $v) {
//                                $data[$k] = [
//                                    'id' => $v->id,
//                                    'title' => $v->title,
//                                    'name' => $v->text,
//                                    'reply' => $v->reply,
//                                    'type' => $v->type,
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
//                        //请求成功示例 {"status":"200","message":"请求成功","data":[{"id":91145,"title":"身份验证","name":"您的手机验证码： {1}，请于{2}分钟内填写。如非本人操作，请忽略。","reply":"","type":0,"status":0},{"id":91148,"title":"找回密码","name":"您的找回密码手机验证码： {1}，请于{2}分钟内填写。如非本人操作，请忽略。","reply":"","type":0,"status":0},{"id":91149,"title":"修改密码","name":"您的修改密码手机验证码： {1}，请于{2}分钟内填写。如非本人操作，请忽略。","reply":"","type":0,"status":0},{"id":100988,"title":"身份验证测","name":"您的手机验证码：{1}，请于{2}分钟内填写。如非本人操作，请忽略1。","reply":"","type":1,"status":0},{"id":101564,"title":"身份验证测试2","name":"您的手机验证码： {1}，请于{2}分钟内填写。如非本人操作，请忽略。","reply":"","type":0,"status":0},{"id":101573,"title":"身份验证测试1","name":"您的手机验证码： {1}，请于{2}分钟内填写。如非本人操作，请忽略。","reply":"","type":0,"status":0},{"id":101576,"title":"身份验证测试1","name":"您的手机验证码： {1}，请于{2}分钟内填写。如非本人操作，请忽略。","reply":"","type":0,"status":0},{"id":103933,"title":"身份验证测2","name":"您的手机验证码：请忽略。","reply":"您好!感谢您对腾讯云短信的支持。请改为“你的验证码为：{1}，请于{2}分钟内填写。如非本人操作，请忽略本短信。”，如有疑问可QQ联系腾讯云短信技术支持QQ:3012203387。","type":1,"status":2}]}
//                        //请求失败示例 {"status":"500","result":1019,"message":"sdkappid illegal"}
//                    } elseif ($params['searchName'] == "single") {
//                        if (!isset($params['id']) || trim($params['id']) == '' || (int)($params['id']) == 0) {
//                            $array = [
//                                'status' => 400,
//                                'message' => '缺少参数 id 或 id 类型错误',
//                            ];
//                            return json_encode($array, JSON_UNESCAPED_UNICODE);
//                        }
//                        //腾讯云短信模板单条
//                        $sms = new SmsTemplate(yii::$app->params['APP_ID'], yii::$app->params['APP_KEY']);
//                        $res = json_decode($sms->pullTemplateStatus([(int)$params['id']]));
//                        //返回参考 {"result":0,"msg":"","count":1,"data":[{"id":103933,"international":0,"text":"您的手机验证码：请忽略。","status":2,"type":1,"reply":"您好!感谢您对腾讯云短信的支持。请改为“你的验证码为：{1}，请于{2}分钟内填写。如非本人操作，请忽略本短信。”，如有疑问可QQ联系腾讯云短信技术支持QQ:3012203387。","title":"身份验证测2","apply_time":"2018-04-04 15:55:08"}]}
//                        if ($res->result == 0) {
//                            $data = $res->data;
//                            if (count($data) == 0) {
//                                $array = [
//                                    'status' => 400,
//                                    'message' => '未找到对应数据',
//                                ];
//                                return json_encode($array, JSON_UNESCAPED_UNICODE);
//                            }
//                            $data = [
//                                'id' => $data[0]->id,
//                                'title' => $data[0]->title,
//                                'name' => $data[0]->text,
//                                'reply' => $data[0]->reply,
//                                'type' => $data[0]->type,
//                                'status' => $data[0]->status
//                            ];
//                            $array = [
//                                'status' => 200,
//                                'message' => '请求成功',
//                                'data' => $data
//                            ];
//                            return json_encode($array, JSON_UNESCAPED_UNICODE);
//                        } else {
//                            $array = [
//                                'status' => 500,
//                                'result' => $res->result,
//                                'message' => $res->errmsg,
//                            ];
//                            return json_encode($array, JSON_UNESCAPED_UNICODE);
//                        }
//                        //请求成功示例 {"status":"200","message":"请求成功","data":{"id":103933,"title":"身份验证测2","name":"您的手机验证码：请忽略。","reply":"您好!感谢您对腾讯云短信的支持。请改为“你的验证码为：{1}，请于{2}分钟内填写。如非本人操作，请忽略本短信。”，如有疑问可QQ联系腾讯云短信技术支持QQ:3012203387。","type":1,"status":2}}
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
//                $must = ['id', 'title', 'remark', 'text', 'type'];
//                $checkRes = $this->checkInput($must, $params);
//                if ($checkRes != false) {
//                    return json_encode($checkRes);
//                }
//                //腾讯云短信签名列表
//                $sms = new SmsTemplate(yii::$app->params['APP_ID'], yii::$app->params['APP_KEY']);
//                $res = json_decode($sms->addTemplate($params['text'],$params['type'],$params['title'],$params['remark']));
//                if (isset($res->ActionStatus) && $res->ActionStatus == "FAIL") {
//                    $array = [
//                        'status' => 500,
//                        'result' => $res->ErrorCode,
//                        'message' => $res->ErrorInfo,
//                    ];
//                    return json_encode($array, JSON_UNESCAPED_UNICODE);
//                }
//                //返回参考  {"status":"200","message":"请求成功","data":{"id":44252,"international":0,"text":"卷泡科技","status":1}}
//                if ($res->result == 0) {
//                    $data = $res->data;
//                    //请求成功时将数据存入本地数据库
//                    $save['name'] = $data->text;//模版内容
//                    $save['type'] = $params['type'];//模版类型
//                    $save['module'] = $params['title'];//模块名称
//                    $save['qcloud_template_id'] = $data->id;//模板 id
//                    $save['merchant_id'] = 1;//商户 id
//                    $save['create_time'] = time();//创建时间
//                    $result = $table->add($save);
//                    if (!$result) {
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
//                    $array = [
//                        'status' => 500,
//                        'result' => $res->result,
//                        'message' => $res->errmsg,
//                    ];
//                    return json_encode($array, JSON_UNESCAPED_UNICODE);
//                }
//                //请求成功示例 {"status":"200","message":"请求成功","data":105951}
//                //请求失败示例 {"status":"500","result":1019,"message":"sdkappid illegal"}
//                break;
//            case 'DELETE':
//                if (!isset($params['id'])) {
//                    $array = [
//                        'status' => 400,
//                        'message' => '缺少参数 id',
//                    ];
//                    return json_encode($array, JSON_UNESCAPED_UNICODE);
//                }
//                //腾讯云短信模板删除
//                $sms = new SmsTemplate(yii::$app->params['APP_ID'], yii::$app->params['APP_KEY']);
//                $res = json_decode($sms->delTemplate((int)$params['id']));
//                if (isset($res->ActionStatus) && $res->ActionStatus == "FAIL") {
//                    $array = [
//                        'status' => 500,
//                        'result' => $res->ErrorCode,
//                        'message' => $res->ErrorInfo,
//                    ];
//                    return json_encode($array, JSON_UNESCAPED_UNICODE);
//                }
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
//                //请求失败示例 {"status":"500","result":1019,"message":"sdkappid illegal"}
//                break;
//            case 'PUT':
//                $must = ['id', 'title', 'remark', 'text', 'type'];
//                $checkRes = $this->checkInput($must, $params);
//                if ($checkRes != false) {
//                    return json_encode($checkRes);
//                }
//                //腾讯云短信模板更新
//                $sms = new SmsTemplate(yii::$app->params['APP_ID'], yii::$app->params['APP_KEY']);
//                $res = json_decode($sms->modTemplate((int)$params['id'],$params['text'],$params['type'],$params['title'],$params['remark']));
//                if (isset($res->ActionStatus) && $res->ActionStatus == "FAIL") {
//                    $array = [
//                        'status' => 500,
//                        'result' => $res->ErrorCode,
//                        'message' => $res->ErrorInfo,
//                    ];
//                    return json_encode($array, JSON_UNESCAPED_UNICODE);
//                }
//                //返回参考  {"status":"200","message":"请求成功","data":{"id":44255,"international":0,"text":"卷泡科技","status":1}}
//                if ($res->result == 0) {
//                    //更新
//                    $save = [
//                        'id' => $params['id'],
//                        'name' => $params['text'],
//                        'type' => $params['type'],
//                        'module' => $params['title'],
//                        'update_time' => time()
//                    ];
//                    $result = $table->update($save);
//                    if (!$result) {
//                        $array = [
//                            'status' => '500',
//                            'message' => '1003 更新失败',
//                        ];
//                        return json_encode($array, JSON_UNESCAPED_UNICODE);
//                    }
//                    $array = [
//                        'status' => '200',
//                        'message' => '请求成功',
//                    ];
//                } else {
//                    $array = [
//                        'status' => '500',
//                        'result' => $res->result,
//                        'message' => $res->errmsg,
//                    ];
//                    return json_encode($array, JSON_UNESCAPED_UNICODE);
//                }
//                //请求成功示例 {"status":"200","message":"请求成功"}
//                //请求失败示例 {"status":"500","result":1019,"message":"sdkappid illegal"}
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
    public function actionAdd() {
//        $uid = $_SESSION['uid'];//获取当前登录的用户 id，如果需要的话
        $request = request(); //获取 request 对象 及方法
        $method = $request['method'];
        if ($method != 'POST') {
            return result(404, $this->requestMethodErrorMsg);
        }
        $params = $request['params'];

        $table = new TemplateModel();
        $must = ['title', 'text'];
        $checkRes = $this->checkInput($must, $params);
        if ($checkRes != false) {
            return $checkRes;
        }
        //腾讯云短信签名列表
        $sms = new SmsTemplate(yii::$app->params['APP_ID'], yii::$app->params['APP_KEY']);
        $res = json_decode($sms->addTemplate($params['text'], $params['type'], $params['title']));
        if (isset($res->ActionStatus) && $res->ActionStatus == "FAIL") {
            $array = [
                'status' => 500,
                'result' => $res->ErrorCode,
                'message' => $res->ErrorInfo,
            ];
            return json_encode($array, JSON_UNESCAPED_UNICODE);
        }
        //返回参考  {"status":"200","message":"请求成功","data":{"id":44252,"international":0,"text":"卷泡科技","status":1}}
        if ($res->result == 0) {
            $data = $res->data;
            //请求成功时将数据存入本地数据库
            $save['name'] = $data->text; //模版内容
            $save['type'] = $params['type']; //模版类型
            $save['module'] = $params['title']; //模块名称
            $save['qcloud_template_id'] = $data->id; //模板 id
            $save['status'] = $params['status']; //模板 id
            $save['merchant_id'] = yii::$app->session['uid']; //商户 id
            $save['create_time'] = time(); //创建时间
            $result = $table->add($save);
            if (!$result) {
                $array = [
                    'status' => 500,
                    'message' => '添加失败',
                ];
                return json_encode($array, JSON_UNESCAPED_UNICODE);
            }
            $array = [
                'status' => 200,
                'message' => '请求成功',
                'data' => $data->id
            ];
        } else {
            $array = [
                'status' => 500,
                'result' => $res->result,
                'message' => $res->errmsg,
            ];
            return json_encode($array, JSON_UNESCAPED_UNICODE);
        }
        return $array;
        //请求成功示例 {"status":"200","message":"请求成功","data":105951}
        //请求失败示例 {"status":"500","result":1019,"message":"sdkappid illegal"}
    }

    /**
     * 删除
     * @param $id
     * @return array
     */
    public function actionDelete($id) {
//        $uid = $_SESSION['uid'];//获取当前登录的用户 id，如果需要的话
        $request = request(); //获取 request 对象 及方法
        $method = $request['method'];
        if ($method != 'DELETE') {
            return result(404, $this->requestMethodErrorMsg);
        }

        //修改记录中的 delete_time 为当前时间 软删除
        $table = new TemplateModel();
        $uid = yii::$app->session['uid'];
        $result = $table->delete(['id' => $id], $uid);

        if (!$result) {
            return result(500, '删除失败');
        } else {
            //腾讯云短信模板删除
            $sms = new SmsTemplate(yii::$app->params['APP_ID'], yii::$app->params['APP_KEY']);
            $res = json_decode($sms->delTemplate((int) $id));
            if (isset($res->ActionStatus) && $res->ActionStatus == "FAIL") {
                return result(500, $res->ErrorInfo);
            }
            if ($res->result == 0) {
                return result(200, '请求成功');
            } else {
                return result(500, $res->errmsg);
            }
        }
        //请求成功示例 {"status":"200","message":"请求成功"}
        //请求失败示例 {"status":"500","result":1019,"message":"sdkappid illegal"}
    }

    /**
     * 只更新启用状态
     * @param $id
     * @return array|string
     */
    public function actionStatus($id) {
//        $uid = $_SESSION['uid'];//获取当前登录的用户 id，如果需要的话
        $request = request(); //获取 request 对象 及方法
        $method = $request['method'];
        if ($method != 'PUT') {
            return result(404, $this->requestMethodErrorMsg);
        }
        $params = $request['params'];
        $params['update_time'] = time();
        $params['id'] = $id;

        $table = new TemplateModel();
        $uid = yii::$app->session['uid'];
        $result = $table->update($params, $uid);
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
    public function actionUpdate($id) {
//        $uid = $_SESSION['uid'];//获取当前登录的用户 id，如果需要的话
        $request = request(); //获取 request 对象 及方法
        $method = $request['method'];
        if ($method != 'PUT') {
            return result(404, $this->requestMethodErrorMsg);
        }
        $params = $request['params'];

        $must = ['title', 'text', 'type'];
        $checkRes = $this->checkInput($must, $params);
        if ($checkRes != false) {
            return json_encode($checkRes);
        }
        //更新
        $save = [
            'id' => $id,
            'name' => $params['text'],
            'type' => $params['type'],
            'module' => $params['title'],
            'status' => $params['status'],
            'update_time' => time()
        ];
        $table = new TemplateModel();
        $uid = yii::$app->session['uid'];
        $result = $table->update($save, $uid);

        if (!$result) {
            return result(500, '更新失败');
        } else {
            //腾讯云短信模板更新
            $sms = new SmsTemplate(yii::$app->params['APP_ID'], yii::$app->params['APP_KEY']);
            $res = json_decode($sms->modTemplate((int) $id, $params['text'], $params['type'], $params['title']));
            if (isset($res->ActionStatus) && $res->ActionStatus == "FAIL") {
                return result(500, $res->ErrorInfo);
            }
            if ($res->result == 0) {
                return result(200, '请求成功');
            } else {
                return result(500, $res->msg);
            }
        }
        //请求成功示例 {"status":"200","message":"请求成功"}
        //请求失败示例 {"status":"500","result":1019,"message":"sdkappid illegal"}
    }

    /**
     * 查询所有（可扩展条件查询）
     * @return array
     */
    public function actionFinds() {
//        $uid = $_SESSION['uid'];//获取当前登录的用户 id，如果需要的话
        $request = request(); //获取 request 对象 及方法
        $method = $request['method'];
        if ($method != 'GET') {
            return result(404, $this->requestMethodErrorMsg);
        }
        $params = $request['params'];

        //腾讯云短信模板列表
        $sms = new SmsTemplate(yii::$app->params['APP_ID'], yii::$app->params['APP_KEY']);
        $res = json_decode($sms->pullTemplateStatus([], 0, 50));
        //返回参考 {"result":0,"msg":"","count":1,"data":[{"id":91149,"international":0,"text":"您的修改密码手机验证码： {1}，请于{2}分钟内填写。如非本人操作，请忽略。","status":0,"type":0,"reply":"","title":"修改密码","apply_time":"2018-03-27 14:47:10"}]}
        Yii::$app->response->format = Response::FORMAT_JSON; //允许返回数组
        if ($res->result == 0) {
//                            //将腾讯云服务器数据存入本地数据库，该代码只执行一次，用于初始化
//                            foreach ($data as $k => $v) {
//                                $save['name'] = $v->text;//模版内容
//                                $save['module'] = $v->title;//模块名称
//                                $save['qcloud_template_id'] = $v->id;//模板 id
//                                $save['merchant_id'] = 1;//商户 id
//                                $save['create_time'] = time();//创建时间
//                                $table->tableAdd('system_sms_template', $save);
//                            }
            $data = [];
            foreach ($res->data as $k => $v) {
                if (isset($params['searchName']) && trim($params['searchName']) != '') {
                    $where = [
                        'qcloud_template_id' => $v->id,
                        "module like '%{$params['searchName']}%'" => null
                    ];
                } else {
                    $where = [
                        'qcloud_template_id' => $v->id,
                    ];
                }
                $where['merchant_id'] = yii::$app->session['uid'];
                $tempModel = new TemplateModel();
                $res = $tempModel->find($where);
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
                    'title' => $v->title,
                    'name' => $v->text,
                    'reply' => $v->reply,
                    'type' => $v->type,
                    'auditStatus' => $auditStatus,
                    'status' => $res['data']['status']
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
            $array = [
                'status' => 500,
                'result' => $res->result,
                'message' => $res->errmsg,
            ];
            return json_encode($array, JSON_UNESCAPED_UNICODE);
        }
        //请求成功示例 {"status":"200","message":"请求成功","data":[{"id":91145,"title":"身份验证","name":"您的手机验证码： {1}，请于{2}分钟内填写。如非本人操作，请忽略。","reply":"","type":0,"status":0},{"id":91148,"title":"找回密码","name":"您的找回密码手机验证码： {1}，请于{2}分钟内填写。如非本人操作，请忽略。","reply":"","type":0,"status":0},{"id":91149,"title":"修改密码","name":"您的修改密码手机验证码： {1}，请于{2}分钟内填写。如非本人操作，请忽略。","reply":"","type":0,"status":0},{"id":100988,"title":"身份验证测","name":"您的手机验证码：{1}，请于{2}分钟内填写。如非本人操作，请忽略1。","reply":"","type":1,"status":0},{"id":101564,"title":"身份验证测试2","name":"您的手机验证码： {1}，请于{2}分钟内填写。如非本人操作，请忽略。","reply":"","type":0,"status":0},{"id":101573,"title":"身份验证测试1","name":"您的手机验证码： {1}，请于{2}分钟内填写。如非本人操作，请忽略。","reply":"","type":0,"status":0},{"id":101576,"title":"身份验证测试1","name":"您的手机验证码： {1}，请于{2}分钟内填写。如非本人操作，请忽略。","reply":"","type":0,"status":0},{"id":103933,"title":"身份验证测2","name":"您的手机验证码：请忽略。","reply":"您好!感谢您对腾讯云短信的支持。请改为“你的验证码为：{1}，请于{2}分钟内填写。如非本人操作，请忽略本短信。”，如有疑问可QQ联系腾讯云短信技术支持QQ:3012203387。","type":1,"status":2}]}
        //请求失败示例 {"status":"500","result":1019,"message":"sdkappid illegal"}
    }

    /**
     * 查询单条
     * @param $id
     * @return array|string
     */
    public function actionFind($id) {
//        $uid = $_SESSION['uid'];//获取当前登录的用户 id，如果需要的话
        //腾讯云短信模板单条
        $sms = new SmsTemplate(yii::$app->params['APP_ID'], yii::$app->params['APP_KEY']);
        $res = json_decode($sms->pullTemplateStatus([(int) $id]));
        //返回参考 {"result":0,"msg":"","count":1,"data":[{"id":103933,"international":0,"text":"您的手机验证码：请忽略。","status":2,"type":1,"reply":"您好!感谢您对腾讯云短信的支持。请改为“你的验证码为：{1}，请于{2}分钟内填写。如非本人操作，请忽略本短信。”，如有疑问可QQ联系腾讯云短信技术支持QQ:3012203387。","title":"身份验证测2","apply_time":"2018-04-04 15:55:08"}]}
        if ($res->result == 0) {
            $data = $res->data;
            if (count($data) == 0) {
                return result(400, '未找到对应数据');
            }
            //本地数据库获取备注
            $table = new TemplateModel();
            $dbRes = $table->find(['qcloud_template_id' => $id, 'merchant_id'=>yii::$app->session['uid']]);
            if ($dbRes['status'] != 200) {
                return json_encode($dbRes, JSON_UNESCAPED_UNICODE);
            }
            if (count($dbRes['data']) == 0) {
                return result(500, '获取数据失败');
            }
            $data = [
                'id' => $data[0]->id,
                'title' => $data[0]->title,
                'name' => $data[0]->text,
                'reply' => $data[0]->reply,
                'type' => $data[0]->type,
                'status' => $dbRes['data']['status']
            ];
            return result(200, '请求成功', $data);
        } else {
            return result(500, $res->errmsg);
        }
        //请求成功示例 {"status":"200","message":"请求成功","data":{"id":103933,"title":"身份验证测2","name":"您的手机验证码：请忽略。","reply":"您好!感谢您对腾讯云短信的支持。请改为“你的验证码为：{1}，请于{2}分钟内填写。如非本人操作，请忽略本短信。”，如有疑问可QQ联系腾讯云短信技术支持QQ:3012203387。","type":1,"status":2}}
        //请求失败示例 {"status":"500","result":1019,"message":"sdkappid illegal"}
    }

}
