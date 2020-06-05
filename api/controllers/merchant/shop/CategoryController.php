<?php

namespace app\controllers\merchant\shop;

use app\models\merchant\system\OperationRecordModel;
use app\models\shop\GoodsModel;
use yii;
use yii\db\Exception;
use yii\web\MerchantController;
use app\models\shop\CategoryModel;
use app\models\shop\MerchantCategoryModel;
use app\models\shop\MerchantsCategoryModel;
use app\models\core\Base64Model;
use app\models\core\CosModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class CategoryController extends MerchantController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * 地址:/admin/group/index 默认访问
     * @throws Exception if the model cannot be found
     * @return array
     */

    public function actionList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new MerchantCategoryModel();
            if (isset($params['key'])) {
                $params['`key`'] = $params['key'];
                unset($params['key']);
            }
            $params['merchant_id'] = yii::$app->session['uid'];
            $params['parent_id'] = 0;
            $params['supplier_id'] = 0;
            $res = $model->findall($params);
            unset($params['parent_id']);
            $params['parent_id !=0'] = null;
            unset($params['limit']);
            unset($params['page']);
            $array = $model->findall($params);

            if ($res['status'] != 200) {
                return $res;
            }
            $data = array();
            for ($i = 0; $i < count($res['data']); $i++) {
                if ($res['data'][$i]['parent_id'] == 0) {
                    $data[] = $res['data'][$i];
                }
            }

            for ($i = 0; $i < count($data); $i++) {
                $data[$i]['data'] = array();
                //此处加个判断，如果分组存在则循环，否则不执行
                if ($array['status'] == 200) {
                    for ($j = 0; $j < count($array['data']); $j++) {
                        if ($data[$i]['id'] == $array['data'][$j]['parent_id']) {
                            $data[$i]['data'][] = $array['data'][$j];
                        }
                    }
                }
            }


            return ['status' => 200, 'message' => '请求成功', 'data' => $data, 'count' => (int)$res['count']];
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 原写法转yii写法，查询商品分组列表
     * @return array|bool
     */
    public function actionAll() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = MerchantsCategoryModel::instance()->get_list();
            var_dump($array);
            return;
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionParent() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $category = new MerchantCategoryModel();
            $data['`key`'] = $params['key'];
            $data['fields'] = " id,name ";
            $data['parent_id'] = 0;
            //由于此接口需要过滤门店分组，所以添加判断是否传参中包含门店id，为了向前兼容，所以判断如果包含再添加查询条件
            if (isset($params['supplier_id'])) {
                $data['supplier_id'] = $params['supplier_id'];
            }
            $array = $category->finds($data);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSub() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $category = new MerchantCategoryModel();
            $data['`key`'] = $params['key'];
            $data['fields'] = " id,name,pic_url ";
            $data['parent_id'] = 0;
            $array = $category->finds($data);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionCategory() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new CategoryModel();
            $array = $model->findall($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $category = new MerchantCategoryModel();
            $params['id'] = $id;
            $array = $category->find($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new MerchantCategoryModel();
            $base = new Base64Model();
            //设置类目 参数
            $must = ['name'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
//            if ($params['pic_url'] != "") {
//                $params['pic_url'] = $base->base64_image_content($params['pic_url'], "./uploads/merchant/shop/category");
//                $cos = new CosModel();
//                $cosRes = $cos->putObject($params['pic_url']);
//                if ($cosRes['status'] == '200') {
//                    $url = $cosRes['data'];
//                } else {
//                    unlink(Yii::getAlias('@webroot/') . $params['pic_url']);
//                    return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
//                }
//                $params['pic_url'] = $url;
//            }

//            if ($params['img_url'] != "") {
//                $params['img_url'] = $base->base64_image_content($params['img_url'], "./uploads/merchant/shop/category");
//                $cos = new CosModel();
//                $cosRes = $cos->putObject($params['img_url']);
//                if ($cosRes['status'] == '200') {
//                    $url = $cosRes['data'];
//                } else {
//                    unlink(Yii::getAlias('@webroot/') . $params['img_url']);
//                    return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
//                }
//                $params['img_url'] = $url;
//            }
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->add($params);
            if ($array['status'] == 200){
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['`key`'];
                $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                $operationRecordData['operation_type'] = '新增';
                $operationRecordData['operation_id'] = $array['data'];
                $operationRecordData['module_name'] = '商品分组';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new MerchantCategoryModel();
            $params['id'] = $id;
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['uid'];
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $array = $model->update($params);

                if ($array['status'] == 200) {
                    //添加操作记录
                    $operationRecordModel = new OperationRecordModel();
                    $operationRecordData['key'] = $params['`key`'];
                    $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                    $operationRecordData['operation_type'] = '更新';
                    $operationRecordData['operation_id'] = $id;
                    $operationRecordData['module_name'] = '商品分组';
                    $operationRecordModel->do_add($operationRecordData);
                }

                return $array;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionStatus($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new MerchantCategoryModel();
            $data['id'] = $id;
            $data['`key`'] = $params['key'];
            $data['merchant_id'] = yii::$app->session['uid'];
            $data['status'] = $params['status'];
            if (!isset($data['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $array = $model->update($data);

                if ($array['status'] == 200) {
                    //添加操作记录
                    $operationRecordModel = new OperationRecordModel();
                    $operationRecordData['key'] = $data['`key`'];
                    $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                    $operationRecordData['operation_type'] = '更新';
                    $operationRecordData['operation_id'] = $id;
                    $operationRecordData['module_name'] = '商品分组';
                    $operationRecordModel->do_add($operationRecordData);
                }

                return $array;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionDelete($id) {
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new MerchantCategoryModel();
            $params['id'] = $id;
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['uid'];
            //删除一级分类时，如有二级分类不能删除
            $categoryWhere['id'] = $id;
            $categoryWhere['`key`'] = $params['`key`'];
            $categoryWhere['merchant_id'] = yii::$app->session['uid'];
            $categoryWhere['parent_id'] = 0;
            $res = $model->find($categoryWhere);
            if ($res['status'] == 200){
                $subsetWhere['`key`'] = $params['`key`'];
                $subsetWhere['merchant_id'] = yii::$app->session['uid'];
                $subsetWhere['parent_id'] = $id;
                $res = $model->find($subsetWhere);
                if ($res['status'] == 200){
                    return result(500, "该分组下有子类，不能删除");
                }
            }

            //删除二级分类时，如当前子类下有商品不能删除
            $where['id'] = $id;
            $where['`key`'] = $params['`key`'];
            $where['merchant_id'] = yii::$app->session['uid'];
            $where['parent_id != 0'] = null;
            $res = $model->find($where);
            if ($res['status'] == 200){
                $goodsModel = new GoodsModel();
                $goodsWhere['key'] = $params['`key`'];
                $goodsWhere['merchant_id'] = yii::$app->session['uid'];
                $goodsWhere['m_category_id'] = $id;
                $result = $goodsModel->one($goodsWhere);
                if ($result['status'] == 200){
                    return result(500, "该分组下有商品,不能删除");
                }
            }

            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $array = $model->delete($params);

                if ($array['status'] == 200){
                    //添加操作记录
                    $operationRecordModel = new OperationRecordModel();
                    $operationRecordData['key'] = $params['`key`'];
                    $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                    $operationRecordData['operation_type'] = '删除';
                    $operationRecordData['operation_id'] = $id;
                    $operationRecordData['module_name'] = '商品分组';
                    $operationRecordModel->do_add($operationRecordData);
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 商户商城商品分类
     */
    public function actionType() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new CategoryModel();
            $data['fields'] = " id,name,parent_id ";
            $data['parent_id'] = 0;
            $array = $model->finds($data);
            unset($data['parent_id']);
            $data['parent_id !=0'] = null;

            $list = $model->finds($data);
            if ($list['status'] != 200) {
                return result(204, "查询失败");
            }
            for ($i = 0; $i < count($array['data']); $i++) {
                for ($j = 0; $j < count($list['data']); $j++) {
                    if ($array['data'][$i]['id'] == $list['data'][$j]['parent_id']) {
                        $array['data'][$i]['sub'][] = $list['data'][$j];
                    }
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 商户商城商户商品分类
     */
    public function actionMerchanttype() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new MerchantCategoryModel();
            $data['fields'] = " id,name,parent_id ";
            $data['parent_id'] = 0;
            $data['`key`'] = $params['key'];
            unset($params['key']);
            if (isset($params['supplier_id'])) {
                $data['supplier_id'] = $params['supplier_id'];
            }else{
                $data['merchant_id'] = yii::$app->session['uid'];
            }
            $array = $model->finds($data);
            if ($array['status'] != 200) {
                return result(204, "查询失败");
            }
            unset($data['parent_id']);
            $data['parent_id !=0'] = null;
            $list = $model->finds($data);
            if ($list['status'] != 200) {
                return result(204, "查询失败");
            }
            for ($i = 0; $i < count($array['data']); $i++) {
                $array['data'][$i]['sub'] = array();
                for ($j = 0; $j < count($list['data']); $j++) {
                    if ($array['data'][$i]['id'] == $list['data'][$j]['parent_id']) {
                        $array['data'][$i]['sub'][] = $list['data'][$j];
                    }
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
