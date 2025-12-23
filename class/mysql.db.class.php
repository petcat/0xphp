<?php
#------------------------------------------------------------------------------
#[谢您使用情感家园企业站程序：qgweb]
#[本程序由情感开发完成，当前版本：5.0]
#[本程序基于LGPL授权发布]
#[如果您使用正式版，请将授权文件用FTP上传至copyright目录中]
#[官方网站：www.phpok.com   www.qinggan.net]
#[客服邮箱：qinggan@188.com]
#[文件：mysql.class.php]
#------------------------------------------------------------------------------

#[类库sql]
class qgSQL
{
	var $queryCount = 0;
	var $host;
	var $user;
	var $pass;
	var $data;
	var $conn;
	var $result;
	var $rsType = MYSQL_ASSOC;
	var $queryTimes = 0;#[查询时间]

	#[构造函数]
	function qgSQL($dbhost,$dbdata,$dbuser="",$dbpass="",$dbOpenType=false)
	{
		$this->host = $dbhost;
		$this->user = $dbuser;
		$this->pass = $dbpass;
		$this->data = $dbdata;
		$this->connect($dbOpenType);
		unset($dbhost,$dbdata,$dbuser,$dbpass,$dbOpenType);
	}

	#[兼容PHP5]
	function __construct($dbhost,$dbdata,$dbuser="",$dbpass="",$dbOpenType=false)
	{
		$this->qgSQL($dbhost,$dbdata,$dbuser,$dbpass,$dbOpenType);
		unset($dbhost,$dbdata,$dbuser,$dbpass,$dbOpenType);
	}

	#[连接数据库]
	function connect($dbconn = false)
	{
		if($dbconn)
		{
			$this->conn = mysql_pconnect($this->host,$this->user,$this->pass) or die(mysql_errno()." : ".mysql_error());
		}
		else
		{
			$this->conn = mysql_connect($this->host,$this->user,$this->pass) or die(mysql_errno()." : ".mysql_error());
		}

		$mysql_version = $this->get_mysql_version();

		if($mysql_version>"4.1")
		{
			mysql_query("SET NAMES 'utf8'",$this->conn);
		}

		if($mysql_version>"5.0.1")
		{
			mysql_query("SET sql_mode=''",$this->conn);
		}

		mysql_select_db($this->data) or die(mysql_errno()." : ".mysql_error());
	}

	#[关闭数据库连接，当您使用持续连接时该功能失效]
	function qgClose()
	{
		return mysql_close($this->conn);
	}

	#[兼容PHP5]
	function __destruct()
	{
		return mysql_close($this->conn);
	}

	function qgQuery($sql,$type="ASSOC")
	{
		$this->rsType = $type != "ASSOC" ? ($type == "NUM" ? MYSQL_NUM : MYSQL_BOTH) : MYSQL_ASSOC;
		$this->result = mysql_query($sql,$this->conn);
		$this->queryCount++;
		if($this->result)
		{
			return $this->result;
		}
		else
		{
			return false;
		}
	}

	function qgBigQuery($sql,$type="ASSOC")
	{
		$this->rsType = $type != "ASSOC" ? ($type == "NUM" ? MYSQL_NUM : MYSQL_BOTH) : MYSQL_ASSOC;
		$this->result = mysql_unbuffered_query($sql,$this->conn);
		$this->queryCount++;
		if($this->result)
		{
			return $this->result;
		}
		else
		{
			return false;
		}
	}

	function qgGetAll($sql="",$nocache=false)
	{
		if($sql)
		{
			if($nocache)
			{
				$this->qgBigQuery($sql);
			}
			else
			{
				$this->qgQuery($sql);
			}
		}
		$rs = array();
		while($rows = mysql_fetch_array($this->result,$this->rsType))
		{
			$rs[] = $rows;
		}
		return $rs;
	}

	function qgGetOne($sql = "")
	{
		if($sql)
		{
			$this->qgQuery($sql);
		}
		$rows = mysql_fetch_array($this->result,$this->rsType);
		return $rows;
	}

	function qgInsertID($sql="")
	{
		if($sql)
		{
			$rs = $this->qgGetOne($sql);
			return $rs;
		}
		else
		{
			return mysql_insert_id($this->conn);
		}
	}

	function qgInsert($sql)
	{
		$this->result = $this->qgQuery($sql);
		$id = $this->qgInsertID();
		return $id;
	}

	function qg_count($sql="")
	{
		if($sql)
		{
			$this->qgQuery($sql,"NUM");
			$rs = $this->qgGetOne();
			return $rs[0];
		}
		else
		{
			$rsC = mysql_num_rows($this->result);
			return $rsC;
		}
	}

	function qgCount($sql = "")
	{
		if($sql)
		{
			$this->qgQuery($sql);
			unset($sql);
		}
		$rsC = mysql_num_rows($this->result);
		return $rsC;
	}

	function qgNumFields($sql = "")
	{
		if($sql)
		{
			$this->qgQuery($sql);
		}
		return @mysql_num_fields($this->result);
	}

	function qgListFields($table)
	{
		$rs = @mysql_list_fields($this->data,$table,$this->conn);
		$count = mysql_num_fields($rs);
		for($i=0;$i<$count;$i++)
		{
			$rslist[] = @mysql_field_name($rs,$i);
		}
		return $rslist;
	}


	function qgListTables()
	{
		$query = mysql_list_tables($this->data);
		$rs = array();
		while($rows = @mysql_fetch_array($query))
		{
			$rs[] = $rows[0];
		}
		return $rs;
	}

	function qgTableName($table_list,$i)
	{
		return @mysql_tablename($table_list,$i);
	}

	function qgEscapeString($char)
	{
		if(!$char)
		{
			return false;
		}
		return @mysql_real_escape_string($char, $this->conn);
	}

	#[安全的参数化查询方法]
	function qgPreparedQuery($sql, $params = array())
	{
		// 替换占位符 ? 为实际值，使用转义
		foreach($params as $param) {
			$escaped_param = $this->qgEscapeString($param);
			$sql = preg_replace('/\?/', "'" . $escaped_param . "'", $sql, 1);
		}
		return $this->qgQuery($sql);
	}

	#[安全的参数化查询获取所有结果方法]
	function qgPreparedGetAll($sql, $params = array())
	{
		$this->qgPreparedQuery($sql, $params);
		$rs = array();
		while($rows = mysql_fetch_array($this->result,$this->rsType))
		{
			$rs[] = $rows;
		}
		return $rs;
	}

	#[安全的参数化查询获取单个结果方法]
	function qgPreparedGetOne($sql, $params = array())
	{
		$this->qgPreparedQuery($sql, $params);
		$rows = mysql_fetch_array($this->result,$this->rsType);
		return $rows;
	}

	function get_mysql_version()
	{
		return mysql_get_server_info();
	}
}
?>