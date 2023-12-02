<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Templator\ViewController;

use brix\Reptor\PhpOffice\SpreadsheetProvider;
use brix\Reptor\Templator\Context\ContextProvider;
use brix\Reptor\Templator\MergeManagerInterface;
use brix\Reptor\Templator\UnitManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ViewControllerFactory
{
    protected AllocationStack $allocationStack;

    public function __construct(
        protected EventDispatcherInterface $eventDispatcher,
        protected SpreadsheetProvider $spreadsheetProvider,
        protected UnitManager $unitManager,
        protected ContextProvider $contextProvider,
    ) {
        $this->allocationStack = new AllocationStack($this->eventDispatcher);
    }

    public function create(string $className): ViewControllerInterface
    {
        return new $className(
            $this->eventDispatcher,
            $this->spreadsheetProvider->getSpreadsheet(),
            $this->unitManager,
            $this->contextProvider,
            $this->allocationStack,
        );
    }
}
