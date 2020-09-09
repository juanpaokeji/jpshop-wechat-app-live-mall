<?php

namespace app\controllers\prints;

use app\models\core\TableModel;
use app\models\shop\ContactModel;
use app\models\system\SystemMerchantMiniAccessModel;
use app\models\system\SystemMerchantMiniSubscribeTemplateAccessModel;
use app\models\system\SystemMerchantMiniSubscribeTemplateModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use yii;
use yii\db\Exception;
use yii\web\MerchantController;
use app\models\shop\OrderModel;
use app\models\shop\ElectronicsModel;
use app\models\shop\SystemExpressModel;

require_once yii::getAlias('@vendor/wxpay/Wechat.php');

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @return array
 * @throws Exception if the model cannot be found
 */
class OrderController extends MerchantController
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors() {
        return [
            'token' => [
                'class' => 'yii\filters\MerchantFilter', //调用过滤器
//                'only' => ['single'],//指定控制器应用到哪些动作
                'except' => ['order-excel','pdf'],//指定控制器不应用到哪些动作
            ]
        ];
    }

    public function actionPrints()
    {

        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $must = ['key', 'electronics_id'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return json_encode($rs, JSON_UNESCAPED_UNICODE);
            }

            if ($params['electronics_id'] == 0) {
                return result(500, "本地发货,无法打印电子面单");
            }

            $model = new OrderModel();
            $eModel = new ElectronicsModel();

            $eData = $eModel->do_one(['id' => $params['electronics_id']]);
            if ($eData['status'] != 200) {
                return result(500, "请求失败");
            }
            $expressModel = new SystemExpressModel();
            $express = $expressModel->find(['id' => $eData['data']['express_id']]);
            if (!is_array($params['order_sn'])) {
                return result(500, "请选择订单编号");
            }

            for ($i = 0; $i < count($params['order_sn']); $i++) {
                $arr = [];
                for ($j = 0; $j < count($params['order_sn'][$i]); $j++) {
                    if ($j == 0) {
                        $order = $model->find(['order_sn' => $params['order_sn'][$i][$j]]);
                        $eorder['ShipperCode'] = $express['data']['simple_name'];
                        if ($express['data']['simple_name'] == "ZJS") {
                            $eorder["LogisticCode"] = $params['LogisticCode'];
                        }
                        //物流公司信息
                        $eorder["ThrOrderCode"] = $params['order_sn'][$i][$j];
                        $eorder["OrderCode"] = date("Y-m-d H:i:s", time()) . rand(1000, 9999);
                        $eorder['IsReturnPrintTemplate'] = 1;
                        $eorder["PayType"] = 1;
                        $eorder["ExpType"] = 1;
                        if ($express['data']['simple_name'] != "SF") {
                            $eorder["CustomerName"] = $eData['data']['customer_name'];
                            $eorder["CustomerPwd"] = $eData['data']['customer_pwd'];
                            $eorder['MonthCode'] = $eData['data']['month_code'];
                            $eorder['SendSite'] = $eData['data']['dot_code'];
                            $eorder['SendStaff'] = $eData['data']['dot_name'];
                        }

                        //发件人信息
                        $sender["Name"] = $eData['data']['name'];
                        $sender["Mobile"] = $eData['data']['phone'];
                        $sender["ProvinceName"] = $eData['data']['province_name'];
                        $sender["CityName"] = $eData['data']['city_name'];
                        $sender["ExpAreaName"] = $eData['data']['area_name'];
                        $sender["Address"] = $eData['data']['addr'];
                        $sender["PostCode"] = $eData['data']['post_code'];

                        //收件人信息
                        $contactModel = new ContactModel();
                        $contactWhere['id'] = $order['data']['user_contact_id'];
                        $contactInfo = $contactModel->find($contactWhere);
                        if ($contactInfo['status'] != 200) {
                            return result(500, "订单_{$params['order_sn'][$i][$j]}用户信息有误");
                        }

                        $receiver["Name"] = $order['data']['name'];
                        $receiver["Mobile"] = $order['data']['phone'];
                        $receiver["ProvinceName"] = $contactInfo['data']['province'];
                        $receiver["CityName"] = $contactInfo['data']['city'];
                        $receiver["ExpAreaName"] = $contactInfo['data']['area'];
                        $receiver["Address"] = $contactInfo['data']['address'];
                        $receiver["PostCode"] = $contactInfo['data']['postcode'];

                        $commodityOne = [];
                        $commodityOne["GoodsName"] = $eData['data']['towing_goods'];
                        $commodity = [];
                        $commodity[] = $commodityOne;

                        $temp = electronics($eorder, $sender, $receiver, $commodity);
                        if ($temp['Success'] == false) {
                            return result(500, "订单_{$params['order_sn'][$i][$j]},{$temp['Reason']}");
                        }
                        $arr['PrintTemplate'] = $temp['PrintTemplate'];
                        $arr['express_number'] = $temp['Order']['LogisticCode'];
                        $arr['order_sn'] = $params['order_sn'][$i][$j];
                    }
                }
                $res[$i] = $arr;
            }

            return result(200, "请求成功", $res);
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 商城后台，订单管理，主订单列表
     * 地址:/admin/group/index 默认访问
     * @return array
     */
    public function actionList()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return json_encode($rs, JSON_UNESCAPED_UNICODE);
            }

            $model = new OrderModel();
            $params['shop_order_group.`key`'] = $params['key'];
            unset($params['key']);
            $params['shop_order_group.merchant_id'] = yii::$app->session['uid'];
//            if (yii::$app->session['sid'] != null) {
//                $params['shop_order_group.supplier_id'] = yii::$app->session['sid'];
//            }
            $array = $model->findAllPirnt($params);

            if ($params['status'] == 1 && $array['status'] == 200) {
                foreach ($array['data'] as $key => $val) {
                    $res[$val['user_id'] . $val['phone'] . $val['name'] . $val['address']][] = $val;
                }
                $array['data'] = [];
                $count = 0;
                foreach ($res as $k => $v) {
                    $array['data'][] = $v;
                    $count++;
                }
                $array['count'] = $count;
            }

            //查询结果中data数据有时会变为对象，重新拼装处理
            if ($params['status'] != 1 && $array['status'] == 200) {
                $re = [];
                foreach ($array['data'] as $key => $val) {
                    $re[] = $val;
                }
                $array['data'] = $re;
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

            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return json_encode($rs, JSON_UNESCAPED_UNICODE);
            }

            $model = new OrderModel();
            $params['shop_order_group.`key`'] = $params['key'];
            unset($params['key']);
            $params['shop_order_group.merchant_id'] = yii::$app->session['uid'];
            $params['shop_order_group.id'] = $id;
            unset($params['id']);
            $array = $model->findAll($params);
            if ($array['status'] == 200) {
                unset($array['count']);
                $array['data'] = $array['data'][0];
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSend()
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参


            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return json_encode($rs, JSON_UNESCAPED_UNICODE);
            }
            if (!isset($params['express_number'][0]) || empty($params['express_number'][0])) {
                return result(500, "快递单号不能为空");
            }
            if ($params['electronics_id'] == 0) {
                $express['data']['name'] = "本地配送";
                $eData['data']['express_id'] = 0;
            } else {
                $eModel = new ElectronicsModel();
                $eData = $eModel->do_one(['id' => $params['electronics_id']]);
                if ($eData['status'] != 200) {
                    return result(500, "未查询到电子面单信息");
                }
                $expressModel = new SystemExpressModel();
                $express = $expressModel->find(['id' => $eData['data']['express_id']]);
                if ($express['status'] != 200) {
                    return result(500, "未查询到快递公司信息");
                }
            }
            $model = new OrderModel();
            if (isset($params['key'])) {
                $data['`key`'] = $params['key'];
                unset($params['key']);
            }
            $data['merchant_id'] = yii::$app->session['uid'];
            $str = "";
            for ($i = 0; $i < count($params['order_sn']); $i++) {
                if ($i == 0) {
                    $str = $params['order_sn'][$i];
                } else {
                    $str = $str . "," . $params['order_sn'][$i];
                }
            }

            $res = $model->findList(["order_sn in ({$str})" => null, 'merchant_id' => $data['merchant_id']]);

            if ($res['status'] != 200) {
                return result(500, "请求失败");
            }
            $bool = true;

            for ($i = 0; $i < count($res['data']); $i++) {
                if ($res['data'][$i]['status'] != 1) {
                    $bool = false;
                    break;
                }
            }
            if ($bool == false) {
                return result(500, "请核对订单状态");
            }
//            if (count($params['order_sn']) != count($params['express_number'])) {
//                return result(500, "参数错误");
//            }
            for ($i = 0; $i < count($params['order_sn']); $i++) {
                $data['order_sn'] = $params['order_sn'][$i];
                if ($params['electronics_id'] == 0) {
                    $data['send_express_type'] = 1;  //实际发货方式
                } else {
                    $data['send_express_type'] = 0;  //实际发货方式
                }
                $data['express_number'] = $params['express_number'][0];
                $data['express_id'] = $eData['data']['express_id'];
                $data['status'] = 3;
                if ($params['is_tuan'][$i] == 1) {
                    $array = $model->updateSend($data, 2);
                } else {
                    $array = $model->updateSend($data);
                }
                //发货成功再处理微信消息
                if ($array['status'] == 200) {
                    $orderModel = new OrderModel;
                    $orderRs = $orderModel->find(['order_sn' => $params['order_sn'][$i]]);

                    $shopUserModel = new \app\models\shop\UserModel();
                    $shopUser = $shopUserModel->find(['id' => $orderRs['data']['user_id']]);

                    $tempModel = new \app\models\system\SystemMiniTemplateModel();
                    $minitemp = $tempModel->do_one(['id' => 29]);
                    //单号,金额,下单时间,物品名称,
                    $tempParams = array(
                        'keyword1' => $params['express_number'][0],
                        'keyword2' => date("Y-m-d h:i:sa", time()),
                        'keyword3' => $orderRs['data']['create_time'],
                        'keyword4' => $orderRs['data']['goodsname'],
                    );

                    $tempAccess = new SystemMerchantMiniAccessModel();
                    $taData = array(
                        'key' => $orderRs['data']['key'],
                        'merchant_id' => $orderRs['data']['merchant_id'],
                        'mini_open_id' => $shopUser['data']['mini_open_id'],
                        'template_id' => 29,
                        'number' => '0',
                        'template_params' => json_encode($tempParams),
                        'template_purpose' => 'order',
                        'page' => "/pages/orderItem/orderItem/orderItem?order_sn={$params['order_sn'][$i]}",
                        'status' => '-1',
                    );
                    $tempAccess->do_add($taData);

                    //订阅消息
                    $subscribeTempModel = new SystemMerchantMiniSubscribeTemplateModel();
                    $subscribeTempInfo = $subscribeTempModel->do_one(['template_purpose' => 'send_goods']);
                    if ($subscribeTempInfo['status'] == 200) {
                        if ($params['electronics_id'] == 0) {  //本地配送
                            $params['express_number'][0] = 0;
                        }
                        if (mb_strlen($orderRs['data']['goodsname'], 'utf-8') > 20) {
                            $goodsName = mb_substr($orderRs['data']['goodsname'], 0, 17, 'utf-8') . '...'; //商品名超过20个汉字截断
                        } else {
                            $goodsName = $orderRs['data']['goodsname'];
                        }
                        $accessParams = array(
                            'character_string1' => ['value' => $params['order_sn'][$i]],  //订单号
                            'thing2' => ['value' => $goodsName],  //商品名
                            'thing8' => ['value' => $express['data']['name']],    //快递公司
                            'character_string9' => ['value' => $params['express_number'][0]],   //快递单号
                        );
                        $subscribeTempAccessModel = new SystemMerchantMiniSubscribeTemplateAccessModel();
                        $subscribeTempAccessData = array(
                            'key' => $orderRs['data']['key'],
                            'merchant_id' => $orderRs['data']['merchant_id'],
                            'mini_open_id' => $shopUser['data']['mini_open_id'],
                            'template_id' => $subscribeTempInfo['data']['template_id'],
                            'number' => '0',
                            'template_params' => json_encode($accessParams, JSON_UNESCAPED_UNICODE),
                            'template_purpose' => 'send_goods',
                            'page' => "/pages/orderItem/orderItem/orderItem?order_sn={$params['order_sn'][$i]}",
                            'status' => '-1',
                        );
                        $subscribeTempAccessModel->do_add($subscribeTempAccessData);
                    }
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionOrderExcel()
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return json_encode($rs, JSON_UNESCAPED_UNICODE);
            }
            $model = new OrderModel();
            $where['page'] = $params['page'];
            $where['limit'] = $params['limit'];
            $where['status'] = $params['status'];
            $where['shop_order_group.`key`'] = $params['key'];
            unset($params['key']);
            $where['shop_order_group.merchant_id'] = yii::$app->session['uid'];
            if (yii::$app->session['sid'] != null) {
                $where['shop_order_group.supplier_id'] = yii::$app->session['sid'];
            }
            $array = $model->findAllPirnt($where);
            if ($array['status'] == 200) {
                $orderList = [];
                foreach ($params['order_sn_list'] as $key => $val) {
                    foreach ($array['data'] as $k => $v) {
                        if ($val == $v['order_sn']) {
                            if ($v['status'] == '0') {
                                $v['status'] = '待付款';
                            } elseif ($v['status'] == '1') {
                                $v['status'] = '待发货';
                            } elseif ($v['status'] == '2') {
                                $v['status'] = '已取消';
                            } elseif ($v['status'] == '3') {
                                $v['status'] = '已发货';
                            } elseif ($v['status'] == '4') {
                                $v['status'] = '已退款';
                            } elseif ($v['status'] == '5') {
                                $v['status'] = '退款中';
                            } elseif ($v['status'] == '6') {
                                $v['status'] = '待评价';
                            } elseif ($v['status'] == '7') {
                                $v['status'] = '已完成';
                            } elseif ($v['status'] == '8') {
                                $v['status'] = '已删除';
                            } elseif ($v['status'] == '9') {
                                $v['status'] = '一键退款';
                            } else {
                                $v['status'] = '类型错误';
                            }
                            if ($v['express_type'] == '0') {
                                $v['express_type'] = '快递';
                            } elseif ($v['express_type'] == '1') {
                                $v['express_type'] = '自提';
                            } elseif ($v['express_type'] == '2') {
                                $v['express_type'] = '团长配送';
                            } else {
                                $v['express_type'] = '类型错误';
                            }
                            $v['remark'] = $v['remark'] . '__' . $v['admin_remark'];
                            foreach ($v['order'] as $ok => $ov) {
                                $v['order'][$ok]['property_name'] = $ov['property1_name'] . ';' . $ov['property2_name'];
                            }
                            $orderList[] = $v;
                        }
                    }
                }

                //处理Excel
                $inputFileName = './uploads/prints.xls';
                /** Load $inputFileName to a Spreadsheet Object  **/
                $objPHPExcel = IOFactory::load($inputFileName);

                $title = '订单表格导出';
                $cellName = array(
                    ['order_sn', '订单号', '1', '20', 'CENTER'],  //0下标是下标索引 1是内容 2是是否合并单元格1为是  3列宽 4居中"LEFT", "CENTER", "RIGHT"
                    ['create_time', '下单时间', '1', '20', 'CENTER'],
                    ['user_id', '用户ID', '1', '0', 'CENTER'],
                    ['nickname', '昵称', '1', '10', 'CENTER'],
                    ['name', '姓名', '1', '10', 'CENTER'],
                    ['phone', '手机号', '1', '12', 'CENTER'],
                    ['express_type', '发货方式', '1', '0', 'CENTER'],
                    ['address', '收件地址', '1', '25', 'CENTER'],
                    ['status', '订单状态', '1', '0', 'CENTER'],
                    ['express_number', '快递单号', '1', '15', 'CENTER'],
                    ['goods_name', '商品', '', '20', 'CENTER'],
                    ['property_name', '规格', '', '12', 'CENTER'],
                    ['number', '商品数量', '', '0', 'CENTER'],
                    ['payment_money', '实付金额', '1', '0', 'CENTER'],
                    ['total_price', '订单总价', '1', '0', 'CENTER'],
                    ['express_price', '配送费', '1', '0', 'CENTER'],
                    ['leader_money', '团长佣金', '1', '0', 'CENTER'],
                    ['remark', '留言备注', '1', '30', 'CENTER'],
                    ['leader_uid', '团长ID', '1', '0', 'CENTER'],
                    ['leader_name', '团长姓名', '1', '10', 'CENTER'],
                    ['leader_phone', '团长电话', '1', '12', 'CENTER'],
                    ['leader_area', '团长小区', '1', '20', 'CENTER'],
                    ['leader_addr', '团长地址', '1', '25', 'CENTER'],
                    ['estimated_service_time', '预约送货时间', '1', '45', 'CENTER'],
                );

                //定义配置
                $topNumber = 2;//表头有几行占用
                $xlsTitle = iconv('utf-8', 'gb2312', $title);//文件名称
                $cellKey = array(
                    'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
                    'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
                    'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM',
                    'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ'
                );

                //处理表头标题
                $objPHPExcel->getActiveSheet()->mergeCells('A1:X1');//合并单元格（如果要拆分单元格是需要先合并再拆分的，否则程序会报错）
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', '订单信息');
                $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);//设置是否加粗
                $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(18);//设置文字大小
                $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER); //设置文字居左（HORIZONTAL_LEFT，默认值）中（HORIZONTAL_CENTER）右（HORIZONTAL_RIGHT）
                $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER); //垂直居中

                //写在处理的前面（了解表格基本知识，已测试）
//                $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);//所有单元格（行）默认高度
//                $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(20);//所有单元格（列）默认宽度
//                $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(30);//设置行高度
//                $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);//设置列宽度
//                $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(18);//设置文字大小
//                $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);//设置是否加粗
//                $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);// 设置文字颜色
//                $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//设置文字居左（HORIZONTAL_LEFT，默认值）中（HORIZONTAL_CENTER）右（HORIZONTAL_RIGHT）
//                $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
//                $objPHPExcel->getActiveSheet()->getStyle('A1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);//设置填充颜色
//                $objPHPExcel->getActiveSheet()->getStyle('A1')->getFill()->getStartColor()->setARGB('FF7F24');//设置填充颜色

                //处理表头
                foreach ($cellName as $k => $v) {
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellKey[$k] . $topNumber, $v[1]);//设置表头数据
//                    $objPHPExcel->getActiveSheet()->freezePane($cellKey[$k] . ($topNumber + 1));//冻结窗口
                    $objPHPExcel->getActiveSheet()->getStyle($cellKey[$k] . $topNumber)->getFont()->setBold(true);//设置是否加粗
                    $objPHPExcel->getActiveSheet()->getStyle($cellKey[$k] . $topNumber)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER); //设置文字居左（HORIZONTAL_LEFT，默认值）中（HORIZONTAL_CENTER）右（HORIZONTAL_RIGHT）
                    $objPHPExcel->getActiveSheet()->getStyle($cellKey[$k] . $topNumber)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);//垂直居中
                    if ($v[3] > 0) { //大于0表示需要设置宽度
                        $objPHPExcel->getActiveSheet()->getColumnDimension($cellKey[$k])->setWidth($v[3]);//设置列宽度
                    }
                }
                //处理数据
                $mergeLine = 0;
                foreach ($orderList as $k => $v) {
                    $line = $k + $topNumber + 1 + $mergeLine;
                    if (count($v['order']) > 1) {
                        $mergeLine += count($v['order']) - 1;
                    }
                    foreach ($cellName as $k1 => $v1) {
                        if (count($v['order']) > 1) {
                            if ($v1[2] == 1) { //这里表示合并单元格
                                $startLine = $line;
                                $endLine = $line + count($v['order']) - 1;
                                $objPHPExcel->getActiveSheet()->mergeCells($cellKey[$k1] . $startLine . ':' . $cellKey[$k1] . $endLine);
                                $objPHPExcel->getActiveSheet()->getStyle($cellKey[$k1] . $startLine)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                            }
                        }
                        $objPHPExcel->getActiveSheet()->getStyle($cellKey[$k1] . $line)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER); //设置文字居左（HORIZONTAL_LEFT，默认值）中（HORIZONTAL_CENTER）右（HORIZONTAL_RIGHT）
                        $goodsLine = 0;
                        if ($v1[0] == 'express_number') {
                            $objPHPExcel->getActiveSheet()->setCellValue($cellKey[$k1] . $line, $v['order'][0]['express_number']."\t");
                        } elseif ($v1[0] == 'goods_name') {
                            foreach ($v['order'] as $ok => $ov) {
                                $goodsLine = $line + $ok;
                                $objPHPExcel->getActiveSheet()->setCellValue($cellKey[$k1] . $goodsLine, $ov['name']."\t");
                                $objPHPExcel->getActiveSheet()->getStyle($cellKey[$k1] . $goodsLine)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER); //设置文字居左（HORIZONTAL_LEFT，默认值）中（HORIZONTAL_CENTER）右（HORIZONTAL_RIGHT）
                            }
                        } elseif ($v1[0] == 'property_name') {
                            foreach ($v['order'] as $ok => $ov) {
                                $goodsLine = $line + $ok;
                                $objPHPExcel->getActiveSheet()->setCellValue($cellKey[$k1] . $goodsLine, $ov['property_name']."\t");
                                $objPHPExcel->getActiveSheet()->getStyle($cellKey[$k1] . $goodsLine)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER); //设置文字居左（HORIZONTAL_LEFT，默认值）中（HORIZONTAL_CENTER）右（HORIZONTAL_RIGHT）
                            }
                        } elseif ($v1[0] == 'number') {
                            foreach ($v['order'] as $ok => $ov) {
                                $goodsLine = $line + $ok;
                                $objPHPExcel->getActiveSheet()->setCellValue($cellKey[$k1] . $goodsLine, $ov['number']."\t");
                                $objPHPExcel->getActiveSheet()->getStyle($cellKey[$k1] . $goodsLine)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER); //设置文字居左（HORIZONTAL_LEFT，默认值）中（HORIZONTAL_CENTER）右（HORIZONTAL_RIGHT）
                            }
                        } else {
                            $objPHPExcel->getActiveSheet()->setCellValue($cellKey[$k1] . $line, $v[$v1[0]]."\t");
                        }
                    }
                }
                //导出execl
                header('pragma:public');
                header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $xlsTitle . '.xls"');
                header("Content-Disposition:attachment;filename=$title.xls");//attachment新窗口打印inline本窗口打印
                $objWriter = IOFactory::createWriter($objPHPExcel, 'Xlsx');
//                $objWriter->save('php://output');
//                exit;

                ob_start();
                $objWriter->save("php://output");
                $xlsData = ob_get_contents();
                ob_end_clean();
                $res = "data:application/vnd.ms-excel;base64," . base64_encode($xlsData);
                return result(200, "请求成功",$res);

            } else {
                return result(500, "订单查询失败");
            }


        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionPdf()
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $must = ['key','order_sn_list'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return json_encode($rs, JSON_UNESCAPED_UNICODE);
            }

            if (isset($params['order_sn_list'])){
                $orderSn = "";
                for ($i=0;$i<count($params['order_sn_list']);$i++){
                    if ($i == 0){
                        $orderSn .= $params['order_sn_list'][$i];
                    }else{
                        $orderSn .= ",".$params['order_sn_list'][$i];
                    }
                }
            }
            $tableModel = new TableModel();
            //统计每个团长订单下商品数量
            $goodsSql = "SELECT sog.leader_uid,so.goods_id,so.name,so.property1_name,so.property2_name,so.stock_id,sum( so.number ) AS num 
                         FROM shop_order_group sog 
                         RIGHT JOIN shop_order so ON so.order_group_sn = sog.order_sn 
                         WHERE sog.order_sn IN ( {$orderSn} ) GROUP BY sog.leader_uid,so.stock_id";
            $goodsInfo = $tableModel->querySql($goodsSql);
            if (count($goodsInfo) <= 0){
                return result(500, "未查询到商品信息");
            }
            //所选订单中各团长信息
            $leaderSql = "SELECT sog.leader_uid,stl.realname,stl.phone,stl.area_name FROM shop_order_group sog 
                          LEFT JOIN shop_tuan_leader stl ON stl.uid = sog.leader_uid 
                          WHERE sog.order_sn IN ( {$orderSn} ) GROUP BY sog.leader_uid";
            $leaderInfo = $tableModel->querySql($leaderSql);
            if (count($leaderInfo) <= 0){
                return result(500, "未查询到团长信息");
            }
            //订单信息
            $orderSql = "SELECT order_sn,name,phone,address,remark,express_type,leader_uid FROM shop_order_group 
                         WHERE order_sn IN ( {$orderSn} )";
            $orderInfo = $tableModel->querySql($orderSql);
            if (count($orderInfo) <= 0){
                return result(500, "未查询到订单信息");
            }
            //订单商品信息
            $orderGoodsSql = "SELECT order_group_sn,name,stock_id,property1_name,property2_name,number FROM shop_order 
                              WHERE order_group_sn IN ( {$orderSn} )";
            $orderGoodsInfo = $tableModel->querySql($orderGoodsSql);
            if (count($orderGoodsInfo) <= 0){
                return result(500, "未查询到订单商品信息");
            }
            //拼装订单数据
            foreach ($orderInfo as $k=>$v){
                foreach ($orderGoodsInfo as $gk=>$gv){
                    if ($v['order_sn'] == $gv['order_group_sn']){
                        $orderInfo[$k]['goods'][] = $gv;
                    }
                }
            }
            //拼装数据
            foreach ($leaderInfo as $k=>$v){
                foreach ($goodsInfo as $gk=>$gv){
                    if ($v['leader_uid'] == $gv['leader_uid']){
                        $leaderInfo[$k]['statistics_goods'][] = $gv;
                    }
                }
                foreach ($orderInfo as $ok=>$ov){
                    if ($v['leader_uid'] == $ov['leader_uid']){
                        $leaderInfo[$k]['order'][] = $ov;
                    }
                }
            }
            return result(200, "请求成功",$leaderInfo);
        } else {
            return result(500, "请求方式错误");
        }
    }

}
