<?php
namespace app\controllers\shop;

use app\models\admin\app\AppAccessModel;
use app\models\admin\user\SystemAccessModel;
use app\models\merchant\system\SystemAppAccessHelpModel;
use app\models\shop\ShopPosterModel;
use Imagine\Gd\Font;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Yii;
use app\models\shop\GoodsModel;
use app\models\tuan\LeaderModel;
use Imagine\Gd\Imagine;

use yii\web\ShopController;

class PosterController extends ShopController{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors() {
        return [
            'token' => [
                'class' => 'yii\filters\ShopFilter', //调用过滤器
                'except' => ['test','home-images','detail-images'], //指定控制器不应用到哪些动作
            ]
        ];
    }

    /**
     * @return array|string
     * @throws \yii\db\Exception
     */
    public function actionHomeImages()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $key = yii::$app->session['key'] == null ? $params['key'] : yii::$app->session['key'];
            $merchant_id = yii::$app->session['merchant_id'];
            if(!$merchant_id){
                $systemAppAccessModel = new AppAccessModel();
                $keyInfo = $systemAppAccessModel->find(['`key`' => $key]);
                if($keyInfo['status'] != 200){
                    return result(500, '数据出错了');
                }
                $merchant_id = $keyInfo['data']['merchant_id'];
            }
            $user_id = yii::$app->session['user_id'];
            if(isset($params['test'])){
                $user_id = 109;
            }
            if(empty($user_id)){
                $rand = rand(100000,999999);
            }else{
                $rand = $user_id;
            }
            $options = [];
            if(!isset($params['scene']) || empty($params['scene'])){
                $params['scene'] = 'home_image';
            }else{
                $params['scene'] = str_replace(',','&',$params['scene']);
            }
            if(!isset($params['page']) || empty($params['page'])){
                $options['page'] = 'pages/index/index/index';
            }
            try{
                $image = new Imagine();
                //查询是否有默认的海报背景图
                $posterModel = new ShopPosterModel();
                $posertInfo = $posterModel->one(['key'=>$key,'type'=> 0]);
                if($posertInfo['status'] != 200){
                    $image_url = './uploads/default_poster/poster1.jpg';
                }else{
                    $image_url = $posertInfo['data']['path'];
                }
                $imagine = $image->open($image_url);
                $options['width'] = 110;
                $options['auto_color'] = true;
                $options['is_hyaline'] = true;
                $code_res = $this->createQrScene((string)$key,(int)$merchant_id,'home',$params['scene'],$options);// 二维码相对路径
                if($code_res['code'] != 200){
                    throw new \Exception($code_res['errmsg']);
                }
                $code_str = $code_res['url'];
                //合成小程序二维码
                $code = $image->open($code_str);
                $code->resize(new Box(110, 110));
                $imagine->paste($code, new Point(242, 524));
                //合成商品信息
                $model = new GoodsModel();
                $where['`key`'] = $key;
                $where['limit'] = 9;
                unset($params['key']);
                $array = $model->finds($where);
                if($array['status'] != 200){
                    throw new \Exception('商品信息出错');
                }
                $font_file = Yii::getAlias('@webroot').'/uploads/default_poster/Alibaba-PuHuiTi-Regular.ttf';
                $text_font    = new Font($font_file, 12, $imagine->palette()->color('000'));
                $text_font2    = new Font($font_file, 16, $imagine->palette()->color('000'));
                $text_font3    = new Font($font_file, 10, $imagine->palette()->color('000'));
                $text_font1    = new Font($font_file, 10, $imagine->palette()->color('FF0000'));
                foreach ($array['data'] as $keys=>$val){
                    if($keys == 0){
                        $local_goods = self::getPicture($val['pic_urls'][0], $rand,'goods');
                        if($local_goods){
                            $pic_url = $image->open($local_goods);
                            $pic_url->resize(new Box(95, 95));
                            $imagine->paste($pic_url, new Point(24, 25));
                        }
                        if(mb_strlen($val['name']) > 7){
                            $val['name'] = mb_substr($val['name'],0,5).'...';
                        }
                        $imagine->draw()->text($val['name'], $text_font, new Point(24, 126));
                        $imagine->draw()->text('￥', $text_font1, new Point(28, 145));
                        $imagine->draw()->text($val['price'], $text_font1, new Point(39, 145));
                    }
                    if($keys == 1){
                        $local_goods = self::getPicture($val['pic_urls'][0], $rand,'goods');
                        if($local_goods){
                            $pic_url = $image->open($local_goods);
                            $pic_url->resize(new Box(95, 95));
                            $imagine->paste($pic_url, new Point(140, 25));
                        }
                        if(mb_strlen($val['name']) > 7){
                            $val['name'] = mb_substr($val['name'],0,5).'...';
                        }
                        $imagine->draw()->text($val['name'], $text_font, new Point(140, 126));
                        $imagine->draw()->text('￥', $text_font1, new Point(144, 149));
                        $imagine->draw()->text($val['price'], $text_font1, new Point(156, 149));
                    }
                    if($keys == 2){
                        $local_goods = self::getPicture($val['pic_urls'][0], $rand,'goods');
                        if($local_goods){
                            $pic_url = $image->open($local_goods);
                            $pic_url->resize(new Box(95, 95));
                            $imagine->paste($pic_url, new Point(256, 25));
                        }
                        if(mb_strlen($val['name']) > 7){
                            $val['name'] = mb_substr($val['name'],0,5).'...';
                        }
                        $imagine->draw()->text($val['name'], $text_font, new Point(256, 126));
                        $imagine->draw()->text('￥', $text_font1, new Point(260, 149));
                        $imagine->draw()->text($val['price'], $text_font1, new Point(271, 149));
                    }
                    if($keys == 3){
                        $local_goods = self::getPicture($val['pic_urls'][0], $rand,'goods');
                        if($local_goods){
                            $pic_url = $image->open($local_goods);
                            $pic_url->resize(new Box(95, 95));
                            $imagine->paste($pic_url, new Point(24, 191));
                        }
                        if(mb_strlen($val['name']) > 7){
                            $val['name'] = mb_substr($val['name'],0,5).'...';
                        }
                        $imagine->draw()->text($val['name'], $text_font, new Point(24, 292));
                        $imagine->draw()->text('￥', $text_font1, new Point(28, 315));
                        $imagine->draw()->text($val['price'], $text_font1, new Point(39, 315));
                    }
                    if($keys == 4){
                        $local_goods = self::getPicture($val['pic_urls'][0], $rand,'goods');
                        if($local_goods){
                            $pic_url = $image->open($local_goods);
                            $pic_url->resize(new Box(95, 95));
                            $imagine->paste($pic_url, new Point(140, 191));
                        }
                        if(mb_strlen($val['name']) > 7){
                            $val['name'] = mb_substr($val['name'],0,5).'...';
                        }
                        $imagine->draw()->text($val['name'], $text_font, new Point(140, 292));
                        $imagine->draw()->text('￥', $text_font1, new Point(144, 315));
                        $imagine->draw()->text($val['price'], $text_font1, new Point(156, 315));
                    }
                    if($keys == 5){
                        $local_goods = self::getPicture($val['pic_urls'][0], $rand,'goods');
                        if($local_goods){
                            $pic_url = $image->open($local_goods);
                            $pic_url->resize(new Box(95, 95));
                            $imagine->paste($pic_url, new Point(256, 191));
                        }
                        if(mb_strlen($val['name']) > 7){
                            $val['name'] = mb_substr($val['name'],0,5).'...';
                        }
                        $imagine->draw()->text($val['name'], $text_font, new Point(256, 292));
                        $imagine->draw()->text('￥', $text_font1, new Point(260, 315));
                        $imagine->draw()->text($val['price'], $text_font1, new Point(271, 315));
                    }
                    if($keys == 6){
                        $local_goods = self::getPicture($val['pic_urls'][0], $rand,'goods');
                        if($local_goods){
                            $pic_url = $image->open($local_goods);
                            $pic_url->resize(new Box(95, 95));
                            $imagine->paste($pic_url, new Point(24, 357));
                        }
                        $pic_url = $image->open($val['pic_urls'][0]);
                        $pic_url->resize(new Box(95, 95));
                        $imagine->paste($pic_url, new Point(24, 357));
                        if(mb_strlen($val['name']) > 7){
                            $val['name'] = mb_substr($val['name'],0,5).'...';
                        }
                        $imagine->draw()->text($val['name'], $text_font, new Point(24, 458));
                        $imagine->draw()->text('￥', $text_font1, new Point(28, 483));
                        $imagine->draw()->text($val['price'], $text_font1, new Point(41, 483));
                    }
                    if($keys == 7){
                        $local_goods = self::getPicture($val['pic_urls'][0], $rand,'goods');
                        if($local_goods){
                            $pic_url = $image->open($local_goods);
                            $pic_url->resize(new Box(95, 95));
                            $imagine->paste($pic_url, new Point(140, 357));
                        }
                        if(mb_strlen($val['name']) > 7){
                            $val['name'] = mb_substr($val['name'],0,5).'...';
                        }
                        $imagine->draw()->text($val['name'], $text_font, new Point(140, 458));
                        $imagine->draw()->text('￥', $text_font1, new Point(144, 483));
                        $imagine->draw()->text($val['price'], $text_font1, new Point(158, 483));
                    }
                    if($keys == 8){
                        $local_goods = self::getPicture($val['pic_urls'][0], $rand,'goods');
                        if($local_goods){
                            $pic_url = $image->open($local_goods);
                            $pic_url->resize(new Box(95, 95));
                            $imagine->paste($pic_url, new Point(256, 357));
                        }
                        if(mb_strlen($val['name']) > 7){
                            $val['name'] = mb_substr($val['name'],0,5).'...';
                        }
                        $imagine->draw()->text($val['name'], $text_font, new Point(256, 458));
                        $imagine->draw()->text('￥', $text_font1, new Point(260, 483));
                        $imagine->draw()->text($val['price'], $text_font1, new Point(273, 483));
                    }
                }
                if($user_id){
                    $userModel = new \app\models\shop\UserModel();
                    $user = $userModel->find(['id' => $user_id]);
                    $leaderModel = new LeaderModel();
                    $leader = $leaderModel->do_one(['key' => $key, 'merchant_id' => $merchant_id, 'uid' => $user['data']['leader_uid']]);
                    if ($leader['status'] == 200 && $user['status'] == 200) {
                        $local_avatar = self::getPicture($user['data']['avatar'], $rand,'goods');
                        if($local_avatar){
                            $local_avatar = $this->radiusImage($local_avatar);
                            $avatar = $image->open($local_avatar);
                            $avatar->resize(new Box(30, 30));
                            $imagine->paste($avatar, new Point(25, 550));
                        }
                        $imagine->draw()->text($user['data']['nickname'], $text_font2, new Point(62, 554));
                        $text = '自提点:'.$leader['data']['area_name'].$leader['data']['addr'];
                        $text_length = mb_strlen($text);
                        if($text_length <= 16){
                            $imagine->draw()->text($text, $text_font3, new Point(25, 593));
                        }elseif ($text_length > 16 && $text_length <= 32){
                            $text1 = mb_substr($text,0,16);
                            $text2 = mb_substr($text,16);
                            $imagine->draw()->text($text1, $text_font3, new Point(25, 593));
                            $imagine->draw()->text($text2, $text_font3, new Point(25, 610));
                        }
                        $imagine->draw()->text('长按扫码识别', $text_font3, new Point(261, 635));
                    }
                }
                $url_path = 'uploads/poster/'.$key.'/'.$merchant_id.'/';
              	creat_mulu( 'uploads/poster/'.$key.'/'.$merchant_id);
                //保存的文件名
                $save_name = 'poster_'.$key.'_'.$merchant_id.'.png';
                //完整路径
                $save_path = $url_path . $save_name;
                $path = Yii::getAlias('@webroot') . $url_path;
                if (!is_dir($path)) {
                    @mkdir($path, 0755, true);
                }
                $imagine->save(Yii::getAlias('@webroot') .'/'.$url_path."/".$save_name);
                return result(200, "请求成功",'https://' . $_SERVER['SERVER_NAME'].'/api/web/' . $save_path);
            }catch (\Exception $e){
                return result(500, $e->getMessage());
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 商品详情页合成
     * @return array|string
     * @throws \yii\db\Exception
     */
    public function actionDetailImages(){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            if(!isset($params['goods_id']) || empty($params['goods_id'])){
                return result(500, "缺少商品id");
            }
            $key = yii::$app->session['key'] == null ? $params['key'] : yii::$app->session['key'];
            $merchant_id = yii::$app->session['merchant_id'];
            if(!$merchant_id){
                $systemAppAccessModel = new AppAccessModel();
                $keyInfo = $systemAppAccessModel->find(['`key`' => $key]);
                if($keyInfo['status'] != 200){
                    return result(500, '数据出错了');
                }
                $merchant_id = $keyInfo['data']['merchant_id'];
            }
            $user_id = yii::$app->session['user_id'];
            if(isset($params['test'])){
                $user_id = 109;
            }
            if(empty($user_id)){
                $rand = rand(100000,999999);
            }else{
                $rand = $user_id;
            }
            $options = [];
            if(!isset($params['scene']) || empty($params['scene'])){
                $params['scene'] = 'detail_image';
            }else{
                $params['scene'] = str_replace(',','&',$params['scene']);
               // $params['scene'] = json_encode(explode(',', $params['scene']));
            }
            if(!isset($params['page']) || empty($params['page'])){
                $options['page'] = 'pages/goodsItem/goodsItem/goodsItem';
            }
            try{
                $image = new Imagine();
                //查询是否有默认的海报背景图
                $posterModel = new ShopPosterModel();
                $posertInfo = $posterModel->one(['key'=>$key,'type'=> 1]);
                if($posertInfo['status'] != 200){
                    $image_url = './uploads/default_poster/poster0.jpg';
                }else{
                    $image_url = $posertInfo['data']['path'];
                }
                $imagine = $image->open($image_url);
                $options['width'] = 110;
                $options['auto_color'] = true;
                $options['is_hyaline'] = true;
                $code_res = $this->createQrScene((string)$key,(int)$merchant_id,'detail',$params['scene'],$options);// 二维码相对路径
                if($code_res['code'] != 200){
                    throw new \Exception($code_res['errmsg']);
                }
                $code_str = $code_res['url'];
                //合成小程序二维码
                $code = $image->open($code_str);
                $code->resize(new Box(110, 110));
                $imagine->paste($code, new Point(249, 423));
                //合成商品信息
                $model = new GoodsModel();
                $where['`key`'] = $key;
                $where['id'] = $params['goods_id'];
                unset($params['key']);
                $array = $model->find($where);
                if($array['status'] != 200){
                    throw new \Exception('商品信息出错');
                }
                $total = $model->TotalSale($params['goods_id']);
                $array['data']['totalSale'] = $total['data'];
                $totals = $array['data']['totalSale']['total'] + $array['data']['sales_number'];
                $avatar = $this->avatar($array['data']['id']);
                if(!empty($avatar)){
                    foreach ($avatar as $k=>$avatar_url){
                        if($k == 0){
                            $local_avatar = self::getPicture($avatar_url['avatar'], $rand,'detail_avatar');
                            if($local_avatar){
                                $local_avatar = $this->radiusImage($local_avatar);
                                $pic_url = $image->open($local_avatar);
                                $pic_url->resize(new Box(20, 20));
                                $imagine->paste($pic_url, new Point(28, 430));
                            }
                        }
                        if($k == 1){
                            $local_avatar = self::getPicture($avatar_url['avatar'], $rand,'detail_avatar');
                            if($local_avatar){
                                $local_avatar = $this->radiusImage($local_avatar);
                                $pic_url = $image->open($local_avatar);
                                $pic_url->resize(new Box(20, 20));
                                $imagine->paste($pic_url, new Point(49, 430));
                            }
                        }
                        if($k == 2){
                            $local_avatar = self::getPicture($avatar_url['avatar'], $rand,'detail_avatar');
                            if($local_avatar){
                                $local_avatar = $this->radiusImage($local_avatar);
                                $pic_url = $image->open($local_avatar);
                                $pic_url->resize(new Box(20, 20));
                                $imagine->paste($pic_url, new Point(70, 430));
                            }
                        }
                        if($k == 3){
                            $local_avatar = self::getPicture($avatar_url['avatar'], $rand,'detail_avatar');
                            if($local_avatar){
                                $local_avatar = $this->radiusImage($local_avatar);
                                $pic_url = $image->open($local_avatar);
                                $pic_url->resize(new Box(20, 20));
                                $imagine->paste($pic_url, new Point(91, 430));
                            }
                        }
                        if($k == 4){
                            $local_avatar = self::getPicture($avatar_url['avatar'], $rand,'detail_avatar');
                            if($local_avatar){
                                $local_avatar = $this->radiusImage($local_avatar);
                                $pic_url = $image->open($local_avatar);
                                $pic_url->resize(new Box(20, 20));
                                $imagine->paste($pic_url, new Point(112, 430));
                            }
                        }
                    }
                }
                $font_file = Yii::getAlias('@webroot').'/uploads/default_poster/Alibaba-PuHuiTi-Regular.ttf';
                $text_font    = new Font($font_file, 18, $imagine->palette()->color('000'));
                $text_font2    = new Font($font_file, 16, $imagine->palette()->color('000'));
                $text_font3    = new Font($font_file, 12, $imagine->palette()->color('000'));
                $text_font1    = new Font($font_file, 20, $imagine->palette()->color('FF0000'));
                $imagine->draw()->text('等已抢购'.$totals.'份', $text_font3, new Point(135, 431));
                $pic_urls = explode(',',$array['data']['pic_urls']);
                
                $local_goods = self::getPicture($pic_urls[0], $rand,'goods');
                
                if($local_goods){
                    $pic_url = $image->open($local_goods);
                    $pic_url->resize(new Box(320, 320));
                    $imagine->paste($pic_url, new Point(28, 30));
                }
                if(mb_strlen($array['data']['name']) > 7){
                    $array['data']['name'] = mb_substr($array['data']['name'],0,13).'...';
                }
                $imagine->draw()->text($array['data']['name'], $text_font, new Point(36, 359));
                $imagine->draw()->text('￥', $text_font1, new Point(46, 400));
                $imagine->draw()->text($array['data']['price'], $text_font1, new Point(66, 400));
                if($user_id){
                    $userModel = new \app\models\shop\UserModel();
                    $user = $userModel->find(['id' => $user_id]);
                    $leaderModel = new LeaderModel();
                    $leader = $leaderModel->do_one(['key' => $key, 'merchant_id' => $merchant_id, 'uid' => $user['data']['leader_uid']]);
                    if ($leader['status'] == 200 && $user['status'] == 200) {
                        $local_avatar = self::getPicture($user['data']['avatar'], $rand,'goods');
                        if($local_avatar){
                            $local_avatar = $this->radiusImage($local_avatar);
                            $avatar = $image->open($local_avatar);
                            $avatar->resize(new Box(30, 30));
                            $imagine->paste($avatar, new Point(28, 470));
                        }
                        $imagine->draw()->text($user['data']['nickname'], $text_font2, new Point(65, 475));
                        $text = '自提点:'.$leader['data']['area_name'].$leader['data']['addr'];
                        $text_length = mb_strlen($text);
                        if($text_length <= 16){
                            $imagine->draw()->text($text, $text_font3, new Point(25, 593));
                        }elseif ($text_length > 16 && $text_length <= 32){
                            $text1 = mb_substr($text,0,16);
                            $text2 = mb_substr($text,16);
                            $imagine->draw()->text($text1, $text_font3, new Point(28, 510));
                            $imagine->draw()->text($text2, $text_font3, new Point(28, 530));
                        }
                        $imagine->draw()->text('长按扫码识别', $text_font3, new Point(268, 534));
                    }
                }
                $url_path = 'uploads/poster_detail/'.$key.'/'.$merchant_id.'/';
              creat_mulu( 'uploads/poster_detail/'.$key.'/'.$merchant_id);
                //保存的文件名
                $save_name = 'poster_detail_'.$key.'_'.$merchant_id.'.png';
                //完整路径
                $save_path = $url_path . $save_name;
                $path = Yii::getAlias('@webroot') . $url_path;
                if (!is_dir($path)) {
                    @mkdir($path, 0755, true);
                }
                $imagine->save(Yii::getAlias('@webroot') .'/'.$url_path."/".$save_name);
                return result(200, "请求成功",'https://' . $_SERVER['SERVER_NAME']."/api/web/" . $save_path);
            }catch (\Exception $e){
                return result(500, $e->getMessage());
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 切圆角
     * @param $imgPath
     * @return bool|string
     */
    public function radiusImage($imgPath)
    {
        $extension = pathinfo($imgPath);
        $savePath = $extension['dirname'].'/'.$extension['basename'];
        $srcImg = null;
        switch ($extension['extension']) {
            case 'jpg':
                $srcImg = imagecreatefromjpeg($imgPath);
                break;
            case 'png':
                $srcImg = imagecreatefrompng($imgPath);
                break;
            case 'jpeg':
                $srcImg = imagecreatefromjpeg($imgPath);
                break;
        }
        $info = getimagesize($imgPath);
        $width = $info[0];
        $height = $info[1];
        $minLength = min($width, $height);
        $img = imagecreatetruecolor($width, $height);
        //这一句一定要有
        imagesavealpha($img, true);
        //拾取一个完全透明的颜色,最后一个参数127为全透明
        $bg = imagecolorallocatealpha($img, 255, 255, 255, 127);
        imagefill($img, 0, 0, $bg);
        $radius = $minLength / 2; //圆半径
        for ($x = 0; $x < $minLength; $x++) {
            for ($y = 0; $y < $minLength; $y++) {
                $rgbColor = imagecolorat($srcImg, $x, $y);
                if (((($x - $radius) * ($x - $radius) + ($y - $radius) * ($y - $radius)) <= ($radius * $radius))) {
                    imagesetpixel($img, $x, $y, $rgbColor);
                }
            }
        }
        $result = imagepng($img, $savePath);
        imagedestroy($img);
        return $result ? $savePath : false;
    }

    /**
     * 头像id
     * @param $id
     * @return array
     * @throws \yii\db\Exception
     */
    public function avatar($id)
    {
        $sql = "select DISTINCT avatar from shop_order_group inner join shop_user on shop_user.id = shop_order_group.user_id inner join shop_order on shop_order.order_group_sn = shop_order_group.order_sn where shop_order.goods_id = {$id} and shop_order_group.status not in  (0,2,8)group by  shop_order.order_group_sn";
        $res = yii::$app->db->createCommand($sql)->queryAll();
        return $res;
    }
    /**
     * 下载图片
     *
     * @param string $url
     * @param string $uid
     * @param string $group
     *
     * @return null|string
     */
    public static function getPicture(string $url, $uid, $group = 'avatar') {
        $file_path = '/uploads/download';
        $path      = Yii::getAlias('@webroot') . $file_path . '/' . $group;
        if (!is_dir($path)) {
            @mkdir($path, 0755, true);
        }
        $new_file = $path . '/' . $uid;
        $header   = [
            'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:45.0) Gecko/20100101 Firefox/45.0',
            'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3',
            'Accept-Encoding: gzip, deflate',
        ];
        $curl     = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        $data = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        $str="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";
        if($str===base64_encode($data)){
            return null;
        }
        if ($code == 200) {//把URL格式的图片转成base64_encode格式的！
            $imgBase64Code = "data:image/jpeg;base64," . base64_encode($data);
        } else {
            $imgBase64Code = '';
        }
        $img_content = $imgBase64Code;//图片内容
        //echo $img_content;exit;
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $img_content, $result)) {
            $type     = $result[2];//得到图片类型png?jpg?gif?
            $new_file = $new_file . '.' . $type;
            file_put_contents($new_file, base64_decode(str_replace($result[1], '', $img_content)));
            return $new_file;
        }
        return null;
    }
}
