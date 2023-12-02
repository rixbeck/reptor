<?php

/**
 * Helper functions for spreadsheet operations.
 *
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\ExpressionLanguage\Extension;

use brix\Reptor\ExpressionLanguage\AbstractExtension;
use brix\Reptor\ExpressionLanguage\Extension\Functions\SpreadsheetFunctionsTrait;
use brix\Reptor\PhpOffice\SpreadsheetProvider;
use brix\Reptor\Templator\Context\ContextProvider;
use brix\Reptor\Templator\Event\PrepareSpreadsheetEvent;
use brix\Reptor\Templator\ViewController\ViewControllerFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;

class SpreadsheetExtension extends AbstractExtension
{
    use SpreadsheetFunctionsTrait;

    protected static array $defaultStyle = [
        'font' => [
            'bold' => false,
            'italic' => false,
            'underline' => Font::UNDERLINE_NONE,
            'strikethrough' => false,
            'color' => ['argb' => Color::COLOR_BLACK],
        ],
        'fill' => [
            'fillType' => Fill::FILL_NONE,
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_NONE,
            ],
        ],
    ];
    protected array $stash = [];

    public function __construct(
        protected SpreadsheetProvider $spreadsheetProvider,
        protected ViewControllerFactory $viewControllerFactory,
        protected EventDispatcherInterface $eventDispatcher,
        protected ContextProvider $contextProvider,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            /*
             * @deprecated
             */
            new ExpressionFunction(
                'NamedRange',
                fn() => '',
                /**
                 * Named range reader.
                 *
                 * usage: NamedRange(string $name)
                 *
                 * @param string $name - named range name
                 */
                fn(array $context, string $name): string => $this->namedRange($context, $name)
            ),
            /*
             * @deprecated
             */
            new ExpressionFunction(
                'Stash',
                fn() => '',
                /**
                 * Stash range of cells.
                 *
                 * usage: Stash(string $range)
                 *
                 * @param array $context
                 * @param string $range
                 *
                 * @return array
                 */
                fn(array $context, string $range) => $this->stash($context, $range)
            ),

            new ExpressionFunction(
                'Col',
                fn() => '',
                fn(array $context) => $this->column($context)
            ),

            new ExpressionFunction(
                'Row',
                fn() => '',
                fn(array $context) => $this->row($context)
            ),

            new ExpressionFunction(
                'Addr',
                fn() => '',
                fn(array $context, int $colId, int $rowId) => $this->address($colId, $rowId)
            ),

            new ExpressionFunction(
                'Formula',
                fn() => '',
                fn(array $context, mixed $excelExpr, mixed $option = true) => $this->formula($excelExpr, $option)
            ),

            new ExpressionFunction(
                'Hyperlink',
                fn() => '',
                fn(array $context, mixed $link, mixed $label = null) => $this->hyperlink($link, $label)
            ),
        ];
    }

    /**
     * @deprecated
     */
    public function makeTemplateVarName(int|string $range): string
    {
        return sprintf('[[STASH_%s]]', $range);
    }

    /**
     * @throws Exception
     * @deprecated
     *
     */
    protected function namedRange(array $context, string $name): string
    {
        $namedRange = $this->spreadsheetProvider
            ->getSpreadsheet()
            ->getNamedRange($name);
        if ($namedRange === null) {
            throw new \RuntimeException(sprintf('Named range "%s" not found.', $name));
        }

        $range = $namedRange->getRange();
        $sheet = $namedRange->getWorksheet();

        $sheetName = $sheet->getTitle();
        $sheetIndex = $this->spreadsheetProvider
            ->getSpreadsheet()
            ->getIndex($sheet);

        $range = str_replace(
            [
                '$',
                $sheetName,
            ],
            [
                '',
                $sheetIndex,
            ],
            $range
        );

        return $range;
    }

    /**
     * @deprecated
     * At this point we listen to the event and prepare the spreadsheet.
     * We may have stashed ranges, so we need to prepare them.
     */
    protected function prepare(PrepareSpreadsheetEvent $event, string $eventName): void
    {
        $this->prepareStashes($event);
    }

    /**
     * @throws Exception
     * @deprecated
     *
     */
    protected function prepareStashes(PrepareSpreadsheetEvent $event): void
    {
        $spreadsheet = $this->spreadsheetProvider->getSpreadsheet();

        foreach ($this->stash as $range => $data) {
            // we have to clear the range first
            foreach ($spreadsheet->getActiveSheet()->rangeToArray($range) as $rowIndex => $row) {
                foreach ($row as $colIndex => $cellValue) {
                    $colLetter = Coordinate::stringFromColumnIndex(
                        $colIndex + Coordinate::columnIndexFromString($range[0])
                    );
                    $rowNumber = $rowIndex + Coordinate::extractAllCellReferencesInRange($range)[0][1];
                    $spreadsheet->getActiveSheet()->setCellValue($colLetter . $rowNumber, null);
                    $spreadsheet->getActiveSheet()->getStyle($colLetter . $rowNumber)->applyFromArray(
                        self::$defaultStyle
                    );
                }
            }

            /*            $templateVarName = $this->makeTemplateVarName($range);
                        // set top left cell of range to $templateVarName
                        $spreadsheet->getActiveSheet()->setCellValue(
                            Coordinate::stringFromColumnIndex(
                                Coordinate::columnIndexFromString($range[0])
                            ).Coordinate::extractAllCellReferencesInRange($range)[0][1],
                            $templateVarName
                        );*/
            // add new template name to context and set value as Table
            // $event->context[$templateVarName] = new InterpreterProxy(new TableCellSetter(), $data);
        }
    }

    /**
     * @deprecated
     */
    protected function stash(array $context, string $range): array
    {
        [$index, $range] = explode('!', $range);
        $sheet = $this->spreadsheetProvider
            ->getSpreadsheet()
            ->getSheet((int)$index + 1);

        return $this->stash[$range] = $this
            ->spreadsheetProvider
            ->getSpreadsheet()
            ->getActiveSheet()
            ->rangeToArray(
                $range,
                null,
                true,
                true,
                true
            );
    }
}
