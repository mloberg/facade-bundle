<?php

namespace Mlo\FacadeBundle\Tests\Mock;

class Test implements BazInterface
{
    /**
     * {@inheritdoc}
     */
    public function one($foo, Bar $bar, $baz = 'test')
    {
    }

    /**
     * @inheritDoc
     */
    public function two()
    {
        return 'foobar';
    }
}
