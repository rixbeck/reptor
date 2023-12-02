<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\ExpressionLanguage\Extension;

use brix\Reptor\ExpressionLanguage\AbstractExtension;
use brix\Reptor\Templator\ExprObject\Aggregator\AggregatorFactory;
use brix\Reptor\Templator\ExprObject\Aggregator\Avg;
use brix\Reptor\Templator\ExprObject\Aggregator\Sum;
use brix\Reptor\Templator\ExprObject\Interface\AggregatorInterface;
use brix\Reptor\Templator\ExprObject\Interface\ExprObjectInterface;
use brix\Reptor\Templator\ExprObject\Type\GroupByInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;

class AggregateExtension extends AbstractExtension
{
    public function __construct(
        protected EventDispatcherInterface $eventDispatcher,
        protected AggregatorFactory $aggregatorFactory,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new ExpressionFunction(
                'Sum',
                fn () => '',
                /**
                 * Sum data rows' fields.
                 *
                 * usage: aggregate(context, dataRows, aggregateFunction, ...arguments)
                 *
                 * @param array $context - context variables of the expression
                 */
                fn (array $context, mixed $subject, GroupByInterface $groupBy = null) => $this->sum($subject, $groupBy)
            ),
            new ExpressionFunction(
                'Avg',
                fn () => '',
                /**
                 * Average data rows' fields.
                 *
                 * usage: aggregate(context, dataRows, aggregateFunction, ...arguments)
                 *
                 * @param array $context - context variables of the expression
                 */
                fn (array $context, mixed $subject, GroupByInterface $groupBy = null) => $this->avg($subject, $groupBy)
            ),
        ];
    }

    private function avg(mixed $subject, ?GroupByInterface $groupBy)
    {
        $value = ($subject instanceof ExprObjectInterface) ? $subject->getValue() : $subject;
        $avg = $this->aggregatorFactory->getInstance(Sum::class, $value, $groupBy);

        return $avg;
    }

    private function sum(mixed $subject, GroupByInterface $refGroup = null): AggregatorInterface
    {
        $addValue = ($subject instanceof ExprObjectInterface) ? $subject->getValue() : $subject;
        $sum = $this->aggregatorFactory->getInstance(Sum::class, $addValue, $refGroup);

        return $sum;
    }
}
