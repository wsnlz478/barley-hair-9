<?php
/**
 * 表单数据管理后台 - 增强版
 * 功能：数据查看、统计分析、Excel导出
 */

// 简单的访问控制 - 通过环境变量或配置文件设置密码
session_start();

// 从环境变量读取密码，如果未设置则使用默认值
$admin_password = getenv('ADMIN_PASSWORD') ?: 'barley2026';

// 检查是否已登录
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // 处理登录请求
    if (isset($_POST['admin_password'])) {
        if ($_POST['admin_password'] === $admin_password) {
            $_SESSION['admin_logged_in'] = true;
        } else {
            die('密码错误');
        }
    } else {
        // 显示登录表单
        echo '<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理后台登录</title>
    <style>
        body { font-family: "Microsoft YaHei", sans-serif; background: #f5f5f5; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 350px; }
        h2 { text-align: center; color: #333; margin-bottom: 30px; }
        input[type="password"] { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; margin-bottom: 20px; }
        button { width: 100%; padding: 12px; background: #2b85e4; color: #fff; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
        button:hover { background: #1a6cc7; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>管理后台登录</h2>
        <form method="POST">
            <input type="password" name="admin_password" placeholder="请输入管理密码" required autofocus>
            <button type="submit">登录</button>
        </form>
    </div>
</body>
</html>';
        exit;
    }
}

require_once __DIR__ . '/config.php';

// 获取数据库连接
$db = getDB();
if (!$db) {
    die('数据库连接失败');
}

// 处理导出请求
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    exportToExcel($db, $_GET);
    exit;
}

// 时间范围过滤
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'today';
$startDate = '';
$endDate = '';

switch ($filter) {
    case 'today':
        $startDate = date('Y-m-d 00:00:00');
        $endDate = date('Y-m-d 23:59:59');
        break;
    case 'yesterday':
        $startDate = date('Y-m-d 00:00:00', strtotime('-1 day'));
        $endDate = date('Y-m-d 23:59:59', strtotime('-1 day'));
        break;
    case 'this_week':
        $startDate = date('Y-m-d 00:00:00', strtotime('monday this week'));
        $endDate = date('Y-m-d 23:59:59');
        break;
    case 'last_week':
        $startDate = date('Y-m-d 00:00:00', strtotime('monday last week'));
        $endDate = date('Y-m-d 23:59:59', strtotime('sunday last week'));
        break;
    case 'this_month':
        $startDate = date('Y-m-01 00:00:00');
        $endDate = date('Y-m-d 23:59:59');
        break;
    case 'last_month':
        $startDate = date('Y-m-01 00:00:00', strtotime('last month'));
        $endDate = date('Y-m-t 23:59:59', strtotime('last month'));
        break;
    case 'this_year':
        $startDate = date('Y-01-01 00:00:00');
        $endDate = date('Y-m-d 23:59:59');
        break;
    case 'all':
        $startDate = '';
        $endDate = '';
        break;
    default:
        if (preg_match('/^\d{4}-\d{2}$/', $filter)) {
            $startDate = $filter . '-01 00:00:00';
            $endDate = date('Y-m-t 23:59:59', strtotime($startDate));
        }
}

// 构建查询条件
$whereClause = "WHERE 1=1";
$params = [];

if ($startDate && $endDate) {
    $whereClause .= " AND created_at BETWEEN :start AND :end";
    $params[':start'] = $startDate;
    $params[':end'] = $endDate;
}

// 获取数据列表
$sql = "SELECT * FROM form_submissions $whereClause ORDER BY created_at DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 统计总数
$countSql = "SELECT COUNT(*) as total FROM form_submissions $whereClause";
$countStmt = $db->prepare($countSql);
$countStmt->execute($params);
$total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

// 获取可用月份列表
$monthSql = "SELECT DISTINCT DATE_FORMAT(created_at, '%Y-%m') as month FROM form_submissions ORDER BY month DESC";
$months = $db->query($monthSql)->fetchAll(PDO::FETCH_COLUMN);

// 统计分析数据（不受筛选条件影响，始终统计全局）
$stats = getStatistics($db);

// 预测本月表单数量
$prediction = getMonthPrediction($db);

// 导出Excel函数
function exportToExcel($db, $getParams) {
    $filter = isset($getParams['filter']) ? $getParams['filter'] : 'all';
    $startDate = '';
    $endDate = '';
    
    switch ($filter) {
        case 'today':
            $startDate = date('Y-m-d 00:00:00');
            $endDate = date('Y-m-d 23:59:59');
            break;
        case 'yesterday':
            $startDate = date('Y-m-d 00:00:00', strtotime('-1 day'));
            $endDate = date('Y-m-d 23:59:59', strtotime('-1 day'));
            break;
        case 'this_week':
            $startDate = date('Y-m-d 00:00:00', strtotime('monday this week'));
            $endDate = date('Y-m-d 23:59:59');
            break;
        case 'last_week':
            $startDate = date('Y-m-d 00:00:00', strtotime('monday last week'));
            $endDate = date('Y-m-d 23:59:59', strtotime('sunday last week'));
            break;
        case 'this_month':
            $startDate = date('Y-m-01 00:00:00');
            $endDate = date('Y-m-d 23:59:59');
            break;
        case 'last_month':
            $startDate = date('Y-m-01 00:00:00', strtotime('last month'));
            $endDate = date('Y-m-t 23:59:59', strtotime('last month'));
            break;
        case 'this_year':
            $startDate = date('Y-01-01 00:00:00');
            $endDate = date('Y-m-d 23:59:59');
            break;
        case 'all':
            $startDate = '';
            $endDate = '';
            break;
        default:
            if (preg_match('/^\d{4}-\d{2}$/', $filter)) {
                $startDate = $filter . '-01 00:00:00';
                $endDate = date('Y-m-t 23:59:59', strtotime($startDate));
            }
    }
    
    $whereClause = "WHERE 1=1";
    $params = [];
    
    if ($startDate && $endDate) {
        $whereClause .= " AND created_at BETWEEN :start AND :end";
        $params[':start'] = $startDate;
        $params[':end'] = $endDate;
    }
    
    $sql = "SELECT * FROM form_submissions $whereClause ORDER BY created_at DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 设置CSV头
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="form_data_' . date('Y-m-d_His') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // 输出BOM（用于Excel识别UTF-8）
    echo "\xEF\xBB\xBF";
    
    // 输出CSV数据
    $output = fopen('php://output', 'w');
    
    // 表头（移除来源域名）
    fputcsv($output, ['ID', '昵称', '咨询项目', '联系方式', '留言', '来源页面', 'IP地址', '提交时间']);
    
    // 数据行
    foreach ($records as $record) {
        fputcsv($output, [
            $record['id'],
            $record['nickname'],
            $record['project'],
            $record['contact'],
            $record['message'],
            $record['site_url'],
            $record['user_ip'],
            $record['created_at']
        ]);
    }
    
    fclose($output);
}

// 获取统计数据
function getStatistics($db) {
    $stats = [];

    // 按项目统计
    $projectSql = "SELECT project, COUNT(*) as count FROM form_submissions GROUP BY project ORDER BY count DESC";
    $stmt = $db->query($projectSql);
    $stats['by_project'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 按来源页面统计
    $pageSql = "SELECT site_url, COUNT(*) as count FROM form_submissions WHERE site_url != '' GROUP BY site_url ORDER BY count DESC LIMIT 20";
    $stmt = $db->query($pageSql);
    $stats['by_page'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 最近7天趋势（完整7天，没有数据补0）
    $byDate = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-{$i} days"));
        $byDate[$date] = ['date' => $date, 'count' => 0];
    }
    $dateSql = "SELECT DATE(created_at) as date, COUNT(*) as count FROM form_submissions WHERE created_at >= :start GROUP BY DATE(created_at)";
    $stmt = $db->prepare($dateSql);
    $stmt->execute([':start' => date('Y-m-d', strtotime('-6 days')) . ' 00:00:00']);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        if (isset($byDate[$row['date']])) {
            $byDate[$row['date']]['count'] = (int)$row['count'];
        }
    }
    $stats['by_date'] = array_values($byDate);

    // 按小时统计（本年，完整24小时，没有数据补0）
    $byHour = array_fill(0, 24, 0);
    $thisYearStart = date('Y-01-01 00:00:00');
    $hourSql = "SELECT HOUR(created_at) as hour, COUNT(*) as count FROM form_submissions WHERE created_at >= :start GROUP BY HOUR(created_at)";
    $stmt = $db->prepare($hourSql);
    $stmt->execute([':start' => $thisYearStart]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $byHour[(int)$row['hour']] = (int)$row['count'];
    }
    $stats['by_hour'] = [];
    for ($h = 0; $h < 24; $h++) {
        $stats['by_hour'][] = ['hour' => $h, 'count' => $byHour[$h]];
    }

    // 本月趋势（完整月份天数，没有数据补0）
    $daysInMonth = (int)date('t');
    $byMonthDay = [];
    for ($d = 1; $d <= $daysInMonth; $d++) {
        $date = date('Y-m-') . str_pad($d, 2, '0', STR_PAD_LEFT);
        $byMonthDay[$date] = ['date' => $date, 'count' => 0];
    }
    $thisMonthStart = date('Y-m-01 00:00:00');
    $monthSql = "SELECT DATE(created_at) as date, COUNT(*) as count FROM form_submissions WHERE created_at >= :start GROUP BY DATE(created_at)";
    $stmt = $db->prepare($monthSql);
    $stmt->execute([':start' => $thisMonthStart]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        if (isset($byMonthDay[$row['date']])) {
            $byMonthDay[$row['date']]['count'] = (int)$row['count'];
        }
    }
    $stats['this_month_trend'] = array_values($byMonthDay);

    // 本年趋势（完整12个月，没有数据补0）
    $byMonth = [];
    for ($m = 1; $m <= 12; $m++) {
        $month = date('Y-') . str_pad($m, 2, '0', STR_PAD_LEFT);
        $byMonth[$month] = ['month' => $month, 'count' => 0];
    }
    $thisYearStart = date('Y-01-01 00:00:00');
    $yearSql = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count FROM form_submissions WHERE created_at >= :start GROUP BY DATE_FORMAT(created_at, '%Y-%m')";
    $stmt = $db->prepare($yearSql);
    $stmt->execute([':start' => $thisYearStart]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        if (isset($byMonth[$row['month']])) {
            $byMonth[$row['month']]['count'] = (int)$row['count'];
        }
    }
    $stats['this_year_trend'] = array_values($byMonth);

    return $stats;
}

// 预测本月表单数量
function getMonthPrediction($db) {
    $today = date('Y-m-d');
    $thisMonthStart = date('Y-m-01');
    $lastMonthStart = date('Y-m-01', strtotime('last month'));
    $lastMonthEnd = date('Y-m-t', strtotime('last month'));
    
    // 本月已提交数量和天数
    $thisMonthSql = "SELECT COUNT(*) as count FROM form_submissions WHERE created_at >= :start";
    $stmt = $db->prepare($thisMonthSql);
    $stmt->execute([':start' => $thisMonthStart . ' 00:00:00']);
    $thisMonthCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    $thisMonthDays = max(1, (int)date('j')); // 今天几号就是几天
    
    // 上月总数和天数
    $lastMonthSql = "SELECT COUNT(*) as count FROM form_submissions WHERE created_at >= :start AND created_at <= :end";
    $stmt = $db->prepare($lastMonthSql);
    $stmt->execute([
        ':start' => $lastMonthStart . ' 00:00:00',
        ':end' => $lastMonthEnd . ' 23:59:59'
    ]);
    $lastMonthCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    $lastMonthDays = (int)date('t', strtotime('last month'));
    
    // 计算日均
    $lastMonthDailyAvg = $lastMonthDays > 0 ? $lastMonthCount / $lastMonthDays : 0;
    $thisMonthDailyAvg = $thisMonthCount / $thisMonthDays;
    
    // 预测：(上月日均 + 本月日均) / 2 * 本月总天数
    $avgDaily = ($lastMonthDailyAvg + $thisMonthDailyAvg) / 2;
    $thisMonthTotalDays = (int)date('t'); // 本月总天数
    $predicted = (int)round($avgDaily * $thisMonthTotalDays);
    
    return [
        'this_month_count' => $thisMonthCount,
        'this_month_days' => $thisMonthDays,
        'this_month_daily_avg' => round($thisMonthDailyAvg, 1),
        'last_month_count' => $lastMonthCount,
        'last_month_days' => $lastMonthDays,
        'last_month_daily_avg' => round($lastMonthDailyAvg, 1),
        'predicted' => $predicted,
        'avg_daily' => round($avgDaily, 1)
    ];
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>表单数据管理后台</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Microsoft YaHei', sans-serif; background: #0a0e27; color: #e0e6ed; overflow-x: hidden; }

        /* 背景动画 */
        body::before {
            content: '';
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background:
                radial-gradient(ellipse at 20% 50%, rgba(0, 255, 255, 0.08) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 80%, rgba(120, 0, 255, 0.08) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 20%, rgba(0, 150, 255, 0.06) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        /* 侧边栏 */
        .sidebar { position: fixed; left: 0; top: 0; bottom: 0; width: 224px; background: linear-gradient(180deg, #0d1117 0%, #161b22 100%); color: #fff; overflow-y: auto; z-index: 100; border-right: 1px solid rgba(0, 255, 255, 0.1); box-shadow: 4px 0 20px rgba(0, 0, 0, 0.5); }
        .sidebar::before { content: ''; position: absolute; top: 0; right: 0; width: 1px; height: 100%; background: linear-gradient(180deg, transparent, rgba(0, 255, 255, 0.3), transparent); }

        .sidebar-logo { padding: 16px 0; display: flex; align-items: center; justify-content: center; border-bottom: 1px solid rgba(0, 255, 255, 0.1); position: relative; }
        .sidebar-logo::after { content: ''; position: absolute; bottom: -1px; left: 20%; right: 20%; height: 1px; background: linear-gradient(90deg, transparent, rgba(0, 255, 255, 0.5), transparent); }
        .sidebar-logo img { width: 80px; height: 80px; object-fit: cover; display: block; border-radius: 12px; box-shadow: 0 0 20px rgba(0, 255, 255, 0.3), inset 0 0 10px rgba(0, 255, 255, 0.1); border: 1px solid rgba(0, 255, 255, 0.2); }

        .sidebar-nav { padding: 12px 0; }
        .sidebar-nav .nav-group { padding: 10px 16px 6px; font-size: 10px; color: rgba(0, 255, 255, 0.5); text-transform: uppercase; letter-spacing: 2px; font-weight: 600; }
        .sidebar-nav a { display: flex; align-items: center; gap: 10px; padding: 11px 16px 11px 24px; color: rgba(224, 230, 237, 0.7); text-decoration: none; font-size: 13px; transition: all 0.3s; border-left: 2px solid transparent; position: relative; }
        .sidebar-nav a:hover { color: #fff; background: rgba(0, 255, 255, 0.05); border-left-color: rgba(0, 255, 255, 0.5); }
        .sidebar-nav a:hover::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 2px; background: #00ffff; box-shadow: 0 0 8px #00ffff; }
        .sidebar-nav a.active { color: #fff; background: linear-gradient(90deg, rgba(0, 255, 255, 0.15), transparent); border-left-color: #00ffff; }
        .sidebar-nav a.active::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 2px; background: #00ffff; box-shadow: 0 0 10px #00ffff; }
        .sidebar-nav a .icon { width: 18px; text-align: center; font-size: 15px; }

        .sidebar-prediction { margin: 16px 12px; padding: 16px; background: linear-gradient(135deg, rgba(0, 255, 255, 0.1), rgba(120, 0, 255, 0.1)); border-radius: 10px; border: 1px solid rgba(0, 255, 255, 0.2); box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.05); position: relative; overflow: hidden; }
        .sidebar-prediction::before { content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: conic-gradient(from 0deg, transparent, rgba(0, 255, 255, 0.1), transparent); animation: rotate 4s linear infinite; }
        @keyframes rotate { to { transform: rotate(360deg); } }
        .sidebar-prediction h4 { font-size: 11px; color: rgba(0, 255, 255, 0.8); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 1px; position: relative; }
        .sidebar-prediction .num { font-size: 32px; font-weight: 700; background: linear-gradient(135deg, #00ffff, #7b61ff); -webkit-background-clip: text; -webkit-text-fill-color: transparent; text-shadow: 0 0 20px rgba(0, 255, 255, 0.3); position: relative; }
        .sidebar-prediction .detail { font-size: 11px; color: rgba(224, 230, 237, 0.5); line-height: 1.6; margin-top: 6px; position: relative; }

        /* 主内容区 */
        .main { margin-left: 224px; min-height: 100vh; position: relative; z-index: 1; }

        /* 顶栏 */
        .header { background: rgba(13, 17, 23, 0.8); backdrop-filter: blur(10px); padding: 0 24px; height: 56px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3); position: sticky; top: 0; z-index: 50; border-bottom: 1px solid rgba(0, 255, 255, 0.1); }
        .header h1 { font-size: 16px; color: #fff; font-weight: 600; text-shadow: 0 0 10px rgba(0, 255, 255, 0.3); }
        .header-right { display: flex; align-items: center; gap: 12px; }
        .header-right .filter-pills { display: flex; gap: 4px; }
        .header-right .filter-pills a { padding: 5px 12px; font-size: 12px; background: rgba(255, 255, 255, 0.05); color: rgba(224, 230, 237, 0.7); text-decoration: none; border-radius: 4px; transition: all 0.3s; border: 1px solid rgba(255, 255, 255, 0.1); }
        .header-right .filter-pills a:hover { background: rgba(0, 255, 255, 0.1); color: #00ffff; border-color: rgba(0, 255, 255, 0.3); box-shadow: 0 0 10px rgba(0, 255, 255, 0.2); }
        .header-right .filter-pills a.active { background: linear-gradient(135deg, rgba(0, 255, 255, 0.2), rgba(120, 0, 255, 0.2)); color: #fff; border-color: #00ffff; box-shadow: 0 0 15px rgba(0, 255, 255, 0.3); }
        .header-right .month-sel { padding: 5px 10px; font-size: 12px; border: 1px solid rgba(0, 255, 255, 0.2); border-radius: 4px; background: rgba(0, 255, 255, 0.05); color: #e0e6ed; }
        .export-btn { padding: 5px 14px; background: linear-gradient(135deg, #00c853, #00e676); color: #fff; text-decoration: none; border-radius: 4px; font-size: 12px; transition: all 0.3s; box-shadow: 0 2px 8px rgba(0, 200, 83, 0.3); }
        .export-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0, 200, 83, 0.4); }

        /* 内容区 */
        .content { padding: 20px 24px; }

        /* 统计卡片 */
        .stat-cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 20px; }
        .stat-card { background: rgba(13, 17, 23, 0.6); backdrop-filter: blur(10px); border-radius: 10px; padding: 20px; border: 1px solid rgba(0, 255, 255, 0.1); box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.05); transition: all 0.3s; }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4), 0 0 20px rgba(0, 255, 255, 0.1); }
        .stat-card .label { font-size: 12px; color: rgba(224, 230, 237, 0.5); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 1px; }
        .stat-card .value { font-size: 28px; font-weight: 700; }
        .stat-card .sub { font-size: 11px; color: rgba(224, 230, 237, 0.4); margin-top: 4px; }
        .stat-card.blue .value { color: #00ffff; text-shadow: 0 0 10px rgba(0, 255, 255, 0.5); }
        .stat-card.green .value { color: #00e676; text-shadow: 0 0 10px rgba(0, 230, 118, 0.5); }
        .stat-card.purple .value { color: #7b61ff; text-shadow: 0 0 10px rgba(123, 97, 255, 0.5); }
        .stat-card.orange .value { color: #ff9100; text-shadow: 0 0 10px rgba(255, 145, 0, 0.5); }

        /* 图表区 */
        .charts-section { margin-bottom: 20px; }
        .section-title { font-size: 15px; font-weight: 600; color: #fff; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; text-shadow: 0 0 10px rgba(0, 255, 255, 0.3); }
        .section-title::before { content: ''; width: 3px; height: 16px; background: linear-gradient(180deg, #00ffff, #7b61ff); border-radius: 2px; box-shadow: 0 0 8px #00ffff; }
        .charts-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .chart-card { background: rgba(13, 17, 23, 0.6); backdrop-filter: blur(10px); border-radius: 10px; padding: 18px; border: 1px solid rgba(0, 255, 255, 0.1); box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.05); transition: all 0.3s; }
        .chart-card:hover { box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4), 0 0 15px rgba(0, 255, 255, 0.1); }
        .chart-card.full-width { grid-column: 1 / -1; }
        .chart-card h3 { font-size: 13px; color: rgba(0, 255, 255, 0.8); margin-bottom: 12px; font-weight: 500; text-transform: uppercase; letter-spacing: 1px; }

        /* 数据表格 */
        .table-card { background: rgba(13, 17, 23, 0.6); backdrop-filter: blur(10px); border-radius: 10px; border: 1px solid rgba(0, 255, 255, 0.1); box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.05); overflow: hidden; }
        .table-card .card-header { padding: 14px 20px; border-bottom: 1px solid rgba(0, 255, 255, 0.1); display: flex; justify-content: space-between; align-items: center; }
        .table-card .card-header h3 { font-size: 15px; font-weight: 600; color: #fff; display: flex; align-items: center; gap: 8px; text-shadow: 0 0 10px rgba(0, 255, 255, 0.3); }
        .table-card .card-header h3::before { content: ''; width: 3px; height: 16px; background: linear-gradient(180deg, #00ffff, #7b61ff); border-radius: 2px; box-shadow: 0 0 8px #00ffff; }
        .table-card .card-header .count { font-size: 12px; color: rgba(0, 255, 255, 0.8); background: rgba(0, 255, 255, 0.1); padding: 2px 10px; border-radius: 10px; border: 1px solid rgba(0, 255, 255, 0.2); }
        .table-card .card-body { max-height: 520px; overflow-y: auto; }

        table { width: 100%; border-collapse: collapse; }
        th { background: rgba(0, 255, 255, 0.05); color: rgba(0, 255, 255, 0.8); padding: 10px 14px; text-align: left; font-weight: 600; font-size: 12px; border-bottom: 1px solid rgba(0, 255, 255, 0.1); position: sticky; top: 0; z-index: 1; text-transform: uppercase; letter-spacing: 1px; }
        td { padding: 10px 14px; border-bottom: 1px solid rgba(255, 255, 255, 0.05); font-size: 13px; color: #e0e6ed; }
        tr:hover { background: rgba(0, 255, 255, 0.03); }
        .no-data { text-align: center; padding: 50px; color: rgba(224, 230, 237, 0.3); font-size: 14px; }

        .tag { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 11px; }
        .tag-blue { background: rgba(0, 255, 255, 0.1); color: #00ffff; border: 1px solid rgba(0, 255, 255, 0.2); }

        /* 滚动条 */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: rgba(0, 0, 0, 0.2); }
        ::-webkit-scrollbar-thumb { background: rgba(0, 255, 255, 0.3); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(0, 255, 255, 0.5); }

        /* 移动端 */
        @media (max-width: 1024px) {
            .sidebar { width: 60px; }
            .sidebar-logo img { width: 40px; height: 40px; }
            .sidebar-nav a { padding: 10px 0; justify-content: center; font-size: 0; }
            .sidebar-nav a .icon { font-size: 18px; }
            .sidebar-nav .nav-group, .sidebar-prediction { display: none; }
            .main { margin-left: 60px; }
            .stat-cards { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main { margin-left: 0; }
            .stat-cards { grid-template-columns: 1fr 1fr; }
            .charts-grid { grid-template-columns: 1fr; }
            .header-right .filter-pills { display: none; }
            table { font-size: 12px; }
            th, td { padding: 8px 6px; }
        }
    </style>
</head>
<body>
    <!-- 左侧导航栏 -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <img src="laoshiren.jpg" alt="Logo">
        </div>
        <nav class="sidebar-nav">
            <div class="nav-group">概览</div>
            <a href="?" class="active"><span class="icon">🏠</span> 数据看板</a>
            <a href="?filter=today"><span class="icon">📅</span> 今日数据</a>
            <div class="nav-group">筛选</div>
            <a href="?filter=this_week"><span class="icon">📆</span> 本周数据</a>
            <a href="?filter=this_month"><span class="icon">🗓️</span> 本月数据</a>
            <a href="?filter=last_month"><span class="icon">📋</span> 上月数据</a>
            <a href="?filter=all"><span class="icon">📈</span> 数据分析</a>
            <div class="nav-group">操作</div>
            <a href="?export=excel&filter=<?php echo $filter; ?>"><span class="icon">📥</span> 导出Excel</a>
        </nav>

        <div class="sidebar-prediction">
            <h4>📈 本月预测</h4>
            <div class="num"><?php echo $prediction['predicted']; ?></div>
            <div class="detail">
                上月日均 <?php echo $prediction['last_month_daily_avg']; ?> 条<br>
                本月日均 <?php echo $prediction['this_month_daily_avg']; ?> 条
            </div>
        </div>
    </aside>

    <!-- 右侧主内容 -->
    <div class="main">
        <!-- 顶栏 -->
        <div class="header">
            <h1>数据看板</h1>
            <div class="header-right">
                <div class="filter-pills">
                    <a href="?filter=today" <?php echo $filter === 'today' ? 'class="active"' : ''; ?>>今天</a>
                    <a href="?filter=yesterday" <?php echo $filter === 'yesterday' ? 'class="active"' : ''; ?>>昨天</a>
                    <a href="?filter=this_week" <?php echo $filter === 'this_week' ? 'class="active"' : ''; ?>>本周</a>
                    <a href="?filter=last_week" <?php echo $filter === 'last_week' ? 'class="active"' : ''; ?>>上周</a>
                    <a href="?filter=this_month" <?php echo $filter === 'this_month' ? 'class="active"' : ''; ?>>本月</a>
                    <a href="?filter=last_month" <?php echo $filter === 'last_month' ? 'class="active"' : ''; ?>>上月</a>
                    <a href="?filter=this_year" <?php echo $filter === 'this_year' ? 'class="active"' : ''; ?>>本年</a>
                </div>
                <select class="month-sel" onchange="window.location.href='?filter=' + this.value">
                    <option value="">按月份...</option>
                    <?php foreach ($months as $month): ?>
                        <option value="<?php echo $month; ?>" <?php echo $filter === $month ? 'selected' : ''; ?>><?php echo $month; ?></option>
                    <?php endforeach; ?>
                </select>
                <a href="?export=excel&filter=<?php echo $filter; ?>" class="export-btn">📥 导出</a>
            </div>
        </div>

        <!-- 内容区 -->
        <div class="content">
            <?php if ($filter !== 'all'): ?>
            <!-- 数据看板：统计卡片 + 详细数据 -->
            <div class="stat-cards">
                <div class="stat-card blue">
                    <div class="label">当前筛选</div>
                    <div class="value"><?php echo $total; ?></div>
                    <div class="sub">条记录</div>
                </div>
                <div class="stat-card green">
                    <div class="label">本月已提交</div>
                    <div class="value"><?php echo $prediction['this_month_count']; ?></div>
                    <div class="sub">日均 <?php echo $prediction['this_month_daily_avg']; ?> 条</div>
                </div>
                <div class="stat-card purple">
                    <div class="label">上月总计</div>
                    <div class="value"><?php echo $prediction['last_month_count']; ?></div>
                    <div class="sub">日均 <?php echo $prediction['last_month_daily_avg']; ?> 条</div>
                </div>
                <div class="stat-card orange">
                    <div class="label">本月预测</div>
                    <div class="value"><?php echo $prediction['predicted']; ?></div>
                    <div class="sub">综合日均 <?php echo $prediction['avg_daily']; ?> 条</div>
                </div>
            </div>

            <!-- 数据表格 -->
            <div class="table-card">
                <div class="card-header">
                    <h3>详细数据</h3>
                    <span class="count"><?php echo $total; ?> 条</span>
                </div>
                <div class="card-body">
                    <?php if (empty($records)): ?>
                        <div class="no-data">暂无数据</div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>昵称</th>
                                    <th>咨询项目</th>
                                    <th>联系方式</th>
                                    <th>留言内容</th>
                                    <th>来源页面</th>
                                    <th>提交时间</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($records as $record): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($record['id']); ?></td>
                                        <td><?php echo htmlspecialchars($record['nickname']); ?></td>
                                        <td><span class="tag tag-blue"><?php echo htmlspecialchars($record['project']); ?></span></td>
                                        <td><?php echo htmlspecialchars($record['contact']); ?></td>
                                        <td><?php echo htmlspecialchars($record['message'] ?: '-'); ?></td>
                                        <td>
                                            <?php if ($record['site_url']): ?>
                                                <a href="<?php echo htmlspecialchars($record['site_url']); ?>" target="_blank" style="color: #1890ff; text-decoration: none;">查看</a>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars(substr($record['created_at'], 5, 11)); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <?php else: ?>
            <!-- 数据分析页：只显示图表 -->
            <div class="charts-section">
                <div class="section-title">数据分析</div>
                <div class="charts-grid">
                    <!-- 整行：最近7天趋势 -->
                    <div class="chart-card full-width">
                        <h3>最近7天趋势</h3>
                        <canvas id="dateChart"></canvas>
                    </div>

                    <!-- 整行：本月趋势 -->
                    <div class="chart-card full-width">
                        <h3>本月趋势</h3>
                        <canvas id="monthTrendChart"></canvas>
                    </div>

                    <!-- 整行：提交时段分布 -->
                    <div class="chart-card full-width">
                        <h3>提交时段分布（本年）</h3>
                        <canvas id="hourChart"></canvas>
                    </div>

                    <!-- 半行：咨询项目分布 -->
                    <div class="chart-card">
                        <h3>咨询项目分布</h3>
                        <canvas id="projectChart"></canvas>
                    </div>

                    <!-- 半行：来源页面分布 -->
                    <div class="chart-card">
                        <h3>来源页面分布</h3>
                        <canvas id="pageChart"></canvas>
                    </div>

                    <!-- 整行：本年趋势 -->
                    <div class="chart-card full-width">
                        <h3>本年趋势</h3>
                        <canvas id="yearTrendChart"></canvas>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    // 仅在数据分析页（filter=all）初始化图表
    if (<?php echo $filter === 'all' ? 'true' : 'false'; ?>) {
    const chartColors = ['#1890ff', '#52c41a', '#fa8c16', '#f5222d', '#13c2c2', '#722ed1', '#fa541c', '#2f54eb', '#eb2f96'];

    // 最近7天趋势
    const dateData = <?php echo json_encode($stats['by_date']); ?>;
    new Chart(document.getElementById('dateChart'), {
        type: 'line',
        data: {
            labels: dateData.map(d => d.date.substring(5)),
            datasets: [{
                label: '提交数量',
                data: dateData.map(d => d.count),
                borderColor: '#1890ff',
                backgroundColor: 'rgba(24, 144, 255, 0.08)',
                tension: 0.4, fill: true, pointRadius: 4, pointBackgroundColor: '#1890ff'
            }]
        },
        options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { display: false } } }
    });

    // 本月趋势
    const monthTrendData = <?php echo json_encode($stats['this_month_trend']); ?>;
    new Chart(document.getElementById('monthTrendChart'), {
        type: 'line',
        data: {
            labels: monthTrendData.map(d => d.date.substring(5)),
            datasets: [{
                label: '提交数量',
                data: monthTrendData.map(d => d.count),
                borderColor: '#722ed1',
                backgroundColor: 'rgba(114, 46, 209, 0.08)',
                tension: 0.4, fill: true, pointRadius: 4, pointBackgroundColor: '#722ed1'
            }]
        },
        options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { display: false } } }
    });

    // 项目分布
    const projectData = <?php echo json_encode($stats['by_project']); ?>;
    new Chart(document.getElementById('projectChart'), {
        type: 'doughnut',
        data: {
            labels: projectData.map(d => d.project),
            datasets: [{ data: projectData.map(d => d.count), backgroundColor: chartColors, borderWidth: 0 }]
        },
        options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { position: 'right', labels: { boxWidth: 12, padding: 10, font: { size: 11 } } } } }
    });

    // 时段分布
    const hourData = <?php echo json_encode($stats['by_hour']); ?>;
    new Chart(document.getElementById('hourChart'), {
        type: 'bar',
        data: {
            labels: hourData.map(d => d.hour + ':00'),
            datasets: [{ label: '提交数量', data: hourData.map(d => d.count), backgroundColor: '#52c41a', borderRadius: 4 }]
        },
        options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { display: false } } }
    });

    // 来源页面
    const pageData = <?php echo json_encode($stats['by_page']); ?>;
    new Chart(document.getElementById('pageChart'), {
        type: 'pie',
        data: {
            labels: pageData.map(d => { const m = (d.site_url||'').match(/\/([^\/]+\.html)/); return m ? m[1] : '未知'; }),
            datasets: [{ data: pageData.map(d => d.count), backgroundColor: chartColors, borderWidth: 0 }]
        },
        options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { position: 'right', labels: { boxWidth: 12, padding: 10, font: { size: 11 } } } } }
    });

    // 本年趋势
    const yearTrendData = <?php echo json_encode($stats['this_year_trend']); ?>;
    new Chart(document.getElementById('yearTrendChart'), {
        type: 'bar',
        data: {
            labels: yearTrendData.map(d => d.month),
            datasets: [{ label: '提交数量', data: yearTrendData.map(d => d.count), backgroundColor: '#fa8c16', borderRadius: 4 }]
        },
        options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { display: false } } }
    });
    } // end if filter === 'all'
    </script>
</body>
</html>
