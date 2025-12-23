<?php
#============================
#	Filename: search.php
#	Note	: 搜索
#	Version : 2.0
#	Author  : qinggan
#	Update  : 2008-02-15
#============================
require_once("global.php");
$sysact = $_GET["act"] ? $_GET["act"] : $_POST["act"];
if($sysact == "searchok")
{
	$keywords = SafeHtml(rawurldecode($_GET["keywords"]));
	if(!$keywords)
	{
		qgheader();
	}
	#[整理]
	$stype = SafeHtml($_GET["stype"]);
	if($stype)
	{
		# 使用参数化查询或验证输入
		$stype = $DB->qgEscapeString($stype);
		$sql = "SELECT c.id FROM ".$prefix."category AS c,".$prefix."sysgroup AS s WHERE s.sign='".$stype."' AND c.sysgroupid=s.id AND c.status='1' AND c.language='".LANGUAGE_ID."'";
	}
	else
	{
		$sql = "SELECT id FROM ".$prefix."category WHERE status='1' AND language='".LANGUAGE_ID."'";
	}
	$idlist = $DB->qgGetAll($sql);
	unset($sql);
	if(!$idlist)
	{
		qgheader();
	}
	foreach($idlist AS $key=>$value)
	{
		$id_array[] = $value["id"];
	}
	$idin = implode(",",$id_array);
	unset($id_array,$idlist);
	define("QGLIST_ID",0);#[定义常量QGLIST_ID]
	define("QGLIST_IDIN",$idin);#[定义常量QGLIST_IDIN，以供模块调用]
	#[]
	# 验证并过滤keywords以防止SQL注入
	$keywords = $DB->qgEscapeString($keywords);
	$condition = " FROM ".$prefix."msg AS m,".$prefix."category AS c WHERE m.cateid in(".$idin.") AND m.ifcheck='1' AND m.subject LIKE '%".$keywords."%' AND m.cateid=c.id";
	$count = intval($_GET["count"]);
	if(!$count || $count<1)
	{
		$s_count = $DB->qgGetOne("SELECT count(*) AS countid ".$condition);
		$count = $s_count["countid"];
		unset($s_count);
	}
	$pageid = intval($_GET["pageid"]);
	$psize = 30;#[每页保留30条搜索]
	$offset = $pageid>0 ? ($pageid-1)*$psize : 0;
	$sql = "SELECT m.id,m.cateid,m.subject,m.postdate,m.hits,m.ext_docket,c.catename ".$condition." ORDER BY istop DESC,isvouch DESC,isbest DESC,orderdate DESC,postdate DESC,id DESC LIMIT ".$offset.",".$psize;
	$searchlist = $DB->qgGetAll($sql);
	if(!$searchlist)
	{
		qgheader();
	}
	$pageurl = "search.php?act=searchok&keywords=".rawurlencode($keywords)."&stype=".$stype."&count=".$count;
	$pagelist = page($pageurl,$count,$psize,$pageid);#[获取分页的数组]
	#[标题头]
	$sitetitle = $langs["searchok"].":".$keywords." - ".$system["sitename"];
	#[向导栏]
	$lead_menu[0]["url"] = pageurl;
	$lead_menu[0]["name"] = $langs["searchok"].":".$keywords;
	HEAD();
	FOOT("search");
}
elseif($sysact == "searchlink")
{
	$keywords = SafeHtml($_POST["keywords"]);
	if(!$keywords)
	{
		qgheader();
	}
	$stype = SafeHtml($_POST["stype"]);
	$refreshurl = "search.php?act=searchok&keywords=".rawurlencode($keywords)."&stype=".$stype;
	qgheader($refreshurl);
}
else
{
	qgheader();
}
?>