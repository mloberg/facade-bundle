<?php

namespace Mlo\FacadeBundle\Tests\Mock;

class Bar
{
    /**
     * @param int $times
     *
     * @return string
     */
    public function bar($times = 1)
    {
        return str_repeat('bar', $times);
    }
}
