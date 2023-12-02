<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\ExpressionLanguage\Extension\Functions;

use brix\Reptor\Templator\ExprObject\Interface\ExprObjectInterface;
use brix\Reptor\Templator\ExprObject\Rate;

trait CalcFunctionsTrait
{
    protected function calcRate(mixed $subject, mixed $base): ExprObjectInterface
    {
        if ($subject instanceof ExprObjectInterface) {
            $subject = $subject->getValue();
        }
        if ($base instanceof ExprObjectInterface) {
            $base = $base->getValue();
        }

        return new Rate($this->viewControllerFactory, $subject, $base);
    }
}
