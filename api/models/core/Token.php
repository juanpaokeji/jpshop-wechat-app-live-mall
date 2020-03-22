<?php
/**
 * This file is part of JWT.
 *
 */

namespace app\models\core;

use \Exception;

class Token{

    protected $key;

    public function __construct($key){
        $this->key = $key.$_SERVER['HTTP_HOST'];
    }

    /**
     * @param string $jwt
     * @throws Exception if the model cannot be found
     * @return string
     */
    public function decode($jwt)
    {
        $tokens = explode('.', $jwt);
        $key    = md5($this->key);

        if (count($tokens) != 3)
            return false;

        list($header64, $payload64, $sign) = $tokens;

        $header = json_decode(self::base64Decode($header64), JSON_OBJECT_AS_ARRAY);
        if (empty($header['alg']))
            return false;

        if (self::signature($header64 . '.' . $payload64, $key, $header['alg']) !== $sign)
            return false;

        $payload = json_decode(self::base64Decode($payload64), JSON_OBJECT_AS_ARRAY);

        $time = $_SERVER['REQUEST_TIME'];
        if (isset($payload['iat']) && $payload['iat'] > $time)
            return false;

        if (isset($payload['exp']) && $payload['exp'] < $time)
            return false;

        return $payload;
    }

    /**
     * @param array $payload
     * @param string $alg
     * @throws Exception if the model cannot be found
     * @return string
     */
    public function encode(array $payload, $alg = 'SHA256')
    {
        $key = md5($this->key);
        $jwt = self::base64Encode(json_encode(['typ' => 'JWT', 'alg' => $alg])) . '.' . self::base64Encode(json_encode($payload));
        return $jwt . '.' . self::signature($jwt, $key, $alg);
    }

    public function signature($input, $key, $alg)
    {
        return hash_hmac($alg, $input, $key);
    }

    /**
     * 用于url的base64encode
     * '+' => '*', '/' => '-', '=' => '_'
     * @param string $string 需要编码的数据
     * @throws Exception if the model cannot be found
     * @return string 编码后的base64串，失败返回false
     */
    private function base64Encode($string) {
        static $replace = Array('+' => '*', '/' => '-', '=' => '_');
        $base64 = base64_encode($string);
        if ($base64 === false) {
            throw new Exception('base64_encode error');
        }
        return str_replace(array_keys($replace), array_values($replace), $base64);
    }

    /**
     * 用于url的base64decode
     * '+' => '*', '/' => '-', '=' => '_'
     * @param string $base64 需要解码的base64串
     * @throws Exception if the model cannot be found
     * @return string 解码后的数据，失败返回false
     */
    private function base64Decode($base64) {
        static $replace = Array('+' => '*', '/' => '-', '=' => '_');
        $string = str_replace(array_values($replace), array_keys($replace), $base64);
        $result = base64_decode($string);
        if ($result == false) {
            throw new Exception('base64_decode error');
        }
        return $result;
    }
}
