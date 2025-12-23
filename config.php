<?php
#[数据库信息]
$dbHost = $_ENV['DB_HOST'] ?? "localhost";
$dbUser = $_ENV['DB_USER'] ?? "sinogacm_sino";
$dbPass = $_ENV['DB_PASS'] ?? "fCbe4B146772EE15";
$dbData = $_ENV['DB_NAME'] ?? "sinogacm_sino";

#[数据表前缀]
$prefix = "sino_";

#[是否启用调试]
$viewbug = 0;

#[是否启用伪静态页功能，使用为true，不使用为false]
$urlRewrite = false;

#[后台是否启用验证码功能，使用为true，不使用为false]
$isCheckCode = true;
?>