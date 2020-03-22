<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\base\InlineAction;
use yii\helpers\Url;
use app\models\core\TableModel;
use yii\db\Exception;
use app\models\core\Token;
use app\models\system\SystemWxConfigModel;
use app\models\core\Base64Model;
use app\models\core\CosModel;
use app\models\core\UploadsModel;
use app\models\merchant\app\AppAccessModel;
use EasyWeChat\Kernel\Http\StreamResponse;
use EasyWeChat\Factory;
use app\models\core\ImageModel;

/**
 * Controller is the base class of web controllers.
 *
 * For more details and usage information on Controller, see the [guide article on controllers](guide:structure-controllers).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header('HTTP/1.1 202 Accepted');
    exit;
}

class Controller extends \yii\base\Controller {

    /**
     * @var bool whether to enable CSRF validation for the actions in this controller.
     * CSRF validation is enabled only when both this property and [[\yii\web\Request::enableCsrfValidation]] are true.
     */
    public $enableCsrfValidation = true;

    /**
     * @var array the parameters bound to the current action.
     */
    public $actionParams = [];

    /**
     * Renders a view in response to an AJAX request.
     *
     * This method is similar to [[renderPartial()]] except that it will inject into
     * the rendering result with JS/CSS scripts and files which are registered with the view.
     * For this reason, you should use this method instead of [[renderPartial()]] to render
     * a view to respond to an AJAX request.
     *
     * @param string $view the view name. Please refer to [[render()]] on how to specify a view name.
     * @param array $params the parameters (name-value pairs) that should be made available in the view.
     * @return string the rendering result.
     */
    public function renderAjax($view, $params = []) {
        return $this->getView()->renderAjax($view, $params, $this);
    }

    /**
     * Send data formatted as JSON.
     *
     * This method is a shortcut for sending data formatted as JSON. It will return
     * the [[Application::getResponse()|response]] application component after configuring
     * the [[Response::$format|format]] and setting the [[Response::$data|data]] that should
     * be formatted. A common usage will be:
     *
     * ```php
     * return $this->asJson($data);
     * ```
     *
     * @param mixed $data the data that should be formatted.
     * @return Response a response that is configured to send `$data` formatted as JSON.
     * @since 2.0.11
     * @see Response::$format
     * @see Response::FORMAT_JSON
     * @see JsonResponseFormatter
     */
    public function asJson($data) {
        $response = Yii::$app->getResponse();
        $response->format = Response::FORMAT_JSON;
        $response->data = $data;
        return $response;
    }

    /**
     * Send data formatted as XML.
     *
     * This method is a shortcut for sending data formatted as XML. It will return
     * the [[Application::getResponse()|response]] application component after configuring
     * the [[Response::$format|format]] and setting the [[Response::$data|data]] that should
     * be formatted. A common usage will be:
     *
     * ```php
     * return $this->asXml($data);
     * ```
     *
     * @param mixed $data the data that should be formatted.
     * @return Response a response that is configured to send `$data` formatted as XML.
     * @since 2.0.11
     * @see Response::$format
     * @see Response::FORMAT_XML
     * @see XmlResponseFormatter
     */
    public function asXml($data) {
        $response = Yii::$app->getResponse();
        $response->format = Response::FORMAT_XML;
        $response->data = $data;
        return $response;
    }

    /**
     * Binds the parameters to the action.
     * This method is invoked by [[\yii\base\Action]] when it begins to run with the given parameters.
     * This method will check the parameter names that the action requires and return
     * the provided parameters according to the requirement. If there is any missing parameter,
     * an exception will be thrown.
     * @param \yii\base\Action $action the action to be bound with parameters
     * @param array $params the parameters to be bound to the action
     * @return array the valid parameters that the action can run with.
     * @throws BadRequestHttpException if there are missing or invalid parameters.
     */
    public function bindActionParams($action, $params) {
        if ($action instanceof InlineAction) {
            $method = new \ReflectionMethod($this, $action->actionMethod);
        } else {
            $method = new \ReflectionMethod($action, 'run');
        }

        $args = [];
        $missing = [];
        $actionParams = [];
        foreach ($method->getParameters() as $param) {
            $name = $param->getName();
            if (array_key_exists($name, $params)) {
                if ($param->isArray()) {
                    $args[] = $actionParams[$name] = (array) $params[$name];
                } elseif (!is_array($params[$name])) {
                    $args[] = $actionParams[$name] = $params[$name];
                } else {
                    throw new BadRequestHttpException(Yii::t('yii', 'Invalid data received for parameter "{param}".', [
                        'param' => $name,
                    ]));
                }
                unset($params[$name]);
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $actionParams[$name] = $param->getDefaultValue();
            } else {
                $missing[] = $name;
            }
        }

        if (!empty($missing)) {
            throw new BadRequestHttpException(Yii::t('yii', 'Missing required parameters: {params}', [
                'params' => implode(', ', $missing),
            ]));
        }

        $this->actionParams = $actionParams;

        return $args;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action) {
        $res = yii::$app->controller->id;
        $str1 ='supplier';
        $str2 ='storehouse';
        $str3 = 'distribution';
        if(strpos($res,$str1)!== false){
            //检测是否购买门店插件
           $this->sl();

        }else if(strpos($res,$str2)!== false){
            //检测是否购买仓库插件
            $this->sh();
        }else if(strpos($res,$str3)!== false){
            //检测是否购买分销插件
            $this->db();
        }else{
            if (parent::beforeAction($action)) {

                if ($this->enableCsrfValidation && Yii::$app->getErrorHandler()->exception === null && !Yii::$app->getRequest()->validateCsrfToken()) {
                    throw new BadRequestHttpException(Yii::t('yii', 'Unable to verify your data submission.'));
                }

                return true;
            }
        }
        return false;
    }

    /**
     * Redirects the browser to the specified URL.
     * This method is a shortcut to [[Response::redirect()]].
     *
     * You can use it in an action by returning the [[Response]] directly:
     *
     * ```php
     * // stop executing this action and redirect to login page
     * return $this->redirect(['login']);
     * ```
     *
     * @param string|array $url the URL to be redirected to. This can be in one of the following formats:
     *
     * - a string representing a URL (e.g. "http://example.com")
     * - a string representing a URL alias (e.g. "@example.com")
     * - an array in the format of `[$route, ...name-value pairs...]` (e.g. `['site/index', 'ref' => 1]`)
     *   [[Url::to()]] will be used to convert the array into a URL.
     *
     * Any relative URL that starts with a single forward slash "/" will be converted
     * into an absolute one by prepending it with the host info of the current request.
     *
     * @param int $statusCode the HTTP status code. Defaults to 302.
     * See <https://tools.ietf.org/html/rfc2616#section-10>
     * for details about HTTP status code
     * @return Response the current response object
     */
    public function redirect($url, $statusCode = 302) {
        return Yii::$app->getResponse()->redirect(Url::to($url), $statusCode);
    }

    /**
     * Redirects the browser to the home page.
     *
     * You can use this method in an action by returning the [[Response]] directly:
     *
     * ```php
     * // stop executing this action and redirect to home page
     * return $this->goHome();
     * ```
     *
     * @return Response the current response object
     */
    public function goHome() {
        return Yii::$app->getResponse()->redirect(Yii::$app->getHomeUrl());
    }

    /**
     * Redirects the browser to the last visited page.
     *
     * You can use this method in an action by returning the [[Response]] directly:
     *
     * ```php
     * // stop executing this action and redirect to last visited page
     * return $this->goBack();
     * ```
     *
     * For this function to work you have to [[User::setReturnUrl()|set the return URL]] in appropriate places before.
     *
     * @param string|array $defaultUrl the default return URL in case it was not set previously.
     * If this is null and the return URL was not set previously, [[Application::homeUrl]] will be redirected to.
     * Please refer to [[User::setReturnUrl()]] on accepted format of the URL.
     * @return Response the current response object
     * @see User::getReturnUrl()
     */
    public function goBack($defaultUrl = null) {
        return Yii::$app->getResponse()->redirect(Yii::$app->getUser()->getReturnUrl($defaultUrl));
    }

    /**
     * Refreshes the current page.
     * This method is a shortcut to [[Response::refresh()]].
     *
     * You can use it in an action by returning the [[Response]] directly:
     *
     * ```php
     * // stop executing this action and refresh the current page
     * return $this->refresh();
     * ```
     *
     * @param string $anchor the anchor that should be appended to the redirection URL.
     * Defaults to empty. Make sure the anchor starts with '#' if you want to specify it.
     * @return Response the response object itself
     */
    public function refresh($anchor = '') {
        return Yii::$app->getResponse()->redirect(Yii::$app->getRequest()->getUrl() . $anchor);
    }

    public function get_randomstr($lenth = 12) {
        return $this->get_random($lenth, 'abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ0123456789');
    }

    /**
     * 产生随机字符串
     *
     * @param    int        $length  输出长度
     * @param    string     $chars   可选的 ，默认为 0123456789
     * @return   string     字符串
     */
    public function get_random($length, $chars) {
        $hash = '';
        $max = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $hash .= $chars[mt_rand(0, $max)];
        }
        return $hash;
    }

    /**
     * 验证必填
     *
     * @param    array        $must  必填项
     * @param    array     $data   表单提交项
     * @return   array     $array 状态，消息
     */
    public function checkInput($must, $data) {
        $array = false;
        if (empty($must) || !is_array($must)) {
            return result(500, '必填参数为空或格式错误');
        }
        if (empty($data) || !is_array($data)) {
            return result(500, '验证参数为空或格式错误');
        }

        for ($i = 0; $i < count($must); $i++) {
            if (isset($data[$must[$i]])) {
                if ($data[$must[$i]] == "") {
                    return result(500, '缺少参数 ' . $must[$i]);
                }
            } else {
                return result(500, '缺少参数 ' . $must[$i]);
            }
        }
        return $array;
    }

    /**
     * 文件转base64
     * @param string $image_file 图片相对路径
     * @return string
     */
    function imageForBase64($image_file) {
        $image_info = getimagesize($image_file);
        $image_data = fread(fopen(Yii::getAlias('@webroot/') . $image_file, 'r'), filesize($image_file));
        $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
        return $base64_image;
    }

//    public function ShopFilter($token) {
//        if (empty($token)) {
//            return result(1001, '缺少token请求');
//        }
//        $tokenClass = new Token(yii::$app->params['JWT_KEY_SHOP']);
//        $check = $tokenClass->decode($token);
//        if (!$check) {
//            return result(1001, '登录状态异常,请重新登录');
//        } else {
////            $res = $this->checkAuth($check['uid']);//判断功能权限，暂无
//            if (false) {
//                return result(200, '您没有该功能的访问权限');
//            }
//            yii::$app->session['key'] = $check['key'];
//            yii::$app->session['merchant_id'] = $check['merchant_id'];
//            yii::$app->session['user_id'] = $check['user_id'];
//            return true;
//        }
//    }

    /**
     *  $config 配置文件
     *  $id   微信媒体id
     *  $type  文件类型 1= 图片 2= 语言
     */
    public function wxUpload($config, $media, $type = 1, $count = 5) {
        //数组多个媒体id  array, 或者 一个媒体id  string
        if (is_array($media)) {
            try {
                if (count($media) > $count) {
                    return false;
                }
                //返回图片连接地址
                $str = "";
                for ($i = 0; $i < count($media); $i++) {

                    $url = $this->media($config, $media[$i]);
                    if ($url == false) {
                        return false;
                    }
                    if ($i == 0) {
                        $str = $url;
                    } else {
                        $str = $str . "," . $url;
                    }
                }

                return $str;
            } catch (\Exception $e) {
                return false;
            }
        } else {

            if ($type == 2) {
                $url = $this->media($config, $media, $type);
                return $url;
            } else {
                $url = $this->media($config, $media);
                return $url;
            }
        }
    }

    /**
     * 小程序 上传
     * type=1 1=图片 图片为base64
     * $base64Model = new Base64Model();  file 为数组，上传已base64编码格式的数组
     *
     * type=2 2=语音
     * UploadsModel($file, $path) file为name  如<input type="file" name="img">
     */
    public function xcxUploads($file, $type = 1) {
        $path = "./uploads/forum/media/" . date('Y') . "/" . date('m') . "/" . date('d');
        if ($type == 2) {
            $upload = new UploadsModel($file, $path);
            $str = $upload->upload();
            if (!$str) {
                return "上传文件错误";
            }
            $new_str = dirname($str) . '/' . mt_rand(100000000000, 999999999999) . '.mp3';
            exec("ffmpeg -i " . $str . " " . $new_str, $output);
            unlink(Yii::getAlias('@webroot/') . $str);
            try {
                //将图片上传到cos
                $cos = new CosModel();
                $cosRes = $cos->putObject($new_str);
                if ($cosRes['status'] == '200') {
                    $url = $cosRes['data'];
                } else {
                    unlink(Yii::getAlias('@webroot/') . $new_str);
                    return json_encode($url, JSON_UNESCAPED_UNICODE);
                }
                return $url;
            } catch (\Exception $e) {
                return false;
            }
        } else {
            $base64Model = new Base64Model();
            if (count($file) > 5) {
                return result(500, "超过上传数量");
            }
            try {
                $str = "";
                for ($i = 0; $i < count($file); $i++) {
                    $str = $base64Model->base64_file_content($file[$i], $path);
                    if (!$str) {
                        return "上传文件错误";
                    }
                    //将图片上传到cos
                    $cos = new CosModel();
                    $cosRes = $cos->putObject($str);
                    if ($cosRes['status'] == '200') {
                        $url = $cosRes['data'];
                    } else {
                        unlink(Yii::getAlias('@webroot/') . $str);
                        return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
                    }
                    if ($i == 0) {
                        $str = $url;
                    } else {
                        $str = $str . "," . $url;
                    }
                }
                return $str;
            } catch (\Exception $e) {
                return false;
            }
        }
    }

    /**
     *  $config 配置文件
     * $id   微信媒体id
     *  $type  文件类型 1= 图片 2= 语言
     */
    public function media($config, $id, $type = 1) {
        try {
            $app = $this->getApp($config);
            $str = "";
            $stream = $app->media->get($id);

            if ($stream instanceof \EasyWeChat\Kernel\Http\StreamResponse) {
                // 以内容 md5 为文件名存到本地
                $str = $stream->save(yii::getAlias('@webroot/') . "/uploads/media/" . date('Y') . "/" . date('m') . "/" . date('d') . "/");
                $str = "./uploads/media/" . date('Y') . "/" . date('m') . "/" . date('d') . "/" . $str;
            } else {
                return false;
            }

            if ($type == 2) {
                $new_str = dirname($str) . '/' . mt_rand(100000000000, 999999999999) . '.mp3';
                exec("ffmpeg -i " . $str . " " . $new_str, $output);
                unlink(Yii::getAlias('@webroot/') . $str);
                $str = $new_str;
            }
            if ($type == 1) {
                $imgage = new ImageModel($str);
                $imgage->compressImg($str);
            }
            //上传到腾讯云cos
            $cos = new CosModel();
            $cosRes = $cos->putObject($str);
            if ($cosRes['status'] == '200') {
                $url = $cosRes['data'];
                unlink(yii::getAlias('@webroot/') . $str);
            } else {
                unlink(yii::getAlias('@webroot/') . $str);
                return json_encode($url, JSON_UNESCAPED_UNICODE);
            }
            return $url;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getSystemConfig($key, $type, $is_pay = 0) {

        $config = getConfig($key);

        if ($is_pay == 1) {
            $merchantUserModel = new \app\models\merchant\user\MerchantModel();
            $res = $merchantUserModel->find(['id' => yii::$app->session['merchant_id']]);
            if ($res['status'] == 200) {
                if ($res['data']['pay_switch'] == 0 && $config['wx_pay_type'] == 1) {
                    return false;
                }
            }
        }
        if ($config == false) {
            $systemConfigModel = new SystemWxConfigModel();
            $params['key'] = $key;
            $systemConfig = $systemConfigModel->find($params);
            if ($systemConfig['status'] == 200) {
                if ($systemConfig['data']['wx_pay_type'] == 1) {
                    $array['miniprogrampay'] = $systemConfig['data']['miniprogram_pay'];
                } else {
                    $array['miniprogrampay'] = $systemConfig['data']['saobei'];
                }
                $array['saobei'] = $systemConfig['data']['saobei'];
                $array['wechat'] = $systemConfig['data']['wechat'];
                $array['wxpay'] = $systemConfig['data']['wechat_pay'];
                $array['miniprogram'] = $systemConfig['data']['miniprogram'];
                $array['wx_pay_type'] = $systemConfig['data']['wx_pay_type'];
                setConfig($key, $array);
                $arr = $this->config($array, $type);
                return $arr;
            } else {
                return false;
            }
        } else {
            $arr = $this->config($config, $type);
            return $arr;
        }
    }

    public function config($array, $type) {
        if (!isset($array[$type])) {
            return false;
        }
        if ($array == false) {
            return false;
        } else {
            return json_decode($array[$type], true);
        }
    }

    //查询商户信息
    public function getMerchant($key) {
        $appAccessModel = new AppAccessModel();
        $data['`key`'] = $key;
        $appData = $appAccessModel->find($data);
        if ($appData['status'] != 200) {
            return false;
        } else {
            return $appData['data']['merchant_id'];
        }
    }

    public function getApp($config) {
        if (isset($config['type'])) {
            if ($config['type'] == 1) {
                //手动配置信息实现公众号业务
                $app = Factory::openPlatform($config);
                return $app;
            } else if ($config['type'] == 2) {
                //带公众号实现业务
                $con = [
                    'app_id' => 'wx8df3a6f4a4f9ec54',
                    'secret' => '7188287cd30aa902d5933654fed60559',
                    'token' => 'juanPao',
                    'aes_key' => '9ILejPm7rpu5kJykkY13oHMO80bYJkNbQfCvL3otaWA',
                ];
                $openPlatform = Factory::openPlatform($con);
                $app = $openPlatform->officialAccount($config['app_id'], $config['refresh_token']);
                return $app;
            } else {
                return false;
            }
        } else {
            //带小程序实现业务
            $con = [
                'app_id' => 'wx8df3a6f4a4f9ec54',
                'secret' => '7188287cd30aa902d5933654fed60559',
                'token' => 'juanPao',
                'aes_key' => '9ILejPm7rpu5kJykkY13oHMO80bYJkNbQfCvL3otaWA',
            ];
            $openPlatform = Factory::openPlatform($con);
            $app = $openPlatform->miniProgram($config['app_id'], $config['refresh_token']);
            return $app;
        }
    }

    private  function sl(){
        isset($_SESSION) or session_start();
        if(!isset($_SESSION['authcode']) || $_SESSION['authcode'] != 'cadef7d447'){
            $hosts = $_SERVER['HTTP_HOST'].'|'.$_SERVER['SERVER_NAME'];
            $ckret = xzphp_curl_get('http://shouquanjs.juanpao.com/check.php?a=index&appsign=6_200306174738401_e2440127_80a5e3e70aef0c7923c9714950d00629&h='.urlencode($hosts).'&t='.$_SERVER['REQUEST_TIME'].'&token='.md5($_SERVER['REQUEST_TIME'].'|'.$hosts.'|xzphp|cadef7d447'));

            if($ckret){
                $ckret = json_decode($ckret, true);
                if($ckret['status'] != 1){
                    exit($ckret['msg']);
                }else{
                    $_SESSION['authcode'] = 'cadef7d447';
                    unset($hosts,$ckret);
                }
            }else{
                exit('授权检测失败，请联系授权提供商。');
            }
        }
    }

    private  function sh(){
        isset($_SESSION) or session_start();
        if(!isset($_SESSION['authcode']) || $_SESSION['authcode'] != 'cadef7d447'){
            $hosts = $_SERVER['HTTP_HOST'].'|'.$_SERVER['SERVER_NAME'];
            $ckret = xzphp_curl_get('http://shouquanjs.juanpao.com/check.php?a=index&appsign=4_200228212212384_8000e442_a528f36127a021de6ebebc67c95d5c15&h='.urlencode($hosts).'&t='.$_SERVER['REQUEST_TIME'].'&token='.md5($_SERVER['REQUEST_TIME'].'|'.$hosts.'|xzphp|cadef7d447'));
            if($ckret){
                $ckret = json_decode($ckret, true);
                if($ckret['status'] != 1){
                    exit($ckret['msg']);
                }else{
                    $_SESSION['authcode'] = 'cadef7d447';
                    unset($hosts,$ckret);
                }
            }else{
                exit('授权检测失败，请联系授权提供商。');
            }
        }
    }

    private  function db(){
        isset($_SESSION) or session_start();
        if(!isset($_SESSION['authcode']) || $_SESSION['authcode'] != 'cadef7d447'){
            $hosts = $_SERVER['HTTP_HOST'].'|'.$_SERVER['SERVER_NAME'];
            $ckret = xzphp_curl_get('http://shouquanjs.juanpao.com/check.php?a=index&appsign=4_200228212212384_8000e442_a528f36127a021de6ebebc67c95d5c15&h='.urlencode($hosts).'&t='.$_SERVER['REQUEST_TIME'].'&token='.md5($_SERVER['REQUEST_TIME'].'|'.$hosts.'|xzphp|cadef7d447'));
            if($ckret){
                $ckret = json_decode($ckret, true);
                if($ckret['status'] != 1){
                    exit($ckret['msg']);
                }else{
                    $_SESSION['authcode'] = 'cadef7d447';
                    unset($hosts,$ckret);
                }
            }else{
                exit('授权检测失败，请联系授权提供商。');
            }
        }
    }

}
