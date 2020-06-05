<?php

//本地
//return [
//    'class' => 'yii\db\Connection',
//    'dsn' => 'mysql:host=192.168.188.236;dbname=juanpao',
//    'username' => 'root',
//    'password' => 'root',
//    'charset' => 'utf8',
//        // Schema cache options (for production environment)
//        //'enableSchemaCache' => true,
//        //'schemaCacheDuration' => 60,
//        //'schemaCache' => 'cache',
//];

//测试服务器
//请将用户名/数据库名称/密码分别替换成 BT_DB_USERNAME/BT_DB_NAME/BT_DB_PASSWORD
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=127.0.0.1;port=3306;dbname=BT_DB_NAME',
    'username' => 'BT_DB_USERNAME',
    'password' => 'BT_DB_PASSWORD',
    'charset' => 'utf8',
    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
