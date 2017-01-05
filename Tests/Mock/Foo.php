<?php

namespace Mlo\FacadeBundle\Tests\Mock;

class Foo extends Bar
{
    /**
     * Say hi
     *
     * @param string $name
     *
     * @return string
     */
    public function greet($name)
    {
        return sprintf('Hello %s!', $name);
    }

    /**
     * Do test
     *
     * @param Test $test
     */
    public function test(Test $test)
    {
    }

    /**
     * @return string
     */
    public static function hello()
    {
        return 'hi';
    }

    /**
     * @return bool
     */
    protected function foo()
    {
        return true;
    }
}
