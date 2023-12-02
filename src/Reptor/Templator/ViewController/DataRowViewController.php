<?php
/**
 * @author Rix Beck <rix@neologik.hu>, Sidorovich Nikolay <sidorovich21101986@mail.ru>
 */

namespace brix\Reptor\Templator\ViewController;

use brix\Reptor\Templator\Context\CellRenderContext;
use brix\Reptor\Templator\Context\ContextProvider;
use brix\Reptor\Templator\Event\NextDataRowEvent;
use brix\Reptor\Templator\Event\NextRowEvent;
use brix\Reptor\Templator\ExprObject\DataRow;
use brix\Reptor\Templator\ExprObject\Interface\ExprObjectInterface;
use brix\Reptor\Templator\ExprObject\Type\DataRowInterface;
use brix\Reptor\Templator\ExprObject\Type\GroupByInterface;
use brix\Reptor\Templator\MergeManagerInterface;
use brix\Reptor\Templator\Unit;
use brix\Reptor\Templator\UnitManager;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DataRowViewController extends AbstractViewController implements ViewControllerInterface
{
    protected DataRowInterface|ExprObjectInterface $baseExprObject;
    private CellRenderContext $contextCopy;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        Spreadsheet $spreadsheet,
        UnitManager $unitManager,
        ContextProvider $contextProvider,
        AllocationStack $allocationStack,
    ) {
        parent::__construct(
            $eventDispatcher,
            $spreadsheet,
            $unitManager,
            $contextProvider,
            $allocationStack
        );
        $this->eventDispatcher->addListener(
            NextRowEvent::class,
            $this->nextRowHandler = fn (...$args) => $this->nextRowEventHandler(...$args),
        );
    }

    public function finishUnit(Unit $unit): self
    {
        $unit->lock();
        if (!$unit->hasParent() && $unit->getUnitModel()->exprObject()->isLast()) {
            $this->unitManager->unlinkUnit($unit);
        }

        return $this;
    }

    public function preserveUnitAllocation(): self
    {
        $this->cellAllocator();

        return $this;
    }

    protected function cellAllocator(): void
    {
        $value = $this->contextProvider->getCellRenderContext()->value;
        $this->baseExprObject = $value->exprObject();

        /** @var DataRow $exprObject */
        $exprObject = $value->exprObject();
        $colLeft = $this->contextProvider->getCellRenderContext()->cellAddress->columnName();

        if ($this->unit->getUnitType() === GroupByInterface::class && !$this->unit->getUnitModel()->isLast()) {
            $this->unit->setHidden(true);
        } else {
            // if it has no grouping or grouping is completed, make room for a whole next unit
            $cellRenderContext = $this->contextProvider->getCellRenderContext();
            $colLeft = $this->unit->getFrom()->columnName();
            $colRight = $this->unit->getTo()->columnName();
            $colsAdded = $this->unit->getWidth() - 1;
            $rowsAdded = $this->unit->getHeight();
            $this->prepareAllocation(
                $colLeft,
                $colRight,
                $cellRenderContext->cellAddress->rowId(),
                $rowsAdded,
                $rowsAdded,
            );
        }
    }

    /**
     * @throws Exception
     */
    protected function nextRowEventHandler(NextRowEvent $event): void
    {
        $this->eventDispatcher->dispatch(
            new NextDataRowEvent($event->getCellRenderContext(), $this->baseExprObject)
        );

        if ($this->baseExprObject->isLast()) {
            $this->eventDispatcher->removeListener(NextRowEvent::class, $this->nextRowHandler);
        }
        $this->baseExprObject->next();
    }

    public function nextDataRowEventHandler(Unit $unit, ...$args): void
    {
        if ($unit->getUnitModel()->exprObject()->isLast()) {
            $this->unitManager->unlinkUnit($unit);

            return;
        }

        if ($unit->getUnitType() === GroupByInterface::class && $unit->isHidden()) {
            $groupByModel = $unit->getUnitModel()
                ->exprObject()
                ->groupBy($unit->getUnitModel()->getFieldName());
            if ($groupByModel->isLast()) {
                $currentRow = $this->contextProvider
                    ->getCellRenderContext()
                    ->cellAddress
                    ->rowId();
                [$offsX, $offsY] = $unit->getOffsetNext();
                $nextUnitTop = $unit->getNext() ? $unit->getNext()->getTop() : $currentRow;
                $this->unitManager->adjustTop(
                    $unit,
                    $nextUnitTop - $offsY - $unit->getTop() + 1,
                    $unit->getWidth() - 1
                );
                $this->shiftDown(
                    $unit->getFrom()->columnName(),
                    $unit->getTo()->columnName(),
                    $currentRow,
                    $unit->getHeight(),
                );
                $unit->setHidden(false);
            }
        }
    }
}
