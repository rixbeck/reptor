<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Templator;

use Psr\Cache\InvalidArgumentException;

class Tokenizer
{
    /**
     * @return $string
     *
     * @throws InvalidArgumentException
     */
    public static function tokenToExpression(string $string): ?string
    {
        $string = trim($string);
        if (str_starts_with($string, '{') && str_ends_with($string, '}')) {
            return substr($string, 1, -1);
        }

        if (str_starts_with($string, '`') && str_ends_with($string, '`')) {
            return sprintf('String(%s)', $string);
        }

        return null;
    }

    public static function isToken(string $string): bool
    {
        return self::tokenToExpression($string) !== null;
    }
}
