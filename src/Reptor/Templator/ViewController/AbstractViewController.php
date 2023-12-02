<?php
/**
 * @author Rix Beck <rix@neologik.hu>, Sidorovich Nikolay <sidorovich21101986@mail.ru>
 */

namespace brix\Reptor\Templator\ViewController;

use brix\Reptor\PhpOffice\Coordinate;
use brix\Reptor\Templator\CellMergeMap;
use brix\Reptor\Templator\Context\ContextProvider;
use brix\Reptor\Templator\ExprObject\Interface\ExprObjectInterface;
use brix\Reptor\Templator\MergeManagerInterface;
use brix\Reptor\Templator\MergeMapManager;
use brix\Reptor\Templator\Unit;
use brix\Reptor\Templator\UnitManager;
use brix\Reptor\Templator\UnitTemplate;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\ColumnIterator;
use PhpOffice\PhpSpreadsheet\Worksheet\RowDimension;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class AbstractViewController.
 *
 * Responsible for control operations both on cell and unit level.
 *
 * @author Rix Beck <rix@neologik.hu>
 */
abstract class AbstractViewController implements ViewControllerInterface
{
    protected AllocationStack $allocationStack;
    protected ContextProvider $contextProvider;
    protected EventDispatcherInterface $eventDispatcher;
    /** @var int|null For debug reason */
    protected ?int $id = 0;
    protected MergeManagerInterface $mergeManager;
    protected Unit $unit;
    protected UnitManager $unitManager;
    protected Worksheet $worksheet;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        Spreadsheet $spreadsheet,
        UnitManager $unitManager,
        ContextProvider $contextProvider,
        AllocationStack $allocationStack,
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->unitManager = $unitManager;
        $this->worksheet = $spreadsheet->getActiveSheet();
        // $this->mergeManager = new MergeMapManager(new CellMergeMap($this->worksheet), $this->worksheet);

        $this->id = spl_object_id($this);
        $this->allocationStack = $allocationStack;
        $this->contextProvider = $contextProvider;
    }

    /**
     * After processing last template in unit, this method should be called.
     */
    public function finishUnit(Unit $unit): self
    {
        $unit->lock();
        if (!$unit->hasParent()) {
            $this->unitManager->unlinkUnit($unit);
        }

        return $this;
    }

    /**
     * Implement this method to handle NextDataRowEvent.
     * Under the event, you can apply any logic which has Unit as a subject.
     * Called by Unit's event listener on NextDataRowEvent.
     */
    public function nextDataRowEventHandler(Unit $unit, ...$args): void
    {
    }

    public function preserveUnitAllocation(): self
    {
        return $this;
    }

    /**
     * Before any cell based operation this method should be called.
     *
     * @throws Exception
     */
    public function setupUnit(): self
    {
        $renderContext = $this->contextProvider->getCellRenderContext();
        $this->unit = $renderContext->unit;
        $this->adjustUnitType($renderContext->value);

        return $this;
    }

    /**
     * Adjust type of unit based on types of templates in unit.
     * Each controller can have own logic to specify type of unit.
     * By default, the highest priority type will be used.
     * Called by renderCell() method.
     */
    protected function adjustUnitType(ExprObjectInterface $exprObject): void
    {
        if ($this->unit->isLocked()) {
            return;
        }

        if ($exprObject->getType()::PRIORITY >= $this->unit->getUnitType()::PRIORITY) {
            $this->unit->setUnitType(
                $exprObject->getType(),
                $exprObject,
                $this->contextProvider->getCellRenderContext()->unitTemplate,
            );
        }
    }

    public function applyUnitAttributes(Unit $unit): self
    {
        /** @var RowDimension $rowAttributes */
        $rowAttributes = $unit->getUnitTemplate()->getAttributes()['row'] ?? null;
        if ($rowAttributes) {
            $this->worksheet
                ->getRowDimension($this->contextProvider
                    ->getCellRenderContext()
                    ->cellAddress
                    ->rowId()
                )
                ->setRowHeight($rowAttributes->getRowHeight());
        }

        return $this;
    }

    /**
     * Prepares allocation for current unit. Pushes allocation callback to stack for later execution.
     * Execution usually takes place when unit has been rendered.
     */
    protected function prepareAllocation(
        string $colLeft,
        string $colRight,
        int $row,
        int $distance = 1,
        bool $keepMergedCells = true,
    ): void {
        /** @var UnitTemplate[] $unitContent */
        $unit = $this->unit;
        $this->allocationStack->push(
            function () use (
                $colLeft,
                $colRight,
                $row,
                $distance,
                $keepMergedCells,
                $unit
            ) {
                $this->unitManager->adjustTop(
                    $unit,
                    $distance,
                    Coordinate::columnIndexFromString(
                        $colRight
                    ) - Coordinate::columnIndexFromString($colLeft)
                );
                $this->shiftDown(
                    $colLeft,
                    $colRight,
                    $row,
                    $distance,
                    $keepMergedCells
                );
            }
        );
    }

    /**
     * @throws Exception
     */
    protected function shiftCellDown(
        string $col,
        int $row,
        int $distance = 1,
        int $bottom = 0,
        bool $keepMergedCells = true
    ): void {
        $bottom = $bottom ?: $this->worksheet->getHighestRow();

/*        $cellRange = $this->mergeManager->getCellMergeMap()->getCellRange(
            $this->worksheet->getCell($col.$row)
        );*/

        $colIterator = new ColumnIterator($this->worksheet, $col, $col);
        $cellIterator = $colIterator->current()->getCellIterator($row, $bottom);
        $cellIterator->setIterateOnlyExistingCells(true);
        $cellsToAlign = array_reverse(iterator_to_array($cellIterator), true);
        $cells = $this->worksheet->getCellCollection();
        $currentCell = $this->worksheet->getCell($col.$row);
        foreach ($cellsToAlign as $row => $cell) {
            if ($cell !== $currentCell) {
                $coordinate = $col.$row;
                $rowAttr = $this->worksheet->getRowDimension($row);
                $cells->delete($coordinate);
                $coordinate = $col.($row + $distance);
                $this->worksheet->getRowDimension($row + $distance)->setRowHeight($rowAttr->getRowHeight());
                $this->worksheet->addCell($coordinate, $cell);
            }
        }
        /*if ($cellRange && $keepMergedCells && $cell) {
            $this->mergeManager->getCellMergeMap()->updateCellRange($cell, $cellRange);
        }*/
    }

    /**
     * @throws Exception
     */
    protected function shiftDown(
        string $colLeft,
        string $colRight,
        int $row,
        int $distance = 1,
        bool $keepMergedCells = true,
        int $bottom = 0,
    ): void {
        $currentColumn = Coordinate::columnIndexFromString($colLeft);
        $lastColumn = Coordinate::columnIndexFromString($colRight);
        for ($column = $currentColumn; $column <= $lastColumn; ++$column) {
            $col = Coordinate::stringFromColumnIndex($column);
            $this->shiftCellDown($col, $row, $distance, $bottom, keepMergedCells: $keepMergedCells);
        }
    }
}
