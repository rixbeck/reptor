<?php
/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Adaptor\DataSource;

interface ReaderInterface
{
    public function __invoke(...$args): \Iterator;
    public function initialize(mixed $options): self;

    public function row(): \Generator;

    public function count(): int;
}
