<?php
#[会员登录页]
require_once("global.php");
if($act == "loginok")
{
	// 使用更严格的输入验证
	$username = isset($_POST['username']) ? trim($_POST['username']) : '';
	$password = isset($_POST['password']) ? trim($_POST['password']) : '';
	
	// 验证输入格式
	if(!$username || !preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username))
	{
		Error($langs["empty_user"],"login.php");
	}
	if(!$password || strlen($password) < 6)
	{
		Error($langs["empty_pass"],"login.php");
	}
	
	// 使用转义防止SQL注入
	$username = mysql_real_escape_string($username);
	$password_hash = md5($password);
	
	$rs = $DB->qgGetOne("SELECT id,user,pass,email FROM ".$prefix."user WHERE user='".$username."' AND pass='".$password_hash."'");
	if(!$rs)
	{
		Error($langs["notuser"],"login.php");
	}
	$_SESSION["qg_sys_user"] = $rs;
	#[指定跳转页]
	if($_SESSION["refresh_url"])
	{
		qgheader($_SESSION["refresh_url"]);
	}
	else
	{
		qgheader();
	}
}
elseif($act == "logout")
{
	$_SESSION["qg_sys_user"] = "";
	qgheader();
}
else
{
	if($_SESSION["qg_sys_user"])
	{
		qgheader();
	}
	#[标题头]
	$sitetitle = $langs["logintitle"]." - ".$system["sitename"];
	#[向导栏]
	$lead_menu[0]["url"] = $siteurl."login.php";
	$lead_menu[0]["name"] = $langs["logintitle"];
	HEAD();
	FOOT("login");
}
?>