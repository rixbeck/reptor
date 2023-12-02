<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Templator\Event;

use brix\Reptor\Templator\Context\CellRenderContext;

class BeforeCellValueSetEvent
{
    protected CellRenderContext $cellRenderContext;

    /**
     * @param CellRenderContext $cellRenderContext
     */
    public function __construct(CellRenderContext $cellRenderContext)
    {
        $this->cellRenderContext = $cellRenderContext;
    }

    public function getCellRenderContext(): CellRenderContext
    {
        return $this->cellRenderContext;
    }

    public function setCellRenderContext(CellRenderContext $cellRenderContext): void
    {
        $this->cellRenderContext = $cellRenderContext;
    }
}
