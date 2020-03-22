<?php

namespace app\controllers\message;

use app\models\system\SystemMerchantTemplateMiniAccessModel;
use yii;
use yii\db\Exception;
use yii\web\Controller;
use EasyWeChat\Factory;
use app\models\core\TableModel;
use app\models\system\SystemMerchantMiniAccessModel;
use app\models\system\SystemFormModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class MessageController extends Controller
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置
    public $config = [
        'app_id' => 'wx8df3a6f4a4f9ec54',
        'secret' => '7188287cd30aa902d5933654fed60559',
        'token' => 'juanPao',
        'aes_key' => '9ILejPm7rpu5kJykkY13oHMO80bYJkNbQfCvL3otaWA'
    ];

    public function actionIndex()
    {

        $model = new SystemMerchantMiniAccessModel();

        $message = $model->do_select(['status' => -1, 'limit' => 30]);

        if ($message['status'] == 204) {
            return $message;
        }
        if ($message['status'] == 500) {
            return $message;
        }

        $model = new TableModel();
        $time = time() + (3600 * 24 * 3);
        $sql = " DELETE  FROM  system_mini_formid WHERE create_time > {$time}";
        Yii::$app->db->createCommand($sql)->execute();
//        var_dump(count($message['data']));
        for ($i = 0; $i < count($message['data']); $i++) {
//            var_dump($message['data'][$i]['id']);
//            echo "</br>";
            $config = $this->getSystemConfig($message['data'][$i]['key'], "miniprogram");
            $openPlatform = Factory::openPlatform($this->config);

            if ($message['data'][$i]['template_purpose'] == 'order') {
                $formModel = new SystemFormModel();
                $form = $formModel->do_one(['mini_open_id' => $message['data'][$i]['mini_open_id'], 'merchant_id' => $message['data'][$i]['merchant_id'], 'key' => $message['data'][$i]['key'], 'status' => 1]);
                if ($form['status'] == 200) {
                    $mtemp = new \app\models\system\SystemMerchantMiniTemplateModel;
                    $mmtemp = $mtemp->do_one(['system_mini_template_id' => $message['data'][$i]['template_id'],'merchant_id'=>$message['data'][$i]['merchant_id'],'key'=>$message['data'][$i]['key']]);
                    $model = new SystemMerchantMiniAccessModel();
                    // 代小程序实现业务
                    $miniProgram = $openPlatform->miniProgram($config['app_id'], $config['refresh_token']);
                    $data = json_decode($message['data'][$i]['template_params'], true);
                    try {
                        $res = $miniProgram->template_message->send([
                            'touser' => $message['data'][$i]['mini_open_id'],
                            'template_id' => $mmtemp['data']['template_id'],
                            'page' => "/pages/orderItem/orderItem/orderItem?order_sn={$data['keyword1']}",
                            'form_id' => $form['data']['formid'],
                            'data' => $data
                        ]);
                    }catch (\Exception $exception){
                        $model->do_update(['id' => $message['data'][$i]['id']], ['status' => 1]);
                      //  $rs = $formModel->do_update(['mini_open_id' => $message['data'][$i]['mini_open_id'], 'merchant_id' => $message['data'][$i]['merchant_id'], 'key' => $message['data'][$i]['key']], ['status' => 2]);
                        $formModel->do_update(['id' => $form['data']['id']], ['status' => 2]);
                        break;
                    }

                    if($message['data'][$i]['id'] == 1977){
//                        var_dump([
//                            'touser' => $message['data'][$i]['mini_open_id'],
//                            'template_id' => $mmtemp['data']['template_id'],
//                            'page' => "/pages/orderItem/orderItem/orderItem?order_sn={$data['keyword1']}",
//                            'form_id' => $form['data']['formid'],
//                            'data' => $data
//                        ]);
//                        var_dump($res);
                    }
                    if ($res['errcode'] == "ok") {
                        $model->do_update(['id' => $message['data'][$i]['id']], ['status' => 1]);
                        $formModel->do_update(['id' => $form['data']['id']], ['status' => 2]);
                    }
//                    var_dump($res);
//                    echo "</br.";
                }
            }
        }

        $assessModel = new SystemMerchantTemplateMiniAccessModel();
        $assess = $assessModel->do_select(['status' => -1]);
        $formModel = new SystemFormModel();

        if ($assess['status'] == 200) {
            $id = array();
            for ($i = 0; $i < count($assess['data']); $i++) {


                $form = $formModel->do_select(['status' => 1, 'key' => $assess['data'][$i]['key'], 'groupBy' => 'mini_open_id']);
                $config = $this->getSystemConfig($assess['data'][$i]['key'], "miniprogram");
                $openPlatform = Factory::openPlatform($this->config);

                $miniProgram = $openPlatform->miniProgram($config['app_id'], $config['refresh_token']);

                for ($j = 0; $j < count($form['data']); $j++) {
                    if ($form['data'][$j]['key'] == $assess['data'][$i]['key']) {
                        $data = json_decode($assess['data'][$i]['template_params'], true);
                        $json = array();
                        for ($k = 1; $k <= count($data); $k++) {
                            $json["keyword{$k}"] = $data[$k - 1]['example'];
                        }


                        try {
                            $page = json_decode($assess['data'][$i]['page'], true);
                            $res = $miniProgram->template_message->send([
                                'touser' => $form['data'][$j]['mini_open_id'],
                                'template_id' => $assess['data'][$i]['template_id'],
                                'page' => $page[0]['page_url'],
                                'form_id' => $form['data'][$j]['formid'],
                                'data' => $json
                            ]);
//                            var_dump($res);
//                            echo "</br.";
                        }catch (\Exception $exception){
                            var_dump($message['data'][$i]['key']);
                            $assessModel->do_update(['status' => -1], ['status' => 1]);
                            break;
                        }

                        $id[] = $form['data'][$j]['id'];
                    }
                }
            }
            $assessModel->do_update(['status' => -1], ['status' => 1]);
            $formModel->do_update(['id' => $id], ['status' => 2]);
        }
        die();
        return true;
    }

}
