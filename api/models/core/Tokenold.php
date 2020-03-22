<?php
/**
 * This file is part of JWT.
 *
 */

namespace app\models\core;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\ValidationData;
use \Exception;

class Tokenold
{

    /**
     * 通用表格操作 model
     *
     * @version   2018年03月19日
     * @author    JYS <272074691@qq.com>
     * @copyright Copyright 2010-2016 Swoft software
     * @license   PHP Version 7.x {@link http://www.php.net/license/3_0.txt}
     *
     */

    /**
     * 创建Token
     * 需要保存的用户身份标识
     * @param $id
     * @return String
     **/
    public static function createToken($id = null)
    {
        $signer = new Sha256();
        $token = (new Builder())->setIssuer('from')
            ->setAudience('jys')
            ->setId('sxs-4f1g23a12aa', true) //自定义标识
            ->setIssuedAt(time()) //当前时间
            ->setExpiration(time() + (3600*24*30)) //token有效期时长
            ->set('id', $id)
            ->sign($signer, '你的加盐字符串')
            ->getToken();
        //这里可以做一些其它的操作，例如把Token放入到Redis内存里面缓存起来。
        /**
         * ......
         * ......
         **/
        return (String) $token;
    }

    /**
     * 检测Token是否过期与篡改
     * @param token
     * @return string
     **/
    public static function validateToken($token = null)
    {
        $token = (new Parser())->parse((String) $token);
        $signer = new Sha256();
        if (!$token->verify($signer, '你的加盐字符串')) {
            return false; //签名不正确
        }

        $validationData = new ValidationData();
        $validationData->setIssuer('from');
        $validationData->setAudience('jys');
        $validationData->setId('sxs-4f1g23a12aa');//自定义标识

        //已过期则返回NULL
        if ($token->validate($validationData)){
//            return $token;//返回 token 所有信息
            return $token->getClaim('id');//单独返回 id
        }
    }
}