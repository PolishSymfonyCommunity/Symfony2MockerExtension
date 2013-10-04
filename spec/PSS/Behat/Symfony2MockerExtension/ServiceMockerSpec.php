<?php

namespace spec\PSS\Behat\Symfony2MockerExtension;

use Behat\Mink\Mink;
use Behat\Mink\Session;
use Behat\Symfony2Extension\Driver\KernelDriver;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Prophecy\Prophet;
use PSS\SymfonyMockerContainer\DependencyInjection\MockerContainer;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\KernelInterface;

class ServiceMockerSpec extends ObjectBehavior
{
    function let(KernelInterface $kernel, Mink $mink)
    {
        $this->beConstructedWith($kernel, $mink);
    }

    function it_returns_mocker_container_when_present(KernelInterface $kernel, Mink $mink, Session $session, KernelDriver $driver, MockerContainer $mockerContainer)
    {
        $mink->getSession()->willReturn($session);
        $session->getDriver()->willreturn($driver);
        $kernel->getContainer()->willReturn($mockerContainer);

        $this->getMockerContainer()->shouldReturn($mockerContainer);
    }

    function it_throws_an_exception_if_container_is_not_an_instance_of_mocker_container(KernelInterface $kernel, Mink $mink, Session $session, KernelDriver $driver, Container $mockerContainer)
    {
        $mink->getSession()->willReturn($session);
        $session->getDriver()->willreturn($driver);
        $kernel->getContainer()->willReturn($mockerContainer);

        $logicException = new \LogicException('Container is not able to mock the services');
        $this->shouldThrow($logicException)->duringGetMockerContainer();
    }

    function it_throws_an_exception_if_driver_is_not_an_instance_of_kernel_container(Mink $mink, Session $session)
    {
        $mink->getSession()->willReturn($session);
        $session->getDriver()->willreturn(new \StdClass);

        $logicException = new \LogicException('Session has no access to client container');
        $this->shouldThrow($logicException)->duringGetMockerContainer();
    }

    function it_verifies_service_expectation_by_id(KernelInterface $kernel, Mink $mink, Session $session, KernelDriver $driver, MockerContainer $mockerContainer, Prophet $service)
    {
        $mink->getSession()->willReturn($session);
        $session->getDriver()->willreturn($driver);
        $kernel->getContainer()->willReturn($mockerContainer);
        $mockerContainer->get('service_id')->willReturn($service);

        $this->verifyServiceExpectationsById('service_id');

        $service->checkPredictions()->shouldHaveBeenCalled();
    }

    function it_verifies_all_pending_expectations(KernelInterface $kernel, Mink $mink, Session $session, KernelDriver $driver, MockerContainer $mockerContainer, Prophet $service1, Prophet $service2)
    {
        $services = array('service1' => $service1, 'service2' => $service2);

        $mink->getSession()->willReturn($session);
        $session->getDriver()->willreturn($driver);
        $kernel->getContainer()->willReturn($mockerContainer);
        $mockerContainer->getMockedServices()->willReturn($services);

        $mockerContainer->unmock('service1')->shouldBeCalled();
        $mockerContainer->unmock('service2')->shouldBeCalled();
        $service1->checkPredictions()->shouldBeCalled();
        $service2->checkPredictions()->shouldBeCalled();

        $this->verifyPendingExpectations();
    }

    function it_proxy_mocks_to_container(KernelInterface $kernel, Mink $mink, Session $session, KernelDriver $driver, MockerContainer $mockerContainer, Prophet $service)
    {
        $mink->getSession()->willReturn($session);
        $session->getDriver()->willreturn($driver);
        $kernel->getContainer()->willReturn($mockerContainer);

        $mockerContainer->mock('service_id', $service)->shouldBeCalled();

        $this->mockService('service_id', $service);
    }
}
