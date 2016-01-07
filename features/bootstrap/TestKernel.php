<?php

use PSS\SymfonyMockerContainer\DependencyInjection\MockerContainer;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

final class TestKernel extends Kernel
{
    use MicroKernelTrait;

    /**
     * @return string
     */
    protected function getContainerBaseClass()
    {
        if ('test' == $this->environment) {
            return MockerContainer::class;
        }

        return parent::getContainerBaseClass();
    }

    /**
     * @return BundleInterface[] An array of bundle instances.
     */
    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
        ];
    }

    /**
     *
     * @param ContainerBuilder $c
     * @param LoaderInterface  $loader
     */
    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader)
    {
        $c->loadFromExtension('framework', [
            'secret' => 'my$secret',
            'test' => null,
        ]);

        $c->register('mailer', Mailer::class);
    }

    /**
     * @param RouteCollectionBuilder $routes
     */
    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        $routes->add('/send-mail', 'kernel:sendMail');
        $routes->add('/', 'kernel:home');
    }

    /**
     * @return Response
     */
    public function home()
    {
        return new Response('Home.');
    }

    /**
     * @return Response
     */
    public function sendMail()
    {
        $this->getContainer()->get('mailer')->send();

        return new Response('E-mail sent.');
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return sys_get_temp_dir().'/Symfony2MockerExtension/cache/'.$this->environment;
    }

    /**
     * @return string
     */
    public function getLogDir()
    {
        return sys_get_temp_dir().'/Symfony2MockerExtension/logs/';
    }
}