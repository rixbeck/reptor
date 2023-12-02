<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\ExpressionLanguage\Extension\Functions;

use brix\Reptor\Templator\ExprObject\Aggregator\AggregatorFactory;
use brix\Reptor\Templator\ExprObject\Aggregator\Avg;
use brix\Reptor\Templator\ExprObject\Aggregator\Sum;
use brix\Reptor\Templator\ExprObject\Interface\AggregatorInterface;
use brix\Reptor\Templator\ExprObject\Interface\ExprObjectInterface;
use brix\Reptor\Templator\ExprObject\Type\GroupByInterface;

trait AggregateFunctionsTrait
{
    protected AggregatorFactory $aggregatorFactory;

    protected function doAvg(mixed $subject, ?GroupByInterface $groupBy): AggregatorInterface
    {
        $value = ($subject instanceof ExprObjectInterface) ? $subject->getValue() : $subject;
        $avg = $this->aggregatorFactory->getInstance(Avg::class, $value, $groupBy);

        return $avg;
    }

    protected function doSum(mixed $subject, GroupByInterface $refGroup = null): AggregatorInterface
    {
        $addValue = ($subject instanceof ExprObjectInterface) ? $subject->getValue() : $subject;
        $sum = $this->aggregatorFactory->getInstance(Sum::class, $addValue, $refGroup);

        return $sum;
    }
}
