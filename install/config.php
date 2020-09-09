<?php
return array(
		/* ------系统------ */
		//系统名称
		'name'=>'卷泡jpshop系统',
		//系统版本
		'version'=>'1.0',
		//系统powered
		'powered'=>'Powered by juanpao.com',
		//系统脚部信息
		'footerInfo'=> 'Copyright © 2016-2020 juanpao.com Corporation',

		/* ------站点------ */
		//数据库文件
		'sqlFileName'=>'db.sql',
		//数据库配置文件
		'dbConfig'=>'../config/config.ini.php',
		//数据库名
		'dbName' => '',
		//数据库表前缀
		'dbPrefix' => '',
		//站点名称
		'siteName' => '',
		//站点关键字
		'siteKeywords' => '',
		//站点描述
		'siteDescription' => '',
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
		/* ------生成数据库配置文件的模板------ */
		'dbSetFile'=> 'config.ini.php',
		/* ------安装验证/生成文件;非云平台安装有效------ */
		'installFile' => '../config/install.lock',
		'alreadyInstallInfo' => '你已经安装过该系统，如果想重新安装，请先删除站点config目录下的 install.lock 文件，然后再尝试安装！',
	);