<?php
#------------------------------------------------------------------------------
#[谢您使用情感家园企业站程序：qgweb]
#[本程序由情感开发完成，当前版本：5.0]
#[本程序基于LGPL授权发布]
#[如果您使用正式版，请将授权文件用FTP上传至copyright目录中]
#[官方网站：www.phpok.com   www.qinggan.net]
#[客服邮箱：qinggan@188.com]
#[文件：sqlite.class.php]
#------------------------------------------------------------------------------

#[类库sql]
class qgSQL
{
	var $queryCount = 0;
	var $dbname;
	var $conn;
	var $result;
	var $rsType = SQLITE3_ASSOC;
	var $queryTimes = 0;#[查询时间]

	#[构造函数]
	function qgSQL($dbname, $dbdata = "", $dbuser = "", $dbpass = "", $dbOpenType = false)
	{
		$this->dbname = $dbname;
		$this->connect();
		unset($dbname, $dbdata, $dbuser, $dbpass, $dbOpenType);
	}

	#[兼容PHP5]
	function __construct($dbname, $dbdata = "", $dbuser = "", $dbpass = "", $dbOpenType = false)
	{
		$this->qgSQL($dbname, $dbdata, $dbuser, $dbpass, $dbOpenType);
		unset($dbname, $dbdata, $dbuser, $dbpass, $dbOpenType);
	}

	#[连接数据库]
	function connect()
	{
		try {
			$this->conn = new SQLite3($this->dbname, SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
			$this->conn->exec("PRAGMA encoding = 'UTF-8'");
		} catch (Exception $e) {
			die("SQLite connection failed: " . $e->getMessage());
		}
	}

	#[关闭数据库连接]
	function qgClose()
	{
		if ($this->conn) {
			return $this->conn->close();
		}
		return true;
	}

	#[兼容PHP5]
	function __destruct()
	{
		$this->qgClose();
	}

	function qgQuery($sql, $type = "ASSOC")
	{
		// 转换MySQL的LIMIT语法到SQLite语法
		// 将 "LIMIT x,y" 转换为 "LIMIT y OFFSET x"
		$sql = preg_replace('/LIMIT\s+(\d+)\s*,\s*(\d+)/i', 'LIMIT $2 OFFSET $1', $sql);
		
		$this->rsType = $type != "ASSOC" ? ($type == "NUM" ? SQLITE3_NUM : SQLITE3_BOTH) : SQLITE3_ASSOC;
		$this->result = $this->conn->query($sql);
		$this->queryCount++;
		if ($this->result) {
			return $this->result;
		} else {
			return false;
		}
	}

	function qgBigQuery($sql, $type = "ASSOC")
	{
		// SQLite3 doesn't have unbuffered queries, so we'll use regular query
		return $this->qgQuery($sql, $type);
	}

	function qgGetAll($sql = "", $nocache = false)
	{
		if ($sql) {
			if ($nocache) {
				$this->qgBigQuery($sql);
			} else {
				$this->qgQuery($sql);
			}
		}
		$rs = array();
		if ($this->result) {
			while ($rows = $this->result->fetchArray($this->rsType)) {
				$rs[] = $rows;
			}
		}
		return $rs;
	}

	function qgGetOne($sql = "")
	{
		if ($sql) {
			$this->qgQuery($sql);
		}
		if ($this->result) {
			$rows = $this->result->fetchArray($this->rsType);
			return $rows;
		}
		return false;
	}

	function qgInsertID($sql = "")
	{
		if ($sql) {
			$rs = $this->qgGetOne($sql);
			return $rs;
		} else {
			return $this->conn->lastInsertRowID();
		}
	}

	function qgInsert($sql)
	{
		$this->result = $this->qgQuery($sql);
		if ($this->result) {
			$id = $this->qgInsertID();
			return $id;
		}
		return false;
	}

	function qg_count($sql = "")
	{
		if ($sql) {
			$this->qgQuery($sql);
			$rs = $this->qgGetOne();
			return $rs[0];
		} else {
			// This method should not be called without SQL in SQLite context
			return 0;
		}
	}

	function qgCount($sql = "")
	{
		if ($sql) {
			// Create a count query from the original query
			// This is a simple implementation that should work for most SELECT queries
			$pattern = '/^SELECT\s+(?:DISTINCT\s+)?(?:.*?)\s+FROM\s+(.*?)(?:\s+WHERE\s+(.*?))?(?:\s+GROUP\s+BY\s+(.*?))?(?:\s+HAVING\s+(.*?))?(?:\s+ORDER\s+BY\s+(.*?))?(?:\s+LIMIT\s+(.*?))?$/i';
			if (preg_match($pattern, trim($sql), $matches)) {
				$from_part = isset($matches[1]) ? $matches[1] : '';
				$where_part = isset($matches[2]) ? ' WHERE ' . $matches[2] : '';
				
				$count_sql = "SELECT COUNT(*) as count_num FROM " . $from_part . $where_part;
				$result = $this->qgGetOne($count_sql);
				return isset($result['count_num']) ? $result['count_num'] : 0;
			} else {
				// If we can't parse the query, return 0
				return 0;
			}
		}
		// If no SQL provided, count rows in current result (not practical for SQLite)
		return 0;
	}

	function qgNumFields($sql = "")
	{
		if ($sql) {
			$this->qgQuery($sql);
		}
		if ($this->result) {
			return $this->result->numColumns();
		}
		return 0;
	}

	function qgListFields($table)
	{
		$sql = "PRAGMA table_info({$table})";
		$result = $this->qgGetAll($sql);
		$fields = array();
		foreach ($result as $row) {
			$fields[] = $row['name'];
		}
		return $fields;
	}

	function qgListTables()
	{
		$sql = "SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'";
		$result = $this->qgGetAll($sql);
		$tables = array();
		foreach ($result as $row) {
			$tables[] = $row['name'];
		}
		return $tables;
	}

	function qgTableName($table_list, $i)
	{
		// This function is not relevant for SQLite
		return isset($table_list[$i]) ? $table_list[$i] : false;
	}

	function qgEscapeString($char)
	{
		if (!$char) {
			return false;
		}
		return $this->conn->escapeString($char);
	}

	function get_sqlite_version()
	{
		return SQLite3::version();
	}
}
?>