<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Templator\ExprObject\Interface;

use brix\Reptor\Templator\ViewController\ViewControllerInterface;

interface ExprObjectInterface extends \Stringable
{
    public function exprObject(): self;
    public function __toString(): string;

    public function getViewController(): ViewControllerInterface;

    public function getValue(): mixed;

    public function getType(): string;
}
