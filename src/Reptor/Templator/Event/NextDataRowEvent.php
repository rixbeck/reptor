<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Templator\Event;

use brix\Reptor\Templator\Context\CellRenderContext;
use brix\Reptor\Templator\ExprObject\DataRow;

class NextDataRowEvent
{
    public function __construct(protected CellRenderContext $cellRenderContext, protected DataRow $dataRow)
    {
    }

    public function getCellRenderContext(): CellRenderContext
    {
        return $this->cellRenderContext;
    }

    public function getDataRow(): DataRow
    {
        return $this->dataRow;
    }
}
