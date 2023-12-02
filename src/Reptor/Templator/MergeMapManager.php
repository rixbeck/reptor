<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Templator;

use brix\Reptor\PhpOffice\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MergeMapManager implements MergeManagerInterface
{
    public function __construct(protected CellMergeMap $cellMergeMap, protected ?Worksheet $worksheet = null)
    {
    }

    /**
     * @throws Exception
     */
    public function setMergeCells(): void
    {
        foreach ($this->getRanges() as $range) {
            $this->worksheet->mergeCells($range);
        }
    }

    /**
     * @return string[]
     */
    public function getRanges(): array
    {
        return $this->cellMergeMap->getRanges();
    }

    /**
     * @throws Exception
     */
    public function copyCellRange(Cell|string $cellFrom, Cell|string $cellTo): ?string
    {
        $currentRange = $this->cellMergeMap->getCellRange($cellFrom);
        $range = $this->moveCellRange($cellFrom, $cellTo);
        if ($range) {
            [$coordFrom, $cellFrom] = is_string($cellFrom) ? [
                $cellFrom,
                $this->worksheet->getCell($cellFrom),
            ] : [$cellFrom->getCoordinate(), $cellFrom];

            $this->cellMergeMap->updateCellRange($cellFrom, $currentRange);
        }

        return $range;
    }

    /**
     * @throws Exception
     */
    public function moveCellRange(Cell|string $cellFrom, Cell|string $cellTo): ?string
    {
        [$coordFrom, $cellFrom] = is_string($cellFrom) ?
            [$cellFrom, $this->worksheet->getCell($cellFrom)] :
            [$cellFrom->getCoordinate(), $cellFrom];

        [$coordTo, $cellTo] = is_string($cellTo) ?
            [$cellTo, $this->worksheet->getCell($cellTo)] :
            [$cellTo->getCoordinate(), $cellTo];

        $range = $this->cellMergeMap->getCellRange($cellFrom);
        if (!$range) {
            return null;
        }

        $diff = Coordinate::calcDistance($coordFrom, $coordTo);
        $this->cellMergeMap->deleteCellRange($cellFrom);

        return $this->cellMergeMap->updateCellRange($cellTo, Coordinate::addRangeDistance($range, $diff));
    }

    public function getCellMergeMap(): CellMergeMap
    {
        return $this->cellMergeMap;
    }
}
