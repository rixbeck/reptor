<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Templator;

use brix\Reptor\PhpOffice\Coordinate;
use brix\Reptor\Templator\Event\NextDataRowEvent;
use brix\Reptor\Templator\ExprObject\Interface\ExprObjectInterface;
use brix\Reptor\Templator\ExprObject\Type\DefaultInterface;
use brix\Reptor\Templator\ExprObject\Type\IterableUnitInterface;
use PhpOffice\PhpSpreadsheet\Cell\CellAddress;
use PhpOffice\PhpSpreadsheet\Cell\CellRange;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Psr\EventDispatcher\EventDispatcherInterface;

class Unit
{
    public bool $newRow = true;
    protected ?Unit $child = null;

    protected ?CellAddress $from = null;

    protected string $name = '';
    protected ?Unit $next = null;
    /**
     * Original offset to next unit in parent unit
     */
    protected array $offsetNext = [0, 0];
    protected ?Unit $parent = null;
    protected ?Unit $prev = null;

    protected ?CellAddress $to = null;
    protected string $unitType = DefaultInterface::class;
    protected ExprObjectInterface $unitModel;
    protected UnitTemplate $unitTemplate;
    protected EventDispatcherInterface $eventDispatcher;
    protected \Closure $nextDataRowHandler;
    /**
     * Hidden units are not rendered.
     * Hidden units are temporarily out of rendering while data row iteration is progressing
     */
    private bool $hidden = false;
    /**
     * Locking unit prevents to change its type. When unit rendering is completed first time, it should be locked.
     */
    private bool $locked = false;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        string $name = null,
        string|array $range = [],
        Worksheet $worksheet = null
    ) {
        if (is_string($range)) {
            [$from, $to] = explode(':', $range);
            $this->from = new CellAddress($from, $worksheet);
            $this->to = new CellAddress($to, $worksheet);
        } elseif (!empty($range)) {
            $from = Coordinate::stringFromColumnIndex($range[0]) . $range[1];
            $to = Coordinate::stringFromColumnIndex($range[2]) . $range[3];

            $this->from = new CellAddress($from, $worksheet);
            $this->to = new CellAddress($to, $worksheet);
        }
        $this->name = $name ?? uniqid('unit_');

        $this->eventDispatcher = $eventDispatcher;
        $this->eventDispatcher->addListener(
            NextDataRowEvent::class,
            $this->nextDataRowHandler = fn (...$args) => $this->getUnitModel()->getViewController()->nextDataRowEventHandler(
                $this, ...$args
            )
        );
    }

    public function adjustHeight(int $rowsOffset): int
    {
        $this->to = $this->to->nextRow($rowsOffset);

        return $this->getHeight();
    }

    public function adjustRight(mixed $columnOrOffset): int
    {
        $cols = is_numeric($columnOrOffset) ? $columnOrOffset - $this->to->columnId(
            ) : Coordinate::columnIndexFromString(
                $columnOrOffset
            ) - $this->to->columnId();
        $this->to->nextColumn($cols);

        return $cols;
    }

    public function adjustTop(int $rowsOffset): int
    {
        $this->from = $this->from->nextRow($rowsOffset);
        $this->to = $this->to->nextRow($rowsOffset);

        return $this->from->rowId();
    }

    public function area(): int
    {
        return $this->getWidth() * $this->getHeight();
    }

    public function getBottom(): int
    {
        return $this->to->rowId();
    }

    public function getCellRange(): ?CellRange
    {
        return new CellRange($this->from, $this->to);
    }

    public function getChild(): ?Unit
    {
        return $this->child;
    }

    public function setChild(?Unit $child): self
    {
        $this->child = $child;

        return $this;
    }

    public function getUnitModel(): ExprObjectInterface
    {
        return $this->unitModel;
    }

    public function getFrom(): CellAddress
    {
        return $this->from;
    }

    public function getHeight(): int
    {
        return $this->getBottom() - $this->getTop() + 1;
    }

    public function getLeft(): int
    {
        return $this->from->columnId();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getNext(): ?Unit
    {
        return $this->next;
    }

    public function setNext(?Unit $next): self
    {
        $this->next = $next;
        $this->offsetNext = $next ? [
            $next->getLeft() - $this->getLeft(),
            $next->getTop() - $this->getTop(),
        ] : [0, 0];

        return $this;
    }

    public function getParent(): ?Unit
    {
        return $this->parent;
    }

    public function setParent(?Unit $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getPrev(): ?Unit
    {
        return $this->prev;
    }

    public function setPrev(?Unit $prev): self
    {
        $this->prev = $prev;

        return $this;
    }

    public function getRange(): string
    {
        return (string)$this->getCellRange();
    }

    public function getRight(): int
    {
        return $this->to->columnId();
    }

    public function getRoot(): Unit
    {
        $root = $this;
        while ($root->hasParent()) {
            $root = $root->getParent();
        }

        return $root;
    }

    public function getTo(): CellAddress
    {
        return $this->to;
    }

    public function getTop(): int
    {
        return $this->from->rowId();
    }

    public function getUnitTemplate(): UnitTemplate
    {
        return $this->unitTemplate;
    }

    public function getUnitType(): string
    {
        return $this->unitType;
    }

    public function setUnitType(string $unitType, ExprObjectInterface $unitModel, UnitTemplate $unitTemplate): self
    {
        $this->unitType = $unitType;
        $this->unitModel = $unitModel;
        $this->unitTemplate = $unitTemplate;

        return $this;
    }

    public function getWidth(): int
    {
        return $this->getRight() - $this->getLeft() + 1;
    }

    public function hasChild(): bool
    {
        return $this->child !== null;
    }

    public function hasCoordinate(array|string $coordinate): bool
    {
        if (is_array($coordinate)) {
            [$colIndex, $rowIndex] = $coordinate;
        } else {
            [$colIndex, $rowIndex] = Coordinate::indexesFromString($coordinate);
        }

        return $this->from->columnId() <= $colIndex && $this->to->columnId() >= $colIndex
            && $this->from->rowId() <= $rowIndex && $this->to->rowId() >= $rowIndex;
    }

    public function hasNext(Unit $unit): bool
    {
        return $this->next !== null;
    }

    public function hasParent(): bool
    {
        return $this->parent !== null;
    }

    public function hasPrev(Unit $unit): bool
    {
        return $this->prev !== null;
    }

    public function isContains(Unit $other): bool
    {
        return $this->getLeft() <= $other->getLeft() && $this->getRight() >= $other->getRight()
            && $this->getTop() <= $other->getTop() && $this->getBottom() >= $other->getBottom();
    }

    public function isHigherThan(Unit $other): bool
    {
        return $this->getTop() < $other->getTop() || ($this->getTop() === $other->getTop(
                ) && $this->getFrom() < $other->getFrom());
    }

    public function isIterableUnitType(): bool
    {
        return $this->unitType === DefaultInterface::class || in_array(
                IterableUnitInterface::class,
                class_implements($this->unitType)
            );
    }

    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function isLowerThan(Unit $other): bool
    {
        return $this->getTop() > $other->getTop() || ($this->getTop() === $other->getTop(
                ) && $this->getFrom() > $other->getFrom());
    }

    public function lock(): void
    {
        $this->locked = true;
    }

    public function offsetParent(): array
    {
        return $this->parent ? [
            $this->getLeft() - $this->parent->getLeft(),
            $this->getTop() - $this->parent->getTop(),
        ] : [$this->getLeft() - 1, $this->getTop() - 1];
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function setHidden(bool $hidden): void
    {
        $this->hidden = $hidden;
    }

    public function getOffsetNext(): array
    {
        return $this->offsetNext;
    }
}
