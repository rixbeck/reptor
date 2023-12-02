<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\ExpressionLanguage\Extension;

use brix\Reptor\ExpressionLanguage\AbstractExtension;
use brix\Reptor\ExpressionLanguage\Extension\Functions\CalcFunctionsTrait;
use brix\Reptor\Templator\ViewController\ViewControllerFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;

class UtilityExtension extends AbstractExtension
{
    use CalcFunctionsTrait;

    public function __construct(
        protected EventDispatcherInterface $eventDispatcher,
        protected ViewControllerFactory $viewControllerFactory,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new ExpressionFunction(
                'Rate',
                fn () => '',
                fn (array $context, mixed $subject, mixed $base) => $this->calcRate($subject, $base)
            ),
        ];
    }
}
