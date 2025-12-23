--- login.php (原始)
<?php
#[会员登录页]
require_once("global.php");
if($act == "loginok")
{
        $username = SafeHtml($username);
        $password = SafeHtml($password);
        if(!$username)
        {
                Error($langs["empty_user"],"login.php");
        }
        if(!$password)
        {
                Error($langs["empty_pass"],"login.php");
        }
        $rs = $DB->qgGetOne("SELECT id,user,pass,email FROM ".$prefix."user WHERE user='".$username."' AND pass='".md5($password)."'");
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

+++ login.php (修改后)
<?php
#[会员登录页]
require_once("global.php");
if($act == "loginok")
{
        $username = SafeHtml($username);
        $password = SafeHtml($password);
        if(!$username)
        {
                Error($langs["empty_user"],"login.php");
        }
        if(!$password)
        {
                Error($langs["empty_pass"],"login.php");
        }
        $username_esc = $DB->qgEscapeString($username);
$password_hash = md5($password);
$rs = $DB->qgGetOne("SELECT id,user,pass,email FROM ".$prefix."user WHERE user='".$username_esc."' AND pass='".$password_hash."'");
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
