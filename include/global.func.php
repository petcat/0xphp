<?php
#[常用函数管理]
function Error($msg="操作错误",$url="",$time=2)
{
	global $DB,$system,$langs;
	global $TPL;
	$TPL->set_var("error_msg",$msg);
	$TPL->set_var("error_time",$time);
	$TPL->set_var("error_url",$url);
	$TPL->p("error.sys");
	rewrite();
}

function qgheader($url="home.php")
{
	global $TPL;
	$url = $url ? $url : "home.php";
	// 验证URL是否为安全的内部URL，防止开放重定向
	if (!is_safe_redirect_url($url)) {
		$url = "home.php";
	}
	ob_end_clean();
	header("Location:".$url);
	exit;
}

// 验证重定向URL的安全性
function is_safe_redirect_url($url) {
	// 检查是否包含协议，如果是外部URL则不安全
	if (preg_match('/^https?:\/\/|^\/\//i', $url)) {
		// 如果是外部URL，只允许当前域名
		$host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
		if (!preg_match('/^https?:\/\/' . preg_quote($host, '/') . '/i', $url)) {
			return false;
		}
	}
	// 检查是否包含危险字符
	if (strpos($url, '..') !== false || strpos($url, '<') !== false || strpos($url, '>') !== false) {
		return false;
	}
	return true;
}

function ErrorMsg($msg="操作不正确")
{
	Error($msg);
}

#[检测缓存文件是否超时]
function CheckCache($filename)
{
	global $system;
	global $system_time;
	if(!$filename)
	{
		return false;
	}
	$system["mintime"] = $system["mintime"] ? intval($system["mintime"]) : 0;
	if(!file_exists($filename))
	{
		return false;
	}
	if(!$system["maxtime"] || $system["maxtime"] < 1)
	{
		return false;
	}
	if((@filemtime($filename) + rand($system["mintime"],$system["maxtime"])) <= $system_time)
	{
		return false;
	}
	return true;
}

#[获取指定语言ID的分类信息，这里只读取ID号信息，其他的不读]
function GetCateIdAll($langid)
{
	global $DB,$prefix;
	$rs = $DB->qgGetAll("SELECT id FROM ".$prefix."category WHERE language='".LANGUAGE_ID."'");
	$rsid = array();
	foreach($rs AS $key=>$value)
	{
		$rsid[$value["id"]] = $value["id"];
	}
	return $rsid;
}

#[管理无限级别分类]
function menu_list($catelist,$cateid,$array=array())
{
	foreach($catelist AS $key=>$value)
	{
		if($value["id"] == $cateid)
		{
			$array[$key] = $value;
			$array = menu_list($catelist,$value["parentid"],$array);
		}
	}
	return $array;
}

#[根据当前分类得到子分类ID]
function get_son_list($catelist,$cateid,$array=array())
{
	foreach($catelist AS $key=>$value)
	{
		if($value["parentid"] == $cateid)
		{
			$array[$key] = $value["id"];
			$array = get_son_list($catelist,$value["id"],$array);
		}
	}
	return $array;
}

function page($url,$total=0,$psize=30,$pageid=0,$halfPage=5)
{
	global $langs;
	if(empty($psize))
	{
		$psize = 30;
	}
	#[添加链接随机数]
	if(strpos($url,"?") === false)
	{
		$url = $url."?qgrand=phpok";
	}
	#[共有页数]
	$totalPage = intval($total/$psize);
	if($total%$psize)
	{
		$totalPage++;#[判断是否存余，如存，则加一
	}
	#[如果分页总数为1或0时，不显示]
	if($totalPage<2)
	{
		return false;
	}
	#[判断分页ID是否存在]
	if(empty($pageid))
	{
		$pageid = 1;
	}
	#[判断如果分页ID超过总页数时]
	if($pageid > $totalPage)
	{
		$pageid = $totalPage;
	}
	#[Html]
	$array_m = 0;
	if($pageid > 0)
	{
		$returnlist[$array_m]["url"] = $url;
		$returnlist[$array_m]["name"] = $langs["page_first"];
		$returnlist[$array_m]["status"] = 0;
		if($pageid > 1)
		{
			$array_m++;
			$returnlist[$array_m]["url"] = $url."&pageid=".($pageid-1);
			$returnlist[$array_m]["name"] = $langs["page_front"];
			$returnlist[$array_m]["status"] = 0;
		}
	}
	#[添加中间项]
	for($i=$pageid-$halfPage,$i>0 || $i=0,$j=$pageid+$halfPage,$j<$totalPage || $j=$totalPage;$i<$j;$i++)
	{
		$l = $i + 1;
		$array_m++;
		$returnlist[$array_m]["url"] = $url."&pageid=".$l;
		$returnlist[$array_m]["name"] = $l;
		$returnlist[$array_m]["status"] = ($l == $pageid) ? 1 : 0;
	}
	#[添加select里的中间项]
	for($i=$pageid-$halfPage*3,$i>0 || $i=0,$j=$pageid+$halfPage*3,$j<$totalPage || $j=$totalPage;$i<$j;$i++)
	{
		$l = $i + 1;
		$select_option_msg = "<option value='".$l."'";
		if($l == $pageid)
		{
			$select_option_msg .= " selected";
		}
		$select_option_msg .= ">".$l."</option>";
		$select_option[] = $select_option_msg;
	}
	#[添加尾项]
	if($pageid < $totalPage)
	{
		$array_m++;
		$returnlist[$array_m]["url"] = $url."&pageid=".($pageid+1);
		$returnlist[$array_m]["name"] = $langs["page_back"];
		$returnlist[$array_m]["status"] = 0;
	}
	$array_m++;
	$returnlist[$array_m]["url"] = $url."&pageid=".$totalPage;
	$returnlist[$array_m]["name"] = $langs["page_last"];
	$returnlist[$array_m]["status"] = 0;
	#[内容组成html]
	#[组织样式]
	$msg = "<table class='pagelist'><tr><td class='n'>".$total."/".$psize."</td>";
	foreach($returnlist AS $key=>$value)
	{
		if($value["status"])
		{
			$msg .= "<td class='m'>".$value["name"]."</td>";
		}
		else
		{
			$msg .= "<td class='n'><a href='".$value["url"]."'>".$value["name"]."</a></td>";
		}
	}
	$msg .= "<td><select onchange=\"window.location='".$url."&pageid='+this.value+''\">".implode("",$select_option)."</option></select></td>";
	$msg .= "</tr></table>";
	unset($returnlist);
	return $msg;
}



function SafeHtml($msg="")
{
	global $STR;
	// 验证输入防止XSS
	$msg = validateInput($msg);
	return $STR->safe($msg);
}

function FckHtml($msg="",$script=false,$iframe=false,$style=false)
{
	global $STR;
	// 验证输入防止XSS
	$msg = validateInput($msg);
	$STR->set("script",$script);
	$STR->set("iframe",$iframe);
	$STR->set("style",$style);
	return $STR->html($msg);
}

function CutString($string,$length=10,$dot="…")
{
	global $STR;
	// 验证输入防止XSS
	$string = validateInput($string);
	return $STR->cut($string,$length,$dot);
}

function FckEditor($var="",$content="",$toolbar="",$height="",$width="100%")
{
	include_once("./class/fckeditor.class.php");
	$var = $var ? $var : "content";
	$fck = new FCKeditor($var) ;//获得一个变量信息
	$sBasePath = $_SERVER['PHP_SELF'] ;
	$sBasePath = substr( $sBasePath, 0, strpos( $sBasePath, "_samples" ) ) ;
	$fck->BasePath = $sBasePath."include/editor/";
	$fck->Value = $content;
	$fck->Config['AutoDetectLanguage'] = false;
	$fck->Config['DefaultLanguage'] = "zh-cn";
	$fck->Config['ToolbarStartExpanded'] = true;
	$fck->ToolbarSet = "Default";
	$fck->Width = $width;
	$fck->Height = $height;
	$fck->Config['EnableXHTML'] = true;
    $fck->Config['EnableSourceXHTML'] = true;
	return $fck->CreateHtml();
}

function SendEmail($email="",$subject="",$content="")
{
	global $system;
	if(empty($email))
	{
		return false;
	}
	include_once("./class/phpmailer.class.php");
	$SML = new PHPMailer();
	$SML->IsSMTP();
	$SML->Host = $system["mailhost"];
	$SML->Port = $system["mailport"] ? $system["mailport"] : 25;
	$SML->SMTPAuth = true;
	$SML->Username = $system["mailuser"];
	$SML->Password = $system["mailpass"];
	$SML->From = $system["mailqg"];
	$SML->FromName = $system["mailuser"];
	#[回复地址]
	if($system["adminemail"])
	{
		$SML->AddReplyTo($system["adminemail"]);
	}
	else
	{
		$SML->AddReplyTo($system["mailqg"]);
	}
	$SML->IsHTML(true);

	$SML->AddAddress($email);
	$SML->CharSet = $system["mailtype"] ? $system["mailtype"] : "utf8";
	if($system["mailtype"] == "gbk")
	{
		$subject = Utf2gb($subject);
		$content = Utf2gb($content);
	}
	$SML->Subject = $subject ? $subject : $email;
	$SML->Body = $content;
	if($SML->Send())
	{
		return true;
	}
	else
	{
		return false;
	}
}

function Utf2gb($utfstr)
{
	global $STR;
	return $STR->charset($utfstr,"UTF-8","GBK");
}

function GetSystemUrl()
{
	$myurl = "http://".str_replace("http://","",$_SERVER["SERVER_NAME"]);
	$docu = $_SERVER["PHP_SELF"];
	$array = explode("/",$docu);
	$count = count($array);
	if($count>1)
	{
		foreach($array AS $key=>$value)
		{
			$value = trim($value);
			if($value)
			{
				if(($key+1) < $count)
				{
					$myurl .= "/".$value;
				}
			}
		}
	}
	$myurl .= "/";
	return $myurl;
}

function HEAD()
{
	global $FS,$DB,$prefix;
	global $TPL;
	if(file_exists("data/nav_".LANGUAGE_ID.".php"))
	{
		include_once("data/nav_".LANGUAGE_ID.".php");
	}
	else
	{
		$sql = "SELECT * FROM ".$prefix."nav WHERE language='".LANGUAGE_ID."' ORDER BY taxis ASC,id DESC";
		$qgnav = $DB->qgGetAll($sql);
		$FS->qgWrite($qgnav,"data/nav_".$language.".php","qgnav");
	}
	$TPL->set_var("qgnav",$qgnav);
}
function FOOT($tplfile)
{
	global $FS,$DB,$prefix;
	global $TPL;
	#[引入页脚信息]
	$TPL->p($tplfile);
	REWRITE();
}


function REWRITE()
{
	global $urlRewrite,$system,$siteurl;
	if($urlRewrite)
	{
		#[定义常量，这些常量是给Rewrite用的]
		define("RW_SITE_URL",$siteurl);
		define("RW_TPL_FOLDER",TPL_FOLDER);
		define("RW_UPFILES","upfiles");
		define("RW_LANG",LANGUAGE_SIGN);
		include_once("class/rewrite.class.php");
		$RW = new Rewrite();
		$content = ob_get_contents();
		ob_end_clean();
		$content = $RW->qg_rewrite($content);
		echo $content;
		ob_end_flush();
	}
	exit;
}

function ContentPageArray($content,$url,$pageid=0)
{
	$page_content = explode("[:page:]",$content);
	$pageid = intval($pageid);
	if($pageid < 1)
	{
		$pageid = 1;
	}
	$page_total = count($page_content);
	$pagelist = page($url,$page_total,1,$pageid);#[获取分页的数组]
	$content = $page_content[($pageid-1)];
	$content = preg_replace("/<div/isU","<p style='margin-top:0px;margin-bottom:0px;padding:0px;'",$content);
	$content = preg_replace("/<\/div>/isU","</p>",$content);
	return array($content,$pagelist);
}

#[显示语言及模板]
function SelectLangTpl()
{
	global $DB,$prefix,$FS;
	if(file_exists("data/cache/lang_select.php"))
	{
		include_once("data/cache/lang_select.php");
	}
	else
	{
		$langlist = $DB->qgGetAll("SELECT sign,name FROM ".$prefix."lang WHERE ifuse='1' ORDER BY ifdefault DESC,sign ASC,id DESC");
		$FS->qgWrite($langlist,"data/cache/lang_select.php","langlist");
	}
	$select = "<table cellpadding='1' cellspacing='0'><tr><td><select onchange='sys_change_lang(this.value)'>";
	foreach((is_array($langlist) ? $langlist : array()) AS $key=>$value)
	{
		$select .= "<option value='".$value["sign"]."'";
		if($value["sign"] == $_SESSION["qglang"]["sign"])
		{
			$select .= " selected";
		}
		$select .= ">".$value["name"]."</option>";
	}
	$select .= "</select>";
	$select .= "</td><td>";
	$select .= "<select onchange='sys_change_tpl(this.value)'>";
	if(file_exists("data/cache/style_select_".LANGUAGE_ID.".php"))
	{
		include_once("data/cache/style_select_".LANGUAGE_ID.".php");
	}
	else
	{
		$tpl_list = $DB->qgGetAll("SELECT folder,name FROM ".$prefix."tpl WHERE language='".LANGUAGE_ID."' ORDER BY isdefault DESC,taxis ASC,id DESC");
		$FS->qgWrite($tpl_list,"data/cache/style_select_".LANGUAGE_ID.".php","tpl_list");
	}
	foreach((is_array($tpl_list) ? $tpl_list : array()) AS $key=>$value)
	{
		$select .= "<option value='".$value["folder"]."'";
		if($value["folder"] == $_SESSION["tpl_folder"])
		{
			$select .= " selected";
		}
		$select .= ">".$value["name"]."</option>";
	}
	$select .= "</select>";
	$select .= "</td></tr></table>";
	$select .= "<span style='display:none;'><script type='text/javascript'>\nfunction sys_change_lang(m){var url='home.php?langsign='+m;window.location.href=url;}\n";
	$select .= "function sys_change_tpl(m){var url='home.php?template='+m;window.location.href=url;}</script></span>";
	$FS->qgWrite($select,"data/cache/lang_style_select.php");
	return $select;
}
?>