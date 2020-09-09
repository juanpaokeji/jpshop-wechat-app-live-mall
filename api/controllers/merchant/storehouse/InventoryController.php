<?php

namespace app\controllers\merchant\storehouse;

use app\models\merchant\storehouse\InventoryDetailModel;
use app\models\merchant\storehouse\InventoryModel;
use app\models\merchant\storehouse\StorehouseModel;
use app\models\shop\GoodsModel;
use app\models\shop\StockModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use yii;
use yii\web\MerchantController;

/**
 * 盘点
 * @author  wmy
 * Class InventoryController
 * @package app\controllers\merchant\storehouse
 */
class InventoryController extends MerchantController
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors()
    {
        return [
            'token' => [
                'class' => 'yii\filters\MerchantFilter', //调用过滤器
                'except' => ['export', 'export-detail','real-stock-export'], //指定控制器不应用到哪些动作
            ]
        ];
    }

    /**
     * 查询列表
     * @return array
     * @throws yii\db\Exception
     */
    public function actionList()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            if (isset($params['storehouse_name']) && !empty($params['storehouse_name'])) {
                $storehouseModel = new StorehouseModel();
                $storehouseInfo = $storehouseModel->do_one(['name' => $params['storehouse_name']]);
                if ($storehouseInfo['status'] == 200) {
                    $params['storehouse_id'] = $storehouseInfo['data']['id'];
                }
            }
            if (isset($params['begin_time']) && !empty($params['begin_time']) && isset($params['end_time']) && !empty($params['end_time'])) {
                $params['begin_time'] = strtotime($params['begin_time']);
                $params['end_time'] = strtotime($params['end_time']);
                $params['>='] = ['create_time', $params['begin_time']];
                $params['<='] = ['create_time', $params['end_time']];
            }
            unset($params['storehouse_name']);
            unset($params['begin_time']);
            unset($params['end_time']);
            $model = new InventoryModel();
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->do_select($params);
            if ($array['status'] == 200) {
                foreach ($array['data'] as &$val) {
                    $val['storehouse_name'] = '';
                    if ($val['storehouse_id']) {
                        $storehouseModel = new StorehouseModel();
                        $storehouseInfo = $storehouseModel->do_one(['id' => $val['storehouse_id']]);
                        if ($storehouseInfo['status'] == 200) {
                            $val['storehouse_name'] = $storehouseInfo['data']['name'];
                        }
                    }
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 查询单条
     * @param $id
     * @return array
     * @throws yii\db\Exception
     */
    public function actionOne($id)
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            if (!$params['id']) {
                return result(500, "缺少id");
            }
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $model = new InventoryModel();
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->one($params);
            if ($array['status'] == 200) {
                $array['data']['storehouse_name'] = '';
                if($array['data']['storehouse_id']){
                    $storehouseModel = new StorehouseModel();
                    $storehouseInfo = $storehouseModel->do_one(['id' => $array['data']['storehouse_id']]);
                    if($storehouseInfo['status'] == 200){
                        $array['data']['storehouse_name'] = $storehouseInfo['data']['name'];
                    }
                }
                $detailModel = new InventoryDetailModel();
                $detailList = $detailModel->do_select(['inventory_id' => $id]);
                $array['data']['detail'] = [];
                if ($detailList['status'] == 200) {
                    foreach ($detailList['data'] as &$val) {
                        $val['goods_name'] = '';
                        $val['property1_name'] = '';
                        $val['property2_name'] = '';
                        $val['code'] = '';
                        $val['pic_url'] = '';
                        $stockModel = new StockModel();
                        $stockInfo = $stockModel->find(['id' => $val['stock_id']]);
                        if ($stockInfo['status'] == 200) {
                            $val['goods_name'] = $stockInfo['data']['name'];
                            $val['property1_name'] = $stockInfo['data']['property1_name'];
                            $val['property2_name'] = $stockInfo['data']['property2_name'];
                            $val['code'] = $stockInfo['data']['code'];
                            $val['pic_url'] = $stockInfo['data']['pic_url'];
                        }
                    }
                    $array['data']['detail'] = $detailList['data'];
                    $array['data']['count'] = $detailList['count'];
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 新增
     * @return array
     * @throws yii\db\Exception
     */
    public function actionAdd()
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            //设置类目 参数
            $must = ['list', 'storehouse_id', 'key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['list'] = json_decode($params['list'], true);
            if (count($params['list']) < 1) {
                return result(500, "数据为空");
            }
            if (empty($params['storehouse_id'])) {
                return result(500, "仓库id为空");
            }
            $params['merchant_id'] = yii::$app->session['uid'];
            //添加一条盘点记录
            $code = 'PD-' . rand(100, 999) . time();
            $inventoryModel = new InventoryModel();
            $inventoryData['key'] = $params['key'];
            $inventoryData['merchant_id'] = $params['merchant_id'];
            $inventoryData['code'] = $code;
            $inventoryData['storehouse_id'] = $params['storehouse_id'];
            $inventoryData['new_number'] = 0;
            $inventoryData['old_number'] = 0;
            $inventoryData['operator'] = '';
            $transaction = Yii::$app->db->beginTransaction();
            $inventoryRes = $inventoryModel->add($inventoryData);
            if ($inventoryRes['status'] != 200) {
                $transaction->rollBack();
                return result(500, "请求失败");
            }
            $number = 0;
            $old_goods_number = 0;
            foreach ($params['list'] as $goods_val) {
                $tr = Yii::$app->db->beginTransaction();
                $goods_val['number'] = (int)$goods_val['number'];
                $stockModel = new StockModel();
                $stockInfo = $stockModel->find(['id' => $goods_val['stock_id']]);
                if ($stockInfo['status'] != 200) {
                    file_put_contents(Yii::getAlias('@webroot/') . '/inventory_error.text', date('Y-m-d H:i:s') . 'stock_id' . $goods_val['stock_id'] . PHP_EOL, FILE_APPEND);
                    continue;
                }
               /* $goodsModel = new GoodsModel();
                $goodsInfo = $goodsModel->findOne(['id' => $goods_val['goods_id']]);
                if ($goodsInfo['status'] != 200) {
                    file_put_contents(Yii::getAlias('@webroot/') . '/inventory_error.text', date('Y-m-d H:i:s') . 'stock_id' . $goods_val['stock_id'] . PHP_EOL, FILE_APPEND);
                    continue;
                }*/
                $stockData['number'] = $goods_val['number'];
                $stockData['id'] = $goods_val['stock_id'];
                $stockRes = $stockModel->update($stockData);
                if ($stockRes['status'] != 200) {
                    $tr->rollBack();
                    file_put_contents(Yii::getAlias('@webroot/') . '/inventory_error.text', date('Y-m-d H:i:s') . '盘点更新失败' . $goods_val['stock_id'] . PHP_EOL, FILE_APPEND);
                    continue;
                }
                //更新商品总库存
              /*  if ($goodsInfo['data']['stocks'] > 0 || $goodsInfo['data']['stocks'] < 0) {
                    $goodsData['stocks'] = bcadd($goods_val['number'], bcsub($goodsInfo['data']['stocks'], $stockInfo['data']['number']));
                } elseif ($goodsInfo['data']['stocks'] = 0) {
                    $goodsData['stocks'] = $goods_val['number'];
                }*/
              /*  $goodsData['id'] = $goods_val['goods_id'];
                $goodsRes = $goodsModel->update($goodsData);
                if ($goodsRes['status'] != 200) {
                    $tr->rollBack();
                    file_put_contents(Yii::getAlias('@webroot/') . '/inventory_error.text', date('Y-m-d H:i:s') . '商品更新失败' . $goods_val['stock_id'] . PHP_EOL, FILE_APPEND);
                    continue;
                }*/
                $number = bcadd($number, $goods_val['number']);
                $old_goods_number = bcadd($old_goods_number, $stockInfo['data']['storehouse_number']);
                $inventoryDetailModel = new InventoryDetailModel();
                $inventoryDetailData['key'] = $params['key'];
                $inventoryDetailData['merchant_id'] = $params['merchant_id'];
                $inventoryDetailData['inventory_code'] = $code;
                $inventoryDetailData['inventory_id'] = $inventoryRes['data'];
                $inventoryDetailData['goods_id'] = $goods_val['goods_id'];
                $inventoryDetailData['stock_id'] = $goods_val['stock_id'];
                $inventoryDetailData['storehouse_id'] = $params['storehouse_id'];
                $inventoryDetailData['old_number'] = $stockInfo['data']['storehouse_number'];
                $inventoryDetailData['new_number'] = $goods_val['number'];
                $inventoryDetailData['remark'] = $goods_val['remark'] ?? '';
                $inventoryDetailRes = $inventoryDetailModel->add($inventoryDetailData);
                if ($inventoryDetailRes['status'] != 200) {
                    $tr->rollBack();
                    file_put_contents(Yii::getAlias('@webroot/') . '/inventory_error.text', date('Y-m-d H:i:s') . '盘点详情插入失败' . $goods_val['stock_id'] . PHP_EOL, FILE_APPEND);
                    continue;
                }
                $tr->commit();
            }
            $array = $inventoryModel->do_update(['id' => $inventoryRes['data']], ['new_number' => $number, 'old_number' => $old_goods_number]);
            if ($array['status'] == 200) {
                $transaction->commit();
            }
            $transaction->rollBack();
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 盘点查询
     * @return array
     * @throws yii\db\Exception
     */
    public function actionSearchList()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            if (isset($params['storehouse_name']) && !empty($params['storehouse_name'])) {
                $storehouseModel = new StorehouseModel();
                $storehouseInfo = $storehouseModel->do_one(['name' => $params['storehouse_name']]);
                if ($storehouseInfo['status'] == 200) {
                    $params['storehouse_id'] = $storehouseInfo['data']['id'];
                }
            }
            if(isset($params['inventory_code']) && empty($params['inventory_code'])){
                unset($params['inventory_code']);
            }
            if (isset($params['begin_time']) && !empty($params['begin_time']) && isset($params['end_time']) && !empty($params['end_time'])) {
                $params['begin_time'] = strtotime($params['begin_time']);
                $params['end_time'] = strtotime($params['end_time']);
                $params['>='] = ['create_time', $params['begin_time']];
                $params['<='] = ['create_time', $params['end_time']];
            }
            unset($params['storehouse_name']);
            unset($params['begin_time']);
            unset($params['end_time']);
            $model = new InventoryDetailModel();
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->do_select($params);
            if ($array['status'] == 200) {
                foreach ($array['data'] as &$val) {
                    $val['goods_name'] = '';
                    $val['property1_name'] = '';
                    $val['property2_name'] = '';
                    $val['code'] = '';
                    $stockModel = new StockModel();
                    $stockInfo = $stockModel->find(['id' => $val['stock_id']]);
                    if ($stockInfo['status'] == 200) {
                        $val['goods_name'] = $stockInfo['data']['name'];
                        $val['property1_name'] = $stockInfo['data']['property1_name'];
                        $val['property2_name'] = $stockInfo['data']['property2_name'];
                        $val['code'] = $stockInfo['data']['code'];
                    }
                    $val['storehouse_name'] = '';
                    if ($val['storehouse_id']) {
                        $storehouseModel = new StorehouseModel();
                        $storehouseInfo = $storehouseModel->do_one(['id' => $val['storehouse_id']]);
                        if ($storehouseInfo['status'] == 200) {
                            $val['storehouse_name'] = $storehouseInfo['data']['name'];
                        }
                    }
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 盘点导出
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws yii\db\Exception
     */
    public function actionExport()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            if (isset($params['storehouse_name']) && !empty($params['storehouse_name'])) {
                $storehouseModel = new StorehouseModel();
                $storehouseInfo = $storehouseModel->do_one(['name' => $params['storehouse_name']]);
                if ($storehouseInfo['status'] == 200) {
                    $params['storehouse_id'] = $storehouseInfo['data']['id'];
                }
            }
            if(isset($params['inventory_code']) && empty($params['inventory_code'])){
                unset($params['inventory_code']);
            }
            if (isset($params['begin_time']) && !empty($params['begin_time']) && isset($params['end_time']) && !empty($params['end_time'])) {
                $params['begin_time'] = strtotime($params['begin_time']);
                $params['end_time'] = strtotime($params['end_time']);
                $params['>='] = ['create_time', $params['begin_time']];
                $params['<='] = ['create_time', $params['end_time']];
            }
            unset($params['storehouse_name']);
            unset($params['begin_time']);
            unset($params['end_time']);
            $params['limit'] = 10000;
            $model = new InventoryModel();
            $array = $model->do_select($params);
            if ($array['status'] != 200) {
                return result(500, "未查到数据");
            }
            $inputFileName = './uploads/inventory.xls';
            /** Load $inputFileName to a Spreadsheet Object  **/
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
            $worksheet = $spreadsheet->getActiveSheet();
            foreach ($array['data'] as $key => $val) {
                $val['storehouse_name'] = '';
                if ($val['storehouse_id']) {
                    $storehouseModel = new StorehouseModel();
                    $storehouseInfo = $storehouseModel->do_one(['id' => $val['storehouse_id']]);
                    if ($storehouseInfo['status'] == 200) {
                        $val['storehouse_name'] = $storehouseInfo['data']['name'];
                    }
                }
                $line = $key + 2;
                $styleArray = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ];
                $worksheet->getStyle('A' . $line)->applyFromArray($styleArray);
                $worksheet->getStyle('B' . $line)->applyFromArray($styleArray);
                $worksheet->getStyle('C' . $line)->applyFromArray($styleArray);
                $worksheet->getStyle('D' . $line)->applyFromArray($styleArray);
                $worksheet->getStyle('E' . $line)->applyFromArray($styleArray);
                $spreadsheet->setActiveSheetIndex(0)
                    ->setCellValue('A' . $line, $val['code'])
                    ->setCellValue('B' . $line, $val['storehouse_name'])
                    ->setCellValue('C' . $line, $val['old_number'])
                    ->setCellValue('D' . $line, $val['new_number'])
                    ->setCellValue('E' . $line, date('Y-m-d H:i:s', $val['create_time']));
            }
            $spreadsheet->getActiveSheet()->setTitle('盘点导出');
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="盘点导出.xls"');
            header('Cache-Control: max-age=0');
            header('Cache-Control: max-age=1');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
            header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
            header('Pragma: public'); // HTTP/1.0
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
            exit;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 盘点详情导出
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws yii\db\Exception
     */
    public function actionExportDetail()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $model = new InventoryModel();
            $inventoryInfo = $model->one($params);
            if ($inventoryInfo['status'] != 200) {
                return result(500, "未查到盘点数据");
            }
            $storehouseModel = new StorehouseModel();
            $storehouseInfo = $storehouseModel->do_one(['id' => $inventoryInfo['data']['storehouse_id']]);
            $inventoryInfo['data']['storehouse_name'] = '';
            if ($storehouseInfo['status'] == 200) {
                $inventoryInfo['data']['storehouse_name'] = $storehouseInfo['data']['name'];
            }
            $inputFileName = './uploads/inventory_detail.xls';
            /** Load $inputFileName to a Spreadsheet Object  **/
            $styleArray = [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ];
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
            $worksheet = $spreadsheet->getActiveSheet();
            $worksheet->getStyle('A2')->applyFromArray($styleArray);
            $worksheet->getStyle('B2')->applyFromArray($styleArray);
            $worksheet->getStyle('C2')->applyFromArray($styleArray);
            $worksheet->getStyle('D2')->applyFromArray($styleArray);
            $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A2', $inventoryInfo['data']['code'])
                ->setCellValue('B2', $inventoryInfo['data']['storehouse_name'])
                ->setCellValue('C2', $inventoryInfo['data']['new_number'])
                ->setCellValue('D2', date('Y-m-d H:i:s', $inventoryInfo['data']['create_time']));
            $inventoryDetailModel = new InventoryDetailModel();
            $inventoryDetail = $inventoryDetailModel->do_select(['inventory_id' => $params['id'], 'key' => $params['key'], 'limit' => 10000]);
            if ($inventoryDetail['status'] == 200) {
                foreach ($inventoryDetail['data'] as $key => $val) {
                    $val['goods_name'] = '';
                    $val['property1_name'] = '';
                    $val['property2_name'] = '';
                    $val['code'] = '';
                    $stockModel = new StockModel();
                    $stockInfo = $stockModel->find(['id' => $val['stock_id']]);
                    if ($stockInfo['status'] == 200) {
                        $val['goods_name'] = $stockInfo['data']['name'];
                        $val['property1_name'] = $stockInfo['data']['property1_name'];
                        $val['property2_name'] = $stockInfo['data']['property2_name'];
                        $val['code'] = $stockInfo['data']['code'];
                    }
                    $line = $key + 5;
                    $worksheet->getStyle('A' . $line)->applyFromArray($styleArray);
                    $worksheet->getStyle('B' . $line)->applyFromArray($styleArray);
                    $worksheet->getStyle('C' . $line)->applyFromArray($styleArray);
                    $worksheet->getStyle('D' . $line)->applyFromArray($styleArray);
                    $worksheet->getStyle('E' . $line)->applyFromArray($styleArray);
                    $worksheet->getStyle('F' . $line)->applyFromArray($styleArray);
                    $spreadsheet->setActiveSheetIndex(0)
                        ->setCellValue('A' . $line, $val['goods_name'])
                        ->setCellValue('B' . $line, $val['property1_name'] . ',' . $val['property2_name'])
                        ->setCellValue('C' . $line, $val['code'])
                        ->setCellValue('D' . $line, $val['old_number'])
                        ->setCellValue('E' . $line, $val['new_number'])
                        ->setCellValue('F' . $line, $val['remark']);
                }
            }
            $spreadsheet->getActiveSheet()->setTitle('盘点详情导出');
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="盘点详情导出.xls"');
            header('Cache-Control: max-age=0');
            header('Cache-Control: max-age=1');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
            header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
            header('Pragma: public'); // HTTP/1.0
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
            exit;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 现有库存
     * @return array
     * @throws yii\db\Exception
     */
    public function actionStock()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            if (isset($params['storehouse_name']) && !empty($params['storehouse_name'])) {
                $storehouseModel = new StorehouseModel();
                $storehouseInfo = $storehouseModel->do_one(['name' => $params['storehouse_name']]);
                if ($storehouseInfo['status'] == 200) {
                    $params['storehouse_id'] = $storehouseInfo['data']['id'];
                }
            }
            if (isset($params['goods_name']) && !empty($params['goods_name'])) {
                $params["name like '%{$params['goods_name']}%'"] = null;
            }
            $params['`key`'] = $params['key'];
            unset($params['storehouse_name']);
            unset($params['goods_name']);
            unset($params['key']);
            $model = new StockModel();
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->findall($params);
            if ($array['status'] == 200) {
                foreach ($array['data'] as &$val) {
                    $val['storehouse_name'] = '';
                    if ($val['storehouse_id']) {
                        $storehouseModel = new StorehouseModel();
                        $storehouseInfo = $storehouseModel->do_one(['id' => $val['storehouse_id']]);
                        if ($storehouseInfo['status'] == 200) {
                            $val['storehouse_name'] = $storehouseInfo['data']['name'];
                        }
                    }
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 现有库存导出
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws yii\db\Exception
     */
    public function actionRealStockExport()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            if (isset($params['storehouse_name']) && !empty($params['storehouse_name'])) {
                $storehouseModel = new StorehouseModel();
                $storehouseInfo = $storehouseModel->do_one(['name' => $params['storehouse_name']]);
                if ($storehouseInfo['status'] == 200) {
                    $params['storehouse_id'] = $storehouseInfo['data']['id'];
                }
            }
            if (isset($params['goods_name']) && !empty($params['goods_name'])) {
                $params["name like '%{$params['goods_name']}%'"] = null;
            }
            $params['`key`'] = $params['key'];
            unset($params['storehouse_name']);
            unset($params['goods_name']);
            unset($params['key']);
            $model = new StockModel();
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->findall($params);
            if ($array['status'] != 200) {
                return result(500, "未查到数据");
            }
            $inputFileName = './uploads/real_stock.xls';
            /** Load $inputFileName to a Spreadsheet Object  **/
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
            $worksheet = $spreadsheet->getActiveSheet();
            foreach ($array['data'] as $key => $val) {
                $val['storehouse_name'] = '';
                if ($val['storehouse_id']) {
                    $storehouseModel = new StorehouseModel();
                    $storehouseInfo = $storehouseModel->do_one(['id' => $val['storehouse_id']]);
                    if ($storehouseInfo['status'] == 200) {
                        $val['storehouse_name'] = $storehouseInfo['data']['name'];
                    }
                }
                $line = $key + 2;
                $styleArray = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ];
                $worksheet->getStyle('A' . $line)->applyFromArray($styleArray);
                $worksheet->getStyle('B' . $line)->applyFromArray($styleArray);
                $worksheet->getStyle('C' . $line)->applyFromArray($styleArray);
                $worksheet->getStyle('D' . $line)->applyFromArray($styleArray);
                $worksheet->getStyle('E' . $line)->applyFromArray($styleArray);
                $worksheet->getStyle('F' . $line)->applyFromArray($styleArray);
                $worksheet->getStyle('G' . $line)->applyFromArray($styleArray);
                $spreadsheet->setActiveSheetIndex(0)
                    ->setCellValue('A' . $line, $val['name'])
                    ->setCellValue('B' . $line, $val['code'])
                    ->setCellValue('C' . $line, $val['property1_name'].','.$val['property2_name'])
                    ->setCellValue('D' . $line, $val['storehouse_name'])
                    ->setCellValue('E' . $line, $val['incoming_number'])
                    ->setCellValue('F' . $line, $val['outbound_number'])
                    ->setCellValue('G' . $line, $val['storehouse_number']);
            }
            $spreadsheet->getActiveSheet()->setTitle('现有库存导出');
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="现有库存导出.xls"');
            header('Cache-Control: max-age=0');
            header('Cache-Control: max-age=1');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
            header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
            header('Pragma: public'); // HTTP/1.0
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
            exit;
        } else {
            return result(500, "请求方式错误");
        }
    }
}
