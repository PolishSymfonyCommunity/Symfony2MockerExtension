<?php

namespace PSS\Behat\Symfony2MockerExtension\ServiceContainer;

use Behat\Behat\EventDispatcher\ServiceContainer\EventDispatcherExtension;
use Behat\MinkExtension\ServiceContainer\MinkExtension;
use Behat\Symfony2Extension\ServiceContainer\Symfony2Extension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use PSS\Behat\Symfony2MockerExtension\Context\Argument\ServiceMockerArgumentResolver;
use PSS\Behat\Symfony2MockerExtension\EventListener\ExpectationVerificationListener;
use PSS\Behat\Symfony2MockerExtension\ServiceMocker;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class Symfony2MockerExtension implements Extension
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
    }

    /**
     * @return string
     */
    public function getConfigKey()
    {
        return 'symfony_mocker';
    }

    /**
     * @param ExtensionManager $extensionManager
     */
    public function initialize(ExtensionManager $extensionManager)
    {
    }

    /**
     * @param ArrayNodeDefinition $builder
     */
    public function configure(ArrayNodeDefinition $builder)
    {
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $mockerDefinition = new Definition(
            ServiceMocker::class,
            [new Reference(Symfony2Extension::KERNEL_ID), new Reference(MinkExtension::MINK_ID)]
        );
        $container->setDefinition('symfony_mocker.service_mocker', $mockerDefinition);

        $argumentResolverDefinition = new Definition(
            ServiceMockerArgumentResolver::class,
            [new Reference('symfony_mocker.service_mocker')]
        );
        $argumentResolverDefinition->addTag('context.argument_resolver');
        $container->setDefinition('symfony_mocker.argument_resolver', $argumentResolverDefinition);

        $listenerDefinition = new Definition(
            ExpectationVerificationListener::class,
            [new Reference('symfony_mocker.service_mocker')]
        );
        $listenerDefinition->addTag(EventDispatcherExtension::SUBSCRIBER_TAG);
        $container->setDefinition('symfony_mocker.expectation_verification_listener', $listenerDefinition);
    }
}