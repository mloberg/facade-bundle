<?php
/*
 * Copyright (c) 2017 Matthew Loberg
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Mlo\FacadeBundle;

use Mlo\FacadeBundle\DependencyInjection\Compiler\FacadeCompilerPass;
use Symfony\Component\ClassLoader\Psr4ClassLoader;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * MloFacadeBundle
 *
 * @author Matthew Loberg <loberg.matt@gmail.com>
 */
class MloFacadeBundle extends Bundle
{
    const FACADE_NAMESPACE = 'Facades\\';

    /**
     * @var Psr4ClassLoader
     */
    private $autoloader;

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $cacheDir = $this->getCacheDir($container);

        $container->addCompilerPass(new FacadeCompilerPass($cacheDir));
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        FacadeFactory::setContainer($this->container);

        $this->autoloader = new Psr4ClassLoader();
        $this->autoloader->addPrefix(static::FACADE_NAMESPACE, $this->getCacheDir($this->container));
        $this->autoloader->register();
    }

    /**
     * {@inheritdoc}
     */
    public function shutdown()
    {
        if ($this->autoloader) {
            $this->autoloader->unregister();
        }
    }

    /**
     * Get cache dir
     *
     * @param ContainerInterface $container
     *
     * @return string
     */
    private function getCacheDir(ContainerInterface $container)
    {
        return $container->getParameter('kernel.cache_dir').'/facade';
    }
}
