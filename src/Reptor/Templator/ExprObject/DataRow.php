<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Templator\ExprObject;

use brix\Reptor\Iterator\PrefetchIterator;
use brix\Reptor\Templator\ExprObject\Aggregator\AggregatorFactory;
use brix\Reptor\Templator\ExprObject\Type\DataRowInterface;
use brix\Reptor\Templator\GroupByManager;
use brix\Reptor\Templator\ViewController\DataRowViewController;
use brix\Reptor\Templator\ViewController\ViewControllerFactory;
use brix\Reptor\Templator\ViewController\ViewControllerInterface;
use SebastianBergmann\Type\RuntimeException;

class DataRow implements DataRowInterface
{
    protected ?PrefetchIterator $datasetRowIterator = null;
    protected mixed $value;
    protected ?ViewControllerInterface $viewController = null;
    protected GroupByManager $groupByManager;
    protected string $fieldName = '';
    /**
     * @var false
     */
    protected bool $endOfData = false;

    public function __construct(
        protected ViewControllerFactory $viewControllerFactory,
        protected AggregatorFactory $aggregatorFactory,
        protected iterable $data,
        protected ?int $count = null
    ) {
        $this->groupByManager = new GroupByManager($this->aggregatorFactory);
    }

    public function __get(string $name): self
    {
        $this->offsetGet($name);

        return clone $this;
    }

    public function __toString(): string
    {
        return isset($this->value) ? (string)$this->value : '';
    }

    public function current(): mixed
    {
        return $this->getDatasetRowIterator()->current();
    }

    public function embed(mixed $embeddedExpr): self
    {
        $this->value = $embeddedExpr;
        $this->fieldName = '';

        return $this;
    }

    public function exprObject(): self
    {
        return $this;
    }

    public function getDatasetRowIterator(): ?PrefetchIterator
    {
        return $this->datasetRowIterator ?? $this->datasetRowIterator = new PrefetchIterator(
            fn() => ($this->data instanceof \Generator) ? PrefetchIterator::generatorWrapper(
                $this->data
            ) : new \ArrayIterator(
                (array)$this->data
            )
        );
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function getGroupByManager(): GroupByManager
    {
        return $this->groupByManager;
    }

    public function getPeekValue(string $field): mixed
    {
        $peekRow = $this->peek();

        return $peekRow[$field] ?? null;
    }

    public function getRow(): array
    {
        return iterator_to_array($this->getDatasetRowIterator());
    }

    public function getType(): string
    {
        return DataRowInterface::class;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getViewController(): ViewControllerInterface
    {
        return $this->viewController ??= $this->viewControllerFactory->create(DataRowViewController::class);
    }

    public function groupBy(string $field, string $alias = null): GroupBy
    {
        $dataRow = $this->current();
        $peekRow = $this->peek();
        $groupBy = $this->groupByManager->resolve(
            $this,
            $alias ?? $field,
            $dataRow[$field] ?? null,
            $peekRow[$field] ?? null
        );
        $this->fieldName = $field;

        return $groupBy;
    }

    public function hasGrouping(): bool
    {
        return !empty($this->groupByManager->getGroups());
    }

    public function isEndOfData(): bool
    {
        return $this->endOfData;
    }

    public function isFirst(): bool
    {
        return $this->getDatasetRowIterator()->isFirst();
    }

    public function isLast(): bool
    {
        return $this->getDatasetRowIterator()->isLast();
    }

    public function key(): mixed
    {
        return $this->getDatasetRowIterator()->key();
    }

    public function next(): void
    {
        if ($this->isLast()) {
            $this->endOfData = true;
            return;
        }

        $this->fieldName = '';
        $this->value = null;
        $this->getDatasetRowIterator()->next();
    }

    public function offsetExists(mixed $offset): bool
    {
        return !$this->isEndOfData() && array_key_exists($offset, $this->current());
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->value = (!$this->isEndOfData()) ? $this->current()[$this->fieldName = $offset] : null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new RuntimeException('Cannot set value in dataset');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new RuntimeException('Cannot unset value in dataset');
    }

    public function peek(): mixed
    {
        return $this->getDatasetRowIterator()->peek();
    }

    public function rewind(): void
    {
        $this->endOfData = false;
        $this->datasetRowIterator->rewind();
    }

    public function skip(int $rowsSkipping): self
    {
        $this->getDatasetRowIterator()->skip($rowsSkipping);

        return $this;
    }

    public function valid(): bool
    {
        return $this->getDatasetRowIterator()->valid();
    }

    protected function toCsvString(): ?string
    {
        $memTmp = fopen('php://memory', 'r+');
        if (fputcsv($memTmp, $this->getRow()) === false) {
            return null;
        }
        rewind($memTmp);
        $csvString = stream_get_contents($memTmp);

        return rtrim($csvString);
    }
}
