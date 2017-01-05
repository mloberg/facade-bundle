<?php

namespace Mlo\FacadeBundle\Tests\Mock;

interface BazInterface
{
    /**
     * @param int    $foo
     * @param Bar    $bar
     * @param string $baz
     *
     * @return string
     */
    public function one($foo, Bar $bar, $baz = 'test');

    /**
     * @return string[]
     */
    public function two();
}
