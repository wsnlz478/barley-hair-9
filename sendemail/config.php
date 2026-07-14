<?php
/**
 * 数据库配置文件
 * 使用 MySQL 存储表单数据
 */

// MySQL 数据库配置
// 优先使用环境变量，如果未设置则使用默认值（开发环境）
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'test_nsnm_com_cn');
define('DB_USER', getenv('DB_USER') ?: 'test_nsnm_com_cn');
define('DB_PASS', getenv('DB_PASSWORD') ?: '8JcrMJDcaXFrt6sH'); // 生产环境建议设置环境变量
define('DB_CHARSET', 'utf8mb4');

// 获取数据库连接
function getDB() {
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $db = new PDO($dsn, DB_USER, DB_PASS, $options);

        // 检查表是否存在，不存在则创建（只在首次连接时执行）
        static $tableCreated = false;
        if (!$tableCreated) {
            $db->exec("
                CREATE TABLE IF NOT EXISTS form_submissions (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    nickname VARCHAR(255) NOT NULL,
                    project VARCHAR(255) NOT NULL,
                    contact VARCHAR(255) NOT NULL,
                    message TEXT,
                    site_domain VARCHAR(255),
                    site_url VARCHAR(500),
                    user_ip VARCHAR(45),
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            $tableCreated = true;
        }

        return $db;
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());
        return null;
    }
}

// 保存表单数据到数据库
function saveToDatabase($data) {
    $db = getDB();
    if (!$db) {
        return false;
    }
    
    try {
        $stmt = $db->prepare("
            INSERT INTO form_submissions (nickname, project, contact, message, site_domain, site_url, user_ip)
            VALUES (:nickname, :project, :contact, :message, :site_domain, :site_url, :user_ip)
        ");
        
        return $stmt->execute([
            ':nickname' => $data['nickname'],
            ':project' => $data['project'],
            ':contact' => $data['contact'],
            ':message' => $data['message'] ?? '',
            ':site_domain' => $data['site_domain'] ?? '',
            ':site_url' => $data['site_url'] ?? '',
            ':user_ip' => $data['user_ip'] ?? ''
        ]);
    } catch (PDOException $e) {
        error_log('Database save error: ' . $e->getMessage());
        return false;
    }
}
?>
