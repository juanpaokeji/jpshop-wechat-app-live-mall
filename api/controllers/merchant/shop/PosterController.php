<?php

namespace app\controllers\merchant\shop;

use app\models\core\UploadsModel;
use app\models\merchant\system\OperationRecordModel;
use app\models\shop\ShopPosterModel;
use Yii;
use yii\web\MerchantController;

class PosterController extends MerchantController
{
    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * 显示
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
                return $rs;
            }
            $posterModel = new ShopPosterModel();
            $list = $posterModel->do_select(['key' => $params['key'], 'merchant_id' => yii::$app->session['uid']]);
            return $list;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 新增或者更新
     * @return array|string
     */
    public function actionUpdate()
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            if (empty($_FILES)) {
                return result(500, "缺少上传文件信息");
            }
            $image_arr = getimagesize($_FILES["file"]['tmp_name']);
            if($params['type'] == 0 && ($image_arr[0] != 375 || $image_arr[1] != 670)){
                return result(500, "首页图片尺寸必须是375*670");
            }
            if($params['type'] == 1 && ($image_arr[0] != 375 || $image_arr[1] != 563)){
                return result(500, "详情页图片尺寸必须是375*563");
            }
            $upload = new UploadsModel('file', "./uploads/poster");
            $uploads = $upload->upload();
            if (!$uploads) {
                return "上传文件错误";
            }
            $path = $uploads;
            $uploads = ltrim($uploads, '.');
            $pic_url = 'https://'.$_SERVER['SERVER_NAME'] .'/api/web/' . $uploads;
            $posterModel = new ShopPosterModel();
            $info = $posterModel->one(['key'=>$params['key'],'type'=>$params['type']]);
            if($info['status'] == 200){
                $res = $posterModel->do_update(['id' => $info['data']['id']], ['path' => $path, 'pic_url' => $pic_url]);
                $operationRecordData['operation_id'] = $info['data']['id'];
            }else{
                $addData['key'] = $params['key'];
                $addData['merchant_id'] = yii::$app->session['uid'];
                $addData['type'] = $params['type'] ?? 0;
                $addData['status'] = 1;
                $addData['path'] = $path;
                $addData['pic_url'] = $pic_url;
                $res = $posterModel->add($addData);
                $operationRecordData['operation_id'] = $res['data'];
            }
            if ($res['status'] == 200){
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['key'];
                $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                $operationRecordData['operation_type'] = '更新';
                $operationRecordData['operation_id'] = $res['data'];
                $operationRecordData['module_name'] = '分享海报';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $res;
        } else {
            return result(500, "请求方式错误");
        }
    }
}
