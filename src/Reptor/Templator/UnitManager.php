<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Templator;

use brix\Reptor\PhpOffice\Coordinate;
use brix\Reptor\Templator\Event\PrepareSpreadsheetEvent;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\NamedRange;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Psr\EventDispatcher\EventDispatcherInterface;

class UnitManager extends \SplObjectStorage
{
    /**
     * @var \brix\Reptor\Templator\Unit[]
     */
    protected array $cellsAvailable = [];

    public function __construct(
        protected EventDispatcherInterface $eventDispatcher,
        protected array $units = []
    ) {
        $this->eventDispatcher->addListener(
            PrepareSpreadsheetEvent::BEFORE_RENDER,
            fn(PrepareSpreadsheetEvent $event) => $this->prepareUnits($event, $units)
        );
    }

    /**
     * Adjust top position of the given unit and units beneath it, including children too.
     */
    public function adjustTop(Unit $unit, int $rowsAdded, int $colsAdded): void
    {
        $unit->adjustTop($rowsAdded);
        while ($next = $unit->getNext()) {
            if ($unit->getLeft() + $colsAdded < $next->getLeft()) {
                $unit = $next;
                continue;
            }
            $next->adjustTop($unit->getHeight());
            $unit = $next;
        }
    }

    /**
     * Find the smallest defined range that contains the given coordinate.
     */
    public function findContainer(array|string $coordinate): ?Unit
    {
        $area = 65536000;
        $unitFound = null;

        foreach ($this as $unit) {
            if ($unit->hasCoordinate($coordinate)) {
                if ($unit->area() < $area) {
                    $area = $unit->area();
                    $unitFound = $unit;
                }
            }
        }

        return $unitFound;
    }

    /**
     * Find the largest defined range that contains the given coordinate.
     */
    public function findRootContainer(array|string $coordinate): ?Unit
    {
        foreach ($this as $unit) {
            if ($unit->hasCoordinate($coordinate)) {
                return $unit->getRoot();
            }
        }

        return null;
    }

    public function findUnitTemplate(
        Unit $unit,
        string $coordinate
    ): ?array {
        $templates = $this[$unit] ?? null;
        if ($templates) {
            /** @var UnitTemplate $template */
            foreach ($templates as $templateId => $template) {
                $templateCellAddress = Coordinate::stringFromColumnIndex(
                        $unit->getLeft() + $template->getOffsetX()
                    ) . ($unit->getTop() + $template->getOffsetY());
                if ($templateCellAddress === $coordinate) {
                    return [$templateId, $template];
                }
            }
        }

        return null;
    }

    public function getFirstUnitTemplate(Unit $unit): ?UnitTemplate
    {
        $templates = $this[$unit] ?? null;

        return $templates ? reset($templates) : null;
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this);
    }

    /**
     * Fetches the last defined template entry for the given unit.
     */
    public function getLastUnitTemplate(Unit $unit): ?UnitTemplate
    {
        $templates = $this[$unit] ?? null;

        return $templates ? end($templates) : null;
    }

    public function getUnitTemplates(
        Unit $unit
    ): array {
        return $this[$unit] ?? [];
    }

    public function hasIterableUnitType(): bool
    {
        foreach ($this as $unit) {
            if ($unit->isIterableUnitType()) {
                return true;
            }
        }

        return false;
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    public function isEmptyUnit(Unit $unit): bool
    {
        return empty($this[$unit]);
    }

    public function isFirstCellInUnit(Unit $unit, string $coordinate): bool
    {
        if ($unit->getWidth() === 1 && $unit->getHeight() === 1) {
            return true;
        }
        $firstCell = $this->getFirstUnitTemplate($unit);

        return $firstCell && Coordinate::cellCoordinate(
                $firstCell->getOffsetX() + $unit->getLeft(),
                $firstCell->getOffsetY() + $unit->getTop()
            ) === $coordinate;
    }

    public function isLastCellInUnit(Unit $unit, string $coordinate): bool
    {
        if ($unit->getWidth() === 1 && $unit->getHeight() === 1) {
            return true;
        }
        $lastCell = $this->getLastUnitTemplate($unit);

        return $lastCell && Coordinate::cellCoordinate(
                $lastCell->getOffsetX() + $unit->getLeft(),
                $lastCell->getOffsetY() + $unit->getTop()
            ) === $coordinate;
    }

    public function unlinkUnit(Unit $unit): void
    {
        $unit->getParent()?->setChild($unit->getChild());
        $unit->getChild()?->setParent($unit->getParent());
        $unit->getPrev()?->setNext($unit->getNext());
        $unit->getNext()?->setPrev($unit->getPrev());
        $this->detach($unit);
    }

    public function unlinkUnitTemplate(Unit $unit, int|string $templateId): void
    {
        $templates = $this[$unit] ?? null;
        if ($templates) {
            unset($templates[$templateId]);
            $this[$unit] = $templates;
        }
    }

    /**
     * Build parent-child relationship between units.
     */
    protected function establishRelationship(): void
    {
        // Clone the unitTemplates to avoid side effects
        $units = iterator_to_array(clone $this);

        // Sort units based on top and left positions
        usort($units, function ($a, $b) {
            if ($a->getTop() === $b->getTop()) {
                return $a->getLeft() <=> $b->getLeft();
            }

            return $a->getTop() <=> $b->getTop();
        });

        // Establish parent-child relationships
        foreach ($units as $unit) {
            foreach ($units as $relative) {
                if ($unit === $relative) {
                    continue;
                }

                if ($relative->isContains($unit)) {
                    $unit->setParent($relative);
                    $relative->setChild($unit);
                    break;
                }
            }
        }

        // Establish next-prev relationships
        for ($i = 0; $i < count($units); ++$i) {
            if (isset($units[$i - 1])) {
                $units[$i]->setPrev($units[$i - 1]);
            }
            if (isset($units[$i + 1])) {
                $units[$i]->setNext($units[$i + 1]);
            }
        }
    }

    protected function noteCellVisited(string $coordinate): void
    {
        unset($this->cellsAvailable[$coordinate]);
    }

    /**
     * Setting up units defined in the spreadsheet by named ranges 'unit_*'.
     */
    protected function prepareSheetDefinedUnits(
        Spreadsheet $spreadsheet
    ): void {
        foreach ($spreadsheet->getDefinedNames() as $definedName) {
            $name = $definedName->getName();
            if (str_starts_with($name, 'unit_')) {
                $range = $definedName->getRange();
                $range = Coordinate::convertRangeFormat($range);
                $unit = new Unit($this->eventDispatcher,  $name, $range);
                $this->attach($unit, []);
            }
        }
    }

    /**
     * Setting up units defined in the spreadsheet.
     */
    protected function prepareUnitTemplates(
        Spreadsheet $spreadsheet
    ): void {
        // to keep iiterator intact
        foreach (clone $this as $unit) {
            $this->readTemplatesInUnits($spreadsheet, $unit);
        }
        $this->collectSingleCellUnitsTemplates($spreadsheet);
    }

    /**
     * Main method to prepare units.
     */
    protected function prepareUnits(
        PrepareSpreadsheetEvent $event,
        array $userDef
    ): void {
        $unitProperties = $event->context['units'] ?? [];
        $this->cellsAvailable = array_flip(
            $event->spreadsheet->getActiveSheet()->getCellCollection()->getCoordinates()
        );
        $this->prepareUserDefinedUnits($event->spreadsheet, [...$unitProperties, ...$userDef]);
        $this->prepareSheetDefinedUnits($event->spreadsheet);
        $this->prepareUnitTemplates($event->spreadsheet);
        $this->establishRelationship();
    }

    /**
     * Pick up templates from the given unit.
     *
     * @throws Exception
     */
    protected function readTemplatesInUnits(
        Spreadsheet $spreadsheet,
        Unit $currentUnit
    ): void {
        $worksheet = $spreadsheet->getActiveSheet();
        if ($currentUnit->getRight() > Coordinate::columnIndexFromString($worksheet->getHighestColumn())
            || $currentUnit->getBottom() > $worksheet->getHighestRow()) {
            return;
        }

        $rowIterator = $worksheet->getRowIterator($currentUnit->getTop(), $currentUnit->getBottom());
        foreach ($rowIterator as $row) {
            $rowDimensions = $worksheet->getRowDimension($row->getRowIndex());
            foreach (
                $row->getCellIterator(
                    $currentUnit->getFrom()->columnName(),
                    $currentUnit->getTo()->columnName()
                ) as $cell
            ) {
                $value = $cell->getValue();
                if ($value === null) {
                    continue;
                }
                $xfIndex = $cell->getXfIndex();
                $coordinate = $cell->getCoordinate();
                $this->noteCellVisited($coordinate);
                if ($xfIndex !== 0) {
                    $smallestUnit = $this->findContainer($coordinate);
                    if ($smallestUnit !== $currentUnit) {
                        continue;
                    }
                    $offsets = $this->getOffsets($smallestUnit, $coordinate);
                    $templates = $this[$smallestUnit];
                    $templates[] = new UnitTemplate(
                        $value,
                        clone $cell,
                        $xfIndex,
                        $offsets,
                        ['row' => $rowDimensions]
                    );
                    $this[$smallestUnit] = $templates;
                }
            }
        }
    }

    private function collectSingleCellUnitsTemplates(Spreadsheet $spreadsheet): void
    {
        $worksheet = $spreadsheet->getActiveSheet();
        foreach ($this->cellsAvailable as $coordinate => $ord) {
            $cell = $worksheet->getCell($coordinate);
            $value = $cell->getValue();
            if ($value !== null && Tokenizer::isToken($value)) {
                $unit = new Unit(
                    $this->eventDispatcher,
                    'unit_cell_' . $coordinate,
                    $coordinate . ':' . $coordinate,
                );
                $column = substr($coordinate, 0, 1);
                $row = (int)substr($coordinate, 1, 1);
                $rowDimensions = $worksheet->getRowDimension($row);
                $this->attach(
                    $unit,
                    [
                        new UnitTemplate(
                            $cell->getValue(),
                            clone $cell,
                            $cell->getXfIndex(),
                            [0, 0],
                            ['row' => $rowDimensions]
                        ),
                    ]
                );
            }
            $this->noteCellVisited($coordinate);
        }
    }

    private function getOffsets(
        Unit $unit,
        mixed $coordinate
    ): array {
        [$x, $y] = Coordinate::indexesFromString($coordinate);

        return [$x - $unit->getLeft(), $y - $unit->getTop()];
    }

    /**
     * User defined ranges are directly defined ranges in arrays.
     *
     * @throws Exception
     */
    private function prepareUserDefinedUnits(
        Spreadsheet $spreadsheet,
        array $userDef
    ): void {
        $sheet = $spreadsheet->getActiveSheet();
        foreach ($userDef as $ord => $range) {
            $id = str_starts_with($ord, 'unit_') ? $ord : 'unit_usr_' . $ord;
            $spreadsheet->addNamedRange(new NamedRange($id, $sheet, $range));
        }
    }
}
