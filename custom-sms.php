<?php

$num = $_GET['num'] ?? '';
$msg = $_GET['msg'] ?? '';

if (empty($num) || empty($msg)) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => false,
        'error'  => 'Missing parameters. Required: num, msg',
        'Api owner' => 'XrootCodeX',
        'Developer' => 'MehediSanix'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

$msgLength = mb_strlen($msg, 'UTF-8');
$halfLength = (int) ceil($msgLength / 2);

$namePart = mb_substr($msg, 0, $halfLength, 'UTF-8');
$businessNamePart = mb_substr($msg, $halfLength, $msgLength, 'UTF-8');

$payload = [
    'name'          => $namePart,
    'phone_number'  => $num,
    'business_name' => $businessNamePart,
];

$curl = curl_init();

curl_setopt_array($curl, [
  CURLOPT_URL => 'https://api-merchant.carrybee.com/api/v2/merchant/register',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
  CURLOPT_SSL_VERIFYPEER => false,
  CURLOPT_SSL_VERIFYHOST => false,
  CURLOPT_HTTPHEADER => [
    'User-Agent: Mozilla/5.0 (Linux; Android 12; V2027 Build/SP1A.210812.003) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.8059.43 Mobile Safari/537.36',
    'Accept: application/json',
    'Accept-Encoding: gzip, deflate, br, zstd',
    'Content-Type: application/json',
    'sec-ch-ua-platform: "Android"',
    'sec-ch-ua: "Android WebView";v="155", "Chromium";v="155", "Not(A:Brand";v="24"',
    'sec-ch-ua-mobile: ?1',
    'origin: https://merchant.carrybee.com',
    'x-requested-with: mark.via.gp',
    'sec-fetch-site: same-site',
    'sec-fetch-mode: cors',
    'sec-fetch-dest: empty',
    'referer: https://merchant.carrybee.com/',
    'accept-language: en-US,en;q=0.9',
    'priority: u=1, i',
  ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

if (PHP_VERSION_ID < 80500) {
  curl_close($curl);
}

header('Content-Type: application/json; charset=utf-8');

if ($err) {
  echo json_encode([
    'status' => false,
    'error'  => 'cURL Error #: ' . $err,
    'Api owner' => 'XrootCodeX',
    'Developer' => 'MehediSanix'
  ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} else {
  $decoded = json_decode($response, true);

  if (json_last_error() === JSON_ERROR_NONE) {
    // যদি এপিআই থেকে সরাসরি JSON রেসপন্স আসে, সেটির সাথে আমাদের তথ্যগুলো মার্জ করে দেব
    if (is_array($decoded)) {
        $decoded['Api owner'] = 'XrootCodeX';
        $decoded['Developer'] = 'MehediSanix';
    }
    echo json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  } else {
    echo json_encode([
      'status' => true,
      'raw_response' => $response,
      'Api owner' => 'XrootCodeX',
      'Developer' => 'MehediSanix'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
  }
}
