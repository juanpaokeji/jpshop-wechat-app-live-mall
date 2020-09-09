<?php

namespace app\controllers\shop;

use app\models\merchant\app\AppAccessModel;
use app\models\shop\ShopGoodsModel;
use app\models\tuan\LeaderModel;
use yii;
use yii\db\Exception;
use yii\web\ShopController;
use app\models\shop\ContactModel;
use app\models\shop\ShopExpressTemplateDetailsModel;
use app\models\shop\ShopExpressTemplateModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class ContactController extends ShopController
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * 地址:/admin/group/index 默认访问
     * @throws Exception if the model cannot be found
     * @return array
     */

    public function behaviors()
    {
        return [
            'token' => [
                'class' => 'yii\filters\ShopFilter', //调用过滤器
                // 'only' => ['single'], //指定控制器应用到哪些动作
                'except' => ['address'], //指定控制器不应用到哪些动作
            ]
        ];
    }

    public function actionList()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new ContactModel();
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['user_id'] = yii::$app->session['user_id'];
            $params['status'] = 1;
            $array = $model->findall($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new ContactModel();
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['user_id'] = yii::$app->session['user_id'];
            $array = $model->find($params);
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
            $model = new ContactModel();
            //设置类目 参数
            $must = ['name', 'phone', 'province', 'city', 'area', 'address'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['`key`'] = yii::$app->session['key'];
            if (isset($params['is_default'])) {
                $params['is_default'] == false ? 0 : 1;
            }
            $appAccess = new AppAccessModel();
            $app = $appAccess->find(['`key`' => 'ccvWPn']);
            if ($app['status'] == 200) {
                if ($app['data']['is_location'] == 1 && ($params['longitude'] == 0 && $params['latitude'] == 0)) {
                    return result(500, '请定位坐标地址！');
                }
            }
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['user_id'] = yii::$app->session['user_id'];
//            if ($params['longitude']!=0 &&$params['latitude']!=0) {
//                $url = "https://restapi.amap.com/v3/geocode/regeo?output=json&location={$params['longitude']},{$params['latitude']}&key=bc55956766e813d3deb1f95e45e97d73&poitype=&radius=1000&extensions=all&batch=true&roadlevel=0";
//                $address = json_decode(curlGet($url), true);
//                if ($address['status'] == 0) {
//                    return result(500, $address['info']);
//                }
//                $params['province'] = $address['regeocodes'][0]['addressComponent']['province'];
//                $params['city'] = is_array($address['regeocodes'][0]['addressComponent']['city']) ? $address['regeocodes'][0]['addressComponent']['province'] : $address['regeocodes'][0]['addressComponent']['city'];
//                $params['area'] = is_array($address['regeocodes'][0]['addressComponent']['district']) ? $address['regeocodes'][0]['addressComponent']['city'] : $address['regeocodes'][0]['addressComponent']['district'];
//            }

            if ($params['city'] == "") {
                $params['city'] = $params['province'];
            }

            if ($params['area'] == "") {
                $params['area'] = $params['city'];
            }


            $array = $model->add($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate($id)
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $model = new ContactModel();
            $must = ['name', 'phone', 'address', 'longitude', 'latitude', 'loction_address'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['id'] = $id;
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['user_id'] = yii::$app->session['user_id'];
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                if (isset($params['is_default'])) {
                    $params['is_default'] == false ? 0 : 1;
                }
                $appAccess = new AppAccessModel();
                $app = $appAccess->find(['`key`' => 'ccvWPn']);
                if ($app['status'] == 200) {
                    if ($app['data']['is_location'] == 1 && ($params['longitude'] == 0 && $params['latitude'] == 0)) {
                        return result(500, '请定位坐标地址！');
                    }
                }
                if ($params['longitude'] != 0 && $params['latitude'] != 0) {
                    $url = "https://restapi.amap.com/v3/geocode/regeo?output=json&location={$params['longitude']},{$params['latitude']}&key=bc55956766e813d3deb1f95e45e97d73&poitype=&radius=1000&extensions=all&batch=true&roadlevel=0";

                    $address = json_decode(curlGet($url), true);

                    if ($address['status'] == 0) {
                        return result(500, $address['info']);
                    }
                    $params['province'] = $address['regeocodes'][0]['addressComponent']['province'];
                    $params['city'] = is_array($address['regeocodes'][0]['addressComponent']['city']) ? "" : $address['regeocodes'][0]['addressComponent']['city'];
                    $params['area'] = is_array($address['regeocodes'][0]['addressComponent']['district']) ? "" : $address['regeocodes'][0]['addressComponent']['district'];
                }
                if ($params['is_default'] == 1) {
                    $data['is_default'] = 0;
                    $data['`key`'] = yii::$app->session['key'];
                    $data['merchant_id'] = yii::$app->session['merchant_id'];
                    $data['user_id'] = yii::$app->session['user_id'];
                    $array = $model->update($data);

                    $params['id'] = $id;
                    $params['is_default'] = 1;
                    $array = $model->update($params);
                    return $array;
                } else {
                    $array = $model->update($params);
                    return $array;
                }

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
            $model = new ContactModel();
            $params['id'] = $id;
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['user_id'] = yii::$app->session['user_id'];
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $params['status'] = 0;
                $array = $model->delete($params);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAddress()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $url = "https://restapi.amap.com/v3/config/district?key=bc55956766e813d3deb1f95e45e97d73&subdistrict=1";
            if (isset($params['keywords'])) {
                $url .= "&keywords=" . $params['keywords'];
            }
            $array = curlGet($url);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionLogistics()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $redis = getConfig($params['nu']);
            if (!$redis) {
                //$nu = "669202949669";
                $dateTime = gmdate("D, d M Y H:i:s T");
                $SecretId = 'AKID6p5FKDFI7gaFP9W1p85PUDYIBKT539eGC74q';
                $SecretKey = 'LzUey5Moflj039nMy0e6dicdp6cy6v972nf7y29e';
                $srcStr = "date: " . $dateTime . "\n" . "source: " . "source";
                $Authen = 'hmac id="' . $SecretId . '", algorithm="hmac-sha1", headers="date source", signature="';
                $signStr = base64_encode(hash_hmac('sha1', $srcStr, $SecretKey, true));
                $Authen = $Authen . $signStr . "\"";

                $url = "https://service-6t1c9ush-1255468759.ap-shanghai.apigateway.myqcloud.com/release/point-list?com=auto&nu={$params['nu']}";
                $headers = array(
                    'Host:service-6t1c9ush-1255468759.ap-shanghai.apigateway.myqcloud.com',
                    'Accept: */*',
                    'Source: source',
                    'Date: ' . $dateTime,
                    'Authorization: ' . $Authen,
                    'X-Requested-With: XMLHttpRequest',
                    'Accept-Encoding: gzip, deflate, sdch'
                );
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
                $data = curl_exec($ch);

                if ($data['showapi_res_body']['status'] == 4) {
                    setConfig($params['nu'], $data);
                    yii::$app->redis->expire($params['nu'], 2592000);
                } else {
                    setConfig($params['nu'], $data);
                    yii::$app->redis->expire($params['nu'], 7200);
                }
                return result(200, "请求成功", $data);
            } else {
                return result(200, "请求成功", $redis);
            }
        }
    }

    public function actionKdf($id)
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取body传参
            $model = new ShopExpressTemplateModel();
            $number = $params['number'];
            $weight = $params['weight'];

            $temp = $model->find(['status' => 1, 'supplier_id' => 0, 'merchant_id' => yii::$app->session['merchant_id'], '`key`' => yii::$app->session['key']]);
            if ($temp['status'] != 200) {
                return $temp;
            }
            $ids = json_decode($params['ids'], true);
            if (count($ids) == 1) {
                $goodsModel = new ShopGoodsModel();
                $goods = $goodsModel->do_one(['id' => $ids[0]]);
                if ($goods['status'] == 200) {
                    if ($goods['data']['is_parcel'] == 1) {
                        return result(200, "请求成功", '0');
                    }
                }
            }
            $type = $temp['data']['type'];
            $templateModel = new ShopExpressTemplateDetailsModel();
            //寄件 寄重
            if ($type == 1) {
                $model = new ContactModel();
                $params['id'] = $id;
                $params['`key`'] = yii::$app->session['key'];
                $params['user_id'] = yii::$app->session['user_id'];
                $tempModel = new ShopExpressTemplateModel();
                $data['merchant_id'] = yii::$app->session['merchant_id'];
                $data['supplier_id'] = 0;
                $data['`key`'] = yii::$app->session['key'];
                $data['status'] = 1;
                $temp = $tempModel->find($data);
                if ($temp['status'] != 200) {
                    return result(500, "快递费获取失败");
                }
                $address = $model->find($params);
                if ($address['status'] != 200) {
                    return result(500, "快递费获取失败");
                }
                $price = 0;
                $kdmb = new ShopExpressTemplateDetailsModel();

                unset($params['id']);
                $data['searchName'] = $address['data']['province'];
                $data['merchant_id'] = yii::$app->session['merchant_id'];
                $data['supplier_id'] = 0;
                $data['`key`'] = yii::$app->session['key'];
                $data['shop_express_template_id'] = $temp['data']['id'];
                $data['status'] = 1;
                if ($address['status'] == 200) {
                    $data['searchName'] = $address['data']['province'];
                    $kdf = $kdmb->find($data);
                } else {
                    $params['searchName'] = "全国统一运费";
                    $kdf = $kdmb->find($data);
                }
                if ($kdf['status'] != 200) {
                    $data['searchName'] = "全国统一运费";
                    $kdf = $kdmb->find($data);
                    $price = $kdf['data']['expand_price'];
                }
                $price = $kdf['data']['first_price'] + (($number - 1) * $kdf['data']['expand_price']);
                $price = $price == 0 ? "0" : $price;
                return result(200, "请求成功", round($price));
            } else if ($type == 2) {
                $model = new ContactModel();
                $params['id'] = $id;
                $params['`key`'] = yii::$app->session['key'];
                $params['user_id'] = yii::$app->session['user_id'];
                $tempModel = new ShopExpressTemplateModel();
                $data['merchant_id'] = yii::$app->session['merchant_id'];
                $data['supplier_id'] = 0;
                $data['`key`'] = yii::$app->session['key'];
                $data['status'] = 1;
                $temp = $tempModel->find($data);
                if ($temp['status'] != 200) {
                    return result(500, "快递费获取失败");
                }
                $address = $model->find($params);
                $price = 0;
                $kdmb = new ShopExpressTemplateDetailsModel();

                unset($params['id']);
                $data['searchName'] = $address['data']['province'];
                $data['merchant_id'] = yii::$app->session['merchant_id'];
                $data['supplier_id'] = 0;
                $data['`key`'] = yii::$app->session['key'];
                $data['shop_express_template_id'] = $temp['data']['id'];
                $data['status'] = 1;
                if ($address['status'] == 200) {
                    $data['searchName'] = $address['data']['province'];
                    $kdf = $kdmb->find($data);
                } else {
                    $params['searchName'] = "全国统一运费";
                    $kdf = $kdmb->find($data);
                }
                if ($kdf['status'] != 200) {
                    $data['searchName'] = "全国统一运费";
                    $kdf = $kdmb->find($data);
                }

                if ($weight <= $kdf['data']['first_num']) {
                    $price = $kdf['data']['first_price'];
                } else {
                    $num1 = ($weight - $kdf['data']['first_num']) / $kdf['data']['expand_num'];
                    $num2 = ($weight - $kdf['data']['first_num']) % $kdf['data']['expand_num'];
                    if ($num2 != 0) {
                        $num1 = $num1 + 1;
                    }
                    $price = $kdf['data']['first_price'] + ($num1 * $kdf['data']['expand_price']);
                }
                return result(200, "请求成功", round($price));
            } else if ($type == 3) {
                //寄距离
                $contactModel = new ContactModel();
                $params['id'] = $id;
                $address = $contactModel->find($params);
                if ($address['status'] != 200) {
                    return $address;
                }
                $appAccessModel = new AppAccessModel();
                $merchan_info = $appAccessModel->find(['`key`' => yii::$app->session['key']]);
                if ($merchan_info['status'] != 200) {
                    return $merchan_info;
                }
                $origin = bd_amap($address['data']['longitude'] . "," . $address['data']['latitude']);//出发地
                $destination = bd_amap($merchan_info['data']['coordinate']);//目的地
                $juli = 0;
                $yunfei = 0;
                $url = "https://restapi.amap.com/v3/distance?key=bc55956766e813d3deb1f95e45e97d73&origins={$origin}&destination={$destination}&type=0";
                $result = json_decode(curlGet($url), true);

                if ($result['status'] == 1) {
                    $juli = $result['results'][0]['distance'] / 1000;
                } else {
                    return result(500, '请求失败，距离计算错误');
                }
                $express = $templateModel->find(['shop_express_template_id' => $temp['data']['id']]);

                if ($express['status'] != 200) {
                    return $express;
                }
                $fw = json_decode($express['data']['distance'], true);
                //{"start_number":["0","4"],"end_number":["3","6"],"freight":["6","11"]}
                for ($i = 0; $i < count($fw['start_number']); $i++) {
                    if ($fw['start_number'][$i] < $juli && $fw['end_number'][$i] > $juli) {
                        $yunfei = $fw['freight'][$i];
                    }
                }
                return result(200, "请求成功", round($yunfei));
            }
        }
    }

    public function actionSkdf($id)
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取body传参
            $model = new ShopExpressTemplateModel();
            $number = $params['number'];
            $weight = $params['weight'];
            $supplierId = $params['supplier_id'];
            unset($params['supplier_id']);

            $temp = $model->find(['status' => 1, 'supplier_id' => $supplierId, 'merchant_id' => yii::$app->session['merchant_id'], '`key`' => yii::$app->session['key']]);
            if ($temp['status'] != 200) {
                return $temp;
            }

            $ids = json_decode($params['ids'], true);
            if (count($ids) == 1) {
                $goodsModel = new ShopGoodsModel();
                $goods = $goodsModel->do_one(['id' => $ids[0]]);
                if ($goods['status'] == 200) {
                    if ($goods['data']['is_parcel'] == 1) {
                        return result(200, "请求成功", '0');
                    }
                }
            }

            $type = $temp['data']['type'];
            $templateModel = new ShopExpressTemplateDetailsModel();
            //寄件 寄重
            if ($type == 1) {
                $model = new ContactModel();
                $params['id'] = $id;
                $params['`key`'] = yii::$app->session['key'];
                $params['user_id'] = yii::$app->session['user_id'];
                $tempModel = new ShopExpressTemplateModel();
                $data['merchant_id'] = yii::$app->session['merchant_id'];
                $data['supplier_id'] = $supplierId;
                $data['`key`'] = yii::$app->session['key'];
                $data['status'] = 1;
                $temp = $tempModel->find($data);
                if ($temp['status'] != 200) {
                    return result(500, "快递费获取失败");
                }
                $address = $model->find($params);
                if ($address['status'] != 200) {
                    return result(500, "快递费获取失败");
                }
                $price = 0;
                $kdmb = new ShopExpressTemplateDetailsModel();

                unset($params['id']);
                $data['searchName'] = $address['data']['province'];
                $data['merchant_id'] = yii::$app->session['merchant_id'];
                $data['supplier_id'] = $supplierId;
                $data['`key`'] = yii::$app->session['key'];
                $data['shop_express_template_id'] = $temp['data']['id'];
                $data['status'] = 1;
                if ($address['status'] == 200) {
                    $data['searchName'] = $address['data']['province'];
                    $kdf = $kdmb->find($data);
                } else {
                    $params['searchName'] = "全国统一运费";
                    $kdf = $kdmb->find($data);
                }
                if ($kdf['status'] != 200) {
                    $data['searchName'] = "全国统一运费";
                    $kdf = $kdmb->find($data);
                    $price = $kdf['data']['expand_price'];
                }
                $price = $kdf['data']['first_price'] + (($number - 1) * $kdf['data']['expand_price']);
                $price = $price == 0 ? "0" : $price;
                return result(200, "请求成功", round($price));
            } else if ($type == 2) {
                $model = new ContactModel();
                $params['id'] = $id;
                $params['`key`'] = yii::$app->session['key'];
                $params['user_id'] = yii::$app->session['user_id'];
                $tempModel = new ShopExpressTemplateModel();
                $data['merchant_id'] = yii::$app->session['merchant_id'];
                $data['supplier_id'] = $supplierId;
                $data['`key`'] = yii::$app->session['key'];
                $data['status'] = 1;
                $temp = $tempModel->find($data);
                if ($temp['status'] != 200) {
                    return result(500, "快递费获取失败");
                }
                $address = $model->find($params);
                $price = 0;
                $kdmb = new ShopExpressTemplateDetailsModel();

                unset($params['id']);
                $data['searchName'] = $address['data']['province'];
                $data['merchant_id'] = yii::$app->session['merchant_id'];
                $data['supplier_id'] = $supplierId;
                $data['`key`'] = yii::$app->session['key'];
                $data['shop_express_template_id'] = $temp['data']['id'];
                $data['status'] = 1;
                if ($address['status'] == 200) {
                    $data['searchName'] = $address['data']['province'];
                    $kdf = $kdmb->find($data);
                } else {
                    $params['searchName'] = "全国统一运费";
                    $kdf = $kdmb->find($data);
                }
                if ($kdf['status'] != 200) {
                    $data['searchName'] = "全国统一运费";
                    $kdf = $kdmb->find($data);
                }
                if ($weight <= $kdf['data']['first_num']) {
                    $price = $kdf['data']['first_price'];
                } else {
                    $num1 = ($weight - $kdf['data']['first_num']) / $kdf['data']['expand_num'];
                    $num2 = ($weight - $kdf['data']['first_num']) % $kdf['data']['expand_num'];
                    if ($num2 != 0) {
                        $num1 = $num1 + 1;
                    }
                    $price = $kdf['data']['first_price'] + ($num1 * $kdf['data']['expand_price']);
                }
                return result(200, "请求成功", round($price));
            } else if ($type == 3) {
                //寄距离
                $contactModel = new ContactModel();
                $params['id'] = $id;
                $address = $contactModel->find($params);
                if ($address['status'] != 200) {
                    return $address;
                }
                $leaderModel = new LeaderModel();
                $leaderWhere['supplier_id'] = $supplierId;
                $leaderInfo = $leaderModel->do_one($leaderWhere);
                if ($leaderInfo['status'] != 200) {
                    return result(500, "未查询到门店信息");
                }
                $origin = bd_amap($address['data']['longitude'] . "," . $address['data']['latitude']);//出发地
                $destination = bd_amap($leaderInfo['data']['longitude'] . "," . $leaderInfo['data']['latitude']);//目的地
                $juli = 0;
                $yunfei = 0;
                $url = "https://restapi.amap.com/v3/distance?key=bc55956766e813d3deb1f95e45e97d73&origins={$origin}&destination={$destination}&type=0";
                $result = json_decode(curlGet($url), true);

                if ($result['status'] == 1) {
                    $juli = $result['results'][0]['distance'] / 1000;
                } else {
                    return result(500, '请求失败，距离计算错误');
                }
                $express = $templateModel->find(['shop_express_template_id' => $temp['data']['id']]);

                if ($express['status'] != 200) {
                    return $express;
                }
                $fw = json_decode($express['data']['distance'], true);
                //{"start_number":["0","4"],"end_number":["3","6"],"freight":["6","11"]}
                for ($i = 0; $i < count($fw['start_number']); $i++) {
                    if ($fw['start_number'][$i] < $juli && $fw['end_number'][$i] > $juli) {
                        $yunfei = $fw['freight'][$i];
                    }
                }
                return result(200, "请求成功", round($yunfei));
            }
        }
    }

}
