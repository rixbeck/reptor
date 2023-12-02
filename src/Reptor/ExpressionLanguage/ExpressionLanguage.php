<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\ExpressionLanguage;

use brix\Reptor\Templator\Context\CellRenderContext;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use Symfony\Component\ExpressionLanguage\SyntaxError as ExpLangSyntaxError;
use uuf6429\ExpressionLanguage\ExpressionLanguageWithTplStr;

class ExpressionLanguage extends ExpressionLanguageWithTplStr
{
    protected \SplObjectStorage $objPropMap;

    public function __construct(CacheItemPoolInterface $cache = null, array $providers = [])
    {
        parent::__construct($cache, $providers);
        $this->objPropMap = new \SplObjectStorage();
    }

    public function evaluate(Expression|string $expression, array $values = []): mixed
    {
        $value = parent::evaluate($expression, $values);

        if (is_object($value)) {
            $this->objPropMap->attach($value, $expression);
        }

        return $value;
    }

    public function evaluateCollection(array $collection, array &$context = []): array
    {
        foreach ($collection as $key => $item) {
            $key = trim($key, ' ');
            if (!str_ends_with($key, '()')) {
                $context[$key] = (is_string($item)) ? $this->evaluate($item, $context) : $item;
            } else {
                $this->prepareRuntimeFunction($key, $item, $context);
            }
        }

        return $context;
    }

    public function findPropertyObjectValue(object $objectInContext): ?string
    {
        return $this->objPropMap->contains($objectInContext) ? $this->objPropMap->offsetGet($objectInContext) : null;
    }

    public function getRuntimeFnName(?string $name): string
    {
        return $name ? self::makeRuntimeFnName($name) : '';
    }

    public function prepareRuntimeFunctions(array $collection, array &$context = []): array
    {
        foreach ($collection as $key => $item) {
            $key = trim($key, ' ');
            $this->prepareRuntimeFunction($key, $item, $context);
        }

        return $context;
    }

    public function registerProvider(ExpressionFunctionProviderInterface $provider)
    {
        $provider->setLanguageContext($this);
        parent::registerProvider($provider);
    }

    protected function prepareRuntimeFunction(string $key, mixed $item, array &$context): void
    {
        if (str_ends_with($key, '()')) {
            [$key, $item] = $this->prepareFunction($key, $item);
            $key = self::makeRuntimeFnName($key);
            $context[$key] = $item;
        }
    }

    private function makeContextualMessage(
        string $message,
        Expression|string $expression,
        CellRenderContext $context = null
    ): string {
        if ($context) {
            $message = sprintf(
                '%s Cell %s',
                $message,
                $context->cellAddress
            );
        }

        return $message;
    }

    private static function makeRuntimeFnName(mixed $name): string
    {
        return sprintf('_FN_%s', strtoupper($name));
    }

    private function prepareFunction(int|string $key, mixed $item): array
    {
        $key = trim($key, '()');

        return [$key, $item];
    }
}
