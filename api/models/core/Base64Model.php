<?php

/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace app\models\core;

//引入各表实体
use app\models\admin\app\AppAccessModel;
use yii;
use yii\db\Exception;
use yii\web\Response;

/**
 * 通用文件上传操作 model
 *
 * @version   2018年04月05日
 * @author    YangJing <120912212@qq.com>
 * @copyright Copyright 2010-2016 Swoft software
 * @license   PHP Version 7.x {@link http://www.php.net/license/3_0.txt}
 *
 * @Bean()
 */
class Base64Model {

    private $new_name = "";
    private $file_folder = ""; //文件上传路径

//
//如果文件夹不存在则创建文件夹 

    function creatFolder($f_path) {
        if (!file_exists($f_path)) {
            mkdir($f_path, 0777);
        }
    }

    function creat_mulu($path) {
        $list = explode("/", $path);
        $str = "";
        for ($i = 0; $i < count($list); $i++) {
            $str .= $list[$i];
            if (!file_exists($str)) {
                mkdir($str, 0777);
            }
            $str .= "/";
        }
        return $path . "/";
    }

    public function base64_image_content($str, $path) {
        //匹配出图片的格式

        $base64_image_content = $str;
        $path = $this->creat_mulu($path);


        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)) {
            $type = $result[2];
            $new_file = $path;
            if (!file_exists($new_file)) {
                //检查是否有该文件夹，如果没有就创建，并给予最高权限
                mkdir($new_file, 0700);
            }
            $new_file = $new_file . time() . uniqid() . ".{$type}";
            if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))) {
                $this->new_name = $new_file;
                $this->file_folder = $path;
                //按设定尺寸压缩图片
                $appModel = new AppAccessModel();
                $appInfo = $appModel->find([]); //单应用商户，system_app_access表只有一条数据
                if($appInfo['status'] == 200 && isset($appInfo['data']['thum_is_open']) && $appInfo['data']['thum_is_open'] == 1){
                    $imgModel = new ImageModel($new_file,$appInfo['data']['thum_width']); //传入图片地址、指定宽度实例化model
                    $imgModel->compressImg($new_file);//保存新图删除旧图
                    return $new_file;
                }else{
                    return $new_file;
                }
            } else {
                return false;
            }
        }else if (preg_match('/^(data:\s*image\/x\-icon;base64,)/', $base64_image_content, $result)) {
            $path  =  $this->creat_mulu('./uploads/ico/'.time());
            $new_file =$path.'favicon.ico';
            if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))) {
                $this->new_name = $new_file;
                $this->file_folder = $path;
                return  $new_file;
            } else {
                return  false;
            }
        }  else {
            return false;
        }
    }

    public function base64_file_content($str, $path) {
        $base64_image_content = $str;
        $path = $this->creat_mulu($path);
        if (preg_match('/^(data..base64,)/', $base64_image_content, $result)) {
            $new_file = "./" . $path;
            if (!file_exists($new_file)) {
                //检查是否有该文件夹，如果没有就创建，并给予最高权限
                mkdir($new_file, 0700);
            }
            $new_file = $new_file . time() . rand(1000, 9999) . ".pem";
            if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))) {
                return $new_file;
            } else {
                return false;
            }
        } else if (preg_match('/^(data:\s*application\/(\w+)-(\w+);base64,)/', $base64_image_content, $result)) {

            $new_file = "./" . $path;
            if (!file_exists($new_file)) {
                //检查是否有该文件夹，如果没有就创建，并给予最高权限
                mkdir($new_file, 0700);
            }
            $new_file = $new_file . time() . rand(1000, 9999) . ".pem";
            if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))) {
                return $new_file;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

//删除文件
    function delFile() {
        unlink($this->new_name);
        return $this->file_folder . "/tpwg.png";
    }

}
