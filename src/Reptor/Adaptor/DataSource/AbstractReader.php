<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Adaptor\DataSource;

abstract class AbstractReader implements ReaderInterface
{
    public function __invoke(...$args): \Generator
    {
        return $this->row(...$args);
    }

    public function count(array $params = []): int
    {
        return $this->count ??= $this->countRows($params);
    }

    public function initialize(mixed $options): ReaderInterface
    {
        return $this;
    }

    protected function countRows(array $params = []): int
    {
        $asArray = iterator_to_array($this->row());
        $this->row($params)->rewind();

        return count($asArray);
    }
}
