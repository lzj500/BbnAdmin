<?php
require "vendor/autoload.php";
/*
\think\facade\Db::setConfig([
    // 默认数据连接标识
    'default' => 'mysql',
    // 数据库连接信息
    'connections' => [
        'mysql' => [
            // 数据库类型
            'type' => 'mysql',
            // 主机地址
            'hostname' => '127.0.0.1',
            // 用户名
            'username' => 'root',
            // 数据库密码
            'password' => 'root',
            // 数据库名
            'database' => 'test1',
            // 数据库编码默认采用utf8
            'charset' => 'utf8mb4',
            // 数据库表前缀
            'prefix' => '',
            // 数据库调试模式
            'debug' => true,
        ],
    ],
]);
$res = \itbbn\admin\Test::hello("zs");
var_dump($res);
$adminList = \itbbn\admin\model\Admin::getList("");
print_r(json_encode($adminList,JSON_UNESCAPED_UNICODE));*/