<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\ExpressionLanguage\Extension;

use brix\Reptor\Adaptor\DataSource\ConnectorProviderInterface;
use brix\Reptor\Adaptor\DataSource\ReaderInterface;
use brix\Reptor\ExpressionLanguage\AbstractExtension;
use brix\Reptor\Templator\Context\ContextProvider;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;

class DataExtension extends AbstractExtension
{
    protected ReaderInterface $reader;
    public function __construct(protected ConnectorProviderInterface $connector, protected ContextProvider $contextProvider)
    {
    }

    public function getFunctions(): array
    {
        return [
            new ExpressionFunction(
                'DataSet',
                fn () => '',
                /**
                 * Dataset reader.
                 *
                 * usage: DataSet(<string>connectionstr, [<string>sql, <array>parameters])
                 *
                 * @param array $context - context variables of the expression
                 * @param mixed ...$a    - reader parameters arguments
                 */
                fn (array $context, ...$a) => $this->dataSet(...$a)
            ),

        ];
    }

    private function dataSet(string $source, ...$args): ReaderInterface
    {
        $this->reader = ($this->connector)($source, $this->contextProvider);

        return $this->reader->initialize(...[...$args, []]);
    }
}
