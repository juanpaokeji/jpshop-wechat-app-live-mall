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
class UploadsModel {

    private $file_size; //上传源文件大小
    private $file_tem; //上传文件临时储存名
    private $file_name; //上传文件名
    private $file_type; //上传文件类型
    private $file_max_size = 60000000; //允许文件上传最大
    private $file_folder = ""; //文件上传路径
    private $over_write = false; //是否覆盖同名文件
    private $new_name = "";
//允许上传图片的类型
    private $allow_type = array('image/jpg', 'image/jpeg', 'image/png', 'image/pjpeg', 'image/gif', 'image/bmp', 'image/x-png', 'audio/x-aac');

//构造类，file:上传文件域
    function __construct($file, $path = "") {
        $this->file_name = $_FILES[$file]['name']; //客户端原文件名
        $this->file_type = $_FILES[$file]['type']; //文件类型
        $this->file_tem = $_FILES[$file]['tmp_name']; //储存的临时文件名，一般是系统默认
        $this->file_size = $_FILES[$file]['size']; //文件大小
        if ($path) {
            $this->file_folder = $path;
        }
    }

//如果文件夹不存在则创建文件夹 
    function creatFolder($f_path) {
        if (!file_exists($f_path)) {
            mkdir($f_path, 0777);
        }
    }

//判断文件是不是超过上传限制
    function is_big() {

        if ($this->file_size > $this->file_max_size) {
            return false;
        }
        return true;
    }

//检查文件类型
    function check_type() {
        if (!in_array($this->file_type, $this->allow_type)) {
            return "上传文件类型不正确";
            exit;
        }
    }

//检查文件是否存在
    function check_file_name() {
        if (!file_exists($this->file_tem)) {
            echo "上传文件不存在";
            exit;
        }
    }

//检查是否有同名文件，是否覆盖
    function check_same_file($filename) {
        if (file_exists($filename) && $this->over_write != true) {
            echo "同名文件已存在！";
            exit;
        }
    }

//移动文件
    function move_file($filename, $destination) {
        if (!move_uploaded_file($filename, $destination)) {
            echo "移动文件出错";
            exit;
        }
    }

//检查文件是否是通过 HTTP POST 上传的
    function is_upload_file() {
        if (!is_uploaded_file($this->file_tem)) {
            echo "文件不存在";
            exit;
        }
    }

//获得文件后缀名
    function getext() {

        $ext = $this->file_name;
        $extstr = explode('.', $ext);
        $count = count($extstr) - 1;
        return $extstr[$count];
    }

//新建文件名
    function set_name() {
        return time() . uniqid() . "." . $this->getext();
    }

//建立以年月日为文件夹名
    function creat_mulu() {
        //建立以年月日为文件夹名
        if ($this->file_folder) {
            $this->creatFolder($this->file_folder);
            creatFolder($this->file_folder . "/" . date('Y'));
            creatFolder($this->file_folder . "/" . date('Y') . "/" . date('m'));
            creatFolder($this->file_folder . "/" . date('Y') . "/" . date('m') . "/" . date('d'));
            return $this->file_folder . "/" . date('Y') . "/" . date('m') . "/" . date('d');
        } else {
            $this->creatFolder("./uploads/" . date('Y'));
            $this->creatFolder("./uploads/" . date('Y') . "/" . date('m'));
            $this->creatFolder("./uploads/" . date('Y') . "/" . date('m') . "/" . date('d'));
            return "./uploads/" . date('Y') . "/" . date('m') . "/" . date('d');
        }
    }

//生成的文件名
    function files_name() {
        $name = $this->set_name();
        $folder = $this->creat_mulu();
        $this->new_name = $folder . "/" . $name;
        return $this->new_name;
    }

//上传文件
    function upload() {
        $bool = $this->is_big();
        if ($bool) {
            $f_name = $this->files_name();
            move_uploaded_file($this->file_tem, $f_name);
            return $f_name;
        } else {
            echo "文件太大无法上传";
            return false;
        }
    }

//删除文件
    function delFile() {
        unlink($this->new_name);
        return $this->file_folder . "/tpwg.png";
    }

//生成缩略图
//最大宽：120，高：120
    function create_simg($img_w, $img_h) {
        $name = $this->set_name();
        $folder = $this->creat_mulu();
        $new_name = "../../" . $folder . "/s_" . $name;
        $imgsize = getimagesize($this->files_name());

        switch ($imgsize[2]) {
            case 1:
                if (!function_exists("imagecreatefromgif")) {
                    echo "你的GD库不能使用GIF格式的图片，请使用Jpeg或PNG格式！返回";
                    exit();
                }
                $im = imagecreatefromgif($this->files_name());
                break;
            case 2:
                if (!function_exists("imagecreatefromjpeg")) {
                    echo "你的GD库不能使用jpeg格式的图片，请使用其它格式的图片！返回";
                    exit();
                }
                $im = imagecreatefromjpeg($this->files_name());
                break;
            case 3:
                $im = imagecreatefrompng($this->files_name());
                break;
            case 4:
                $im = imagecreatefromwbmp($this->files_name());
                break;
            default:
                die("is not filetype right");
                exit;
        }

        $src_w = imagesx($im); //获得图像宽度
        $src_h = imagesy($im); //获得图像高度
        $new_wh = ($img_w / $img_h); //新图像宽与高的比值
        $src_wh = ($src_w / $src_h); //原图像宽与高的比值
        if ($new_wh <= $src_wh) {
            $f_w = $img_w;
            $f_h = $f_w * ($src_h / $src_w);
        } else {
            $f_h = $img_h;
            $f_w = $f_h * ($src_w / $src_h);
        }
        if ($src_w > $img_w || $src_h > $img_h) {
            if (function_exists("imagecreatetruecolor")) {//检查函数是否已定义
                $new_img = imagecreatetruecolor($f_w, $f_h);
                if ($new_img) {
                    imagecopyresampled($new_img, $im, 0, 0, 0, 0, $f_w, $f_h, $src_w, $src_h); //重采样拷贝部分图像并调整大小
                } else {
                    $new_img = imagecreate($f_w, $f_h);
                    imagecopyresized($new_img, $im, 0, 0, 0, 0, $f_w, $f_h, $src_w, $src_h);
                }
            } else {
                $$new_img = imagecreate($f_w, $f_h);
                imagecopyresized($new_img, $im, 0, 0, 0, 0, $f_w, $f_h, $src_w, $src_h);
            }
            if (function_exists('imagejpeg')) {
                imagejpeg($new_img, $new_name);
            } else {
                imagepng($new_img, $new_name);
            }
            imagedestroy($new_img);
        }
//imagedestroy($new_img);
        return $new_name;
    }

}
