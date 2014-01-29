<?php

namespace PSS\Behat\Symfony2MockerExtension;

use Behat\Mink\Mink;
use Behat\Symfony2Extension\Driver\KernelDriver;
use PSS\SymfonyMockerContainer\DependencyInjection\MockerContainerInterface;
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
     * @param string $id               Service Id
     * @param string $classOrInterface Class or Interface name
     *
     * @return object
     */
    public function mockService($id, $classOrInterface)
    {
        return $this->getMockerContainer()->mock($id, $classOrInterface);
    }

    /**
     * @return MockerContainerInterface
     * @throws \LogicException when used with not supported driver or container cannot create mocks
     */
    public function getMockerContainer()
    {
        if ($this->isKernelDriverUsed()) {
            $container = $this->kernel->getContainer();

            if (!$container instanceof MockerContainerInterface) {
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
     * @return null
     */
    public function verifyPendingExpectations()
    {
        if (!$this->isKernelDriverUsed()) {
            return;
        }

        $container      = $this->getMockerContainer();
        $mockedServices = $container->getMockedServices();

        foreach (array_keys($mockedServices) as $id) {
            $this->verifyServiceExpectationsById($id);
            $container->unmock($id);
        }
    }

    /**
     * @param string $serviceId
     */
    public function verifyServiceExpectationsById($serviceId)
    {
        $this->getMockerContainer()->verifyServiceExpectationsById($serviceId);
    }
}
