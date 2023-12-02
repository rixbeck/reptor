<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Adaptor\DataSource;

use brix\Reptor\Templator\Context\ContextProvider;

interface ConnectorProviderInterface
{
    public function __invoke(string $connectionString, ContextProvider $contextProvider, ...$args): ReaderInterface;
}
