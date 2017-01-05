<?php

namespace Mlo\FacadeBundle\Tests;

use Mlo\FacadeBundle\DependencyInjection\Compiler\FacadeCompilerPass;
use Mlo\FacadeBundle\FacadeFactory;
use Mlo\FacadeBundle\Tests\Mock\Foo;
use Mlo\FacadeBundle\Tests\Mock\Test;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Filesystem\Filesystem;

class FacadeCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->filesystem = new Filesystem();
        $this->cacheDir = __DIR__.'/facades';

        $foo = new Definition(Foo::class);
        $foo->addTag('facade', ['facade' => 'Foo']);
        $this->container->setDefinition('foo', $foo);

        $test = new Definition(Test::class);
        $test->addTag('facade');
        $this->container->setDefinition('test', $test);

        $compilerPass = new FacadeCompilerPass($this->cacheDir);
        $compilerPass->process($this->container);

        FacadeFactory::setContainer($this->container);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        $this->filesystem->remove($this->cacheDir);

        unset($this->container, $this->filesystem, $this->cacheDir);
    }

    public function testFoo()
    {
        $path = $this->cacheDir.'/Foo.php';

        $this->assertTrue($this->filesystem->exists($path));

        $content = file_get_contents($path);

        // Test @method tags
        $this->assertContains('@method static string greet(string $name)', $content);
        $this->assertContains('@method static void test(\Mlo\FacadeBundle\Tests\Mock\Test $test)', $content);
        // Method from Bar
        $this->assertContains('@method static string bar(int $times = 1)', $content);
        // Skip non-public methods
        $this->assertNotContains('foo(', $content);
        // Skip static methods
        $this->assertNotContains('hello(', $content);

        require_once $path;

        // Make sure we can call the Facade
        $this->assertEquals('Hello World!', \Facades\Foo::greet('World'));
        $this->assertInstanceOf(Foo::class, \Facades\Foo::getFacadeRoot());
    }

    public function testTest()
    {
        $path = $this->cacheDir.'/Mlo/FacadeBundle/Tests/Mock/Test.php';

        $this->assertTrue($this->filesystem->exists($path));

        $content = file_get_contents($path);

        // Test @method tags
        $this->assertContains('@method static string one(int $foo, \Mlo\FacadeBundle\Tests\Mock\Bar $bar, string $baz = "test")', $content);
        $this->assertContains('@method static string[] two()', $content);

        require_once $path;

        // Make sure we can call the Facade
        $this->assertEquals('foobar', \Facades\Mlo\FacadeBundle\Tests\Mock\Test::two());
    }
}
