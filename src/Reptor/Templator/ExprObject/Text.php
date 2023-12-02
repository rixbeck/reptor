<?php
/**
 * @author Rix Beck <rix@neologik.hu>
 */

declare(strict_types=1);

namespace brix\Reptor\Templator\ExprObject;

use brix\Reptor\Templator\ExprObject\Interface\ExprObjectInterface;
use brix\Reptor\Templator\ExprObject\Type\TextInterface;
use brix\Reptor\Templator\ViewController\DefaultViewController;
use brix\Reptor\Templator\ViewController\ViewControllerFactory;

class Text implements ExprObjectInterface, TextInterface
{
    protected DefaultViewController $viewController;
    public function __construct(
        protected ViewControllerFactory $viewControllerFactory,
        protected string $value,
    ) {
    }

    public function exprObject(): self
    {
        return $this;
    }

    public function __toString(): string
    {
        return $this->value;
    }


    public function getViewController(): DefaultViewController
    {
        return $this->viewController ??= $this->viewControllerFactory->create(DefaultViewController::class);
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getType(): string
    {
        return TextInterface::class;
    }
}
