<?php
// SQLite数据库初始化脚本
require_once("config.php");
require_once("class/sqlite.db.class.php");

try {
    $DB = new qgSQL($dbName, $dbData, $dbUser, $dbPass, false);
    echo "连接SQLite数据库成功！\n";
    
    // 创建管理员表
    $sql_admin = "CREATE TABLE IF NOT EXISTS " . $prefix . "admin (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user TEXT NOT NULL,
        pass TEXT NOT NULL,
        truename TEXT,
        email TEXT,
        typer TEXT DEFAULT 'system',
        modulelist TEXT,
        taxis INTEGER DEFAULT 255,
        status INTEGER DEFAULT 1
    )";
    $DB->qgQuery($sql_admin);
    echo "管理员表创建成功\n";
    
    // 创建语言表
    $sql_lang = "CREATE TABLE IF NOT EXISTS " . $prefix . "lang (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        sign TEXT NOT NULL,
        ifdefault INTEGER DEFAULT 0,
        taxis INTEGER DEFAULT 255,
        status INTEGER DEFAULT 1
    )";
    $DB->qgQuery($sql_lang);
    echo "语言表创建成功\n";
    
    // 创建导航表
    $sql_nav = "CREATE TABLE IF NOT EXISTS " . $prefix . "nav (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        parent_id INTEGER DEFAULT 0,
        title TEXT NOT NULL,
        url TEXT,
        taxis INTEGER DEFAULT 255,
        target INTEGER DEFAULT 0,
        status INTEGER DEFAULT 1,
        language INTEGER DEFAULT 1
    )";
    $DB->qgQuery($sql_nav);
    echo "导航表创建成功\n";
    
    // 创建内容表
    $sql_content = "CREATE TABLE IF NOT EXISTS " . $prefix . "content (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        cate_id INTEGER DEFAULT 0,
        title TEXT NOT NULL,
        content TEXT,
        pic TEXT,
        taxis INTEGER DEFAULT 255,
        hits INTEGER DEFAULT 0,
        postdate INTEGER,
        language INTEGER DEFAULT 1,
        status INTEGER DEFAULT 1
    )";
    $DB->qgQuery($sql_content);
    echo "内容表创建成功\n";
    
    // 创建分类表
    $sql_cate = "CREATE TABLE IF NOT EXISTS " . $prefix . "cate (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        parent_id INTEGER DEFAULT 0,
        title TEXT NOT NULL,
        content TEXT,
        taxis INTEGER DEFAULT 255,
        status INTEGER DEFAULT 1,
        language INTEGER DEFAULT 1
    )";
    $DB->qgQuery($sql_cate);
    echo "分类表创建成功\n";
    
    // 插入默认语言
    $lang_exists = $DB->qgGetOne("SELECT id FROM " . $prefix . "lang WHERE sign='zh'");
    if (!$lang_exists) {
        $DB->qgQuery("INSERT INTO " . $prefix . "lang (title, sign, ifdefault, taxis, status) VALUES ('中文', 'zh', 1, 1, 1)");
        echo "默认语言插入成功\n";
    }
    
    // 插入默认管理员账户 (用户名: admin, 密码: 123456 经过md5加密)
    $admin_exists = $DB->qgGetOne("SELECT id FROM " . $prefix . "admin WHERE user='admin'");
    if (!$admin_exists) {
        $DB->qgQuery("INSERT INTO " . $prefix . "admin (user, pass, truename, email, typer, status) VALUES ('admin', '" . md5('123456') . "', '管理员', 'admin@example.com', 'system', 1)");
        echo "默认管理员账户创建成功 (用户名: admin, 密码: 123456)\n";
    }
    
    // 插入默认导航
    $nav_exists = $DB->qgGetOne("SELECT id FROM " . $prefix . "nav WHERE title='首页'");
    if (!$nav_exists) {
        $DB->qgQuery("INSERT INTO " . $prefix . "nav (title, url, taxis, status, language) VALUES ('首页', 'index.php', 1, 1, 1)");
        echo "默认导航创建成功\n";
    }
    
    echo "数据库初始化完成！\n";
    
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
}

if (isset($DB)) {
    $DB->qgClose();
}
?>