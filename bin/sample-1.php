<?php
/**
 * @author Rix Beck <rix@neologik.hu>
 */

require __DIR__ . '/../vendor/autoload.php';

use brix\Reptor\Bootstrap;
use Symfony\Component\EventDispatcher\EventDispatcher;

$templateFile = '../reports/sample-1.xlsx';
$fileName = '../reports/sample-1-report.xlsx';
$dsnJson = sprintf('file://%s/../reports/sample.json', __DIR__ );
$dsnSql = sprintf('file://%s/../reports/sample.db', __DIR__ );
$query = include __DIR__ . '/../config/sample-1.query.php';

$reptorBootstrap = new Bootstrap($eventDispatcher = new EventDispatcher());


$reptorBootstrap->addProperties([
    'params' => [
        'partner' => ['Andrew and Co. Ltd.', 'Alessio Stephard Ltd.'],
    ],
    'json_source' => '"'.$dsnJson.'"',
    'sql_source' => '"'.$dsnSql.'"',
    'query' => $query,
    'ds' => "{
        'json': DataSet(json_source),
        'sql': DataSet(sql_source, query)
     }",
    'activity' => "View(ds['json'].row())",
]);

$reptorBootstrap->run($templateFile, $fileName);
