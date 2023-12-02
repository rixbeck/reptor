<?php
/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

use brix\Reptor\ExpressionLanguage\ExpressionLanguage;

class ExpressionLanguageTest extends \PHPUnit\Framework\TestCase
{
    private ExpressionLanguage $expressionLanguage;

    public function testEcho(): void
    {
        $this->assertEquals('x-dummy', $this->expressionLanguage->evaluate('echo("x-dummy")', ['params' => [1, 2]]));
    }

    public function testExcelParam(): void
    {
    }

    protected function setUp(): void
    {
        $this->expressionLanguage = new ExpressionLanguage(null, [new \brix\Reptor\ExpressionLanguage\Extension\CoreExtension(
        )]);
    }
}
