<?php
/*
 * Copyright (c) 2017 Matthew Loberg
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Mlo\FacadeBundle;

/**
 * AbstractFacade
 *
 * @author Matthew Loberg <loberg.matt@gmail.com>
 */
abstract class AbstractFacade
{
    /**
     * Get object behind facade
     *
     * @return object
     */
    public static function getFacadeRoot()
    {
        return FacadeFactory::getContainer()->get(static::getFacadeAccessor());
    }

    /**
     * Get the registered name of the component
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        throw new \RuntimeException('Facade does not implement getFacadeAccessor method.');
    }

    /**
     * Handle calls to the Facade
     *
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return FacadeFactory::create(static::getFacadeAccessor(), $name, $arguments);
    }
}
