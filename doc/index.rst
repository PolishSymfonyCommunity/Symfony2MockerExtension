Symfony2 Mocker Extension
=========================

Behat extension for mocking services defined in the Symfony2 dependency
injection container.

**Use it sparingly. Mocking a service container is not a good practice.
Most of the time if there's a need for this,
the problem can be better solved by improving the design instead.**

Internally it uses `Mockery <https://github.com/padraic/mockery>`_ and
`SymfonyMockerContainer <https://github.com/PolishSymfonyCommunity/SymfonyMockerContainer>`_.

    .. note::

        Mocking services in acceptance tests is not always the best option, 
        but sometimes it is a necessity. Especially, if we don't have a possibility to use
        the service in a test mode to prevent interactions with the production environment.


Installation
------------

This extension requires:

* Behat 3.0+

1. Install the extension:

    .. code-block:: bash

        $ composer require polishsymfonycommunity/symfony2-mocker-extension

2. Activate it in ``behat.yml``:

    .. code-block:: yaml

        default:
            # ...

            extensions:
                Behat\MinkExtension:
                    sessions:
                        default:
                            symfony2: ~
                Behat\Symfony2Extension: ~
                PSS\Behat\Symfony2MockerExtension: ~

Enabling the mocker container
-----------------------------

The base container class for the test environment needs to be replaced in
``app/AppKernel.php``:

    .. code-block:: php

        <?php

        /**
         * @return string
         */
        protected function getContainerBaseClass()
        {
            if ('test' == $this->environment) {
                return '\PSS\SymfonyMockerContainer\DependencyInjection\MockerContainer';
            }

            return parent::getContainerBaseClass();
        }

    .. note::

        Don't forget to clear your cache.

Usage
-----

Simply add the ``ServiceMocker`` to the list of constructor arguments in the context file:

    .. code-block:: php

        use Behat\Behat\Context\Context;
        use PSS\Behat\Symfony2MockerExtension\ServiceMocker;

        final class FeatureContext implements Context
        {
            /**
             * @var ServiceMocker
             */
            private $serviceMocker;

            /**
             * @param ServiceMocker $serviceMocker
             */
            public function __construct(ServiceMocker $serviceMocker)
            {
                $this->serviceMocker = $serviceMocker;
            }

            /**
             * @Given /^CRM API is available$/
             */
            public function crmApiIsAvailable()
            {
                $this->mocker->mockService('crm.client', 'PSS\Crm\Client')
                    ->shouldReceive('send')
                    ->once()
                    ->andReturn(true);
            }
        }

Example story
-------------

Imagine you're working on a following feature:

    .. code-block:: yaml

        Feature: Submitting contact request form
          As a Visitor
          I want to contact sales
          In order to receive more information

          Scenario: Submitting the form
            When I go to "/contact-us"
             And I complete the contact us form with following information
               |First name|Last name|Email                |
               |Jakub     |Zalas    |jzalas+spam@gmail.com|
             And CRM API is available
             And I submit the contact us form
            Then the contact request should be sent to the CRM

You probably wouldn't like your CRM API to be hit every time scenarios are run.
One way of solving this issue is to mock the service and only verify if it was called:

    .. code-block:: php

        <?php

        namespace PSS\Features\Context;

        use Behat\Behat\Context\Context;
        use PSS\Behat\Symfony2MockerExtension\ServiceMocker;

        class AcmeContext implements Context
        {
            /**
             * @var ServiceMocker
             */
            private $serviceMocker;

            /**
             * @param ServiceMocker $serviceMocker
             */
            public function __construct(ServiceMocker $serviceMocker)
            {
                $this->serviceMocker = $serviceMocker;
            }

            /**
             * @Given /^CRM API is available$/
             */
            public function crmApiIsAvailable()
            {
                $this->serviceMocker
                    ->mockService('crm.client', 'PSS\Crm\Client')
                    ->shouldReceive('send')
                    ->once()
                    ->andReturn(true);
            }

            /**
             * @Given /^(the )?contact request should be sent to (the )?CRM$/
             */
            public function theContactRequestShouldBeSentToCrm()
            {
                $this->serviceMocker->verifyServiceExpectationsById('crm.client');
            }
        }

All the expectations are checked automatically with ``afterScenario`` and
``afterOutlineExample`` hooks. Doing it manually only improves the readability
of the scenario and outputs a better error message.
