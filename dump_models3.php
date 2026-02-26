<?php
$c = file_get_contents('https://generativelanguage.googleapis.com/v1beta/models?key=AIzaSyC4M2G7mA2EHrEl75MFSid1ksuxaxjxtqY');
$d = json_decode($c, true);
$found = [];
foreach($d['models'] as $m) {
    if (strpos($m['name'], 'gemini-2.5-flash') !== false || strpos($m['name'], 'image') !== false) {
        $found[] = ['name' => $m['name'], 'methods' => $m['supportedGenerationMethods']];
    }
}
file_put_contents('models_list.json', json_encode($found, JSON_PRETTY_PRINT));
