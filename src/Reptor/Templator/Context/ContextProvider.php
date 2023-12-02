<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Templator\Context;

class ContextProvider
{
    public function __construct(protected CellRenderContext $cellRenderContext, protected array $expressionContext = [])
    {
    }

    public function getCellRenderContext(): CellRenderContext
    {
        return $this->cellRenderContext;
    }

    public function &getExpressionContext(): array
    {
        return $this->expressionContext;
    }

    public function setCellRenderContext(CellRenderContext $cellRenderContext): CellRenderContext
    {
        $actual = clone $this->cellRenderContext;
        $this->cellRenderContext = $cellRenderContext;

        return $actual;
    }

    public function setExpressionContext(array $expressionContext): void
    {
        $this->expressionContext = $expressionContext;
    }
}
