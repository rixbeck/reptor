<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Templator\ExprObject\Type;

use brix\Reptor\Templator\ExprObject\Interface\AddressableObjectInterface;
use brix\Reptor\Templator\ExprObject\Interface\ExprObjectInterface;

interface GroupByInterface extends ExprObjectInterface, AddressableObjectInterface, IterableUnitInterface
{
    public const PRIORITY = 200;
}
