<?php

namespace app\controllers\forum;

use yii;
use yii\web\ForumController;
use yii\db\Exception;
use app\models\forum\PostModel;
use app\models\forum\ForumModel;
use app\models\merchant\app\AppAccessModel;
use EasyWeChat\Kernel\Http\StreamResponse;
use app\models\forum\UserModel;
use app\models\forum\ScoreModel;
use app\models\core\UploadsModel;
use app\models\core\CosModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class PostController extends ForumController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors() {
        return [
            'token' => [
                'class' => 'yii\filters\ForumFilter', //调用过滤器
                //'only' => ['single'],//指定控制器应用到哪些动作
                'except' => ['list', 'one', 'posttop'], //指定控制器不应用到哪些动作
            ]
        ];
    }

    public function actionList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new PostModel();
            $must = ['page'];
            if (isset($params['key'])) {
                $appAccessModel = new AppAccessModel();
                $data['`key`'] = $params['key'];
                $appData = $appAccessModel->find($data);
                if ($appData['status'] == 200) {
                    $merchant_id = $appData['data']['merchant_id'];
                } else {
                    return result('500', '找不到该应用');
                }
                unset($appData);
                unset($data);
            } else {
                return result('500', '找不到该应用');
            }
            $params['`key`'] = $params['key'];
            $params['merchant_id'] = $merchant_id;
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $array = $model->finds($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionOne($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new PostModel();
            $params['id'] = $id;
            if (isset($params['key'])) {
                $appAccessModel = new AppAccessModel();
                $data['`key`'] = $params['key'];
                $appData = $appAccessModel->find($data);
                if ($appData['status'] == 200) {
                    $merchant_id = $appData['data']['merchant_id'];
                } else {
                    return result('500', '找不到该应用');
                }
                unset($appData);
                unset($data);
            } else {
                return result('500', '找不到该应用');
            }
            $params['`key`'] = $params['key'];
            $params['merchant_id'] = $merchant_id;
            $array = $model->find($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAll() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new PostModel();
            $params['`key`'] = yii::$app->session['key'];
            $params['forum_user_collection.user_id'] = yii::$app->session['user_id'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $array = $model->finds($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionPosts() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new PostModel();
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['user_id'] = yii::$app->session['user_id'];
            $array = $model->finds($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new PostModel();
            $params['id'] = $id;
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $array = $model->find($params);
            $postData['hits_count = hits_count+1'] = null;
            $postData['id'] = $params['id'];
            $rs = $model->updatePost($postData);

            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new PostModel();

            //设置类目 参数
            $must = ['content', 'type'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['user_id'] = yii::$app->session['user_id'];
            $userModel = new UserModel();
            $user = $userModel->find(['id' => yii::$app->session['user_id']]);
            if ($user['status'] != 200) {
                return result(500, "请用微信登陆");
            }
            if ($user['data']['status'] == 0) {
                return result(500, "您已被拉入黑名单");
            }

            $forumModel = new ForumModel();
            $data['`key`'] = yii::$app->session['key'];
            $forum = $forumModel->find($params);

            if ($forum['status'] != 200) {
                return result(500, "请求失败");
            }
            $forumconfig = json_decode($forum['data']['config'], true);

            if ($forumconfig['must_keyword'] == 1) {
                if ($params['keywords_id'] == 0) {
                    return result(500, "请选择话题");
                }
            }


            if (( (int) $forumconfig['allow_post_time'] * 3600) + (int) $user['data']['time'] > time()) {
                return result(500, "新用户在" . $forumconfig['allow_post_time'] . "小时后可以发贴");
            }

            if ($forumconfig['must_examine'] == 1) {

                $illegally = explode("，", $forumconfig['illegally']);
                foreach ($illegally as $value) {

                    if (strpos($params['content'], $value) === false) {
                        $params['status'] = 1;
                    } else {
                        $params['status'] = 0;
                        break;
                    }
                }
            } else {
                $params['status'] = 1;
            }

            //class 1 微信上传图片,音频 微信服务器下载  
            if ($params['class'] == 1) {

                $config = $this->getSystemConfig(yii::$app->session['key'], "wechat");
                if ($config == false) {
                    return result(500, "未配置微信信息");
                }
                $params['img'] = json_decode($params['img'], true);
                if (is_array($params['img'])) {
                    if (count($params['img']) != 0) {
                        $url = $this->wxUpload($config, $params['img'], 1, 9);
                        if ($url == false) {
                            return result(500, "图片信息失败");
                        }
                        unset($params['img']);
                        $params['pic_urls'] = $url;
                    }
                    unset($params['img']);
                } else {
                    unset($params['img']);
                }
                if ($params['type'] == 3) {
                    if (isset($params['voice_url'])) {
                        $url = $this->wxUpload($config, $params['voice_url'], 2);
                        if ($url == false) {
                            return result(500, "音频信息失败");
                        }
                        $params['voice_url'] = $url;
                    }
                }
            } else if ($params['class'] == 2) {
                $config = $this->getSystemConfig(yii::$app->session['key'], "miniprogram");
                if ($config == false) {
                    return result(500, "未配置小程序信息");
                }
                //class 2 小程序上传图片,音频  base64,file 上传
                //不是图文    图文没有语言信息 类型 1=文字 2=图文 3=语音
                if ($params['type'] == 3) {
                    $params['img'] = json_decode($params['img'], true);
                    if (isset($_FILES)) {
                        $url = $this->xcxUploads("voices", 2);
                        if ($url == false) {
                            return result(500, "音频信息失败");
                        }
                        $params['voice_url'] = $url;
                    }
                }
                //图片上传 则走uploads 接口先上传图片 后发布帖子
            } else {
                result(500, "请求失败");
            }
            unset($params['class']);

            $array = $model->add($params);
            //积分计算
            if ($params['status'] == 1 && $array['status'] == 200 && is_array($forumconfig['score'])) {
                $scoreModel = new ScoreModel();
                $score['`key`'] = yii::$app->session['key'];
                $score['merchant_id'] = yii::$app->session['merchant_id'];
                $score['user_id'] = yii::$app->session['user_id'];
                $score['type'] = "post";
                $score['source_id'] = $array['data'];
                $res = $scoreModel->score($score, $score['type'], $forumconfig['score']);
            }
            $array['message'] = $params['status'] == 0 ? "您发的帖子包含敏感信息" : "发帖成功";
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new PostModel();
            $params['id'] = $id;
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['user_id'] = yii::$app->session['user_id'];
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

    public function actionPosttop() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new PostModel();
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['is_top'] = 1;
            $params['limit'] = " 0,10 ";
            $params['orderby'] = " id desc ";
            $array = $model->postTop($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionDelete($id) {
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new PostModel();
            $params['id'] = $id;
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['user_id'] = yii::$app->session['user_id'];
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $array = $model->delete($params);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUserinfo() {
        if (yii::$app->request->isGet) {
            $weChat = new WeChatDevelopmentModel();
            //获取商户公众号配置
            $params['id'] = 1;
            $config = $weChat->getWeChatConfig($params['id']);
            if (!$config) {
                return result(400, "获取公众号配置出错");
            }
            $user = new UserModel();
            $userass = new UserAccessModel();
            $params['union_id'] = $union_id;
            $rs = $userass->find($params);
            //拉去微信信息 如果存在$union_id 则更新信息 不存在则新增user和forum_user_access
            if ($rs['status'] == 200) {
                $userinfo = jsonDecode($weChat->getUser($config, $union_id), true);
                $data = array(
                    'id' => $rs['data']['id'],
                    'nickname' => $userinfo['nickname'],
                    'sex' => $userinfo['sex'],
                    'city' => $userinfo['city'],
                    'province' => $userinfo['province'],
                    'avatar' => $userinfo['headimgurl'],
                    'update_time' => time()
                );
                $array = $user->update($data);
                return $array;
            } else {
                $userinfo = jsonDecode($weChat->getUser($config, $union_id), true);
                $data = array(
                    'nickname' => $userinfo['nickname'],
                    'sex' => $userinfo['sex'],
                    'city' => $userinfo['city'],
                    'province' => $userinfo['province'],
                    'avatar' => $userinfo['headimgurl'],
                    'create_time' => time()
                );
                $transaction = Yii::$app->db->beginTransaction();
                $rs1 = $user->add($data);
                try {
                    $ass = array(
                        'union_id' => $union_id,
                        'user_id' => $rs1['data'],
                        'create_time' => time(),
                    );
                    $rs2 = $userass->add($ass);
                    $transaction->commit(); //只有执行了commit(),对于上面数据库的操作才会真正执行
                    return result(200, "添加成功", $rs1['data']);
                } catch (Exception $e) {
                    $transaction->rollBack(); //回滚
                    return result(500, "添加失败");
                }
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUploads() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参        
            //设置类目 参数
            $upload = new UploadsModel('pic_url', "./uploads/forum");
            $str = $upload->upload();
            if (!$str) {
                return "上传文件错误";
            }
            //将图片上传到cos
            $cos = new CosModel();
            $cosRes = $cos->putObject($str);
            if ($cosRes['status'] == '200') {
                $url = $cosRes['data'];
                unlink(Yii::getAlias('@webroot/') . $str);
            } else {
                unlink(Yii::getAlias('@webroot/') . $str);
                return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
            }
            return $url;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
