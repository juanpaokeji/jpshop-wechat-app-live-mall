<?php

namespace app\controllers\shop;

use app\models\system\SystemPicServerModel;
use yii;
use yii\db\Exception;
use yii\web\ShopController;
use app\models\shop\CommentModel;
use app\models\shop\OrderModel;
use app\models\core\CosModel;
use app\models\core\UploadsModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class CommentController extends ShopController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors() {
        return [
            'token' => [
                'class' => 'yii\filters\ShopFilter', //调用过滤器
//                'only' => ['single'],//指定控制器应用到哪些动作
                'except' => ['all'], //指定控制器不应用到哪些动作
            ]
        ];
    }

    public function actionList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new CommentModel();
            $params['shop_user_comment.`key`'] = yii::$app->session['key'];
            $params['shop_user_comment.merchant_id'] = yii::$app->session['merchant_id'];
            $params['shop_user_comment.user_id'] = yii::$app->session['user_id'];
            $array = $model->findall($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAll($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new CommentModel();

            $params['shop_user_comment.`key`'] = $params['key'];
            unset($params['key']);
            $params['shop_user_comment.status'] = 1;
            $params['so.goods_id'] = $id;
            unset($params['id']);
            $array = $model->findComment($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $params['data'] = json_decode($params['data'], true);

            $type = $params['class'] == 1 ? "wechat" : "miniprogram";
            $config = $this->getSystemConfig(yii::$app->session['key'], $type);
            if ($config == false) {
                return result(500, "未配置微信信息");
            }
            //$model = new CartModel();
            //设置类目 参数
            $must = ['order_id'];
            $rs = $this->checkInput($must, $params['data'][0]);
            if ($rs != false) {
                return $rs;
            }
            $params['`key`'] = yii::$app->session['key'];
            $orderModel = new OrderModel();
            $rs = $orderModel->tableSingle("shop_order_group", ['order_sn' => $params['order_sn'], 'delete_time is null' => null]);

            if (is_array($rs)) {
                if ($rs['status'] != 6) {
                    return result(500, '该订单未确认收货或已评价');
                }
            } else {
                return result(500, '找不到该订单');
            }
            $commentModel = new CommentModel();
            for ($i = 0; $i < count($params['data']); $i++) {
                $data['merchant_id'] = yii::$app->session['merchant_id'];
                $data['user_id'] = yii::$app->session['user_id'];
                if ($params['data'][$i]['describe_score'] > 5 && $params['data'][$i]['service_score'] > 5 && $params['data'][$i]['express_score'] > 5) {
                    return result(500, '评论分数参数错误');
                }
                $data['order_id'] = $params['data'][$i]['order_id'];
                $data['describe_score'] = $params['data'][$i]['describe_score'] == "" ? 5 : $params['data'][$i]['describe_score'];
                $data['service_score'] = $params['data'][$i]['service_score'] == "" ? 5 : $params['data'][$i]['service_score'];
                $data['express_score'] = $params['data'][$i]['express_score'] == "" ? 5 : $params['data'][$i]['express_score'];
                $data['content'] = $params['data'][$i]['content'];
                if ($params['class'] == 1) {
                    if (count($params['data'][$i]['serverId']) != 0) {
                        if (count($params['data'][$i]['serverId']) != 0) {
                            $data['pics_url'] = "";
                            $url = $this->wxUpload($config, $params['data'][$i]['serverId']);
                            if ($url == false) {
                                return result(500, "图片信息失败");
                            }
                            $data['pics_url'] = $data['pics_url'] . "," . $url;
                        }
                    }
                }
                if ($params['class'] == 2) {
                    for ($k = 0; $k < count($params['data'][$i]['pics_url']); $k++) {
                        if ($k == 0) {
                            $data['pics_url'] = $params['data'][$i]['pics_url'][$k];
                        } else {
                            $data['pics_url'] = $data['pics_url'] . "," . $params['data'][$i]['pics_url'][$k];
                        }
                    }
                }

                $data['`key`'] = yii::$app->session['key'];
                $array = $commentModel->add($data);
                if ($array['status'] == 200) {
                    $orderData['status'] = 7;
                    $orderData['`key`'] = yii::$app->session['key'];
                    $orderData['merchant_id'] = yii::$app->session['merchant_id'];
                    $orderData['user_id'] = yii::$app->session['user_id'];
                    $orderData['id'] = $rs['id'];
                    $orderData['order_sn'] = $params['order_sn'];
                    $orderModel->update($orderData);
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
            $model = new CommentModel();
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

    public function actionDelete() {
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new CommentModel();
            $params['`key`'] = yii::$app->session['key'];
            $data['merchant_id'] = yii::$app->session['merchant_id'];
            $data['user_id'] = yii::$app->session['user_id'];
            for ($i = 0; $i < count($params['ids']); $i++) {
                $data['id'] = $params['ids'][$i];
                $model->delete($data);
            }
            return result(200, "请求成功");
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new OrderModel();
            $params['`key`'] = yii::$app->session['key'];
            $array = $model->goodsOrder($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUploads() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参        
            //设置类目 参数
            $upload = new UploadsModel('pic_url', "./uploads/goods/commit");
            $str = $upload->upload();
            if (!$str) {
                return "上传文件错误";
            }

            $cosModel = new SystemPicServerModel();
            $where['status'] = 1; //服务器只会有一个开启，没有开启则使用本地
            $a  = $cosModel->do_one($where);
            if($a['status']==200){
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
            }else{
                $url = "http://".$_SERVER['HTTP_HOST']."/api/web/".$str;
            }

            return result(200, "请求成功!", $url);
        } else {
            return result(500, "请求方式错误");
        }
    }

}
