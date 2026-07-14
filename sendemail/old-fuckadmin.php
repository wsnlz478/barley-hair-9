<?php
/**
 * 表单数据管理后台 - 增强版
 * 功能：数据查看、统计分析、Excel导出
 */

// 简单的访问控制 - 通过环境变量或配置文件设置密码
session_start();

// 从环境变量或默认配置读取密码（建议在生产环境中使用环境变量）
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
    
    // 按来源页面统计（替换原来的来源域名）
    $pageSql = "SELECT site_url, COUNT(*) as count FROM form_submissions WHERE site_url != '' GROUP BY site_url ORDER BY count DESC LIMIT 20";
    $stmt = $db->query($pageSql);
    $stats['by_page'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 按日期统计（最近7天）
    $dateSql = "SELECT DATE(created_at) as date, COUNT(*) as count FROM form_submissions GROUP BY DATE(created_at) ORDER BY date DESC LIMIT 7";
    $stmt = $db->query($dateSql);
    $stats['by_date'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 按小时统计
    $hourSql = "SELECT HOUR(created_at) as hour, COUNT(*) as count FROM form_submissions GROUP BY HOUR(created_at) ORDER BY hour";
    $stmt = $db->query($hourSql);
    $stats['by_hour'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 本月趋势（按天统计）
    $thisMonthStart = date('Y-m-01 00:00:00');
    $monthSql = "SELECT DATE(created_at) as date, COUNT(*) as count FROM form_submissions WHERE created_at >= :start GROUP BY DATE(created_at) ORDER BY date";
    $stmt = $db->prepare($monthSql);
    $stmt->execute([':start' => $thisMonthStart]);
    $stats['this_month_trend'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 本年趋势（按月统计）
    $thisYearStart = date('Y-01-01 00:00:00');
    $yearSql = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count FROM form_submissions WHERE created_at >= :start GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY month";
    $stmt = $db->prepare($yearSql);
    $stmt->execute([':start' => $thisYearStart]);
    $stats['this_year_trend'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
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
        body { font-family: 'Microsoft YaHei', sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 1400px; margin: 0 auto; }
        h1 { color: #333; margin-bottom: 20px; }
        h2 { color: #555; margin: 30px 0 15px; font-size: 20px; }
        
        .stats { background: #fff; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .stats p { font-size: 18px; color: #666; }
        .stats span { color: #2b85e4; font-weight: bold; font-size: 24px; }
        
        .prediction-box { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 25px; border-radius: 8px; margin-bottom: 20px; color: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .prediction-box h3 { font-size: 18px; margin-bottom: 15px; opacity: 0.9; }
        .prediction-box .prediction-number { font-size: 48px; font-weight: bold; margin: 10px 0; }
        .prediction-box .prediction-detail { font-size: 14px; opacity: 0.85; line-height: 1.8; }
        
        .filter-bar { background: #fff; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .filter-bar a { display: inline-block; padding: 8px 16px; margin: 4px; background: #f0f0f0; color: #333; text-decoration: none; border-radius: 4px; transition: all 0.3s; }
        .filter-bar a:hover { background: #2b85e4; color: #fff; }
        .filter-bar a.active { background: #2b85e4; color: #fff; }
        
        .export-btn { display: inline-block; padding: 10px 20px; background: #28a745; color: #fff; text-decoration: none; border-radius: 4px; margin-left: 10px; transition: all 0.3s; }
        .export-btn:hover { background: #218838; }
        
        .month-select { margin-top: 10px; }
        .month-select select { padding: 8px 16px; font-size: 14px; border: 1px solid #ddd; border-radius: 4px; }
        
        .charts-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .chart-box { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .chart-box h3 { color: #555; margin-bottom: 15px; font-size: 16px; }
        
        table { width: 100%; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        th { background: #2b85e4; color: #fff; padding: 12px; text-align: left; font-weight: normal; }
        td { padding: 12px; border-bottom: 1px solid #f0f0f0; }
        tr:hover { background: #f9f9f9; }
        .no-data { text-align: center; padding: 40px; color: #999; }
        
        @media (max-width: 768px) {
            .charts-grid { grid-template-columns: 1fr; }
            table { font-size: 12px; }
            th, td { padding: 8px 4px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 表单数据管理后台</h1>
        
        <!-- 统计概览 -->
        <div class="stats">
            <p>当前筛选结果：<span><?php echo $total; ?></span> 条记录</p>
        </div>
        
        <!-- 本月预测 -->
        <div class="prediction-box">
            <h3>📈 本月表单预测</h3>
            <div class="prediction-number"><?php echo $prediction['predicted']; ?> 条</div>
            <div class="prediction-detail">
                上月：<?php echo $prediction['last_month_count']; ?> 条 / <?php echo $prediction['last_month_days']; ?> 天 = 日均 <?php echo $prediction['last_month_daily_avg']; ?> 条<br>
                本月已过：<?php echo $prediction['this_month_count']; ?> 条 / <?php echo $prediction['this_month_days']; ?> 天 = 日均 <?php echo $prediction['this_month_daily_avg']; ?> 条<br>
                综合日均：<?php echo $prediction['avg_daily']; ?> 条 × <?php echo date('t'); ?> 天 = <strong><?php echo $prediction['predicted']; ?> 条</strong>
            </div>
        </div>
        
        <!-- 筛选栏 -->
        <div class="filter-bar">
            <p style="margin-bottom: 10px; color: #666;">快速筛选：</p>
            <a href="?filter=today" <?php echo $filter === 'today' ? 'class="active"' : ''; ?>>今天</a>
            <a href="?filter=yesterday" <?php echo $filter === 'yesterday' ? 'class="active"' : ''; ?>>昨天</a>
            <a href="?filter=this_week" <?php echo $filter === 'this_week' ? 'class="active"' : ''; ?>>本周</a>
            <a href="?filter=last_week" <?php echo $filter === 'last_week' ? 'class="active"' : ''; ?>>上周</a>
            <a href="?filter=this_month" <?php echo $filter === 'this_month' ? 'class="active"' : ''; ?>>本月</a>
            <a href="?filter=last_month" <?php echo $filter === 'last_month' ? 'class="active"' : ''; ?>>上月</a>
            <a href="?filter=this_year" <?php echo $filter === 'this_year' ? 'class="active"' : ''; ?>>本年</a>
            <a href="?filter=all" <?php echo $filter === 'all' ? 'class="active"' : ''; ?>>全部</a>
            
            <div class="month-select">
                <p style="margin: 10px 0 5px; color: #666;">按月份筛选：</p>
                <select onchange="window.location.href='?filter=' + this.value">
                    <option value="">选择月份...</option>
                    <?php foreach ($months as $month): ?>
                        <option value="<?php echo $month; ?>" <?php echo $filter === $month ? 'selected' : ''; ?>>
                            <?php echo $month; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <p style="margin-top: 15px;">
                <a href="?export=excel&filter=<?php echo $filter; ?>" class="export-btn">📥 导出Excel</a>
            </p>
        </div>
        
        <!-- 数据分析图表 -->
        <?php if ($total > 0): ?>
        <h2>📈 数据分析</h2>
        <div class="charts-grid">
            <!-- 按项目统计 -->
            <div class="chart-box">
                <h3>咨询项目分布</h3>
                <canvas id="projectChart"></canvas>
            </div>
            
            <!-- 最近7天趋势 -->
            <div class="chart-box">
                <h3>最近7天趋势</h3>
                <canvas id="dateChart"></canvas>
            </div>
            
            <!-- 按小时统计 -->
            <div class="chart-box">
                <h3>提交时段分布</h3>
                <canvas id="hourChart"></canvas>
            </div>
            
            <!-- 来源页面分布 -->
            <div class="chart-box">
                <h3>来源页面分布</h3>
                <canvas id="pageChart"></canvas>
            </div>
            
            <!-- 本月趋势 -->
            <div class="chart-box">
                <h3>本月趋势</h3>
                <canvas id="monthTrendChart"></canvas>
            </div>
            
            <!-- 本年趋势 -->
            <div class="chart-box">
                <h3>本年趋势</h3>
                <canvas id="yearTrendChart"></canvas>
            </div>
        </div>
        
        <script>
        // 项目分布图
        const projectData = <?php echo json_encode($stats['by_project']); ?>;
        new Chart(document.getElementById('projectChart'), {
            type: 'doughnut',
            data: {
                labels: projectData.map(d => d.project),
                datasets: [{
                    data: projectData.map(d => d.count),
                    backgroundColor: ['#2b85e4', '#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6610f2', '#fd7e14', '#20c997', '#e83e8c']
                }]
            },
            options: { responsive: true, maintainAspectRatio: true }
        });
        
        // 日期趋势图
        const dateData = <?php echo json_encode($stats['by_date']); ?>;
        new Chart(document.getElementById('dateChart'), {
            type: 'line',
            data: {
                labels: dateData.map(d => d.date),
                datasets: [{
                    label: '提交数量',
                    data: dateData.map(d => d.count),
                    borderColor: '#2b85e4',
                    backgroundColor: 'rgba(43, 133, 228, 0.1)',
                    tension: 0.4
                }]
            },
            options: { responsive: true, maintainAspectRatio: true }
        });
        
        // 小时分布图
        const hourData = <?php echo json_encode($stats['by_hour']); ?>;
        new Chart(document.getElementById('hourChart'), {
            type: 'bar',
            data: {
                labels: hourData.map(d => d.hour + ':00'),
                datasets: [{
                    label: '提交数量',
                    data: hourData.map(d => d.count),
                    backgroundColor: '#28a745'
                }]
            },
            options: { responsive: true, maintainAspectRatio: true }
        });
        
        // 来源页面分布图
        const pageData = <?php echo json_encode($stats['by_page']); ?>;
        new Chart(document.getElementById('pageChart'), {
            type: 'pie',
            data: {
                labels: pageData.map(d => {
                    const url = d.site_url || '未知';
                    // 提取页面名称
                    const match = url.match(/\/([^\/]+\.html)/);
                    return match ? match[1] : url;
                }),
                datasets: [{
                    data: pageData.map(d => d.count),
                    backgroundColor: ['#ffc107', '#17a2b8', '#6610f2', '#fd7e14', '#20c997', '#e83e8c', '#6c757d', '#2b85e4', '#28a745', '#dc3545']
                }]
            },
            options: { responsive: true, maintainAspectRatio: true }
        });
        
        // 本月趋势图
        const monthTrendData = <?php echo json_encode($stats['this_month_trend']); ?>;
        new Chart(document.getElementById('monthTrendChart'), {
            type: 'line',
            data: {
                labels: monthTrendData.map(d => d.date.substring(5)), // 只显示 MM-DD
                datasets: [{
                    label: '提交数量',
                    data: monthTrendData.map(d => d.count),
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: { responsive: true, maintainAspectRatio: true }
        });
        
        // 本年趋势图
        const yearTrendData = <?php echo json_encode($stats['this_year_trend']); ?>;
        new Chart(document.getElementById('yearTrendChart'), {
            type: 'bar',
            data: {
                labels: yearTrendData.map(d => d.month),
                datasets: [{
                    label: '提交数量',
                    data: yearTrendData.map(d => d.count),
                    backgroundColor: '#764ba2'
                }]
            },
            options: { responsive: true, maintainAspectRatio: true }
        });
        </script>
        <?php endif; ?>
        
        <!-- 数据列表 -->
        <h2>📋 详细数据</h2>
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
                        <th>留言</th>
                        <th>来源页面</th>
                        <th>IP地址</th>
                        <th>提交时间</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $record): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($record['id']); ?></td>
                            <td><?php echo htmlspecialchars($record['nickname']); ?></td>
                            <td><?php echo htmlspecialchars($record['project']); ?></td>
                            <td><?php echo htmlspecialchars($record['contact']); ?></td>
                            <td><?php echo htmlspecialchars($record['message'] ?: '-'); ?></td>
                            <td>
                                <?php if ($record['site_url']): ?>
                                    <a href="<?php echo htmlspecialchars($record['site_url']); ?>" target="_blank" style="color: #2b85e4;">查看</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($record['user_ip'] ?: '-'); ?></td>
                            <td><?php echo htmlspecialchars($record['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
