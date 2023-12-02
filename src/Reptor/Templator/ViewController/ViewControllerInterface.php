<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Templator\ViewController;

use brix\Reptor\Templator\Unit;

interface ViewControllerInterface
{
    public function setupUnit(): self;

    public function preserveUnitAllocation(): self;

    public function nextDataRowEventHandler(Unit $unit, ...$args): void;

    public function applyUnitAttributes(Unit $unit): self;

    public function finishUnit(Unit $unit): self;
}
