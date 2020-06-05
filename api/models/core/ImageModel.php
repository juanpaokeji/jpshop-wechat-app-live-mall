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
class ImageModel {

    private $src;
    private $image;
    private $imageinfo;
    private $percent = 0.5;

    /**
     * 图片压缩
     * @param $src 源图
     * @param float $percent  压缩比例
     */
    public function __construct($src, $width = 750) {
        $this->src = $src;
        $this->width = $width;
    }

    /** 高清压缩图片
     * @param string $saveName  提供图片名（可不带扩展名，用源图扩展名）用于保存。或不提供文件名直接显示
     */
    public function compressImg($saveName = '') {
        $this->_openImage();
        if (!empty($saveName)) {
            $this->_saveImage($saveName);  //保存
        } else {
            $this->_showImage();
        }
    }

    /**
     * 内部：打开图片
     */
    private function _openImage() {
        list($width, $height, $type, $attr) = getimagesize($this->src);

        if ($width > 750) {
            $this->imageinfo = array(
                'width' => $width,
                'height' => $height,
                'type' => image_type_to_extension($type, false),
                'attr' => $attr
            );
            $fun = "imagecreatefrom" . $this->imageinfo['type'];
            $this->image = $fun($this->src);
            $this->_thumpImage($width, $height);
        }
    }

    /**
     * 内部：操作图片
     */
    private function _thumpImage($width, $height) {
        $new_width = $this->width;
        $new_height = round($this->width * $height / $width);
        $image_thump = imagecreatetruecolor($new_width, $new_height);
        //将原图复制带图片载体上面，并且按照一定比例压缩,极大的保持了清晰度
        imagecopyresampled($image_thump, $this->image, 0, 0, 0, 0, $new_width, $new_height, $this->imageinfo['width'], $this->imageinfo['height']);
        imagedestroy($this->image);
        $this->image = $image_thump;
    }

    /**
     * 输出图片:保存图片则用saveImage()
     */
    private function _showImage() {
        header('Content-Type: image/' . $this->imageinfo['type']);
        $funcs = "image" . $this->imageinfo['type'];
        $funcs($this->image);
    }

    /**
     * 保存图片到硬盘：
     * @param  string $dstImgName  1、可指定字符串不带后缀的名称，使用源图扩展名 。2、直接指定目标图片名带扩展名。
     */
    private function _saveImage($dstImgName) {
        if (empty($dstImgName))
            return false;
        list($width, $height, $type, $attr) = getimagesize($this->src);

        if ($width > 750) {

            $allowImgs = ['.jpg', '.jpeg', '.png', '.bmp', '.wbmp', '.gif'];   //如果目标图片名有后缀就用目标图片扩展名 后缀，如果没有，则用源图的扩展名
            $dstExt = strrchr($dstImgName, ".");
            $sourseExt = strrchr($this->src, ".");
            if (!empty($dstExt))
                $dstExt = strtolower($dstExt);
            if (!empty($sourseExt))
                $sourseExt = strtolower($sourseExt);

            //有指定目标名扩展名
            if (!empty($dstExt) && in_array($dstExt, $allowImgs)) {
                $dstName = $dstImgName;
            } elseif (!empty($sourseExt) && in_array($sourseExt, $allowImgs)) {
                $dstName = $dstImgName . $sourseExt;
            } else {
                $dstName = $dstImgName . $this->imageinfo['type'];
            }
            $funcs = "image" . $this->imageinfo['type'];
            $funcs($this->image, $dstName);
        }
    }

    /**
     * 销毁图片
     */
    public function __destruct()
    {
        if ($this->image != null) {
            imagedestroy($this->image);
        }
    }

}
