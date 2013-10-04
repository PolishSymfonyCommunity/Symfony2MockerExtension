<?php

namespace spec\PSS\Behat\Symfony2MockerExtension\Context\Initializer;

use Behat\Behat\Context\ContextInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSS\Behat\Symfony2MockerExtension\Context\ServiceMockerAwareInterface;
use PSS\Behat\Symfony2MockerExtension\ServiceMocker;
use Symfony\Component\EventDispatcher\Event;

class ServiceMockerInitializerSpec extends ObjectBehavior
{
    function let(ServiceMocker $serviceMocker)
    {
        $this->beConstructedWith($serviceMocker);
    }

    function it_subscribes_behat_events()
    {
        $this->getSubscribedEvents()->shouldReturn(
            array(
                'afterScenario'       => 'verifyPendingExpectations',
                'afterOutlineExample' => 'verifyPendingExpectations'
            )
        );
    }

    function it_verifies_expectations(Event $event, ServiceMocker $serviceMocker)
    {
        $this->verifyPendingExpectations($event);
        $serviceMocker->verifyPendingExpectations()->shouldHaveBeenCalled();
    }

    function it_supports_service_mocker_aware_context(ServiceMockerAwareContext $context)
    {
        $this->supports($context)->shouldReturn(true);
    }

    function it_doesnt_support_other_contexts(ContextInterface $context)
    {
        $this->supports($context)->shouldReturn(false);
    }

    function it_initialises_context_with_service_mocker(ServiceMockerAwareContext $context, ServiceMocker $serviceMocker)
    {
        $this->initialize($context);
        $context->setServiceMocker($serviceMocker)->shouldHaveBeenCalled();
    }
}

class ServiceMockerAwareContext implements ContextInterface, ServiceMockerAwareInterface
{
    public function setServiceMocker(ServiceMocker $serviceMocker){}
}
