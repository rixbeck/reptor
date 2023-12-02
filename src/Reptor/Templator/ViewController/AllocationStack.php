<?php
/**
 * @author Rix Beck <rix@neologik.hu>
 */

declare(strict_types=1);

namespace brix\Reptor\Templator\ViewController;

use brix\Reptor\Templator\Event\NextRowEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AllocationStack extends \SplStack
{
    public function __construct(protected EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher->addListener(NextRowEvent::class, function () {
            $this->apply();
        });
    }

    public function apply()
    {
        while (!$this->isEmpty()) {
            ($this->pop())();
        }
    }
}
