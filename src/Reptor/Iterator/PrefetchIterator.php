<?php

declare(strict_types=1);
/**
 * @author Rix Beck <rix@neologik.hu>
 */

namespace brix\Reptor\Iterator;

class PrefetchIterator implements \Iterator, PrefetchIteratorInterface
{
    private \Iterator $iterator;
    private mixed $current;
    private bool $hasNext;
    private mixed $key = 0;
    private mixed $next = null;  // Buffer for the next value
    private $generatorFactory;
    private int $offset = 0;
    private int $rowsToSkip = 0;
    private mixed $endValue = null;  // the special end value

    public function __construct(callable $generatorFactory, $endValue = null)
    {
        $this->generatorFactory = $generatorFactory;
        $this->endValue = $endValue;
        $this->iterator = $generatorFactory();
        $this->advance();
    }

    public function current(): mixed
    {
        return $this->current;
    }

    public static function generatorWrapper(\Generator $values, $endValue = null)
    {
        foreach ($values as $value) {
            yield (array)$value;
        }
        yield $endValue;
    }

    public function getRowsToSkip(): int
    {
        return $this->rowsToSkip;
    }

    public function isFirst(): bool
    {
        return $this->offset === 0;
    }

    public function isLast(): bool
    {
        return !$this->hasNext;
    }

    public function key(): mixed
    {
        return $this->offset;
    }

    public function next(): void
    {
        if ($this->rowsToSkip > 0) {
            $this->rowsToSkip--;

            return;
        }

        if ($this->hasNext) {
            $this->offset++;
            $this->advance();
        }
    }

    public function offset(): int
    {
        return $this->offset;
    }

    public function peek(): mixed
    {
        return $this->hasNext ? $this->iterator->current() : false;
    }

    public function rewind(): void
    {
        $this->offset = 0;
        $this->iterator = ($this->generatorFactory)();
        $this->advance();
    }

    public function skip(int $rowsSkipping): void
    {
        $this->rowsToSkip = $rowsSkipping;
    }

    public function valid(): bool
    {
        return $this->hasNext;
    }

    private function advance(): void
    {
        $this->hasNext = $this->iterator->valid();
        if ($this->hasNext) {
            $this->current = $this->iterator->current();
            $this->key = $this->iterator->key();
            $this->iterator->next();
            if ($this->iterator->valid()) {
                if ($this->iterator instanceof \Generator) {
                    $this->hasNext = $this->iterator->current() !== $this->endValue;
                }
            } else {
                $this->hasNext = false;
            }
        } else {
            $this->current = null;
        }
    }
}
