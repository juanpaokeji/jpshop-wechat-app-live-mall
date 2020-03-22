<?php

namespace app\controllers\admin\message;

use yii;
use yii\web\Controller;
use yii\db\Exception;
use app\models\admin\message\NoticeModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class NoticeController extends Controller {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * 地址:/admin/group/index 默认访问
     * @throws Exception if the model cannot be found
     * @return array
     */

    public function actionIndex() {
//        $uid = $_SESSION['uid'];//获取当前登录的用户 id，如果需要的话
        $request = yii::$app->request; //获取 request 对象
        $method = $request->getMethod(); //获取请求方式 GET POST PUT DELETE
        if ($method == 'GET') {
            $params = $request->get(); //获取地址栏参数
        } else {
            $params = $request->bodyParams; //获取body传参
        }

        $notice = new NoticeModel();
        switch ($method) {
            case 'GET':
                if (!isset($params['searchName'])) {
                    $array = ['status' => 400, 'message' => '缺少参数 searchName',];
                    return json_encode($array, JSON_UNESCAPED_UNICODE);
                }
                if ($params['searchName'] == "list") {
                    $array = $notice->findall($params);
                } else if ($params['searchName'] == "single") {
                    $array = $notice->find($params);
                } else {
                    $array = ['status' => 501, 'message' => '无该 searchName 请求',];
                    return json_encode($array, JSON_UNESCAPED_UNICODE);
                }
                break;
            case 'POST':
                $must = ['marchant_id', 'type', 'title', 'content'];
                $rs = $this->checkInput($must, $params);
                if ($rs != true) {
                    return $rs;
                }
                $array = $notice->add($params);
                break;
            case 'PUT':
                if (!isset($params['id'])) {
                    $array = ['status' => 400, 'message' => '缺少参数 id',];
                } else {
                    $array = $notice->update($params);
                }
                break;
            case 'DELETE':
                if (!isset($params['id'])) {
                    $array = ['status' => 400, 'message' => '缺少参数 id',];
                } else {
                    $array = $notice->delete($params);
                }
                break;
            default:
                $array = ['status' => 404, 'message' => 'ajax请求类型错误，找不到该请求',];
        }
        return $array;
    }

}
