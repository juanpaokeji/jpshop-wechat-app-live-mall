<?php

namespace app\controllers\forum;

use yii;
use yii\web\ForumController;
use yii\db\Exception;
use app\models\forum\UserCollectionModel;
use app\models\forum\ForumModel;
use app\models\forum\ScoreModel;
use app\models\forum\UserModel;

/**
 * 社交收藏表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class CollectionController extends ForumController {

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
            $model = new UserCollectionModel();
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
            $model = new UserCollectionModel();
            $params['id'] = $id;
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['user_id'] = yii::$app->session['user_id'];
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
            $model = new UserCollectionModel();

            //设置类目 参数
            $must = ['post_id', 'status'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
                //设置类目 参
            }
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['user_id'] = yii::$app->session['user_id'];

            $userModel = new UserModel();
            $user = $userModel->find(['id' => yii::$app->session['user_id']]);
            if ($user['status'] != 200) {
                return result(500, "用户信息查询不到！");
            }
            if ($user['data']['status'] == 0) {
                return result(500, "您已被拉入黑名单");
            }

            $forumdata['`key`'] = yii::$app->session['key'];
            $forumdata['merchant_id'] = yii::$app->session['merchant_id'];
            $forumModel = new ForumModel();
            $forum = $forumModel->find($forumdata);
            if ($forum['status'] != 200) {
                return result(500, "请求失败");
            }
            $config = json_decode($forum['data']['config'], true);

            $data['post_id'] = $params['post_id'];
            $data['`key`'] = yii::$app->session['key'];
            $data['merchant_id'] = yii::$app->session['merchant_id'];
            $data['user_id'] = yii::$app->session['user_id'];
            $single = $model->find($data);
            if ($single['status'] == 200) {
                if ($params['status'] != 1) {
                    $params['status'] = 2;
                }
                $params['`key`'] = yii::$app->session['key'];
                $params['merchant_id'] = yii::$app->session['merchant_id'];
                $params['user_id'] = yii::$app->session['user_id'];

                $array = $model->update($params);
            } else {
                $params['`key`'] = yii::$app->session['key'];
                $params['merchant_id'] = yii::$app->session['merchant_id'];
                $params['user_id'] = yii::$app->session['user_id'];
                $params['status'] = 1;
                $array = $model->add($params);

                if ($array['status'] == 200 && is_array($config['score'])) {
                    $scoreModel = new ScoreModel();
                    $score['`key`'] = yii::$app->session['key'];
                    $score['merchant_id'] = yii::$app->session['merchant_id'];
                    $score['user_id'] = yii::$app->session['user_id'];
                    $score['type'] = "collection";
                    $score['source_id'] = $array['data'];
                    $scoreModel->score($score, $score['type'], $config['score']);
                }
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
            $model = new UserCollectionModel();
            $params['id'] = $id;
            //$params['user_id'] = yii::$app->session['user_id'];
            if (!isset($params['id'])) {
                result(400, "缺少参数 id");
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
            $model = new UserCollectionModel();
            $params['id'] = $id;
            $params['user_id'] = yii::$app->session['user_id'];
            if (!isset($params['id'])) {
                result(400, "缺少参数 id");
            } else {
                $array = $model->delete($params);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
