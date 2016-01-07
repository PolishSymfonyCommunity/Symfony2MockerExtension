<?php

use Behat\Behat\Context\Context;
use Mockery\CountValidator\Exception;
use PSS\Behat\Symfony2MockerExtension\ServiceMocker;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

final class FeatureContext implements Context
{
    /**
     * @var ServiceMocker
     */
    private $serviceMocker;

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @param ServiceMocker $serviceMocker
     */
    public function __construct(ServiceMocker $serviceMocker, KernelInterface $kernel)
    {
        $this->serviceMocker = $serviceMocker;
        $this->kernel = $kernel;
    }

    /**
     * @Given I am working on an application that sends e-mails
     */
    public function iAmWorkingOnAnApplicationThatSendsEMails()
    {
    }

    /**
     * @When I mock the mailer in my test
     */
    public function iMockTheMailerInMyTest()
    {
        $this->serviceMocker->mockService('mailer', ServiceMocker::class)
            ->shouldReceive('send')
            ->once();
    }

    /**
     * @When I call the action that would call the mailer
     */
    public function iCallTheActionThatWouldCallTheMailer()
    {
        $this->kernel->handle(Request::create('/send-mail'));
    }

    /**
     * @Then the mock expectations should pass
     */
    public function theMockExpectationsShouldPass()
    {
        $this->serviceMocker->verifyServiceExpectationsById('mailer');
    }

    /**
     * @When I call the action that would not call the mailer
     */
    public function iWonTCallTheActionThatWouldCallTheMailer()
    {
        $this->kernel->handle(Request::create('/'));
    }

    /**
     * @Then the mock expectations should fail
     */
    public function theMockExpectationsShouldFail()
    {
        try {
            $this->serviceMocker->verifyServiceExpectationsById('mailer');
        } catch (Exception $e) {
            return;
        }

        throw new \LogicException('The service expectations did not fail.');
    }
}
