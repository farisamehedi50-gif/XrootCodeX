<?php  
header('Content-Type: application/json');  
  
if (!isset($_GET['username']) || empty($_GET['username'])) {  
    $response = ['error' => 'Please provide a username via GET parameter (e.g., ?username=example)'];  
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);  
    exit;  
}  
  
$username = htmlspecialchars($_GET['username']);  
  
function get_info($username) {  
    $headers = [  
        "Host: www.tiktok.com",  
        "sec-ch-ua: \" Not A;Brand\";v=\"99\", \"Chromium\";v=\"99\", \"Google Chrome\";v=\"99\"",  
        "sec-ch-ua-mobile: ?1",  
        "sec-ch-ua-platform: \"Android\"",  
        "upgrade-insecure-requests: 1",  
        "user-agent: Mozilla/5.0 (Linux; Android 8.0.0; Plume L2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.88 Mobile Safari/537.36",  
        "accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",  
        "sec-fetch-site: none",  
        "sec-fetch-mode: navigate",  
        "sec-fetch-user: ?1",  
        "sec-fetch-dest: document",  
        "accept-language: en-US,en;q=0.9,ar-DZ;q=0.8,ar;q=0.7,fr;q=0.6,hu;q=0.5,zh-CN;q=0.4,zh;q=0.3",  
    ];  
  
    $ch = curl_init();  
    curl_setopt($ch, CURLOPT_URL, "https://www.tiktok.com/@$username");  
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  
  
    $response = curl_exec($ch);  
    curl_close($ch);  
  
    if ($response === false) {  
        return ['error' => "Failed to fetch data for username: $username"];  
    }  
  
    try {  
        $userData = explode('webapp.user-detail"', $response)[1];  
        $userData = explode('"RecommendUserList"', $userData)[0];  
  
        function extract_between($string, $start, $end) {  
            $string = explode($start, $string)[1];  
            return explode($end, $string)[0];  
        }  
  
        $id = isset(explode('id":"', $userData)[1]) ? extract_between($userData, 'id":"', '",') : '';  
        $name = isset(explode('nickname":"', $userData)[1]) ? extract_between($userData, 'nickname":"', '",') : '';  
        
        // --- প্রোফাইল পিকচার সংগ্রহ করার কোড ---
        $avatar = isset(explode('avatarLarger":"', $userData)[1]) ? extract_between($userData, 'avatarLarger":"', '"') : '';  
        $avatar = stripslashes(str_replace('\u002F', '/', $avatar));
        // ------------------------------------

        $bio = isset(explode('signature":"', $userData)[1]) ? extract_between($userData, 'signature":"', '",') : '';  
        $country = isset(explode('region":"', $userData)[1]) ? extract_between($userData, 'region":"', '",') : '';  
        $private = isset(explode('privateAccount":', $userData)[1]) ? extract_between($userData, 'privateAccount":', ',') : '';  
        $followers = isset(explode('followerCount":', $userData)[1]) ? extract_between($userData, 'followerCount":', ',') : '';  
        $following = isset(explode('followingCount":', $userData)[1]) ? extract_between($userData, 'followingCount":', ',') : '';  
        $like = isset(explode('heart":', $userData)[1]) ? extract_between($userData, 'heart":', ',') : '';  
        $video = isset(explode('videoCount":', $userData)[1]) ? extract_between($userData, 'videoCount":', ',') : '';  
        $secid = isset(explode('secUid":"', $userData)[1]) ? extract_between($userData, 'secUid":"', '"') : '';  
  
        $bio = str_replace(['\\n', '\n'], "\n", $bio);  
        $bio = stripcslashes($bio);  
        $bio = preg_replace('/\s*\n\s*/', "\n", trim($bio));  
  
        $countryData = [  
            'US' => ['name' => 'United States', 'flag' => '🇺🇸'],  
            'GB' => ['name' => 'United Kingdom', 'flag' => '🇬🇧'],  
            'BD' => ['name' => 'Bangladesh', 'flag' => '🇧🇩'],  
            'IN' => ['name' => 'India', 'flag' => '🇮🇳'],  
            'CA' => ['name' => 'Canada', 'flag' => '🇨🇦'],  
            'AU' => ['name' => 'Australia', 'flag' => '🇦🇺'],  
            'BR' => ['name' => 'Brazil', 'flag' => '🇧🇷'],  
            'FR' => ['name' => 'France', 'flag' => '🇫🇷'],  
            'DE' => ['name' => 'Germany', 'flag' => '🇩🇪'],  
            'IT' => ['name' => 'Italy', 'flag' => '🇮🇹'],  
            'ES' => ['name' => 'Spain', 'flag' => '🇪🇸'],  
            'JP' => ['name' => 'Japan', 'flag' => '🇯🇵'],  
            'CN' => ['name' => 'China', 'flag' => '🇨🇳'],  
            'RU' => ['name' => 'Russia', 'flag' => '🇷🇺'],  
            'MX' => ['name' => 'Mexico', 'flag' => '🇲🇽'],  
            'ZA' => ['name' => 'South Africa', 'flag' => '🇿🇦'],  
            'KR' => ['name' => 'South Korea', 'flag' => '🇰🇷'],  
            'NG' => ['name' => 'Nigeria', 'flag' => '🇳🇬'],  
            'PK' => ['name' => 'Pakistan', 'flag' => '🇵🇰'],  
            'EG' => ['name' => 'Egypt', 'flag' => '🇪🇬'],  
            'TR' => ['name' => 'Turkey', 'flag' => '🇹🇷'],  
            'ID' => ['name' => 'Indonesia', 'flag' => '🇮🇩'],  
            'PH' => ['name' => 'Philippines', 'flag' => '🇵🇭'],  
            'VN' => ['name' => 'Vietnam', 'flag' => '🇻🇳'],  
            'TH' => ['name' => 'Thailand', 'flag' => '🇹🇭'],  
            'AR' => ['name' => 'Argentina', 'flag' => '🇦🇷'],  
            'CL' => ['name' => 'Chile', 'flag' => '🇨🇱'],  
            'CO' => ['name' => 'Colombia', 'flag' => '🇨🇴'],  
            'PE' => ['name' => 'Peru', 'flag' => '🇵🇪'],  
            'NZ' => ['name' => 'New Zealand', 'flag' => '🇳🇿'],  
            'SG' => ['name' => 'Singapore', 'flag' => '🇸🇬'],  
            'MY' => ['name' => 'Malaysia', 'flag' => '🇲🇾'],  
            'SA' => ['name' => 'Saudi Arabia', 'flag' => '🇸🇦'],  
            'AE' => ['name' => 'United Arab Emirates', 'flag' => '🇦🇪'],  
            'IE' => ['name' => 'Ireland', 'flag' => '🇮🇪'],  
            'NL' => ['name' => 'Netherlands', 'flag' => '🇳🇱'],  
            'BE' => ['name' => 'Belgium', 'flag' => '🇧🇪'],  
            'CH' => ['name' => 'Switzerland', 'flag' => '🇨🇭'],  
            'SE' => ['name' => 'Sweden', 'flag' => '🇸🇪'],  
            'NO' => ['name' => 'Norway', 'flag' => '🇳🇴'],  
            'DK' => ['name' => 'Denmark', 'flag' => '🇩🇰'],  
            'FI' => ['name' => 'Finland', 'flag' => '🇫🇮']  
        ];  
        $countryn = isset($countryData[$country]) ? $countryData[$country]['name'] : $country;  
        $countryf = isset($countryData[$country]) ? $countryData[$country]['flag'] : '';  
  
        $binary = str_pad(decbin($id), 64, '0', STR_PAD_LEFT);  
        $timestamp = bindec(substr($binary, 0, 31));  
        $cdt = date('Y-m-d H:i:s', $timestamp);  
  
        return [  
            'username' => $username,  
            'secuid' => $secid,  
            'name' => $name,  
            'avatar' => $avatar,  
            'followers' => $followers,  
            'following' => $following,  
            'likes' => $like,  
            'videos' => $video,  
            'private' => $private,  
            'country' => $countryn,  
            'country_flag' => $countryf,  
            'created_date' => $cdt,  
            'id' => $id,  
            'bio' => $bio,  
            'Api owner' => 'XrootCodeX',
            'Developer' => 'MehediSanix'
        ];  
    } catch (Exception $e) {  
        return ['error' => "Wrong UserName: $username"];  
    }  
}  
  
$second_response_data = get_info($username);  
  
echo json_encode($second_response_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);  
?>
