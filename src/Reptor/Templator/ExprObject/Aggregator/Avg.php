<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Templator\ExprObject\Aggregator;

use brix\Reptor\Templator\ExprObject\Interface\ExprObjectInterface;
use brix\Reptor\Templator\ExprObject\Type\AvgInterface;

class Avg extends AbstractAggregator implements AvgInterface
{
    protected int $count = 0;
    protected float $sum = 0;

    public function calculation(mixed $subject): void
    {
        if ($this->subject instanceof ExprObjectInterface) {
            $subject = $this->subject->getValue();
        }
        ++$this->count;
        $this->sum += (float)$subject ?? 0;
        $this->value = $this->sum / $this->count;
    }

    public function getType(): string
    {
        return AvgInterface::class;
    }
}
