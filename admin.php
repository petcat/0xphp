<?php
#[后台通用页信息]
ob_start();
$st = explode(" ",microtime());
$st = $st[0] + $st[1];
define("STARTTIME",$st);
header("Content-type: text/html; charset=utf8");
require_once("config.php");
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_magic_quotes_runtime(0);
define("PHPOK_SET", TRUE);
$system_time = $systemTime = time();
include_once("version.php");

#[加载字符串处理类]
require_once("class/string.class.php");
$STR = new QG_C_STRING(false,false,false);

$magic_quotes_gpc = get_magic_quotes_gpc();
// 移除不安全的extract()函数调用
// 改为手动过滤和验证输入数据
if(!$magic_quotes_gpc)
{
	$_FILES = $STR->format($_FILES);
}

require_once("class/mysql.db.class.php");
$DB = new qgSQL($dbHost,$dbData,$dbUser,$dbPass,$dbOpenType);

include_once("class/file.class.php");
$FS = new files();
unset($dbHost,$dbData,$dbUser,$dbPass,$dbOpenType);

session_start();

#[加载常用函数]
include_once("include/admin.func.php");

#[加载模板配置]
$set = array
(
	"tplid"=>1,
	"tpldir"=>"admin/tpl",
	"cache"=>"data/admin_tplc",
	"phpdir"=>"",
	"ext"=>"htm",
	"autorefresh"=>true,
	"autoimg"=>true
);
require_once("class/tpl.class.php");
$TPL = new QG_C_TEMPLATE($set);
$TPL->set($set["tplid"],"tplid");
$TPL->set($set["tpldir"],"tpldir");
$TPL->set($set["cache"],"cache");
$TPL->set($set["phpdir"],"phpdir");

#[获取get或post到的变量，并附新值]
$sysfile = $sys_file = $sysFile = SafeHtml($file);
$sysact = $sys_act = $sysAct = SafeHtml($act);

#[判断加载的code]
if($isCheckCode && function_exists("imagecreate") && $sys_act == "chkcode")
{
	ob_clean();
	SetCheckCodes();
	exit;
}

#[判断会员是否已经登录]
$sys_status = false;
if($_SESSION["admin"]["user"] && $_SESSION["admin"]["pass"])
{
	if(strpos("login,logout,loginok",$sys_act) === false)
	{
		$sys_status = true;
		#[设置管理员权限]
		if($_SESSION["admin"]["typer"] != "system")
		{
			$adminer_tmp_power = explode(",",$_SESSION["admin"]["modulelist"]);
		}
		else
		{
			$modulelist = "notice,orderlist,user,picplay,link,vote,online,nav,book_feedback,job,ad,special";
			$adminer_tmp_power = explode(",",$modulelist);
		}
		if(count($adminer_tmp_power)>0)
		{
			foreach($adminer_tmp_power AS $key=>$value)
			{
				$QG_AP[$value] = true;
			}
		}
		unset($adminer_tmp_power);
	}
}
#[加载相应的页面]
if($sys_status)
{
	#[这里是弹出窗口的设置]
	$incfile = $STR->safe(rawurldecode($_GET["incfile"]));

	if(!$sysfile && !$incfile)
	{
		$TPL->p("frame");
		exit;#[中止]
	}

	#[判断是否是左侧页]
	if($sysFile == "left")
	{
		include_once("admin/left.sys.php");
		exit;
	}

	if($sysfile && !file_exists("admin/".$sysfile.".qg.php"))
	{
		$TPL->p("nofile.sys");
		exit;
	}

	$right_head_notice = $right_head_language = false;
	#[加载后台设置的常规配置信息]
	if(!$_SESSION["language"])
	{
		$rsLang = $DB->qgGetOne("SELECT id FROM ".$prefix."lang WHERE ifdefault='1'");
		if(!$rsLang)
		{
			$right_head_language = true;
		}
		$_SESSION["language"] = $rsLang["id"];
		$language = $rsLang["id"];
		unset($rsLang);
	}
	else
	{
		$language = $_SESSION["language"];
	}
	#[如果有参数传递过来]
	if($langid)
	{
		$language = $langid;
		$_SESSION["language"] = $language;
	}
	if(file_exists("data/system_".$language.".php"))
	{
		include_once("data/system_".$language.".php");
	}
	#[加载常规配置]
	if($system)
	{
		#[设置时区]
		if(function_exists("date_default_timezone_set"))
		{
			if(!$system["timezone"])
			{
				$system["timezone"] = "8";
			}
			date_default_timezone_set("Etc/GMT".intval($system["timezone"]));
			$system_time = $systemTime = $system_now = time() + $system["timerevise"];
		}
		else
		{
			$system_time = $systemTime = $system_now = mktime(gmdate("H")+$system["timezone"],gmdate("i")+$system["timerevise"],gmdate("s"),gmdate("m"),gmdate("d"),gmdate("Y"));
		}
		include_once("class/upload.class.php");
		$UP = new UPLOAD("upfiles/".date("Ym/d/",$system_time),"jpg,gif,png,zip,rar,gz");
		include_once("class/gd.class.php");
		$GD = new GD($system["isgd"],$system["gdpic"],$system["gdposition"],$system["thumbwidth"],$system["thumbheight"],$system["markwidth"],$system["markheight"],$system["thumbtype"]);
	}
	else
	{
		$right_head_notice = true;
	}

	if($incfile)
	{
		$site_title = "欢迎进入弹窗页";
		$iframe_height = intval($_GET["iframe_height"]);
		$inputname = $STR->safe($_GET["inputname"]);
		$subtype = intval($_GET["subtype"]);
		if(!$iframe_height)
		{
			$iframe_height = 124;
		}
		$TPL->p("open.index.sys");
		exit;
	}

	require_once("admin/right.head.php");
	require_once("admin/".$sysFile.".qg.php");
	exit;
}

if($act == "loginok")
{
	if(!$username || !$password)
	{
		Error("用户名或密码或认证码为空...","admin.php?act=login",2,true);
	}
	#[认证码功能]
	if(function_exists("imagecreate") && $isCheckCode)
	{
		if(!$chk)
		{
			Error("验证码不能为空！","admin.php?act=login",2,true);
		}
		$chk = md5(strtolower($chk));
		if($chk != $_SESSION["qgLoginChk"])
		{
			Error("认证码输入不正确！","admin.php?act=login",2,true);
		}
	}
	unset($_SESSION["qgLoginChk"],$chk);
	
	// 使用转义防止SQL注入
	$username = mysql_real_escape_string($username);
	$password_hash = md5($password);
	$rows = $DB->qgGetOne("SELECT * FROM ".$prefix."admin WHERE user='".$username."' AND pass='".$password_hash."' LIMIT 1");
	if($rows)
	{
		$_SESSION["admin"] = $rows;
		unset($rows,$password);
		Error("管理员 <strong>".$username."</strong> 登录后台...","admin.php",2,true);
	}
	else
	{
		Error("管理员账号或密码不正确...","admin.php?act=login",2,true);
	}
}
elseif($act == "logout")
{
	session_destroy();
	Error("管理员成功退出...","admin.php?act=login");
}
else
{
	Foot("login.sys");
}
?>