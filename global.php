<?php
ob_start();
define("PHPOK_SET", TRUE);
$start_time = explode(" ",microtime());
$start_time = $start_time[0] + $start_time[1];
define("START_TIME",$start_time);
unset($start_time);
error_reporting(E_ERROR | E_WARNING | E_PARSE);
#error_reporting(E_ALL);
require_once("config.php");
require_once("version.php");


#[加载字符串处理类]
require_once("class/string.class.php");

$STR = new QG_C_STRING(false,false,false);

$magic_quotes_gpc = get_magic_quotes_gpc();
@extract($STR->format($_POST));
@extract($STR->format($_GET));
if(!$magic_quotes_gpc)
{
	$_FILES = $STR->format($_FILES);
}

session_start();
#[加载数据库]
require_once("class/sqlite.db.class.php");
$DB = new qgSQL($dbName,$dbData,$dbUser,$dbPass,false);

#[加载管理语言包]
require_once("class/lang.class.php");
$LNG = new QG_C_LANG($DB,$prefix);
$langs = $LNG->lang();

require_once("class/file.class.php");
$FS = new files();
require_once("include/global.func.php");

#[加载网站常规选项]
if(!file_exists("data/system_".LANGUAGE_ID.".php"))
{
	echo "Not Set System.";
	exit;

}
include_once("data/system_".LANGUAGE_ID.".php");

#[获取网站设置的网址]
$siteurl = $system["siteurl"];
if(!$siteurl)
{
	$siteurl = "./";
}
if(substr($siteurl,-1) != "/")
{
	$siteurl .= "/";
}

#[计算当前时间]
$system_time = $system_now = mktime(gmdate("H")+$system["timezone"],gmdate("i")+$system["timerevise"],gmdate("s"),gmdate("m"),gmdate("d"),gmdate("Y"));

#[模板信息参数]
require_once("class/tplfolder.class.php");
$STPL = new QG_C_TPLFOLDER($DB,$prefix);
$tplfolder = $STPL->folder();
$template_id = $STPL->tplid();
define("TPL_FOLDER",$tplfolder);
define("TemplateID",$template_id);
$_SESSION["tpl_folder"] = $tplfolder;
$_SESSION["template_id"] = $template_id;
unset($STPL);
#[加载模板类]
require_once("class/tpl.class.php");
$set = array
(
	"tplid"=>TemplateID,
	"tpldir"=>"templates/".LANGUAGE_SIGN."/".TPL_FOLDER,
	"cache"=>"data/phpok_tplc",
	"phpdir"=>"",
	"ext"=>"htm",
	"autorefresh"=>true,
	"autoimg"=>true
);
$TPL = new QG_C_TEMPLATE($set);
$TPL->set($set["tplid"],"tplid");
$TPL->set($set["tpldir"],"tpldir");
$TPL->set($set["cache"],"cache");
$TPL->set($set["phpdir"],"phpdir");
define("NewTemplate",$set["tpldir"]);
unset($set);
#[判断网站状态]
if(file_exists("data/site.lock.php"))
{
	$content = $FS->qgRead("data/site.lock.php");
	if($content)
	{
		Error($content);
	}
	else
	{
		Error("Close...");
	}
}

#[加载模块]
require_once("include/qgmod.func.php");

if($_SESSION["qg_sys_user"])
{
	$USER_STATUS = true;
}
else
{
	$USER_STATUS = false;
}
define("USER_STATUS",$USER_STATUS);
unset($USER_STATUS);

#[计算当前页的网址]
$PHP_SELL_QUERY_STRING = basename($_SERVER["PHP_SELF"]);
if($_SERVER["QUERY_STRING"])
{
	$PHP_SELL_QUERY_STRING .= "?".$_SERVER["QUERY_STRING"];
}
?>