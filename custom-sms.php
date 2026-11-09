<?php
// API Request Handling (GET/POST)
if (isset($_GET['num']) || isset($_POST['num'])) {
    $num  = $_GET['num'] ?? $_POST['num'] ?? '';
    $name = $_GET['name'] ?? $_POST['name'] ?? '';
    $msg  = $_GET['msg'] ?? $_POST['msg'] ?? '';

    header('Content-Type: application/json; charset=utf-8');

    if (empty($num) || empty($name) || empty($msg)) {
        echo json_encode([
            'status' => false,
            'error'  => 'Missing parameters. Required: num, name, msg'
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', system-ui, -apple-system, sans-serif;
        }

        body {
            background: #030014;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            position: relative;
            padding: 20px;
        }

        /* Real Glass Floating Orb Animations */
        .glass-orb {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.02));
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: inset 0 0 20px rgba(255, 255, 255, 0.15), 0 15px 35px rgba(0, 0, 0, 0.5);
            animation: floatGlass 15s infinite ease-in-out alternate;
            z-index: 1;
        }

        .orb-1 {
            width: 250px;
            height: 250px;
            top: -5%;
            left: -5%;
            border: 1px solid rgba(255, 255, 255, 0.15);
            animation-duration: 12s;
        }

        .orb-2 {
            width: 320px;
            height: 320px;
            bottom: -10%;
            right: -5%;
            border: 1px solid rgba(255, 255, 255, 0.1);
            animation-duration: 18s;
            animation-delay: -5s;
        }

        .orb-3 {
            width: 180px;
            height: 180px;
            top: 40%;
            right: 15%;
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation-duration: 10s;
            animation-delay: -2s;
        }

        @keyframes floatGlass {
            0% { transform: translateY(0px) translateX(0px) rotate(0deg); }
            50% { transform: translateY(-40px) translateX(30px) rotate(180deg); }
            100% { transform: translateY(40px) translateX(-30px) rotate(360deg); }
        }

        /* Glassmorphic Container */
        .container {
            position: relative;
            z-index: 10;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 30px;
            padding: 40px 30px;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.7);
            color: #fff;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h2 {
            font-size: 28px;
            font-weight: 700;
            background: linear-gradient(135deg, #ffffff 0%, #a5b4fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: 0.5px;
        }

        .header p {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.5);
            margin-top: 6px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.8);
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper i {
            position: absolute;
            left: 16px;
            color: rgba(255, 255, 255, 0.4);
            font-size: 16px;
            transition: color 0.3s;
        }

        input {
            width: 100%;
            padding: 15px 16px 15px 48px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            color: #fff;
            font-size: 14px;
            outline: none;
            transition: all 0.3s ease;
        }

        input::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }

        input:focus {
            border-color: #818cf8;
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 20px rgba(129, 140, 248, 0.3);
        }

        input:focus + i {
            color: #818cf8;
        }

        button {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border: none;
            border-radius: 16px;
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.4);
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(99, 102, 241, 0.6);
            background: linear-gradient(135deg, #818cf8 0%, #6366f1 100%);
        }

        button:active {
            transform: translateY(0);
        }

        /* Result Display Box */
        .status-box {
            margin-top: 25px;
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 18px;
            display: none;
            animation: fadeIn 0.4s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .status-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.5);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .copy-btn {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            padding: 5px 12px;
            border-radius: 6px;
            color: #fff;
            font-size: 11px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .copy-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .status-content {
            font-family: 'Fira Code', monospace, sans-serif;
            font-size: 13px;
            color: #34d399;
            white-space: pre-wrap;
            word-break: break-all;
            max-height: 180px;
            overflow-y: auto;
        }

        .status-content.error {
            color: #f87171;
        }

        /* Spinner Loader */
        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

    <!-- Floating Glass Orbs Background -->
    <div class="glass-orb orb-1"></div>
    <div class="glass-orb orb-2"></div>
    <div class="glass-orb orb-3"></div>

    <div class="container">
        <div class="header">
            <h2>Custom SMS Portal</h2>
            <p>Advanced Glass UI Gateway</p>
        </div>

        <form id="smsForm">
            <div class="form-group">
                <label>Target Name</label>
                <div class="input-wrapper">
                    <i class="fa-regular fa-user"></i>
                    <input type="text" id="name" placeholder="e.g. John (Min 3 chars)" required>
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
                    <input type="text" id="msg" placeholder="e.g. Welcome Offer" required>
                </div>
            </div>

            <button type="submit" id="submitBtn">
                <span class="spinner" id="spinner"></span>
                <span id="btnText"><i class="fa-solid fa-paper-plane"></i> Send Request</span>
            </button>
        </form>

        <div class="status-box" id="statusBox">
            <div class="status-header">
                <span>Response Output</span>
                <button class="copy-btn" onclick="copyResult()"><i class="fa-regular fa-copy"></i> Copy</button>
            </div>
            <div class="status-content" id="statusContent"></div>
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
            const statusBox = document.getElementById('statusBox');
            const statusContent = document.getElementById('statusContent');

            spinner.style.display = 'block';
            btnText.innerHTML = 'Processing...';
            submitBtn.disabled = true;

            fetch(`?name=${encodeURIComponent(name)}&num=${encodeURIComponent(num)}&msg=${encodeURIComponent(msg)}`)
                .then(response => response.json())
                .then(data => {
                    statusBox.style.display = 'block';
                    statusContent.innerText = JSON.stringify(data, null, 2);
                    
                    if(data.status === false || data.error) {
                        statusContent.className = 'status-content error';
                    } else {
                        statusContent.className = 'status-content';
                    }
                })
                .catch(error => {
                    statusBox.style.display = 'block';
                    statusContent.className = 'status-content error';
                    statusContent.innerText = 'Connection Error: ' + error;
                })
                .finally(() => {
                    spinner.style.display = 'none';
                    btnText.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Send Request';
                    submitBtn.disabled = false;
                });
        });

        function copyResult() {
            const text = document.getElementById('statusContent').innerText;
            navigator.clipboard.writeText(text).then(() => {
                alert('Copied to clipboard!');
            });
        }
    </script>
</body>
</html>
