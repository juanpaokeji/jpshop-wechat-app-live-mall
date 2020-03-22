<?php

/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace app\models\shop;

//引入各表实体
use yii;
use app\models\core\TableModel;
 use app\models\shop\GoodsModel;

/**
 *
 * @version   2018年04月16日
 * @author    YangJing <120912212@qq.com>
 * @copyright Copyright 2018 Swoft software
 * @license   PHP Version 7.x {@link http://www.php.net/license/3_0.txt}
 *
 * @Bean()
 */
class SystemModel extends TableModel {

    public function init($key, $id) {
        $sql = "INSERT INTO `shop_banner` ('`key`','merchant_id','name','pic_url','jump_url','type','status','create_time','update_time','delete_time') VALUES ( '{$key}', '13', '1', 'http://juanpao999-1255754174.cos.cn-south.myqcloud.com/shop%2Fbanner%2F2019%2F02%2F27%2F15512598575c7658d182aaa.jpeg', '1', '2', '1', '1551259857', null, null);" .
                "INSERT INTO `shop_marchant_category`('name','`key`','merchant_id','parent_id','pic_url','detail_info','is_top','sort','status','create_time','update_time','delete_time')  VALUES ( '新品', '{$key}', '13', '0', 'http://juanpao999-1255754174.cos.cn-south.myqcloud.com/admin%2Fshop%2Fcategory%2F15512586645c765428949c2.png', '测试主分类，仅供首次体验，审核小程序前需要修改', '0', '100', '1', '1551255915', '1551258664', null);" .
                "INSERT INTO `shop_marchant_category` ('name','`key`','merchant_id','parent_id','pic_url','detail_info','is_top','sort','status','create_time','update_time','delete_time') VALUES ( '热卖', '{$key}', '13', '0', 'http://juanpao999-1255754174.cos.cn-south.myqcloud.com/admin%2Fshop%2Fcategory%2F15512586375c76540da845b.png', '测试主分类，仅供首次体验，审核小程序前需要修改', '0', '200', '1', '1551255930', '1551258637', null);" .
                "INSERT INTO `shop_marchant_category` ('name','`key`','merchant_id','parent_id','pic_url','detail_info','is_top','sort','status','create_time','update_time','delete_time') VALUES ('热卖', '{$key}', '13', '58', 'http://juanpao999-1255754174.cos.cn-south.myqcloud.com/admin%2Fshop%2Fcategory%2F15512587925c7654a82482f.png', '测试主分类，仅供首次体验，审核小程序前需要修改', '0', '101', '1', '1551256021', '1551520435', null);" .
                "INSERT INTO `shop_marchant_category` ('name','`key`','merchant_id','parent_id','pic_url','detail_info','is_top','sort','status','create_time','update_time','delete_time') VALUES ( '热卖', '{$key}', '13', '58', 'http://juanpao999-1255754174.cos.cn-south.myqcloud.com/admin%2Fshop%2Fcategory%2F15512587515c76547fe8d03.png', '测试主分类，仅供首次体验，审核小程序前需要修改', '0', '102', '1', '1551256168', '1551520442', null);" .
                "INSERT INTO `shop_marchant_category` ('name','`key`','merchant_id','parent_id','pic_url','detail_info','is_top','sort','status','create_time','update_time','delete_time') VALUES ( '新款', '{$key}', '13', '59', 'http://juanpao999-1255754174.cos.cn-south.myqcloud.com/admin%2Fshop%2Fcategory%2F15512587695c765491a98cb.png', '测试主分类，仅供首次体验，审核小程序前需要修改', '0', '201', '1', '1551256211', '1551520465', null);" .
                "INSERT INTO `shop_marchant_category` ('name','`key`','merchant_id','parent_id','pic_url','detail_info','is_top','sort','status','create_time','update_time','delete_time') VALUES ('新品', '{$key}', '13', '58', 'http://juanpao999-1255754174.cos.cn-south.myqcloud.com/merchant%2Fshop%2Fcategory%2F15512588895c7655097c765.png', '测试主分类，仅供首次体验，审核小程序前需要修改', '0', '1', '1', '1551258889', '1551520452', null);" .
                "INSERT INTO `shop_voucher_type` ('`key`','merchant_id','name','price','full_price','receive_count','send_count','count','days','from_date','to_date','type','set_online_time','status','create_time','update_time','delete_time') VALUES ( 'wLCSUf', '13', '测试优惠券', '5.00', '98.00', '2', '4', '100', '0', '1551139200', '1585612800', '1', '1551139200', '1', '1551260000', '1551432204', null);" .
                "INSERT INTO `shop_voucher_type` ('`key`','merchant_id','name','price','full_price','receive_count','send_count','count','days','from_date','to_date','type','set_online_time','status','create_time','update_time','delete_time')  VALUES ('wLCSUf', '13', '测试优惠券', '10.00', '198.00', '1', '2', '100', '0', '1551139200', '1585612800', '1', '1551139200', '1', '1551260038', '1551403890', null);" .
                "INSERT INTO `shop_voucher_type` ('`key`','merchant_id','name','price','full_price','receive_count','send_count','count','days','from_date','to_date','type','set_online_time','status','create_time','update_time','delete_time') VALUES ( 'wLCSUf', '13', '测试优惠券', '20.00', '398.00', '1', '1', '100', '0', '1551139200', '1585612800', '1', '1551139200', '1', '1551260061', '1551343573', null);";
        yii::$app->db->createCommand($sql)->execute();
        $goodsModel = new GoodsModel();

        $goodsData = Array
            (
            [0] => Array
                (
                ['`key`'] => $key,
                ['merchant_id'] => $id,
                ['name'] => "商品1",
                ['code'] => "",
                ['pic_urls'] => "http://juanpao999-1255754174.cos.cn-south.myqcloud.com/goods%2F2019%2F03%2F02%2F15515205225c7a530ac2611.png,",
                ['price'] => 1.00,
                ['line_price'] => 0.00,
                ['stocks'] => 20,
                ['category_id'] => 0,
                ['m_category_id'] => 63,
                ['property1'] => ":红色, 蓝色",
                ['property2'] => " :",
                ['stock_type'] => 1,
                ['have_stock_type'] => 1,
                ['sort'] => 0,
                ['shop_express_template_id'] => 0,
                ['type'] => 1,
                ['detail_info'] => '<p>1<img src = "http://juanpao999-1255754174.cos.cn-south.myqcloud.com/shop%2F15515205285c7a5310c8e14.png" style = "max-width: 100%;"><br></p>',
                ['simple_info'] => "",
                ['label'] => " 微信支付,",
                ['short_name'] => "",
                ['is_top'] => 1,
                ['look'] => 0,
                ['status'] => 1,
                ['start_time'] => 1551521170,
                ['create_time'] => 1551521170,
                ['update_time'] => 1551521170,
                ['delete_time'] => null,
            ),
            [1] => Array
                (
                ['`key`'] => $key,
                ['merchant_id'] => $id,
                ['name'] => '商品2',
                ['code'] => "",
                ['pic_urls'] => 'http://juanpao999-1255754174.cos.cn-south.myqcloud.com/goods%2F2019%2F03%2F02%2F15515204915c7a52eb9eb98.png,',
                ['price'] => 1.00,
                ['line_price'] => 0.00,
                ['stocks'] => 105,
                ['category_id'] => 0,
                ['m_category_id'] => 60,
                ['property1'] => '',
                ['property2'] => '',
                ['stock_type'] => 1,
                ['have_stock_type'] => 0,
                ['sort'] => 0,
                ['shop_express_template_id'] => 0,
                ['type'] => 1,
                ['detail_info'] => '<p>12<img src = "http://juanpao999-1255754174.cos.cn-south.myqcloud.com/shop%2F15515205015c7a52f527275.png" style = "max-width: 100%;"><br></p>',
                ['simple_info'] => "",
                ['label'] => ' 微信支付,',
                ['short_name'] => "",
                ['is_top'] => 1,
                ['look'] => 0,
                ['status'] => 1,
                ['start_time'] => 1551521004,
                ['create_time'] => 1551521004,
                ['update_time'] => 1551521004,
                ['delete_time'] => null,
            )
        );


        $res = $goodsModel->add($goodsData[1]);
        $sql = " INSERT INTO `shop_stock` ('`key`', 'merchant_id', 'goods_id', 'property1_name', 'property2_name', 'name', 'code', 'weight', 'number', 'price', 'cost_price', 'pic_url', 'status', 'create_time', 'update_time', 'delete_time') VALUES('{$key}', '{$id}', '{$res['data']}', '默认', '', '商品2', '', null, '105', '1.00', '1.00', 'http://juanpao999-1255754174.cos.cn-south.myqcloud.com/goods%2F2019%2F03%2F02%2F15515204915c7a52eb9eb98.png', '1', '1551521004', null, null);";
        yii::$app->db->createCommand($sql)->execute();

        $res = $goodsModel->add($goodsData[0]);
        $sql = "INSERT INTO `shop_stock` ('`key`', 'merchant_id', 'goods_id', 'property1_name', 'property2_name', 'name', 'code', 'weight', 'number', 'price', 'cost_price', 'pic_url', 'status', 'create_time', 'update_time', 'delete_time') VALUES('{$key}', '{$id}', '{$res['data']}', '', '商品1', '', null, '20', '1.00', '0.00', 'http://juanpao999-1255754174.cos.cn-south.myqcloud.com/goods%2F13%2F2019%2F03%2F02%2F15515210625c7a5526afdde.png', '1', '1551521170', null, null);" .
                "INSERT INTO `shop_stock` ('`key`', 'merchant_id', 'goods_id', 'property1_name', 'property2_name', 'name', 'code', 'weight', 'number', 'price', 'cost_price', 'pic_url', 'status', 'create_time', 'update_time', 'delete_time') VALUES('{$key}', '{$id}', '{$res['data']}', '蓝色', '', '商品1', '', null, '0', '0.00', '0.00', 'http://juanpao999-1255754174.cos.cn-south.myqcloud.com/goods%2F13%2F2019%2F03%2F02%2F15515211705c7a559254f9c.png', '1', '1551521170', null, null);";
        yii::$app->db->createCommand($sql)->execute();
    }

}
