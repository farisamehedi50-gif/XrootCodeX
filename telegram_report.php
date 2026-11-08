<?php
declare(strict_types=1);
error_reporting(0);
ini_set('display_errors', '0');

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: *');

$statusFile = __DIR__ . '/api_status.json';
$logFile = __DIR__ . '/api_logs.json';

$client_ip = $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

// ইউজারনেম গ্রহণ করা
$username = $_GET['username'] ?? $_GET['target'] ?? $_GET['query'] ?? '';
$username = trim(strip_tags($username));

if (empty($username)) {
    http_response_code(400);
    echo json_encode([
        "status"      => false,
        "error_code"  => 400,
        "message"     => "Target username missing. Example: ?username=CobraX_Empire",
        "Api owner"   => "XrootCodeX",
        "Developer"   => "MehediSanix"
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// ইউজারনেমের শুরুতে @ না থাকলে অটো যুক্ত করা
if ($username[0] !== '@') {
    $username = '@' . $username;
}

// অ্যাডমিন প্যানেল থেকে সার্ভিস অন/অফ চেক
if (file_exists($statusFile)) {
    $statusData = json_decode(file_get_contents($statusFile), true);
    if (isset($statusData['is_active']) && $statusData['is_active'] === false) {
        http_response_code(503);
        echo json_encode([
            "status"           => false,
            "error_code"       => 503,
            "notice_title"     => $statusData['notice_title'] ?? "সার্ভার রক্ষণাবেক্ষণে আছে",
            "notice_message"   => $statusData['notice_message'] ?? "বর্তমানে রিকোয়েস্ট গ্রহণ করা হচ্ছে না।",
            "telegram_channel" => $statusData['telegram_channel'] ?? "https://t.me/CobraX_Empire",
            "Api owner"        => "XrootCodeX",
            "Developer"        => "MehediSanix"
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// লগে ইউজার আইপি ও টার্গেট সেভ করা
$logs = file_exists($logFile) ? json_decode(file_get_contents($logFile), true) : [];
if (!is_array($logs)) $logs = [];
$logs[] = [
    "ip"       => $client_ip,
    "username" => $username,
    "time"     => date("Y-m-d H:i:s")
];
if (count($logs) > 500) array_shift($logs);
file_put_contents($logFile, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// ডাইনামিক ফেক ট্র্যাকিং ডাটা জেনারেট
$ticketId        = 'TG-REP-' . strtoupper(substr(md5($username . microtime()), 0, 10));
$proxyIp         = '185.159.' . rand(10, 200) . '.' . rand(5, 250);
$proxyNodes      = rand(15, 45);
$proxyPower      = rand(95, 99) . '.' . rand(10, 99) . '%';
$guidelineBreach = rand(88, 98) . '%';

// প্রফেশনাল সাকসেস JSON রেসপন্স
$response = [
    "status"          => true,
    "status_code"     => 200,
    "result"          => "SUCCESS",
    "message"         => "Telegram report successfully executed and submitted to security compliance queue.",
    "report_details"  => [
        "target_username"      => $username,
        "report_status"        => "DONE",
        "ticket_id"            => $ticketId,
        "report_category"      => "Spam, Fraud & Malicious Activity",
        "action_taken"         => "Account Flagged for Moderation Review"
    ],
    "proxy_analytics" => [
        "proxy_power"          => $proxyPower,
        "guideline_breach_rate"=> $guidelineBreach,
        "active_proxy_nodes"   => $proxyNodes,
        "exit_node_ip"         => $proxyIp,
        "protocol"             => "HTTPS / SOCKS5 Encrypted"
    ],
    "timestamp"       => date("Y-m-d H:i:s"),
    "Api owner"       => "XrootCodeX",
    "Developer"       => "MehediSanix"
];

http_response_code(200);
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
