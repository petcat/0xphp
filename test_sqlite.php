<?php
// 测试SQLite连接
require_once("config.php");
require_once("class/sqlite.db.class.php");

try {
    $DB = new qgSQL($dbName, $dbData, $dbUser, $dbPass, false);
    echo "SQLite数据库连接成功！\n";
    
    // 测试基本查询
    $result = $DB->qgQuery("SELECT sqlite_version() as version");
    if ($result) {
        $row = $DB->qgGetOne();
        echo "SQLite版本: " . $row['version'] . "\n";
    }
    
    // 测试创建表
    $create_table = "CREATE TABLE IF NOT EXISTS test_table (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($DB->qgQuery($create_table)) {
        echo "测试表创建成功！\n";
        
        // 插入测试数据
        $insert_sql = "INSERT INTO test_table (name) VALUES ('测试数据')";
        $insert_id = $DB->qgInsert($insert_sql);
        if ($insert_id) {
            echo "测试数据插入成功，ID: $insert_id\n";
            
            // 查询数据
            $select_sql = "SELECT * FROM test_table WHERE id = $insert_id";
            $data = $DB->qgGetAll($select_sql);
            if ($data) {
                echo "查询结果：\n";
                foreach ($data as $row) {
                    echo "- ID: " . $row['id'] . ", Name: " . $row['name'] . ", Created: " . $row['created_at'] . "\n";
                }
            }
        }
    }
    
    echo "SQLite测试完成！\n";
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
}

// 关闭连接
if (isset($DB)) {
    $DB->qgClose();
}
?>