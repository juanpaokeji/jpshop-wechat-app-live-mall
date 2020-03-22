<?php
/**
 * This file is part of JWT.
 *
 */

namespace App\Models\Core\SMS;

use Qcloud\Sms\SmsSenderUtil;


/**
 * 腾讯云短信模板
 *
 * @version   2018年03月29日
 * @author    JYS <272074691@qq.com>
 * @copyright Copyright 2010-2016 Swoft software
 * @license   PHP Version 7.x {@link http://www.php.net/license/3_0.txt}
 */
class SmsTemplate
{
    private $url;
    private $appid;
    private $appkey;
    private $util;

    /**
     * 构造函数
     *
     * @param string $appid sdkappid
     * @param string $appkey sdkappid对应的appkey
     */
    public function __construct($appid, $appkey)
    {
        $this->url['add'] = "https://yun.tim.qq.com/v5/tlssmssvr/add_template";
        $this->url['mod'] = "https://yun.tim.qq.com/v5/tlssmssvr/mod_template";
        $this->url['del'] = "https://yun.tim.qq.com/v5/tlssmssvr/del_template";
        $this->url['get'] = "https://yun.tim.qq.com/v5/tlssmssvr/get_template";
        $this->appid = $appid;
        $this->appkey = $appkey;
        $this->util = new SmsSenderUtil();
    }

    /**
     * 添加模板
     *
     * 模板审核通过，国内，海外均可使用。
     *
     * @param string $text 模板内容
     * @param int $type 模板类型，0 为普通短信，1 营销短信，2 语音短信
     * @param string $title 模板名称，可选字段
     * @param string $remark 模板备注，比如申请原因，使用场景等，可选字段
     * @return string 应答json字符串，详细内容参见腾讯云协议文档https://cloud.tencent.com/document/product/382/5817
     */
    public function addTemplate($text, $type = 0, $title = "", $remark = "")
    {
        $random = $this->util->getRandom();
        $curTime = time();
        $wholeUrl = $this->url['add'] . "?sdkappid=" . $this->appid . "&random=" . $random;

        // 按照协议组织 post 包体
        $data = new \stdClass();
        $data->text = $text;
        $data->type = (int)$type;
        $data->title = $title;
        $data->remark = $remark;
        $data->sig = hash("sha256",
            "appkey=" . $this->appkey . "&random=" . $random . "&time="
            . $curTime, FALSE);
        $data->time = $curTime;

        return $this->util->sendCurlPost($wholeUrl, (array)$data);
    }

    /**
     * 修改模板
     *
     * 已审核通过的模板不允许修改。
     *
     *
     * @param  int $tpl_id [description]
     * @param  string $text 新的模板内容
     * @param  int $type 新的模板类型，0：普通短信模板；1：营销短信模板；2：语音短信模板
     * @param  string $title 新的模板名称，可选字段
     * @param  string $remark 新的模板备注，比如申请原因，使用场景等，可选字段
     * @return string  应答json字符串，详细内容参见腾讯云协议文档https://cloud.tencent.com/document/product/382/8649
     */
    public function modTemplate($tpl_id, $text, $type = 0, $title = "", $remark = "")
    {
        $random = $this->util->getRandom();
        $curTime = time();
        $wholeUrl = $this->url['mod'] . "?sdkappid=" . $this->appid . "&random=" . $random;

        // 按照协议组织 post 包体
        $data = new \stdClass();
        $data->tpl_id = (int)$tpl_id;
        $data->text = $text;
        $data->type = (int)$type;
        $data->title = $title;
        $data->remark = $remark;
        $data->sig = hash("sha256",
            "appkey=" . $this->appkey . "&random=" . $random . "&time="
            . $curTime, FALSE);
        $data->time = $curTime;

        return $this->util->sendCurlPost($wholeUrl, (array)$data);
    }

    /**
     * 删除模板
     * @param  array|int $tpl_ids 模板id，也可以通过值指定一个"tpl_id"：123
     * @return string  应答json字符串，详细内容参见腾讯云协议文档https://cloud.tencent.com/document/product/382/5818
     */
    public function delTemplate($tpl_ids)
    {
        $random = $this->util->getRandom();
        $curTime = time();
        $wholeUrl = $this->url['del'] . "?sdkappid=" . $this->appid . "&random=" . $random;

        $data = new \stdClass();
        $data->tpl_id = (array)$tpl_ids;
        $data->sig = hash("sha256",
            "appkey=" . $this->appkey . "&random=" . $random . "&time="
            . $curTime, FALSE);
        $data->time = $curTime;

        return $this->util->sendCurlPost($wholeUrl, (array)$data);
    }

    /**
     * 模板状态查询
     * @param  array $tpl_ids 查询指定模版id的信息，与offset max字段不能同时出现
     * @param  string $offset 分页查询全量模版信息，与tpl_id字段不能同时出现，拉取的偏移量，初始为0，如果要多次拉取，需赋值为上一次的offset与max字段的和（应答包的total字段为模版总条数）
     * @param  string $max 分页查询全量模版信息，与tpl_id字段不能同时出现，一次拉取的条数，最多50
     * @return string  应答json字符串，详细内容参见腾讯云协议文档https://cloud.tencent.com/document/product/382/5819
     */
    public function pullTemplateStatus($tpl_ids, $offset = "", $max = "")
    {
        $random = $this->util->getRandom();
        $curTime = time();
        $wholeUrl = $this->url['get'] . "?sdkappid=" . $this->appid . "&random=" . $random;

        $data = new \stdClass();

        if (empty($offset) && empty($max)) {
            $data->tpl_id = (array)$tpl_ids;
        } else {
            $tpl_page = new \stdClass();
            if (empty($offset)) {
                $tpl_page->offset = 0;
            } else {
                $tpl_page->offset = (int)$offset;
            }

            if (empty($max)) {
                $tpl_page->max = 10;
            } else {
                if ((int)$max > 50) {
                    $tpl_page->max = 50;
                } else {
                    $tpl_page->max = (int)$max;
                }
            }

            $data->tpl_page = $tpl_page;
        }

        $data->sig = hash("sha256",
            "appkey=" . $this->appkey . "&random=" . $random . "&time="
            . $curTime, FALSE);
        $data->time = $curTime;

        return $this->util->sendCurlPost($wholeUrl, (array)$data);
    }
}
