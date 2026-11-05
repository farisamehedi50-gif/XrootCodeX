<?php
/**
 * Bangladesh Birth Record (BRN) Verification API
 * Owner & Developer: @MehediSanix
 * Channel: https://t.me/CobraX_Empire
 */

if (PHP_VERSION_ID < 70400) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'PHP 7.4 or newer is required. Current version: ' . PHP_VERSION,
    ]);
    exit;
}

ini_set('display_errors', '0');
ini_set('html_errors', '0');
error_reporting(E_ALL);

ini_set('log_errors', '1');

@set_time_limit(120);
@ini_set('max_execution_time', '120');
@ini_set('memory_limit', '256M');

@ob_start();

if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool
    {
        return $needle === '' || strpos($haystack, $needle) !== false;
    }
}
if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool
    {
        return strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}
if (!function_exists('str_ends_with')) {
    function str_ends_with(string $haystack, string $needle): bool
    {
        return $needle === '' || substr($haystack, -strlen($needle)) === $needle;
    }
}

const API_VERSION               = '3.0.0';
const VERIFY_URL                = 'https://everify.bdris.gov.bd/';
const SEARCH_PATH               = '/UBRNVerification/Search';
const DEFAULT_SOLVER_URL        = 'https://math-solver.amzz.workers.dev/solve';
const MAX_CAPTCHA_ATTEMPTS      = 5;
const UPSTREAM_TIMEOUT_SECONDS  = 45;
const RESULT_CACHE_TTL_SECONDS  = 30;

const DEVELOPER_INFO = [
    'owner'     => '@XrootCodeX',
    'developer' => '@MehediSanix',
    'channel'   => 'https://t.me/CobraX_Empire'
];

const BASE_HEADERS = [
    'User-Agent: Mozilla/5.0 (compatible; BirthRecordVerifier/3.0; +https://workers.cloudflare.com)',
    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
    'Accept-Language: bn-BD,bn;q=0.9,en-US;q=0.8,en;q=0.7',
    'Cache-Control: no-cache',
    'Pragma: no-cache',
];

class UpstreamTimeout extends RuntimeException {}
class UpstreamError extends RuntimeException {}

set_exception_handler(static function ($error) {
    error_log('[birth-api] Unhandled: ' . $error->getMessage());
    sendJson(['success' => false, 'error' => 'Unexpected server error.'], 500);
});

handleRequest();

function handleRequest(): void
{
    $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));

    if ($method === 'OPTIONS') {
        sendJson(null, 204);
    }

    if ($method !== 'GET') {
        sendJson(['success' => false, 'error' => 'Method not allowed. Use GET.'], 405, ['Allow: GET, OPTIONS']);
    }

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    $protocol = $isHttps ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = $_SERVER['SCRIPT_NAME'] ?? ($_SERVER['PHP_SELF'] ?? '/');
    $endpoint = $protocol . $host . $script;

    if (!isset($_GET['brn']) && !isset($_GET['dob'])) {
        sendJson([
            'name'       => 'Bangladesh Birth Record Verification API',
            'version'    => API_VERSION,
            'owner'      => '@MehediSanix',
            'developer'  => '@MehediSanix',
            'channel'    => 'https://t.me/CobraX_Empire',
            'endpoint'   => $endpoint . '?brn={your_brn}&dob={your_dob}',
            'method'     => 'GET',
            'parameters' => [
                'brn'    => '১৭ ডিজিটের জন্ম নিবন্ধন নম্বর',
                'dob'    => 'জন্ম তারিখ, YYYY-MM-DD ফরম্যাটে',
                'pretty' => 'ঐচ্ছিক — 1 দিলে সুন্দর JSON',
                'flush'  => 'ঐচ্ছিক — 1 দিলে ক্যাশ বাইপাস',
                'debug'  => 'ঐচ্ছিক — 1 দিলে ডায়াগনস্টিক তথ্য',
            ],
            'example'    => $endpoint . '?brn=20186113154117630&dob=2018-10-12',
            'output'     => 'All visible record fields + structured sections + developer_info.',
            'auth'       => env('API_KEY') !== ''
                ? 'Required — send X-API-Key header or api_key parameter.'
                : 'No API key required.',
        ], 200);
    }

    $configuredKey = env('API_KEY');
    if ($configuredKey !== '') {
        $suppliedKey = (string) ($_SERVER['HTTP_X_API_KEY'] ?? ($_GET['api_key'] ?? ''));
        if ($suppliedKey === '' || !hash_equals($configuredKey, $suppliedKey)) {
            sendJson(['success' => false, 'error' => 'Unauthorized.'], 401);
        }
    }

    $brn = preg_replace('/\D+/', '', toEnglishDigits(trim((string) ($_GET['brn'] ?? ''))));
    $dob = trim((string) ($_GET['dob'] ?? ''));

    if (!preg_match('/^[0-9]{17}$/', (string) $brn)) {
        sendJson(['success' => false, 'error' => 'BRN must be exactly 17 digits.'], 400);
    }

    $dob = normalizeDate($dob) ?? $dob;
    if (!isValidIsoDate($dob)) {
        sendJson(['success' => false, 'error' => 'DOB must be a valid date (YYYY-MM-DD, e.g. 2018-10-12).'], 400);
    }

    $cacheKey  = hash('sha256', $brn . '|' . $dob);
    $bypass    = (string) ($_GET['flush'] ?? '') === '1';
    $startedAt = microtime(true);

    if (!$bypass) {
        $cached = readCache($cacheKey);
        if ($cached !== null) {
            sendJson($cached, 200, ['X-Birth-Cache: HIT', 'X-Elapsed-Ms: 0']);
        }
    }

    try {
        $result = verifyBirthRecord($brn, $dob, env('SOLVER_URL', DEFAULT_SOLVER_URL));
        $elapsedMs = (int) round((microtime(true) - $startedAt) * 1000);

        if ($result['status'] === 200) {
            if (!$bypass) {
                writeCache($cacheKey, $result['body']);
            }
            sendJson($result['body'], 200, [
                'X-Birth-Cache: MISS',
                'X-Elapsed-Ms: ' . $elapsedMs,
            ]);
        }

        sendJson($result['body'], $result['status'], ['X-Elapsed-Ms: ' . $elapsedMs]);
    } catch (UpstreamTimeout $error) {
        error_log('[birth-api] Timeout: ' . $error->getMessage());
        sendJson(['success' => false, 'error' => 'Verification page timed out. Please try again.'], 504);
    } catch (Throwable $error) {
        error_log('[birth-api] Failure: ' . $error->getMessage());
        sendJson(['success' => false, 'error' => 'Unexpected automation error.'], 502);
    }
}

function verifyBirthRecord(string $brn, string $dob, string $solverUrl): array
{
    if (!function_exists('curl_init')) {
        return [
            'body'   => ['success' => false, 'error' => 'cURL extension is not enabled on this server.'],
            'status' => 500,
        ];
    }

    $client    = new HttpClient(UPSTREAM_TIMEOUT_SECONDS);
    $searchUrl = absoluteUrl(SEARCH_PATH, VERIFY_URL);
    $lastError = 'Verification page timed out.';
    $attempts  = 0;

    for ($attempt = 1; $attempt <= MAX_CAPTCHA_ATTEMPTS; $attempt++) {
        $attempts = $attempt;

        try {
            $page = $client->get(VERIFY_URL);
            if (!isSuccessStatus($page['status'])) {
                throw new UpstreamError('Verification site returned HTTP ' . $page['status']);
            }

            $pageHtml    = $page['body'];
            $token       = hiddenInput($pageHtml, '__RequestVerificationToken');
            $captchaText = hiddenInput($pageHtml, 'CaptchaDeText');
            $captchaSrc  = imageSource($pageHtml, 'CaptchaImage');

            if ($captchaSrc === '' || $captchaText === '') {
                throw new UpstreamError('CAPTCHA was not found on the verification page.');
            }

            $captcha = $client->get(absoluteUrl($captchaSrc, VERIFY_URL), [
                'Accept: image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
                'Referer: ' . VERIFY_URL,
            ]);
            if (!isSuccessStatus($captcha['status'])) {
                throw new UpstreamError('CAPTCHA image returned HTTP ' . $captcha['status']);
            }

            $answer = solveCaptcha(
                $client,
                $captcha['body'],
                (string) ($captcha['headers']['content-type'] ?? 'image/png'),
                $solverUrl
            );

            if ($answer === null) {
                $lastError = 'Failed to solve CAPTCHA via API.';
                continue;
            }

            $fields = [
                'UBRN'             => $brn,
                'BirthDate'        => $dob,
                'CaptchaDeText'    => $captchaText,
                'CaptchaInputText' => (string) $answer,
            ];
            if ($token !== '') {
                $fields['__RequestVerificationToken'] = $token;
            }

            $submit = $client->postForm($searchUrl, $fields, [
                'Accept: text/html,application/xhtml+xml',
                'Origin: ' . rtrim(originOf(VERIFY_URL), '/'),
                'Referer: ' . VERIFY_URL,
            ]);

            if (!isSuccessStatus($submit['status'])) {
                throw new UpstreamError('Search endpoint returned HTTP ' . $submit['status']);
            }

            $resultHtml = $submit['body'];
            $resultText = strtolower(stripTags($resultHtml));

            if (isCaptchaRejected($resultText)) {
                $lastError = 'The CAPTCHA answer was rejected by the verification site.';
                continue;
            }

            $data = parseResultPage($resultHtml);

            if (hasRecordData($data)) {
                return [
                    'body' => [
                        'success' => true,
                        'code'    => 200,
                        'status'  => true,
                        'message' => 'তথ্য সফল ভাবে পাওয়া গেছে!!',
                        'data'    => $data,
                        'meta'    => [
                            'checked_at' => date('c'),
                            'attempts'   => $attempts,
                            'source'     => 'everify.bdris.gov.bd',
                        ],
                    ],
                    'status' => 200,
                ];
            }

            return [
                'body'   => [
                    'success' => false,
                    'error'   => 'Result data was not found. Please check BRN and date of birth.',
                    'meta'    => ['attempts' => $attempts],
                ],
                'status' => 404,
            ];
        } catch (UpstreamTimeout $error) {
            $lastError = 'Verification page timed out.';
            continue;
        } catch (UpstreamError $error) {
            $lastError = $error->getMessage();
            continue;
        }
    }

    return [
        'body'   => ['success' => false, 'error' => $lastError, 'meta' => ['attempts' => $attempts]],
        'status' => str_contains(strtolower($lastError), 'timed out') ? 504 : 422,
    ];
}

function solveCaptcha(HttpClient $client, string $bytes, string $contentType, string $solverUrl): ?int
{
    if ($bytes === '' || $solverUrl === '') {
        return null;
    }

    $tmp = tempnam(sys_get_temp_dir(), 'captcha_');
    if ($tmp === false) {
        return null;
    }
    if (file_put_contents($tmp, $bytes) === false) {
        @unlink($tmp);
        return null;
    }

    $mime = strtolower(trim(explode(';', $contentType, 2)[0]));
    if (!str_starts_with($mime, 'image/')) {
        $mime = 'image/png';
    }

    try {
        $response = $client->postFile($solverUrl, 'image', $tmp, $mime, 'captcha.png');
    } finally {
        @unlink($tmp);
    }

    if (!isSuccessStatus($response['status'])) {
        return null;
    }

    $payload = json_decode($response['body'], true);
    if (!is_array($payload) || ($payload['success'] ?? false) !== true || !array_key_exists('answer', $payload)) {
        return null;
    }
    if (!is_numeric($payload['answer'])) {
        return null;
    }

    $value = (int) $payload['answer'];

    return abs($value) <= 100000 ? $value : null;
}

class HttpClient
{
    private $handle;
    private $timeout;

    public function __construct(int $timeout = UPSTREAM_TIMEOUT_SECONDS)
    {
        $handle = curl_init();
        if ($handle === false) {
            throw new UpstreamError('Could not initialize cURL.');
        }
        $this->handle  = $handle;
        $this->timeout = $timeout;
    }

    public function __destruct()
    {
        if ($this->handle !== null) {
            @curl_close($this->handle);
            $this->handle = null;
        }
    }

    public function get(string $url, array $headers = []): array
    {
        return $this->send($url, 'GET', $headers);
    }

    public function postForm(string $url, array $fields, array $headers = []): array
    {
        return $this->send($url, 'POST', $headers, http_build_query($fields, '', '&'));
    }

    public function postFile(string $url, string $field, string $filePath, string $mime, string $fileName): array
    {
        return $this->send($url, 'POST', [], [
            $field => new CURLFile($filePath, $mime, $fileName),
        ]);
    }

    private function send(string $url, string $method, array $headers = [], $payload = null): array
    {
        curl_reset($this->handle);

        $options = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_ENCODING       => '',
            CURLOPT_COOKIEFILE     => '',
            CURLOPT_HTTPHEADER     => array_values(array_unique(array_merge(BASE_HEADERS, $headers))),
            CURLOPT_HEADER         => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ];

        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
            if ($payload !== null) {
                $options[CURLOPT_POSTFIELDS] = $payload;
            }
        }

        curl_setopt_array($this->handle, $options);

        $raw       = curl_exec($this->handle);
        $errno     = curl_errno($this->handle);
        $error     = curl_error($this->handle);
        $status    = (int) curl_getinfo($this->handle, CURLINFO_HTTP_CODE);
        $headerLen = (int) curl_getinfo($this->handle, CURLINFO_HEADER_SIZE);

        if ($errno === CURLE_OPERATION_TIMEDOUT) {
            throw new UpstreamTimeout('cURL timed out after ' . $this->timeout . 's');
        }
        if ($raw === false || $errno !== 0) {
            throw new UpstreamError($error !== '' ? $error : 'HTTP request failed.');
        }

        return [
            'status'  => $status,
            'headers' => parseResponseHeaders(substr((string) $raw, 0, $headerLen)),
            'body'    => (string) substr((string) $raw, $headerLen),
        ];
    }
}

function parseResultPage(string $html): array
{
    $fields = [];
    $rows   = [];

    preg_match_all('/<tr\b([^>]*)>(.*?)<\/tr>/is', $html, $rowMatches, PREG_SET_ORDER);
    foreach ($rowMatches as $rowMatch) {
        if (stripos($rowMatch[1] . $rowMatch[2], 'ui-datepicker-div') !== false) {
            continue;
        }

        preg_match_all('/<(?:th|td)\b[^>]*>(.*?)<\/(?:th|td)>/is', $rowMatch[2], $cellMatches);
        if (empty($cellMatches[1])) {
            continue;
        }

        $cells = array_map('cellInfo', $cellMatches[1]);

        if (trim(implode('', array_column($cells, 'full'))) === '') {
            continue;
        }
        $rows[] = $cells;
    }

    $total = count($rows);
    for ($index = 0; $index < $total; $index++) {
        $cells     = $rows[$index];
        $next      = isset($rows[$index + 1]) ? $rows[$index + 1] : [];
        $current   = array_column($cells, 'full');
        $following = array_column($next, 'full');

        if (in_array(count($cells), [2, 3], true)
            && count($next) === count($cells)
            && isSummaryHeaderRow($current)
        ) {
            foreach ($current as $position => $label) {
                addField($fields, $label, $following[$position] ?? '');
            }
            $index++;
            continue;
        }

        if (count($cells) >= 4) {
            for ($position = 0; $position + 1 < count($cells); $position += 2) {
                addField($fields, $cells[$position]['full'], $cells[$position + 1]['full']);
            }
            continue;
        }

        if (count($cells) === 3) {
            addField($fields, $cells[0]['full'], $cells[1]['full']);
            addControlField($fields, $cells[2]);
            continue;
        }

        if (count($cells) === 2) {
            addField($fields, $cells[0]['full'], $cells[1]['full']);
            addControlField($fields, $cells[1]);
        }
    }

    preg_match_all('/<dt\b[^>]*>(.*?)<\/dt>\s*<dd\b[^>]*>(.*?)<\/dd>/is', $html, $matches, PREG_SET_ORDER);
    foreach ($matches as $match) {
        addField($fields, cleanText($match[1]), cellInfo($match[2])['full']);
    }

    $usefulFields = array_values(array_filter(
        $fields,
        static function (array $field): bool {
            return $field['value'] !== ''
                && !isDeveloperField($field['label'])
                && !isDeveloperField($field['value']);
        }
    ));

    $allDetails = [];
    foreach ($usefulFields as $field) {
        $key = aliasForLabel($field['label']);
        if ($key === null) {
            $key = safeFieldKey($field['label']);
        }
        if ($key === null || isDeveloperField($key)) {
            continue;
        }
        if (!array_key_exists($key, $allDetails)) {
            $allDetails[$key] = $field['value'];
        }
    }

    $placeOfBirth = bilingualField($usefulFields, ['জন্মস্থান', 'জন্মের স্থান'], ['Place of Birth', 'Birth Place']);
    $placeAddress = mergeAddress(
        parseAddressParts($placeOfBirth['en']),
        parseAddressParts($placeOfBirth['bn'])
    );

    $bodyText               = stripTags($html);
    $registerOfficeLocation = extractRegisterOfficeLocation($bodyText);
    $registerOffice         = parseRegisterOffice($registerOfficeLocation);

    $dateOfBirth      = findField($usefulFields, ['Date of Birth', 'Birth Date']);
    $registrationDate = findField($usefulFields, ['Registration Date']);
    $issuanceDate     = findField($usefulFields, ['Issuance Date', 'Issue Date']);

    return array_merge($allDetails, [
        'registration_details' => [
            'registration_date'      => $registrationDate,
            'registration_date_iso'  => normalizeDate($registrationDate),
            'registration_office'    => findField($usefulFields, ['Registration Office', 'Registering Office']),
            'issuance_date'          => $issuanceDate,
            'issuance_date_iso'      => normalizeDate($issuanceDate),
        ],
        'birth_details' => [
            'date_of_birth'             => $dateOfBirth,
            'date_of_birth_iso'         => normalizeDate($dateOfBirth),
            'birth_registration_number' => findField($usefulFields, ['Birth Registration Number', 'Birth Registration No', 'BRN']),
            'sex'                       => findField($usefulFields, ['Sex', 'Gender']),
        ],
        'person_details' => [
            'name'           => bilingualField($usefulFields, ['নিবন্ধিত ব্যক্তির নাম', 'ব্যক্তির নাম'], ['Registered Person Name', 'Name of Registered Person']),
            'place_of_birth' => $placeOfBirth,
        ],
        'address_details' => [
            'place_of_birth'     => $placeAddress,
            'raw_place_of_birth' => $placeOfBirth,
        ],
        'parents_details' => [
            'mother' => [
                'name'        => bilingualField($usefulFields, ['মাতার নাম', 'মায়ের নাম'], ["Mother's Name", 'Mothers Name', 'Mother Name']),
                'nationality' => bilingualField($usefulFields, ['মাতার জাতীয়তা', 'মায়ের জাতীয়তা'], ["Mother's Nationality", 'Mothers Nationality', 'Mother Nationality']),
            ],
            'father' => [
                'name'        => bilingualField($usefulFields, ['পিতার নাম', 'বাবার নাম'], ["Father's Name", 'Fathers Name', 'Father Name']),
                'nationality' => bilingualField($usefulFields, ['পিতার জাতীয়তা', 'বাবার জাতীয়তা'], ["Father's Nationality", 'Fathers Nationality', 'Father Nationality']),
            ],
        ],
        'register_office_details' => [
            'location'        => $registerOfficeLocation,
            'union_or_office' => $registerOffice['union_or_office'],
            'upazila'         => $registerOffice['upazila'],
            'district'        => $registerOffice['district'],
        ],
        'record_source' => extractRecordSource($bodyText),
        'all_fields'    => $usefulFields,
    ]);
}

function cellInfo(string $fragment): array
{
    $text = cleanText(preg_replace(
        ['/<input\b[^>]*>/i', '/<textarea\b[^>]*>.*?<\/textarea>/is'],
        ' ',
        $fragment
    ));

    $controls = [];

    preg_match_all('/<input\b([^>]*)>/i', $fragment, $inputs, PREG_SET_ORDER);
    foreach ($inputs as $input) {
        if (strtolower(attribute($input[1], 'type')) === 'hidden') {
            continue;
        }
        $value = attribute($input[1], 'value');
        if ($value !== '') {
            $controls[] = $value;
        }
    }

    preg_match_all('/<textarea\b[^>]*>(.*?)<\/textarea>/is', $fragment, $textareas);
    foreach ($textareas[1] as $value) {
        $value = cleanText($value);
        if ($value !== '') {
            $controls[] = $value;
        }
    }

    $controls = array_values(array_unique(array_filter(array_map('cleanText', $controls))));

    return [
        'text'     => $text,
        'controls' => $controls,
        'full'     => implode(' ', array_values(array_unique(array_filter(array_merge([$text], $controls))))),
    ];
}

function addControlField(array &$fields, array $cell): void
{
    if ($cell['text'] !== '' && !empty($cell['controls'])) {
        addField($fields, $cell['text'], $cell['controls'][0]);
    }
}

function addField(array &$fields, string $label, string $value): void
{
    $label = trim((string) preg_replace('/:\s*$/u', '', cleanText($label)));
    $value = cleanText($value);

    if ($label === '' || $value === '' || isDeveloperField($label)) {
        return;
    }

    $fields[] = ['label' => $label, 'value' => $value];
}

function isSummaryHeaderRow(array $cells): bool
{
    $labels = [
        'registrationdate',
        'registrationoffice',
        'issuancedate',
        'dateofbirth',
        'birthregistrationnumber',
        'sex',
    ];

    return count($cells) > 0
        && count(array_filter($cells, static function (string $cell) use ($labels): bool {
            return in_array(labelKey($cell), $labels, true);
        })) === count($cells);
}

function findField(array $fields, array $labels): ?string
{
    $wanted = array_map('labelKey', $labels);
    foreach ($fields as $field) {
        if (in_array(labelKey($field['label']), $wanted, true)) {
            return $field['value'];
        }
    }
    return null;
}

function bilingualField(array $fields, array $banglaLabels, array $englishLabels): array
{
    return [
        'bn' => findField($fields, $banglaLabels),
        'en' => findField($fields, $englishLabels),
    ];
}

function normalizeKey(string $value): string
{
    $value = str_replace(
        ["\u{09DC}", "\u{09DD}", "\u{09DF}"],
        ["\u{09A1}\u{09BC}", "\u{09A2}\u{09BC}", "\u{09AF}\u{09BC}"],
        $value
    );

    return str_replace(["\u{09BC}", "\u{200B}", "\u{200C}", "\u{200D}", "\u{FEFF}"], '', $value);
}

function labelKey(string $value): string
{
    $value = strtolower(cleanText($value));
    $value = normalizeKey($value);

    $value = (string) preg_replace('/[\'"’‘“”():.,\/_\-\s\x{0964}\x{0965}]+/u', '', $value);

    return $value;
}

function aliasForLabel(string $label): ?string
{
    static $normalized = null;

    if ($normalized === null) {
        $aliases = [
            'requestid'                   => 'requestId',
            'registeredpersonname'        => 'nameEnglish',
            'nameofregisteredperson'      => 'nameEnglish',
            'ব্যক্তিরনাম'                  => 'nameBangla',
            'নিবন্ধিতব্যক্তিরনাম'          => 'nameBangla',
            'gender'                      => 'genderEnglish',
            'sex'                         => 'genderEnglish',
            'জেন্ডার'                      => 'genderBangla',
            'লিঙ্গ'                        => 'genderBangla',
            'dateofbirth'                 => 'dateOfBirth',
            'birthdate'                   => 'dateOfBirth',
            'dateofbirthinword'           => 'dobInWord',
            'dobinword'                   => 'dobInWord',
            'converteddob'                => 'convertedDob',
            'birthregistrationnumber'     => 'brn',
            'birthregistrationno'         => 'brn',
            'brn'                         => 'brn',
            'phonenumber'                 => 'phoneNumber',
            'religion'                    => 'religionEnglish',
            'ধর্ম'                        => 'religionBangla',
            'todaydateenglish'            => 'todayDateEnglish',
            'todaydatebangla'             => 'todayDateBangla',
            'isadult'                     => 'isAdult',
            'officeid'                    => 'officeId',

            'fathername'                  => 'fatherNameEnglish',
            'fathersname'                 => 'fatherNameEnglish',
            'পিতারনাম'                    => 'fatherNameBangla',
            'বাবারনাম'                    => 'fatherNameBangla',
            'fathernationality'           => 'fatherNationalityEnglish',
            'fathersnationality'          => 'fatherNationalityEnglish',
            'পিতারজাতীয়তা'                => 'fatherNationalityBangla',
            'বাবারজাতীয়তা'                => 'fatherNationalityBangla',

            'mothername'                  => 'motherNameEnglish',
            'mothersname'                 => 'motherNameEnglish',
            'মাতারনাম'                    => 'motherNameBangla',
            'মায়েরনাম'                    => 'motherNameBangla',
            'mothernationality'           => 'motherNationalityEnglish',
            'mothersnationality'          => 'motherNationalityEnglish',
            'মাতারজাতীয়তা'                => 'motherNationalityBangla',
            'মায়েরজাতীয়তা'                => 'motherNationalityBangla',

            'birthplace'                  => 'birthPlaceEnglish',
            'placeofbirth'                => 'birthPlaceEnglish',
            'জন্মস্থান'                    => 'birthPlaceBangla',
            'জন্মেরস্থান'                  => 'birthPlaceBangla',
            'address'                     => 'addressEnglish',
            'ঠিকানা'                       => 'addressBangla',
            'registrationoffice'          => 'registerOfficeEnglish',
            'registeringoffice'           => 'registerOfficeEnglish',
            'নিবন্ধনঅফিস'                  => 'registerOfficeBangla',
            'রেজিস্টারঅফিস'                 => 'registerOfficeBangla',
            'locationoftheregisteroffice' => 'registerOfficeLocationEnglish',
            'নিবন্ধনঅফিসেরঅবস্থান'         => 'registerOfficeLocationBangla',

            'registrationdate'            => 'registrationDate',
            'issuancedate'                => 'issuanceDate',
            'issuedate'                   => 'issuanceDate',
        ];

        $normalized = [];
        foreach ($aliases as $aliasLabel => $target) {
            $normalized[labelKey($aliasLabel)] = $target;
        }
    }

    return $normalized[labelKey($label)] ?? null;
}

function safeFieldKey(string $label): ?string
{
    $key = (string) preg_replace('/[^\p{L}\p{N}]+/u', '_', labelKey($label));
    $key = trim($key, '_');

    return $key !== '' ? $key : null;
}

function parseAddressParts(?string $value): array
{
    $result = [
        'village'     => null,
        'post_office' => null,
        'union'       => null,
        'upazila'     => null,
        'district'    => null,
    ];

    if ($value === null || trim($value) === '') {
        return $result;
    }

    $text = str_replace(['।', '|', ';', "\n", "\r", "\t"], ',', $value);

    $labels = [
        'village'      => ['village', 'vill'],
        'post_office'  => ['post office', 'postoffice', 'post', 'p.o', 'po'],
        'union'        => ['union'],
        'upazila'      => ['upazilla', 'upazila', 'upjalla', 'upojela', 'thana'],
        'district'     => ['district', 'dist'],

        'গ্রাম'        => ['village'],
        'সাং'          => ['village'],
        'ডাকঘর'       => ['post_office'],
        'পোস্টঅফিস'    => ['post_office'],
        'পো'          => ['post_office'],
        'ইউনিয়ন'      => ['union'],
        'ইউপি'        => ['union'],
        'উপজেলা'      => ['upazila'],
        'উপঃ'         => ['upazila'],
        'জেলা'        => ['district'],
    ];

    $patterns = [];
    foreach ($labels as $label => $canonical) {
        $patterns[] = ['label' => $label, 'canonical' => $canonical[0], 'length' => strlen($label)];
    }
    usort($patterns, static function (array $a, array $b): int {
        return $b['length'] <=> $a['length'];
    });

    $alternatives = array_map(static function (array $item): string {
        return preg_quote($item['label'], '/');
    }, $patterns);

    $regex = '/(?<![\p{Bengali}])(?:' . implode('|', $alternatives) . ')(?![\p{Bengali}])\s*[:.\-–]?\s*/iu';

    if (!preg_match_all($regex, $text, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
        return $result;
    }

    $textLength = strlen($text);
    $map        = [];
    foreach ($labels as $label => $canonical) {
        $map[labelKey($label)] = $canonical[0];
    }

    foreach ($matches as $index => $match) {
        $matchedText = $match[0][0];
        $start       = $match[0][1] + strlen($matchedText);
        $end         = isset($matches[$index + 1]) ? $matches[$index + 1][0][1] : $textLength;

        $key = $map[labelKey($matchedText)] ?? null;
        if ($key === null || $result[$key] !== null) {
            continue;
        }

        $part = (string) substr($text, $start, max(0, $end - $start));

        $part = (string) preg_replace('/^[\s,.:;\x{0964}\-–]+|[\s,.:;\x{0964}\-–]+$/u', '', $part);
        if ($part !== '') {
            $result[$key] = toValidUtf8($part);
        }
    }

    return $result;
}

function mergeAddress(array $english, array $bangla): array
{
    foreach ($english as $key => $value) {
        if ($value === null && isset($bangla[$key])) {
            $english[$key] = $bangla[$key];
        }
    }

    return $english;
}

function extractRegisterOfficeLocation(string $bodyText): ?string
{
    return preg_match('/Location\s+of\s+the\s+Register\s+office\s*:\s*([^\.\n]+?)(?:\.|$)/i', $bodyText, $match)
        ? cleanText($match[1])
        : null;
}

function parseRegisterOffice(?string $value): array
{
    $parts = $value
        ? array_values(array_filter(array_map('trim', explode(',', $value))))
        : [];

    return [
        'union_or_office' => $parts[0] ?? null,
        'upazila'         => $parts[1] ?? null,
        'district'        => $parts[2] ?? null,
    ];
}

function extractRecordSource(string $bodyText): ?string
{
    if (preg_match('/This record is retrieved from\s+(.+?)\s*\.\s*Location\s+of\s+the\s+Register\s+office/i', $bodyText, $match)) {
        return cleanText($match[1]);
    }

    return preg_match('/Birth\s+and\s+Death\s+Registration\s+Database/i', $bodyText)
        ? 'Birth and Death Registration Database'
        : null;
}

function toEnglishDigits(string $value): string
{
    return strtr($value, [
        '০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4',
        '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9',
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
        '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
    ]);
}

function monthToNumber(string $value): ?int
{
    static $months = null;

    if ($months === null) {
        $english = [
            'january' => 1, 'february' => 2, 'march' => 3, 'april' => 4,
            'may' => 5, 'june' => 6, 'july' => 7, 'august' => 8,
            'september' => 9, 'october' => 10, 'november' => 11, 'december' => 12,
            'jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4, 'jun' => 6,
            'jul' => 7, 'aug' => 8, 'sep' => 9, 'sept' => 9, 'oct' => 10, 'nov' => 11, 'dec' => 12,
        ];
        $bangla = [
            'জানুয়ারি' => 1, 'ফেব্রুয়ারি' => 2, 'মার্চ' => 3, 'এপ্রিল' => 4,
            'মে' => 5, 'জুন' => 6, 'জুলাই' => 7, 'আগস্ট' => 8,
            'সেপ্টেম্বর' => 9, 'অক্টোবর' => 10, 'নভেম্বর' => 11, 'ডিসেম্বর' => 12,
        ];

        $months = [];
        foreach ($english as $name => $number) {
            $months[$name] = $number;
        }
        foreach ($bangla as $name => $number) {
            $months[labelKey($name)] = $number;
        }
    }

    $key = labelKey($value);

    return $months[$key] ?? null;
}

function normalizeDate(?string $value): ?string
{
    if ($value === null) {
        return null;
    }

    $value = trim(cleanText(toEnglishDigits($value)));
    if ($value === '') {
        return null;
    }

    if (preg_match('#^(\d{4})-(\d{1,2})-(\d{1,2})$#', $value, $m)) {
        return formatDate((int) $m[1], (int) $m[2], (int) $m[3]);
    }

    if (preg_match('#^(\d{1,2})[\/\-.](\d{1,2})[\/\-.](\d{4})$#', $value, $m)) {
        return formatDate((int) $m[3], (int) $m[2], (int) $m[1]);
    }

    if (preg_match('#^(\d{1,2})\s+([^\s\d]+)\s+(\d{4})$#u', $value, $m)) {
        $month = monthToNumber($m[2]);
        if ($month !== null) {
            return formatDate((int) $m[3], $month, (int) $m[1]);
        }
    }

    if (preg_match('#^([^\s\d]+)\s+(\d{1,2}),?\s+(\d{4})$#u', $value, $m)) {
        $month = monthToNumber($m[1]);
        if ($month !== null) {
            return formatDate((int) $m[3], $month, (int) $m[2]);
        }
    }

    return null;
}

function formatDate(int $year, int $month, int $day): ?string
{
    if ($year < 1900 || $year > 2200 || !checkdate($month, $day, $year)) {
        return null;
    }

    return sprintf('%04d-%02d-%02d', $year, $month, $day);
}

function isValidIsoDate(string $value): bool
{
    if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m)) {
        return false;
    }

    return formatDate((int) $m[1], (int) $m[2], (int) $m[3]) !== null
        && (int) $m[1] <= (int) date('Y');
}

function hiddenInput(string $html, string $name): string
{
    preg_match_all('/<input\b([^>]*)>/i', $html, $matches, PREG_SET_ORDER);
    foreach ($matches as $match) {
        if (attribute($match[1], 'name') === $name) {
            return attribute($match[1], 'value');
        }
    }
    return '';
}

function imageSource(string $html, string $id): string
{
    preg_match_all('/<img\b([^>]*)>/i', $html, $matches, PREG_SET_ORDER);
    foreach ($matches as $match) {
        if (attribute($match[1], 'id') === $id) {
            return attribute($match[1], 'src');
        }
    }
    return '';
}

function attribute(string $attributes, string $name): string
{
    $quoted = preg_quote($name, '/');
    if (preg_match('/\b' . $quoted . '\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>]+))/i', $attributes, $match)) {
        return decodeEntities($match[1] ?? ($match[2] ?? ($match[3] ?? '')));
    }
    return '';
}

function cleanText(?string $value): string
{
    $value = (string) preg_replace(
        [
            '/<br\s*\/?>/i',
            '/<script\b[^>]*>.*?<\/script>/is',
            '/<style\b[^>]*>.*?<\/style>/is',
            '/<[^>]+>/s',
        ],
        ' ',
        (string) $value
    );

    return toValidUtf8(decodeEntities(trim((string) preg_replace('/\s+/u', ' ', $value))));
}

function stripTags(string $value): string
{
    return cleanText($value);
}

function decodeEntities(string $value): string
{
    return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function toValidUtf8(string $value): string
{
    if ($value === '' || preg_match('//u', $value) === 1) {
        return $value;
    }
    if (function_exists('iconv')) {
        $fixed = @iconv('UTF-8', 'UTF-8//IGNORE', $value);
        if (is_string($fixed)) {
            return $fixed;
        }
    }

    return (string) preg_replace('/[\x80-\xFF]/', '', $value);
}

function isCaptchaRejected(string $text): bool
{
    return str_contains($text, 'captcha is not valid')
        || str_contains($text, 'captcha is invalid')
        || str_contains($text, 'invalid captcha')
        || str_contains($text, 'captcha does not match')
        || (str_contains($text, 'captcha') && (str_contains($text, 'not valid') || str_contains($text, 'invalid')));
}

function hasRecordData($value): bool
{
    if ($value === null) {
        return false;
    }
    if (is_array($value)) {
        foreach ($value as $item) {
            if (hasRecordData($item)) {
                return true;
            }
        }
        return false;
    }

    return trim((string) $value) !== '';
}

function isDeveloperField(?string $value): bool
{
    return (bool) preg_match('/(?:developer|credit|owner|auth(?:or)?|telegram|channel|mehedisanix|cobrax_empire)/i', (string) $value);
}

function isSuccessStatus(int $status): bool
{
    return $status >= 200 && $status < 300;
}

function parseResponseHeaders(string $raw): array
{
    $headers = [];

    foreach (preg_split("/\r\n|\n|\r/", $raw) ?: [] as $line) {
        if (preg_match('#^HTTP/\d(?:\.\d)?\s+\d{3}#i', $line)) {
            $headers = ['set-cookie' => $headers['set-cookie'] ?? []];
            continue;
        }

        if (!str_contains($line, ':')) {
            continue;
        }

        list($name, $value) = explode(':', $line, 2);
        $name  = strtolower(trim($name));
        $value = trim($value);

        if ($name === '') {
            continue;
        }

        if ($name === 'set-cookie') {
            $headers['set-cookie'][] = $value;
        } else {
            $headers[$name] = $value;
        }
    }

    if (isset($headers['set-cookie']) && $headers['set-cookie'] === []) {
        unset($headers['set-cookie']);
    }

    return $headers;
}

function originOf(string $url): string
{
    $parts  = parse_url($url);
    $scheme = $parts['scheme'] ?? 'https';
    $host   = $parts['host'] ?? '';
    $port   = isset($parts['port']) ? ':' . $parts['port'] : '';

    return $scheme . '://' . $host . $port;
}

function absoluteUrl(string $url, string $base): string
{
    if (preg_match('#^https?://#i', $url)) {
        return $url;
    }
    if ($url === '') {
        return $base;
    }
    if ($url[0] !== '/') {
        return rtrim($base, '/') . '/' . ltrim($url, '/');
    }

    return rtrim(originOf($base), '/') . $url;
}

function env(string $name, string $default = ''): string
{
    $value = getenv($name);
    if ($value === false || trim((string) $value) === '') {
        $value = $_SERVER[$name] ?? ($_ENV[$name] ?? '');
    }

    $value = trim((string) $value);

    return $value !== '' ? $value : $default;
}

function cacheDirectory(): ?string
{
    static $dir = false;

    if ($dir !== false) {
        return $dir;
    }

    $candidates = [];
    if (env('CACHE_DIR') !== '') {
        $candidates[] = env('CACHE_DIR');
    }
    $candidates[] = sys_get_temp_dir();

    foreach ($candidates as $candidate) {
        if ($candidate !== '' && is_dir($candidate) && is_writable($candidate)) {
            $dir = rtrim($candidate, DIRECTORY_SEPARATOR);
            return $dir;
        }
    }

    $dir = null;

    return null;
}

function cacheTtl(): int
{
    $ttl = env('CACHE_TTL', (string) RESULT_CACHE_TTL_SECONDS);

    return is_numeric($ttl) ? (int) $ttl : RESULT_CACHE_TTL_SECONDS;
}

function cachePath(string $key): ?string
{
    $dir = cacheDirectory();

    return $dir === null
        ? null
        : $dir . DIRECTORY_SEPARATOR . 'birth_verifier_' . $key . '.json';
}

function readCache(string $key): ?array
{
    if (cacheTtl() <= 0) {
        return null;
    }

    $path = cachePath($key);
    if ($path === null || !is_file($path)) {
        return null;
    }

    $modified = (int) @filemtime($path);
    if ($modified + cacheTtl() < time()) {
        @unlink($path);
        return null;
    }

    $value = json_decode((string) @file_get_contents($path), true);

    return is_array($value) ? $value : null;
}

function writeCache(string $key, array $value): void
{
    if (cacheTtl() <= 0) {
        return;
    }

    $path = cachePath($key);
    if ($path === null) {
        return;
    }

    @file_put_contents(
        $path,
        (string) json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        LOCK_EX
    );
}

function sendJson(?array $payload, int $status, array $extraHeaders = []): void
{
    while (ob_get_level() > 0) {
        @ob_end_clean();
    }

    if (!headers_sent()) {
        http_response_code($status);
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, OPTIONS');
        header('Access-Control-Allow-Headers: content-type, x-api-key');
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        header('X-Api-Version: ' . API_VERSION);
        header('X-Powered-By-Name: BirthRecordAPI by @MehediSanix');
        foreach ($extraHeaders as $line) {
            header($line);
        }
    }

    if ($payload === null) {
        exit;
    }

    $body = array_merge($payload, ['developer_info' => DEVELOPER_INFO]);

    $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
    if ((string) ($_GET['pretty'] ?? '') === '1') {
        $flags |= JSON_PRETTY_PRINT;
    }

    $json = json_encode($body, $flags);
    if ($json === false && defined('JSON_INVALID_UTF8_SUBSTITUTE')) {
        $json = json_encode($body, $flags | JSON_INVALID_UTF8_SUBSTITUTE);
    }
    if ($json === false) {
        $json = (string) json_encode([
            'success'        => false,
            'error'          => 'Response could not be encoded as JSON.',
            'developer_info' => DEVELOPER_INFO,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    echo $json;
    exit;
}
?>
