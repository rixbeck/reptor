<?php
/**
 * @author Rix Beck <rix@neologik.hu>, Sidorovich Nikolay <sidorovich21101986@mail.ru>
 */

namespace brix\Reptor\Templator;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\IWriter;

class TemplatorPdf extends Templator
{
    protected static function getWriter(Spreadsheet $spreadsheet): IWriter
    {
        return IOFactory::createWriter($spreadsheet, 'Mpdf');
    }

    /**
     * Sets the header parameters needed to download the Excel file.
     *
     * @param string $fileName
     */
    protected static function setHeaders(string $fileName): void
    {
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-type: application/pdf');
        header('Pragma: public');

        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate');
    }
}
