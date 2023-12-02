<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Templator;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Worksheet\RowIterator;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CellMergeMap
{
    protected \SplObjectStorage $rangeMap;
    protected array $ranges = [];

    public function __construct(protected ?Worksheet $worksheet = null)
    {
        $this->rangeMap = new \SplObjectStorage();
        if ($worksheet) {
            $this->init($worksheet);
        }
    }

    protected function init(Worksheet $worksheet): void
    {
        foreach ($rowIterator = new RowIterator($worksheet) as $rowIndex => $row) {
            foreach ($row->getCellIterator() as $cell) {
                $this->updateCellRange($cell, $cell->getMergeRange() ?: null);
            }
        }
    }

    public function updateCellRange(Cell $cell, string $range = null): ?string
    {
        $isMergedCell = $this->hasCellRange($cell);
        // uf range is null, then remove the cell from the range
        if ($isMergedCell) {
            if (!$range) {
                $range = $this->deleteCellRange($cell);
            } elseif ($range !== $this->getCellRange($cell)) {
                $this->moveCellRange($cell, $range);
            }
        } elseif ($range) {
            $this->addCellRange($cell, $range);
        }

        return $range;
    }

    public function hasCellRange(Cell $cell): bool
    {
        return $this->rangeMap->offsetExists($cell);
    }

    public function getWorksheet(): ?Worksheet
    {
        return $this->worksheet;
    }

    public function getCellRange(Cell $cell): ?string
    {
        return $this->rangeMap->offsetExists($cell) ? $this->rangeMap->offsetGet($cell) : null;
    }

    /**
     * @return string[]
     */
    public function getRanges(): array
    {
        return array_keys(array_filter($this->ranges, fn ($elm) => !empty($elm)));
    }

    public function deleteCellRange(Cell $cell): mixed
    {
        $range = $this->rangeMap->offsetGet($cell);
        unset($this->ranges[$range][spl_object_id($cell)]);
        $this->rangeMap->offsetUnset($cell);

        return $range;
    }

    public function moveCellRange(Cell $cell, string $range): void
    {
        $this->deleteCellRange($cell);
        $this->addCellRange($cell, $range);
    }

    public function addCellRange(Cell $cell, string $range): void
    {
        $this->ranges[$range][spl_object_id($cell)] = $cell;
        $this->rangeMap[$cell] = $range;
    }
}
