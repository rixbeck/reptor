<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\ExpressionLanguage\Extension\Functions;

use brix\Reptor\Templator\ExprObject\Formula;
use brix\Reptor\Templator\ExprObject\Hyperlink;
use brix\Reptor\Templator\ExprObject\Interface\ExprObjectInterface;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

trait SpreadsheetFunctionsTrait
{

    protected function address(int $colId, int $rowId): string
    {
        return Coordinate::stringFromColumnIndex($colId).$rowId;
    }

    protected function column(array $context): int
    {
        return $this->contextProvider->getCellRenderContext()->cellAddress->columnId();
    }

    protected function formula(mixed $formula, mixed $option): ExprObjectInterface
    {
        if ($formula instanceof ExprObjectInterface) {
            $formula = $formula->exprObject()->getValue();
        }

        return new Formula(
            $this->viewControllerFactory,
            $this->eventDispatcher,
            $this->spreadsheetProvider,
            $this->contextProvider->getCellRenderContext()->cell,
            $formula,
            $option,
        );
    }

    protected function row(array $context): int
    {
        return $this->contextProvider->getCellRenderContext()->cellAddress->rowId();
    }

    protected function hyperlink(mixed $link, mixed $label): ExprObjectInterface
    {
        if ($link instanceof ExprObjectInterface) {
            $link = $link->exprObject()->getValue();
        }
        if ($label instanceof ExprObjectInterface) {
            $label = $label->exprObject()->getValue();
        }

        return new Hyperlink(
            $this->viewControllerFactory,
            $this->contextProvider->getCellRenderContext()->cell,
            $link,
            $label,
        );

    }
}
