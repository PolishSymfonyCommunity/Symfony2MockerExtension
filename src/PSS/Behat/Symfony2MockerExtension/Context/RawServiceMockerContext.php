<?php

namespace PSS\Behat\Symfony2MockerExtension\Context;

use Behat\Behat\Context\BehatContext;
use PSS\Behat\Symfony2MockerExtension\ServiceMocker;
use PSS\Behat\Symfony2MockerExtension\Context\ServiceMockerAwareInterface;

class RawServiceMockerContext extends BehatContext implements ServiceMockerAwareInterface
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
    public function setServiceMocker(ServiceMocker $serviceMocker)
    {
        $this->serviceMocker = $serviceMocker;
    }

    /**
     * @return \PSS\Behat\Symfony2MockerExtension\ServiceMocker
     */
    protected function getServiceMocker()
    {
        return $this->serviceMocker;
    }

    /**
     * @return \Mockery\Mock
     */
    protected function mockService()
    {
        return call_user_func_array(array($this->serviceMocker, 'mockService'), func_get_args());
    }
}
