<?php
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
    "owner"       => "XrootCodeX"
];

http_response_code(200);
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
