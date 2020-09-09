<?php
namespace app\models\core\aliyun;

use app\models\admin\system\SystemSmsModel;
use Yii;
use app\models\core\aliyun\SignatureHelper;

class AliSms {
    /**
     * 发送短信
     */
    public function sendSms($phone,$sign,$templId,$data = []) {
        $params = array ();
        //必填：是否启用https
        $security = false;

        //必填: 请参阅 https://ak-console.aliyun.com/ 取得您的AK信息
        $model = new SystemSmsModel();
        $where['type'] = 2; //阿里云
        $where['status'] = 1;
        $smsInfo = $model->do_one($where); //查询阿里云配置
        if ($smsInfo['status'] != 200){
            return result(204, '阿里云配置查询失败');
        }
        $smsInfo['data']['config'] = json_decode($smsInfo['data']['config'], true);
        $accessKeyId = $smsInfo['data']['config']['access_key_id'];
        $accessKeySecret = $smsInfo['data']['config']['access_key_secret'];
        $params["PhoneNumbers"] = $phone; //必填: 短信接收号码
        $params["SignName"] = $sign; //必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $params["TemplateCode"] = $templId; //必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template

        if(!empty($data) && is_array($data)) {
            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE); //可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
        }

        // 初始化SignatureHelper实例用于设置参数，签名以及发送请求
        $helper = new SignatureHelper();

        // 此处可能会抛出异常，注意catch
        try {
            $content = $helper->request(
                $accessKeyId,
                $accessKeySecret,
                "dysmsapi.aliyuncs.com",
                array_merge($params, array(
                    "RegionId" => "cn-hangzhou",
                    "Action" => "SendSms",
                    "Version" => "2017-05-25",
                )),
                $security
            );
            $content = (array)$content;
            if ($content['Code'] == 'OK'){
                return result(200, "发送成功",$content);
            }else{
                return result(500, $content['Message']);
            }
        } catch (\Exception $e) {
            return result(500, "内部错误");
        }

    }

}

