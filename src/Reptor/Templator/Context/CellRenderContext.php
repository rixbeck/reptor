<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Templator\Context;

use brix\Reptor\Templator\ExprObject\Interface\ExprObjectInterface;
use brix\Reptor\Templator\Unit;
use brix\Reptor\Templator\UnitTemplate;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\CellAddress;

class CellRenderContext
{
    public function __construct(
        public ?string $cellContent = null,
        public ?Cell $cell = null,
        public ?CellAddress $cellAddress = null,
        public ExprObjectInterface|string|null $value = null,
        public ?Unit $unit = null,
        public ?UnitTemplate $unitTemplate = null,
        public ?string $templateId = null,
    ) {
    }
}
