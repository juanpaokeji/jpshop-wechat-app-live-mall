<?php

header("Content-type: text/html; charset=utf-8");
//开启session
session_start();

//配置信息
$config = include './config.php';
if(empty($config)){
    exit(get_tip_html('安装配置信息不存在，无法继续安装！'));
}
//安装环境验证，获取相应判断信息
if(function_exists('saeAutoLoader')){
    //新浪SAE
    define('INSTALLTYPE', 'SAE');
    require './sae.php';
}elseif(isset($_SERVER['HTTP_BAE_ENV_APPID'])){
    //百度BAE
    define('INSTALLTYPE', 'BAE');
    require './bae.php';
}else{
    define('INSTALLTYPE', 'HOST');
    //本地
    require './localhost.php';
}

//限制最大的执行时间
set_time_limit(1000);
//php版本
$phpversion = phpversion();
//php版本过低提示
if($phpversion < '7.1.0'){
    exit(get_tip_html('您的php版本过低，不能安装本软件，请升级到5.2.0或更高版本再安装，谢谢！'));
}
//数据库文件
if(!file_exists('./'.$config['sqlFileName'])){
    exit(get_tip_html('数据库文件不存在，无法继续安装！'));
}
//写入数据库完成后处理的文件
if (!file_exists('./'.$config['handleFile'])) {
    exit(get_tip_html('处理文件不存在，无法继续安装！'));
}
//设置报错级别并返回当前级别。
error_reporting(E_ALL & ~E_NOTICE);
//安装步骤
$steps = array(
    '1' => '安装许可协议',
    '2' => '运行环境检测',
    '3' => '安装参数设置',
    '4' => '安装详细过程',
    '5' => '安装完成',
);
$step = isset($_GET['step']) ? $_GET['step'] : 1;
//当前安装步骤
$step_html = '';
foreach ($steps as $key => $value) {
    $current = $key == $step? 'current':'';
    $step_html .= '<li class="'.$current.'"><em>'.$key.'</em>'.$value.'</li>';
}
//安装页面
switch ($step) {
    //安装许可协议
    case '1':
        $license = file_get_contents('./license.txt');
        include ("./templates/1.php");
        break;
    //运行环境检测
    case '2':
        $server = array(
            //操作系统
            'os' => php_uname(),
            //PHP版本
            'php' => $phpversion,
        );
        $error = 0;
        //数据库
        if (function_exists('mysqli_connect')) {
            $server['mysql'] = '<span class="correct_span">&radic;</span> 已安装';
        } else {
            $server['mysql'] = '<span class="correct_span error_span">&radic;</span> 出现错误';
            $error++;
        }
        //上传限制
        if (ini_get('file_uploads')) {
            $server['uploadSize'] = '<span class="correct_span">&radic;</span> ' . ini_get('upload_max_filesize');
        } else {
            $server['uploadSize'] = '<span class="correct_span error_span">&radic;</span>禁止上传';
        }
        //session
        if (function_exists('session_start')) {
            $server['session'] = '<span class="correct_span">&radic;</span> 支持';
        } else {
            $server['session'] = '<span class="correct_span error_span">&radic;</span> 不支持';
            $error++;
        }

        if (class_exists('Redis')) {
            $server['redis'] = '<span class="correct_span">&radic;</span> 支持';
        } else {
            $server['redis'] = '<span class="correct_span error_span">&radic;</span> 不支持';
            $error++;
        }
        //需要读写权限的目录
        $folder = $config['dirAccess'];
        $install_path = str_replace('\\','/',getcwd()).'/';
        $site_path = str_replace('Install/', '', $install_path);
        include ("./templates/2.php");
        $_SESSION['INSTALLSTATUS'] = $error == 0?'SUCCESS':$error;
        break;
    //安装参数设置
    case '3':
        verify(3);
        //测试数据库链接
        if (isset($_GET['testdbpwd'])) {
            empty($_POST['dbhost'])?alert(0,'数据库服务器地址不能为空！','dbhost'):'';
            empty($_POST['dbuser'])?alert(0,'数据库用户名不能为空！','dbuser'):'';
            empty($_POST['dbname'])?alert(0,'数据库名不能为空！','dbname'):'';
            empty($_POST['dbport'])?alert(0,'数据库端口不能为空！','dbport'):'';
            $dbHost = $_POST['dbhost'] . ':' . $_POST['dbport'];
            $conn = mysqli_connect($dbHost, $_POST['dbuser'], $_POST['dbpw']);
            $conn?alert(1,'数据库链接成功！','dbpw'):alert(0,'数据库链接失败！','dbpw');
        }
        //域名+路径
        $domain = empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
        if ((int) $_SERVER['SERVER_PORT'] != 80) {
            $domain .= ":" . $_SERVER['SERVER_PORT'];
        }
        $scriptName = !empty($_SERVER["REQUEST_URI"]) ? $scriptName = $_SERVER["REQUEST_URI"] : $scriptName = $_SERVER["PHP_SELF"];
        $rootpath = preg_replace("/\/(I|i)nstall\/index\.php(.*)$/", "", $scriptName);
        $domain = $domain . $rootpath;
        include ("./templates/3.php");
        break;
    //安装详细过程
    case '4':
        if (!isset($_GET['install'])){
            switch (INSTALLTYPE){
                case 'SAE':
                    // 服务器地址
                    $_POST['dbhost'] = SAE_MYSQL_HOST_M;
                    // 端口
                    $_POST['dbport'] = SAE_MYSQL_PORT;
                    // 数据库名
                    $_POST['dbname'] = SAE_MYSQL_DB;
                    // 用户名
                    $_POST['dbuser'] = SAE_MYSQL_USER;
                    // 密码
                    $_POST['dbpw'] = SAE_MYSQL_PASS;
                    break;
                case 'BAE':
                    // 服务器地址
                    $_POST['dbhost'] = HTTP_BAE_ENV_ADDR_SQL_IP;
                    // 端口
                    $_POST['dbport'] = HTTP_BAE_ENV_ADDR_SQL_PORT;
                    // 用户名
                    $_POST['dbuser'] = HTTP_BAE_ENV_SK;
                    // 密码
                    $_POST['dbpw'] = SAE_MYSQL_PASS;
                    break;
            }
        }
        verify(4);
        if (intval($_GET['install'])) {
            dataVerify();
            //关闭特殊字符提交处理到数据库
            if($phpversion <= '5.3.0'){
                set_magic_quotes_runtime(0);
            }
            //设置时区
            date_default_timezone_set('PRC');
            //当前进行的数据库操作
            $n = intval($_GET['n']);
            $arr = array();
            //数据库服务器地址
            $dbHost = trim($_POST['dbhost']);
            //数据库端口
            $dbPort = trim($_POST['dbport']);
            //数据库名
            $dbName = trim($_POST['dbname']);
            $dbHost = empty($dbPort) || $dbPort == 3306 ? $dbHost : $dbHost . ':' . $dbPort;
            //数据库用户名
            $dbUser = trim($_POST['dbuser']);
            //数据库密码
            $dbPwd = trim($_POST['dbpw']);
            //表前缀
            $dbPrefix = empty($_POST['dbprefix']) ? 'db_' : trim($_POST['dbprefix']);
            //链接数据库
            $conn =  mysqli_connect($dbHost, $dbUser, $dbPwd);
            if (!$conn) {
                alert(0,'连接数据库失败!');
            }
            //设置数据库编码
            mysqli_query($conn,"SET NAMES 'utf8'"); //,character_set_client=binary,sql_mode='';
            //获取数据库版本信息
            $version = mysqli_get_server_info($conn);
            if ($version < 4.1) {
                alert(0,'连接数版本太低!');
            }
            //选择数据库
            if (!mysqli_select_db($conn, $dbName)) {
                //创建数据时同时设置编码
                if (!mysqli_query($conn,"CREATE DATABASE IF NOT EXISTS `" . $dbName . "` DEFAULT CHARACTER SET utf8;")) {
                    alert(0,'<li><span class="correct_span error_span">&radic;</span>数据库 ' . $dbName . ' 不存在，也没权限创建新的数据库！<span style="float: right;">'.date('Y-m-d H:i:s').'</span></li>');
                } else {
                    alert(1,"<li><span class='correct_span'>&radic;</span>成功创建数据库:{$dbName}<span style='float: right;''>".date('Y-m-d H:i:s')."</span></li>",0);
                }
            }

            //读取数据文件
            $sqldata = file_get_contents('./'.$config['sqlFileName']);
            if(empty($sqldata)){
                alert(0,'数据库文件不能为空！');
            }
            $sqlFormat = sql_split($sqldata, $dbPrefix,$config['dbPrefix']);


            /**
             * 执行SQL语句
             */
            $counts = count($sqlFormat);
			$insert_sql= "";
            for ($i = $n; $i < $counts; $i++) {
                $sql = trim($sqlFormat[$i]);
				
                if (strstr($sql, 'CREATE TABLE')||strstr($sql, 'INSERT INTO')) {
                    //创建表
                    preg_match('/CREATE TABLE `([^ ]*)`/', $sql, $matches);
                    preg_match('/INSERT INTO `([^ ]*)`/', $sql, $insert);
                    if(empty($matches)){
                        preg_match('/CREATE TABLE IF NOT EXISTS `([^ ]*)`/', $sql, $matches);
                    }
                    if(empty($insert)){
                        preg_match('/INSERT INTO `([^ ]*)`/', $sql, $insert);
                    }
                    if(!empty($matches[1])){
                        //mysqli_query($conn,"DROP TABLE IF EXISTS `$matches[1]");
                        //	$ret = mysqli_query($conn,$sql);
                        //var_dump($ret);
                        $i++;
                        if(mysqli_query($conn,$sql)){
                            $info = '<li><span class="correct_span">&radic;</span>创建数据表' . $matches[1] . '，完成！<span style="float: right;">'.date('Y-m-d H:i:s').'</span></li> ';
                            alert(1,$info,$i);
                        } else {
                            $info = '<li><span class="correct_span error_span">&radic;</span>创建数据表' . $matches[1] . '，失败，安装停止！<span style="float: right;">'.date('Y-m-d H:i:s').'</span></li>';
                            alert(0,$info,$i);
                        }
                    }
                    if(!empty($insert[1])){
                        $i++;
						$insert_sql =$insert_sql.$sql; 

                        if(mysqli_query($conn,$sql)){
                            $info = '<li><span class="correct_span">&radic;</span>新增数据表' . $insert[1] . '，完成！<span style="float: right;">'.date('Y-m-d H:i:s').'</span></li> ';
                            alert(1,$info,$i);
                        } else {
                            $info = '<li><span class="correct_span error_span">&radic;</span>新增数据表' . $insert[1] . '，失败，安装停止！<span style="float: right;">'.date('Y-m-d H:i:s').'</span></li>';
                            alert(0,$info,$i);
                        }
                    }
                }
            }
            //处理
            $data = include './'.$config['handleFile'];
            $_SESSION['INSTALLOK'] = $data['status']?1:0;
            alert($data['status'],$data['info']);
        }
        include ("./templates/4.php");
        break;
    //安装完成
    case '5':
        verify(5);
        include ("./templates/5.php");
        //安装完成,生成.lock文件
        if(isset($_SESSION['INSTALLOK']) && $_SESSION['INSTALLOK'] == 1){
            filewrite($config['installFile']);
        }
        unset($_SESSION);
        break;
}

/**
 * 错误提示html
 */
function get_tip_html($info){
    return '<div style="border: 2px solid #69c; background:#f1f1f1; padding:20px;margin:20px;width:800px;font-weight:bold;color: #69c;text-align:center;margin-left: auto;margin-right: auto;border-radius: 5px;"><h1>'.$info.'</h1></div>';
}
//返回提示信息
function alert($status,$info,$type = 0){
    exit(json_encode(array('status'=>$status,'info'=>$info,'type'=>$type)));
}
function verify($step = 3){
    if($step >= 3){
        //未运行环境检测，跳转到安装许可协议页面
        if(!isset($_SESSION['INSTALLSTATUS'])){
            header('location:./index.php');
            exit();
        }
        //运行环境检测存在错误，返回运行环境检测
        if($_SESSION['INSTALLSTATUS'] != 'SUCCESS'){
            header('location:./index.php?step=2');
            exit();
        }
    }
    if($step == 4){
        //未提交数据
        if(empty($_POST)){
            header('location:./index.php?step=3');
            exit();
        }
    }
    if($step >= 5){
        //数据库未写入完成
        if(!isset($_SESSION['INSTALLOK'])){
            header('location:./index.php?step=4');
            exit();
        }
    }
}
function dataVerify(){
    empty($_POST['dbhost'])?alert(0,'数据库服务器不能为空！'):'';
    empty($_POST['dbport'])?alert(0,'数据库端口不能为空！'):'';
    empty($_POST['dbuser'])?alert(0,'数据库用户名不能为空！'):'';
    empty($_POST['dbname'])?alert(0,'数据库名不能为空！'):'';
    //empty($_POST['dbprefix'])?alert(0,'数据库表前缀不能为空！'):'';
    //empty($_POST['siteurl'])?alert(0,'网站域名不能为空！'):'';
    //empty($_POST['uploaddir'])?alert(0,'附件上传的目录不能为空！'):'';
    empty($_POST['manager'])?alert(0,'管理员帐号不能为空！'):'';
    empty($_POST['manager_pwd'])?alert(0,'管理员密码不能为空！'):'';
    //empty($_POST['manager_email'])?alert(0,'管理员邮箱不能为空！'):'';
}
/**
 * 判断目录是否可写
 */
function testwrite($d) {
    $tfile = "_test.txt";
    $fp = fopen($d . "/" . $tfile, "w");
    if (!$fp) {
        return false;
    }
    fclose($fp);
    $rs = unlink($d . "/" . $tfile);
    if ($rs) {
        return true;
    }
    return false;
}
/**
 * 创建目录
 */
function dir_create($path, $mode = 0777) {
    if (is_dir($path)) {
        return TRUE;
    }
    mkdir($path, $mode, true);
    chmod($path, $mode);
}
/**
 * 数据库语句解析
 * @param $sql 数据库
 * @param $newTablePre 新的前缀
 * @param $oldTablePre 旧的前缀
 */
function sql_split($sql, $newTablePre, $oldTablePre) {
    //前缀替换
    if ($newTablePre != $oldTablePre){
        $sql = str_replace($oldTablePre, $newTablePre, $sql);
    }
    $sql = preg_replace("/TYPE=(InnoDB|MyISAM|MEMORY)( DEFAULT CHARSET=[^; ]+)?/", "ENGINE=\\1 DEFAULT CHARSET=utf8", $sql);

    $sql = str_replace("\r", "\n", $sql);
    $ret = array();
    $queriesarray = explode(";\n", trim($sql));
    unset($sql);
    foreach ($queriesarray as $k=>$query) {
        $ret[$k] = '';
        $queries = explode("\n", trim($query));
        $queries = array_filter($queries);
        foreach ($queries as $query) {
            $str1 = substr($query, 0, 1);
            if ($str1 != '#' && $str1 != '-')
                $ret[$k] .= $query;
        }
    }
    return $ret;
}
/**
 * 产生随机字符串
 * 产生一个指定长度的随机字符串,并返回给用户
 * @access public
 * @param int $len 产生字符串的位数
 * @return string
 */
function genRandomString($len = 6) {
    $chars = array(
        "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
        "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
        "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
        "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
        "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
        "3", "4", "5", "6", "7", "8", "9", '!', '@', '#', '$',
        '%', '^', '&', '*', '(', ')'
    );
    $charsLen = count($chars) - 1;
    shuffle($chars);	// 将数组打乱
    $output = "";
    for ($i = 0; $i < $len; $i++) {
        $output .= $chars[mt_rand(0, $charsLen)];
    }
    return $output;
}
/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @return mixed
 */
function get_client_ip($type = 0) {
    $type	   =  $type ? 1 : 0;
    static $ip  =   NULL;
    if ($ip !== NULL) return $ip[$type];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $arr	=   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $pos	=   array_search('unknown',$arr);
        if(false !== $pos) unset($arr[$pos]);
        $ip	 =   trim($arr[0]);
    }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip	 =   $_SERVER['HTTP_CLIENT_IP'];
    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip	 =   $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u",ip2long($ip));
    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}
