<?php
/*
 * Copyright (c) 2017 Matthew Loberg
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Mlo\FacadeBundle;

use Mlo\FacadeBundle\DependencyInjection\Compiler\FacadeCompilerPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * MloFacadeBundle
 *
 * @author Matthew Loberg <loberg.matt@gmail.com>
 */
class MloFacadeBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $cacheDir = $container->getParameter('kernel.cache_dir').'/facade';

        $container->addCompilerPass(new FacadeCompilerPass($cacheDir));
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        FacadeFactory::setContainer($this->container);
    }
}
