<?php
$c = file_get_contents('https://generativelanguage.googleapis.com/v1beta/models?key=AIzaSyC4M2G7mA2EHrEl75MFSid1ksuxaxjxtqY');
$d = json_decode($c, true);
foreach($d['models'] as $m) {
    if (strpos($m['name'], 'image') !== false || strpos($m['name'], 'flash') !== false) {
        echo $m['name'] . "\n";
    }
}
