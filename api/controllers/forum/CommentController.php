<?php

namespace app\controllers\forum;

use yii;
use yii\web\ForumController;
use yii\db\Exception;
use app\models\forum\CommentModel;
use app\models\forum\PostModel;
use app\models\forum\LevelModel;
use app\models\forum\UserModel;
use app\models\forum\ForumModel;
use app\models\forum\ScoreModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class CommentController extends ForumController {

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
            $comment = new CommentModel();

            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['user_id'] = yii::$app->session['user_id'];
            $array = $comment->findall($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAll() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new CommentModel();
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['post_id'] = $id;
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
            $comment = new CommentModel();
            $params['id'] = $id;
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['user_id'] = yii::$app->session['user_id'];
            $array = $comment->find($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $comment = new CommentModel();
            //设置类目 参数
            $must = ['post_id'];
            $rs = $this->checkInput($must, $params);
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['user_id'] = yii::$app->session['user_id'];
            $params['status'] = 1;
            if ($rs != false) {
                return $rs;
            }

            $forumdata['`key`'] = yii::$app->session['key'];
            $forumdata['merchant_id'] = yii::$app->session['merchant_id'];
            $forumModel = new ForumModel();
            $forum = $forumModel->find($forumdata);
            if ($forum['status'] != 200) {
                return result(500, "请求失败");
            }
            $forumconfig = json_decode($forum['data']['config'], true);
            
            $userModel = new UserModel();
            $user = $userModel->find(['id' => yii::$app->session['user_id']]);
            if ($user['status'] != 200) {
                return result(500, "用户信息查询不到！");
            }
            if ($user['data']['status'] == 0) {
                return result(500, "您已被拉入黑名单");
            }
            //商户设置的回帖限制
            if ($forumconfig['allow_comment_level'] != 0) {
                $levelModel = new LevelModel();
                $res = $levelModel->find(['id' => $forumconfig['allow_comment_level']]);


                if ($user['data']['score'] < $res['data']['min_score']) {
                    return result(500, "您未达到回帖等级！");
                }
            }
            //回帖
            $postModel = new PostModel();
            $postData['id'] = $params['post_id'];
            $postData['`key`'] = yii::$app->session['key'];
            $postData['merchant_id'] = yii::$app->session['merchant_id'];
            $post = $postModel->find($postData);
            //
            //
            if ($post['status'] == 200) {
                $path = "./uploads/forum/post/" . date('Y') . "/" . date('m') . "/" . date('d');
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
//                    if (isset($params['voices'])) {
//                        $url = $this->wxUploads($params['voices'], "app_key_" . $params['`key`']);
//                        $params['voice_url'] = $url['data'];
//                        unset($params['voices']);
//                    }
                } else if ($params['class'] == 2) {
                    //2019-1-28  取消上传      -- 单独上传图片，先走单独上传图片接口 后保存
//                    $config = $this->getSystemConfig(yii::$app->session['key'], "miniprogram");
//                    if ($config == false) {
//                        return result(500, "未配置小程序信息");
//                    }
//                    $params['img'] = json_decode($params['img'], true);
//                    if (is_array($params['img'])) {
//                        if (count($params['img']) != 0) {
//                            $url = $this->xcxUploads($params['img']);
//                            if ($url == false) {
//                                return result(500, "图片信息失败");
//                            }
//                            unset($params['img']);
//                            $params['pic_urls'] = $url;
//                        }
//                        unset($params['img']);
//                    } else {
//                        unset($params['img']);
//                    }
//                    if (isset($_FILES)) {
//                        $url = $this->xcxMedia("voices", "./uploads/forum");
//                        $params['voice_url'] = $url['data'];
//                    }
                } else {
                    result(500, "请求失败");
                }
                unset($params['class']);
                $array = $comment->add($params);
                $postData['comment_count'] = $post['data']['comment_count'] + 1;
                $postData['comment_time'] = time();
                $postModel->update($postData);

                //积分计算
                if ($array['status'] == 200 && is_array($forumconfig['score'])) {
                    $scoreModel = new ScoreModel();
                    $score['`key`'] = yii::$app->session['key'];
                    $score['merchant_id'] = yii::$app->session['merchant_id'];
                    $score['user_id'] = yii::$app->session['user_id'];
                    $score['type'] = "replies";
                    $score['source_id'] = $array['data'];
                    $scoreModel->score($score, $score['type'], $forumconfig['score']);
                }
                return $array;
            } else {
                return result(500, '找不到帖子');
            }
            //$rs = score($params['merchant_id'], $params['`key`'], $params['user_id'], 1);
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $comment = new CommentModel();
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['user_id'] = yii::$app->session['user_id'];
            $params['id'] = $id;
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $array = $comment->update($params);
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
            $comment = new CommentModel();
            $params['id'] = $id;
            $params['user_id'] = yii::$app->session['user_id'];
            if (!isset($params['id'])) {
                result(400, "缺少参数 id");
            } else {
                $array = $comment->delete($params);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
