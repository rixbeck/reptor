<?php
/**
 * @author Rix Beck <rix@neologik.hu>
 */

namespace brix\Reptor\Templator;

use brix\Reptor\ExpressionLanguage\ExpressionLanguage;
use brix\Reptor\PhpOffice\SpreadsheetProvider;
use brix\Reptor\Templator\Context\ContextProvider;
use brix\Reptor\Templator\Event\BeforeCellValueSetEvent;
use brix\Reptor\Templator\Event\NextRowEvent;
use brix\Reptor\Templator\Event\PrepareSpreadsheetEvent;
use brix\Reptor\Templator\ExprObject\Interface\ExprObjectInterface;
use PhpOffice\PhpSpreadsheet\Calculation\Calculation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use PhpOffice\PhpSpreadsheet\Writer\IWriter;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class Templator
{
    /**
     * Possible execution modes. Interpreter based rendering is supported only in this version.
     *
     * @var string BUILD|PARSE|RENDER
     */
    public const BUILD = 'build';
    public const PARSE = 'parse';
    public const RENDER = 'render';

    protected ContextProvider $contextProvider;
    protected EventDispatcherInterface $eventDispatcher;
    protected MergeMapManager $mergeManager;

    protected UnitManager $unitManager;
    protected Worksheet $worksheet;
    protected Spreadsheet $spreadsheet;
    protected ExpressionLanguage $expressionLanguage;
    protected SpreadsheetProvider $spreadsheetProvider;

    public function __construct(
        SpreadsheetProvider $spreadsheetProvider,
        EventDispatcherInterface $eventDispatcher,
        ExpressionLanguage $expressionLanguage,
        ContextProvider $contextProvider,
        UnitManager $unitManager,
        CacheItemPoolInterface $cache = null
    ) {
        $this->contextProvider = $contextProvider;
        $this->expressionLanguage = $expressionLanguage;
        $this->eventDispatcher = $eventDispatcher;
        $this->unitManager = $unitManager;
        $this->spreadsheetProvider = $spreadsheetProvider;
    }

    /**
     * @param string $inputFile
     * @param mixed[] $context
     *
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function __invoke(
        string $inputFile,
        string $outputFile,
        array &$context,
        string $mode = self::RENDER,
    ): void {
        $this->spreadsheet = $this->spreadsheetProvider->loadFile($inputFile)->getSpreadsheet();
        $this->worksheet = $this->spreadsheet->getActiveSheet();

        $this->eventDispatcher->dispatch(
            new PrepareSpreadsheetEvent($this->spreadsheet, $context),
            PrepareSpreadsheetEvent::BEFORE_RENDER
        );

        $this->$mode($context);

        $this->eventDispatcher->dispatch(
            new PrepareSpreadsheetEvent($this->spreadsheet, $context),
            PrepareSpreadsheetEvent::AFTER_RENDER
        );

        if ($mode !== 'parse') {
            $this->writeSpreadsheet($outputFile);
        }
    }

    public function findPropertyValue(object $objectInContext): ?string
    {
        return $this->expressionLanguage->findPropertyObjectValue($objectInContext);
    }

    /**
     * @return \Traversable<mixed>
     */
    public function getCustomProperties(): \Traversable
    {
        $properties = $this->spreadsheet->getProperties();
        foreach ($properties->getCustomProperties() as $property) {
            yield $property => $properties->getCustomPropertyValue($property);
        }
    }

    public function getMergeManager(): MergeMapManager
    {
        return $this->mergeManager;
    }

    public function getSpreadsheet(): Spreadsheet
    {
        return $this->spreadsheetProvider->getSpreadsheet();
    }

    public function render(array &$context): Worksheet
    {
        $cellRenderContext = $this->contextProvider->getCellRenderContext();
        // $context['_context'] = $this->cellRenderContext;
        while (!$this->unitManager->isEmpty() && $this->unitManager->hasIterableUnitType()) {
            $this->unitManager->rewind();
            $unitIterator = clone $this->unitManager;
            /* @var Unit $unit */
            while ($unitIterator->valid()) {
                $unit = $unitIterator->current();
                if ($unit->isHidden()) {
                    $unitIterator->next();
                    continue;
                }

                /** @var UnitTemplate[] $templates */
                $templates = $unitIterator->getInfo();
                foreach ($templates as $templateId => $template) {
                    $cellValue = Tokenizer::tokenToExpression($template->getTemplate());
                    $cellRenderContext->cellAddress = $template->getCellAddress($unit);
                    $cellRenderContext->cell = $this->worksheet->getCell(
                        $cellRenderContext->cellAddress->cellAddress()
                    );
                    $cellRenderContext->unit = $unit;
                    $cellRenderContext->templateId = $templateId;
                    $cellRenderContext->unitTemplate = $template;
                    $this->contextProvider->setCellRenderContext($cellRenderContext);
                    /** @var ExprObjectInterface $value */
                    $value = $this->expressionLanguage->evaluate($cellValue, $context);

                    if ($value !== null && !is_scalar($value)) {
                        // echo sprintf("%s:%s: %s=%s\n", $rowIndex, $colIndex, $cellValue, $value);
                        $cellRenderContext->value = $value;
                        $this->eventDispatcher->dispatch(
                            new BeforeCellValueSetEvent($cellRenderContext),
                            BeforeCellValueSetEvent::class
                        );

                        $value
                            ->exprObject()
                            ->getViewController()
                            ->setupUnit();
                    }

                    if ($value !== null) {
                        $value = (string)$value;
                        $cell = $this->worksheet->getCell($cellRenderContext->cellAddress->cellAddress());
                        $cell->setValue($value);
                        $cell->setXfIndex($template->getXfindex());
                    }
                }
                $unit->getUnitModel()->getViewController()
                    ->preserveUnitAllocation()
                    ->applyUnitAttributes($unit)
                    ->finishUnit($unit);
                $unitIterator->next();
            }
            $this->eventDispatcher->dispatch(
                $nextRowEvent = new NextRowEvent($cellRenderContext),
                NextRowEvent::class
            );
        }

        return $this->worksheet;
    }

    /**
     * @throws Exception
     */
    protected static function getWriter(Spreadsheet $spreadsheet): IWriter
    {
        return IOFactory::createWriter($spreadsheet, 'Xlsx');
    }

    /**
     * @param string $templateFile Path to *.xlsx template file
     */
    protected static function readSpreadsheet(string $templateFile): Spreadsheet
    {
        return IOFactory::load($templateFile);
    }

    /**
     * @throws Exception
     */
    protected function writeSpreadsheet(string $outputFile): void
    {
        $writer = static::getWriter($this->spreadsheet);
        Calculation::getInstance($this->spreadsheet)->clearCalculationCache();

        $writer->save($outputFile);
    }
}
