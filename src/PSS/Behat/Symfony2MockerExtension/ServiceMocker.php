<?php

namespace PSS\Behat\Symfony2MockerExtension;

use Behat\Mink\Mink;
use Behat\Symfony2Extension\Driver\KernelDriver;
use Prophecy\Prophet;
use PSS\SymfonyMockerContainer\DependencyInjection\MockerContainer;
use Symfony\Component\HttpKernel\KernelInterface;

class ServiceMocker
{
    /**
     * @var KernelInterface|null
     */
    private $kernel = null;

    /**
     * @var Mink|null
     */
    private $mink = null;

    /**
     * @param KernelInterface $kernel
     * @param Mink            $mink
     */
    public function __construct(KernelInterface $kernel, Mink $mink)
    {
        $this->kernel = $kernel;
        $this->mink   = $mink;
    }

    /**
     * @return \Prophecy\Prophet
     */
    public function mockService()
    {
        return call_user_func_array(array($this->getMockerContainer(), 'mock'), func_get_args());
    }

    /**
     * @return MockerContainer
     * @throws \LogicException when used with not supported driver or container cannot create mocks
     */
    public function getMockerContainer()
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

    /**
     * @param string $serviceId
     *
     * @throws ExpectationException
     */
    public function verifyServiceExpectationsById($serviceId)
    {
        $container = $this->getMockerContainer();
        $service   = $container->get($serviceId);

        $this->verifyServiceExpectations($service);
    }

    /**
     * @param Prophet $service
     */
    public function verifyServiceExpectations(Prophet $service)
    {
        $service->checkPredictions();
    }

    /**
     * @return null
     */
    public function verifyPendingExpectations()
    {
        if (!$this->isKernelDriverUsed()) {
            return;
        }

        $container      = $this->getMockerContainer();
        $mockedServices = $container->getMockedServices();

        foreach ($mockedServices as $id => $service) {
            $this->verifyServiceExpectations($service);
            $container->unmock($id);
        }
    }
}
