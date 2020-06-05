<?php

/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/4/12 14:58
 */

namespace app\models\core;

use app\models\admin\system\SystemCosModel;
use app\models\system\SystemPicServerModel;
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
                    'region' => $cos['data']['config']['region'],
                    'credentials' => array(
                        'appId' => $cos['data']['config']['appId'],
                        'secretId' =>$cos['data']['config']['secretId'],
                        'secretKey' => $cos['data']['config']['secretKey']
                    ))
            );
            $url = substr($res, 10);
            try {
                $args = array(
                    'Bucket' => $cos['data']['config']['Bucket'],
                    'Key' => $url,
                    'Body' => fopen(Yii::getAlias('@webroot/') . $res, 'rb'),
                );
                $result = $this->object2array($cosClient->putObject($args));
                $url =  $result['ObjectURL'];
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
                'region' => $cos['data']['config']['region'],
                'timeout' => '',
                'credentials' => array(
                    'appId' => $cos['data']['config']['appId'],
                    'secretId' =>$cos['data']['config']['secretId'],
                    'secretKey' => $cos['data']['config']['secretKey']
                ))
            );
            //  bucket的命名规则为{name}-{appid} ，此处填写的存储桶名称必须为此格式
            $result = $cosClient->deleteObject(array('Bucket' => $cos['data']['config']['Bucket'], 'Key' => $key));
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
        $model = new SystemPicServerModel();
        $where['type'] = 1; //1为腾讯云
        $where['status'] = 1;
        $cos  = $model->do_one($where);
        if ($cos['status'] == 200){
            $cos['data']['config'] = json_decode($cos['data']['config'],true);
        }
        return $cos;
    }

}
