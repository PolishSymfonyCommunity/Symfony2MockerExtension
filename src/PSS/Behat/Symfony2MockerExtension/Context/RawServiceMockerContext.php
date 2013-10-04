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

    /**
     * @return \Prophecy\Prophet
     */
    protected function mockService()
    {
        return call_user_func_array(array($this->serviceMocker, 'mockService'), func_get_args());
    }
}
