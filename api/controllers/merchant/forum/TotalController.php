<?php

namespace app\controllers\merchant\forum;

use yii;
use yii\web\MerchantController;
use yii\db\Exception;
use app\models\merchant\forum\PostModel;
use app\models\merchant\app\AppAccessModel;
use app\models\merchant\user\UserModel;
use app\models\core\TableModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class TotalController extends MerchantController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors() {
        return [
            'token' => [
                'class' => 'yii\filters\ForumFilter', //调用过滤器
                'only' => ['single'], //指定控制器应用到哪些动作
                'except' => ['post'], //指定控制器不应用到哪些动作
            ]
        ];
    }

    /**
     * 周概况
     */
    public function actionWeek($id) {
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
            $table = new TableModel();
            $sql = "select * from  forum_post where DATE_SUB(CURDATE(), INTERVAL 7 DAY) <= date(FROM_UNIXTIME(create_time))";
            $list = $table->querySql($sql);

            $total = array();
            $week = array();
            for ($i = 1; $i <= 7; $i++) {
                $time = date("Y-m-d", strtotime("-{$i} day"));
                $num = 0;
                for ($j = 0; $j < count($list); $j++) {
                    $rstime = date('Y-m-d', $list[$j]['create_time']);
                    if ($rstime == $time) {
                        $num = $num + 1;
                    }
                }
                $week[$i - 1] = $time;
                $total[$i - 1] = $num;
            }
            $rs['total'][0] = $week;
            $rs['total'][1] = $total;
            return result(200, "请求成功", $rs);
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 用户概况
     */
    public function actionUser($id) {
        if (yii::$app->request->isGet) {

            $request = request(); //获取地址栏参数
            $params = $request['params'];
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['uid'];
//            $postModel = new PostModel();
//            $time = date("Y-m-d", strtotime("-1 day"));
//            $startTime = strtotime($time . " 00:00:00");
//            $params["create_time >={$startTime}"] = null;
//            $endTime = strtotime($time . " 23:59:59");
//            $params["create_time <={$endTime}"] = null;
//            unset($params['id']);
//            $array = $postModel->findall($params);
//
//            $app = new AppAccessModel();
//            $data['id'] = $id;
//            $appinfo = $app->find($data);
//
            $userModel = new UserModel();
//            $udata["update_time >={$startTime}"] = null;
//            $udata["update_time <={$endTime}"] = null;
//            $udata['`key`'] = $params['`key`'];
//            $userinfo = $userModel->finds($udata);

            $userData['`key`'] = $params['`key`'];
            $userData['merchant_id'] = yii::$app->session['uid'];
            $users = $userModel->finds($userData);
//            if ($appinfo['status'] != 200) {
//                $rs['app'] = 0;
//            } else {
//                $rs['app'] = $appinfo['data'];
//            }
//            if ($array['status'] != 200) {
//                $rs['post'] = 0;
//            } else {
//                $rs['post'] = count($array['data']);
//            }
//            if ($userinfo['status'] != 200) {
//                $rs['user'] = 0;
//            } else {
//                $rs['user'] = count($userinfo['data']);
//            }
            if ($users['status'] != 200) {
                $rs['userAll'] = 0;
            } else {
                $rs['userAll'] = count($users['data']);
            }
            $table = new TableModel();
            $sql = "select * from  forum_user where `key` = '{$params['`key`']}' and delete_time is null and  DATE_SUB(CURDATE(), INTERVAL 7 DAY) <= date(FROM_UNIXTIME(create_time))";
            $list = $table->querySql($sql);

            $total = array();
            $week = array();
            for ($i = 1; $i <= 7; $i++) {
                $time = date("Y-m-d", strtotime("-{$i} day"));
                $num = 0;
                for ($j = 0; $j < count($list); $j++) {
                    $rstime = date('Y-m-d', $list[$j]['create_time']);
                    if ($rstime == $time) {
                        $num = $num + 1;
                    }
                }
                $week[$i - 1] = $time;
                $total[$i - 1] = $num;
            }
            $rs['total'][0] = $week;
            $rs['total'][1] = $total;
            return result(200, "请求成功", $rs);
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionVisit($id) {
        if (yii::$app->request->isGet) {
            $request = request(); //获取地址栏参数
            $params = $request['params'];
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['uid'];

            $table = new TableModel();

            $sql = "select count(id) as num  from system_log  where `key` = '{$params['`key`']}'";
            $pv = $table->querySql($sql);
            $sql = "select count(id)   from system_log  where `key` = '{$params['`key`']}' GROUP BY user_agent,ip";
            $uv = $table->querySql($sql);

            $sql = "select count(id)   from system_log  where `key` = '{$params['`key`']}' GROUP BY ip";
            $ip = $table->querySql($sql);



            $sql = "select * from  forum_post where DATE_SUB(CURDATE(), INTERVAL 7 DAY) <= date(FROM_UNIXTIME(create_time))";
            $list = $table->querySql($sql);

            $data['total']['pv'] = $pv[0]['num'];
            $data['total']['uv'] = count($uv);
            $data['total']['ip'] = count($ip);

            for ($i = 0; $i <= 6; $i++) {

                $pvs = 0;
                $uvs = 0;
                $ips = 0;
                $time = date("Y-m-d", strtotime("-{$i} day"));
                $startTime = strtotime($time . " 00:00:00");
                $params["create_time >={$startTime}"] = null;
                $endTime = strtotime($time . " 23:59:59");
                $params["create_time <={$endTime}"] = null;
                $sql = "select count(id) as num  from system_log  where `key` = '{$params['`key`']}' and create_time>={$startTime} and create_time<={$endTime}";
                $pvs = $table->querySql($sql);

                $sql = "select count(id)   from system_log  where `key` = '{$params['`key`']}'  and create_time>={$startTime} and create_time<={$endTime} GROUP BY user_agent,ip";
                $uvs = $table->querySql($sql);

                $sql = "select count(id)   from system_log  where `key` = '{$params['`key`']}'  and create_time>={$startTime} and create_time<={$endTime} GROUP BY ip";
                $ips = $table->querySql($sql);
                $data['week']['day'][] = $time;
                $data['week']['pv'][] = (int) $pvs[0]['num'];
                $data['week']['uv'][] = count($uvs);
                $data['week']['ip'][] = count($ips);
            }
            return result(200, "请求成功", $data);
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionPost() {
        if (yii::$app->request->isGet) {
            $request = request(); //获取地址栏参数
            $params = $request['params'];
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['uid'];

            $table = new TableModel();

            $sql = "select count(id) as num  from forum_post    where `key` = '{$params['`key`']}' and status=1 and delete_time is  null";
            $post = $table->querySql($sql);
            $sql = "select count(forum_comment.id) as num   from forum_comment inner join forum_post on forum_post.id  = forum_comment.post_id  where forum_comment.`key` = '{$params['`key`']}' and forum_comment.status=1 and forum_post.delete_time is null";
            $comment = $table->querySql($sql);

            $sql = "select count(forum_user_collection.id) as num   from forum_user_collection inner join forum_post on forum_post.id  = forum_user_collection.post_id where forum_user_collection.`key` = '{$params['`key`']}' and forum_user_collection.status=1 and forum_post.delete_time is null";
            $collection = $table->querySql($sql);

            $sql = "select count(forum_user_like.id) as num   from forum_user_like  inner join forum_post on forum_post.id  = forum_user_like.source_id  where forum_user_like.`key` = '{$params['`key`']}' and forum_user_like.status=1 and  forum_post.delete_time is null";
            $like = $table->querySql($sql);
            $share = 0;

            $data['total']['post'] = (int) $post[0]['num'];
            $data['total']['comment'] = (int) $comment[0]['num'];
            $data['total']['collection'] = (int) $collection[0]['num'];
            $data['total']['like'] = (int) $like[0]['num'];
            $data['total']['share'] = $share;

            for ($i = 0; $i <= 6; $i++) {

                $time = date("Y-m-d", strtotime("-{$i} day"));
                $startTime = strtotime($time . " 00:00:00");
                $params["create_time >={$startTime}"] = null;
                $endTime = strtotime($time . " 23:59:59");
                $params["create_time <={$endTime}"] = null;

                $sql = "select count(id) as num  from forum_post  where `key` = '{$params['`key`']}' and create_time>={$startTime} and create_time<={$endTime}";
                $posts = $table->querySql($sql);
                $sql = "select count(id) as num   from forum_comment  where `key` = '{$params['`key`']}' and create_time>={$startTime} and create_time<={$endTime}";
                $comments = $table->querySql($sql);

                $sql = "select count(id) as num   from forum_user_collection  where `key` = '{$params['`key`']}' and create_time>={$startTime} and create_time<={$endTime}";
                $collections = $table->querySql($sql);

                $sql = "select count(id) as num   from forum_user_like  where `key` = '{$params['`key`']}' and create_time>={$startTime} and create_time<={$endTime}";
                $likes = $table->querySql($sql);
                $shares = 0;

                $data['week']['day'][] = $time;
                $data['week']['post'][] = (int) $posts[0]['num'];
                $data['week']['comment'][] = (int) $comments[0]['num'];
                $data['week']['collection'][] = (int) $collections[0]['num'];
                $data['week']['like'][] = (int) $likes[0]['num'];
                $data['week']['share'][] = $share;
            }
            return result(200, "请求成功", $data);
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionTest($id) {


        var_dump(yii::$app->request->getUserIP());
        var_dump($_SERVER['HTTP_USER_AGENT']);
        var_dump(request());
        die();
    }

}
