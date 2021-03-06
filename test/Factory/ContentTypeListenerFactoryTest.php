<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Versioning\Factory;

use Interop\Container\ContainerInterface;
use PHPUnit_Framework_TestCase as TestCase;
use ReflectionClass;
use ZF\Versioning\ContentTypeListener;
use ZF\Versioning\Factory\ContentTypeListenerFactory;

class ContentTypeListenerFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);

        $r = new ReflectionClass(ContentTypeListener::class);
        $props = $r->getDefaultProperties();
        $this->defaultRegexes = $props['regexes'];
    }

    public function testCreatesEmptyContentTypeListenerIfNoConfigServicePresent()
    {
        $this->container->has('config')->willReturn(false);
        $factory = new ContentTypeListenerFactory();
        $listener = $factory($this->container->reveal());
        $this->assertInstanceOf(ContentTypeListener::class, $listener);
        $this->assertAttributeSame($this->defaultRegexes, 'regexes', $listener);
    }

    public function testCreatesEmptyContentTypeListenerIfNoVersioningConfigPresent()
    {
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn(['foo' => 'bar']);
        $factory = new ContentTypeListenerFactory();
        $listener = $factory($this->container->reveal());
        $this->assertInstanceOf(ContentTypeListener::class, $listener);
        $this->assertAttributeSame($this->defaultRegexes, 'regexes', $listener);
    }

    public function testCreatesEmptyContentTypeListenerIfNoVersioningContentTypeConfigPresent()
    {
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn(['zf-versioning' => ['foo' => 'bar']]);
        $factory = new ContentTypeListenerFactory();
        $listener = $factory($this->container->reveal());
        $this->assertInstanceOf(ContentTypeListener::class, $listener);
        $this->assertAttributeSame($this->defaultRegexes, 'regexes', $listener);
    }

    public function testConfiguresContentTypeListeneWithRegexesFromConfiguration()
    {
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn(['zf-versioning' => [
            'content-type' => [
                '#foo=bar#',
            ],
        ]]);
        $factory = new ContentTypeListenerFactory();
        $listener = $factory($this->container->reveal());
        $this->assertInstanceOf(ContentTypeListener::class, $listener);
        $this->assertAttributeContains('#foo=bar#', 'regexes', $listener);

        foreach ($this->defaultRegexes as $regex) {
            $this->assertAttributeContains($regex, 'regexes', $listener);
        }
    }
}
