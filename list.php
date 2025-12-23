<?php
require_once("global.php");
$id = intval($id);
#[如果不存在ID时]
if(!$id || $id == "0")
{
	qgheader();
}
# 使用参数化查询或确保ID是整数（已通过intval处理）
$sql = "SELECT c.*,s.sign AS sysgroup_sign FROM ".$prefix."category AS c,".$prefix."sysgroup AS s WHERE c.id='".$id."' AND c.sysgroupid=s.id AND c.status=1";
$thiscate = $DB->qgGetOne($sql);
if(!$thiscate)
{
	qgheader();
}
$sysgroupid = $thiscate["sysgroupid"];
#[如果没有找到系统组]
if(!$sysgroupid)
{
	qgheader();
}
if(!$thiscate["sysgroup_sign"])
{
	qgheader();
}
$sysgroup_sign = $thiscate["sysgroup_sign"];
#[判断模板是否存在]
if($thiscate["tpl_list"] && file_exists(NewTemplate."/".$thiscate["tpl_list"]))
{
	$tpl_list = substr($thiscate["tpl_list"],0,-4);
}
else
{
	if(!file_exists(NewTemplate."/".$sysgroup_sign.".list.htm") && !file_exists(NewTemplate."/../default/".$sysgroup_sign.".list.htm"))
	{
		qgheader();
	}
	$tpl_list = $sysgroup_sign.".list";
}

$sqlid = $thiscate["rootid"] ? $thiscate["rootid"] : $id;
# 确保sqlid也是整数
$sqlid = intval($sqlid);
$sql = "SELECT id,sysgroupid,rootid,parentid,catename,catestyle FROM ".$prefix."category WHERE status='1' AND (rootid='".$sqlid."' OR id='".$sqlid."' OR parentid='".$sqlid."') ORDER BY taxis ASC,id DESC";
$catelist = $DB->qgGetAll($sql);
$menulist = menu_list($catelist,$id);
unset($sql,$sqlid);

#[标题头和向导栏]
$sitetitle_list = array();
foreach($menulist AS $key=>$value)
{
	$sitetitle_list[] = $value["catename"];
	$temp = array();
	$temp["url"] = "list.php?id=".$value["id"];
	$temp["name"] = $value["catename"];
	$lead_menu[$key] = $temp;
	unset($temp);
}
$sitetitle = implode(" - ",$sitetitle_list)." - ".$system["sitename"];
unset($menulist);
$lead_menu = array_reverse($lead_menu);

#[检测是否有子分类，如果有的话收集子分类]
$iscateson = false;
foreach($catelist AS $key=>$value)
{
	if($value["parentid"] == $id)
	{
		$iscateson = true;
		$left_catelist[] = $value;
	}
}
#[如果不存在子分类，那么显示同级分类]
if(!$iscateson)
{
	foreach($catelist AS $key=>$value)
	{
		if($thiscate["parentid"] == $value["parentid"])
		{
			$left_catelist[] = $value;
		}
	}
}
#[根据当前分类得到所有子分类ID]
$sonidlist = array();
$sonidlist = get_son_list($catelist,$id);

$msgcatelist = array();
foreach($catelist AS $key=>$value)
{
	$msgcatelist[$value["id"]] = $value["catename"];
}
unset($catelist);

#[设定每页显示数量]
$psize = intval($thiscate["psize"]) ? intval($thiscate["psize"]) : 30;
$pageurl = "list.php?id=".$id;
#[获取分页ID]
$pageid = intval($pageid);
if($pageid < 1)
{
	$pageid = 1;
}
$offset = $pageid > 0 ? ($pageid-1)*$psize : 0;

$condition = "WHERE m.ifcheck='1'";
#[获取文章列表信息]
$idin = trim(implode(",",$sonidlist));
if($idin)
{
	if(strpos(",".$idin.",",",".$id.",") === false)
	{
		$idin .= ",".$id;
	}
	# 验证IDIN只包含数字和逗号
	$idin = preg_replace('/[^0-9,]/', '', $idin);
	$condition .= " AND m.cateid in(".$idin.")";
}
else
{
	$idin = $id;
	$condition .= " AND m.cateid='".$idin."'";
}
define("QGLIST_ID",$id);#[定义常量QGLIST_ID]
define("QGLIST_IDIN",$idin);#[定义常量QGLIST_IDIN，以供模块调用]
$sql = "SELECT count(*) AS countid FROM ".$prefix."msg AS m ".$condition;
$listcount = $DB->qgGetOne($sql);#[获取总数]
$msglistcount = $listcount["countid"];

$pagelist = page($pageurl,$msglistcount,$psize,$pageid);#[获取分页的数组]

if($thiscate["showtype"] == 3)
{
	$sqlorder = "m.istop DESC,m.hits ASC,m.orderdate DESC,m.id DESC";
}
elseif($thiscate["showtype"] == 2)
{
	$sqlorder = "m.istop DESC,m.hits DESC,m.orderdate DESC,m.id DESC";
}
elseif($thiscate["showtype"] == 1)
{
	$sqlorder = "m.istop DESC,m.postdate DESC,m.orderdate DESC,m.id DESC";
}
else
{
	$sqlorder = "m.istop DESC,m.orderdate DESC,m.id DESC";
}

$sql = "SELECT m.*,u.filename,u.tmpname,u.folder,u.thumbfile,u.markfile FROM ".$prefix."msg AS m LEFT JOIN ".$prefix."upfiles AS u ON m.thumb=u.id ".$condition." ORDER BY ".$sqlorder." LIMIT ".$offset.",".$psize;

$rslist = $DB->qgGetAll($sql,true);
$msglist = array();
foreach($rslist AS $key=>$value)
{
	$value["catename"] = $msgcatelist[$value["cateid"]];
	$value["v_catename"] = $STR->cut($value["catename"],8,"");
	$value["postdate"] = date("m-d H:i",$value["postdate"]);
	$value["cut_subject"] = CutString($value["subject"],48);
	$msglist[] = $value;
}
unset($rslist,$msgcatelist,$msglistcount,$listcount,$sql);
$cateid = $id;#[兼容两个]
HEAD();
FOOT($tpl_list);
?>