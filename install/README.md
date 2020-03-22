### 序言
作为一名php开发工程师，肯定少不了自己开发web系统项目。如果项目是面向大众的，需要他人安装你的产品，不可缺少的就是需要弄个安装向导，这样才能让他们简单轻松的安装你的产品。如果你觉得没必要，觉得写个文档教程就可以，那我想说，你的产品是面向同行人...不过作为程序员，最终我们还是需要学习怎样开发出系统的安装向导，因为这不是有没有用的问题，而是学没学到的问题....

我们都知道，一般系统有没有安装都是通过判断系统中是否有某种文件，有则说明已安装，没有则未安装。而这个文件是安装完成后生成的，所以可以拿来判断。在这里我也是使用判断文件的方式来判断系统是否已安装。但这里有个问题，对于使用新浪SAE来说，由于不支持本地文件写操作，那我们就生成不了文件，这样判断文件是否存在就无效了。而这里的解决方法是将文件生成在新浪的storage，但这里又有个问题，就是生成的操作方式不一样，storage是新浪SAE为开发者提供的分布式文件存储服务，我们只能用它给出的类来生成文件，所以如果系统需要在新浪SAE上完成安装向导的话，则需要判断当前是哪中平台，然后根据不同平台调用不同的方法....

### 开始

#### 目录结构  
install  --------------------------------->安装入口文件夹  
 ├ templates  ------------------------->页面模板文件夹  
 │ ├ images  -------------------------->页面图片文件夹  
 │ │ └ ....  
 │ ├ js  -------------------------------->页面js文件夹  
 │ │ ├ jquery.js  
 │ │ └ validate.js  
 │ ├ css  ------------------------------>页面css文件夹  
 │ │ └ install.css  
 │ ├ 0.php  ---------------------------->获取新浪sae storage 页面  
 │ ├ 1.php  ---------------------------->安装许可协议页面  
 │ ├ 2.php  ---------------------------->运行环境检测页面  
 │ ├ 3.php  ---------------------------->安装参数设置页面  
 │ ├ 4.php  ---------------------------->安装详细过程页面  
 │ ├ 5.php  ---------------------------->安装完成页面  
 │ ├ header.php  --------------------->公共页面头部  
 │ └ footer.php  ---------------------->公共页面尾部  
 ├ config.ini.php  --------------------->数据库配置文件模板  
 ├ config.php  ------------------------>安装配置文件  
 ├ index.php  ------------------------->系统安装入口  
 ├ location.php  ---------------------->本地环境安装，非云平台  
 ├ main.php  -------------------------->当数据写入到数据库后，进行添加管理员，生成配置文件等操作  
 ├ sae.php  --------------------------->新浪sae平台  
 ├ db.sql  ----------------------------->数据库文件  
 └ license.txt  ------------------------->协议文件  

#### 图结构    
![安装步骤图](http://chenhaizan-upload.stor.sinaapp.com/article/image/201310/5253ad80026ab.png)

install文件夹作为安装入口文件存放的地方，因为在安装完成后这个文件夹是可以删除的，所以在开发的时候，这部分需要独立出来，就是删除后不影响系统运行...

#### 步骤
##### 1、当进入安装时，首先运行index.php入口文件

##### 2、然后获取配置信息config.php
```
//配置信息
$config = include './config.php';
if(empty($config)){
	exit(get_tip_html('安装配置信息不存在，无法继续安装！'));
}
```

这里的配置信息的目的是：只需要修改这个文件就能兼容在其他系统上，而不需要修改太多的其他文件
```
return array(
		/* ------系统------ */
		//系统名称
		'name'=>'赞博客，赞生活',
		//系统版本
		'version'=>'1.0',
		//系统powered
		'powered'=>'Powered by chenhaizan.com',
		//系统脚部信息
		'footerInfo'=> 'Copyright © 2012-2013 chenhaizan.cn Corporation',

		/* ------站点------ */
		//数据库文件
		'sqlFileName'=>'db.sql',
		//生成数据库配置文件的模板
		'dbSetFile'=>'config.ini.php',
		//数据库名
		'dbName' => 'myblog',
		//数据库表前缀
		'dbPrefix' => 'haizan_',
		//站点名称
		'siteName' => '我的博客',
		//站点关键字
		'siteKeywords' => '我的博客',
		//站点描述
		'siteDescription' => '我的博客',
		//附件上传的目录
		'uploaddir' => 'upload',
		//需要读写权限的目录
        'dirAccess' => array(
        	'/',
        	'config',
            'upload',
            'template',
            'install',
            'includes/uc_client/data',
        ),
		/* ------写入数据库完成后处理的文件------ */
		'handleFile' => 'main.php',
		/* ------安装验证/生成文件;非云平台安装有效------ */
		'installFile' => '../config/install.lock',
		'alreadyInstallInfo' => '你已经安装过该系统，如果想重新安装，请先删除站点config目录下的 install.lock 文件，然后再尝试安装！',
	);
```


##### 3、然后进行判断当前运行的平台，获取相应的平台文件
```
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
```

如当是本地环境时，加载location.php文件，我们在这个文件中进行是否安装判断等操作
```
//检测是否已经安装
if(file_exists($config['installFile'])){
	exit(get_tip_html($config['alreadyInstallInfo']));
}

//写入文件
function filewrite($file){
	@touch($file);
}
```

当在SAE中，加载sae.php，进行获取storage domain操作，判断安装操作和一些服务是否开启
```
//设置storage的domain
if($_GET['step'] == 0){
	if(empty($_POST['storagedomain'])){
		$step_html = '<li class="current"><em>0</em>Storage设置</li>';
		include './templates/0.php';
		exit;
	} else {
		$_SESSION['STORAGEDOMAIN'] = $_POST['storagedomain'];
		if(!empty($_SESSION['STORAGEDOMAIN'])){
			header('location:./index.php?step=1');
			exit();	
		}
	}
}
if(!isset($_SESSION['STORAGEDOMAIN']) || empty($_SESSION['STORAGEDOMAIN'])){
	header('location:./index.php?step=0');
	exit;
}
$config['uploaddir'] = $_SESSION['STORAGEDOMAIN'];
define('SAESTOR_INSTALL_NAME', $_SESSION['STORAGEDOMAIN'].'/saestor_'. $_SERVER['HTTP_APPVERSION'] . '_install.lock');
$config['alreadySaeInstallInfo'] = "版本" . $_SERVER['HTTP_APPVERSION'] . "已完成安装!请删除网站根目录下的install目录！<br>如果需要重新安装，请先删除storage内的 saestor_" . $_SERVER['HTTP_APPVERSION'] . "_install.lock 文件";
if(fileExists(SAESTOR_INSTALL_NAME)){
	exit(get_tip_html($config['alreadySaeInstallInfo']));
}
if(!is_storage){
	exit(get_tip_html('请开启storage服务！'));
}
if(!is_mc){
	exit(get_tip_html('请开启memcahce服务！'));
}
if(!is_mysql){
	exit(get_tip_html('请开启mysql服务！'));
}
if(!is_kv){
	exit(get_tip_html('请开启KV数据库服务！'));
}

//SaeStorage
function SaeStorage(){
	static $SaeStorage = array();
	if(!isset($SaeStorage['SaeStorage'])){
		$SaeStorage['SaeStorage'] = new SaeStorage();
	}
	return $SaeStorage['SaeStorage'];
}
//domain 路径
function file_getdomainfilepath($filename){
	$arr=explode('/',ltrim($filename,'./'));
	if($arr[count($arr)-1] == ''){
		unset($arr[count($arr)-1]);
	}
	$domain=array_shift($arr);
	$filePath=implode('/',$arr);
	return array('domain'=>$domain,'filepath'=>$filePath);
}
//检查文件是否存在
function fileExists($filename){
	$arr=file_getdomainfilepath($filename);
	return SaeStorage()->fileExists($arr['domain'], $arr['filepath']);
}
//写入文件
function filewrite($file = ''){
	$arr=file_getdomainfilepath(SAESTOR_INSTALL_NAME);
	SaeStorage()->write($arr['domain'], $arr['filepath'],'1');
}


//判断是否开启storage
function is_storage() {
    $s = new SaeStorage();
    if (!$s->write(SAESTOR_NAME, 'is_storage', '1')) {
        return FALSE;
    } else {
        return TRUE;
    }
}
//判断是否开启memcahce
function is_mc() {
    $mmc = @memcache_init();
    if ($mmc) {
        return TRUE;
    } else {
        return FALSE;
    }
}
//判断是否开启mysql
function is_mysql() {
    $mysql = @new SaeMysql();
    $sql = "select database()";
    $data = @$mysql->getData($sql);
    if ($data) {
        return TRUE;
    } else {
        return FALSE;
    }
}
//判断是否开启KV数据库
function is_kv(){
	$kv=new SaeKV();
	if($kv->init()){
		return TRUE;
    } else {
        return FALSE;
    }
}
```

##### 4、然后进行一些配置信息和满足条件的判断
```
//php版本
$phpversion = phpversion();
//php版本过低提示
if($phpversion < '5.2.0'){
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
```

##### 5、进行安装流程步骤
```
$step = isset($_GET['step']) ? $_GET['step'] : 1;
//安装页面
switch ($step) {
	case '1':
	case '2':
	case '3':
	case '4':
	case '5':
}
```

0）、设置stirage
当运行在sae上，首先我们需要得到storage的domain，因为需要判断storage中是否存生成的文件

![20131008131218](http://chenhaizan-upload.stor.sinaapp.com/article/image/201310/5253be39c12c5.jpg)

所以需要页面跳转到0.php，进行domain设置

![20131008131431](http://chenhaizan-upload.stor.sinaapp.com/article/image/201310/5253bee558fbb.jpg)


1）、安装许可协议
```
//安装许可协议
case '1':
	$license = @file_get_contents('./license.txt');
	include ("./templates/1.php");
	break;
```

![20131008132853](http://chenhaizan-upload.stor.sinaapp.com/article/image/201310/5253bf0deb703.jpg)

2）、运行环境检测
```
case '2':
	$server = array(
		//操作系统
		'os' => php_uname(),
		//PHP版本
		'php' => $phpversion,
	);
	$error = 0;
	//数据库
	if (function_exists('mysql_connect')) {
		$server['mysql'] = '<span class="correct_span">√</span> 已安装';
	} else {
		$server['mysql'] = '<span class="correct_span error_span">√</span> 出现错误';
		$error++;
	}
	//上传限制
	if (ini_get('file_uploads')) {
		$server['uploadSize'] = '<span class="correct_span">√</span> ' . ini_get('upload_max_filesize');
	} else {
		$server['uploadSize'] = '<span class="correct_span error_span">√</span>禁止上传';
	}
	//session
	if (function_exists('session_start')) {
		$server['session'] = '<span class="correct_span">√</span> 支持';
	} else {
		$server['session'] = '<span class="correct_span error_span">√</span> 不支持';
		$error++;
	 }
	//需要读写权限的目录
	$folder = $config['dirAccess'];
	$install_path = str_replace('\\','/',getcwd()).'/';
	$site_path = str_replace('install/', '', $install_path);
	include ("./templates/2.php");
	$_SESSION['INSTALLSTATUS'] = $error == 0?'SUCCESS':$error;
	break;
```

检测环境需要记录错误，这里用session保存，如果有错误，将不能进行下一步的安装。在本地环境上

![20131008133626](http://chenhaizan-upload.stor.sinaapp.com/article/image/201310/5253c02cf2939.jpg)

如果在sae上，因为sae已经禁止了本地文件操作，所以没必要检测读写判断，这里通过`INSTALLTYPE`判断进行隐藏

![20131008132912](http://chenhaizan-upload.stor.sinaapp.com/article/image/201310/5253c0c13a80d.jpg)

3）、安装参数设置

```
case '3':
	//验证
	verify(3);
	//测试数据库链接
	if (isset($_GET['testdbpwd'])) {
		empty($_POST['dbhost'])?alert(0,'数据库服务器地址不能为空！','dbhost'):'';
		empty($_POST['dbuser'])?alert(0,'数据库用户名不能为空！','dbuser'):'';
		empty($_POST['dbname'])?alert(0,'数据库名不能为空！','dbname'):'';
		empty($_POST['dbport'])?alert(0,'数据库端口不能为空！','dbport'):'';
		$dbHost = $_POST['dbhost'] . ':' . $_POST['dbport'];
		$conn = @mysql_connect($dbHost, $_POST['dbuser'], $_POST['dbpw']);
		$conn?alert(1,'数据库链接成功！','dbpw'):alert(0,'数据库链接失败！','dbpw');
	}
	//域名+路径
	$domain = empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
	if ((int) $_SERVER['SERVER_PORT'] != 80) {
		$domain .= ":" . $_SERVER['SERVER_PORT'];
	}
	$scriptName = !empty($_SERVER["REQUEST_URI"]) ? $scriptName = $_SERVER["REQUEST_URI"] : $scriptName = $_SERVER["PHP_SELF"];
	$rootpath = @preg_replace("/\/(I|i)nstall\/index\.php(.*)$/", "", $scriptName);
	$domain = $domain . $rootpath;
	include ("./templates/3.php");
	break;
```

在本地环境上

![20131008133856](http://chenhaizan-upload.stor.sinaapp.com/article/image/201310/5253c1b6407a8.jpg)

如果在sae上，因为sae已经将数据库的信息设置为常量，所以这里不需要给出数据库输入框，通过`INSTALLTYPE`判断进行隐藏页面的数据库输入部分，留出表前缀输入框

![20131008132940](http://chenhaizan-upload.stor.sinaapp.com/article/image/201310/5253c1fc6871e.jpg)

4）、安装详细过程
困难的部分就在这一步，涉及到数据库的写入。  
这里设计是提交得到配置信息后，跳转到数据库信息写入页面，因为需要在页面中动态显示创建表的信息，所以需要使用ajax来获取信息。验证数据库连接正确性后，将数据库文件提取出来，进行分割解析，得到数组，然后循环运行每条sql语句，同时判断当前语句是否为创建表，如果是创建表，执行这条语句后，返回ajax信息，带回当前数组key+1参数，前后接收后显示在页面，然后再发送ajax请求，带回key参数，循环到结束。

在发送请求时，也需要验证配置参数，这里将配置信息json在页面上

```
var data = <?php echo json_encode($_POST);?>;
```

通过` $_GET['install'] `判断ajax请求

完整js如下

```
var n=0;
var data = <?php echo json_encode($_POST);?>;
$.ajaxSetup ({ cache: false });
function reloads(n) {
	var url =  "./index.php?step=4&install=1&n="+n;
        $.ajax({
            type: "POST",		
            url: url,
            data: data,
            dataType: 'json',
            success: function(data){
            	$('#loginner').append(data.info);
            	if(data.status == 1){
            		reloads(data.type);
            	}
            	if(data.status == 0){
            		$('#installloading').removeClass('btn_old').addClass('btn').html('继续安装').unbind('click').click(function(){
            			reloads(0);
            		});
            		alert('安装已停止！');
            	}
            	if(data.status == 2){
            		$('#installloading').removeClass('btn_old').addClass('btn').attr('href','./index.php?step=5').html('安装完成...');
            		setTimeout(function(){
            			window.location.href='./index.php?step=5';
            		},5000);
            	}
            }
	});
 }
$(function(){
	 reloads(n);
})
```

当在sae平台时，需要获取在sae上的数据库信息

```
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
```

```
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
            $c @ mysql_connect($dbHost, $dbUser, $dbPwd);
            if (!$conn) {
            	alert(0,'连接数据库失败!');
            }
            //设置数据库编码
            mysql_query("SET NAMES 'utf8'"); //,character_set_client=binary,sql_mode='';
            //获取数据库版本信息
            $version = mysql_get_server_info($conn);
            if ($version < 4.1) {
            	alert(0,'连接数版本太低!');
            }
            //选择数据库
            if (!mysql_select_db($dbName, $conn)) {
                //创建数据时同时设置编码
                if (!mysql_query("CREATE DATABASE IF NOT EXISTS `" . $dbName . "` DEFAULT CHARACTER SET utf8;", $conn)) {
                    alert(0,'<li><span class="correct_span error_span">√</span>数据库 ' . $dbName . ' 不存在，也没权限创建新的数据库！<span style="float: right;">'.date('Y-m-d H:i:s').'</span></li>');
                } else {
                	alert(1,"<li><span class='correct_span'>√</span>成功创建数据库:{$dbName}<span style='float: right;''>".date('Y-m-d H:i:s')."</span></li>",0);
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

            for ($i = $n; $i < $counts; $i++) {
                $sql = trim($sqlFormat[$i]);
                if (strstr($sql, 'CREATE TABLE')) {
                	//创建表
                    preg_match('/CREATE TABLE `([^ ]*)`/', $sql, $matches);
                    if(empty($matches)){
                    	preg_match('/CREATE TABLE IF NOT EXISTS `([^ ]*)`/', $sql, $matches);
                    }
                    if(!empty($matches[1])){
                    	mysql_query("DROP TABLE IF EXISTS `$matches[1]",$conn);
	                    $ret = mysql_query($sql,$conn);
	                    $i++;
	                    if(mysql_query($sql,$conn)){
	                    	$info = '<li><span class="correct_span">√</span>创建数据表' . $matches[1] . '，完成！<span style="float: right;">'.date('Y-m-d H:i:s').'</span></li> ';
	                    	alert(1,$info,$i);
	                    } else {
	                    	$info = '<li><span class="correct_span error_span">√</span>创建数据表' . $matches[1] . '，失败，安装停止！<span style="float: right;">'.date('Y-m-d H:i:s').'</span></li>';
	                    	alert(0,$info,$i);
	                    }
                    }
                } else {
                	//插入数据
                    $ret = mysql_query($sql);
                }
            }

            //处理
            $data = include './'.$config['handleFile'];
            $_SESSION['INSTALLOK'] = $data['status']?1:0;
            alert($data['status'],$data['info']);
        }
        include ("./templates/4.php");
        break;
```

![20131008133924](http://chenhaizan-upload.stor.sinaapp.com/article/image/201310/5253ca74a3437.jpg)

写入成功后，需要进行添加管理员，生成配置文件等操作，如上面代码中的

```
 //处理
$data = include './'.$config['handleFile'];
$_SESSION['INSTALLOK'] = $data['status']?1:0;
```

在配置文件中`$config['handleFile']`为`main.php`

```
$username = trim($_POST['manager']);
$password = trim($_POST['manager_pwd']);
//网站名称
$site_name = addslashes(trim($_POST['sitename']));
//网站域名
$site_url = trim($_POST['siteurl']);
//附件目录
$upload_path = $_SESSION['UPLOADPATH'];
//描述
$seo_description = trim($_POST['sitedescription']);
//关键词
$seo_keywords = trim($_POST['sitekeywords']);
//更新配置信息
mysql_query("UPDATE `{$dbPrefix}config` SET  `value` = '$site_name' WHERE varname='site_name'");
mysql_query("UPDATE `{$dbPrefix}config` SET  `value` = '$site_url' WHERE varname='site_domain' ");
mysql_query("UPDATE `{$dbPrefix}config` SET  `value` = '$seo_description' WHERE varname='site_description'");
mysql_query("UPDATE `{$dbPrefix}config` SET  `value` = '$seo_keywords' WHERE varname='site_keywords'");

if(!empty($upload_path)){
	mysql_query("UPDATE `{$dbPrefix}config` SET  `value` = '$upload_path' WHERE varname='attach_storage_domain' ");
}
if(INSTALLTYPE == 'HOST'){
	//读取配置文件，并替换真实配置数据
	$strConfig = file_get_contents('./' . $config['dbSetFile']);
	$strConfig = str_replace('#DB_HOST#', $dbHost, $strConfig);
	$strConfig = str_replace('#DB_NAME#', $dbName, $strConfig);
	$strConfig = str_replace('#DB_USER#', $dbUser, $strConfig);
	$strConfig = str_replace('#DB_PWD#', $dbPwd, $strConfig);
	$strConfig = str_replace('#DB_PORT#', $dbPort, $strConfig);
	$strConfig = str_replace('#DB_PREFIX#', $dbPrefix, $strConfig);
	$strConfig = str_replace('#AUTHCODE#', genRandomString(18), $strConfig);
	$strConfig = str_replace('#COOKIE_PREFIX#', genRandomString(6) . "_", $strConfig);
	$strConfig = str_replace('#DATA_CACHE_PREFIX#', genRandomString(6) . "_", $strConfig);
	$strConfig = str_replace('#SESSION_PREFIX#', genRandomString(6) . "_", $strConfig);
	@file_put_contents($config['dbConfig'], $strConfig);
}

//插入管理员
//生成随机认证码
$verify = genRandomString(6);
$time = time();
$ip = get_client_ip();
$password = md5($password . md5($verify));
$email = trim($_POST['manager_email']);
$query = "INSERT INTO `{$dbPrefix}member` VALUES (1, 0, 0, '{$username}', '{$password}', '{$email}', '', '', 0, '', '', '{$verify}', 1, '{$time}', 0, 0, 1, 2, 1, '', 65535, 1, 1, 1, 1, 0, '')";
if(mysql_query($query)){
	return array('status'=>2,'info'=>'成功添加管理员<br>成功写入配置文件<br>安装完成...');
}
return array('status'=>0,'info'=>'安装失败...');
```

如果在本地环境，将数据库配置模板文件进行特定位置替换，生成配置文件到设置的` $config['dbConfig'] （../config/config.ini.php）`中

5）、安装完成
```
case '5':
	verify(5);
        include ("./templates/5.php");
        //安装完成,生成.lock文件
        if(isset($_SESSION['INSTALLOK']) && $_SESSION['INSTALLOK'] == 1){
			filewrite($config['installFile']);
		}
        unset($_SESSION);
        break;
```


![20131008133939](http://chenhaizan-upload.stor.sinaapp.com/article/image/201310/5253cca6c699b.jpg)

在这一步生成`.lock`文件

安装完成后再次运行时，出现提示信息

![20131008134243](http://chenhaizan-upload.stor.sinaapp.com/article/image/201310/5253ccf049de6.jpg)

![20131008133053](http://chenhaizan-upload.stor.sinaapp.com/article/image/201310/5253ccfca0e18.jpg)

### 其他一些函数

```
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
	empty($_POST['dbprefix'])?alert(0,'数据库表前缀不能为空！'):'';
	empty($_POST['siteurl'])?alert(0,'网站域名不能为空！'):'';
	empty($_POST['uploaddir'])?alert(0,'附件上传的目录不能为空！'):'';
	empty($_POST['manager'])?alert(0,'管理员帐号不能为空！'):'';
	empty($_POST['manager_pwd'])?alert(0,'管理员密码不能为空！'):'';
	empty($_POST['manager_email'])?alert(0,'管理员邮箱不能为空！'):'';
}
/**
 * 判断目录是否可写
 */
function testwrite($d) {
    $tfile = "_test.txt";
    $fp = @fopen($d . "/" . $tfile, "w");
    if (!$fp) {
        return false;
    }
    fclose($fp);
    $rs = @unlink($d . "/" . $tfile);
    if ($rs) {
        return true;
    }
    return false;
}
/**
 * 创建目录
 */
function dir_create($path, $mode = 0777) {
    if (is_dir($path))
        return TRUE;
    $temp = explode('/', $path);
    $cur_dir = '';
    $max = count($temp) - 1;
    for ($i = 0; $i < $max; $i++) {
        $cur_dir .= $temp[$i] . '/';
        if (@is_dir($cur_dir))
            continue;
        @mkdir($cur_dir, $mode, true);
        @chmod($cur_dir, $mode);
    }
    return dir_create($path);
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
	shuffle($chars);    // 将数组打乱
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
    $type       =  $type ? 1 : 0;
    static $ip  =   NULL;
    if ($ip !== NULL) return $ip[$type];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $pos    =   array_search('unknown',$arr);
        if(false !== $pos) unset($arr[$pos]);
        $ip     =   trim($arr[0]);
    }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip     =   $_SERVER['HTTP_CLIENT_IP'];
    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip     =   $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $l sprintf("%u",ip2long($ip));
    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
 }
```