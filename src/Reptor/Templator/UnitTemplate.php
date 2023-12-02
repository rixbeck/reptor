<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Templator;

use brix\Reptor\PhpOffice\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\CellAddress;

class UnitTemplate
{
    protected CellAddress $cellAddress;

    public function __construct(
        protected string $template,
        protected Cell $cell,
        protected int $xfindex,
        protected array $offsets,
        protected array $attributes = [],
    ) {
        $this->cellAddress = new CellAddress($this->cell->getCoordinate());
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getCell(): Cell
    {
        return $this->cell;
    }

    public function getCellAddress(?Unit $baseUnit = null): CellAddress
    {
        return $baseUnit ? $this->cellAddress = new CellAddress(
            Coordinate::cellCoordinate(
                $baseUnit->getLeft() + $this->getOffsetX(),
                $baseUnit->getTop() + $this->getOffsetY()
            )
        ) : $this->cellAddress;
    }

    public function getOffsetX(): int
    {
        return $this->offsets[0];
    }

    public function getOffsetY(): int
    {
        return $this->offsets[1];
    }

    public function getOffsets(): array
    {
        return $this->offsets;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function getXfindex(): int
    {
        return $this->xfindex;
    }
}
