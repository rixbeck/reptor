<?php
/**
 * @author Rix Beck <rix@neologik.hu>
 */

declare(strict_types=1);

namespace brix\Reptor\Templator\ExprObject;

use brix\Reptor\Templator\ExprObject\Interface\ExprObjectInterface;
use brix\Reptor\Templator\ExprObject\Type\DefaultInterface;
use brix\Reptor\Templator\ViewController\DefaultViewController;
use brix\Reptor\Templator\ViewController\ViewControllerFactory;
use brix\Reptor\Templator\ViewController\ViewControllerInterface;

class Rate extends AbstractExpression
{
    protected ?ViewControllerInterface $viewController = null;
    protected float $value = 0.0;

    public function __construct(
        protected ViewControllerFactory $viewControllerFactory,
        protected float $subject,
        protected float $base,
    ) {
        $this->value = (empty($base)) ? 0.0 : (float)$subject / (float)$base;
    }

    public function __toString(): string
    {
        return (string)$this->value;
    }
}
