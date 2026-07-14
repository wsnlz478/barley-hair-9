<?php
/**
 * 植发咨询表单 - 全端兼容版
 * 升级：支持多类型联系方式（手机/微信/WhatsApp/邮箱）
 * 新增：繁体中文适配
 * 修复：跨域导致的网络错误
 */

// ===================== 全局配置（置顶，可直接修改） =====================
$CONFIG = [
    'receive_email' => ['hairtransplant@qq.com','richbro@qq.com'], // 接收表单的邮箱列表
    'email_title'   => '植发客户表单，提交时间：' . date('Y年m月d日'), // 邮件标题
    'allow_cors'    => true, // 是否允许跨域（true=允许/false=禁止）
    'nickname_max_length' => 30, // 昵称最大长度
    // 咨询项目白名单（简体中文，前端多语言最终映射到该值）
    'project_whitelist' => [
        '发际线种植','头发加密','秃顶植发','眉毛种植','胡须种植',
        '鬓角种植','体毛种植','脱发治疗','头发养护'
    ]
];

// ===================== 安全头配置 =====================
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Cache-Control: no-cache, no-store");

// ===================== 跨域配置 =====================
if($CONFIG['allow_cors']){
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
}
// 处理OPTIONS预检请求
if($_SERVER['REQUEST_METHOD'] === 'OPTIONS'){
    http_response_code(204);
    exit;
}
header("Content-Type: application/json; charset=utf-8");

// ===================== 工具函数 =====================
/**
 * 安全过滤输入内容
 * @param string $str 原始输入
 * @return string 过滤后的内容
 */
function safe_input($str){
    $str = trim($str);
    $str = strip_tags($str);
    $str = htmlspecialchars($str, ENT_QUOTES, "UTF-8");
    return $str;
}

// ===================== 接收并验证表单数据 =====================
// 接收表单数据（contact替换原phone，支持多类型联系方式）
$nickname     = safe_input($_POST['nickname'] ?? '');
$project      = safe_input($_POST['project'] ?? '');
$contact      = safe_input($_POST['contact'] ?? ''); // 手机/微信/WhatsApp/邮箱
$message      = safe_input($_POST['message'] ?? '');
$site_domain  = safe_input($_POST['site_domain'] ?? '未知');
$site_url     = safe_input($_POST['site_url'] ?? '未知');
$user_ip      = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

// 验证昵称
if(empty($nickname) || mb_strlen($nickname) > $CONFIG['nickname_max_length']){
    exit(json_encode(['code'=>0,'msg'=>'请填写正确昵称']));
}

// 验证咨询项目
if(!in_array($project, $CONFIG['project_whitelist'])){
    exit(json_encode(['code'=>0,'msg'=>'请选择咨询项目']));
}

// 验证联系方式（仅非空验证，支持多类型）
if(empty($contact)){
    exit(json_encode(['code'=>0,'msg'=>'请填写手机/微信/WhatsApp/邮箱']));
}

// ===================== 构造邮件内容 =====================
$email_body = "客户咨询信息\n------------------------\n";
$email_body .= "昵称：{$nickname}\n";
$email_body .= "咨询项目：{$project}\n";
$email_body .= "联系方式：{$contact}\n"; // 替换原电话字段
$email_body .= "留言：{$message}\n";
$email_body .= "来源网站：{$site_domain}\n";
$email_body .= "提交页面：{$site_url}\n";
$email_body .= "客户IP：{$user_ip}\n";
$email_body .= "提交时间：".date("Y-m-d H:i:s")."\n------------------------\n";
$email_body .= "【老冀免费渠道】大米饭植发客户挖掘机1.0为您服务。\n";

// 邮件头配置
$email_header = "From: 表单通知 <no-reply@{$_SERVER['HTTP_HOST']}>\r\n";
$email_header .= "Content-Type: text/plain; charset=utf-8\r\n";

// ===================== 发送邮件 =====================
foreach($CONFIG['receive_email'] as $to) {
    mail($to, $CONFIG['email_title'], $email_body, $email_header);
}

// ===================== 返回响应 =====================
echo json_encode(['code'=>1,'msg'=>'提交成功，我们将尽快联系您！']);
?>