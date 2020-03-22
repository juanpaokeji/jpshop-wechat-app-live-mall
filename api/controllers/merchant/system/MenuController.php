<?php

namespace app\controllers\merchant\system;

use app\models\admin\user\SystemAccessModel;
use app\models\merchant\system\GroupModel;
use app\models\merchant\system\UserModel;
use app\models\shop\ShopAuthGroupAccessModel;
use app\models\system\SystemMenuModel;
use yii\web\MerchantController;
use yii;

class MenuController extends MerchantController
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置


    public function actionList()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new SystemMenuModel();
            unset($params['key']);
            $params['orderby'] = "sort asc";
            $params['pid'] = 0;
            $array = $model->do_select($params);
            if ($array['status'] == 200) {
                for ($i = 0; $i < count($array['data']); $i++) {
                    $params['pid'] = $array['data'][$i]['id'];
                    $sub = $model->do_select($params);
                    if ($sub['status'] == 200) {
                        $array['data'][$i]['sub'] = $sub['data'];
                    }else{
                        $array['data'][$i]['sub'] = array();
                    }
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionMenu(){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new SystemMenuModel();


            if( yii::$app->session['sid']!=null){

                $groupAccess = new ShopAuthGroupAccessModel();
                $subUser = $groupAccess->do_one(['uid'=>yii::$app->session['sid'],'orderby'=>'uid asc']);

                if($subUser['status']!=200){
                    return result(500, "请求错误");
                }

                $groupModel = new GroupModel();
                $group = $groupModel->find(['id'=>$subUser['data']['group_ids']]);
               // var_dump($group['data']['rules']);die();
                unset($params['key']);
                $params['limit'] = false;
                $params['in'] = ['id',explode(",",$group['data']['rules'])];
                $params['orderby'] = "sort asc";
                $params['pid'] = 0;
               // var_dump($params);die();
                $array = $model->do_select($params);
                if ($array['status'] == 200) {
                    for ($i = 0; $i < count($array['data']); $i++) {
                        $params['pid'] = $array['data'][$i]['id'];
                        $sub = $model->do_select($params);
                        if ($sub['status'] == 200) {
                            $array['data'][$i]['sub'] = $sub['data'];
                        }else{
                            $array['data'][$i]['sub'] = array();
                        }
                    }
                }
            }else{
                return result(500, "请求错误");
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
