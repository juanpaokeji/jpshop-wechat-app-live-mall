<?php

namespace app\controllers\supplier\goods;

use app\models\merchant\picture\PictureGroupModel;
use app\models\merchant\picture\PictureModel;
use app\models\merchant\system\BargainModel;
use app\models\merchant\system\ShopGroupingModel;
use app\models\shop\AssembleRecordModel;
use app\models\shop\ShopAssembleModel;
use app\models\shop\ShopBargainInfoModel;
use app\models\spike\FlashSaleGroupModel;
use app\models\system\SystemPicServerModel;
use TencentCloud\Common\Credential;
use TencentCloud\Vod\V20180717\Models\ConfirmEventsRequest;
use TencentCloud\Vod\V20180717\Models\DeleteMediaRequest;
use TencentCloud\Vod\V20180717\Models\DescribeMediaInfosRequest;
use TencentCloud\Vod\V20180717\Models\PullEventsRequest;
use TencentCloud\Vod\V20180717\VodClient;
use Vod\Model\VodUploadRequest;
use Vod\VodUploadClient;
use yii;
use yii\db\Exception;
use yii\web\SupplierController;
use app\models\shop\GoodsModel;
use app\models\shop\StockModel;
use app\models\core\UploadsModel;
use app\models\core\CosModel;
use app\models\core\Base64Model;
use EasyWeChat\Factory;
use app\models\admin\system\SystemCosModel;
use app\models\merchant\system\OperationRecordModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @return array
 * @throws Exception if the model cannot be found
 */
class GoodsController extends SupplierController
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * 地址:/admin/group/index 默认访问
     * @return array
     * @throws Exception if the model cannot be found
     */
    public $config = [
        'app_id' => '',
        'secret' => '',
        'token' => '',
        'aes_key' => '',
    ];

    public function actionGoodsBool($id)
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get();
            $goodsId[] = $id;
            $time = time();

            $sql = "select * from shop_flash_sale_group where FIND_IN_SET ({$id},goods_ids) and start_time <={$time} and end_time >= {$time} and delete_time is null";
            $res = Yii::$app->db->createCommand($sql)->queryAll();
            if (count($res)!=0) {
                return result(500, "已有正在秒杀的商品，无法修改");
            }else {
                $sql = "update shop_goods set is_flash_sale = 0 where id = {$id}";
                Yii::$app->db->createCommand($sql)->execute();

            }
            $bargainModel = new ShopBargainInfoModel();
            $bargain = $bargainModel->do_select(['goods_id' => $id, 'key' => yii::$app->session['key'], 'merchant_id' => yii::$app->session['uid']]);
            if ($bargain['status'] == 200) {
                return result(500, "已有正在砍价的商品，无法修改");
            } else if ($bargain['status'] == 500) {
                return $bargain;
            } else {
                $sql = "update shop_goods set is_bargain = 0 where id = {$id}";
                Yii::$app->db->createCommand($sql)->execute();
            }
            return result(200, '请求成功');

        }
    }

    public function actionList()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new GoodsModel();

            $params['`key`'] = yii::$app->session['key'];
            $key = yii::$app->session['key'];
            unset(yii::$app->session['key']);
            $params['supplier_id'] = yii::$app->session['sid'];
            $params['delete_time'] = 1;
            if (isset($params['audit'])) {
                $params['join'] = " inner join system_sub_admin on system_sub_admin.id = supplier_id ";
                $params['fields'] = " shop_goods.*,(select sum(number) from shop_order where confirm_time != 0  and shop_order.goods_id = shop_goods.id) as number,system_sub_admin.username ";
                if ($params['audit'] == 0) {
                    $params['supplier_id!=0'] = null;
                    $params['is_check'] = 0;
                } else if ($params['audit'] == 1) {
                    $params['supplier_id!=0'] = null;
                    $params['is_check'] = 1;
                } else if ($params['audit'] == 2) {
                    $params['supplier_id!=0'] = null;
                    $params['is_check'] = 2;
                } else if ($params['audit'] == 3) {
                    $params['supplier_id!=0'] = null;
                }
                unset($params['audit']);
                $params['shop_goods.`key`'] = $params['`key`'];
                unset($params['`key`']);
                $params['shop_goods.supplier_id'] = yii::$app->session['sid'];

                unset($params['supplier_id']);
            }

            $array = $model->findall($params);
            if ($array['status'] == 200) {
                for ($i = 0; $i < count($array['data']); $i++) {
                    if ($array['data'][$i]['start_time'] > time()) {
                        $array['data'][$i]['is_sale'] = 1;
                    } else {
                        $array['data'][$i]['is_sale'] = 0;
                    }
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle($id)
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $category = new GoodsModel();

            $params['id'] = $id;
            $params['supplier_id'] = yii::$app->session['sid'];
            $array = $category->findOne($params);
            $array['data']['group_info'] = [];
            if ($array['status'] == 200) {
                $array['data']['attribute'] = json_decode($array['data']['attribute'], true);
                $array['data']['bargain_rule'] = json_decode($array['data']['bargain_rule'], true);
                $array['data']['format_bargain_start_time'] = date("Y-m-d H:i:s", $array['data']['bargain_start_time']);
                $array['data']['format_bargain_end_time'] = date("Y-m-d H:i:s", $array['data']['bargain_end_time']);
            }
            if ($array['status'] == 200 && $array['data']['is_open_assemble'] == 1) {
                //查询拼团配置信息
                $shopGroupModel = new ShopAssembleModel();
                $shopinfo = $shopGroupModel->one(['goods_id' => $id, 'key' => yii::$app->session['key']]);
                if ($shopinfo['status'] == 200) {
                    $array['data']['group_info'] = $shopinfo['data'];
                    $property = json_decode($shopinfo['data']['property'], true);
                    if ($shopinfo['data']['group_price_discount']) {
                        $array['data']['group_info']['group_price_discount'] = json_decode($shopinfo['data']['group_price_discount'], true);
                    }
                    if ($property) {
                        $i = 0;
                        foreach ($property as $number => $val) {
                            $array['data']['group_info']['assemble_number'][] = (string)$number;
                            $tmp = [];
                            foreach ($val as $key => $va) {
                                if ($i === 0) {
                                    if ($array['data']['group_info']['group_price_discount']) {
                                        if ($array['data']['group_info']['group_price_discount'][0] / 100 == 0) {
                                            $array['data']['group_info']['assemble_price'][$key] = 0;
                                        } else {
                                            $array['data']['group_info']['assemble_price'][$key] = bcdiv($va['price'], $array['data']['group_info']['group_price_discount'][0] / 100, 2);
                                        }

                                    } else {
                                        $array['data']['group_info']['assemble_price'][$key] = $va['price'];
                                    }
                                }
                                if (!array_key_exists($number, $tmp)) {
                                    $tmp[$number] = $number;
                                    $array['data']['group_info']['group_discount'][] = $va['tuan_price'];
                                }
                            }
                            $i++;
                        }
                    }
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd()
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            //	var_dump($params);die();
            $model = new GoodsModel();

            //设置类目 参数
            $must = ['name', 'price', 'pic_urls', 'detail_info', 'stocks'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['`key`'] = yii::$app->session['key'];
            unset($params['key']);
            $params['supplier_id'] = yii::$app->session['sid'];
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


            $goodsData = array(
                '`key`' => $params['`key`'],
                'merchant_id' => yii::$app->session['uid'],
                'supplier_id' => yii::$app->session['sid'],
                'name' => htmlentities($params['name']),
                'code' => htmlentities($params['code']),
                'price' => $params['price'],
                'line_price' => $params['line_price'],
                'pic_urls' => $params['pic_urls'],
                'stocks' => $params['stocks'],
                'category_id' => $params['category_id'],
                'm_category_id' => $params['m_category_id'],
                'storehouse_id' => $params['storehouse_id'] ?? 0,
                'city_group_id' => $params['city_group_id'],
                'unit' => isset($params['unit']) ? $params['unit'] : "",
                'sort' => $params['sort'],
                'type' => $params['type'],
                'start_time' => $start_time,
                'end_time' => $end_time,
                'take_goods_time' => $take_goods_time,
                'detail_info' => $params['detail_info'],
                'simple_info' => htmlentities($params['simple_info']),
                'label' => htmlentities($params['label']),
                'label_id' => isset($params['label_id']) ? $params['label_id'] : "",
                'short_name' => htmlentities($params['short_name']),
                'band_self_leader_id' => isset($params['band_self_leader_id']) ? $params['band_self_leader_id'] : 0,
                'property1' => $params['property1'] == "" ? "默认:默认" : $params['property1'],
                'property2' => $params['property2'],
                'stock_type' => $params['stock_type'],
                'start_type' => $params['start_type'],
                'sales_number' => $params['sales_number'],
                'commission_leader_ratio' => isset($params['commission_leader_ratio']) ? $params['commission_leader_ratio'] : 0,
                'commission_selfleader_ratio' => isset($params['commission_selfleader_ratio']) ? $params['commission_selfleader_ratio'] : 0,
                'distribution'=>isset($params['distribution']) ? $params['distribution'] : 0,
                'have_stock_type' => $params['have_stock_type'],
                //    'is_top' => $params['is_top'],
                'status' => $params['status'],
                'video_url' => isset($params['video_url']) ? $params['video_url'] : '',
                'video_pic_url' => isset($params['video_pic_url']) ? $params['video_pic_url'] : '',
                'video_id' => isset($params['video_id']) ? $params['video_id'] : '',
                'attribute' => isset($params['attribute']) ? json_encode($params['attribute'], JSON_UNESCAPED_UNICODE) : "",
                'video_status' => $params['video_url'] != "" ? 1 : 0,
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
                'partner_id' => $params['partner_id'] ?? 0,
                'grouping_id' => $params['grouping_id'] ?? 0,
                'weight' => isset($params['weight']) ? $params['weight'] : 0,
                'commission_is_open' => isset($params['commission_is_open']) ? $params['commission_is_open'] : 0,

            );

            $transaction = yii::$app->db->beginTransaction();
            $array = $model->add($goodsData);
            if ($array['status'] != 200) {
                return $array;
            }
            $stockModel = new StockModel();
            $str = creat_mulu("./uploads/goods/" . yii::$app->session['uid']);
            $base = new Base64Model();
            try {

                $pic_url = explode(",", $params['pic_urls']);
                if ($params['have_stock_type'] == 0) {
                    $data['`key`'] = $params['`key`'];
                    $data['merchant_id'] = yii::$app->session['uid'];
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
                    $data['storehouse_id'] =  $params['storehouse_id'] ?? 0;
                    $stockModel->add($data);
                } else {
                    $num = count($params['stock']['code']);
                    for ($i = 0; $i < $num; $i++) {
                        $data['`key`'] = $params['`key`'];
                        $data['merchant_id'] = yii::$app->session['uid'];
                        $data['goods_id'] = $array['data'];
                        $data['name'] = htmlentities($params['name']);
                        $data['code'] = $params['stock']['code'][$i];
                        $data['number'] = $params['stock']['number'][$i];
                        $data['weight'] = isset($params['stock']['weight'][$i]) ? $params['stock']['weight'][$i] : 0;
                        $data['price'] = $params['stock']['price'][$i];
                        $data['cost_price'] = $params['stock']['cost_price'][$i];
                        $data['property1_name'] = $params['stock']['property1_name'][$i];
                        $data['property2_name'] = $params['stock']['property2_name'][$i];
                        $data['storehouse_id'] =  $params['storehouse_id'] ?? 0;
                        if (isset($params['stock']['pic_url'])) {
                            if ($params['stock']['pic_url'][$i] != "") {
                                $data['pic_url'] = $params['stock']['pic_url'][$i];
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


                //添加砍价活动开启记录
                if (isset($params['is_bargain']) && $params['is_bargain'] == '1') {
                    $bargainModel = new BargainModel();
                    $bargainData = array(
                        'key' => $params['`key`'],
                        'merchant_id' => yii::$app->session['uid'],
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

                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['`key`'];
                $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                $operationRecordData['operation_type'] = '新增';
                $operationRecordData['operation_id'] = $array['data'];
                $operationRecordData['module_name'] = '商品列表';
                $operationRecordModel->do_add($operationRecordData);

                $transaction->commit(); //只有执行了commit(),对于上面数据库的操作才会真正执行
                if (isset($params['video_id']) && $params['video_id']) {
                    \Yii::$app->redis->sadd("goods_video_keys", $params['video_id']);
                }
                if ($end_time) {
                    \Yii::$app->redis->lpush(date('Y-m-d H:i', $end_time), $array['data']); //下架队列
                }
                return $array;
            } catch (Exception $e) {
                $transaction->rollBack(); //回滚
                return result(500, "新增失败");
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate($id)
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new GoodsModel();
            $params['`key`'] = yii::$app->session['key'];

            $flashModel = new FlashSaleGroupModel();
            $goodsId[] = $id;
            $res = $flashModel->do_select(['in' => ['goods_ids', $goodsId], 'key' => yii::$app->session['key'], 'merchant_id' => yii::$app->session['uid']]);
            if ($res['status'] == 200) {
                return result(500, "已有正在秒杀的商品，无法修改");
            }
            if ($res['status'] == 500) {
                return $res;
            }

            $bargainModel = new ShopBargainInfoModel();
            $bargain = $bargainModel->do_select(['goods_id' => $id, 'key' => yii::$app->session['key'], 'merchant_id' => yii::$app->session['uid']]);
            if ($bargain['status'] == 200) {
                return result(500, "已有正在砍价的商品，无法修改");
            }
            if ($bargain['status'] == 500) {
                return $bargain;
            }

            $params['id'] = $id;
            $params['supplier_id'] = yii::$app->session['sid'];
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $params['`key`'] = yii::$app->session['key'];
                unset($params['key']);
                $info = $model->find(['id'=>$params['id'],'merchant_id'=>yii::$app->session['uid'],'`key`'=>$params['`key`']]);
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
                        $videoModel = new SystemVideoModel();
                        $video = $videoModel->do_one([]);
                        $credential = new Credential($video['secretId'], $video['secretKey']);
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
                        $start_time = $params['start_time'] == "" ? time() : strtotime($params['start_time']);
                    }
                } else {
                    $start_time = 0;
                }
                if (isset($params['end_time'])) {
                    if ($params['end_time'] == "") {
                        $end_time = 0;
                    } else {
                        $end_time = $params['end_time'] == "" ? time() : strtotime($params['end_time']);
                    }
                } else {
                    $end_time = 0;
                }
                if (isset($params['take_goods_time'])) {
                    if ($params['take_goods_time'] == "") {
                        $take_goods_time = 0;
                    } else {
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
                    '`key`' => $params['`key`'],
                    'merchant_id' => yii::$app->session['uid'],
                    'supplier_id' => yii::$app->session['sid'],
                    'name' => $params['name'],
                    'code' => $params['code'],
                    'price' => $params['price'],
                    'line_price' => $params['line_price'],
                    'pic_urls' => $params['pic_urls'],
                    'stocks' => $params['stocks'],
                    'category_id' => $params['category_id'],
                    'm_category_id' => $params['m_category_id'],
                    'city_group_id' => $params['city_group_id'],
                    'storehouse_id' => $params['storehouse_id'] ?? 0,
                    'sort' => $params['sort'],
                    'type' => $params['type'],
                    'end_time' => $end_time,
                    'start_time' => $start_time,
                    'detail_info' => $params['detail_info'],
                    'simple_info' => $params['simple_info'],
                    'sales_number' => $params['sales_number'],
                    'label' => $params['label'],
                    'label_id' => isset($params['label_id']) ? $params['label_id'] : "",
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
                    'distribution'=>isset($params['distribution']) ? $params['distribution'] : 0,
                    //  'is_top' => $params['is_top'],
                    'status' => $params['status'],
                    'video_url' => isset($params['video_url']) ? $params['video_url'] : '',
                    'video_pic_url' => isset($params['video_pic_url']) ? $params['video_pic_url'] : '',
                    'video_id' => isset($params['video_id']) ? $params['video_id'] : '',
                    'video_status' => $params['video_url'] != "" ? 1 : 0,
                    'is_check' => 0,
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
                    'partner_id' => $params['partner_id'] ?? 0,
                    'grouping_id' => $params['grouping_id'] ?? 0,
                    'weight' => isset($params['weight']) ? $params['weight'] : 0,
                    'commission_is_open' => isset($params['commission_is_open']) ? $params['commission_is_open'] : 0,
                );

                $array = $model->update($goodsData);

                if ($array['status'] == 200) {
                    //添加操作记录
                    $operationRecordModel = new OperationRecordModel();
                    $operationRecordData['key'] = $params['`key`'];
                    $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                    $operationRecordData['operation_type'] = '更新';
                    $operationRecordData['operation_id'] = $id;
                    $operationRecordData['module_name'] = '商品列表';
                    $operationRecordModel->do_add($operationRecordData);
                }

                $stockModel = new StockModel();
                $delData['goods_id'] = $params['id'];
                $stockModel->del($delData);
                $str = creat_mulu("./uploads/goods/" . yii::$app->session['uid']);
                $base = new Base64Model();
                $transaction = yii::$app->db->beginTransaction();
                try {

                    $pic_url = explode(",", $params['pic_urls']);
                    if ($params['have_stock_type'] == 0) {

                        $data['`key`'] = $params['`key`'];
                        $data['merchant_id'] = yii::$app->session['uid'];
                        $data['goods_id'] = $params['id'];
                        $data['name'] = $params['name'];
                        $data['code'] = $params['code'];
                        $data['number'] = $params['stocks'];
                        $data['price'] = $params['price'];
                        $data['cost_price'] = $params['cost_price'];
                        $data['assemble_price'] = $params['assemble_price'];
                        $data['property1_name'] = "默认";
                        $data['property2_name'] = "";
                        $data['pic_url'] = is_array($pic_url) ? $pic_url[0] : $params['pic_urls'];
                        $data['status'] = 1;
                        $data['storehouse_id'] =  $params['storehouse_id'] ?? 0;
                        $stockModel->add($data);
                    } else {
                        //	var_dump($params['stock']);die();
                        $num = count($params['stock']['code']);
                        for ($i = 0; $i < $num; $i++) {
                            $data['`key`'] = $params['`key`'];
                            $data['merchant_id'] = yii::$app->session['uid'];
                            //$data['id'] = $params['stock']['id'];
                            $data['goods_id'] = $params['id'];
                            $data['name'] = $params['name'];
                            $data['code'] = $params['stock']['code'][$i];
                            $data['number'] = $params['stock']['number'][$i];
                            $data['weight'] = isset($params['stock']['weight'][$i]) ? $params['stock']['weight'][$i] : 0;
                            $data['price'] = $params['stock']['price'][$i];
                            $data['cost_price'] = $params['stock']['cost_price'][$i];
                            $data['assemble_price'] = $params['stock']['assemble_price'][$i];
                            $data['property1_name'] = $params['stock']['property1_name'][$i];
                            $data['property2_name'] = $params['stock']['property2_name'][$i];
                            $data['storehouse_id'] =  $params['storehouse_id'] ?? 0;
                            if (isset($params['stock']['pic_url'])) {
                                if ($params['stock']['pic_url'][$i] != "") {
                                    $data['pic_url'] = $params['stock']['pic_url'][$i];
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


                    //添加砍价活动开启记录
                    if (isset($params['is_bargain']) && $params['is_bargain'] == '1') {
                        $bargainModel = new BargainModel();
                        $bargainData = array(
                            'key' => $params['`key`'],
                            'merchant_id' => yii::$app->session['uid'],
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

    public function actionUpdates($id)
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new GoodsModel();

            $params['id'] = $id;
            $params['`key`'] = yii::$app->session['key'];
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                if (isset(yii::$app->session['key'])) {
                    $params['`key`'] = yii::$app->session['key'];
                    unset(yii::$app->session['key']);
                }
                $params['supplier_id'] = yii::$app->session['sid'];
                $array = $model->update($params);
                return $array;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAudit($id)
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new GoodsModel();

            $data['id'] = $id;
            if (!isset($data['id'])) {
                return result(400, "缺少参数 id");
            } else {
                if (isset(yii::$app->session['key'])) {
                    $data['`key`'] = yii::$app->session['key'];
                }
                $data['is_check'] = $params['is_check'];
                $data['status'] = $params['is_check'] == 1 ? 1 : 0;
                $data['supplier_id'] = yii::$app->session['sid'];
                $array = $model->update($data);
                if ($params['is_check'] == 1) {
                    $price = array();
                    $stockData = explode(",", $params['price_str']);
                    for ($i = 0; $i < count($stockData); $i++) {
                        $arr = explode(":", $stockData[$i]);
                        $price[$i]['id'] = $arr[0];
                        $price[$i]['price'] = $arr[1];
                    }
                    $stockModel = new StockModel();
                    for ($i = 0; $i < count($price); $i++) {
                        $stockModel->update(['id' => $price[$i]['id'], 'price' => $price[$i]['price']]);
                    }
                }
                return $array;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionDelete($id)
    {
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参


            $model = new GoodsModel();
            $params['id'] = $id;
            $params['`key`'] = yii::$app->session['key'];
            $params['supplier_id'] = yii::$app->session['sid'];
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
            $supplier_id = yii::$app->session['sid'];
            foreach ($params['pic_url'] as $key => $pic_url_val) {
                $data_val['pic_url'] = $pic_url_val;
                $width = $params['width'][$key] ? $params['width'][$key] : 0;
                $name = $params['name'][$key] ? $params['name'][$key] : '';
                $height = $params['height'][$key] ? $params['height'][$key] : 0;
                $pictureModel = new PictureModel();
                $data_val['pic_url'] = $base->base64_image_content($data_val['pic_url'], "./uploads/merchant/shop/goods_picture/" . yii::$app->session['sid'] . '/' . yii::$app->session['key']);
                // 将图片上传到cos
                $cosModel = new SystemPicServerModel();
                $where['status'] = 1; //服务器只会有一个开启，没有开启则使用本地
                $a  = $cosModel->do_one($where);
                if ($a['status'] == 200) {
                    $cosRes = $cos->putObject($data_val['pic_url']);
                    if ($cosRes['status'] == '200') {
                        $url = $cosRes['data'];
                        unlink(Yii::getAlias('@webroot/') . $data_val['pic_url']);
                    } else {
                        unlink(Yii::getAlias('@webroot/') . $data_val['pic_url']);
                        return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
                    }
                } else {
                    $data_val['pic_url'] = "http://" . $_SERVER['HTTP_HOST'] . "/api/web/" . $data_val['pic_url'];
                    $url = $data_val['pic_url'];
                }
                // 将图片存到 图片库中
                $data['md5'] = md5(rand(1000, 9999) . 'picture');
                $data['supplier_id'] = $supplier_id;
                list($newWidth, $newHeight) = getimagesize($url); //图片可能压缩过，获取最新宽高
                unset($data['pic_url']);
                $data['pic_url'] = $url;
                $data['key'] = yii::$app->session['key'];
                $data['width'] = $newWidth;
                $data['height'] = $newHeight;
                $data['name'] = $name;
                $data['picture_group_id'] = $params['picture_group_id'];
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
            $params['supplier_id'] = yii::$app->session['sid'];
            $params['id'] = $id;
            return $pictureModel->do_delete($params);
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUploadsinfo()
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
            unset(yii::$app->session['key']);
            $params['supplier_id'] = yii::$app->session['sid'];
            $params['delete_time'] = 2;
            $array = $model->findall($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 恢复商品
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
                $data['supplier_id'] = yii::$app->session['sid'];
                $data['status'] = 0;
                $array = $model->updates($data);
                return $array;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 上传视频
     * $params type 1是背景图 2 是视频
     * @return array
     */
    public function actionUploadVod()
    {
        $request = yii::$app->request; //获取 request 对象
        $params = $request->bodyParams; //获取body传参
        if (empty($_FILES)) {
            return result(500, "上传出错了");
        }
        if ($_FILES["file"]["size"] > 10485760) {
            return result(500, "上传的视频文件超过10M了");
        }
        $type = substr($_FILES["file"]["type"], 0, 5);
        if ($type != 'video') {
            return result(500, "上传格式出错了");
        }
        $upload = new UploadsModel('file', "./uploads/goods_video_url");
        $video_url = $upload->upload();
        if (!$video_url) {
            return result(500, "上传视频错误");
        }
        $videoModel = new SystemVideoModel();
        $video = $videoModel->do_one([]);
        if($video['status']==200){
            try {
                $client = new VodUploadClient($video['secretId'], $video['secretKey']);
                $req = new VodUploadRequest();
                if (!$video_url) {
                    return result(500, "上传文件路径错误");
                }
                $req->MediaFilePath = $video_url;
                $req->Procedure = "处理视频"; //指定任务流
                $rsp = $client->upload("ap-guangzhou", $req);
                if ($rsp->FileId) {
                    $credential = new Credential($video['secretId'], $video['secretKey']);
                    $vodClient = new VodClient($credential, "ap-guangzhou");
                    $DescribeMediaInfosRequest = new DescribeMediaInfosRequest();
                    $DescribeMediaInfosRequest->deserialize(['FileIds' => [$rsp->FileId], 'Filters' => ['basicInfo', 'metaData']]);
                    $applyUploadResponse = $vodClient->DescribeMediaInfos($DescribeMediaInfosRequest);
                    if ($applyUploadResponse) {
                        $Duration = $applyUploadResponse->MediaInfoSet[0]->MetaData->Duration;
                        if ($Duration > 30) { //太长了删除它
                            $DeleteMediaRequest = new DeleteMediaRequest();
                            $DeleteMediaRequest->deserialize(['FileId' => $rsp->FileId]);
                            $vodClient->DeleteMedia($DeleteMediaRequest);
                            return result(500, "视频太长了！");
                        }
                        $data['video_id'] = $rsp->FileId;
                        $data['video_url'] = $rsp->MediaUrl;
                        $data['video_pic_url'] = $rsp->CoverUrl;
                        return result(200, "上传成功", $data);
                    }
                    return result(500, "出错了");
                } else {
                    return result(500, "上传失败");
                }
            } catch (\Exception $e) {
                // 处理上传异常
                return result(500, $e->getMessage());
            }
        }else{
            $data['video_id'] = "";
            $data['video_url'] ="http://".$_SERVER['HTTP_HOST']."/api/web/".$video_url;
            $data['video_pic_url'] = "";
            return result(200, "上传成功", $data);
        }
        // $credential = new Credential($video['secretId'],$video['secretKey']);
        $client = new VodUploadClient($video['secretId'], $video['secretKey']);
        $req = new VodUploadRequest();
        if (!$video_url) {
            return result(500, "上传文件路径错误");
        }
        $req->MediaFilePath = $video_url;
        $req->Procedure = "处理视频"; //指定任务流

    }

    /**
     * 任务流处理完成 主动回调地址
     */
    public function actionVideoNotify()
    {
        $videoModel = new SystemVideoModel();
        $video = $videoModel->do_one(['id'=>1]);
        $credential = new Credential($video['secretId'],$video['secretKey']);
        $vodClient = new VodClient($credential, "ap-guangzhou");
        $PullEventsRequest = new PullEventsRequest();
        $applyUploadResponse = $vodClient->PullEvents($PullEventsRequest);
        if (empty($applyUploadResponse)) {
            return 'no_response';
        }
        $length = \Yii::$app->redis->scard("goods_video_keys");
        if ($length < 1) {
            if (!empty($applyUploadResponse)) {
                foreach ($applyUploadResponse->EventSet as $vals) {
                    $ConfirmEventsRequest = new ConfirmEventsRequest();
                    $ConfirmEventsRequest->deserialize(['EventHandles' => [$vals->EventHandle]]);
                    $vodClient->ConfirmEvents($ConfirmEventsRequest);
                    continue;
                }
            }
            return 'no_redis_data';
        }
        $model = new GoodsModel();
        try {
            foreach ($applyUploadResponse->EventSet as $val) {
                if (\Yii::$app->redis->sismember("goods_video_keys", $val->ProcedureStateChangeEvent->FileId)) {
                    $video_url = '';
                    $video_picture_url = '';
                    foreach ($val->ProcedureStateChangeEvent->MediaProcessResultSet as $media) {
                        if ($media->Type == 'Transcode') {
                            if ($media->TranscodeTask->Status == 'SUCCESS') {
                                $video_url = $media->TranscodeTask->Output->Url;
                            }
                        }
                        if ($media->Type == 'CoverBySnapshot') {
                            if ($media->CoverBySnapshotTask->Status == 'SUCCESS') {
                                $video_picture_url = $media->CoverBySnapshotTask->Output->CoverUrl;
                            }
                        }
                    }
                    $parmas['video_id'] = $val->ProcedureStateChangeEvent->FileId;
                    $goodsInfo = $model->findByVideoId($parmas);
                    if ($goodsInfo['status'] == 200) {
                        $where['video_id'] = $val->ProcedureStateChangeEvent->FileId;
                        $where['video_pic_url'] = $video_picture_url;
                        $where['video_url'] = $video_url;
                        $where['video_status'] = 1;
                        $where['id'] = $goodsInfo['data']['id'];
                        $res = $model->update($where);
                        if ($res['status'] == 200) {
                            //删除redis中的值
                            @\Yii::$app->redis->srem("goods_video_keys", $val->ProcedureStateChangeEvent->FileId);
                            $ConfirmEventsRequest = new ConfirmEventsRequest();
                            $ConfirmEventsRequest->deserialize(['EventHandles' => [$val->EventHandle]]);
                            $res = $vodClient->ConfirmEvents($ConfirmEventsRequest);
                            continue;
                        }
                    }
                }
                continue;
            }
            return 'success';
        } catch (\Exception $e) {
            file_put_contents(Yii::getAlias('@webroot/') . '/video_notify_error1.text', date('Y-m-d H:i:s') . $e->getMessage() . PHP_EOL, FILE_APPEND);
        }
    }

    public function actionQcode($id)
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $params['key'] = yii::$app->session['key'];
            $model = new GoodsModel();
            $params['`key`'] = $params['key'];
            $config = $this->getSystemConfig($params['key'], "miniprogram");

            $miniProgram = Factory::miniProgram($config);
            $response = $miniProgram->app_code->getUnlimit($id, ['width' => 280, "page" => $params['url']]);
            $url = "";
            if ($response instanceof \EasyWeChat\Kernel\Http\StreamResponse) {

                $filename = $response->saveAs(yii::getAlias('@webroot/') . "/uploads/qcode/" . date('Y') . "/" . date('m') . "/" . date('d') . "/", time() . $config['app_id'] . rand(1000, 9999) . ".png");
                $localRes = "./uploads/qcode/" . date('Y') . "/" . date('m') . "/" . date('d') . "/" . $filename;
                $cos = new CosModel();
                $cosRes = $cos->putObject($localRes);

                if ($cosRes['status'] == '200') {
                    $data['url'] = $cosRes['data'];
                    unlink(Yii::getAlias('@webroot/') . $localRes);
                } else {
                    $data['url'] = "http://".$_SERVER['HTTP_HOST']."/api/web/".$localRes;
                }
            }

            return result(200, '请求成功', $data);
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionStock(){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            //设置类目 参数
            $must = ['key','code'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $model = new SaleGoodsStockModel();
            $stock = $model->do_one(['code'=>$params['code'],'key'=>$params['key']]);

            return $stock;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 自动下架
     * @return bool
     * @throws Exception
     */
    public function actionAutoObtained()
    {
        $key = date('Y-m-d H:i', time());
        $length = \Yii::$app->redis->llen($key); //下架队列
        if ($length < 1) {
            return true;
        }
        $arr = \Yii::$app->redis->lrange($key, 0, $length); //下架队列
        $str = implode(',', $arr);
        //更新
        $sql = "UPDATE shop_goods set `status`=0 where id in({$str}) AND `status`=1";
        $str = yii::$app->db->createCommand($sql)->execute();
        if ($str) {
            \Yii::$app->redis->del($key);
        }
        return true;
    }

    public function actionGrouping(){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $model = new ShopGroupingModel();
            $params['field'] = "id,name";
            $params['key'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['uid'];
            $params['status'] = 1;
            $array = $model->do_select($params);
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
