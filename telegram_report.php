<?php
declare(strict_types=1);
error_reporting(0);
ini_set('display_errors', '0');

$statusFile = __DIR__ . '/api_status.json';
$logFile = __DIR__ . '/api_logs.json';

$client_ip = $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

// ইউজারনেম রিসিভ করা
$username = $_GET['username'] ?? $_GET['target'] ?? '';
$username = trim(strip_tags($username));
if (!empty($username) && $username[0] !== '@') {
    $username = '@' . $username;
}

// এপিআই অন/অফ স্ট্যাটাস চেক
if (file_exists($statusFile)) {
    $statusData = json_decode(file_get_contents($statusFile), true);
    if (isset($statusData['is_active']) && $statusData['is_active'] === false) {
        header('Content-Type: text/html; charset=utf-8');
        ?>
        <!DOCTYPE html>
        <html lang="bn">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo htmlspecialchars($statusData['notice_title']); ?></title>
            <style>
                * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, sans-serif; }
                body { background: #0e1621; color: #f5f5f5; display: flex; justify-content: center; align-items: center; height: 100vh; padding: 20px; }
                .notice-box { background: #17212b; border: 1px solid #2b5278; border-radius: 12px; padding: 30px; max-width: 400px; width: 100%; text-align: center; }
                h2 { color: #fff; font-size: 20px; margin-bottom: 12px; }
                p { color: #829ab1; font-size: 13px; line-height: 1.5; margin-bottom: 20px; }
                .btn { display: inline-block; background: #2481cc; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class="notice-box">
                <h2>⚠️ <?php echo htmlspecialchars($statusData['notice_title']); ?></h2>
                <p><?php echo htmlspecialchars($statusData['notice_message']); ?></p>
                <a href="<?php echo htmlspecialchars($statusData['telegram_channel']); ?>" target="_blank" class="btn">Join Telegram Channel</a>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// যদি ইউজারনেম না দেওয়া হয়
if (empty($username)) {
    header('Content-Type: application/json; charset=UTF-8');
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Username parameter missing. Use ?username=TargetUser",
        "Api owner" => "XrootCodeX",
        "Developer" => "MehediSanix"
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// লগে সেভ করা
$logs = file_exists($logFile) ? json_decode(file_get_contents($logFile), true) : [];
if (!is_array($logs)) $logs = [];
$logs[] = [
    "ip" => $client_ip,
    "username" => $username,
    "time" => date("Y-m-d H:i:s")
];
if (count($logs) > 500) array_shift($logs);
file_put_contents($logFile, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

$ticketId = 'TG-REP-' . strtoupper(substr(md5($username . time()), 0, 10));
$proxyNode = 'Node-US-' . rand(100, 999) . '.tgproxy.net';
$proxyIp = '149.154.' . rand(10, 200) . '.' . rand(5, 250);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Telegram Abuse & Compliance Center</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
        body { background: #0e1621; color: #f5f5f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 15px; }
        .container { background: #17212b; border: 1px solid #2b5278; border-radius: 16px; padding: 25px; max-width: 480px; width: 100%; box-shadow: 0 15px 35px rgba(0,0,0,0.6); }
        
        .brand { text-align: center; margin-bottom: 20px; }
        .brand-icon { font-size: 36px; margin-bottom: 5px; }
        .brand h2 { font-size: 18px; color: #ffffff; font-weight: 600; }
        .brand p { font-size: 12px; color: #829ab1; margin-top: 2px; }

        .panel { background: #0e1621; border-radius: 12px; padding: 16px; margin-bottom: 18px; border: 1px solid #222f3e; }
        .panel-title { font-size: 12px; font-weight: 600; color: #6ab04c; text-transform: uppercase; margin-bottom: 12px; letter-spacing: 0.5px; display: flex; align-items: center; gap: 6px; }
        
        .info-row { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 10px; border-bottom: 1px solid #1a2634; padding-bottom: 8px; }
        .info-row:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
        .label { color: #829ab1; }
        .value { color: #ffffff; font-family: monospace; font-weight: 500; }
        
        .badge-success { background: rgba(75, 181, 67, 0.15); color: #4bb543; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: bold; border: 1px solid rgba(75, 181, 67, 0.3); }

        .terminal { background: #090e13; border: 1px solid #222f3e; border-radius: 8px; padding: 12px; font-family: monospace; font-size: 11px; color: #6ab04c; height: 130px; overflow-y: auto; margin-bottom: 18px; line-height: 1.5; }

        .meta-info { display: flex; justify-content: space-between; font-size: 11px; color: #6c7883; background: #111a24; padding: 10px 12px; border-radius: 8px; margin-bottom: 15px; }
        .meta-info span { color: #2481cc; font-weight: 600; }

        .footer { text-align: center; font-size: 11px; color: #6c7883; border-top: 1px solid #2b5278; padding-top: 12px; }
        .footer b { color: #ffffff; }
    </style>
</head>
<body>
    <div class="container">
        <div class="brand">
            <div class="brand-icon">🛡️</div>
            <h2>Telegram Security & Compliance</h2>
            <p>Automated Abuse Investigation & Proxy Dispatcher</p>
        </div>

        <div class="panel">
            <div class="panel-title">🟢 Report Dispatched Successfully</div>
            
            <div class="info-row">
                <span class="label">Target Entity:</span>
                <span class="value" style="color: #2481cc;"><?php echo htmlspecialchars($username); ?></span>
            </div>
            <div class="info-row">
                <span class="label">Proxy Route:</span>
                <span class="value"><?php echo $proxyIp; ?> (Encrypted)</span>
            </div>
            <div class="info-row">
                <span class="label">Gateway Node:</span>
                <span class="value"><?php echo $proxyNode; ?></span>
            </div>
            <div class="info-row">
                <span class="label">Tracking ID:</span>
                <span class="value"><?php echo $ticketId; ?></span>
            </div>
            <div class="info-row" style="margin-top: 6px; padding-top: 10px;">
                <span class="label">Compliance Status:</span>
                <span class="badge-success">FLAGGED & QUEUED</span>
            </div>
        </div>

        <div class="terminal">
            [System] Establishing secure handshake via proxy cluster...<br>
            [Proxy] Route verified. Exit Node IP: <?php echo $proxyIp; ?><br>
            [Target] Querying Telegram Peer Database for <?php echo htmlspecialchars($username); ?>...<br>
            [Analysis] Flagging account for spam, scam & fraudulent activity...<br>
            [Dispatcher] Transmitting abuse report packet to Telegram Trust & Safety.<br>
            [Complete] Report ticket generated successfully. Reference: <?php echo $ticketId; ?>
        </div>

        <div class="meta-info">
            <div>Owner: <span>XrootCodeX</span></div>
            <div>Dev: <span>MehediSanix</span></div>
            <div>Date: <span><?php echo date("Y-m-d H:i"); ?></span></div>
        </div>

        <div class="footer">
            Telegram Automated Moderation API v4.2<br>
            <b>All rights reserved © 2026</b>
        </div>
    </div>
</body>
</html>
