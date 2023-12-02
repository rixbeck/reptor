<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Templator\ExprObject\Aggregator;

use brix\Reptor\ExpressionLanguage\ExpressionLanguage;
use brix\Reptor\Templator\Context\CellRenderContext;
use brix\Reptor\Templator\Context\ContextProvider;
use brix\Reptor\Templator\Event\NextDataRowEvent;
use brix\Reptor\Templator\ExprObject\Interface\AggregatorInterface;
use brix\Reptor\Templator\ExprObject\Interface\ExprObjectInterface;
use brix\Reptor\Templator\ExprObject\Type\GroupByInterface;
use brix\Reptor\Templator\Tokenizer;
use brix\Reptor\Templator\ViewController\AggregateViewController;
use brix\Reptor\Templator\ViewController\ViewControllerFactory;
use brix\Reptor\Templator\ViewController\ViewControllerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class AbstractAggregator implements ExprObjectInterface, AggregatorInterface
{
    protected mixed $subject;

    protected mixed $value = 0;
    protected ?ViewControllerInterface $viewController = null;
    protected \Closure $eventHandler;
    /**
     * @var mixed|null
     */
    protected ?GroupByInterface $aggregationBase;
    protected mixed $groupingValue = null;

    public function __construct(
        protected ViewControllerFactory $viewControllerFactory,
        protected EventDispatcherInterface $eventDispatcher,
        protected ExpressionLanguage $expressionLanguage,
        protected ContextProvider $contextProvider,
        GroupByInterface $aggregationBase = null,
    ) {
        $ownRenderContext = clone $this->contextProvider->getCellRenderContext();
        $this->aggregationBase = $aggregationBase;
        $this->prepareAggregation();

        $this->eventDispatcher->addListener(
            NextDataRowEvent::class,
            $this->eventHandler = fn(NextDataRowEvent $event) => $this->renderCalculation($ownRenderContext)
        );
    }

    public function __toString(): string
    {
        return (string)$this->value;
    }

    public function exprObject(): self
    {
        return $this;
    }

    abstract public function getType(): string;

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getViewController(): ViewControllerInterface
    {
        return $this->viewController ??= $this->viewControllerFactory->create(AggregateViewController::class);
    }

    abstract public function calculation(mixed $subject): void;

    protected function prepareAggregation(): void
    {
        $this->groupingValue = $this->aggregationBase?->exprObject()[$this->aggregationBase->getFieldName()] ?? null;
    }

    protected function renderCalculation(CellRenderContext $ownRenderContext): void
    {
        if ((!$this->aggregationBase)
            || $this->aggregationBase
            && $this->aggregationBase->exprObject()[$this->aggregationBase->getFieldName()] === $this->groupingValue) {

            $savedContext = $this->contextProvider
                ->setCellRenderContext($ownRenderContext);
            $token = $ownRenderContext->unitTemplate->getTemplate();
            $result = $this->expressionLanguage->evaluate(
                Tokenizer::tokenToExpression($token),
                $this->contextProvider->getExpressionContext(),
            );
            $this->calculation($this->subject);
            $this->subject = null;
            $this->exprObject()->getViewController()->renderByEvent($ownRenderContext, $this);
            $this->contextProvider
                ->setCellRenderContext($savedContext);
        }
    }

    public function setSubject(mixed $subject): self
    {
        $this->subject = $subject;

        return $this;
    }
}
