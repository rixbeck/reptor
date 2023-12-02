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

abstract class AbstractExpression implements ExprObjectInterface, DefaultInterface
{
    protected ?ViewControllerInterface $viewController = null;

    protected ViewControllerFactory $viewControllerFactory;

    abstract public function __toString(): string;

    public function exprObject(): self
    {
        return $this;
    }

    public function getType(): string
    {
        return DefaultInterface::class;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getViewController(): DefaultViewController
    {
        return $this->viewController ??= $this->viewControllerFactory->create(DefaultViewController::class);
    }
}
