<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Templator\ExprObject;

use brix\Reptor\PhpOffice\SpreadsheetProvider;
use brix\Reptor\Templator\Event\PrepareSpreadsheetEvent;
use brix\Reptor\Templator\ViewController\ViewControllerFactory;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Formula extends AbstractExpression
{
    protected string $value;

    public function __construct(
        protected ViewControllerFactory $viewControllerFactory,
        EventDispatcherInterface $eventDispatcher,
        SpreadsheetProvider $spreadsheetProvider,
        Cell $cell,
        string $formula,
        mixed $expandFormula = true,
    ) {
        $expandFormula = boolval($expandFormula);
        $this->value = (str_starts_with($formula, '=')) ? $formula : '='.$formula;

        if ($expandFormula) {
            $eventDispatcher->addListener(PrepareSpreadsheetEvent::AFTER_RENDER, function () use ($spreadsheetProvider, $cell) {
                $worksheet = $spreadsheetProvider->getSpreadsheet()->getActiveSheet();
                $cell->attach($worksheet->getCellCollection());
                $cell->setValue($cell->getCalculatedValue());
            });
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
