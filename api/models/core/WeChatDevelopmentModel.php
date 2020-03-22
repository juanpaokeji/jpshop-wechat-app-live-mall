<?php

namespace app\models\core;

use app\models\pay\WeChatConfigModel;
use app\models\system\SystemConfigModel;

class WeChatDevelopmentModel {

    const API_URL = 'https://api.weixin.qq.com'; //默认微信请求前缀

    /**
     * 获取该公众号用户code
     * @param $config
     * @throws
     * @return array
     */

    public function getCode($config) {
        $appID = $config['app_id'];
        $url = "http://api.juanpao.com/wxinfo";
        redirect("https://open.weixin.qq.com/connect/oauth2/authorize?appid={$appID}&redirect_uri={$url}&response_type=code&scope=snsapi_userinfo&state=true#wechat_redirect");
        //返回正确token 9_kXwgdY8OGB3WyIjdAfxVpQWnZMGj45dc4kzRw0mYTeOT3PQUp67NoFgNyq4yfqs7QISkZoW0s7nDvVxtJgpvhx0eJ-BrE20Fh2QZ3LGAy7W7Pk7uz_8Al0M9GO9JXOop72m1Itmgi9yLyj7jKKAiAGAFFV
    }

    /**
     * 获取该公众号token
     * @param $config
     * @throws
     * @return array
     */
    public function getToken($config, $key) {
        $appID = $config['app_id'];
        $appSecret = $config['app_secret'];
        $result = $this->curlGet(
                self::API_URL . "/cgi-bin/token?grant_type=client_credential&appid=$appID&secret=$appSecret"
        );
        $result = json_decode($result);

        //判断token是否获取成功
        if (isset($result->errcode)) {
            return ['status' => 500, 'message' => 'token获取失败', 'errcode' => $result->errcode, 'errmsg' => $result->errmsg];
        }
        //获取成功则将获取到的token存到appid对应的数据库中
        $systemConfigModel = new SystemConfigModel();
        $data['key'] = "app_key_" . $key;
        $arr = $systemConfigModel->find($data);
        $arr = json_decode($arr['data']['value'], true);
        $arr['wechat']['access_token'] = $result->access_token;
        $arr['wechat']['expires_in'] = $result->expires_in;
        $arr['wechat']['update_time'] = time();
        $configData['value'] = json_encode($arr, JSON_UNESCAPED_UNICODE);
        $configData['`key`'] = $key;

        $res = $systemConfigModel->update($configData);
        if ($res['status'] != 200) {
            return $res;
        }
        return ['status' => 200, 'access_token' => $result->access_token, 'expires_in' => $result->expires_in];
        //返回正确token 9_kXwgdY8OGB3WyIjdAfxVpQWnZMGj45dc4kzRw0mYTeOT3PQUp67NoFgNyq4yfqs7QISkZoW0s7nDvVxtJgpvhx0eJ-BrE20Fh2QZ3LGAy7W7Pk7uz_8Al0M9GO9JXOop72m1Itmgi9yLyj7jKKAiAGAFFV
    }

    /**
     * 通过用户code获取授权登录用户token
     * @param $code
     * @param $config
     * @return array|string
     */
    public function getUserToken($code, $config) {
        $appID = $config['app_id'];
        $appSecret = $config['app_secret'];
        $result = $this->curlGet(
                self::API_URL . "/sns/oauth2/access_token?appid=$appID&secret=$appSecret&code=$code&grant_type=authorization_code"
        );
        return $result;
        //正确返回 {"access_token":"9_7cAmAKcKTfZewAQLpxMTQCsGObZeWrieB4yABMo3PCJyoRdXfyTy8y1IUIXHnYt31QToWcQVyjOZUGrzn0jl_g","expires_in":7200,"refresh_token":"9_aAa4Q6EFUfOA1wYDrsLqjBJ0Nm9bN3p0Gv75FGCPEKorH4oO8_mqL0wJaXsR-elfgCuP3M7yq1dICZIv4fibPg","openid":"oCkxFwXWfKj-e2gsMqXeC-VBEBgA","scope":"snsapi_userinfo"}
    }

    /**
     * http GET方式请求获得jsapi_ticket
     * @param $code
     * @param $config
     * @return array|string
     */
    public function getJsapi_Ticket($config, $key) {
        $access_token = $config['access_token'];
        $result = $this->curlGet("https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={$access_token}&type=jsapi");
        $result = json_decode($result, true);
        //获取成功则将获取到的token存到appid对应的数据库中
        $systemConfigModel = new SystemConfigModel();
        $data['key'] = $key;
        $arr = $systemConfigModel->find($data);
        $arr = json_decode($arr['data']['value'], true);
        $arr['wechat']['access_token'] = $access_token;
        $arr['wechat']['ticket'] = $result['ticket'];
        $arr['wechat']['update_time'] = time();
        $configData['value'] = json_encode($arr, JSON_UNESCAPED_UNICODE);
        $configData['`key`'] = $key;
        $res = $systemConfigModel->update($configData);
        if ($res['status'] != 200) {
            return $res;
        }
        return $result;
        //正确返回 {"access_token":"9_7cAmAKcKTfZewAQLpxMTQCsGObZeWrieB4yABMo3PCJyoRdXfyTy8y1IUIXHnYt31QToWcQVyjOZUGrzn0jl_g","expires_in":7200,"refresh_token":"9_aAa4Q6EFUfOA1wYDrsLqjBJ0Nm9bN3p0Gv75FGCPEKorH4oO8_mqL0wJaXsR-elfgCuP3M7yq1dICZIv4fibPg","openid":"oCkxFwXWfKj-e2gsMqXeC-VBEBgA","scope":"snsapi_userinfo"}
    }

    /**
     * 通过用户token获取授权登录用户信息
     * @param $token
     * @return array|string
     */
    public function getUserInfo($token) {
        $access_token = $token['access_token'];
        $openid = $token['openid'];
        $result = $this->curlGet(
                self::API_URL . "/sns/userinfo?access_token=$access_token&openid=$openid&lang=zh_CN"
        );
        return $result;
        //正确返回 {"openid":"oCkxFwXWfKj-e2gsMqXeC-VBEBgA","nickname":"对方正在输入...","sex":1,"language":"zh_CN","city":"连云港","province":"江苏","country":"中国","headimgurl":"http:\/\/thirdwx.qlogo.cn\/mmopen\/vi_32\/DYAIOgq83erALNpuZFB4awN8pDv42C2DCibBibEdQVLjEib2QMjWxFZ7DAuGq6AbcAORlaCGwuK1d2ZPNuiaDqzgpg\/132","privilege":[]}
        //错误返回 {"errcode":40163,"errmsg":"code been used, hints: [ req_id: YeLFya0574th50 ]"}
    }

    /**
     * 获取公众号下的用户列表 openid
     * @param array $config
     * @return array|string
     */
    public function getUsersOpenId($config) {
        $access_token = $config['access_token'];
        $result = $this->curlGet(
                self::API_URL . "/cgi-bin/user/get?access_token=$access_token"
//            self::API_URL . "/cgi-bin/user/get?access_token=ACCESS_TOKEN&next_openid=NEXT_OPENID"
        );
        return $result;
        //正确返回 {"total":1,"count":1,"data":{"openid":["oCkxFwS7MijCL-uAOfRpideb7bx0"]},"next_openid":"oCkxFwS7MijCL-uAOfRpideb7bx0"}
        //错误返回 {"errcode":40163,"errmsg":"code been used, hints: [ req_id: YeLFya0574th50 ]"}
    }

// https://api.weixin.qq.com/cgi-bin/user/info?access_token=ACCESS_TOKEN&openid=OPENID&lang=zh_CN 
    /**
     * 获取公众号下的用户列表 openid
     * @param array $config
     * @return array|string
     */
    public function getUsersUnionid($config, $openid) {
        $access_token = $config['access_token'];
        $result = $this->curlGet(self::API_URL . "/cgi-bin/user/info?access_token={$access_token}&openid={$openid}&lang=zh_CN");
        return $result;
        //正确返回 {"total":1,"count":1,"data":{"openid":["oCkxFwS7MijCL-uAOfRpideb7bx0"]},"next_openid":"oCkxFwS7MijCL-uAOfRpideb7bx0"}
        //错误返回 {"errcode":40163,"errmsg":"code been used, hints: [ req_id: YeLFya0574th50 ]"}
    }

    /**
     * 获取公众号下的单个用户信息
     * @param $config
     * @param $openid
     * @return array|string
     */
    public function getUser($config, $openid) {
        $access_token = $config['access_token'];
        $result = $this->curlGet(
                self::API_URL . "/cgi-bin/user/info?access_token=$access_token&openid=$openid&lang=zh_CN"
//            self::API_URL . "/cgi-bin/user/get?access_token=ACCESS_TOKEN&next_openid=NEXT_OPENID"
        );
        return $result;
        //正确返回 {"subscribe":1,"openid":"oCkxFwS7MijCL-uAOfRpideb7bx0","nickname":"@@@@","sex":1,"language":"zh_CN","city":"","province":"","country":"冰岛","headimgurl":"http://thirdwx.qlogo.cn/mmopen/1RV1ibzqaYGu9ZKXJAuIAu3rQLBpnofMHhYtnhpzEbO5Mkw4Fbj0m2sdoCNIELXpbia1KyQEahgC1y9p7oqkbk2dibHqFZC9NOS/132","subscribe_time":1524642768,"remark":"","groupid":0,"tagid_list":[],"subscribe_scene":"ADD_SCENE_QR_CODE","qr_scene":0,"qr_scene_str":""}
        //错误返回 {"errcode":40163,"errmsg":"code been used, hints: [ req_id: YeLFya0574th50 ]"}
    }

    /**
     * 创建菜单menu
     * @param string $data
     * @param array $config
     * @return array|string
     */
    public function menuCreate($data, $config) {
        $access_token = $config['access_token'];
        $result = $this->curlPost(
                self::API_URL . "/cgi-bin/menu/create?access_token=$access_token", $data
        );
        return $result;
        //正确返回 {"errcode":0,"errmsg":"ok"}
        //错误返回 {"errcode":85005,"errmsg":"appid not bind weapp hint: [bxsJza0543vr29]"}
    }

    /**
     * 查询菜单
     * @param array $config
     * @return array|string
     */
    public function menuGet($config) {
        $access_token = $config['access_token'];
        $result = $this->curlGet(
                self::API_URL . "/cgi-bin/menu/get?access_token=$access_token"
        );
        return $result;
        //正确返回 {"menu":{"button":[{"type":"click","name":"今日歌曲","key":"V1001_TODAY_MUSIC","sub_button":[]},{"name":"菜单","sub_button":[{"type":"view","name":"搜索","url":"http://www.soso.com/","sub_button":[]},{"type":"click","name":"赞一下我们","key":"V1001_GOOD","sub_button":[]}]}]}}
        //错误返回 {"status":500,"message":"菜单查询失败","errcode":40066,"errmsg":"invalid url hint: [Fdd38a0167vr47!]"}
    }

    public function getFile($data, $config) {
        $access_token = $config['access_token'];
        $result = $this->curlGet(
                self::API_URL . "/cgi-bin/media/get?access_token={$access_token}&media_id={$data}"
        );
        return $result;
    }

    /**
     * 删除菜单
     * @param array $config
     * @return array|string
     */
    public function menuDelete($config) {
        $access_token = $config['access_token'];
        $result = $this->curlGet(
                self::API_URL . "/cgi-bin/menu/delete?access_token=$access_token"
        );
        return $result;
        //正确返回 { ["errcode"]=> int(0) ["errmsg"]=> string(2) "ok" }
        //错误返回 { ["errcode"]=> int(40066) ["errmsg"]=> string(35) "invalid url hint: [904KcA0014vr63!]" }
    }

    /**
     * 被动回复 文本
     * @param $xml
     * @return string
     */
    public function replyText($xml) {
        $result = '<xml>
                    <ToUserName>< ![CDATA[' . $xml->FromUserName . '] ]></ToUserName>
                    <FromUserName>< ![CDATA[' . $xml->ToUserName . '] ]></FromUserName>
                    <CreateTime>' . time() . '</CreateTime>
                    <MsgType>< ![CDATA[text] ]></MsgType>
                    <Content>< ![CDATA[' . $xml->content . '] ]></Content>
                </xml>';
        return $result;
    }

    /**
     * 被动回复 图片
     * @param $xml
     * @return string
     */
    public function replyImage($xml) {
        $result = '<xml>
                    <ToUserName>< ![CDATA[' . $xml->FromUserName . '] ]></ToUserName>
                    <FromUserName>< ![CDATA[' . $xml->ToUserName . '] ]></FromUserName>
                    <CreateTime>' . time() . '</CreateTime>
                    <MsgType>< ![CDATA[image] ]></MsgType>
                    <MediaId>< ![CDATA[' . $xml->MediaId . '] ]></MediaId>
                </xml>';
        return $result;
    }

    /**
     * 获取公众号数据库配置
     * @param $id
     * @return array|bool
     */
    public function getWeChatConfig($key) {
        if (isset($key) && trim($key) != '') {
            $res = $this->getSystemConfig("wechat", $key);

            /*
             * 判断 token 是否过期
             * 判断条件 当更新时间存在时，判断更新时间加上token有效期是否小于当前时间
             * 当更新时间不存在时，判断新增时间加上token有效期是否小于当前时间
             * 都不存在则表示数据异常，直接返回false
             */
            $update_time = $res['update_time'];

            $flag = true;
            if ($update_time != 0) {
                $time = $update_time + (int) $res['expires_in'];
                if ($time < time()) {
                    $flag = false;
                }
            } else {
                $time = $update_time + (int) $res['expires_in'];

                if ($time < time()) {
                    $flag = false;
                }
            }

            //已过期重新获取token

            if (!$flag) {

                $tokenRes = self::getToken($res, $key);
                $res['access_token'] = $tokenRes['access_token'];
                $ticket = self::getJsapi_Ticket($res, $key);
                if ($tokenRes['status'] != 200) {
                    return false;
                }

                //返回当前创建 token
                $arr = [
                    'app_id' => $res['app_id'],
                    'app_secret' => $res['app_secret'],
                    'access_token' => $tokenRes['access_token'],
                    'ticket' => $ticket['ticket'],
                    'expires_in' => $tokenRes['expires_in'],
                ];
            } else {

                //获取需要返回的数组
                $arr = [
                    'app_id' => $res['app_id'],
                    'app_secret' => $res['app_secret'],
                    'access_token' => $res['access_token'],
                    'ticket' => $res['ticket'],
                    'expires_in' => $res['expires_in'],
                ];
            }

            return $arr;
        } else {
            return false;
        }
    }

    public function getSystemConfig($type, $key) {
        $systemConfigModel = new SystemConfigModel();
        $params['`key`'] = $key;
        $systemConfig = $systemConfigModel->find($params);
        if ($systemConfig['status'] == 200) {
            $config = json_decode($systemConfig['data']['value'], true);
            if (isset($config[$type])) {
                return $config[$type];
            } else {
                return result(500, "未找到系统配置信息");
            }
        } else {
            return result(500, "未找到系统配置信息");
        }
    }

    /**
     * curl get 请求
     * @param string $url 请求地址
     * @return array
     */
    public function curlGet($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!curl_exec($ch)) {
            $data = '';
        } else {
            $data = curl_multi_getcontent($ch);
        }
        curl_close($ch);
        return $data;
    }

    /**
     * curl post 请求
     * @param string $url 请求地址
     * @param string $data 请求参数
     * @return array
     */
    public function curlPost($url, $data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        if (!curl_exec($ch)) {
            $data = '';
        } else {
            $data = curl_multi_getcontent($ch);
        }
        curl_close($ch);
        return $data;
    }

}
