<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Templator\ExprObject\Aggregator;

use brix\Reptor\ExpressionLanguage\ExpressionLanguage;
use brix\Reptor\Templator\Context\ContextProvider;
use brix\Reptor\Templator\ExprObject\Interface\AggregatorInterface;
use brix\Reptor\Templator\ExprObject\Type\GroupByInterface;
use brix\Reptor\Templator\ViewController\ViewControllerFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AggregatorFactory
{
    protected array $aggregators = [];
    protected ExpressionLanguage $expressionLanguage;

    public function __construct(
        protected ViewControllerFactory $viewControllerFactory,
        protected EventDispatcherInterface $eventDispatcher,
        protected ContextProvider $contextProvider,
    ) {
    }

    public function configureExpressionLanguage(ExpressionLanguage $expressionLanguage)
    {
        $this->expressionLanguage = $expressionLanguage;
    }

    public function create(string $className, ...$args): AggregatorInterface
    {
        return new $className(
            $this->viewControllerFactory,
            $this->eventDispatcher,
            $this->expressionLanguage,
            $this->contextProvider,
            ...$args
        );
    }

    public function getInstance(
        string $className,
        mixed $subject,
        GroupByInterface $aggregationBase = null
    ): AggregatorInterface {
        $unitTemplate = $this->contextProvider
            ->getCellRenderContext()
            ->unitTemplate;
        if ($aggregationBase) {
            $baseObject = $aggregationBase->exprObject();
            $fieldName = $aggregationBase->getFieldName();
            $distinctionValue = base64_encode((string)$baseObject[$fieldName] ?? '');
            $index = implode(
                '_',
                [$className, spl_object_id($unitTemplate), spl_object_id($baseObject), $fieldName, $distinctionValue]
            );
        } else {
            $index = implode('_', [$className, spl_object_id($unitTemplate)]);
        }
        if (isset($this->aggregators[$index])) {
            return $this->aggregators[$index]->setSubject($subject);
        }

        $aggregator = $this->create($className, $aggregationBase)->setSubject($subject);
        $this->aggregators[$index] = $aggregator;

        return $aggregator;
    }
}
