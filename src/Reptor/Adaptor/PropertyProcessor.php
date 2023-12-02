<?php
/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Adaptor;

use brix\Reptor\Adaptor\Property\PropertyAdaptor;

class PropertyProcessor implements PropertyAdaptor
{
    public function __construct(protected array $propertyAdaptors)
    {
    }

    public function getProperties(): array
    {
        $properties = [];
        foreach ($this->propertyAdaptors as $propertyAdaptor) {
            $properties = [ ...$properties, ...$propertyAdaptor->getProperties()];
        }

        return $properties;
    }
}
