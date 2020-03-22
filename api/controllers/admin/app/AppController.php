<?php

namespace app\controllers\admin\app;

use yii;
use yii\web\CommonController;
use yii\db\Exception;
use app\models\admin\app\AppModel;
use app\models\core\Base64Model;
use app\models\core\CosModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class AppController extends CommonController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * 地址:/admin/group/index 默认访问
     * @throws Exception if the model cannot be found
     * @return array
     */

    public function actionIndex() {
//        $uid = $_SESSION['uid'];//获取当前登录的用户 id，如果需要的话
        $request = yii::$app->request; //获取 request 对象
        $method = $request->getMethod(); //获取请求方式 GET POST PUT DELETE
        if ($method == 'GET') {
            $params = $request->get(); //获取地址栏参数
        } else {
            $params = $request->bodyParams; //获取body传参
        }

        $app = new AppModel();
        $base = new Base64Model();
        switch ($method) {
            case 'GET':
                if (!isset($params['searchName'])) {
                    $array = ['status' => 400, 'message' => '缺少参数 searchName',];
                    return json_encode($array, JSON_UNESCAPED_UNICODE);
                }
                if ($params['searchName'] == "list") {
                    $array = $app->findall($params);
                } else if ($params['searchName'] == "single") {
                    $array = $app->find($params);
                } else {
                    $array = ['status' => 501, 'message' => '无该 searchName 请求',];
                    return json_encode($array, JSON_UNESCAPED_UNICODE);
                }
                break;
            case 'POST':
                //设置类目 参数
                $must = ['category_id', 'app_name', 'app_pic_url', 'app_type'];

//                $params['app_key'] = $this->generateCode();
//
//                $arr = $app->findall($params);
//
//                if ($arr['status'] == 200) {
//                    for ($i = 0; $i < count($arr['data']); $i++) {
//                        $list[$i] = $arr['data'][$i]['key'];
//                    }
//                    $params['app_key'] = $this->generateCode(1, $list, 6, '');
//                } else if ($arr['status'] == 204) {
//                    $params['app_key'] = $this->generateCode(1, '', 6, '');
//                }

                $rs = $this->checkInput($must, $params);
                if ($rs != false) {
                    return json_encode($rs, JSON_UNESCAPED_UNICODE);
                }

//                $category = new CategoryModel();
//                $data1['name'] = $params['category_name'];
//                if (isset($params['category_detail_info'])) {
//                    $data1['detail_info'] = $params['category_detail_info'];
//                }
//                if (isset($params['category_status'])) {
//                    $data1['status'] = $params['category_status'];
//                }
//                $cid = $category->add($data1);
                //设置app 参数
                $data2 = [
                    'name' => $params['app_name'],
                    'category_id' => $params['category_id'],
                    'pic_url' => $base->base64_image_content($params['app_pic_url'], "./uploads/app"),
                    'detail_info' => isset($params['app_detail_info']) ? $params['app_detail_info'] : "",
                    //   '`key`' => $params['app_key'],
                    'type' => $params['app_type'],
                    'parent_id' => isset($params['app_parent_id']) ? $params['app_parent_id'] : "",
                    'status' => isset($params['app_status']) ? $params['app_status'] : "",
                    'create_time' => time(),
                ];
                $array = $app->add($data2);

                break;
            case 'PUT':
                if (!isset($params['id'])) {
                    $array = ['status' => 400, 'message' => '缺少参数 id',];
                } else {
                    $data = [
                        'id' => $params['id'],
                        'name' => $params['app_name'],
                        'category_id' => $params['category_id'],
                        'pic_url' => '',
                        'detail_info' => isset($params['app_detail_info']) ? $params['app_detail_info'] : "",
                        'type' => $params['app_type'],
                        'parent_id' => isset($params['app_parent_id']) ? $params['app_parent_id'] : "",
                        'status' => isset($params['app_status']) ? $params['app_status'] : "",
                        'update_time' => time(),
                    ];
                    if (isset($params['app_pic_url'])) {
                        $params['pic_url'] = $base->base64_image_content($params['app_pic_url'], "./uploads/app");
                    }
                    $array = $app->update($data);
                }
                break;
            case 'DELETE':
                if (!isset($params['id'])) {
                    $array = ['status' => 400, 'message' => '缺少参数 id',];
                } else {
                    $array = $app->delete($params);
                }
                break;
            default:
                return json_encode(['status' => 404, 'message' => 'ajax请求类型错误，找不到该请求',], JSON_UNESCAPED_UNICODE);
        }
        return $array;
    }

    public function generateCode($nums = 1, $exist_array = '', $code_length = 6, $prefix = '') {

        $characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnpqrstuvwxyz";
        $promotion_codes = array(); //这个数组用来接收生成的优惠码
        for ($j = 0; $j < $nums; $j++) {
            $code = '';
            for ($i = 0; $i < $code_length; $i++) {

                $code .= $characters[mt_rand(0, strlen($characters) - 1)];
            }
            //如果生成的4位随机数不再我们定义的$promotion_codes数组里面
            if (!in_array($code, $promotion_codes)) {
                if (is_array($exist_array)) {

                    if (!in_array($code, $exist_array)) {//排除已经使用的优惠码
                        $promotion_codes[$j] = $prefix . $code; //将生成的新优惠码赋值给promotion_codes数组
                    } else {

                        $j--;
                    }
                } else {
                    $promotion_codes[$j] = $prefix . $code; //将优惠码赋值给数组
                }
            } else {
                $j--;
            }
        }
        return $promotion_codes[0];
    }

    public function actionList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $app = new AppModel();
            $array = $app->findall($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $app = new AppModel();
            $params['id'] = $id;
            $array = $app->find($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $app = new AppModel();
            $base = new Base64Model();
            //设置类目 参数
            $must = ['category_id', 'name', 'pic_url', 'type'];

//            $params['key'] = $this->generateCode();
//
//            $arr = $app->findall($params);
//
//            if ($arr['status'] == 200) {
//                for ($i = 0; $i < count($arr['data']); $i++) {
//                    $list[$i] = $arr['data'][$i]['key'];
//                }
//                $params['key'] = $this->generateCode(1, $list, 6, '');
//            } else if ($arr['status'] == 204) {
//                $params['key'] = $this->generateCode(1, '', 6, '');
//            }

            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return json_encode($rs, JSON_UNESCAPED_UNICODE);
            }
            $str = creat_mulu("./uploads/app");

            $localRes = $base->base64_image_content($params['pic_url'], $str);
            if (!$localRes) {
                return result(500, "图片格式错误");
            }
            //将图片上传到cos
            $cos = new CosModel();
            $cosRes = $cos->putObject($localRes);
            if ($cosRes['status'] == '200') {
                $url = $cosRes['data'];
            } else {
                unlink(Yii::getAlias('@webroot/') . $localRes);
                return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
            }
            //设置app 参数
            $data2 = [
                'name' => $params['name'],
                'category_id' => $params['category_id'],
                'pic_url' => $url,
                'detail_info' => isset($params['detail_info']) ? $params['detail_info'] : "",
                //   '`key`' => $params['key'],
                'type' => $params['type'],
                'parent_id' => isset($params['parent_id']) ? $params['parent_id'] : "",
                'status' => isset($params['status']) ? $params['status'] : "",
                'create_time' => time(),
            ];

            $array = $app->add($data2);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $app = new AppModel();
            $base = new Base64Model();
            $params['id'] = $id;
            if (!isset($params['id'])) {
                result(400, "缺少参数 id");
            } else {
//                $data = [
//                    'id' => $params['id'],
//                    'name' => $params['name'],
//                    'category_id' => $params['category_id'],
//                    'pic_url' => '',
//                    'detail_info' => isset($params['detail_info']) ? $params['detail_info'] : "",
//                    'type' => $params['type'],
//                    'parent_id' => isset($params['parent_id']) ? $params['parent_id'] : "",
//                    'status' => isset($params['status']) ? $params['status'] : "",
//                    'update_time' => time(),
//                ];
                if (isset($params['pic_url'])) {
                    if ($params['pic_url'] != "") {
                        $str = creat_mulu("./uploads/app");
                        $params['pic_url'] = $base->base64_image_content($params['pic_url'], $str);
                        //将图片上传到cos
                        $cos = new CosModel();
                        $cosRes = $cos->putObject($params['pic_url']);
                        if ($cosRes['status'] == '200') {
                            $url = $cosRes['data'];
                        } else {
                            unlink(Yii::getAlias('@webroot/') . $params['pic_url']);
                            return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
                        }
                        $params['pic_url'] = $url;
                    } else {
                        unset($params['pic_url']);
                    }
                }
                $array = $app->update($params);
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
            $app = new AppModel();
            $params['id'] = $id;
            $array = $app->delete($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }
    public function actionUpdates(){
        define('DOWN_WAY', 1); //1表示下载到服务器 2表示下载到本地
        //必须存在data/version.php data/xzlic.php
        $version = include './data/version.php';
        $sqkey = '';
        if(is_file('./data/xzlic.php')){
            $xzlic = include './data/xzlic.php';
            $sqkey = $xzlic['sqkey'];
        }
        $download_type = DOWN_WAY == 1 ? 'download' : 'download_v2';
        $apiurl = 'http://shouquanjs.juanpao.com/check.php';
        $ac = isset($_GET['a']) ? trim($_GET['a']) : 'check';
        $host = $_SERVER['HTTP_HOST'];
        $hosts = $host . '|' . $_SERVER['SERVER_NAME'];
        $host_url = 'http://' . $host . '/';
        $time = time();
        $token = md5($time . '|' . $hosts . '|xzphp');
        $apiurl.= '?h=' . $hosts . '&t=' . $time . '&token=' . $token . '&v=' . $version.'&sqkey='.$sqkey;

        if ($ac == 'check') {
            $url = $apiurl . '&a=upgrade';
            $html = file_get_contents($url);
            if (!$html) {
                message('请求失败，请刷新试试!' . $apiurl);
            }
            $data = json_decode($html, true);
            if ($data['status'] != 1) {
                message($data['msg'],0,array('notice'=>$data['data']['notice']));
            }
            $dtoken = $data['data']['dtoken'];
            $download_url = $host_url . 'upgrade.php?a=' . $download_type . '&dtoken=' . $dtoken;
             $this-> message('您当前版本'.$version.',系统最新版本' . $data['data']['version'] . ',请点击这里下载更新',1,array('note'=>$data['data']['note'],'notice'=>$data['data']['notice']));

        } elseif ($ac == 'download') {
            //直接下载更新包保存到服务器
            $dtoken = isset($_GET['dtoken']) ? trim($_GET['dtoken']) : '';
            if (!$dtoken) {
                exit('非法请求!');
            }
            $url = $apiurl . '&a=download&dtoken=' . $dtoken;
            $headers = get_headers($url, 1);
            if (strpos($headers['Content-Type'],'text/html') !== false) {
                $html = file_get_contents($url);
                $data = json_decode($html, true);
                exit($data['msg']);
            }
            $upgrade_file =$this->get_file($url);
            echo '下载最新版本更新包成功：' . $upgrade_file;
            exit;
        } elseif ($ac == 'download_v2') {
            //浏览器下载更新包到用户本地
            $dtoken = isset($_GET['dtoken']) ? trim($_GET['dtoken']) : '';
            if (!$dtoken) {
                exit('非法请求!');
            }
            $url = $apiurl . '&a=download&dtoken=' . $dtoken;
            $headers = get_headers($url, 1);
            if (strpos($headers['Content-Type'],'text/html') !== false) {
                $html = file_get_contents($url);
                $data = json_decode($html, true);
                exit($data['msg']);
            }
            ob_end_clean();
            $filename = date('Ymd') . '_' . rand(100, 100000) . uniqid() . '.zip';
            header("Cache-Control: max-age=0");
            header("Content-Description: File Transfer");
            header('Content-disposition: attachment; filename=' . $filename);
            header("Content-Type: application/zip");
            header("Content-Transfer-Encoding: binary");
//header ( 'Content-Length: ' . filesize ( $file));
            readfile($url);
            flush();
            ob_flush();
            exit;
        }

    }

    function message($msg,$status = 0, $data = array()){
        exit(json_encode(array('status'=>$status,'msg'=>$msg,'data'=>$data)));
    }


    function get_file($url, $folder = './data/upgradex/') {
        set_time_limit(24 * 60 * 60);
        $target_dir = $folder . '';
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $newfname = date('Ymd') . rand(1000, 10000000) . uniqid() . '.zip';
        $newfname = $target_dir . $newfname;
        $file = fopen($url, "rb");
        if ($file) {
            $newf = fopen($newfname, "wb");
            if ($newf) while (!feof($file)) {
                fwrite($newf, fread($file, 1024 * 8) , 1024 * 8);
            }
        }
        if ($file) {
            fclose($file);
        }
        if ($newf) {
            fclose($newf);
        }
        return $newfname;
    }



}
