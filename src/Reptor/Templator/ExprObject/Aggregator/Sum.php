<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Templator\ExprObject\Aggregator;

use brix\Reptor\Templator\ExprObject\Interface\ExprObjectInterface;
use brix\Reptor\Templator\ExprObject\Type\SumInterface;

class Sum extends AbstractAggregator implements SumInterface
{
    public function calculation(mixed $subject): void
    {
        $subject = (array)$subject;
        foreach ($subject as $value) {
            if ($value instanceof ExprObjectInterface) {
                $value = $value->getValue();
            }
            $this->value += (float)$value ?? 0;
        }
    }

    public function getType(): string
    {
        return SumInterface::class;
    }
}
