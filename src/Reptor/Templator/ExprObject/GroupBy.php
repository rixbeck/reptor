<?php
/**
 * @author Rix Beck <rix@neologik.hu>
 */

declare(strict_types=1);

namespace brix\Reptor\Templator\ExprObject;

use brix\Reptor\Templator\ExprObject\Aggregator\AggregatorFactory;
use brix\Reptor\Templator\ExprObject\Aggregator\Avg;
use brix\Reptor\Templator\ExprObject\Aggregator\Sum;
use brix\Reptor\Templator\ExprObject\Interface\AggregatorInterface;
use brix\Reptor\Templator\ExprObject\Interface\ExprObjectInterface;
use brix\Reptor\Templator\ExprObject\Type\GroupByInterface;
use brix\Reptor\Templator\ViewController\ViewControllerInterface;

class GroupBy implements GroupByInterface
{
    protected mixed $resolvedValue = '';
    protected mixed $value = '';
    protected mixed $peekValue = null;

    public function __construct(
        protected AggregatorFactory $aggregatorFactory,
        protected DataRow $dataRow,
        protected string $fieldName
    ) {
    }

    public function __toString(): string
    {
        return $this->resolvedValue ?? '';
    }

    public function exprObject(): DataRow
    {
        return $this->dataRow;
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function getPeekValue(): mixed
    {
        return $this->peekValue;
    }

    public function setPeekValue(mixed $peekValue): void
    {
        $this->peekValue = $peekValue;
    }

    public function getType(): string
    {
        return GroupByInterface::class;
    }

    public function getValue(): mixed
    {
        return $this->resolvedValue;
    }

    public function getViewController(): ViewControllerInterface
    {
        return $this->dataRow->getViewController();
    }

    public function isLast(): bool
    {
        return $this->value !== $this->peekValue;
    }

    public function resolve(mixed $value): self
    {
        if ($this->value === $value || $value === null) {
            $this->resolvedValue = ($value === null) ? null : '';

            return $this;
        }
        $this->value = $value;
        $this->resolvedValue = $value;

        return $this;
    }

    public function skip(int $rowsSkipping = 1): self
    {
        if ($this->resolvedValue === '') {
            return $this;
        }

        $this->dataRow->skip($rowsSkipping);

        return $this;
    }

    /**
     * @param string|array $subject
     * @return null Prohibit to render cell for Templator
     */
    public function Sum(mixed $subject): AggregatorInterface
    {
        $addValue = ($subject instanceof ExprObjectInterface) ? $subject->getValue() : $subject;
        $sum = $this->aggregatorFactory
            ->getInstance(Sum::class,  $addValue, $this);

        return $sum;
    }

    /**
     * @param string|array $field
     * @return null Prohibit to render cell for Templator
     */
    public function Avg(mixed $subject): AggregatorInterface
    {
        $value = ($subject instanceof ExprObjectInterface) ? $subject->getValue() : $subject;
        $avg = $this->aggregatorFactory
            ->getInstance(Avg::class,  $value, $this);

        return $avg;
    }
}
