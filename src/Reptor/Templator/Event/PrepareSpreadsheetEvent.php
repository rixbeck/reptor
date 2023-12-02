<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Templator\Event;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

class PrepareSpreadsheetEvent
{
    public const BEFORE_RENDER = 'reptor.templator.render.before';
    public const AFTER_RENDER = 'reptor.templator.render.after';

    public function __construct(public Spreadsheet $spreadsheet, public array &$context)
    {
    }
}
