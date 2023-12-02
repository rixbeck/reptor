<?php
/**
 * @author Rix Beck <rix@neologik.hu>
 */

declare(strict_types=1);

namespace brix\Reptor\Templator;

use brix\Reptor\Templator\ExprObject\Aggregator\AggregatorFactory;
use brix\Reptor\Templator\ExprObject\DataRow;
use brix\Reptor\Templator\ExprObject\GroupBy;
use brix\Reptor\Templator\ViewController\ViewControllerFactory;

class GroupByManager
{
    protected $groups = [];

    public function __construct(protected AggregatorFactory $aggregatorFactory)
    {
    }

    public function __invoke(DataRow $dataRow, string $field, mixed $value, mixed $peekValue): GroupBy
    {
        return $this->resolve($dataRow, $field, $value, $peekValue);
    }

    public function findGroupsCompleted(): array
    {
        $completed = array_filter($this->groups, function ($group) {
            return $group->isLast();
        });

        return $completed;
    }

    public function getGroups(): array
    {
        return array_keys($this->groups);
    }

    public function hasGrouping(): bool
    {
        return count($this->groups) > 0;
    }

    public function isGroupCompleted(string $field = null): bool
    {
        return $this->isGroupedBy($field) && $this->groups[$field]->isLast();
    }

    public function isGroupedBy(string $field): bool
    {
        return array_key_exists($field, $this->groups);
    }

    public function resolve(DataRow $dataRow, string $field, mixed $value, mixed $peekValue): GroupBy
    {
        $this->groups[$field] = $this->groups[$field] ?? new GroupBy($this->aggregatorFactory, $dataRow, $field);
        $this->groups[$field]->setPeekValue($peekValue);

        return $this->groups[$field]->resolve($value);
    }
}
