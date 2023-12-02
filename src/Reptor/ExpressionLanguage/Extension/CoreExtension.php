<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\ExpressionLanguage\Extension;

use brix\Reptor\ExpressionLanguage\AbstractExtension;
use brix\Reptor\Templator\ExprObject\Aggregator\AggregatorFactory;
use brix\Reptor\Templator\ExprObject\DataRow;
use brix\Reptor\Templator\ExprObject\Text;
use brix\Reptor\Templator\ViewController\ViewControllerFactory;
use PhpOffice\PhpSpreadsheet\Style\Style;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;

class CoreExtension extends AbstractExtension
{
    public function __construct(
        protected EventDispatcherInterface $eventDispatcher,
        protected ViewControllerFactory $viewControllerFactory,
        protected AggregatorFactory $aggregatorFactory,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new ExpressionFunction(
                'call',
                fn() => '',
                /**
                 * Call runtime function.
                 *
                 * usage: call(context, runtimeFunctionName, ...arguments)
                 *
                 * @param array $context - context variables of the expression
                 * @param string $rtFn - runtime function name
                 * @param mixed ...$a - runtime function arguments
                 */
                fn(array $context, string $rtFn, ...$a) => $this->call($context, $rtFn, ...$a)
            ),

            new ExpressionFunction(
                'View',
                fn() => '',
                fn(array &$vars, iterable $value, int $count = null) => $this->dataView($vars, $value, $count)
            ),

            new ExpressionFunction(
                'String',
                fn() => '',
                fn(array &$vars, string $value) => $this->text($vars, $value)
            ),

            new ExpressionFunction(
                'Iterator',
                fn() => '',
                /**
                 * Iterator template expression handler function.
                 *
                 * usage: Iterator()
                 *
                 * @param array $vars - context variables of the expression
                 */
                fn(
                    array &$vars,
                    \Traversable|array $value,
                    string $handler
                ) => $this->iteratorHandler(
                    $vars,
                    $value,
                    $handler
                )
            ),

            new ExpressionFunction(
                'Prif',
                fn() => '',
                fn(array &$vars, mixed $value, $printString) => $this->printIf(
                    $vars,
                    $value,
                    $printString
                )
            ),
        ];
    }

    private function dataView(array $vars, iterable $value, int $count = null): DataRow
    {
        return new DataRow(
            $this->viewControllerFactory,
            $this->aggregatorFactory,
            $value,
            $count
        );
    }

    private function iteratorHandler(array $vars, \Traversable|array $value, string $handler)
    {
        foreach ($value as $item) {
            yield $this->call($vars, $handler, $item);
        }
    }

    private function printIf(array $vars, mixed $value, string $printString): string
    {
        if (empty($value) || (($value = (string)$value) === '')) {
            return '';
        }

        return sprintf($printString, $value);
    }

    private function text(array $vars, string $value)
    {
        return new Text($this->viewControllerFactory, $value);
    }
}
