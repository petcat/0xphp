<?php
#[会员注册]
require_once("global.php");
if($act == "regok")
{
	// 使用更严格的输入验证
	$username = isset($_POST['username']) ? trim($_POST['username']) : '';
	$password = isset($_POST['password']) ? trim($_POST['password']) : '';
	$chkpass = isset($_POST['checkpass']) ? trim($_POST['checkpass']) : '';
	$email = isset($_POST['email']) ? trim($_POST['email']) : '';
	
	if(!$username || !preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username))
	{
		Error($langs["reg_emptyuser"],"register.php");
	}
	if(!$password || strlen($password) < 6)
	{
		Error($langs["reg_emptypass"],"register.php");
	}
	if(!$chkpass)
	{
		Error($langs["reg_emptychkpass"],"register.php");
	}
	if($password != $chkpass)
	{
		Error($langs["reg_difpass"],"register.php");
	}
	if(!$email || !filter_var($email, FILTER_VALIDATE_EMAIL))
	{
		Error($langs["reg_emptyemail"],"register.php");
	}
	
	// 使用转义防止SQL注入
	$username = mysql_real_escape_string($username);
	$email = mysql_real_escape_string($email);
	$check_user = $DB->qgGetOne("SELECT * FROM ".$prefix."user WHERE user='".$username."'");
	if($check_user)
	{
		Error($langs["reg_user_exist"],"register.php");
	}
	$password = md5($password);
	$id = $DB->qgInsert("INSERT INTO ".$prefix."user(user,nickname,realname,pass,email,regdate) VALUES('".$username."','".$username."','','".$password."','".$email."','".$system_time."')");
	$id = $DB->qgInsertID();
	#[直接登录]
	$_SESSION["qg_sys_user"]["id"] = $id;
	$_SESSION["qg_sys_user"]["user"] = $username;
	$_SESSION["qg_sys_user"]["pass"] = $password;
	$_SESSION["qg_sys_user"]["email"] = $email;
	Error($langs["reg_ok"],"home.php");
}
else
{
	if(USER_STATUS == true)
	{
		Error($langs["reg_disabled"],"home.php");
	}
	#[标题头]
	$sitetitle = $langs["reg_title"]." - ".$system["sitename"];
	#[向导栏]
	$lead_menu[0]["url"] = "register.php";
	$lead_menu[0]["name"] = $langs["reg_title"];
	HEAD();
	FOOT("register");
}
?>