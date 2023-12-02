<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Templator\ExprObject\Interface;

interface AddressableObjectInterface
{
    public function getFieldName(): string;
}
