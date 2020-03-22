<?php

namespace app\controllers\admin\user;

use app\models\admin\user\RuleModel;
use yii;
use yii\web\CommonController;
use yii\db\Exception;
use app\models\admin\user\GroupModel;

/**
 * 角色接口控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class GroupController extends CommonController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * 地址:/admin/group/index 默认访问
     * @throws Exception if the model cannot be found
     * @return array
     */
    public function actionList() {

        if (yii::$app->request->isGet) {
            $request = yii::$app->request;
            $params = $request->get(); //获取地址栏参数
            if (isset($params['searchName'])) {
                $params["title like '%{$params['searchName']}%'"] = null;
                unset($params['searchName']);
            }

            $group = new GroupModel();
            $rule  = new RuleModel();
            $array = $group->findall($params);
            if(is_array($array) && !empty($array['data'])){
                foreach ($array['data'] as $key=>$val){
                    $rule_ids = trim($val['rules'],',');
                    $sql = "SELECT `name`,`title` FROM system_auth_rule WHERE id IN ({$rule_ids})";
                    $rule_lists = $rule->querySql($sql);
                    if(is_array($rule_lists) && !empty($rule_lists)){
                        $str = '';
                        foreach ($rule_lists as $val){
                            $str .= $val['name'].$val['title'].'/';
                            $array['data'][$key]['rules_text'] = $str;
                        }
                    }
                }
            }
            return $array;
        }
    }

    public function actionSingle($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request;
            $params = $request->get(); //获取地址栏参数

            $group = new GroupModel();
            $params['id'] = $id;
            if (!isset($params['id'])) {
                $array = ['status' => 400, 'message' => '缺少参数 id',];
            } else {
                $array = $group->find($params);
            }
            return $array;
        }
    }

    public function actionRule() {

        if (yii::$app->request->isGet) {
            $request = yii::$app->request;
            $params = $request->get(); //获取地址栏参数

            $group = new GroupModel();
            $params['id'] = $id;
            if (!isset($params['id'])) {
                $array = ['status' => 400, 'message' => '缺少参数 角色id  group_id',];
            } else {
                $array = $group->rule($params);
            }
            return $array;
        }
    }

    public function actionUsers($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request;
            $params = $request->get(); //获取地址栏参数

            $group = new GroupModel();
            $params['group_id'] = $id;
            if (!isset($params['group_id'])) {
                $array = ['status' => 400, 'message' => '缺少参数 group_id',];
            } else {
                $array = $group->users($params);
            }
            return $array;
        }
    }

    public function actionAdd() {
        $request = yii::$app->request; //获取 request 对象
        $params = $request->bodyParams; //获取body传参
        $group = new GroupModel();

        $must = ['title', 'status'];
        $rs = $this->checkInput($must, $params);

        if ($rs != false) {
            return $rs;
        }
        $array = $group->add($params);
        return $array;
    }

    public function actionUpdate($id) {
        $request = yii::$app->request; //获取 request 对象
        $params = $request->bodyParams; //获取body传参

        $group = new GroupModel();


        $params['id'] = $id;
        $array = $group->update($params);

        return $array;
    }

    public function actionDelete($id) {
        $request = yii::$app->request; //获取 request 对象
        $params = $request->bodyParams; //获取body传参

        $group = new GroupModel();
        $params['id'] = $id;
        if (!isset($params['id'])) {
            $array = ['status' => 400, 'message' => '缺少参数 id',];
        } else {
            $array = $group->delete($params);
        }
        return $array;
    }

}
