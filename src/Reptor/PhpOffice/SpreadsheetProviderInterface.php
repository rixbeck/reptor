<?php
/**
 * @author Rix Beck <rix@neologik.hu>
 */

namespace brix\Reptor\PhpOffice;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

interface SpreadsheetProviderInterface
{
    public function getSpreadsheet(): Spreadsheet;

    public function getActiveSheet(): Worksheet;

    public function loadFile(string $filename): SpreadsheetProvider;
}
