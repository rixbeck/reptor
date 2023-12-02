<?php
/**
 * @author Rix Beck <rix@neologik.hu>
 */

namespace brix\Reptor\Iterator;

interface PrefetchIteratorInterface extends \Iterator
{
    public function peek(): mixed;

    public function isLast(): bool;

    public function isFirst(): bool;
}
