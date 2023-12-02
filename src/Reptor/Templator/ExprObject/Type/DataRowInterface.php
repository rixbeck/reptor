<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Templator\ExprObject\Type;

use brix\Reptor\Iterator\PrefetchIteratorInterface;
use brix\Reptor\Templator\ExprObject\Interface\AddressableObjectInterface;
use brix\Reptor\Templator\ExprObject\Interface\ExprObjectInterface;

interface DataRowInterface extends ExprObjectInterface, \ArrayAccess, PrefetchIteratorInterface,
                                   AddressableObjectInterface, IterableUnitInterface
{
    public const PRIORITY = 100;
}
