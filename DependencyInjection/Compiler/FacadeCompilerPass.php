<?php
/*
 * Copyright (c) 2017 Matthew Loberg
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Mlo\FacadeBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Filesystem\Filesystem;

/**
 * FacadeCompilerPass
 *
 * @author Matthew Loberg <loberg.matt@gmail.com>
 */
class FacadeCompilerPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private $cacheDirectory;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Constructor
     *
     * @param string $cacheDirectory
     */
    public function __construct($cacheDirectory)
    {
        $this->cacheDirectory = rtrim($cacheDirectory, '/');
        $this->filesystem = new Filesystem();
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($container->findTaggedServiceIds('facade') as $id => $tags) {
            $class = $container->getDefinition($id)->getClass();
            $facade = @$tags[0]['facade'] ?: $class;

            $this->createFacade($facade, $id, $class);
        }
    }

    /**
     * Create facade
     *
     * @param string $facade
     * @param string $service
     * @param string $class
     */
    private function createFacade($facade, $service, $class)
    {
        $parts = explode('\\', $facade);
        $basename = array_pop($parts);
        $namespace = implode('\\', $parts);
        $path = $this->cacheDirectory.'/'.str_replace('\\', '/', $namespace);
        $filename = $path.'/'.$basename.'.php';

        if ($this->filesystem->exists($filename)) {
            return;
        }

        $content = strtr($this->getStub(), [
            '{{ NAMESPACE }}' => rtrim('Facades\\'.$namespace, '\\'),
            '{{ TARGET }}' => $class,
            '{{ CLASS }}' => $basename,
            '{{ SERVICE }}' => $service,
        ]);

        $this->filesystem->mkdir($path);
        $this->filesystem->dumpFile($filename, $content);
    }

    /**
     * Get Facade stub
     *
     * @return string
     */
    private function getStub()
    {
        return <<<'EOF'
<?php

namespace {{ NAMESPACE }};

use Mlo\FacadeBundle\AbstractFacade;

/**
 * @see \{{ TARGET }}
 */
class {{ CLASS }} extends AbstractFacade
{
    protected static function getFacadeAccessor()
    {
        return '{{ SERVICE }}';
    }
}
EOF;
    }
}
