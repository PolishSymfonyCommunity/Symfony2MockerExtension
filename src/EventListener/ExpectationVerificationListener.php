<?php

namespace PSS\Behat\Symfony2MockerExtension\EventListener;

use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use PSS\Behat\Symfony2MockerExtension\ServiceMocker;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ExpectationVerificationListener implements EventSubscriberInterface
{
    /**
     * @var ServiceMocker
     */
    private $serviceMocker;

    /**
     * @param ServiceMocker $serviceMocker
     */
    public function __construct(ServiceMocker $serviceMocker)
    {
        $this->serviceMocker = $serviceMocker;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ScenarioTested::AFTER  => ['verifyPendingExpectations', 0],
            ExampleTested::AFTER   => ['verifyPendingExpectations', 0],
        ];
    }

    public function verifyPendingExpectations()
    {
        $this->serviceMocker->verifyPendingExpectations();
    }
}