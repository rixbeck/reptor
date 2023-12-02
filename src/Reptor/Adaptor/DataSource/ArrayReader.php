<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Adaptor\DataSource;

/**
 * Class ArrayReader
 *
 * @package brix\Reptor\Adaptor\DataSource
 * @author Rix Beck <rix@neologik.hu>
 */
class ArrayReader extends AbstractReader implements ReaderInterface
{
    public function __construct(protected ConnectorInterface $connector)
    {
    }

    public function row(): \Generator
    {
        $data = $this->connector->__invoke();

        foreach ($data as $dataRow) {
            yield $dataRow;
        }
    }
}
