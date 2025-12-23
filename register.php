<?php
#[会员注册]
require_once("global.php");
if($act == "regok")
{
	$username = $username; // 已在global.php中处理
	if(!$username)
	{
		Error($langs["reg_emptyuser"],"register.php");
	}
	$password = $password; // 已在global.php中处理
	$chkpass = $chkpass; // 已在global.php中处理
	if(!$password)
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
	$email = $email; // 已在global.php中处理
	if(!$email)
	{
		Error($langs["reg_emptyemail"],"register.php");
	}
	if(!filter_var($email, FILTER_VALIDATE_EMAIL))
	{
		Error($langs["reg_erroremail"],"register.php");
	}
	$check_user = $DB->qgGetOne("SELECT * FROM ".$prefix."user WHERE user=? LIMIT 1", [$username]);
	if($check_user)
	{
		Error($langs["reg_user_exist"],"register.php");
	}
	$hashed_password = password_hash($password, PASSWORD_DEFAULT);
	$id = $DB->qgInsert("INSERT INTO ".$prefix."user(user,nickname,realname,pass,email,regdate) VALUES(?,?,?,?,?,?)", [$username, $username, "", $hashed_password, $email, $system_time]);;
	$user_info = $DB->qgGetOne("SELECT id FROM ".$prefix."user WHERE user=? LIMIT 1", [$username]);
        $id = $user_info["id"];
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