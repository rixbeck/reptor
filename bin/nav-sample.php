<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use brix\Reptor\Bootstrap;
use Symfony\Component\EventDispatcher\EventDispatcher;

$templateFile = __DIR__ . '/../reports/nav_template.xlsx';
$outputFile = sprintf(__DIR__ . '/../reports/nav-report.%s.xlsx', time());

$payload = json_decode((string)file_get_contents(__DIR__ . '/../reports/data.json'), true, 512, JSON_THROW_ON_ERROR);
$navData = $payload['nav'] ?? [];

$reptor = new Bootstrap(new EventDispatcher());

$reptor->addProperties([
    'nav_data' => $navData,
    'nav' => "View(DataSet('array://nav_data').row())",
]);

$reptor->run($templateFile, $outputFile);

fwrite(STDOUT, $outputFile . PHP_EOL);
