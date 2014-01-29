<?php

namespace PSS\Behat\Symfony2MockerExtension\Context\Initializer;

use Behat\Behat\Context\Initializer\InitializerInterface;
use Behat\Behat\Context\ContextInterface;
use Behat\Behat\Event\OutlineEvent;
use Behat\Behat\Event\ScenarioEvent;
use PSS\Behat\Symfony2MockerExtension\ServiceMocker;
use PSS\Behat\Symfony2MockerExtension\Context\ServiceMockerAwareInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ServiceMockerInitializer implements InitializerInterface, EventSubscriberInterface
{
    /**
     * @var ServiceMocker $serviceMocker
     */
    private $serviceMocker = null;

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
        return array(
            'afterScenario'       => 'verifyPendingExpectations',
            'afterOutlineExample' => 'verifyPendingExpectations'
        );
    }

    /**
     * @param ContextInterface $context
     *
     * @return boolean
     */
    public function supports(ContextInterface $context)
    {
        if ($context instanceof ServiceMockerAwareInterface) {
            return true;
        }

        return false;
    }

    /**
     * @param ContextInterface $context
     */
    public function initialize(ContextInterface $context)
    {
        $context->setServiceMocker($this->serviceMocker);
    }

    /**
     * @param ScenarioEvent|OutlineEvent $event
     */
    public function verifyPendingExpectations(Event $event)
    {
        $this->serviceMocker->verifyPendingExpectations();
    }
}
