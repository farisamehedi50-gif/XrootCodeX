<?php  
header('Content-Type: application/json; charset=utf-8');  
  
if (!isset($_GET['username']) || empty($_GET['username'])) {  
    $response = ['error' => 'Please provide a username via GET parameter (e.g., ?username=example)'];  
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);  
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
        "user-agent: Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Mobile Safari/537.36",  
        "accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8",  
        "sec-fetch-site: none",  
        "sec-fetch-mode: navigate",  
        "sec-fetch-user: ?1",  
        "sec-fetch-dest: document",  
        "accept-language: en-US,en;q=0.9",  
    ];  
  
    $ch = curl_init();  
    curl_setopt($ch, CURLOPT_URL, "https://www.tiktok.com/@$username");  
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
  
    $response = curl_exec($ch);  
    curl_close($ch);  
  
    if ($response === false) {  
        return ['error' => "Failed to fetch data for username: $username"];  
    }  
  
    try {  
        $userData = explode('webapp.user-detail"', $response)[1];  
        $userData = explode('"RecommendUserList"', $userData)[0];  
  
        function extract_between($string, $start, $end) {  
            $parts = explode($start, $string);
            if (!isset($parts[1])) return '';
            return explode($end, $parts[1])[0];  
        }  
  
        // উন্নত এবং নিখুঁত ইউআরএল ক্লিনিং ফাংশন (u002F সমস্যা সমাধান করা হয়েছে)
        function clean_url($url) {
            if (empty($url)) return '';
            $url = stripslashes($url);
            // ইউনিকোড এবং ব্যাকস্ল্যাশ ডিকোড করা
            $url = str_replace(['\u002F', '\/', 'u002F'], '/', $url);
            // যদি ডাবল স্ল্যাশ ভেঙে গিয়ে থাকে তা ঠিক করা
            $url = preg_replace('#(?<!:)//+#', '/', $url);
            $url = str_replace('https:/', 'https://', $url);
            return htmlspecialchars_decode(trim($url));
        }

        // তথ্য এক্সট্রাক্ট করা
        $id = extract_between($userData, 'id":"', '",');  
        $unique_id = extract_between($userData, 'uniqueId":"', '",');  
        $name = extract_between($userData, 'nickname":"', '",');  
        
        // ছবির লিংকগুলো সঠিকভাবে তুলে ক্লিন করা
        $avatar_thumb = clean_url(extract_between($userData, 'avatarThumb":"', '"'));  
        $avatar_medium = clean_url(extract_between($userData, 'avatarMedium":"', '"'));  
        $avatar_larger = clean_url(extract_between($userData, 'avatarLarger":"', '"'));  

        $bio = extract_between($userData, 'signature":"', '",');  
        
        $country_raw = extract_between($userData, 'region":"', '"');
        $country = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $country_raw), 0, 2));

        $private = extract_between($userData, 'privateAccount":', ',');  
        $verified = extract_between($userData, 'verified":', ',');  
        $commerce = extract_between($userData, 'commerceUser":', ',');
        
        $followers = extract_between($userData, 'followerCount":', ',');  
        $following = extract_between($userData, 'followingCount":', ',');  
        $like = extract_between($userData, 'heart":', ',');  
        $video = extract_between($userData, 'videoCount":', ',');  
        $digg = extract_between($userData, 'diggCount":', ','); 
        
        $secid = extract_between($userData, 'secUid":"', '"');  
        $bio_url = clean_url(extract_between($userData, 'linkItem":{{"url":"', '"'));
  
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

        $countryn = isset($countryData[$country]) ? $countryData[$country]['name'] : ($country ? $country : 'Global');  
        $countryf = isset($countryData[$country]) ? $countryData[$country]['flag'] : '🌍';  
  
        $binary = str_pad(decbin($id), 64, '0', STR_PAD_LEFT);  
        $timestamp = bindec(substr($binary, 0, 31));  
        $cdt = date('Y-m-d H:i:s', $timestamp);  
  
        return [  
            'status' => true,
            'username' => $unique_id ? $unique_id : $username,  
            'name' => $name,  
            'id' => $id,  
            'secuid' => $secid,  
            'avatars' => [
                'thumb' => $avatar_thumb,       
                'medium' => $avatar_medium,     
                'larger' => $avatar_larger      
            ],  
            'statistics' => [
                'followers' => (int)$followers,  
                'following' => (int)$following,  
                'total_likes' => (int)$like,  
                'total_videos' => (int)$video,
                'total_liked_videos' => (int)$digg
            ],
            'account_info' => [
                'verified' => ($verified === 'true' || $verified === '1') ? true : false,
                'private' => ($private === 'true' || $private === '1') ? true : false,
                'commerce_user' => ($commerce === 'true' || $commerce === '1') ? true : false,
                'country' => $countryn,  
                'country_code' => $country,
                'country_flag' => $countryf,  
                'created_date' => $cdt
            ],
            'bio_details' => [
                'bio' => $bio,  
                'bio_link' => $bio_url ? $bio_url : null
            ],
            'Api owner' => 'XrootCodeX',  
            'Developer' => 'MehediSanix'  
        ];  
    } catch (Exception $e) {  
        return [
            'status' => false,
            'error' => "Wrong Username or Profile is Private/Protected: $username"
        ];  
    }  
}  
  
$response_data = get_info($username);  
  
echo json_encode($response_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);  
?>
