<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor;

use brix\Reptor\Adaptor\DataSource\ArrayReader;
use brix\Reptor\Adaptor\DataSource\ConnectorProvider;
use brix\Reptor\Adaptor\DataSource\CSVReader;
use brix\Reptor\Adaptor\DataSource\JSONReader;
use brix\Reptor\Adaptor\DataSource\PDOReader;
use brix\Reptor\ExpressionLanguage\ExpressionLanguage;
use brix\Reptor\ExpressionLanguage\Extension\AggregateExtension;
use brix\Reptor\ExpressionLanguage\Extension\CoreExtension;
use brix\Reptor\ExpressionLanguage\Extension\DataExtension;
use brix\Reptor\ExpressionLanguage\Extension\PhpExtension;
use brix\Reptor\ExpressionLanguage\Extension\SpreadsheetExtension;
use brix\Reptor\ExpressionLanguage\Extension\UtilityExtension;
use brix\Reptor\PhpOffice\SpreadsheetProvider;
use brix\Reptor\Templator\Context\CellRenderContext;
use brix\Reptor\Templator\Context\ContextProvider;
use brix\Reptor\Templator\ExprObject\Aggregator\AggregatorFactory;
use brix\Reptor\Templator\Templator;
use brix\Reptor\Templator\UnitManager;
use brix\Reptor\Templator\ViewController\ViewControllerFactory;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Bootstrap
{
    /** @var mixed[] */
    protected array $additionalProperties = [];

    public function __construct(
        protected EventDispatcherInterface $eventDispatcher,
        protected ?Templator $templator = null,
        protected ?ExpressionLanguage $expressionLanguage = null,
        protected array $properties = [],
        protected array $phpMethods = [
            'date',
            'microtime',
            'array_reverse',
            'strval',
            'floatval',
            'intval',
            'boolval',
            'implode',
            'explode',
            'count',
            'filter_val',
        ],
    ) {
    }

    public function addProperties(array $properties): array
    {
        return $this->additionalProperties = [...$this->additionalProperties, ...$properties];
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function run(string $templateFile, string $outputFile): void
    {
        $this->initialize($templateFile);
        $this->templator->__invoke($templateFile, $outputFile, $this->properties);
    }

    protected function initialize(string $templateFile): void
    {
        $contextProvider = new ContextProvider(new CellRenderContext());
        $spreadsheetProvider = new SpreadsheetProvider();
        $spreadsheetProvider->loadFile($templateFile);

        if ($this->expressionLanguage === null) {
            $this->expressionLanguage = new ExpressionLanguage(
                new ArrayAdapter(storeSerialized: false)
            );
        }

        if ($this->templator === null) {
            $unitManager = new UnitManager($this->eventDispatcher, []);
            $this->templator = new Templator(
                $spreadsheetProvider,
                $this->eventDispatcher,
                $this->expressionLanguage,
                $contextProvider,
                $unitManager,
                new ArrayAdapter(storeSerialized: false),
            );

            $viewControllerFactory = new ViewControllerFactory(
                $this->eventDispatcher,
                $spreadsheetProvider,
                $unitManager,
                $contextProvider,
            );
            $aggregateFactory = new AggregatorFactory(
                $viewControllerFactory,
                $this->eventDispatcher,
                $contextProvider,
            );

            $this->expressionLanguage->registerProvider(
                new CoreExtension(
                    $this->eventDispatcher,
                    $viewControllerFactory,
                    $aggregateFactory,
                )
            );
            $this->expressionLanguage->registerProvider(
                new PhpExtension($this->phpMethods),
            );
            $this->expressionLanguage->registerProvider(
                new DataExtension(
                    new ConnectorProvider([
                        'pdo' => PDOReader::class,
                        'csv' => CSVReader::class,
                        'json' => JSONReader::class,
                        'array' => ArrayReader::class,
                    ]), $contextProvider
                )
            );
            $this->expressionLanguage->registerProvider(
                new AggregateExtension(
                    $this->eventDispatcher,
                    $aggregateFactory,
                )
            );
            $this->expressionLanguage->registerProvider(
                new UtilityExtension(
                    $this->eventDispatcher,
                    $viewControllerFactory,
                )
            );
            $this->expressionLanguage->registerProvider(
                new SpreadsheetExtension(
                    $spreadsheetProvider,
                    $viewControllerFactory,
                    $this->eventDispatcher,
                    $contextProvider,
                )
            );

            $aggregateFactory->configureExpressionLanguage($this->expressionLanguage);
        }

        $this->setDefaultProperties($this->properties);
        $this->expressionLanguage->evaluateCollection($this->additionalProperties, $this->properties);
        $contextProvider->setExpressionContext($this->properties);
    }

    /**
     * @param mixed[] $constants evaluated objects to be used as properties extending or overriding the default properties
     */
    protected function setDefaultProperties(array $constants): void
    {
        $defaultProperties = [
            'generator' => new class() {
                public string $name = 'Reptor';
                public string $version = '1.0.0';
            },
            'request' => [], // request parameters if http request
            'units' => [],
            'req' => [],     // alias
            'r' => [],      // alias
            'source' => '', // data source URI
            'ds' => [], // datasets
            'document' => $this->templator->getSpreadsheet(), // excel document
            'doc' => $this->templator->getSpreadsheet(),     // alias
            'spreadsheet' => $this->templator->getSpreadsheet(), // alias
        ];

        $this->properties = array_merge($defaultProperties, $constants);
    }
}
