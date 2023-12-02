<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Templator\ViewController;

use brix\Reptor\Templator\Context\CellRenderContext;
use brix\Reptor\Templator\ExprObject\Interface\ExprObjectInterface;

class AggregateViewController extends AbstractViewController
{
    public function renderByEvent(CellRenderContext $context, ExprObjectInterface $exprObject): void
    {
        $cell = $this->worksheet->getCell($context->cellAddress->cellAddress());
        $cell->setValue((string)$exprObject);
        $cell->setXfIndex($context->unitTemplate->getXfIndex());
    }

    protected function adjustUnitType(ExprObjectInterface $exprObject): void
    {
        parent::adjustUnitType($exprObject);
    }
}
