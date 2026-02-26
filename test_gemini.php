<?php
require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;
use App\Service\GeminiAssistantService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/.env');

$client = HttpClient::create();
$params = new ParameterBag();
$service = new GeminiAssistantService($client, $params);

try {
    $result = $service->generateSkinDetails('AWP Dragon Lore', 'CS:GO');
    print_r($result);
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
