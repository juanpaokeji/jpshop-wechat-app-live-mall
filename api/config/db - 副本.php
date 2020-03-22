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
    'dsn' => 'mysql:host=122.51.238.159;port=3306;dbname=juanpao_test',
    'username' => 'juanpao_test',
    'password' => 'wxzjn6KXndDGCMFs',
    'charset' => 'utf8',
    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
