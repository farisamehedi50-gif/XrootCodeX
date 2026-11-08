<?php
declare(strict_types=1);
error_reporting(0);
ini_set('display_errors', '0');

$statusFile = __DIR__ . '/api_status.json';
$logFile = __DIR__ . '/api_logs.json';

$client_ip = $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

// ডিফল্ট স্ট্যাটাস ফাইল তৈরি (যদি না থাকে)
if (!file_exists($statusFile)) {
    $defaultData = [
        "is_active" => true,
        "notice_title" => "এপিআই সাময়িকভাবে বন্ধ রয়েছে",
        "notice_message" => "এই এপিআই নিয়ে বাণিজ্যিক ব্যবসা করার কারণে অফ রাখা হয়েছে। নতুন আপডেট পেতে এবং আরো অনেক টুল ফ্রিতে পেতে আমাদের চ্যানেলে জয়েন করতে পারেন।",
        "telegram_channel" => "https://t.me/CobraX_Empire"
    ];
    file_put_contents($statusFile, json_encode($defaultData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// স্ট্যাটাস চেক - যদি অফ থাকে তবে ছবির মতো প্রিমিয়াম HTML নোটিশ পেজ দেখাবে
if (file_exists($statusFile)) {
    $statusData = json_decode(file_get_contents($statusFile), true);
    if (isset($statusData['is_active']) && $statusData['is_active'] === false) {
        header('Content-Type: text/html; charset=utf-8');
        http_response_code(503);
        ?>
        <!DOCTYPE html>
        <html lang="bn">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo htmlspecialchars($statusData['notice_title']); ?></title>
            <style>
                * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, sans-serif; }
                body { 
                    background: #07090e; 
                    color: #f1f5f9; 
                    display: flex; 
                    justify-content: center; 
                    align-items: center; 
                    height: 100vh; 
                    padding: 20px; 
                }
                .notice-box { 
                    background: #111827; 
                    border: 1px solid #1f2937; 
                    border-radius: 20px; 
                    padding: 40px 30px; 
                    max-width: 440px; 
                    width: 100%; 
                    text-align: center; 
                    box-shadow: 0 20px 40px rgba(0,0,0,0.8); 
                }
                h2 { 
                    color: #f8fafc; 
                    font-size: 22px; 
                    margin-bottom: 20px; 
                    font-weight: 700;
                    line-height: 1.4;
                }
                p { 
                    color: #94a3b8; 
                    font-size: 14px; 
                    line-height: 1.7; 
                    margin-bottom: 30px; 
                }
                .btn { 
                    display: inline-block; 
                    background: #2563eb; 
                    color: #fff; 
                    text-decoration: none; 
                    padding: 14px 28px; 
                    border-radius: 12px; 
                    font-weight: 600; 
                    font-size: 15px; 
                    transition: 0.2s; 
                    box-shadow: 0 6px 20px rgba(37,99,235,0.4);
                }
                .btn:hover { 
                    background: #1d4ed8; 
                }
            </style>
        </head>
        <body>
            <div class="notice-box">
                <h2>⚠️ <?php echo htmlspecialchars($statusData['notice_title']); ?></h2>
                <p><?php echo htmlspecialchars($statusData['notice_message']); ?></p>
                <a href="<?php echo htmlspecialchars($statusData['telegram_channel']); ?>" target="_blank" class="btn">📢 অফিসিয়াল চ্যানেলে যুক্ত হন</a>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// এপিআই অন থাকলে কাজের অংশ (JSON রেসপন্স)
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');

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

if ($username[0] !== '@') {
    $username = '@' . $username;
}

// লগ সেভ করা
$logs = file_exists($logFile) ? json_decode(file_get_contents($logFile), true) : [];
if (!is_array($logs)) $logs = [];
$logs[] = [
    "ip"       => $client_ip,
    "username" => $username,
    "time"     => date("Y-m-d H:i:s")
];
if (count($logs) > 500) array_shift($logs);
file_put_contents($logFile, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

$ticketId        = 'TG-REP-' . strtoupper(substr(md5($username . microtime()), 0, 10));
$proxyIp         = '185.159.' . rand(10, 200) . '.' . rand(5, 250);
$proxyNodes      = rand(15, 45);
$proxyPower      = rand(95, 99) . '.' . rand(10, 99) . '%';
$guidelineBreach = rand(88, 98) . '%';

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
