<?php

namespace app\controllers\common;

use app\models\admin\system\SystemCosModel;
use yii;
use yii\web\Controller;
use app\models\core\UploadsModel;
use app\models\core\CosModel;
use app\models\core\Base64Model;

class UploadsController extends Controller
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function actionIndex()
    {

        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参        
            //设置类目 参数
            $upload = new UploadsModel('imgage', "./uploads");
            $str = $upload->upload();
            if (!$str) {
                return "上传文件错误";
            }
            //将图片上传到cos
            $cos = new CosModel();
            $cosModel = new SystemCosModel();
            $a = $cosModel->do_select([]);
            if ($a['status'] == 200) {
                $cosRes = $cos->putObject($str);
                if ($cosRes['status'] == '200') {
                    $url = $cosRes['data'];
                    unlink(Yii::getAlias('@webroot/') . $str);
                } else {
                    unlink(Yii::getAlias('@webroot/') . $str);
                    return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
                }
            } else {
                $str = "http://" . $_SERVER['HTTP_HOST'] . "/api/web/" . $str;
                $url = $str;
            }


            return result(200, "请求成功", $url);
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionBase()
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参        
            //设置类目 参数
            $must = ['pic_url'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $base = new Base64Model();
            $path = creat_mulu("./uploads");
            $str = $base->base64_image_content($params['pic_url'], $path);
            //将图片上传到cos
            $cos = new CosModel();
            $cosRes = $cos->putObject($str);

            if ($cosRes['status'] == 200) {
                $url = $cosRes['data'];
                unlink(Yii::getAlias('@webroot/') . $str);
            } else {
                $url = "http://" . $_SERVER['HTTP_HOST'] . "/api/web/" . $str;
            }
            return result(200, "请求成功", $url);
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUploads()
    {
        if (yii::$app->request->isGet) {
            if (yii::$app->request->isPost) {
                $request = yii::$app->request; //获取 request 对象
                $params = $request->bodyParams; //获取body传参        
                //设置类目 参数
                $upload = new UploadsModel('pic_url', "./uploads");
                $str = $upload->uploads();
                if ($str['status'] != 200) {
                    return $str;
                }
                //将图片上传到cos
                $cos = new CosModel();
                for ($i = 0; $i < count($str); $i++) {
                    $cosRes = $cos->putObject($str[$i]['url']);
                    if ($cosRes['status'] == '200') {
                        $url[$i] = $cosRes['data'];
                        unlink(Yii::getAlias('@webroot/') . $str[$i]['url']);
                    } else {
                        $url[$i] = "http://" . $_SERVER['HTTP_HOST'] . "/api/web/" . $str;
                    }

                }

                return $url;
            } else {
                return result(500, "请求方式错误");
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpload()
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            //设置类目 参数
            $upload = new UploadsModel('pic_url', "./uploads/pic");
            $str = $upload->upload();
            if (!$str) {
                return "上传文件错误";
            }
            //将图片上传到cos
            $cos = new CosModel();
            $cosModel = new SystemCosModel();
            $a = $cosModel->do_select([]);
            if ($a['status'] == 200) {
                $cosRes = $cos->putObject($str);
                if ($cosRes['status'] == '200') {
                    $url = $cosRes['data'];
                    unlink(Yii::getAlias('@webroot/') . $str);
                } else {
                    unlink(Yii::getAlias('@webroot/') . $str);
                    return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
                }
            } else {
                $str = "http://" . $_SERVER['HTTP_HOST'] . "/api/web/" . $str;
                $url = $str;
            }
            return result(200, "请求成功", $url);
        } else {
            return result(500, "请求方式错误");
        }

    }

}
