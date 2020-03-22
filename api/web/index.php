<?php
$allow_origin_arr = array(
    'http://api.juanpao.com',
    'https://api.juanpao.com',
    'http://web.juanpao.com',
    'https://web.juanpao.com',
    'http://api2.juanpao.com',
    'https://api2.juanpao.com',
    'http://web2.juanpao.com',
    'https://web2.juanpao.com',
    'http://test.uicut.com',
    'http://192.168.188.236',
    'http://192.168.188.128',
    'https://192.168.188.236',
    'http://192.168.188.61:8080',
    'https://192.168.188.61:8080',
    'http://localhost:8080',
    'https://localhost:8080',
    'http://192.168.188.71:8080',
    'https://192.168.188.71:8080',
    'http://192.168.188.71',
    'https://192.168.188.71',
    'http://www.weikejs.com',
    'http://weikejs.com',
    'http://106.54.11.57:9528',
    'https://192.168.80.1:8080',
    'http://shop.juanpao.com',
    'http://partner.juanpao.com',
    'http://supplier.juanpao.com',
);
$cur_origin = empty($_SERVER['HTTP_ORIGIN']) ? '' : $_SERVER['HTTP_ORIGIN'];
if (empty($_SERVER['HTTP_ORIGIN'])) {
    $cur_origin = $_SERVER['SERVER_PORT'] == 80 ? 'http://' . $_SERVER['SERVER_NAME'] : 'https://' . $_SERVER['SERVER_NAME'];
}
$allow_orign = in_array($cur_origin, $allow_origin_arr) ? $cur_origin : '';

header('Access-Control-Allow-Origin: ' . $allow_orign);
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers:Access_Token,Access-Token');
header('Access-Control-Allow-Methods:GET,POST,OPTIONS,PUT,DELETE');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/web.php';

(new yii\web\Application($config))->run();
