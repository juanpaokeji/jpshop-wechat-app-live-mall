<?php

/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/4/12 14:58
 */

namespace app\models\core;

use app\models\admin\system\SystemCosModel;
use Qcloud\Cos\Client;
use Yii;

require(Yii::getAlias('@vendor/tencentyun/cos-php-sdk-v5/cos-autoloader.php'));

header('Access-Control-Allow-Headers:Access-Token');
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:GET,POST,PUT,DELETE');

class CosModel {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * @param object|array $object 对象
     * @return array
     */

    public function object2array($object) {
        if (is_object($object)) {
            $array = [];
            foreach ($object as $key => $value) {
                $array[$key] = $value;
            }
            return $array;
        } else {
            return $object;
        }
    }

    /**
     * 腾讯云  对象存储
     * @param string $res  页面file标签input=file name='$name'
     * @return array
     */
    public function putObject($res) {
        if ($res) {
            $cos =  $this->cos();
            if($cos['status']!=200){
                return result(500,'未配置cos信息');
            }
            $cosClient = new Client(array(
                    'region' => 'cn-south',
                    'credentials' => array('appId' => $cos['data'][0]['appId'], 'secretId' =>$cos['data'][0]['secretId'], 'secretKey' => $cos['data'][0]['secretKey'], 'token' => $cos['data'][0]['token']))
            );
            $url = substr($res, 10);
            try {
                $args = array(
                    'Bucket' => $cos['data'][0]['Bucket'],
                    'Key' => $url,
                    'Body' => fopen(Yii::getAlias('@webroot/') . $res, 'rb'),
                );
                $result = $this->object2array($cosClient->putObject($args));
                $url = str_replace('http://juanpao999-1255754174.cos.cn-south.myqcloud.com', 'https://imgs.juanpao.com', $result['ObjectURL']);
                return [
                    'status' => '200',
                    'data' => $url,
                ];
            } catch (\Exception $e) {
                return [
                    'status' => '500',
                    'message' => $e->getMessage()
                ];
            }
        }
    }

    /**
     * 腾讯云  对象删除
     * @param string $key
     * @return array
     */
    public function deleteObject($key) {
        try {
             $cos =  $this->cos();
             if($cos['status']!=200){
                 return result(500,'未配置cos信息');
             }
            $cosClient = new Client(array(
                'region' => 'cn-south',
                'timeout' => '',
                'credentials' => array('appId' => $cos['data'][0]['appId'], 'secretId' =>$cos['data'][0]['secretId'], 'secretKey' => $cos['data'][0]['secretKey'], 'token' => $cos['data'][0]['token']))
            );
            //  bucket的命名规则为{name}-{appid} ，此处填写的存储桶名称必须为此格式
            $result = $cosClient->deleteObject(array('Bucket' => 'juanpao999-1255754174', 'Key' => $key));
            $this->object2array($result);
            return json_encode([
                'status' => '200',
                'message' => '请求成功',
                    ], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return json_encode(['status' => '500', 'message' => $e], JSON_UNESCAPED_UNICODE);
        }
    }

     function cos(){
        $cosModel = new SystemCosModel();
        $cos  = $cosModel->do_select([]);
         return $cos;
    }

}
