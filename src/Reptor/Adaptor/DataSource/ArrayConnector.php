<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Adaptor\DataSource;

use brix\Reptor\Templator\Context\ContextProvider;

/**
 * Class ArrayConnector
 *
 * Allows to use an array in specified context as a data source. Connection string should be in the following format:
 * array://<variable_name>
 *     - variable_name: name of the variable in the context
 * Example:
 *    array://data
 *
 * @package brix\Reptor\Adaptor\DataSource
 */
class ArrayConnector implements ConnectorInterface
{

    protected string $variableName;
    /**
     * @param string $connectionString
     * @param $args
     */
    public function __construct(string $connectionString, protected ContextProvider $contextProvider)
    {
        $uri = parse_url($connectionString);
        $this->variableName = $uri['host'];
    }

    public function __invoke()
    {
        return $this->contextProvider->getExpressionContext()[$this->variableName];
    }

    public function getDataFormat(): ?string
    {
        return 'array';
    }
}
