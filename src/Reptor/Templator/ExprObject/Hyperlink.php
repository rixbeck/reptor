<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Templator\ExprObject;

use brix\Reptor\Templator\Event\PrepareSpreadsheetEvent;
use brix\Reptor\Templator\ViewController\ViewControllerFactory;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Hyperlink extends AbstractExpression
{
    protected string $value;

    public function __construct(
        protected ViewControllerFactory $viewControllerFactory,
        Cell $cell,
        string $link,
        string $label = null,
    ) {
        $label ??= $link;
        $this->value = $label;
        $cell->getHyperlink()->setUrl($link);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
