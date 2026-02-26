<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-image:generateContent?key=AIzaSyC4M2G7mA2EHrEl75MFSid1ksuxaxjxtqY");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
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
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$result = curl_exec($ch);
curl_close($ch);
$d = json_decode($result, true);
if (isset($d['candidates'][0]['content']['parts'][0]['inlineData'])) {
    echo "YES BASE64: " . strlen($d['candidates'][0]['content']['parts'][0]['inlineData']['data']);
} else {
    print_r($d);
}
