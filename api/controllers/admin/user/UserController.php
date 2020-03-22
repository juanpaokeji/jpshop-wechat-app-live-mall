<?php

namespace app\controllers\admin\user;

use app\models\core\TableModel;
use app\models\admin\user\UserModel;
use app\models\core\Token;
use yii;
use yii\web\Controller;
use yii\db\Exception;

use app\models\admin\user\SystemAccessModel;

class UserController extends Controller {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置
    private $requestMethodErrorMsg = '恶意请求!';

    public function behaviors() {
        return [
            'token' => [
                'class' => 'yii\filters\TokenFilter', //调用过滤器
//                'only' => ['single'],//指定控制器应用到哪些动作
//                'except' => ['info'],//指定控制器不应用到哪些动作
            ]
        ];
    }

    public function actionTest() {
        $redis = yii::$app->redis;
//        //redis 存值取值
//        $admin_user = '{
//            "status":200,
//            "message":"请求成功",
//            "data":[
//                {
//                    "id":"17",
//                    "username":"迪迦奥特曼",
//                    "status":"1",
//                    "phone":null,
//                    "title":"测试"
//                },
//                {
//                    "id":"33",
//                    "username":"泰罗奥特曼",
//                    "status":"0",
//                    "phone":null,
//                    "title":"123"
//                }
//            ]
//        }';
//        $redis->set('admin_user', $admin_user);  //设置redis缓存
//        $res = json_decode($redis->get('admin_user'));
//        return  result($res->status, $res->message, $res->data);

        $result = $redis->executeCommand('hmset', ['test', 'key1', 'val1', 'key2', 'val2']);
        var_dump($result);
        var_dump($redis->get('hmset'));

//        //关系依赖
//        $dependency = new \yii\caching\DbDependency(
//            ['sql'=> 'select * from juanpao.admin_user']
//        );
//        $cache = \Yii::$app->cache;
//        $cache->add('one', 'hello world', 3000, $dependency);
//        $result = $cache->get('one');
//        var_dump($result);
//        $cache->add('name','jys',5);
//        $cache->flush();
//        if($cache->exists('name')){
//            var_dump($cache->get('name'));
//        } else {
//            var_dump('该值已不存在');
//        }
    }

    /**
     * 地址:/admin/user/user
     * @throws Exception if the model cannot be found
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
//        $table = new UserModel();
//        switch ($method) {
//            case 'GET':
//                try {
//                    if ($params['searchName'] == "list") {
//                        $array = $table->findAll($params);
//                    } elseif ($params['searchName'] == "single") {
//                        if (isset($params['id']) && trim($params['id']) != '' && (int)($params['id']) != 0) {
//                            $res = $table->find($params);
//                            if ($res['status'] != 200) {
//                                return json_encode($res, JSON_UNESCAPED_UNICODE);
//                            }
//                            //获取需要返回的数组
//                            $arr = [
//                                'id'=>$res['data']['id'],
//                                'username'=>$res['data']['username'],
//                                'status'=>$res['data']['status'],
//                            ];
//                            $res['data'] = $arr;//替换原有返回字段
//                            $array = $res;
//                        } else {
//                            $array = [
//                                'status' => 400,
//                                'message' => '缺少参数 id 或 id 类型错误',
//                            ];
//                        }
//                    } else {
//                        $array = [
//                            'status' => 501,
//                            'message' => '未找到该请求',
//                        ];
//                    }
//                } catch (\Exception $e) {
//                    $array = [
//                        'status' => 500,
//                        'message' => '内部错误',
//                    ];
//                }
//                break;
//            case 'POST':
//                /**
//                 * 新增
//                 * 创建用户的时候，随机生成一个32位随机数然后md5获取salt值，将用户输入的md5(password+salt)存入password
//                 */
//                $must = ['username', 'password', 'group_id'];
//                $checkRes = $this->checkInput($must, $params);
//                if ($checkRes != false) {
//                    return json_encode($checkRes);
//                }
//                $res = $table->find(['username'=>$params['username']]);
//                //返回错误
//                if ($res['status'] != 200) {
//                    return json_encode($res, JSON_UNESCAPED_UNICODE);
//                }
//                //正确返回，判断是否存在数据
//                if (count($res['data']) != 0) {
//                    $array = [
//                        'status' => 409,
//                        'message' => '该用户名已存在',
//                    ];
//                    return json_encode($array, JSON_UNESCAPED_UNICODE);
//                }
//                //获取 md5 加密的 32 位随机字符串
//                $params['salt'] = md5($this->get_randomstr(32));
//                $array = $table->add($params);
//                break;
//            case 'DELETE':
//                if (!isset($params['id'])) {
//                    $array = [
//                        'status' => 400,
//                        'message' => '缺少参数 id',
//                    ];
//                    return json_encode($array, JSON_UNESCAPED_UNICODE);
//                }
//                $array = $table->delete($params);
//                break;
//            case 'PUT':
//                if (isset($params['status'])) {
//                    $array = $table->update($params);
//                } else {
//                    $must = ['id', 'username', 'password', 'group_id'];
//                    $checkRes = $this->checkInput($must, $params);
//                    if ($checkRes != false) {
//                        return json_encode($checkRes);
//                    }
//                    //判断用户名是否重复
//                    $res = $table->find(['username'=>$params['username'], 'id != ' . $params['id']=>null]);
//                    //返回错误
//                    if ($res['status'] != 200) {
//                        return json_encode($res, JSON_UNESCAPED_UNICODE);
//                    }
//                    //正确返回，判断是否存在数据
//                    if (count($res['data']) != 0) {
//                        $array = [
//                            'status' => 409,
//                            'message' => '该用户名已存在',
//                        ];
//                        return json_encode($array, JSON_UNESCAPED_UNICODE);
//                    }
//                    //获取该用户的盐
//                    $res = $table->find(['id'=>$params['id']]);
//                    if ($res['status'] != 200) {
//                        return json_encode($res, JSON_UNESCAPED_UNICODE);
//                    }
//                    $params['salt'] = $res['data']['salt'];
//                    $array = $table->update($params);
//                }
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
     * @throws Exception
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
        $table = new UserModel();
        /**
         * 新增
         * 创建用户的时候，随机生成一个32位随机数然后md5获取salt值，将用户输入的md5(password+salt)存入password
         */
        $must = ['username', 'password', 'group_id'];
        $checkRes = $this->checkInput($must, $params);
        if ($checkRes != false) {
            return $checkRes;
        }
        $res = $table->find(['username' => $params['username']]);
        //返回错误
        if ($res['status'] != 200) {
            return $res;
        }
        //正确返回，判断是否存在数据
        if (count($res['data']) != 0) {
            return result(409, '该用户名已存在', $res);
        }
        //获取 md5 加密的 32 位随机字符串
        $params['salt'] = md5($this->get_randomstr(32));
        $array = $table->add($params);
        return $array;
    }

    /**
     * 删除
     * @param $id
     * @return array
     * @throws Exception
     */
    public function actionDelete($id) {
//        $uid = $_SESSION['uid'];//获取当前登录的用户 id，如果需要的话
        $request = request(); //获取 request 对象 及方法
        $method = $request['method'];
        if ($method != 'DELETE') {
            return result(404, $this->requestMethodErrorMsg);
        }
        $table = new UserModel();
        $array = $table->delete(['id' => $id]);
        return $array;
    }

    /**
     * 更新
     * @param $id
     * @return array|string
     * @throws Exception
     */
    public function actionUpdate($id) {
//        $uid = $_SESSION['uid'];//获取当前登录的用户 id，如果需要的话
        $request = request(); //获取 request 对象 及方法
        $method = $request['method'];
        if ($method != 'PUT') {
            return result(404, $this->requestMethodErrorMsg);
        }
        $params = $request['params'];
        $params['id'] = $id;
        $table = new UserModel();
        if (!isset($params['username'])) {//存在 status 则表示修改状态字段
            $array = $table->update($params);
        } else {
//            $must = ['username', 'password', 'group_id'];
//            $checkRes = $this->checkInput($must, $params);
//            if ($checkRes != false) {
//                return $checkRes;
//            }
            //判断用户名是否重复
            $res = $table->find(['username' => $params['username'], 'id != ' . $id => null]);
            //返回错误
            if ($res['status'] != 200) {
                return json_encode($res);
            }
            //正确返回，判断是否存在数据
            if (count($res['data']) != 0) {
                $array = [
                    'status' => 409,
                    'message' => '该用户名已存在',
                ];
                return $array;
            }
            //获取该用户的盐
            $res = $table->find(['id' => $id]);
            if ($res['status'] != 200) {
                return json_encode($res, JSON_UNESCAPED_UNICODE);
            }
            $params['salt'] = $res['data']['salt'];
            $array = $table->update($params);
        }
        return $array;
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
        $table = new UserModel();
        $array = $table->findAll($params);
        return $array;
    }

    /**
     * 查询单条
     * @param $id
     * @return array|string
     */
    public function actionFind($id) {
//        $uid = $_SESSION['uid'];//获取当前登录的用户 id，如果需要的话
        $request = request(); //获取 request 对象 及方法
        $method = $request['method'];
        if ($method != 'GET') {
            return result(404, $this->requestMethodErrorMsg);
        }
        $table = new UserModel();
        if (isset($id) && trim($id) != '' && (int) ($id) != 0) {
            $res = $table->find(['id' => $id]);
            if ($res['status'] != 200) {
                return json_encode($res, JSON_UNESCAPED_UNICODE);
            }
            if (count($res['data']) == 0) {
                $array = [
                    'status' => 500,
                    'message' => '未找到对应数据',
                ];
                return $array;
            }
            //获取需要返回的数组
            $arr = [
                'id' => $res['data']['id'],
                'username' => $res['data']['username'],
                'status' => $res['data']['status'],
                'phone' => $res['data']['phone'],
                'intro' => $res['data']['intro'],
                'group_id' => $res['data']['group_ids'],
                'title' => $res['data']['title'],
            ];
            $res['data'] = $arr; //替换原有返回字段
            $array = $res;
        } else {
            $array = [
                'status' => 400,
                'message' => '缺少参数 id 或 id 类型错误',
            ];
        }
        return $array;
    }

    /**
     * 查询单条
     * @return array|string
     */
    public function actionInfo() {
        $id = yii::$app->session['uid']; //获取当前登录的用户 id，如果需要的话
        $request = request(); //获取 request 对象 及方法
        $method = $request['method'];
        if ($method != 'GET') {
            return result(404, $this->requestMethodErrorMsg);
        }
        $table = new UserModel();
        if (isset($id) && trim($id) != '' && (int) ($id) != 0) {
            $res = $table->one(['id' => $id]);

            if ($res['status'] != 200) {
                return $res;
            }
            if (count($res['data']) == 0) {
                $array = [
                    'status' => 500,
                    'message' => '未找到对应数据',
                ];
                return $array;
            }
            //获取需要返回的数组
            $arr = [
                'id' => $res['data']['id'],
                'username' => $res['data']['username'],
                'real_name' => $res['data']['real_name'],
                'status' => $res['data']['status'],
                'phone' => $res['data']['phone'],
                'intro' => $res['data']['intro'],
                'create_time' => date('Y-m-d H:i:s', $res['data']['create_time']),
                'update_time' => date('Y-m-d H:i:s', $res['data']['update_time']),
            ];
            $res['data'] = $arr; //替换原有返回字段
            $array = $res;
        } else {
            $array = [
                'status' => 400,
                'message' => '缺少参数 id 或 id 类型错误',
            ];
        }
        return $array;
    }

    /**
     * 后台用户修改信息
     * @return array|string
     * @throws Exception
     */
    public function actionUpdateinfo() {
        $uid = yii::$app->session['uid']; //获取当前登录的用户 id，如果需要的话
        $request = request(); //获取 request 对象 及方法
        $method = $request['method'];
        if ($method != 'PUT') {
            return result(404, $this->requestMethodErrorMsg);
        }
        $params = $request['params'];
        $params['id'] = $uid;
        $table = new UserModel();
        $array = $table->updateInfo($params);
        return $array;
    }

    /**
     * 后台用户修改密码
     * @return array|string
     * @throws Exception
     */
    public function actionUpdatepassword() {
        $uid = yii::$app->session['uid']; //获取当前登录的用户 id，如果需要的话
        $request = request(); //获取 request 对象 及方法
        $method = $request['method'];
        if ($method != 'PUT') {
            return result(404, $this->requestMethodErrorMsg);
        }
        $params = $request['params'];
        $params['id'] = $uid;
        $table = new UserModel();
        //判断当前密码是否正确，获取当前用户的盐，加密后是否与数据库密码相同
        $res = $table->find(['id' => $uid]);
        if ($res['status'] != 200) {
            return json_encode($res, JSON_UNESCAPED_UNICODE);
        }
        $salt = $res['data']['salt'];
        $password = $res['data']['password'];
        if (md5($params['oldPassword'] . $salt) != $password) {
            return result('500', '原密码错误');
        }

        $params['password'] = md5($params['password'] . $salt);
        $array = $table->updatePassword($params);
        return $array;
    }

    public function actionApptoken()
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $table = new TableModel();
            //获取 token
            $payload = [
                'iat' => $_SERVER['REQUEST_TIME'], //什么时候签发的
                'exp' => $_SERVER['REQUEST_TIME'] + 12 * 60 * 60, //过期时间
                'uid' => 13
            ];
            $tokenClass = new Token(yii::$app->params['JWT_KEY_MERCHANT']);
            try {
                $token = $tokenClass->encode($payload);
                //返回token
                if ($token) {
                    $array = [
                        'status' => 200,
                        'message' => '请求成功',
                        'data' => $token,
                        'username' => '系统管理员',
                    ];
                } else {
                    return result(500, 'token生成失败,请再次登录');
                }
            } catch (\Exception $e) {
                return result(500, '内部错误');
            }

            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
