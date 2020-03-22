<?php

/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace app\models\merchant\partnerUser;

use app\models\common\CommonModel;
use Yii;

/**
 * Class PartnerUserModel
 * @package app\models\partnerUser
 */
class PartnerUserModel extends CommonModel {

    /**
     * merchant_partner_user
     * @return string
     */
    public static function tableName() {
        return 'merchant_partner_user';
    }

    /**
     * 列表
     * @param $params
     * @return array
     */
    public function do_select($params) {
        //数据库操作
        $params['orderby'] = "id desc";
        $params['count'] = true;
        $res = $this->get_list($params);
        if (empty($res)) {
            return result(204, '查询失败');
        } else {
            return ['status'=> 200, 'message' => '请求成功','data' => $res['data'], 'count' => $res['count']];
        }
    }
    /**
     * 新增
     * @param $params
     * @return array
     */
    public function add($params) {
        $data = array();
        $res = $this->modify($params, $data);
        if (empty($res)) {
            return result(204, '查询失败');
        } else {
            return result(200, '新增成功',$res);
        }
    }


    /**
     * @param $where
     * @param $data
     * @return array
     */
    public function do_update($where, $data) {

        $res = $this->modify($where, $data);
        if ($res == false) {
            return result(500, '更新失败');
        } else {
           return result(200, '请求成功');
        }
    }


    /**
     * 查询单条数据
     * @param $params
     * @return array
     */
    public function one($params) {

        $res = $this->get_info($params);

        if (empty($res)) {
            return result(204, '查询失败');
        } else {
            return result(200, '请求成功',$res);
        }
    }

    /**
     * 删除
     * @param $where
     * @return array
     */
    public function do_delete($where) {
        $res = $this->soft_delete($where);
        if ($res == false) {
            return result(500, '删除失败');
        } else {
            return result(200, '请求成功');
        }
    }

    /**
     * 高德
     * @param $locations
     * @param int $type 0 是经纬度转地理位置 1是地理位置转经纬度
     * @return bool|string
     */
    public function getAddrGD($locations,$type = 0){
        if($type){
            $url = "https://restapi.amap.com/v3/geocode/regeo?key=6508c123d3c4d9d785ee82ac7ce37a81&location=".$locations;
            $result = json_decode($this->curl_get_contents($url),true);
            if($result['status'] == 1){
                return $result['regeocode']['addressComponent']['adcode'];
            }
        }else{
            $url = "https://restapi.amap.com/v3/geocode/geo?key=6508c123d3c4d9d785ee82ac7ce37a81&address=".$locations;
            $result = json_decode($this->curl_get_contents($url),true);
            if($result['status'] == 1 && $result['geocodes'][0]['level'] == '区县'){
                return $result['geocodes'][0]['adcode'];
            }
            return false;
        }
        return false;
    }

    /**
     * 腾讯
     * @param $locations
     * @param int $type 0 是经纬度转地理位置 1是地理位置转经纬度
     * @return bool|string
     */
    public function getAddrTX($locations,$type = 0){
        if($type){
            $url = "https://apis.map.qq.com/ws/geocoder/v1/?key=JMIBZ-JU2WK-OJJJB-AC7HK-4J4OZ-K6BTO&location=".$locations;
            $result = $this->curl_get_contents($url);
            if($result['status'] == 0){
                return $result['ad_info']['adcode'];
            }
        }else{
            $url = "https://apis.map.qq.com/ws/geocoder/v1/?key=JMIBZ-JU2WK-OJJJB-AC7HK-4J4OZ-K6BTO&address=".$locations;
            $result = $this->curl_get_contents($url);
            if($result['status'] == 0){
                return ['ad_info']['adcode'];
            }
        }
        return false;
    }

    protected function curl_get_contents($url, $timeout = 5)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        @curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, true);
        curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 0);
        $content = curl_exec($ch);
        curl_close($ch);
        return $content;
    }
}
