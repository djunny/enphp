<?php
// 应用入口文件
// 检测PHP环境
if (version_compare(PHP_VERSION, '5.3.0', '<')) die('require PHP > 5.3.0 !');
//超时时间
@set_time_limit(120);
//内存限制 取消内存限制
@ini_set("memory_limit", '-1');
// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
//define('APP_DEBUG',True);
// 定义应用目录
define('APP_PATH', './Lib/');

// 引入ThinkPHP入口文件
//require 'inc.php';
// 亲^_^ 后面不需要任何代码了 就是如此简单
echo "123";
?>