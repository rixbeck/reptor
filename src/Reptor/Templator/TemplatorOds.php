<?php
/**
 * @author Rix Beck <rix@neologik.hu>, Sidorovich Nikolay <sidorovich21101986@mail.ru>
 */

namespace brix\Reptor\Templator;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\Reader\Ods;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\IWriter;

class TemplatorOds extends Templator
{
    /**
     * @throws Exception
     */
    protected static function readSpreadsheet($templateFile): Spreadsheet
    {
        return (new Ods())->load($templateFile);
    }

    protected static function getWriter(Spreadsheet $spreadsheet): IWriter
    {
        return IOFactory::createWriter($spreadsheet, 'Ods');
    }
}
