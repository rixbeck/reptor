<?php
/**
 * @author Rix Beck <rix@neologik.hu>
 */

declare(strict_types=1);

namespace brix\Reptor\Templator\Event;

use brix\Reptor\Templator\Context\CellRenderContext;

class NextRowEvent
{
    public function __construct(protected CellRenderContext $cellRenderContext)
    {
    }

    public function getCellRenderContext(): CellRenderContext
    {
        return $this->cellRenderContext;
    }
}
