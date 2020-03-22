<?php

namespace app\controllers\merchant\tuan;

use yii;
use yii\db\Exception;
use yii\web\MerchantController;
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
class GoodsController extends MerchantController {

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
            $model = new GoodsModel();
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['supplier_id'] = yii::$app->session['sid'];
            $params['merchant_id'] = yii::$app->session['uid'];
            $params['delete_time'] = 1;
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
            $category = new GoodsModel();
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['id'] = $id;
            $array = $category->findOne($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $model = new GoodsModel();

            //设置类目 参数
            $must = ['name', 'key', 'price', 'pic_urls', 'detail_info', 'stocks'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['uid'];
            $params['start_time'] = str_replace("+", " ", $params['start_time']);
            $start_time = $params['start_time'] == "" ? time() : strtotime($params['start_time']);
            $goodsData = array(
                '`key`' => yii::$app->session['key'],
                'merchant_id' => yii::$app->session['uid'],
                'supplier_id' => yii::$app->session['sid'],
                'name' => $params['name'],
                'code' => $params['code'],
                'price' => $params['price'],
                'line_price' => $params['line_price'],
                'pic_urls' => $params['pic_urls'],
                'stocks' => $params['stocks'],
                'm_category_id' => $params['m_category_id'],
                'city_group_id' => $params['city_group_id'],
                'sort' => $params['sort'],
                'type' => $params['type'],
                'start_time' => $start_time,
                'detail_info' => $params['detail_info'],
                'simple_info' => $params['simple_info'],
                'label' => $params['label'],
                'short_name' => $params['short_name'],
                'property1' => $params['property1'] == "" ? "默认:默认" : $params['property1'],
                'property2' => $params['property2'],
                'stock_type' => $params['stock_type'],
                'have_stock_type' => $params['have_stock_type'],
                'is_top' => $params['is_top'],
                'is_check' => 0,
                'status' => 0,
            );

            $array = $model->add($goodsData);
            if ($array['status'] != 200) {
                return $array;
            }
            $stockModel = new StockModel();
            $str = creat_mulu("./uploads/goods/" . $params['merchant_id']);
            $base = new Base64Model();


            $transaction = yii::$app->db->beginTransaction();
            try {
                if ($params['have_stock_type'] == 0) {
                    $pic_url = explode(",", $params['pic_urls']);
                    $data['`key`'] = $params['`key`'];
                    $data['merchant_id'] = yii::$app->session['uid'];
                    $data['goods_id'] = $array['data'];
                    $data['name'] = $params['name'];
                    $data['code'] = $params['code'];
                    $data['number'] = $params['stocks'];
                    $data['price'] = 0;
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
                        $data['merchant_id'] = yii::$app->session['uid'];
                        $data['goods_id'] = $array['data'];
                        $data['name'] = $params['name'];
                        $data['code'] = $params['stock']['code'][$i];
                        $data['number'] = $params['stock']['number'][$i];
                        $data['price'] = 0;
                        $data['cost_price'] = $params['stock']['cost_price'][$i];
                        $data['property1_name'] = $params['stock']['property1_name'][$i];
                        $data['property2_name'] = $params['stock']['property2_name'][$i];
                        if (isset($params['stock']['pic_url'][$i])) {
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
                            }
                        }
                        $data['status'] = 1;
                        $stockModel->add($data);
                    }
                }

                $transaction->commit(); //只有执行了commit(),对于上面数据库的操作才会真正执行
                return result(200, "新增成功");
            } catch (Exception $e) {
                $transaction->rollBack(); //回滚
                return result(500, "新增失败");
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new GoodsModel();
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['id'] = $id;
            $params['merchant_id'] = yii::$app->session['uid'];

            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $params['`key`'] = $params['key'];
                unset($params['key']);
                $params['merchant_id'] = yii::$app->session['uid'];
                $params['start_time'] = str_replace("+", " ", $params['start_time']);
                $start_time = $params['start_time'] == "" ? time() : strtotime($params['start_time']);
                $goodsData = array(
                    'id' => $params['id'],
                    '`key`' => yii::$app->session['key'],
                    'merchant_id' => yii::$app->session['uid'],
                    'supplier_id' => yii::$app->session['sid'],
                    'name' => $params['name'],
                    'code' => $params['code'],
                    'price' => $params['price'],
                    'line_price' => $params['line_price'],
                    'pic_urls' => $params['pic_urls'],
                    'stocks' => $params['stocks'],
                    'm_category_id' => $params['m_category_id'],
                    'city_group_id' => $params['city_group_id'],
                    'sort' => $params['sort'],
                    'type' => $params['type'],
                    'start_time' => $start_time,
                    'detail_info' => $params['detail_info'],
                    'simple_info' => $params['simple_info'],
                    'label' => $params['label'],
                    'short_name' => $params['short_name'],
                    'property1' => $params['property1'] == "" ? "默认:默认" : $params['property1'],
                    'property2' => $params['property2'],
                    'stock_type' => $params['stock_type'],
                    'have_stock_type' => $params['have_stock_type'],
                    'is_top' => $params['is_top'],
                    'is_check' => 0,
                    'status' => 0,
                );
                $array = $model->update($goodsData);
                // $transaction->commit();
                $transaction = yii::$app->db->beginTransaction();
                try {

                    $stockModel = new StockModel();
                    $delData['goods_id'] = $params['id'];
                    $stockModel->delete($delData);
                    $str = creat_mulu("./uploads/goods/" . $params['merchant_id']);
                    $base = new Base64Model();

                    if ($params['have_stock_type'] == 0) {
                        $pic_url = explode(",", $params['pic_urls']);
                        $data['`key`'] = $params['`key`'];
                        $data['merchant_id'] = yii::$app->session['uid'];
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
                            $data['`key`'] = $params['`key`'];
                            $data['merchant_id'] = yii::$app->session['uid'];
                            $data['goods_id'] = $params['id'];
                            $data['name'] = $params['name'];
                            $data['code'] = $params['stock']['code'][$i];
                            $data['number'] = $params['stock']['number'][$i];
                            $data['price'] = 0;
                            $data['cost_price'] = $params['stock']['cost_price'][$i];
                            $data['property1_name'] = $params['stock']['property1_name'][$i];
                            $data['property2_name'] = $params['stock']['property2_name'][$i];
                            if (strpos($params['stock']['pic_url'][$i], 'https://imgs.juanpao.com') !== false) {
                                $data['pic_url'] = $params['stock']['pic_url'][$i];
                            } else {
                                $localRes = $base->base64_image_content($params['stock']['pic_url'][$i], $str);
                                if (!$localRes) {
                                    $transaction->rollBack(); //回滚
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
                    $transaction->commit(); //只有执行了commit(),对于上面数据库的操作才会真正执行
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

    public function actionUpdates($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new GoodsModel();
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['id'] = $id;
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                if (isset($params['key'])) {
                    $params['`key`'] = $params['key'];
                    unset($params['key']);
                }
                $params['merchant_id'] = yii::$app->session['uid'];
                $array = $model->update($params);
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

            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $model = new GoodsModel();
            $params['id'] = $id;
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['uid'];
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

    public function actionUploads() {
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

    public function actionUploadsinfo() {
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
    public function actionRecycle() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new GoodsModel();
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['uid'];
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
    public function actionReduction($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new GoodsModel();
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $data['id'] = $id;
            if (!isset($data['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $data['`key`'] = $params['key'];
                $data['merchant_id'] = yii::$app->session['uid'];
                $data['status'] = 0;
                $array = $model->updates($data);
                return $array;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

}
