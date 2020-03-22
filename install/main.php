<?php
$username = trim($_POST['manager']);
$password = trim($_POST['manager_pwd']);

if(INSTALLTYPE == 'HOST'){
	//读取配置文件，并替换真实配置数据

    $filename = '../api/config/db.php';
    $fp= fopen($filename, "w");  //w是写入模式，文件不存在则创建文件写入。
    $len = fwrite($fp, "<?php".
        //服务器
        " return [".
        " 'class' => 'yii\db\Connection',".
        " 'dsn' => 'mysql:host=".$dbHost.";port=".$dbPort.";dbname=".$dbName."',".
        " 'username' => '".$dbUser."',".
        " 'password' => '".$dbPwd."',".
        " 'charset' => 'utf8',];");
    fclose($fp);
}

//插入管理员
//生成随机认证码
$verify = md5(genRandomString(6));
$time = time();
$ip = get_client_ip();
$password = md5($password . $verify);
$email = trim($_POST['manager_email']);
$query = "INSERT INTO `admin_user` VALUES ('1', '{$username}', '0', '0', '0.00', '系统管理员', '{$password}', '{$verify}', '15366669450', '1', '1', '1522911742', '1571964511', null);";
$a = mysqli_query($conn,$query);
$query  ="INSERT INTO `merchant_user` VALUES (13, '{$username}', NULL, NULL, '{$password}', '{$verify}', 'admin', '', NULL, NULL, NULL, '0.00', 1, 1, 0, 1, 1582532374, NULL, 1551255661, 1582532374, NULL);";
$b= mysqli_query($conn,$query);

if($a){
	return array('status'=>2,'info'=>'成功添加管理员<br />成功写入配置文件<br>安装完成...');
}else{
	return array('status'=>0,'info'=>'安装失败...');
}
if($b){
	return array('status'=>2,'info'=>'成功添加管理员<br />成功写入配置文件<br>安装完成...');
}else{
	return array('status'=>0,'info'=>'安装失败...');
}

return array('status'=>0,'info'=>'安装失败...');
