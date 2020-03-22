<?php

namespace app\controllers\merchant\statistics;

use app\models\shop\OrderModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use yii;
use yii\web\MerchantController;

/**
 * 统计
 * @author  wmy
 * Class StatisticsController
 * @package app\controllers\merchant\statistics
 */
class StatisticsController extends MerchantController
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors()
    {
        return [
            'token' => [
                'class' => 'yii\filters\MerchantFilter', //调用过滤器
                'except' => ['sales', 'sales-export', 'goods-sales', 'goods-sales-export', 'leader-sales', 'leader-sales-export', 'user-sales', 'user-sales-export'], //指定控制器不应用到哪些动作
            ]
        ];
    }

    /**
     * 销售统计
     * @return array
     */
    public function actionSales()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            if (!isset($params['year']) || empty($params['year'])) {
                $year = date("Y", time());
                $begin_time = strtotime($year . "-01-01 00:00:00");
                $end_time = strtotime($year . "-12-31 23:59:59");
                $params['type'] = 1;
                $type = '%m';
                $days = 12;
            } else {
                if ($params['type'] == 1) { //按年
                    $begin_time = strtotime($params['year'] . "-01-01 00:00:00");
                    $end_time = strtotime($params['year'] . "-12-31 23:59:59");
                    $type = '%m';
                    $days = 12;
                } else { //按月
                    $days = cal_days_in_month(CAL_GREGORIAN, $params['month'], $params['year']);
                    $date = $this->getMonthFirstAndLast($params['year'], $params['month']);
                    $begin_time = $date['firstDay'];
                    $end_time = $date['lastDay'];
                    $type = '%d';
                }
            }
            $key = $params['key'];
            $monthOrDays = [];
            for ($i = 1; $i <= $days; $i++) {
                if ($i < 10) {
                    $monthOrDays['0' . $i] = '0' . $i;
                } else {
                    $monthOrDays[$i] = (string)$i;
                }
            }
            $result = $this->getSales($type, $begin_time, $end_time, $key, $monthOrDays);
            $refunds = $this->getRefunds($type, $begin_time, $end_time, $key, $monthOrDays);
            foreach ($result as $key => &$item) {
                $key = trim($key);
                $item['refund_money'] = $refunds[$key]['refund_money'];
                if (empty($item['money'])) $item['money'] = '0.00';
                if (empty($item['total'])) $item['total'] = '0';
                if (empty($item['cost'])) $item['cost'] = '0.00';
                if (empty($item['express_price'])) $item['express_price'] = '0.00';
            }
            return result(200, "请求成功", $result);
        } else {
            return result(500, "请求方式错误");
        }
    }


    /**
     * 销售导出
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws yii\db\Exception
     */
    public function actionSalesExport()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            if (!isset($params['year']) || empty($params['year'])) {
                $year = date("Y", time());
                $begin_time = strtotime($year . "-01-01 00:00:00");
                $end_time = strtotime($year . "-12-31 23:59:59");
                $params['type'] = 1;
                $type = '%m';
                $days = 12;
            } else {
                if ($params['type'] == 1) { //按年
                    $begin_time = strtotime($params['year'] . "-01-01 00:00:00");
                    $end_time = strtotime($params['year'] . "-12-31 23:59:59");
                    $type = '%m';
                    $days = 12;
                } else { //按月
                    $days = cal_days_in_month(CAL_GREGORIAN, $params['month'], $params['year']);
                    $date = $this->getMonthFirstAndLast($params['year'], $params['month']);
                    $begin_time = $date['firstDay'];
                    $end_time = $date['lastDay'];
                    $type = '%d';
                }
            }
            $key = $params['key'];
            $monthOrDays = [];
            for ($i = 1; $i <= $days; $i++) {
                if ($i < 10) {
                    $monthOrDays['0' . $i] = '0' . $i;
                } else {
                    $monthOrDays[$i] = (string)$i;
                }
            }
            $result = $this->getSales($type, $begin_time, $end_time, $key, $monthOrDays);
            $refunds = $this->getRefunds($type, $begin_time, $end_time, $key, $monthOrDays);
            foreach ($result as $key => &$item) {
                $key = trim($key);
                $item['refund_money'] = $refunds[$key]['refund_money'];
                if (empty($item['money'])) $item['money'] = '0.00';
                if (empty($item['total'])) $item['total'] = '0';
                if (empty($item['cost'])) $item['cost'] = '0.00';
                if (empty($item['express_price'])) $item['express_price'] = '0.00';
            }
            $inputFileName = './uploads/sales.xls';
            /** Load $inputFileName to a Spreadsheet Object  **/
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
            $worksheet = $spreadsheet->getActiveSheet();
            foreach ($result as $key => $val) {
                $key = trim($key);
                $line = $key + 1;
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
                $spreadsheet->setActiveSheetIndex(0)
                    ->setCellValue('A' . $line, $val['time'])
                    ->setCellValue('B' . $line, $val['money'])
                    ->setCellValue('C' . $line, $val['total'])
                    ->setCellValue('D' . $line, $val['cost'])
                    ->setCellValue('E' . $line, $val['express_price'])
                    ->setCellValue('F' . $line, $val['refund_money']);
            }
            $spreadsheet->getActiveSheet()->setTitle('销售统计导出');
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="销售统计导出.xls"');
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
     * 商品统计
     * @return array
     */
    public function actionGoodsSales()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $where = [];
            if (isset($params['begin_time']) && !empty($params['begin_time'])) {
                $where['begin_time'] = strtotime($params['begin_time']);
                $where['end_time'] = strtotime($params['end_time']);
            } else {
                $where['begin_time'] = 1514339841;
                $where['end_time'] = time();
            }
            if (isset($params['goods_name']) && !empty($params['goods_name'])) {
                $where['name'] = $params['goods_name'];
            }
            if (isset($params['goods_code']) && !empty($params['goods_code'])) {
                $where['code'] = $params['goods_code'];
            }
            $result = $this->getGoodsSales($where, $params['key']);
            $res['status'] = 200;
            $res['message'] = "请求成功";
            $res['data']=$result;
            $res['count'] = count($result);
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return $res;
        } else {
            return result(500, "请求方式错误");
        }
    }


    /**
     * 商品销售导出
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws yii\db\Exception
     */
    public function actionGoodsSalesExport()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $where = [];
            if (isset($params['begin_time']) && !empty($params['begin_time'])) {
                $where['begin_time'] = strtotime($params['begin_time']);
                $where['end_time'] = strtotime($params['end_time']);
            } else {
                $where['begin_time'] = 1514339841;
                $where['end_time'] = time();
            }
            if (isset($params['goods_name']) && !empty($params['goods_name'])) {
                $where['name'] = $params['goods_name'];
            }
            if (isset($params['goods_code']) && !empty($params['goods_code'])) {
                $where['code'] = $params['goods_code'];
            }
            $result = $this->getGoodsSales($where, $params['key']);
            $inputFileName = './uploads/goods_sales.xls';
            /** Load $inputFileName to a Spreadsheet Object  **/
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
            $worksheet = $spreadsheet->getActiveSheet();
            foreach ($result as $key => $val) {
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
                    ->setCellValue('A' . $line, $val['goods_id'])
                    ->setCellValue('B' . $line, $val['goods_name'])
                    ->setCellValue('C' . $line, $val['price_total'])
                    ->setCellValue('D' . $line, $val['sale_total'])
                    ->setCellValue('E' . $line, $val['cost_price']);
            }
            $spreadsheet->getActiveSheet()->setTitle('商品销售统计导出');
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="商品销售统计导出.xls"');
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
     * 团长销售统计
     * @return array
     */
    public function actionLeaderSales()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $where = [];
            if (isset($params['begin_time']) && !empty($params['begin_time'])) {
                $where['begin_time'] = strtotime($params['begin_time']);
                $where['end_time'] = strtotime($params['end_time']);
            } else {
                $where['begin_time'] = 1514339841;
                $where['end_time'] = time();
            }
            if (isset($params['leader_name']) && !empty($params['leader_name'])) {
                $where['leader_name'] = $params['leader_name'];
            }
            $result = $this->getLeaderSales($where, $params['key']);
            $res['status'] = 200;
            $res['message'] = "请求成功";
            $res['data']=$result;
            $res['count'] = count($result);
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return $res;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 团长销售统计导出
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws yii\db\Exception
     */
    public function actionLeaderSalesExport()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $where = [];
            if (isset($params['begin_time']) && !empty($params['begin_time'])) {
                $where['begin_time'] = strtotime($params['begin_time']);
                $where['end_time'] = strtotime($params['end_time']);
            } else {
                $where['begin_time'] = 1514339841;
                $where['end_time'] = time();
            }
            if (isset($params['leader_name']) && !empty($params['leader_name'])) {
                $where['leader_name'] = $params['leader_name'];
            }
            $result = $this->getLeaderSales($where, $params['key']);
            $inputFileName = './uploads/leader_sales.xls';
            /** Load $inputFileName to a Spreadsheet Object  **/
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
            $worksheet = $spreadsheet->getActiveSheet();
            foreach ($result as $key => $val) {
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
                $worksheet->getStyle('H' . $line)->applyFromArray($styleArray);
                $spreadsheet->setActiveSheetIndex(0)
                    ->setCellValue('A' . $line, $val['leader_uid'])
                    ->setCellValue('B' . $line, $val['nickname'])
                    ->setCellValue('C' . $line, $val['area_name'])
                    ->setCellValue('D' . $line, $val['total_money'])
                    ->setCellValue('E' . $line, $val['total'])
                    ->setCellValue('F' . $line, $val['remain_money'])
                    ->setCellValue('G' . $line, $val['express_price'])
                    ->setCellValue('H' . $line, $val['refund_money']);
            }
            $spreadsheet->getActiveSheet()->setTitle('团长销售统计导出');
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="团长销售统计导出.xls"');
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
     * 用户排行统计
     * @return array
     */
    public function actionUserSales()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $where = [];
            if (isset($params['begin_time']) && !empty($params['begin_time'])) {
                $where['begin_time'] = strtotime($params['begin_time']);
                $where['end_time'] = strtotime($params['end_time']);
            } else {
                $where['begin_time'] = 1514339841;
                $where['end_time'] = time();
            }
            if (isset($params['search_user']) && !empty($params['search_user'])) {
                $where['search_user'] = $params['search_user'];
            }
            $order_by = 'total';
            if (isset($params['order_by']) && !empty($params['order_by'])) {
                $order_by = $params['order_by'];
            }
            $result = $this->getUserSales($where, $params['key'], $order_by);
            $res['status'] = 200;
            $res['message'] = "请求成功";
            $res['data']=$result;
            $res['count'] = count($result);
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return $res;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 用户排行统计导出
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws yii\db\Exception
     */
    public function actionUserSalesExport()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $where = [];
            if (isset($params['begin_time']) && !empty($params['begin_time'])) {
                $where['begin_time'] = strtotime($params['begin_time']);
                $where['end_time'] = strtotime($params['end_time']);
            } else {
                $where['begin_time'] = 1514339841;
                $where['end_time'] = time();
            }
            if (isset($params['search_user']) && !empty($params['search_user'])) {
                $where['search_user'] = $params['search_user'];
            }
            $order_by = 'total';
            if (isset($params['order_by']) && !empty($params['order_by'])) {
                $order_by = $params['order_by'];
            }
            $result = $this->getUserSales($where, $params['key'], $order_by);
            $inputFileName = './uploads/user_sales.xls';
            /** Load $inputFileName to a Spreadsheet Object  **/
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
            $worksheet = $spreadsheet->getActiveSheet();
            foreach ($result as $key => $val) {
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
                $worksheet->getStyle('H' . $line)->applyFromArray($styleArray);
                $spreadsheet->setActiveSheetIndex(0)
                    ->setCellValue('A' . $line, $key + 1)
                    ->setCellValue('B' . $line, $val['user_id'])
                    ->setCellValue('C' . $line, $val['nickname'])
                    ->setCellValue('D' . $line, $val['phone'])
                    ->setCellValue('E' . $line, $val['user_level'])
                    ->setCellValue('F' . $line, $val['total_money'])
                    ->setCellValue('G' . $line, $val['total'])
                    ->setCellValue('H' . $line, $val['goods_number']);
            }
            $spreadsheet->getActiveSheet()->setTitle('用户排行统计导出');
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="用户排行统计导出.xls"');
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
     * 订单销售
     * @param $type
     * @param $begin_time
     * @param $end_time
     * @param $key
     * @param $monthOrDays
     * @return array
     */
    function getSales($type, $begin_time, $end_time, $key, $monthOrDays)
    {
        $sql = "SELECT
                DATE_FORMAT( FROM_UNIXTIME( SOG.create_time ), '$type' ) AS time,
                sum( SOG.payment_money ) money,
                count( SOG.id ) total,
                sum( SS.cost_price * SO.number ) cost,
                sum( SOG.express_price ) express_price
            FROM
                shop_order_group AS SOG
                LEFT JOIN shop_order AS SO on SO.order_group_sn = SOG.order_sn
                LEFT JOIN shop_stock AS SS on SS.id = SO.stock_id
            WHERE
                SOG.create_time > {$begin_time} 
                AND SOG.create_time < {$end_time} 
                AND SOG.status in(1,3,5,6,7,11)
                AND SOG.`key` = '$key'
            GROUP BY
                time";
        $orderModel = new OrderModel();
        $data[] = ['time' => '01', 'money' => '0.00', 'total' => '0', 'cost' => '0.00', 'express_price' => '0.00',];
        $data = $orderModel->querySql($sql);
        $new_data = [];
        foreach ($data as $key => $val_data) {
            $new_data[$val_data['time']] = $val_data;
        }
        $result = [];
        foreach ($monthOrDays as $val) {
            if (!array_key_exists($val, $new_data)) {
                $result[' '.$val] = ['time' => $val, 'money' => '0.00', 'total' => '0', 'cost' => '0.00', 'express_price' => '0.00'];
            } else {
                $result[' '.$val] = $new_data[$val];
            }
        }
        return $result;
    }

    /**
     * 退单销售
     * @param $type
     * @param $begin_time
     * @param $end_time
     * @param $key
     * @param $monthOrDays
     * @return array
     */
    function getRefunds($type, $begin_time, $end_time, $key, $monthOrDays)
    {
        $sql = "SELECT
                DATE_FORMAT( FROM_UNIXTIME( SOG.create_time ), '$type' ) AS time,
                sum( SOG.payment_money ) refund_money
            FROM
                shop_order_group AS SOG
            WHERE
                SOG.create_time > {$begin_time} 
                AND SOG.create_time < {$end_time} 
                AND SOG.status = 4
                AND SOG.`key` = '$key'
            GROUP BY
                time";
        $orderModel = new OrderModel();
        $data[] = ['time' => '01', 'refund_money' => '0.00'];
        $data = $orderModel->querySql($sql);
        $new_data = [];
        foreach ($data as $key => $val) {
            $new_data[$val['time']] = $val;
        }
        $result = [];
        foreach ($monthOrDays as $val) {
            if (!array_key_exists($val, $new_data)) {
                $result[$val] = ['time' => $val, 'refund_money' => '0.00'];
            } else {
                $result[$val] = $new_data[$val];
            }
        }
        return $result;
    }

    /**
     * @param $params
     * @param $key
     * @return array
     */
    function getGoodsSales($params, $key)
    {
        $where = '';
        if (isset($params['code']) && !empty($params['code'])) {
            $code = $params['code'];
            $where = " AND SS.code = '$code'";
        }
        if (isset($params['name']) && !empty($params['name'])) {
            $name = $params['name'];
            $where = " AND SS.`name` = '$name'";
        }
        $sql = "SELECT
                    SO.goods_id,
                    SS.`name` goods_name,
                    SS.`pic_url` pic_url,
                    sum( SO.number ) sale_total,
                    sum( SS.cost_price * SO.number ) cost_price,
                    sum( SS.price * SO.number ) price_total
                FROM
                    shop_order_group AS SOG
                    right JOIN shop_order AS SO ON SO.order_group_sn = SOG.order_sn
                    right JOIN shop_stock AS SS ON SS.id = SO.stock_id
                WHERE
                    SOG.create_time > {$params['begin_time']} 
                    AND SOG.create_time < {$params['end_time']} 
                    AND SOG.`status` IN ( 1, 3, 5, 6, 7, 11 ) 
                    AND SOG.`key` = '$key' $where
                GROUP BY
                    goods_id";
        $orderModel = new OrderModel();
        $data = $orderModel->querySql($sql);
        return $data;
    }

    /**
     * @param $params
     * @param $key
     * @return array
     */
    function getLeaderSales($params, $key)
    {
        $where = '';
        if (isset($params['leader_name']) && !empty($params['leader_name'])) {
            $name = $params['leader_name'];
            $where = " AND `shop_tuan_leader`.`realname` = '$name'";
        }
        $sql = "SELECT
                    SUM( `shop_order_group`.payment_money ) total_money,
                    COUNT( `shop_order_group`.id ) total,
                    `shop_user`.`nickname`,
                    `shop_user`.`avatar`,
                    `shop_tuan_leader`.`realname`,
                    `shop_order_group`.`leader_uid`,
                    `shop_order_group`.`leader_self_uid`,
                    sum( `shop_order_group`.express_price ) express_price,
                    `shop_tuan_leader`.`area_name` 
                FROM
                    `shop_order_group`
                    LEFT JOIN `shop_tuan_leader` ON shop_tuan_leader.uid = shop_order_group.leader_uid 
                    AND shop_tuan_leader.`key` = '$key'
                    LEFT JOIN `shop_user` ON shop_user.id = shop_order_group.user_id 
                WHERE
                    ( `shop_order_group`.`delete_time` IS NULL ) 
                    AND ( `shop_order_group`.`leader_uid` > 0 ) 
                    AND ( `shop_order_group`.`leader_uid` IS NOT NULL ) 
                    AND ( `shop_order_group`.`key` = '$key' ) 
                    AND ( `shop_order_group`.`status` IN ( 1, 3, 5, 6, 7, 11 ) ) 
                    AND ( `shop_order_group`.`supplier_id` =0 ) 
                    AND ( `shop_order_group`.create_time > {$params['begin_time']}) 
                    AND ( `shop_order_group`.create_time < {$params['end_time']}) $where
                GROUP BY
                    `shop_order_group`.`leader_uid`";
        $orderModel = new OrderModel();
        $data = $orderModel->querySql($sql);
        if (!empty($data)) {
            foreach ($data as &$val) {
                if (empty($val['nickname'])) $val['nickname'] = '';
                if (empty($val['avatar'])) $val['avatar'] = '';
                if (empty($val['realname'])) $val['realname'] = '';
                if (empty($val['area_name'])) $val['area_name'] = '';
                if (empty($val['total_money'])) $val['total_money'] = '0.00';
                if (empty($val['total'])) $val['total'] = '0';
                if (empty($val['express_price'])) $val['express_price'] = '0.00';
                if ($val['leader_uid']) {
                    $refunds = $this->leaderRefunds($params['begin_time'], $params['end_time'], $val['leader_uid']);
                    $val['refund_money'] = $refunds[0]['refund_money'] ? $refunds[0]['refund_money'] : 0.00;
                    $refunds = $this->leaderBalance($params['begin_time'], $params['end_time'], $val['leader_self_uid']);
                    $val['remain_money'] = $refunds[0]['remain_money'] ? $refunds[0]['remain_money'] : 0.00;
                }
            }
        }
        return $data;
    }

    /**
     * 团长退单
     * @param $begin_time
     * @param $end_time
     * @param $key
     * @param $leader_uid
     * @return array
     */
    function leaderRefunds($begin_time, $end_time, $leader_uid)
    {
        $sql = "SELECT
                sum( SOG.payment_money ) refund_money
            FROM
                shop_order_group AS SOG
            WHERE
                SOG.create_time > {$begin_time} 
                AND SOG.create_time < {$end_time} 
                AND SOG.status = 4
                AND SOG.`leader_uid` = $leader_uid";
        $orderModel = new OrderModel();
        return $orderModel->querySql($sql);
    }

    /**
     * 团长佣金
     * @param $begin_time
     * @param $end_time
     * @param $leader_self_uid
     * @return array
     */
    function leaderBalance($begin_time, $end_time, $leader_self_uid)
    {
        $sql = "SELECT
                sum( SOG.money ) remain_money
            FROM
                shop_user_balance AS SOG
            WHERE
                SOG.create_time > {$begin_time} 
                AND SOG.create_time < {$end_time} 
                AND SOG.type = 3
                AND SOG.`uid` = $leader_self_uid";
        $orderModel = new OrderModel();
        return $orderModel->querySql($sql);
    }

    /**
     * 获取某年某月的第一天和最后一天
     * @param string $y
     * @param string $m
     * @return array
     */
    function getMonthFirstAndLast($y = "", $m = "")
    {
        if ($y == "") $y = date("Y");
        if ($m == "") $m = date("m");
        $m = sprintf("%02d", intval($m));
        $y = str_pad(intval($y), 4, "0", STR_PAD_RIGHT);
        $m > 12 || $m < 1 ? $m = 1 : $m = $m;
        $firstDay = strtotime($y . $m . "01000000");
        $firstDayStr = date("Y-m-01", $firstDay);
        $lastDay = strtotime(date('Y-m-d 23:59:59', strtotime("$firstDayStr +1 month -1 day")));
        return array("firstDay" => $firstDay, "lastDay" => $lastDay);
    }


    /**
     * @param $params
     * @param $key
     * @param string $order_by
     * @return array
     */
    function getUserSales($params, $key, $order_by = 'total')
    {
        $where = '';
        if (isset($params['search_user']) && !empty($params['search_user'])) {
            $search_user = $params['search_user'];
            $where = " AND (`shop_user`.`nickname` = '$search_user' OR `shop_user`.`id` = '$search_user')";
        }
        $sql = "SELECT
                    SUM( `shop_order_group`.payment_money ) total_money,
                    COUNT( `shop_order_group`.id ) total,
                    `shop_user`.`nickname`,
                    `shop_order_group`.`user_id`,
                    `shop_user`.`phone`,
                IF
                    ( `shop_user`.`is_vip` = 1, 'VIP会员', '普通会员' ) AS user_level,
                    SUM( `shop_order`.`number` ) goods_number,
                    `shop_user`.`avatar` 
                FROM
                    `shop_order_group`
                    LEFT JOIN `shop_user` ON shop_user.id = shop_order_group.user_id
                    LEFT JOIN `shop_order` ON shop_order.order_group_sn = shop_order_group.order_sn 
                WHERE
                    ( `shop_order_group`.`delete_time` IS NULL ) 
                    AND ( `shop_order_group`.`key` = '$key' ) 
                    AND ( `shop_order_group`.create_time > {$params['begin_time']}) 
                    AND ( `shop_order_group`.create_time < {$params['end_time']})
                    AND ( `shop_order_group`.`status` IN ( 1, 3, 5, 6, 7, 11 ) ) 
                    AND ( `shop_order_group`.`supplier_id` = 0 ) $where
                GROUP BY
                    `shop_order_group`.`user_id`
                    ORDER BY {$order_by} DESC";
        $orderModel = new OrderModel();
        $data = $orderModel->querySql($sql);
        if (!empty($data)) {
            foreach ($data as &$val) {
                if (empty($val['nickname'])) $val['nickname'] = '';
                if (empty($val['avatar'])) $val['avatar'] = '';
                if (empty($val['total_money'])) $val['total_money'] = '0.00';
                if (empty($val['total'])) $val['total'] = '0';
                if (empty($val['goods_number'])) $val['goods_number'] = '0';
                if (empty($val['phone'])) $val['phone'] = '';
                if (empty($val['avatar'])) $val['avatar'] = '';
            }
        }
        return $data;
    }
}
