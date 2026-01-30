<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use brix\Reptor\Bootstrap;
use Symfony\Component\EventDispatcher\EventDispatcher;

$templateFile = __DIR__ . '/../reports/sample-1.xlsx';
$outputFile = sprintf(__DIR__ . '/../reports/sample-1-json-report.%s.xlsx', time());

$dsnJson = sprintf('file://%s', realpath(__DIR__ . '/../reports/sample.json'));

$reptor = new Bootstrap(new EventDispatcher());

$reptor->addProperties([
    'json_source' => '"' . $dsnJson . '"',
    'ds' => "{ 'json': DataSet(json_source) }",
    'activity' => "View(ds['json'].row())",
]);

$reptor->run($templateFile, $outputFile);

fwrite(STDOUT, $outputFile . PHP_EOL);
