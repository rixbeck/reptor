<?php
/**
 * @author Rix Beck <rix@neologik.hu>, Sidorovich Nikolay <sidorovich21101986@mail.ru>
 */

namespace brix\Reptor\Templator;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\IWriter;

class TemplatorXls extends Templator
{
    protected static function getWriter(Spreadsheet $spreadsheet): IWriter
    {
        return IOFactory::createWriter($spreadsheet, 'Xls');
    }
}
