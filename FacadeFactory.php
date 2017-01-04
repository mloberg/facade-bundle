<?php
/*
 * Copyright (c) 2017 Matthew Loberg
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Mlo\FacadeBundle;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * FacadeFactory
 *
 * @author Matthew Loberg <loberg.matt@gmail.com>
 */
class FacadeFactory
{
    /**
     * @var ContainerInterface
     */
    private static $container;

    /**
     * Set Container
     *
     * @param ContainerInterface $container
     */
    public static function setContainer(ContainerInterface $container)
    {
        static::$container = $container;
    }

    /**
     * Get Container
     *
     * @return ContainerInterface
     */
    public static function getContainer()
    {
        return static::$container;
    }

    /**
     * Create facade
     *
     * @param string $accessor
     * @param string $name
     * @param array  $arguments
     *
     * @return object
     */
    public static function create($accessor, $name, array $arguments = [])
    {
        $service = static::getContainer()->get($accessor);

        return call_user_func_array([$service, $name], $arguments);
    }
}
