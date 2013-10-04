<?php

namespace PSS\Behat\Symfony2MockerExtension\Context;

use PSS\Behat\Symfony2MockerExtension\ServiceMocker;

interface ServiceMockerAwareInterface
{
    /**
     * @param ServiceMocker $serviceMocker
     *
     * @return mixed
     */
    public function setServiceMocker(ServiceMocker $serviceMocker);
}
