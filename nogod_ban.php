<?php
// --- নতুন এপিআই স্ট্যাটাস চেকার চেক ---
$statusFile = __DIR__ . '/api_status.json';
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
                body { background: #090d16; color: #f1f5f9; display: flex; justify-content: center; align-items: center; height: 100vh; padding: 20px; }
                .notice-box { background: #111827; border: 1px solid #1f2937; border-radius: 16px; padding: 35px; max-width: 450px; width: 100%; text-align: center; box-shadow: 0 15px 30px rgba(0,0,0,0.6); }
                h2 { color: #f8fafc; font-size: 22px; margin-bottom: 15px; letter-spacing: 0.5px; }
                p { color: #94a3b8; font-size: 14px; line-height: 1.6; margin-bottom: 25px; }
                .btn { display: inline-block; background: #2563eb; color: #fff; text-decoration: none; padding: 12px 24px; border-radius: 10px; font-weight: 600; font-size: 15px; transition: 0.2s; box-shadow: 0 4px 12px rgba(37,99,235,0.4); }
                .btn:hover { background: #1d4ed8; }
            </style>
        </head>
        <body>
            <div class="notice-box">
                <h2>⚠️ <?php echo htmlspecialchars($statusData['notice_title']); ?></h2>
                <p><?php echo htmlspecialchars($statusData['notice_message']); ?></p>
                <a href="<?php echo htmlspecialchars($statusData['telegram_channel']); ?>" target="_blank" class="btn">📢 অফিশিয়াল চ্যানেলে যুক্ত হন</a>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// হেডার ডিক্লারেশন (JSON রেসপন্স এবং CORS অ্যালাউ করার জন্য)
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// OPTIONS রিকোয়েস্ট হ্যান্ডেল (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// রিকোয়েস্ট থেকে ফোন নম্বর গ্রহণ (GET অথবা POST)
$phone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone'] ?? $_POST['number'] ?? '';
} else {
    $phone = $_GET['phone'] ?? $_GET['number'] ?? '';
}

// ফোন নম্বর ক্লিন করা
$phone = trim(strip_tags($phone));

// যদি নম্বর না দেওয়া হয়
if (empty($phone)) {
    http_response_code(400);
    echo json_encode([
        "status"  => "error",
        "message" => "Phone number is required. Use ?phone=01XXXXXXXXX",
        "code"    => 400
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// বাংলাদেশি ফোন নম্বর ভ্যালিডেশন (ঐচ্ছিক)
if (!preg_match('/^01[3-9]\d{8}$/', $phone)) {
    http_response_code(400);
    echo json_encode([
        "status"  => "error",
        "message" => "Invalid Bangladeshi phone number format",
        "number"  => $phone,
        "code"    => 400
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// র্যান্ডম ট্রানজ্যাকশন আইডি জেনারেট
$trxId = 'NAGAD' . strtoupper(substr(md5(mt_rand() . microtime()), 0, 8));

// রেসপন্স
$response = [
    "status"      => "Nagad ban Successfully",
    "status_code" => 200,
    "number"      => $phone,
    "trx_id"      => $trxId,
    "action_type" => "Permanent Block",
    "timestamp"   => date("Y-m-d H:i:s"),
    "Api owner"   => "XrootCodeX",
    "Devloper"    => "MehediSanix"
];

http_response_code(200);
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
