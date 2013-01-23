Symfony2 Mocker Extension
=========================

Behat extension for mocking services defined in the Symfony2 dependency
injection container.

Internally it uses `Mockery <https://github.com/padraic/mockery>`_ and
`SymfonyMockerContainer <https://github.com/PolishSymfonyCommunity/SymfonyMockerContainer>`_.

    .. note::

        Mocking services in acceptance tests is not always the best option, 
        but sometimes it is a necessity. Especially, if we don't have a possibility to use
        the service in a test mode to prevent interactions with the production environment.


Installation
------------

This extension requires:

* Behat 2.4+

Through PHAR
~~~~~~~~~~~~

First, download phar archives:

* `behat.phar <http://behat.org/downloads/behat.phar>`_ - Behat itself
* `symfony2_mocker_extension.phar <http://behat.org/downloads/symfony2_mocker_extension.phar>`_
  - Symfony2 mocker extension

After downloading and placing ``*.phar`` into project directory, you need to
activate ``Symfony2MockerExtension`` in your ``behat.yml``:

    .. code-block:: yaml

        default:
          # ...
          extensions:
            symfony2_mocker_extension.phar: ~


Through Composer
~~~~~~~~~~~~~~~~

The easiest way to keep your suite updated is to use `Composer <http://getcomposer.org>`_:

1. Define the dependencies in your `composer.json`:

    .. code-block:: js

        {
            "require": {
                ...

                "polishsymfonycommunity/symfony2-mocker-extension": "*"
            }
        }

2. Install/update your vendors:

    .. code-block:: bash

        $ curl http://getcomposer.org/installer | php
        $ php composer.phar install

3. Activate the extension in your ``behat.yml``:

    .. code-block:: yaml

        default:
            # ...
            extensions:
                PSS\Behat\Symfony2MockerExtension\Extension: ~

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

There are three ways you can use ``ServiceMocker`` in your contexts to mock
services:

* Implement the ``ServiceMockerAwareInterface``
* Extend the ``RawServiceMockerContext``
* Use ``ServiceMockerContext``

Implementing the ServiceMockerAwareInterface
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Implement ``PSS\Behat\Symfony2MockerExtension\Context\ServiceMockerAwareInterface``
and mocker will be injected into your context automatically:

    .. code-block:: php

        <?php

        namespace PSS\Features\Context;

        use Behat\Behat\Context\BehatContext;
        use PSS\Behat\Symfony2MockerExtension\Context\ServiceMockerAwareInterface;
        use PSS\Behat\Symfony2MockerExtension\ServiceMocker;

        class AcmeContext extends BehatContext implements ServiceMockerAwareInterface
        {
            /**
             * @var ServiceMocker $mocker
             */
            private $mocker = null;

            /**
             * @param ServiceMocker $mocker
             */
            public function setServiceMocker(ServiceMocker $mocker)
            {
                $this->mocker = $mocker;
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

Extending the RawServiceMockerContext
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Alternatively, extend the ``PSS\Behat\Symfony2MockerExtension\Context\RawServiceMocker``
and call the mocker with the ``mockService()`` method:

    .. code-block:: php

        <?php

        namespace PSS\Features\Context;

        use PSS\Behat\Symfony2MockerExtension\Context\RawServiceMocker;

        class AcmeContext extends RawServiceMockerContext
        {
            /**
             * @Given /^CRM API is available$/
             */
            public function crmApiIsAvailable()
            {
                $this->mockService('crm.client', 'PSS\Crm\Client')
                    ->shouldReceive('send')
                    ->once()
                    ->andReturn(true);
            }
        }

Using ServiceMockerContext
~~~~~~~~~~~~~~~~~~~~~~~~~~

Extending ``ServiceMockerContext`` is not recommended as it can only be extend
once.

Most of the time you'd rather want to include it as a subcontext:

    .. code-block:: php

        <?php

        namespace PSS\Features\Context;

        use Behat\Behat\Context\BehatContext;
        use PSS\Behat\Symfony2MockerExtension\Context\ServiceMockerContext;

        class FeatureContext extends RawServiceMockerContext
        {
            /**
             * @return null
             */
            public function __construct()
            {
                $this->useContext('service_mocker', new ServiceMockerContext());
            }
        }

``ServiceMockerContext`` can be used just like ``RawServiceMockerContext`` but
it additionally provides a step to verify Mockery expectations. Most of the
time you'd want to use it internally in other steps:

    .. code-block:: php

        <?php

        /**
         * @Given /^(the )?contact request should be sent to (the )?CRM$/
         *
         * @return null
         */
        public function theContactRequestShouldBeSentToCrm()
        {
            return new Then('the "crm.client" service should meet my expectations');
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

        use Behat\Behat\Context\BehatContext;
        use PSS\Behat\Symfony2MockerExtension\Context\ServiceMockerContext;

        class AcmeContext extends RawServiceMockerContext
        {
            /**
             * @Given /^CRM API is available$/
             */
            public function crmApiIsAvailable()
            {
                $this->getMainContext()->getSubContext('container')
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
                return new Then('the "crm.client" service should meet my expectations');
            }
        }

All the expectations are checked automatically with ``afterScenario`` and
``afterOutlineExample`` hooks. Doing it manually only improves the readability
of the scenario and outputs a better error message.
