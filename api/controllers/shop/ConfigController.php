<?php
namespace app\controllers\shop;


use app\models\merchant\partnerUser\PartnerUserModel;
use app\models\system\PluginModel;
use app\models\system\SystemAppAccessVersionModel;
use app\models\system\SystemPluginAccessModle;
use app\models\system\SystemPluginModel;
use Yii;
use app\models\admin\app\AppAccessModel;
use yii\web\ShopController;

class ConfigController extends ShopController{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors() {
        return [
            'token' => [
                'class' => 'yii\filters\ShopFilter', //调用过滤器
//                'only' => ['single'],//指定控制器应用到哪些动作
                'except' => ['list','search-partner'],//指定控制器不应用到哪些动作
            ]
        ];
    }

    public function actionList()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $appModel = new AppAccessModel();
            $res = $appModel->find(['`key`'=>$params['key']]);
            return $res;
               if ($res['status'] == 200) {
                    $data['app_id'] = $res['data']['app_id'];
                    $data['limit'] = 1000;
                    $data['status'] = 1;
                } else {
                    return $res;
                }

                //当access表中无插件记录时默认为关闭
                $pluginModel = new SystemPluginModel();
                $accessModel = new SystemPluginAccessModle();
                $pluginInfo = $pluginModel->do_select($data); //所有能用的插件
                if ($pluginInfo['status'] != 200){
                    return $pluginInfo;
                }
                $array = [];
                foreach ($pluginInfo['data'] as $k=>$v){
                    $array[$k]['name'] = $v['name'];
                    $array[$k]['english_name'] = $v['english_name'];
                    $params['plug_in_id'] = $v['id'];
                    $accessInfo = $accessModel->do_one($params);
                    if ($accessInfo['status'] == 200) {
                        $array[$k]['is_open'] = $accessInfo['data']['is_open'];
                    } else {
                        $array[$k]['is_open'] = 0;
                    }
                }
            return result(200, "请求成功",$array);
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 检测登陆用户是属于哪个合伙人
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionSearchPartner(){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            if(!isset($params['longitude']) || empty($params['longitude'])){
                return result(500, "缺少参数longitude");
            }
            if(!isset($params['latitude']) || empty($params['latitude'])){
                return result(500, "缺少参数latitude");
            }
            //校验商户是否关闭合伙人设置
            $app = new \app\models\merchant\app\AppAccessModel();
            $info = $app->find(['key' => $params['key'], 'open_partner' => 1]);
            if($info['status'] != 200){
                return result(201, "应用已关闭合伙人设置");
            }
            $partnerModel = new PartnerUserModel();
            $result = $partnerModel->getAddrGD($params['longitude'] . ',' . $params['latitude'], 1);
            if(!$result){
                return result(500, "地址解析失败！");
            }
            $partnerInfo = $partnerModel->one(['adcode'=>$result]);
            if($partnerInfo['status'] == 200){
                if($partnerInfo['data']['time_type'] == 1 || ($partnerInfo['data']['time_type'] == 2 && $partnerInfo['data']['expired_time'] >= time())){
                    return result(200, "请求成功",$partnerInfo['data']['id']);
                }
                return result(2019, "合伙人设置出错");
            }elseif($partnerInfo['status'] == 204){
                return result(2019, "没查到合伙人信息");
            }
            return result(500, "请求失败");
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionPlugin(){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new PluginModel();
            $res = $model->do_select(['status'=>1,'>='=>['end_time',time()]]);
            return $res;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
