Symfony2 Mocker Extension
=========================

[![Build Status](https://secure.travis-ci.org/PolishSymfonyCommunity/Symfony2MockerExtension.png?branch=master)](http://travis-ci.org/PolishSymfonyCommunity/Symfony2MockerExtension)

Behat extension for mocking services defined in the Symfony2 dependency
injection container.

Internally it uses [Mockery](https://github.com/padraic/mockery) and [SymfonyMockerContainer](https://github.com/PolishSymfonyCommunity/SymfonyMockerContainer).

Installation
------------

Add the extension to your composer.json:

```js
{
    "require": {
        "polishsymfonycommunity/symfony2-mocker-extension": "*"
    }
}
```

Replace the base container class for test environment in `app/AppKernel.php`:

```php
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
```

Clear your cache.

Enable the extension in your `behat.yml`:

```yaml
default:
  extensions:
    PSS\Behat\Symfony2MockerExtension\Extension:
```

Usage
-----

There are three ways you can use `ServiceMocker` in your contexts to mock
services:
* Implement the `ServiceMockerAwareInterface`
* Extend the `RawServiceMockerContext`
* Use `ServiceMockerContext`

### Implementing the ServiceMockerAwareInterface ###

```php
<?php

namespace PSS\Features\Context;

use Behat\Behat\Context\BehatContext;
use PSS\Behat\Symfony2MockerExtension\Context\ServiceMockerAwareInterface;
use PSS\Behat\Symfony2MockerExtension\ServiceMocker;

class AcmeContext extends BehatContext implements ServiceMockerAwareInterface
{
    /**
     * @var \PSS\Behat\Symfony2MockerExtension\ServiceMocker $mocker
     */
    private $mocker = null;

    /**
     * @param \PSS\Behat\Symfony2MockerExtension\ServiceMocker $mocker
     *
     * @return null
     */
    public function setServiceMocker(ServiceMocker $mocker)
    {
        $this->mocker = $mocker;
    }

    /**
     * @Given /^CRM API is available$/
     *
     * @return null
     */
    public function crmApiIsAvailable()
    {
        $this->mocker->mockService('crm.client', 'PSS\Crm\Client')
            ->shouldReceive('send')
            ->once()
            ->andReturn(true);
    }
}
```

### Extending the RawServiceMockerContext ###

```php
<?php

namespace PSS\Features\Context;

use PSS\Behat\Symfony2MockerExtension\Context\RawServiceMocker

class AcmeContext extends RawServiceMockerContext
{
    /**
     * @Given /^CRM API is available$/
     *
     * @return null
     */
    public function crmApiIsAvailable()
    {
        $this->mockService('crm.client', 'PSS\Crm\Client')
            ->shouldReceive('send')
            ->once()
            ->andReturn(true);
    }
}
```

### Using ServiceMockerContext ###

Extending `ServiceMockerContext` is not recommended as it can only be extend once.

Most of the time you'd rather want to include it as a subcontext:

```php
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
```

`ServiceMockerContext` can be used just like `RawServiceMockerContext` but it additionally 
provides a step to verify Mockery expectations. Most of the time you'd want to
use it internally in other steps:

```php
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
```

Example story
-------------

```yaml
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
```

```php
<?php

namespace PSS\Features\Context;

use Behat\Behat\Context\BehatContext;
use PSS\Behat\Symfony2MockerExtension\Context\ServiceMockerContext;

class AcmeContext extends RawServiceMockerContext
{
    /**
     * @Given /^CRM API is available$/
     *
     * @return null
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
     *
     * @return null
     */
    public function theContactRequestShouldBeSentToCrm()
    {
        return new Then('the "crm.client" service should meet my expectations');
    }
}
```

All the expectations are checked automatically with `afterScenario` and
`afterOutlineExample` hooks. Doing it manually only improves the readability
of the scenario and outputs a better error message.

