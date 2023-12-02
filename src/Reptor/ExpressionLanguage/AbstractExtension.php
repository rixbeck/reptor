<?php
/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

abstract class AbstractExtension implements ExpressionFunctionProviderInterface
{
    protected ?ExpressionLanguage $languageContext = null;
    /**
     * @return ExpressionFunction[]
     */
    abstract public function getFunctions(): array;

    public function setLanguageContext(ExpressionLanguage $languageContext)
    {
        $this->languageContext = $languageContext;
    }

    protected function call(array $context, ?string $runtimeFn, mixed $args): mixed
    {
        $context = [...$context, ...[ 'arg' => $args ]];
        $runtimeFnName = $this->languageContext?->getRuntimeFnName($runtimeFn);
        if ($context[$runtimeFnName] ?? false) {
            $expression = $context[$runtimeFnName];

            return $this->languageContext?->evaluate($expression, $context);
        }

        return null;
    }
}
