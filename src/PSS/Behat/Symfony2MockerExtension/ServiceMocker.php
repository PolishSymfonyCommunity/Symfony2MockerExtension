<?php

namespace PSS\Behat\Symfony2MockerExtension;

use Behat\Mink\Mink;
use Behat\Symfony2Extension\Driver\KernelDriver;
use PSS\SymfonyMockerContainer\DependencyInjection\MockerContainer;
use Symfony\Component\HttpKernel\KernelInterface;

class ServiceMocker
{
    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface $kernel
     */
    private $kernel = null;

    /**
     * @var \Behat\Mink\Mink $mink
     */
    private $mink = null;

    /**
     * @param \Symfony\Component\HttpKernel\KernelInterface $kernel
     * @param \Behat\Mink\Mink                              $mink
     *
     * @return null
     */
    public function __construct(KernelInterface $kernel, Mink $mink)
    {
        $this->kernel = $kernel;
        $this->mink = $mink;
    }

    /**
     * @return \Mockery\Mock
     */
    public function mockService()
    {
        return call_user_func_array(array($this->getMockerContainer(), 'mock'), func_get_args());
    }

    /**
     * @return null
     */
    public function verifyPendingExpectations()
    {
        if (!$this->isKernelDriverUsed()) {
            return;
        }

        $container = $this->getMockerContainer();
        $mockedServices = $container->getMockedServices();

        foreach ($mockedServices as $id => $service) {
            $this->verifyServiceExpectations($service);
            $container->unmock($id);
        }
    }

    /**
     * @param object $service
     *
     * @throws \Behat\Mink\Exception\ExpectationException
     *
     * @return null
     */
    public function verifyServiceExpectations($service)
    {
        try {
            $service->mockery_verify();
        } catch (CountValidatorException $exception) {
            throw new ExpectationException('One of the expected services was not called', $this->mink->getSession(), $exception);
        }
    }

    /**
     * @param integer $serviceId
     *
     * @throws \Behat\Mink\Exception\ExpectationException
     *
     * @return null
     */
    public function verifyServiceExpectationsById($serviceId)
    {
        $container = $this->getMockerContainer();
        $service = $container->get($serviceId);

        $this->verifyServiceExpectations($service);
    }

    /**
     * @throws \LogicException when used with not supporteddriver or container cannot create mocks
     *
     * @return \Symfony\Component\DependencyInjection\Container
     */
    protected function getMockerContainer()
    {
        if ($this->isKernelDriverUsed()) {
            $container = $this->kernel->getContainer();

            if (!$container instanceof MockerContainer) {
                throw new \LogicException('Container is not able to mock the services');
            }

            return $container;
        }

        throw new \LogicException('Session has no access to client container');
    }

    /**
     * @return boolean
     */
    protected function isKernelDriverUsed()
    {
        $driver = $this->mink->getSession()->getDriver();

        return $driver instanceof KernelDriver;
    }
}
