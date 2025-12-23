<?php
/**
 * 安全配置文件
 * 用于修复代码中的安全漏洞
 */

// 定义安全常量
define('SECURITY_ENABLED', true);

// 安全头设置
function setSecurityHeaders() {
    // 防止点击劫持
    header('X-Frame-Options: DENY');
    
    // 防止MIME类型嗅探
    header('X-Content-Type-Options: nosniff');
    
    // 防止跨站脚本攻击
    header('X-XSS-Protection: 1; mode=block');
    
    // 内容安全策略
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline';");
}

// 验证输入函数
function validateInput($input, $type = 'string', $options = array()) {
    switch ($type) {
        case 'int':
            $min = isset($options['min']) ? $options['min'] : null;
            $max = isset($options['max']) ? $options['max'] : null;
            return validateInteger($input, $min, $max);
        case 'email':
            return validateEmail($input);
        case 'string':
        default:
            $maxLength = isset($options['max_length']) ? $options['max_length'] : 255;
            $pattern = isset($options['pattern']) ? $options['pattern'] : '/^[a-zA-Z0-9\s\-_\.]+$/';
            return validateString($input, $maxLength, $pattern);
    }
}

// 防止CSRF攻击的函数
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// 安全的文件上传验证
function validateUploadFile($file, $allowedTypes = array('jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx')) {
    if (!isset($file['tmp_name']) || !file_exists($file['tmp_name'])) {
        return false;
    }
    
    // 获取文件扩展名
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // 检查文件类型
    if (!in_array($fileExtension, $allowedTypes)) {
        return false;
    }
    
    // 检查文件内容
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    // 额外的安全检查：防止上传PHP等可执行文件
    $dangerousTypes = array('php', 'php3', 'php4', 'php5', 'phtml', 'asp', 'aspx', 'jsp', 'exe', 'bat', 'sh');
    if (in_array($fileExtension, $dangerousTypes)) {
        return false;
    }
    
    return true;
}

// 生成安全的文件名
function generateSecureFilename($originalName) {
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    $name = pathinfo($originalName, PATHINFO_FILENAME);
    
    // 清理文件名
    $name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);
    $name = substr($name, 0, 100); // 限制长度
    
    // 生成唯一文件名
    $uniqueName = $name . '_' . time() . '_' . bin2hex(random_bytes(8));
    
    return $uniqueName . '.' . $extension;
}

// 安全的输出函数
function safeOutput($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// 验证URL安全性
function validateUrl($url) {
    // 检查是否为有效的URL格式
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }
    
    // 检查协议
    $parsed = parse_url($url);
    if (!in_array(strtolower($parsed['scheme']), ['http', 'https'])) {
        return false;
    }
    
    // 防止SSRF攻击，检查是否为内部地址
    $host = $parsed['host'];
    if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
        // 如果是私有地址或保留地址，进一步验证
        $ip = gethostbyname($host);
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return false;
        }
    }
    
    return true;
}

// 安全的会话初始化
function secureSessionStart() {
    // 设置安全的会话参数
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1); // 如果使用HTTPS
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Strict');
    
    session_start();
}

// 检查用户权限
function checkUserPermission($requiredPermission = '') {
    // 实现权限检查逻辑
    if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
        return false;
    }
    
    // 可以添加更详细的权限检查
    return true;
}

// 日志记录函数
function logSecurityEvent($event, $details = array()) {
    $logEntry = date('Y-m-d H:i:s') . " - SECURITY - " . $event . " - " . 
                "IP: " . $_SERVER['REMOTE_ADDR'] . " - " . 
                "User Agent: " . $_SERVER['HTTP_USER_AGENT'] . " - " .
                "Details: " . json_encode($details) . "\n";
    
    error_log($logEntry, 3, '/workspace/logs/security.log');
}

// 安全的数据库查询辅助函数
function buildSafeQuery($table, $conditions = array(), $fields = array('*'), $orderBy = '', $limit = '') {
    global $DB, $prefix;
    
    // 验证表名
    $allowedTables = array('user', 'msg', 'category', 'admin', 'book', 'order', 'feedback', 'job', 'lang', 'nav', 'sysgroup', 'tpl', 'upfiles');
    if (!in_array($table, $allowedTables)) {
        throw new Exception("Invalid table name: $table");
    }
    
    // 构建字段列表
    $fieldList = '*';
    if (!empty($fields) && is_array($fields)) {
        $safeFields = array();
        foreach ($fields as $field) {
            // 简单验证字段名
            if (preg_match('/^[a-zA-Z0-9_]+$/', $field)) {
                $safeFields[] = $field;
            }
        }
        $fieldList = implode(', ', $safeFields);
    }
    
    // 构建查询
    $sql = "SELECT $fieldList FROM " . $prefix . $table;
    
    if (!empty($conditions)) {
        $whereClause = array();
        $params = array();
        
        foreach ($conditions as $field => $value) {
            // 验证字段名
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $field)) {
                continue;
            }
            
            $whereClause[] = "$field = ?";
            $params[] = $value;
        }
        
        if (!empty($whereClause)) {
            $sql .= " WHERE " . implode(' AND ', $whereClause);
        }
    }
    
    if (!empty($orderBy)) {
        // 简单验证排序字段
        if (preg_match('/^[a-zA-Z0-9_,\s]+$/', $orderBy)) {
            $sql .= " ORDER BY $orderBy";
        }
    }
    
    if (!empty($limit)) {
        // 验证limit参数
        if (preg_match('/^[0-9, ]+$/', $limit)) {
            $sql .= " LIMIT $limit";
        }
    }
    
    return array('sql' => $sql, 'params' => $params);
}

// 防止暴力破解
function checkRateLimit($identifier, $maxAttempts = 5, $timeWindow = 900) { // 15分钟
    $attemptsKey = 'login_attempts_' . $identifier;
    $attempts = isset($_SESSION[$attemptsKey]) ? $_SESSION[$attemptsKey] : array();
    
    // 清理过期尝试
    $attempts = array_filter($attempts, function($time) use ($timeWindow) {
        return (time() - $time) < $timeWindow;
    });
    
    if (count($attempts) >= $maxAttempts) {
        return false; // 超出限制
    }
    
    // 记录新尝试
    $attempts[] = time();
    $_SESSION[$attemptsKey] = $attempts;
    
    return true;
}

// 清理速率限制记录
function clearRateLimit($identifier) {
    $attemptsKey = 'login_attempts_' . $identifier;
    unset($_SESSION[$attemptsKey]);
}
?>