<?php

namespace PSS\Behat\Symfony2MockerExtension\Context\Initializer;

use Behat\Behat\Context\Initializer\InitializerInterface;
use Behat\Behat\Context\ContextInterface;
use PSS\Behat\Symfony2MockerExtension\ServiceMocker;
use PSS\Behat\Symfony2MockerExtension\Context\ServiceMockerAwareInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ServiceMockerInitializer implements InitializerInterface, EventSubscriberInterface
{
    /**
     * @var \PSS\Behat\Symfony2MockerExtension\ServiceMocker $serviceMocker
     */
    private $serviceMocker = null;

    /**
     * @param \PSS\Behat\Symfony2MockerExtension\ServiceMocker $serviceMocker
     *
     * @return null
     */
    public function __construct(ServiceMocker $serviceMocker)
    {
        $this->serviceMocker = $serviceMocker;
    }

    /**
     * @param \Behat\Behat\Context\ContextInterface $context
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
     * @param Behat\Behat\Context\ContextInterface $context
     *
     * @return null
     */
    public function initialize(ContextInterface $context)
    {
        $context->setServiceMocker($this->serviceMocker);
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            'afterScenario' => 'verifyPendingExpectations',
            'afterOutlineExample' => 'verifyPendingExpectations'
        );
    }

    /**
     * @param \Behat\Behat\Event\ScenarioEvent|\Behat\Behat\Event\OutlineEvent $event
     *
     * @return null
     */
    public function verifyPendingExpectations($event)
    {
        $this->serviceMocker->verifyPendingExpectations();
    }
}
