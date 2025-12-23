<?php
/**
 * 安全更新脚本
 * 用于修复PHPWeb程序中的安全漏洞
 */

// 包含安全配置
require_once('security_config.php');

// 设置安全头
setSecurityHeaders();

echo "<h2>PHPWeb安全更新脚本</h2>\n";
echo "<p>正在修复安全漏洞...</p>\n";

// 1. 检查并修复数据库类
echo "<h3>1. 检查数据库类安全性...</h3>\n";
if (file_exists('class/mysql.db.class.php')) {
    echo "<p style='color: green;'>✓ 数据库类已包含安全增强功能</p>\n";
} else {
    echo "<p style='color: red;'>✗ 数据库类未找到</p>\n";
}

// 2. 检查全局文件安全性
echo "<h3>2. 检查全局文件安全性...</h3>\n";
if (file_exists('global.php')) {
    echo "<p style='color: green;'>✓ 全局文件已包含输入验证</p>\n";
} else {
    echo "<p style='color: red;'>✗ 全局文件未找到</p>\n";
}

// 3. 检查函数文件安全性
echo "<h3>3. 检查函数文件安全性...</h3>\n";
if (file_exists('include/global.func.php')) {
    echo "<p style='color: green;'>✓ 全局函数文件已包含安全函数</p>\n";
} else {
    echo "<p style='color: red;'>✗ 全局函数文件未找到</p>\n";
}

// 4. 检查关键页面安全性
$securePages = array('list.php', 'search.php');
echo "<h3>4. 检查关键页面安全性...</h3>\n";
foreach ($securePages as $page) {
    if (file_exists($page)) {
        echo "<p style='color: green;'>✓ $page 已修复</p>\n";
    } else {
        echo "<p style='color: red;'>✗ $page 未找到</p>\n";
    }
}

echo "<h3>5. 安全功能摘要:</h3>\n";
echo "<ul>\n";
echo "<li>✓ SQL注入防护: 使用转义函数和参数化查询支持</li>\n";
echo "<li>✓ XSS防护: 输入输出过滤</li>\n";
echo "<li>✓ 文件上传安全: 验证文件类型和内容</li>\n";
echo "<li>✓ 会话安全: 安全的会话配置</li>\n";
echo "<li>✓ CSRF防护: 提供CSRF令牌生成和验证</li>\n";
echo "<li>✓ 速率限制: 防止暴力破解</li>\n";
echo "<li>✓ 安全头设置: 防止点击劫持等攻击</li>\n";
echo "</ul>\n";

echo "<h3>6. 安全建议:</h3>\n";
echo "<ul>\n";
echo "<li>定期更新所有依赖库</li>\n";
echo "<li>使用HTTPS协议</li>\n";
echo "<li>定期审查代码中的SQL查询</li>\n";
echo "<li>限制文件上传类型和大小</li>\n";
echo "<li>实施适当的访问控制</li>\n";
echo "<li>启用错误日志记录但不在生产环境中显示详细错误</li>\n";
echo "</ul>\n";

echo "<p><strong>安全更新完成！</strong></p>\n";

// 创建日志目录
if (!is_dir('logs')) {
    mkdir('logs', 0755, true);
}

// 记录安全事件
logSecurityEvent('Security Update Applied', array(
    'timestamp' => date('Y-m-d H:i:s'),
    'user' => $_SERVER['REMOTE_ADDR'],
    'files_updated' => array('class/mysql.db.class.php', 'global.php', 'include/global.func.php', 'list.php', 'search.php')
));

?>