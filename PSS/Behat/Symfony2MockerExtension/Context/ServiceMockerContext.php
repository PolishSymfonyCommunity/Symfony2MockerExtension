<?php

namespace PSS\Behat\Symfony2MockerExtension\Context;

class ServiceMockerContext extends RawServiceMockerContext
{
    /**
     * This step is not meant to be used directly in your scenarios.
     * You should rather build your steps upon it with \Behat\Behat\Context\Step\Then class:
     *
     *     return new \Behat\Behat\Context\Step\Then('the "user.service" should meet my expectations');
     *
     * @Given /^(the )?"(?P<serviceId>(?:[^"])*)" service should meet my expectations$/
     *
     * @return null
     */
    public function theServiceShouldMeetMyExpectations($serviceId)
    {
        $this->getServiceMocker()->verifyServiceExpectationsById($serviceId);
    }
}
