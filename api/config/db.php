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
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=127.0.0.1;port=3306;dbname=ceshi_juanpao_c',
    'username' => 'ceshi_juanpao_c',
    'password' => 'jccsettZCscJ6mYs',
    'charset' => 'utf8',
    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
