<?php
/*
 * Copyright (c) 2017 Matthew Loberg
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Mlo\FacadeBundle\DependencyInjection\Compiler;

use Mlo\FacadeBundle\MloFacadeBundle;
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
            '{{ NAMESPACE }}' => rtrim(MloFacadeBundle::FACADE_NAMESPACE.$namespace, '\\'),
            '{{ METHODS }}' => implode("\n * ", $this->getMethodSignatures($class)),
            '{{ TARGET }}' => $class,
            '{{ CLASS }}' => $basename,
            '{{ SERVICE }}' => $service,
        ]);

        $this->filesystem->mkdir($path);
        $this->filesystem->dumpFile($filename, $content);
    }

    /**
     * Generate method signatures for IDEs
     *
     * @param string $class
     *
     * @return string[]
     */
    private function getMethodSignatures($class)
    {
        $refClass = new \ReflectionClass($class);

        $methods = [];

        foreach ($refClass->getMethods() as $refMethod) {
            if (!$refMethod->isPublic() || $refMethod->isConstructor()) {
                continue;
            }

            $docBlock = $this->getMethodDocBlock($refMethod);

            // Get parameters
            preg_match_all('/@param ?([^\s]+)?\s*\$([^\s]+)/', $docBlock, $matches);

            $params = array_combine($matches[2], $matches[1]);
            $defaults = [];

            foreach ($refMethod->getParameters() as $parameter) {
                if ($parameter->isDefaultValueAvailable()) {
                    $defaults[$parameter->getName()] = $parameter->getDefaultValue();
                }
            }

            $parameters = [];

            foreach ($params as $name => $type) {
                $parameters[] = $param = trim(sprintf('%s $%s = %s', $type, $name, @$defaults[$name]), ' =');
            }

            // Get return type
            preg_match('/@return ([^\s]+)/', $docBlock, $matches);
            $returnType = @$matches[1] ?: 'void';

            $methods[] = sprintf(
                '@method static %s %s(%s)',
                $returnType,
                $refMethod->getName(),
                implode(', ', $parameters)
            );
        }

        return $methods;
    }

    /**
     * Get method doc block
     *
     * @param \ReflectionMethod $method
     *
     * @return string
     */
    private function getMethodDocBlock(\ReflectionMethod $method)
    {
        $docBlock = $method->getDocComment();

        if (preg_match('/@inheritdoc/i', $docBlock)) {
            foreach ($this->getParents($method->getDeclaringClass()) as $parent) {
                if ($parent->hasMethod($method->getName())) {
                    return $this->getMethodDocBlock($parent->getMethod($method->getName()));
                }
            }
        }

        return $docBlock;
    }

    /**
     * Get all parents for a class
     *
     * @param \ReflectionClass $class
     *
     * @return \ReflectionClass[]
     */
    private function getParents(\ReflectionClass $class)
    {
        $parents = [];

        foreach ($class->getInterfaces() as $interface) {
            $parents[] = $interface;

            if ($parentInterface = $interface->getParentClass()) {
                $parents = array_merge($parents, $this->getParents($parentInterface));
            }
        }

        while ($parent = $class->getParentClass()) {
            $parents[] = $parent;

            $parents = array_merge($parents, $this->getParents($parent));
        }

        return $parents;
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
 * {{ METHODS }}
 *
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
