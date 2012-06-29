<?php

namespace PSS\Behat\Symfony2MockerExtension\Context;

use PSS\Behat\Symfony2MockerExtension\ServiceMocker;

interface ServiceMockerAwareInterface
{
    /**
     * @param \PSS\Behat\Symfony2MockerExtension\ServiceMocker $serviceMocker
     *
     * @return null
     */
    public function setServiceMocker(ServiceMocker $serviceMocker);
}
