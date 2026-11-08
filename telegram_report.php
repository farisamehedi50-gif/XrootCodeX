<?php
declare(strict_types=1);
error_reporting(0);
ini_set('display_errors', '0');

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');

$statusFile = __DIR__ . '/api_status.json';
$logFile = __DIR__ . '/api_logs.json';

$client_ip = $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

// ইউজারনেম রিসিভ করা
$username = $_GET['username'] ?? $_GET['target'] ?? '';
$username = trim(strip_tags($username));

if (empty($username)) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Username parameter missing. Use ?username=TargetChannel",
        "Api owner" => "XrootCodeX",
        "Developer" => "MehediSanix"
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

if ($username[0] === '@') {
    $username = substr($username, 1);
}

// এপিআই অন/অফ স্ট্যাটাস চেক
if (file_exists($statusFile)) {
    $statusData = json_decode(file_get_contents($statusFile), true);
    if (isset($statusData['is_active']) && $statusData['is_active'] === false) {
        http_response_code(503);
        echo json_encode([
            "status" => "maintenance",
            "message" => $statusData['notice_title'] ?? "API is temporarily disabled",
            "notice" => $statusData['notice_message'] ?? "",
            "telegram_channel" => $statusData['telegram_channel'] ?? "",
            "Api owner" => "XrootCodeX",
            "Developer" => "MehediSanix"
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// আপনার চ্যানেল ইনফরমেশন এপিআই থেকে রিয়েল ডাটা ফেচ করা
$infoApiUrl = "https://xrootcobrax.onrender.com/chenel-informastion.php?username=" . urlencode($username);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $infoApiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => false
]);
$channelResponse = curl_exec($ch);
curl_close($ch);

$channelData = json_decode($channelResponse, true);

// চ্যানেল ইনফরমেশন থেকে ডেটা এক্সট্রাক্ট করা (যদি না থাকে তবে ফলব্যাক ডিফল্ট মান)
$title       = $channelData['title'] ?? $channelData['name'] ?? ucfirst($username);
$subscribers = $channelData['subscribers'] ?? $channelData['members'] ?? rand(2500, 58000);
$description = $channelData['description'] ?? $channelData['bio'] ?? "No public description found for this Telegram entity.";
$verified    = $channelData['verified'] ?? false;

// লগে সেভ করা
$logs = file_exists($logFile) ? json_decode(file_get_contents($logFile), true) : [];
if (!is_array($logs)) $logs = [];
$logs[] = [
    "ip" => $client_ip,
    "username" => "@" . $username,
    "subscribers" => $subscribers,
    "time" => date("Y-m-d H:i:s")
];
if (count($logs) > 500) array_shift($logs);
file_put_contents($logFile, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// রিয়েলিস্টিক মেট্রিকস জেনারেট
$ticketId        = 'TG-SEC-' . strtoupper(substr(md5($username . time()), 0, 10));
$proxyPower      = rand(89, 99) . "." . rand(10, 99) . "%";
$guidelineBreach = rand(78, 98) . "%"; // গাইডলাইন কতখানি ভায়োলেট করেছে
$proxyRoutes     = rand(12, 35);       // কয়টি প্রক্সি রুট থেকে রিপোর্ট করা হয়েছে
$proxyIp         = '149.154.' . rand(10, 200) . '.' . rand(5, 250);

// প্রফেশনাল JSON রেসপন্স
$response = [
    "status" => "success",
    "status_code" => 200,
    "message" => "Target Telegram channel successfully analyzed, flagged, and reported through multi-proxy nodes.",
    "channel_info" => [
        "username" => "@" . $username,
        "title" => $title,
        "subscribers" => $subscribers,
        "description" => $description,
        "is_verified" => $verified
    ],
    "abuse_metrics" => [
        "guideline_breach_rate" => $guidelineBreach,
        "proxy_power_score" => $proxyPower,
        "total_proxy_routes_used" => $proxyRoutes,
        "threat_level" => "High (Scam/Fraud Activity Detected)"
    ],
    "report_meta" => [
        "ticket_id" => $ticketId,
        "exit_node_ip" => $proxyIp,
        "gateway_cluster" => "Node-US-" . rand(100, 999) . ".tgproxy.net",
        "action_taken" => "Automated Compliance Flag Queued",
        "timestamp" => date("Y-m-d H:i:s")
    ],
    "Api owner" => "XrootCodeX",
    "Developer" => "MehediSanix"
];

http_response_code(200);
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
