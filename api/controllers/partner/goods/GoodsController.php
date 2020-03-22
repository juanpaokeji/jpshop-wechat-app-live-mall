<?php

namespace app\controllers\partner\goods;

use app\models\merchant\app\AppAccessModel;
use app\models\merchant\picture\PictureGroupModel;
use app\models\merchant\picture\PictureModel;
use app\models\merchant\system\BargainModel;
use app\models\shop\AssembleRecordModel;
use app\models\shop\ShopAssembleModel;
use app\models\shop\ShopBargainInfoModel;
use app\models\spike\FlashSaleGroupModel;
use app\models\tuan\ConfigModel;
use yii;
use yii\db\Exception;
use app\models\shop\GoodsModel;
use app\models\shop\StockModel;
use app\models\core\UploadsModel;
use app\models\core\CosModel;
use app\models\core\Base64Model;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class GoodsController extends yii\web\PartnerController
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * 地址:/admin/group/index 默认访问
     * @throws Exception if the model cannot be found
     * @return array
     */

    public function actionList()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new GoodsModel();
            $params['partner_id'] = yii::$app->session['partner_id'];
            $params['merchant_id'] = yii::$app->session['m_id'];
            $params['`key`'] = yii::$app->session['key'];
            $params['delete_time'] = 1;
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $array = $model->findall($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * @param $id
     * @return array
     * @throws Exception
     */
    public function actionSingle($id)
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $category = new GoodsModel();
            $params['id'] = $id;
            $params['`key`'] = yii::$app->session['key'];
            $array = $category->findOne($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * @return array|false|string
     * @throws Exception
     */
    public function actionAdd()
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $model = new GoodsModel();

            //设置类目 参数
            $must = ['name','price', 'pic_urls', 'detail_info', 'stocks'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['`key`'] = yii::$app->session['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['m_id'];
            if (isset($params['start_time'])) {
                if ($params['start_time'] == "") {
                    $start_time = 0;
                } else {
                    $params['start_time'] = str_replace("+", " ", $params['start_time']);
                    $start_time = $params['start_time'] == "" ? time() : strtotime($params['start_time']);
                }
            } else {
                $start_time = 0;
            }
            if (isset($params['end_time'])) {
                if ($params['end_time'] == "") {
                    $end_time = 0;
                } else {
                    $params['end_time'] = str_replace("+", " ", $params['end_time']);
                    $end_time = $params['end_time'] == "" ? time() : strtotime($params['end_time']);
                }
            } else {
                $end_time = 0;
            }
            if (isset($params['take_goods_time'])) {
                if ($params['take_goods_time'] == "") {
                    $take_goods_time = 0;
                } else {
                    $params['take_goods_time'] = str_replace("+", " ", $params['take_goods_time']);
                    $take_goods_time = $params['take_goods_time'] == "" ? time() : strtotime($params['take_goods_time']);
                }
            } else {
                $take_goods_time = 0;
            }
            //计算拼团人数最低值
            $group_number = 0;
            if (isset($params['tuan_type']) && !empty($params['tuan_type']) && isset($params['assemble_number']) && !empty($params['assemble_number'])) {
                $group_number = max($params['assemble_number']);
            }
            $goodsData = array(
                '`key`' => $params['`key`'],
                'merchant_id' => yii::$app->session['m_id'],
                'name' => htmlentities($params['name']),
                'code' => htmlentities($params['code']),
                'price' => $params['price'],
                'line_price' => $params['line_price'],
                'pic_urls' => $params['pic_urls'],
                'stocks' => $params['stocks'],
                'm_category_id' => $params['m_category_id'],
                'city_group_id' => $params['city_group_id'] ?? 0,
                'unit' => isset($params['unit']) ? $params['unit'] : "",
                'sort' => $params['sort'],
                'type' => $params['type'],
                'start_time' => $start_time,
                'end_time' => $end_time,
                'take_goods_time' => $take_goods_time,
                'detail_info' => $params['detail_info'],
                'simple_info' => htmlentities($params['simple_info']),
                'label' => htmlentities($params['label']),
                'short_name' => htmlentities($params['short_name']),
                'band_self_leader_id' => isset($params['band_self_leader_id']) ? $params['band_self_leader_id'] : 0,
                'property1' => $params['property1'] == "" ? "默认:默认" : $params['property1'],
                'property2' => $params['property2'],
                'stock_type' => $params['stock_type'],
                'start_type' => $params['start_type'],
                'sales_number' => $params['sales_number'],
                'commission_leader_ratio' => isset($params['commission_leader_ratio']) ? $params['commission_leader_ratio'] : 0,
                'commission_selfleader_ratio' => isset($params['commission_selfleader_ratio']) ? $params['commission_selfleader_ratio'] : 0,
                'have_stock_type' => $params['have_stock_type'],
                'status' => $params['status'],
                'video_url' => isset($params['video_url']) ? $params['video_url'] : '',
                'video_pic_url' => isset($params['video_pic_url']) ? $params['video_pic_url'] : '',
                'video_id' => isset($params['video_id']) ? $params['video_id'] : '',
                'attribute' => isset($params['attribute']) ? json_encode($params['attribute'], JSON_UNESCAPED_UNICODE) : "",
                'video_status' => 0,
                'is_limit' => $params['is_limit'],
                'limit_number' => $params['limit_number'],
                'regimental_only' => $params['regimental_only'] ?? 0,
                'is_open_assemble' => $params['is_open_assemble'] ?? 0,
                'assemble_price' => $params['assemble_price'] ?? 0,
                'service_goods_is_ship' => $params['service_goods_is_ship'] ?? 0,
                'is_bargain' => $params['is_bargain'] ?? 0,
                'bargain_start_time' => isset($params['bargain_start_time']) ? strtotime($params['bargain_start_time']) : 0,
                'bargain_end_time' => isset($params['bargain_end_time']) ? strtotime($params['bargain_end_time']) : 0,
                'is_buy_alone' => $params['is_buy_alone'] ?? 0,
                'fictitious_initiate_bargain' => $params['fictitious_initiate_bargain'] ?? 0,
                'fictitious_help_bargain' => $params['fictitious_help_bargain'] ?? 0,
                'bargain_price' => $params['bargain_price'] ?? 0,
                'help_number' => $params['help_number'] ?? 0,
                'bargain_limit_time' => $params['bargain_limit_time'] ?? 0,
                'bargain_rule' => isset($params['bargain_rule']) ? json_encode($params['bargain_rule']) : '',
                'partner_id' => Yii::$app->session['partner_id'],
            );

            $transaction = yii::$app->db->beginTransaction();
            $array = $model->add($goodsData);
            if ($array['status'] != 200) {
                return $array;
            }
            $stockModel = new StockModel();
            $str = creat_mulu("./uploads/goods/" . $params['merchant_id']);
            $base = new Base64Model();
            try {
                //拼团开关记录
                if (isset($params['is_open_assemble']) && $params['is_open_assemble'] == 1) {
                    $assembleRecordModel = new AssembleRecordModel();
                    $assembleRecordData['key'] = $params['`key`'];
                    $assembleRecordData['merchant_id'] = yii::$app->session['m_id'];
                    $assembleRecordData['goods_id'] = $array['data'];
                    $assembleRecordData['name'] = htmlentities($params['name']);
                    $assembleRecordData['status'] = $params['is_open_assemble'];
                    $assembleRecordData['time'] = time();
                    $assembleRecordModel->do_add($assembleRecordData);
                }

                $pic_url = explode(",", $params['pic_urls']);
                if ($params['have_stock_type'] == 0) {
                    $data['`key`'] = $params['`key`'];
                    $data['merchant_id'] = yii::$app->session['m_id'];
                    $data['goods_id'] = $array['data'];
                    $data['name'] = htmlentities($params['name']);
                    $data['code'] = $params['code'];
                    $data['number'] = $params['stocks'];
                    $data['price'] = $params['price'];
                    $data['cost_price'] = $params['price'];
                    $data['property1_name'] = "默认";
                    $data['property2_name'] = "";
                    $data['pic_url'] = is_array($pic_url) ? $pic_url[0] : $params['pic_urls'];
                    $data['status'] = 1;
                    $stockModel->add($data);
                } else {
                    $num = count($params['stock']['code']);
                    for ($i = 0; $i < $num; $i++) {
                        $data['`key`'] = $params['`key`'];
                        $data['merchant_id'] = yii::$app->session['m_id'];
                        $data['goods_id'] = $array['data'];
                        $data['name'] = htmlentities($params['name']);
                        $data['code'] = $params['stock']['code'][$i];
                        $data['number'] = $params['stock']['number'][$i];
                        $data['price'] = $params['stock']['price'][$i];
                        $data['cost_price'] = $params['stock']['cost_price'][$i];
                        $data['property1_name'] = $params['stock']['property1_name'][$i];
                        $data['property2_name'] = $params['stock']['property2_name'][$i];
                        if (isset($params['stock']['pic_url'])) {
                            if ($params['stock']['pic_url'][$i] != "") {
                                $localRes = $base->base64_image_content($params['stock']['pic_url'][$i], $str);
                                if (!$localRes) {
                                    return result(500, "图片格式错误");
                                }
                                //将图片上传到cos
                                $cos = new CosModel();
                                $cosRes = $cos->putObject($localRes);
                                $url = "";
                                if ($cosRes['status'] == '200') {
                                    $url = $cosRes['data'];
                                } else {
                                    unlink(Yii::getAlias('@webroot/') . $localRes);
                                    return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
                                }
                                $data['pic_url'] = $url;
                            } else {
                                $data['pic_url'] = is_array($pic_url) ? $pic_url[0] : $params['pic_urls'];
                            }
                        } else {
                            $data['pic_url'] = is_array($pic_url) ? $pic_url[0] : $params['pic_urls'];
                        }
                        $data['status'] = 1;
                        $stockModel->add($data);
                    }
                }
                //添加拼团配置
                if (isset($params['tuan_type']) && !empty($params['tuan_type']) && isset($params['assemble_number']) && !empty($params['assemble_number'])) {
                    if (!isset($params['stock']['assemble_price'])) {
                        $params['stock']['assemble_price'] = [$params['assemble_price']];
                        if (empty($params['assemble_price'])) {
                            return result(500, "请设置拼团价格");
                        }
                    }
                    $new_group_arr = [];
                    $assemble_price = $params['stock']['assemble_price'];
                    foreach ($params['assemble_number'] as $ass_key => $ass_number) {
                        if (empty($ass_number)) {
                            return result(500, "平团人数错误");
                        }
                        foreach ($assemble_price as $price_key => $price_val) {
                            $new_group_arr[$ass_number][$price_key]['price'] = $price_val;
                            if ($params['is_leader_discount']) {
                                $new_group_arr[$ass_number][$price_key]['tuan_price'] = $params['assemble_group_discount'][$ass_key];
                            } else {
                                $new_group_arr[$ass_number][$price_key]['tuan_price'] = 0;
                            }
                            if (isset($params['group_price_discount']) && $params['group_price_discount']) {
                                if ($params['group_price_discount'][$ass_key] == 0) {
                                    return result(500, "拼团折扣率不能为0");
                                }
                                if ($params['group_price_discount'][$ass_key]) {
                                    $new_group_arr[$ass_number][$price_key]['price'] = bcmul($params['group_price_discount'][$ass_key] / 100, $price_val, 2);
                                }
                            }
                            if ($params['have_stock_type'] == 0) {
                                $new_group_arr[$ass_number][$price_key]['property1_name'] = "默认";
                                $new_group_arr[$ass_number][$price_key]['property2_name'] = "";
                            } else {
                                $new_group_arr[$ass_number][$price_key]['property1_name'] = $params['stock']['property1_name'][$price_key];
                                $new_group_arr[$ass_number][$price_key]['property2_name'] = $params['stock']['property2_name'][$price_key];
                            }
                        }
                    }
                    $group['goods_id'] = $array['data'];
                    $group['is_self'] = $params['is_self'] ?? 0;
                    $group['older_with_newer'] = $params['older_with_newer'] ?? 0;
                    $group['is_automatic'] = $params['is_automatic'] ?? 0;
                    $group['is_leader_discount'] = $params['is_leader_discount'] ?? 0;
                    $group['type'] = $params['tuan_type'];
                    $group['number'] = $group_number;
                    $group['property'] = json_encode($new_group_arr);
                    $group['min_price'] = $params['assemble_price'];
                    $group['is_show'] = $params['is_show'];
                    $group['merchant_id'] = yii::$app->session['m_id'];
                    $group['key'] = $params['`key`'];
                    $group['group_price_discount'] = isset($params['group_price_discount']) ? json_encode($params['group_price_discount']) : '';
                    $group['status'] = 1;
                    $groupModel = new ShopAssembleModel();
                    $groupModel->add($group);
                }

                //添加砍价活动开启记录
                if (isset($params['is_bargain']) && $params['is_bargain'] == '1') {
                    $bargainModel = new BargainModel();
                    $bargainData = array(
                        'key' => $params['`key`'],
                        'merchant_id' => yii::$app->session['m_id'],
                        'goods_id' => $array['data'],
                        'is_bargain' => $params['is_bargain'] ?? 0,
                        'bargain_start_time' => $params['bargain_start_time'] ? strtotime($params['bargain_start_time']) : 0,
                        'bargain_end_time' => $params['bargain_end_time'] ? strtotime($params['bargain_end_time']) : 0,
                        'is_buy_alone' => $params['is_buy_alone'] ?? 0,
                        'fictitious_initiate_bargain' => $params['fictitious_initiate_bargain'] ?? 0,
                        'fictitious_help_bargain' => $params['fictitious_help_bargain'] ?? 0,
                        'bargain_price' => $params['bargain_price'] ?? 0,
                        'help_number' => $params['help_number'] ?? 0,
                        'bargain_limit_time' => $params['bargain_limit_time'] ?? 0,
                        'bargain_rule' => $params['bargain_rule'] ? json_encode($params['bargain_rule']) : '',
                    );
                    $bargainModel->do_add($bargainData);
                }

                $transaction->commit(); //只有执行了commit(),对于上面数据库的操作才会真正执行
                if (isset($params['video_id']) && $params['video_id']) {
                    \Yii::$app->redis->sadd("goods_video_keys", $params['video_id']);
                }
                if ($end_time) {
                    \Yii::$app->redis->lpush(date('Y-m-d H:i', $end_time), $array['data']); //下架队列
                }
                return result(200, "新增成功");
            } catch (Exception $e) {
                $transaction->rollBack(); //回滚
                return result(500, "新增失败");
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * @param $id
     * @return array|false|string
     * @throws Exception
     */
    public function actionUpdate($id)
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new GoodsModel();
            $flashModel = new FlashSaleGroupModel();
            $goodsId[] = $id;
            $res = $flashModel->do_select(['in' => ['goods_ids', $goodsId], 'key' => yii::$app->session['key'], 'merchant_id' => yii::$app->session['m_id']]);
            if ($res['status'] == 200) {
                return result(500, "以有正在秒杀的商品，无法修改");
            }
            if ($res['status'] == 500) {
                return $res;
            }

            $bargainModel = new ShopBargainInfoModel();
            $bargain = $bargainModel->do_select(['goods_id' => $id, 'key' => yii::$app->session['key'], 'merchant_id' => yii::$app->session['m_id']]);
            if ($bargain['status'] == 200) {
                return result(500, "以有正在砍价的商品，无法修改");
            }
            if ($bargain['status'] == 500) {
                return $bargain;
            }

            $params['id'] = $id;
            $params['merchant_id'] = yii::$app->session['m_id'];
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $params['`key`'] = yii::$app->session['key'];
                unset($params['key']);
                $info = $model->find($params);
                if ($info['status'] != 200) {
                    return result(400, "数据错误");
                }
                $video_status = 0;
                $check_video_status = 0;
                if (isset($params['video_id'])) {
                    if ($info['data']['video_id'] && ($info['data']['video_id'] == $params['video_id'])) {
                        $video_status = $info['data']['video_status'];
                    } elseif ($params['video_id'] && $info['data']['video_id'] && ($info['data']['video_id'] != $params['video_id'])) {
                        //删除腾讯云原先的video_id
                        $credential = new Credential("AKIDVywpZUVuO0kX9dqdt00vgix1veVClUxG", "Owa5teVKzXDkPuSThlqpvHSTJnFX1RAC");
                        $vodClient = new VodClient($credential, "ap-guangzhou");
                        $DeleteMediaRequest = new DeleteMediaRequest();
                        $DeleteMediaRequest->deserialize(['FileId' => $info['data']['video_id']]);
                        $vodClient->DeleteMedia($DeleteMediaRequest);
                        $check_video_status = 1;
                    } elseif ($params['video_id'] && empty($info['data']['video_id'])) {
                        $check_video_status = 1;
                    }
                }
                $params['merchant_id'] = yii::$app->session['uid'];

                if (isset($params['start_time'])) {
                    if ($params['start_time'] == "") {
                        $start_time = 0;
                    } else {
                        $params['start_time'] = str_replace("+", " ", $params['start_time']);
                        $start_time = $params['start_time'] == "" ? time() : strtotime($params['start_time']);
                    }
                } else {
                    $start_time = 0;
                }
                if (isset($params['end_time'])) {
                    if ($params['end_time'] == "") {
                        $end_time = 0;
                    } else {
                        $params['end_time'] = str_replace("+", " ", $params['end_time']);
                        $end_time = $params['end_time'] == "" ? time() : strtotime($params['end_time']);
                    }
                } else {
                    $end_time = 0;
                }
                if (isset($params['take_goods_time'])) {
                    if ($params['take_goods_time'] == "") {
                        $take_goods_time = 0;
                    } else {
                        $params['take_goods_time'] = str_replace("+", " ", $params['take_goods_time']);
                        $take_goods_time = $params['take_goods_time'] == "" ? time() : strtotime($params['take_goods_time']);
                    }
                } else {
                    $take_goods_time = 0;
                }
                //计算拼团人数最大值最低值
                $group_number = 0;
                if (isset($params['tuan_type']) && !empty($params['tuan_type']) && isset($params['assemble_number']) && !empty($params['assemble_number'])) {
                    $group_number = max($params['assemble_number']);
                }
                $goodsData = array(
                    'id' => $params['id'],
                    '`key`' => yii::$app->session['key'],
                    'merchant_id' => yii::$app->session['m_id'],
                    'name' => $params['name'],
                    'code' => $params['code'],
                    'price' => $params['price'],
                    'line_price' => $params['line_price'],
                    'pic_urls' => $params['pic_urls'],
                    'stocks' => $params['stocks'],
                    'm_category_id' => $params['m_category_id'],
                    'city_group_id' => $params['city_group_id'] ?? 0,
                    'sort' => $params['sort'],
                    'type' => $params['type'],
                    'end_time' => $end_time,
                    'start_time' => $start_time,
                    'detail_info' => $params['detail_info'],
                    'simple_info' => $params['simple_info'],
                    'sales_number' => $params['sales_number'],
                    'label' => $params['label'],
                    'short_name' => $params['short_name'],
                    'take_goods_time' => $take_goods_time,
                    'property1' => $params['property1'] == "" ? "默认:默认" : $params['property1'],
                    'property2' => $params['property2'],
                    'stock_type' => $params['stock_type'],
                    'start_type' => $params['start_type'],
                    'unit' => $params['unit'],
                    'attribute' => isset($params['attribute']) ? json_encode($params['attribute'], JSON_UNESCAPED_UNICODE) : "",
                    'have_stock_type' => $params['have_stock_type'],
                    'band_self_leader_id' => isset($params['band_self_leader_id']) ? $params['band_self_leader_id'] : 0,
                    'commission_leader_ratio' => isset($params['commission_leader_ratio']) ? $params['commission_leader_ratio'] : 0,
                    'commission_selfleader_ratio' => isset($params['commission_selfleader_ratio']) ? $params['commission_selfleader_ratio'] : 0,
                    'status' => $params['status'],
                    'video_url' => isset($params['video_url']) ? $params['video_url'] : '',
                    'video_pic_url' => isset($params['video_pic_url']) ? $params['video_pic_url'] : '',
                    'video_id' => isset($params['video_id']) ? $params['video_id'] : '',
                    'video_status' => $video_status,
                    'is_limit' => $params['is_limit'],
                    'limit_number' => $params['limit_number'],
                    'regimental_only' => $params['regimental_only'] ?? 0,
                    'is_open_assemble' => $params['is_open_assemble'] ?? 0,
                    'assemble_price' => $params['assemble_price'] ?? 0,
                    'service_goods_is_ship' => $params['service_goods_is_ship'] ?? 0,
                    'is_bargain' => $params['is_bargain'] ?? 0,
                    'bargain_start_time' => isset($params['bargain_start_time']) ? strtotime($params['bargain_start_time']) : 0,
                    'bargain_end_time' => isset($params['bargain_end_time']) ? strtotime($params['bargain_end_time']) : 0,
                    'is_buy_alone' => $params['is_buy_alone'] ?? 0,
                    'fictitious_initiate_bargain' => $params['fictitious_initiate_bargain'] ?? 0,
                    'fictitious_help_bargain' => $params['fictitious_help_bargain'] ?? 0,
                    'bargain_price' => $params['bargain_price'] ?? 0,
                    'help_number' => $params['help_number'] ?? 0,
                    'bargain_limit_time' => $params['bargain_limit_time'] ?? 0,
                    'bargain_rule' => isset($params['bargain_rule']) ? json_encode($params['bargain_rule']) : '',
                    'partner_id' => yii::$app->session['partner_id']
                );
                $array = $model->update($goodsData);

                $stockModel = new StockModel();
                $delData['goods_id'] = $params['id'];
                $stockModel->delete($delData);
                $str = creat_mulu("./uploads/goods/" . $params['merchant_id']);
                $base = new Base64Model();
                $transaction = yii::$app->db->beginTransaction();
                try {
                    //拼团开关记录
                    if (isset($params['is_open_assemble'])) {
                        $assembleRecordModel = new AssembleRecordModel();
                        $assembleRecordWhere['key'] = yii::$app->session['key'];
                        $assembleRecordWhere['merchant_id'] = yii::$app->session['m_id'];
                        $assembleRecordWhere['goods_id'] = $id;
                        $assembleRecordWhere['orderby'] = "id desc";
                        $assembleRecordInfo = $assembleRecordModel->do_one($assembleRecordWhere);
                        if (($assembleRecordInfo['status'] != 200 && $params['is_open_assemble'] == 1) || (isset($assembleRecordInfo['data']) && $assembleRecordInfo['data']['status'] != $params['is_open_assemble'])) {
                            $assembleRecordData['key'] = yii::$app->session['key'];
                            $assembleRecordData['merchant_id'] = yii::$app->session['m_id'];
                            $assembleRecordData['goods_id'] = $id;
                            $assembleRecordData['name'] = $params['name'];
                            $assembleRecordData['status'] = $params['is_open_assemble'];
                            $assembleRecordData['time'] = time();
                            $assembleRecordModel->do_add($assembleRecordData);
                        }
                    }

                    if ($params['have_stock_type'] == 0) {
                        $pic_url = explode(",", $params['pic_urls']);
                        $data['`key`'] = yii::$app->session['key'];
                        $data['merchant_id'] = yii::$app->session['m_id'];
                        $data['goods_id'] = $params['id'];
                        $data['name'] = $params['name'];
                        $data['code'] = $params['code'];
                        $data['number'] = $params['stocks'];
                        $data['price'] = $params['price'];
                        $data['cost_price'] = $params['price'];
                        $data['property1_name'] = "默认";
                        $data['property2_name'] = "";
                        $data['pic_url'] = is_array($pic_url) ? $pic_url[0] : $params['pic_urls'];
                        $data['status'] = 1;
                        $stockModel->add($data);
                    } else {
                        $num = count($params['stock']['code']);
                        for ($i = 0; $i < $num; $i++) {
                            $data['`key`'] = yii::$app->session['key'];
                            $data['merchant_id'] = yii::$app->session['m_id'];
                            $data['goods_id'] = $params['id'];
                            $data['name'] = $params['name'];
                            $data['code'] = $params['stock']['code'][$i];
                            $data['number'] = $params['stock']['number'][$i];
                            $data['price'] = $params['stock']['price'][$i];
                            $data['cost_price'] = $params['stock']['cost_price'][$i];
                            $data['property1_name'] = $params['stock']['property1_name'][$i];
                            $data['property2_name'] = $params['stock']['property2_name'][$i];
                            if (strpos($params['stock']['pic_url'][$i], 'https://imgs.juanpao.com') !== false) {
                                $data['pic_url'] = $params['stock']['pic_url'][$i];
                            } else {
                                $localRes = $base->base64_image_content($params['stock']['pic_url'][$i], $str);
                                if (!$localRes) {
                                    return result(500, "图片格式错误");
                                }
                                //将图片上传到cos
                                $cos = new CosModel();
                                $cosRes = $cos->putObject($localRes);
                                $url = "";
                                if ($cosRes['status'] == '200') {
                                    $url = $cosRes['data'];
                                } else {
                                    unlink(Yii::getAlias('@webroot/') . $localRes);
                                    return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
                                }

                                $data['pic_url'] = $url;
                            }
                            $data['status'] = 1;
                            $stockModel->add($data);
                        }
                    }
                    //添加拼团配置
                    if (isset($params['tuan_type']) && !empty($params['tuan_type']) && isset($params['assemble_number']) && !empty($params['assemble_number'])) {
                        //查询是否开启过拼团配置没有新增有更新
                        if (!isset($params['stock']['assemble_price'])) {
                            $params['stock']['assemble_price'] = [$params['assemble_price']];
                            if (empty($params['assemble_price'])) {
                                return result(500, "请设置拼团价格");
                            }
                        }
                        $groupModel = new ShopAssembleModel();
                        $groupInfo = $groupModel->one(['goods_id' => $id, 'key' => yii::$app->session['key']]);
                        $new_group_arr = [];
                        $assemble_price = $params['stock']['assemble_price'];
                        foreach ($params['assemble_number'] as $ass_key => $ass_number) {
                            if (empty($ass_number)) {
                                return result(500, "平团人数错误");
                            }
                            foreach ($assemble_price as $price_key => $price_val) {
                                $new_group_arr[$ass_number][$price_key]['price'] = $price_val;
                                if ($params['is_leader_discount']) {
                                    $new_group_arr[$ass_number][$price_key]['tuan_price'] = $params['assemble_group_discount'][$ass_key];
                                } else {
                                    $new_group_arr[$ass_number][$price_key]['tuan_price'] = 0;
                                }
                                if (isset($params['group_price_discount']) && $params['group_price_discount']) {
                                    $new_group_arr[$ass_number][$price_key]['price'] = bcmul($params['group_price_discount'][$ass_key] / 100, $price_val, 2);
                                }
                                if ($params['have_stock_type'] == 0) {
                                    $new_group_arr[$ass_number][$price_key]['property1_name'] = "默认";
                                    $new_group_arr[$ass_number][$price_key]['property2_name'] = "";
                                } else {
                                    $new_group_arr[$ass_number][$price_key]['property1_name'] = $params['stock']['property1_name'][$price_key];
                                    $new_group_arr[$ass_number][$price_key]['property2_name'] = $params['stock']['property2_name'][$price_key];
                                }
                            }
                        }
                        $group['goods_id'] = $id;
                        $group['is_self'] = $params['is_self'] ?? 0;
                        $group['older_with_newer'] = $params['older_with_newer'] ?? 0;
                        $group['is_automatic'] = $params['is_automatic'] ?? 0;
                        $group['is_leader_discount'] = $params['is_leader_discount'] ?? 0;
                        $group['type'] = $params['tuan_type'];
                        $group['number'] = $group_number;
                        $group['property'] = json_encode($new_group_arr);
                        $group['min_price'] = $params['assemble_price'];
                        $group['is_show'] = $params['is_show'];
                        $group['group_price_discount'] = isset($params['group_price_discount']) ? json_encode($params['group_price_discount']) : '';
                        $group['merchant_id'] = yii::$app->session['m_id'];
                        $group['key'] = yii::$app->session['key'];
                        $group['status'] = 1;
                        if ($groupInfo['status'] == 200) {
                            $groupModel->do_update(['id' => $groupInfo['data']['id']], $group);
                        } else {
                            $groupModel->add($group);
                        }
                    }

                    //添加砍价活动开启记录
                    if (isset($params['is_bargain']) && $params['is_bargain'] == '1') {
                        $bargainModel = new BargainModel();
                        $bargainData = array(
                            'key' => yii::$app->session['key'],
                            'merchant_id' => yii::$app->session['m_id'],
                            'goods_id' => $params['id'],
                            'is_bargain' => $params['is_bargain'] ?? 0,
                            'bargain_start_time' => $params['bargain_start_time'] ? strtotime($params['bargain_start_time']) : 0,
                            'bargain_end_time' => $params['bargain_end_time'] ? strtotime($params['bargain_end_time']) : 0,
                            'is_buy_alone' => $params['is_buy_alone'] ?? 0,
                            'fictitious_initiate_bargain' => $params['fictitious_initiate_bargain'] ?? 0,
                            'fictitious_help_bargain' => $params['fictitious_help_bargain'] ?? 0,
                            'bargain_price' => $params['bargain_price'] ?? 0,
                            'help_number' => $params['help_number'] ?? 0,
                            'bargain_limit_time' => $params['bargain_limit_time'] ?? 0,
                            'bargain_rule' => $params['bargain_rule'] ? json_encode($params['bargain_rule']) : '',
                        );
                        $bargainModel->do_add($bargainData);
                    }

                    $transaction->commit(); //只有执行了commit(),对于上面数据库的操作才会真正执行
                    //存redis队列中
                    if (isset($params['video_id']) && $params['video_id'] && $check_video_status) {
                        \Yii::$app->redis->sadd("goods_video_keys", $params['video_id']);
                    }
                    if ($end_time) {
                        \Yii::$app->redis->lpush(date('Y-m-d H:i', $end_time), $id); //下架队列
                    }
                    return result(200, "更新成功");
                } catch (Exception $e) {
                    $transaction->rollBack(); //回滚
                    return result(500, "更新失败");
                }
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * @param $id
     * @return array
     * @throws Exception
     */
    public function actionUpdates($id)
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new GoodsModel();
            $params['id'] = $id;
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $params['`key`'] = yii::$app->session['key'];
                $params['merchant_id'] = yii::$app->session['m_id'];
                $params['partner_id'] = yii::$app->session['partner_id'];
                $array = $model->update($params);
                return $array;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * @param $id
     * @return array
     * @throws Exception
     */
    public function actionDelete($id)
    {
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new GoodsModel();
            $params['id'] = $id;
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['m_id'];
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $array = $model->delete($params);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * @return array|false|string
     */
    public function actionUploads()
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            //设置类目 参数
            $upload = new UploadsModel('file', "./uploads/goods");
            $str = $upload->upload();
            if (!$str) {
                return "上传文件错误";
            }
            $imgModel = new \app\models\core\ImageModel($str, 750);
            $imgModel->compressImg($str);

            // 将图片上传到cos
            $cos = new CosModel();
            $cosRes = $cos->putObject($str);
            if ($cosRes['status'] == '200') {
                $url = $cosRes['data'];
                unlink(Yii::getAlias('@webroot/') . $str);
            } else {
                unlink(Yii::getAlias('@webroot/') . $str);
                return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
            }
            $data['code'] = 200;
            $data['msg'] = "上传成功！";
            $data['data']['src'] = $url;
            return json_encode($data);
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * @return array|false|string
     */
    public function actionUploadsInfo()
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            //设置类目 参数
            $base64 = new Base64Model();

            $str = $base64->base64_image_content($params['pic'], "./uploads/{$params['type']}");
            if (!$str) {
                return "上传文件错误";
            }
            //将图片上传到cos
            $cos = new CosModel();
            $cosRes = $cos->putObject($str);
            if ($cosRes['status'] == '200') {
                $url = $cosRes['data'];
                unlink(Yii::getAlias('@webroot/') . $str);
            } else {
                unlink(Yii::getAlias('@webroot/') . $str);
                return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
            }
            return $url;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 已删除的商品
     */
    public function actionRecycle()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new GoodsModel();
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['m_id'];
            $params['partner_id'] = yii::$app->session['partner_id'];
            $params['delete_time'] = 2;
            $array = $model->findall($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 恢复商品
     * @param $id
     * @return array
     */
    public function actionReduction($id)
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new GoodsModel();
            $data['id'] = $id;
            if (!isset($data['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $data['`key`'] = yii::$app->session['key'];
                $data['merchant_id'] = yii::$app->session['m_id'];
                $data['partner_id'] = yii::$app->session['partner_id'];
                $data['status'] = 0;
                $array = $model->updates($data);
                return $array;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * @return array
     */
    public function actionInfo()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new \app\models\merchant\system\UserModel();
            $params['id'] = yii::$app->session['partner_id'];
            $array = $model->one($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 商品图片上传专用
     * @return array|false|string
     */
    public function actionUploadsPicture()
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $must = ['pic_url', 'name', 'width', 'height']; // 校验图片必传字段
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $base = new Base64Model();
            $cos = new CosModel();
            $pictureGroupModel = new PictureGroupModel();
            if (!isset($params['picture_group_id']) || empty($params['picture_group_id'])) {
                $params['picture_group_id'] = 0;
            }
            if ($params['picture_group_id'] != 0) { // 检测是否存在此分类
                $info = $pictureGroupModel->one(['id' => $params['picture_group_id']]);
                if ($info['status'] != 200) {
                    $params['picture_group_id'] = 0;
                }
            }
            $res_list = [];
            $merchant_id = yii::$app->session['m_id'];
            $params['key'] = yii::$app->session['key'];
            foreach ($params['pic_url'] as $key => $pic_url_val) {
                $data_val['pic_url'] = $pic_url_val;
                $width = $params['width'][$key] ? $params['width'][$key] : 0;
                $name = $params['name'][$key] ? $params['name'][$key] : '';
                $height = $params['height'][$key] ? $params['height'][$key] : 0;
                $pictureModel = new PictureModel();
                $data_val['pic_url'] = $base->base64_image_content($data_val['pic_url'], "./uploads/merchant/shop/goods_picture/" . yii::$app->session['m_id'] . '/' . $params['key']);
                // 将图片上传到cos
                $cosRes = $cos->putObject($data_val['pic_url']);
                if ($cosRes['status'] == '200') {
                    $url = $cosRes['data'];
                    unlink(Yii::getAlias('@webroot/') . $data_val['pic_url']);
                } else {
                    unlink(Yii::getAlias('@webroot/') . $data_val['pic_url']);
                    return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
                }
                // 将图片存到 图片库中
                $data['md5'] = md5(rand(1000, 9999) . 'picture');
                $data['merchant_id'] = $merchant_id;
                unset($data['pic_url']);
                $data['pic_url'] = $url;
                $data['key'] = $params['key'];
                $data['width'] = $width;
                $data['height'] = $height;
                $data['name'] = $name;
                $data['picture_group_id'] = $params['picture_group_id'];
                $data['partner_id'] = yii::$app->session['partner_id'];
                $res = $pictureModel->add($data);
                if ($res['status'] == 200) {
                    $res_list[] = $url;
                }
            }
            $res_data['status'] = 200;
            $res_data['msg'] = "上传成功!";
            $res_data['data']['src'] = $res_list;
            return $res_data;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 删除图片
     * @param $id
     * @return array
     */
    public function actionDeletePicture($id)
    {
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $pictureModel = new PictureModel();
            $params['merchant_id'] = yii::$app->session['m_id'];
            $params['partner_id'] = yii::$app->session['partner_id'];
            $params['id'] = $id;
            return $pictureModel->do_delete($params);
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 是否开启团购配置
     * @return array
     * @throws Exception
     */
    public function actionOpenConfig() {
        if (yii::$app->request->isGet) {
            $model = new ConfigModel();
            $data['merchant_id'] = yii::$app->session['m_id'];
            $data['key'] = yii::$app->session['key'];
            $array = $model->do_one($data);
            if ($array['status'] == 200) {
                $appAccessModel = new AppAccessModel();
                $appAccessInfo = $appAccessModel->find(['`key`' => yii::$app->session['key'], 'merchant_id' => yii::$app->session['m_id']]);
                if ($appAccessInfo['status'] == 200){
                    $array['data']['is_open'] = $appAccessInfo['data']['group_buying'];
                } else {
                    return $appAccessInfo;
                }
                $array['data']['open_time'] = secToTime($array['data']['open_time']);
                $array['data']['close_time'] = secToTime($array['data']['close_time']);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }
}
