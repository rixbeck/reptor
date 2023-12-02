<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Templator;

use PhpOffice\PhpSpreadsheet\Cell\Cell;

interface MergeManagerInterface
{

    public function moveCellRange(Cell|string $cellFrom, Cell|string $cellTo): ?string;

    public function copyCellRange(Cell|string $cellFrom, Cell|string $cellTo): ?string;

    /**
     * @return string[]
     */
    public function getRanges(): array;

    public function getCellMergeMap(): CellMergeMap;

    public function setMergeCells(): void;
}
