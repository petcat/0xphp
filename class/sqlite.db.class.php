<?php
#------------------------------------------------------------------------------
#[情感家园企业站程序：qgweb]
#[本程序由情感开发完成，当前版本：5.0]
#[本程序基于LGPL授权发布]
#[如果您使用正式版，请将授权文件用FTP上传至copyright目录中]
#[官方网站：www.phpok.com   www.qinggan.net]
#[客服邮箱：qinggan@188.com]
#[文件：sqlite.class.php]
#------------------------------------------------------------------------------

#[类库sql - SQLite3版本]
class qgSQL
{
    var $queryCount = 0;
    var $dbPath;
    var $conn;
    var $result;
    var $rsType = SQLITE3_ASSOC;
    var $queryTimes = 0;#[查询时间]

    #[构造函数]
    function qgSQL($dbPath, $dbOpenType=false)
    {
        $this->dbPath = $dbPath;
        $this->connect($dbOpenType);
        unset($dbPath, $dbOpenType);
    }

    #[兼容PHP5]
    function __construct($dbPath, $dbOpenType=false)
    {
        $this->qgSQL($dbPath, $dbOpenType);
        unset($dbPath, $dbOpenType);
    }

    #[连接数据库]
    function connect($dbOpenType = false)
    {
        try {
            if($dbOpenType) {
                // 持久连接，SQLite3不支持，使用普通连接
                $this->conn = new SQLite3($this->dbPath, SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
            } else {
                $this->conn = new SQLite3($this->dbPath, SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
            }
            
            // 设置错误处理模式
            $this->conn->enableExceptions(true);
            
            // 设置UTF-8编码
            $this->conn->exec("PRAGMA encoding = 'UTF-8'");
            
            // 设置外键约束
            $this->conn->exec("PRAGMA foreign_keys = ON");
            
        } catch (Exception $e) {
            die("SQLite3连接错误: " . $e->getMessage());
        }
    }

    #[关闭数据库连接]
    function qgClose()
    {
        if($this->conn) {
            return $this->conn->close();
        }
        return true;
    }

    #[析构函数]
    function __destruct()
    {
        $this->qgClose();
    }

    function qgQuery($sql, $type="ASSOC")
    {
        $this->rsType = $type != "ASSOC" ? ($type == "NUM" ? SQLITE3_NUM : SQLITE3_BOTH) : SQLITE3_ASSOC;
        
        try {
            $this->result = $this->conn->query($sql);
            $this->queryCount++;
            
            if($this->result) {
                return $this->result;
            } else {
                return false;
            }
        } catch (Exception $e) {
            error_log("SQL Error: " . $e->getMessage() . " - SQL: " . $sql);
            return false;
        }
    }

    function qgBigQuery($sql, $type="ASSOC")
    {
        return $this->qgQuery($sql, $type);
    }

    function qgGetAll($sql="", $nocache=false)
    {
        if($sql) {
            if($nocache) {
                $this->qgBigQuery($sql);
            } else {
                $this->qgQuery($sql);
            }
        }
        
        if (!$this->result) {
            return array();
        }
        
        $rs = array();
        while($rows = $this->result->fetchArray($this->rsType)) {
            $rs[] = $rows;
        }
        return $rs;
    }

    function qgGetOne($sql = "")
    {
        if($sql) {
            $this->qgQuery($sql);
        }
        
        if (!$this->result) {
            return null;
        }
        
        $rows = $this->result->fetchArray($this->rsType);
        return $rows;
    }

    function qgInsertID($sql="")
    {
        if($sql) {
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

    function qg_count($sql="")
    {
        if($sql) {
            $this->qgQuery($sql, "NUM");
            $rs = $this->qgGetOne();
            return $rs[0];
        } else {
            // 如果没有提供SQL，需要先执行查询
            if ($this->result) {
                // 这个方法在SQLite3中不太适用，因为结果集不同
                // 返回0作为默认值
                return 0;
            }
            return 0;
        }
    }

    function qgCount($sql = "")
    {
        if($sql) {
            $this->qgQuery($sql);
            unset($sql);
        }
        
        if ($this->result) {
            // SQLite3没有mysql_num_rows等价函数，我们需要获取所有行来计算
            $count = 0;
            while ($row = $this->result->fetchArray(SQLITE3_NUM)) {
                $count++;
            }
            // 重新执行查询以便后续使用
            if($sql) {
                $this->qgQuery($sql);
            }
            return $count;
        }
        return 0;
    }

    function qgNumFields($sql = "")
    {
        if($sql) {
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
        foreach($result as $row) {
            $fields[] = $row['name'];
        }
        return $fields;
    }

    function qgListTables()
    {
        $sql = "SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'";
        $result = $this->qgGetAll($sql);
        $tables = array();
        foreach($result as $row) {
            $tables[] = $row['name'];
        }
        return $tables;
    }

    function qgTableName($table_list, $i)
    {
        // SQLite3不使用此方法，返回空值
        return isset($table_list[$i]) ? $table_list[$i] : '';
    }

    function qgEscapeString($char)
    {
        if(!$char) {
            return false;
        }
        
        // 使用SQLite3的转义方法
        return $this->conn->escapeString($char);
    }

    function get_sqlite_version()
    {
        return $this->conn->version()['versionString'];
    }
    
    // 添加准备语句方法以提高安全性
    function prepare($sql) {
        return $this->conn->prepare($sql);
    }
}
?>