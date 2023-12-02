<?php
/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Adaptor\Property;

use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelFileAdaptor extends ExcelDocumentAdaptor implements PropertyAdaptor
{
    public function __construct(protected string $filename)
    {
        parent::__construct(IOFactory::load($filename));
    }
}
