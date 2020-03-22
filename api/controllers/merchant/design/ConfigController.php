<?php

namespace app\controllers\merchant\design;

use yii;
use yii\web\MerchantController;
use yii\db\Exception;
use app\models\system\SystemConfigModel;
use app\models\core\Base64Model;
use app\models\core\CosModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class ConfigController extends MerchantController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * 地址:/admin/group/index 默认访问
     * @throws Exception if the model cannot be found
     * @return array
     */

    public function actionSingle() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new SystemConfigModel();
            $params = $request->get(); //获取body传参
            $data['key'] = "design_config";
            $data['fields'] = " logo,title,copyright ";
            $array = $model->find($data);
            if ($array['status'] == 200) {
                $rs = json_decode($array['data']['value'], true);
                unset($array['data']);
                $array['data'] = $rs;
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate() {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new SystemConfigModel();
            $data['key'] = "design_config";
            $base = new Base64Model();
            $config = $model->find($data);

            if (isset($params['logo'])) {
                if ($params['logo'] != "") {
                    $str = creat_mulu("./uploads/design");
                    $params['logo'] = $base->base64_image_content($params['logo'], $str);
                    if (!$params['logo']) {
                        return result(500, "图片格式错误");
                    }
                    //将图片上传到cos
                    $cos = new CosModel();
                    $cosRes = $cos->putObject($params['logo']);
                    if ($cosRes['status'] == '200') {
                        $url = $cosRes['data'];
                    } else {
                        unlink(Yii::getAlias('@webroot/') . $params['pic_url']);
                        return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
                    }
                    $params['logo'] = $url;
                } else {
                    $config = json_decode($config['data']['value'], true);
                    $params['logo'] = $config['logo'];
                }
            } else {
                $config = json_decode($config['data']['value'], true);
                $params['logo'] = $config['logo'];
            }
            $data['value'] = json_encode($params);
            $rs = $model->update($data);
            return $rs;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionDelete($id) {
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $app = new AppModel();
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $array = $app->delete($params);
                return $array;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

}
