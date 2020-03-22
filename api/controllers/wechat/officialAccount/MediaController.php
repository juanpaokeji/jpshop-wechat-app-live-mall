<?php

/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/4/18 9:03
 */

namespace app\controllers\wechat\officialAccount;

use yii\web\MerchantController;
use app\models\core\Base64Model;
use EasyWeChat\Factory;

class MediaController extends MerchantController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置
    public $config = [
        'app_id' => 'wx8df3a6f4a4f9ec54',
        'secret' => '7188287cd30aa902d5933654fed60559',
        'token' => 'juanPao',
        'aes_key' => '9ILejPm7rpu5kJykkY13oHMO80bYJkNbQfCvL3otaWA'
    ];

    public function actionIndex() {
        $request = request();
        //判断请求方式
        if ($request['method'] != 'GET') {
            return result('500', '请求失败');
        }
        $params = $request['params'];
        $must = ['key'];
        $rs = $this->checkInput($must, $params);
        if ($rs != false) {
            return json_encode($rs, JSON_UNESCAPED_UNICODE);
        }

        //获取微信配置信息
        //获取微信配置信息
        $config = $this->getSystemConfig($params['key'], "wechat");

        if ($config == false) {
            return result(500, "未配置微信信息");
        }

        //获取公众号实例 必须
        try {
            $app = $this->getApp($config);
        } catch (\Exception $e) {
            return result(500, '配置信息错误');
        }

        //获取已创建菜单列表
        $result = "";
        if (isset($params['media_id'])) {
            $result = $app->material->get($params['media_id']);
        } else {
            $limit = 18;
            if ($params['page'] != 0) {
                $result = $app->material->list($params['type'], ($params['page'] - 1) * $limit, $limit);
            } else {
                $result = $app->material->list($params['type'], 0, $limit);
            }
        }
        return result(200, "请求成功", $result);
    }

    public function actionUploads() {
        $request = request();
        //判断请求方式
        if ($request['method'] != 'POST') {
            return result('500', '请求失败');
        }
        $params = $request['params'];
        $must = ['key'];
        $rs = $this->checkInput($must, $params);
        if ($rs != false) {
            return json_encode($rs, JSON_UNESCAPED_UNICODE);
        }
        //获取微信配置信息
        //获取微信配置信息
        $config = $this->getSystemConfig($params['key'], "wechat");
        if ($config == false) {
            return result(500, "未配置微信信息");
        }
        //获取公众号实例 必须
        try {
            $app = $this->getApp($config);
        } catch (\Exception $e) {
            return result(500, '配置信息错误');
        }
        $base = new Base64Model();
        $result = "";
        if ($params['type'] == "image") {
            $path = $base->base64_image_content($params['pic'], "./uploads/media");
            $result = $app->material->uploadImage($path);
        } else if ($params['type'] == "news") {
            $article = new Article([
                'title' => 'EasyWeChat 4.0 发布了！',
                'thumb_media_id' => $params['thumb_media_id'], // 封面图片 mediaId
                'author' => 'overtrue', // 作者
                'show_cover' => 1, // 是否在文章内容显示封面图片
                'digest' => '这里是文章摘要',
                'content' => '',
                'source_url' => 'https://www.easywechat.com',
            ]);
            $result = $app->material->uploadArticle($article);
        } else {
            return result('500', '哈哈哈哈哈哈');
        }
        return result(200, "请求成功", $result);
    }

    /**
     * 删除全部菜单
     * @return array
     */
    public function actionDelete() {
        $request = request();
        $params = $request['params'];
        //判断请求方式
        if ($request['method'] != 'DELETE') {
            return result('500', '请求失败');
        }
        $must = ['key'];
        $rs = $this->checkInput($must, $params);
        if ($rs != false) {
            return json_encode($rs, JSON_UNESCAPED_UNICODE);
        }
        //获取微信配置信息
        //获取微信配置信息
        $config = $this->getSystemConfig($params['key'], "wechat");
        if ($config == false) {
            return result(500, "未配置微信信息");
        }
        //获取公众号实例 必须
        //获取公众号实例 必须
        try {
            $app = $this->getApp($config);
        } catch (\Exception $e) {
            return result(500, '配置信息错误');
        }
        if (is_array($params['media_id'])) {
            for ($i = 0; $i < count($params['media_id']); $i++) {
                $app->material->delete($params['media_id'][$i]);
            }
            return result(200, '请求成功');
        } else {
            return result(500, '请求失败');
        }
    }

    public function getApp($config) {
        if ($config['type'] == 2) {
            //获取公众号实例 必须
            $appId = $config['app_id'];
            $refreshToken = $config['refresh_token'];
            $openPlatform = Factory::openPlatform($this->config);
            $app = $openPlatform->officialAccount($appId, $refreshToken);
        } else {
            $app = Factory::officialAccount($config);
        }
        return $app;
    }

}
