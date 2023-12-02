<?php
/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\ExpressionLanguage\Extension;

use brix\Reptor\ExpressionLanguage\AbstractExtension;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;

class PhpExtension extends AbstractExtension
{
    public function __construct(protected array $functions = [])
    {
    }

    public function getFunctions(): array
    {
        $functions = [];
        foreach ($this->functions as $function) {
            $functions[] = new ExpressionFunction($function, fn () => '', fn (array $vars, ...$a) => $function(...$a));
        }

        return $functions;
    }
}
