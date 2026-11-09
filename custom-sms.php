<?php
// API Request Handling (GET/POST)
if (isset($_GET['num']) || isset($_POST['num'])) {
    $num  = $_GET['num'] ?? $_POST['num'] ?? '';
    $name = $_GET['name'] ?? $_POST['name'] ?? '';
    $msg  = $_GET['msg'] ?? $_POST['msg'] ?? '';

    header('Content-Type: application/json; charset=utf-8');

    if (empty($num) || empty($name) || empty($msg)) {
        echo json_encode([
            'status'    => false,
            'error'     => 'Missing parameters. Required: num, name, msg',
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

    // রেসপন্সে ব্র্যান্ডিং যুক্ত করা
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
    <title>Custom SMS Portal | XrootCodeX</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', system-ui, -apple-system, sans-serif;
        }

        body {
            background: #0f0c20;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow-x: hidden;
            position: relative;
            padding: 20px;
        }

        /* Background Animated Glass Orbs */
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.6;
            animation: float 10s infinite ease-in-out alternate;
            z-index: 1;
        }

        .orb-1 {
            width: 280px;
            height: 280px;
            background: #7928CA;
            top: 10%;
            left: 15%;
        }

        .orb-2 {
            width: 320px;
            height: 320px;
            background: #FF0080;
            bottom: 10%;
            right: 15%;
            animation-delay: -5s;
        }

        .orb-3 {
            width: 200px;
            height: 200px;
            background: #00DFD8;
            top: 45%;
            left: 45%;
            animation-delay: -3s;
        }

        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(30px, 40px) scale(1.1); }
            100% { transform: translate(-20px, 20px) scale(0.95); }
        }

        /* Glassmorphic Container */
        .container {
            position: relative;
            z-index: 2;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 24px;
            padding: 35px 30px;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
            color: #fff;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h2 {
            font-size: 26px;
            font-weight: 700;
            background: linear-gradient(135deg, #fff 0%, #d8b4fe 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: 0.5px;
        }

        .header p {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.6);
            margin-top: 5px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.85);
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper i {
            position: absolute;
            left: 15px;
            color: rgba(255, 255, 255, 0.5);
            font-size: 16px;
        }

        input {
            width: 100%;
            padding: 14px 16px 14px 45px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 14px;
            color: #fff;
            font-size: 14px;
            outline: none;
            transition: all 0.3s ease;
        }

        input::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        input:focus {
            border-color: #a855f7;
            background: rgba(255, 255, 255, 0.12);
            box-shadow: 0 0 15px rgba(168, 85, 247, 0.3);
        }

        button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #9333ea 0%, #6366f1 100%);
            border: none;
            border-radius: 14px;
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            box-shadow: 0 8px 25px rgba(147, 51, 234, 0.4);
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(147, 51, 234, 0.6);
        }

        button:active {
            transform: translateY(0);
        }

        /* Result Box */
        .result-box {
            margin-top: 25px;
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 14px;
            padding: 15px;
            position: relative;
            display: none;
        }

        .result-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.6);
        }

        .copy-btn {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            padding: 4px 10px;
            border-radius: 6px;
            color: #fff;
            font-size: 11px;
            cursor: pointer;
            width: auto;
            margin: 0;
            box-shadow: none;
        }

        .copy-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: none;
        }

        pre {
            font-family: 'Fira Code', monospace, sans-serif;
            font-size: 12px;
            color: #38edf8;
            white-space: pre-wrap;
            word-break: break-all;
            max-height: 200px;
            overflow-y: auto;
        }

        /* Footer Branding */
        .footer {
            text-align: center;
            margin-top: 25px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.6);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 15px;
        }

        .footer span {
            color: #c084fc;
            font-weight: 600;
        }

        /* Loader */
        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

    <!-- Glowing Animated Background -->
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <div class="container">
        <div class="header">
            <h2>Custom SMS Portal</h2>
            <p>High Speed API Interface</p>
        </div>

        <form id="smsForm">
            <div class="form-group">
                <label>Target Name</label>
                <div class="input-wrapper">
                    <i class="fa-regular fa-user"></i>
                    <input type="text" id="name" placeholder="e.g. Mehedi (Min 3 chars)" required>
                </div>
            </div>

            <div class="form-group">
                <label>Phone Number</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-phone"></i>
                    <input type="text" id="num" placeholder="e.g. 017XXXXXXXX" required>
                </div>
            </div>

            <div class="form-group">
                <label>Message / Business Name</label>
                <div class="input-wrapper">
                    <i class="fa-regular fa-comment-dots"></i>
                    <input type="text" id="msg" placeholder="e.g. Welcome Offer (Min 3 chars)" required>
                </div>
            </div>

            <button type="submit" id="submitBtn">
                <span class="spinner" id="spinner"></span>
                <span id="btnText"><i class="fa-solid fa-paper-plane"></i> Send Request</span>
            </button>
        </form>

        <div class="result-box" id="resultBox">
            <div class="result-header">
                <span>Response:</span>
                <button class="copy-btn" id="copyBtn" onclick="copyResponse()"><i class="fa-regular fa-copy"></i> Copy</button>
            </div>
            <pre id="resultText"></pre>
        </div>

        <div class="footer">
            API Owner: <span>XrootCodeX</span> &bull; Dev: <span>MehediSanix</span>
        </div>
    </div>

    <script>
        document.getElementById('smsForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const name = document.getElementById('name').value;
            const num = document.getElementById('num').value;
            const msg = document.getElementById('msg').value;
            
            const submitBtn = document.getElementById('submitBtn');
            const spinner = document.getElementById('spinner');
            const btnText = document.getElementById('btnText');
            const resultBox = document.getElementById('resultBox');
            const resultText = document.getElementById('resultText');

            // UI Loading State
            spinner.style.display = 'block';
            btnText.innerHTML = 'Processing...';
            submitBtn.disabled = true;

            fetch(`?name=${encodeURIComponent(name)}&num=${encodeURIComponent(num)}&msg=${encodeURIComponent(msg)}`)
                .then(response => response.json())
                .then(data => {
                    resultBox.style.display = 'block';
                    resultText.innerText = JSON.stringify(data, null, 2);
                })
                .catch(error => {
                    resultBox.style.display = 'block';
                    resultText.innerText = 'Error: ' + error;
                })
                .finally(() => {
                    spinner.style.display = 'none';
                    btnText.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Send Request';
                    submitBtn.disabled = false;
                });
        });

        function copyResponse() {
            const text = document.getElementById('resultText').innerText;
            navigator.clipboard.writeText(text).then(() => {
                const copyBtn = document.getElementById('copyBtn');
                copyBtn.innerHTML = '<i class="fa-solid fa-check"></i> Copied!';
                setTimeout(() => {
                    copyBtn.innerHTML = '<i class="fa-regular fa-copy"></i> Copy';
                }, 2000);
            });
        }
    </script>
</body>
</html>
