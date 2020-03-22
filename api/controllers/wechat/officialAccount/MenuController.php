<?php

/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/4/18 9:03
 */

namespace app\controllers\wechat\officialAccount;

use yii\web\MerchantController;
use EasyWeChat\Factory;
use app\models\system\SystemAutoWordsModel;

class MenuController extends MerchantController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置
    public $config = [
        'app_id' => 'wx8df3a6f4a4f9ec54',
        'secret' => '7188287cd30aa902d5933654fed60559',
        'token' => 'juanPao',
        'aes_key' => '9ILejPm7rpu5kJykkY13oHMO80bYJkNbQfCvL3otaWA'
    ];

    /**
     * 控制器默认显示菜单列表 get 请求
     * @return array|void
     */
    public function actionIndex() {

        $request = request();
        //判断请求方式
        if ($request['method'] != 'GET') {
            return result('500', '请求失败');
        }
        //获取微信配置信息
        $params = $request['params'];
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
        $list = $app->menu->list();

        //获取公众号实例 必须
        //无菜单会返回 errcode ，有菜单直接返回 menu，不返回状态码，比较坑

        if (isset($list['errcode'])) {
//            if($list['errcode']!="ok"){
//                return result($list['errcode'], $list['errmsg']);
//            }
            return result('200', '暂无菜单', $list);
        }


        if (isset($list['menu'])) {
            return result('200', '请求成功', $list['menu']);
        }
    }

    /**
     * 创建菜单，和修改一样，先删除，后新增。。。
     */
    public function actionCreate() {
        $request = request();

        //判断请求方式
        if ($request['method'] != 'POST') {
            return result('500', '请求失败');
        }
        $params = $request['params'];
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

        $list = $app->menu->list();
        //获取公众号实例 必须
        $button = array();
        if (isset($list['menu'])) {
            $button = $list['menu'];
        }

        //删除菜单
        $delRes = $app->menu->delete();
        if ($delRes['errcode'] != 0) {
            return result('200', '修改中删除操作失败', $delRes);
        }
        $res = $app->menu->create($params['button']);
        if ($res['errcode'] == 0) {
            return result('200', '创建成功');
        } else {
            if (isset($button['button'])) {
                $app->menu->create($button['button']);
            }
            return result('500', '创建失败', $res);
        }
    }

    /**
     * 删除全部菜单
     * @return array
     */
    public function actionDelete() {
        $request = request();
        //判断请求方式
        if ($request['method'] != 'DELETE') {
            return result('500', '请求失败');
        }

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
        //获取公众号实例 必须
        $res = $app->menu->delete();
        if ($res['errcode'] == 0) {
            return result('200', '删除成功');
        } else {
            return result('500', '删除失败', $res);
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
