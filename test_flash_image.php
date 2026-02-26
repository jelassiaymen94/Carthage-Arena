<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-image:generateContent?key=AIzaSyC4M2G7mA2EHrEl75MFSid1ksuxaxjxtqY");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
$payload = json_encode([
    'contents' => [
        [
            'parts' => [
                ['text' => 'An Epic Dragon Lore skin from CSGO']
            ]
        ]
    ]
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
$headers = array();
$headers[] = 'Content-Type: application/json';
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$result = curl_exec($ch);
if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
}
curl_close($ch);
$data = json_decode($result, true);
if (isset($data['candidates'][0]['content']['parts'][0])) {
    $part = $data['candidates'][0]['content']['parts'][0];
    if (isset($part['inlineData'])) {
        echo "Returns inlineData: " . $part['inlineData']['mimeType'] . " (length: " . strlen($part['inlineData']['data']) . ")\n";
    } else {
        echo print_r($part, true);
    }
} else {
    echo $result;
}
