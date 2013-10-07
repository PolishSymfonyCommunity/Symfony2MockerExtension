<?php

namespace PSS\Behat\Symfony2MockerExtension\Context;

use Behat\Behat\Context\BehatContext;
use PSS\Behat\Symfony2MockerExtension\ServiceMocker;
use PSS\Behat\Symfony2MockerExtension\Context\ServiceMockerAwareInterface;

class RawServiceMockerContext extends BehatContext implements ServiceMockerAwareInterface
{
    /**
     * @var ServiceMocker $serviceMocker
     */
    private $serviceMocker = null;

    /**
     * @param $id               Service Id
     * @param $classOrInterface Class or Interface name
     *
     * @return ObjectProphecy
     */
    public function mockService($id, $classOrInterface)
    {
        return $this->serviceMocker->mockService($id, $classOrInterface);
    }

    /**
     * @return ServiceMocker
     */
    protected function getServiceMocker()
    {
        return $this->serviceMocker;
    }

    /**
     * @param ServiceMocker $serviceMocker
     */
    public function setServiceMocker(ServiceMocker $serviceMocker)
    {
        $this->serviceMocker = $serviceMocker;
    }
}
