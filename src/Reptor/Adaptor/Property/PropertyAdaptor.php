<?php
/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Adaptor\Property;

interface PropertyAdaptor
{
    public function getProperties(): array;
}
