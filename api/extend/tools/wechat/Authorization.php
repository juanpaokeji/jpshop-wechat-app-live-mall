<?php
namespace tools\wechat;
use yii\redis\Cache;
class Authorization{

    private $post_url = "https://api.weixin.qq.com/cgi-bin/component/";
    private $config;

    public function __construct()
    {
        $this->config = [
            'appid'=>'wx8df3a6f4a4f9ec54',//自己定义
            'AppSecret'=>'7188287cd30aa902d5933654fed60559',//自己定义
        ];
    }


    /**
     * 获取微信第三方component_access_token
     */
    public function get_component_access_token()
    {
        $Cache = new Cache();
        $component_access_token =$Cache->get("ComponentAccessToken");
        if (!$component_access_token) {
            $ComponentVerifyTicket = $Cache->get('ComponentVerifyTicket');
            $postData = [
                'component_appid' => $this->config['appid'],
                'component_appsecret' => $this->config['AppSecret'],
                'component_verify_ticket' => $ComponentVerifyTicket['component_verify_ticket']
            ];
            $component_access_token = curlPost($this->post_url . 'api_component_token', json_encode($postData));
            $component_access_token = json_decode($component_access_token, true);
            $Cache->set('ComponentAccessToken', $component_access_token,$component_access_token['expires_in'] - 600);
        }
        return $component_access_token;
    }

    /**
     * 获取预授权码
     * @return bool|string
     */
    public function get_pre_auth_code()
    {
        $ComponentAccessToken = $this->get_component_access_token();
        $postData = [
            'component_appid' => $this->config['appid'],
        ];
        $pre_auth_code =curlPost($this->post_url . 'api_create_preauthcode?component_access_token=' . $ComponentAccessToken['component_access_token'], json_encode($postData));
        $pre_auth_code = json_decode($pre_auth_code, true);
        return $pre_auth_code;
    }

    /**
     * 获取预授权码
     * @return bool|string
     */
    public function api_query_auth($pre_auth_code)
    {
        $Cache = new Cache();
        $ComponentAccessToken = $this->get_component_access_token();
        $postData = [
            'component_appid' => $this->config['appid'],
            'authorization_code' => $pre_auth_code
        ];
        $query_auth = curlPost($this->post_url . 'api_query_auth?component_access_token=' . $ComponentAccessToken['component_access_token'], json_encode($postData));
        $query_auth = json_decode($query_auth, true);

        $cacheData = [
            'authorizer_access_token' => $query_auth['authorization_info']['authorizer_access_token'],
            'authorizer_refresh_token' => $query_auth['authorization_info']['authorizer_refresh_token'],
        ];
        $Cache->set("query_auth" . $query_auth['authorization_info']['authorizer_appid'], $cacheData,$query_auth['authorization_info']['expires_in'] - 300);
        return $query_auth;

    }

    /**
     * 获取第三方公众号令牌
     * @param $authorizer_appid 第三方公众号appid
     */
    public function get_authorizer_access_token($authorizer_appid)
    {
        $Cache = new Cache();
        $query_auth = $Cache->get("query_auth" . $authorizer_appid);
        //如果不存在重新获取
        if (!$query_auth) {
            $info = $this->get_authorizer_info($authorizer_appid);
            $ComponentAccessToken = $this->get_component_access_token();
            $postData = [
                'component_appid' => $this->config['appid'],
                'authorizer_appid' => $authorizer_appid,
                'authorizer_refresh_token' => !empty($info['authorization_info']['authorizer_refresh_token']) ? $info['authorization_info']['authorizer_refresh_token'] : ""
            ];
            $query_auth = curlPost($this->post_url . 'api_authorizer_token?component_access_token=' . $ComponentAccessToken['component_access_token'], json_encode($postData));
            $query_auth = json_decode($query_auth, true);
            if (!empty($query_auth['authorizer_access_token'])) {
                $query_auth['create_time'] = time() + $query_auth['expires_in'] - 180;
                $Cache->set("query_auth" . $authorizer_appid,$query_auth, $query_auth['expires_in']);
            }
        } else {
            $now_time = time();
            if ($query_auth['create_time'] < $now_time) {
                $ComponentAccessToken = $this->get_component_access_token();
                $postData = [
                    'component_appid' => 'appid',
                    'authorizer_appid' => $authorizer_appid,
                    'authorizer_refresh_token' => $query_auth['authorizer_refresh_token']
                ];
                $query_auth = curlPost($this->post_url . 'api_authorizer_token?component_access_token=' . $ComponentAccessToken['component_access_token'], json_encode($postData));
                $query_auth = json_decode($query_auth, true);
                if (!empty($query_auth['authorizer_access_token'])) {
                    $query_auth['create_time'] = $now_time + $query_auth['expires_in'] - 180;
                    $Cache->set("query_auth" . $authorizer_appid,$query_auth);
                }
            }
        }
        return $query_auth;
    }

    /**
     * 发送客服消息
     * @param $data
     * @return bool|mixed
     */
    public function send_custom_message($accessToken, $data)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=' . $accessToken;
        return curlPost($url, json_encode($data));
    }

    /**
     * 获取授权方公众号信息
     */
    public function get_authorizer_info($authorizer_appid)
    {
        $ComponentAccessToken = $this->get_component_access_token();
        $postData = [
            'component_appid' => $this->config['appid'],
            'authorizer_appid' => $authorizer_appid
        ];
        $pre_auth_code = curlPost($this->post_url . 'api_get_authorizer_info?component_access_token=' . $ComponentAccessToken['component_access_token'], json_encode($postData));
        return json_decode($pre_auth_code, true);
    }


    /**
     * 获取对方用户信息
     * @param $authorizer_appid
     * @param string $openid
     */
    public function get_authorizer_user_info($authorizer_appid, $openid)
    {
        $authorizer_token = $this->get_authorizer_access_token($authorizer_appid);
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=" . $authorizer_token['authorizer_access_token'] . "&openid=" . $openid . "&lang=zh_CN";
        $result =curlGet($url);
        return $result;
    }



    /**
     * {@inheritdoc}
     */
    public function actions(){
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'maxLength'=>4,
                'minLength'=>4,
            ],
        ];
    }
}
