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
    'dsn' => 'mysql:host=111.222.75.227;port=3306;dbname=test',
    'username' => '2222',
    'password' => '2222',
    'charset' => 'utf8mb4',
    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
