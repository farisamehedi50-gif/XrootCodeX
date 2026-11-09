<?php
// যদি AJAX বা সরাসরি POST/GET রিকোয়েস্ট হ্যান্ডেল করতে হয়
if (isset($_GET['num']) || isset($_POST['num'])) {
    $num  = $_GET['num'] ?? $_POST['num'] ?? '';
    $name = $_GET['name'] ?? $_POST['name'] ?? '';
    $msg  = $_GET['msg'] ?? $_POST['msg'] ?? '';

    header('Content-Type: application/json; charset=utf-8');

    if (empty($num) || empty($name) || empty($msg)) {
        echo json_encode([
            'status' => false,
            'error'  => 'Missing parameters. Required: num, name, msg',
            'Api owner' => 'XrootCodeX',
            'Developer' => 'MehediSanix'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    $payload = [
        'name'          => $name,
        'phone_number'  => $num,
        'business_name' => $msg,   
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

    if ($err) {
        $result = [
            'status' => false,
            'error'  => 'cURL Error #: ' . $err
        ];
    } else {
        $decoded = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $result = $decoded;
        } else {
            $result = ['status' => true, 'raw_response' => $response];
        }
    }

    // ব্র্যান্ডিং তথ্য যুক্ত করা
    $result['Api owner'] = 'XrootCodeX';
    $result['Developer'] = 'MehediSanix';

    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custom SMS Portal</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 50%, #ff416c 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 30px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
            color: #fff;
        }
        h2 {
            text-align: center;
            margin-bottom: 25px;
            font-size: 24px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.9);
        }
        input {
            width: 100%;
            padding: 14px 16px;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            color: #fff;
            font-size: 15px;
            outline: none;
            transition: all 0.3s ease;
        }
        input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        input:focus {
            border-color: #fff;
            background: rgba(255, 255, 255, 0.25);
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.2);
        }
        button {
            width: 100%;
            padding: 14px;
            background: #ffffff;
            border: none;
            border-radius: 12px;
            color: #2575fc;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        button:hover {
            background: #f0f0f0;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        #result {
            margin-top: 20px;
            background: rgba(0, 0, 0, 0.3);
            padding: 15px;
            border-radius: 10px;
            font-family: monospace;
            font-size: 13px;
            white-space: pre-wrap;
            word-break: break-all;
            display: none;
            max-height: 200px;
            overflow-y: auto;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.7);
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Custom SMS Portal</h2>
    <form id="smsForm">
        <div class="form-group">
            <label>Target Name</label>
            <input type="text" id="name" placeholder="e.g. John (Min 3 chars)" required>
        </div>
        <div class="form-group">
            <label>Phone Number</label>
            <input type="text" id="num" placeholder="e.g. 017XXXXXXXX" required>
        </div>
        <div class="form-group">
            <label>Message / Business Name</label>
            <input type="text" id="msg" placeholder="e.g. Welcome (Min 3 chars)" required>
        </div>
        <button type="submit">Send SMS</button>
    </form>
    <div id="result"></div>
    <div class="footer">
        Powered by XrootCodeX &bull; Developed by MehediSanix
    </div>
</div>

<script>
    document.getElementById('smsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const name = document.getElementById('name').value;
        const num = document.getElementById('num').value;
        const msg = document.getElementById('msg').value;
        const resultDiv = document.getElementById('result');

        resultDiv.style.display = 'block';
        resultDiv.innerText = 'Sending... Please wait...';

        fetch(`?name=${encodeURIComponent(name)}&num=${encodeURIComponent(num)}&msg=${encodeURIComponent(msg)}`)
            .then(response => response.json())
            .then(data => {
                resultDiv.innerText = JSON.stringify(data, null, 2);
            })
            .catch(error => {
                resultDiv.innerText = 'Error: ' + error;
            });
    });
</script>

</body>
</html>
