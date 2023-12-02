<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Adaptor\DataSource;

interface ConnectorInterface
{
    public function __invoke();

    public function getDataFormat(): ?string;
}
