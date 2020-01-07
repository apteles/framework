<?php
declare(strict_types=1);

namespace ApTeles\Core;

use ApTeles\Router\Router;
use ApTeles\Http\Responder;
use Composer\Autoload\ClassLoader;
use ApTeles\Router\RouterInterface;
use ApTeles\Http\ResponderInterface;
use Psr\Container\ContainerInterface;

class Application
{
    /**
     *
     * @var array
     */
    private $settings = [];

    /**
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     *
     * @var array
     */
    private $modules = [];

    /**
     *
     * @var ClassLoader
     */
    private $composer;


    /**
     *
     * @param array $settings
     * @param ClassLoader $composer
     */
    public function __construct(array $settings, ClassLoader $composer = null)
    {
        $this->settings = $settings;

        $this->composer = $composer;
    }

    /**
     *
     * @return void
     */
    public function init(): void
    {
        $this->registerModules();
    }

    /**
     *
     * @param ContainerInterface $container
     * @return void
     */
    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    /**
     *
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        if ($this->container) {
            return $this->container;
        }
    }

    /**
     *
     * @return ResponderInterface
     */
    public function getResponder(): ResponderInterface
    {
        if (!$this->container->has(ResponderInterface::class)) {
            $this->container->set(ResponderInterface::class, new Responder($this->container));
        }

        return $this->container->get(ResponderInterface::class);
    }

    /**
     *
     * @return RouterInterface
     */
    public function getRouter(): RouterInterface
    {
        if (!$this->container->has(RouterInterface::class)) {
            $router = new Router;
            $router->setContainer($this->container);
            $this->container->set(RouterInterface::class, $router);
        }

        return $this->container->get(RouterInterface::class);
    }

    /**
     *
     * @param array $modules
     * @return void
     */
    public function defineModules(array $modules): self
    {
        foreach ($modules as $file => $module) {
            $this->modules[$file] = $module;
        }

        return $this;
    }

    /**
     *
     * @return void
     */
    private function registerModules(): void
    {
        if (!$this->modules) {
            return;
        }

        $registry = new ModuleRegistry;

        $registry->setApp($this);
        $registry->setComposer($this->composer);

        foreach ($this->modules as $file => $module) {
            require $file;
            $registry->addModule(new $module);
        }

        $registry->run();
    }

    public function dispatch()
    {
        $responder = $this->getResponder();
        $router = $this->getRouter();
        $proccessed = $router->run();
 
        $responder(
            $proccessed['invoker'],
            $proccessed['action'],
            $proccessed['params']
        );
    }
}
