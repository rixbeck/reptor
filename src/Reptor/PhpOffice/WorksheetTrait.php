<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\PhpOffice;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

trait WorksheetTrait
{

    public function addCell(string $coordinate, Cell $cell): Cell
    {
        [$column, $row, $columnString] = Coordinate::indexesFromString($coordinate);
        $this->cellCollection->add($coordinate, $cell);

        // Coordinates
        if ($column > $this->cachedHighestColumn) {
            $this->cachedHighestColumn = $column;
        }
        if ($row > $this->cachedHighestRow) {
            $this->cachedHighestRow = $row;
        }

        // Cell needs appropriate xfIndex from dimensions records
        //    but don't create dimension records if they don't already exist
        $rowDimension = $this->rowDimensions[$row] ?? null;
        $columnDimension = $this->columnDimensions[$columnString] ?? null;

        if ($rowDimension !== null) {
            $rowXf = (int) $rowDimension->getXfIndex();
            if ($rowXf > 0) {
                // then there is a row dimension with explicit style, assign it to the cell
                $cell->setXfIndex($rowXf);
            }
        } elseif ($columnDimension !== null) {
            $colXf = (int) $columnDimension->getXfIndex();
            if ($colXf > 0) {
                // then there is a column dimension, assign it to the cell
                $cell->setXfIndex($colXf);
            }
        }

        return $cell;
    }
}
