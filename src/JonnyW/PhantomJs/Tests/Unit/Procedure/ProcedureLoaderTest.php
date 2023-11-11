<?php

/*
 * This file is part of the php-phantomjs.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JonnyW\PhantomJs\Tests\Unit\Procedure;

use JonnyW\PhantomJs\Cache\FileCache;
use JonnyW\PhantomJs\Engine;
use JonnyW\PhantomJs\Parser\JsonParser;
use JonnyW\PhantomJs\Procedure\ProcedureFactory;
use JonnyW\PhantomJs\Procedure\ProcedureFactoryInterface;
use JonnyW\PhantomJs\Procedure\ProcedureLoader;
use JonnyW\PhantomJs\Template\TemplateRenderer;
use Symfony\Component\Config\FileLocatorInterface;

/**
 * PHP PhantomJs.
 *
 * @author Jon Wenmoth <contact@jonnyw.me>
 *
 * @internal
 *
 * @coversNothing
 */
class ProcedureLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test filename.
     *
     * @var string
     */
    protected $filename;

    /**
     * Test directory.
     *
     * @var string
     */
    protected $directory;

    /** +++++++++++++++++++++++++++++++++++ */
    /** ++++++++++++ UTILITIES ++++++++++++ */
    /** +++++++++++++++++++++++++++++++++++ */

    /**
     * Set up test environment.
     */
    public function setUp()
    {
        $this->filename = 'test.proc';
        $this->directory = sys_get_temp_dir();

        if (!is_writable($this->directory)) {
            throw new \RuntimeException(sprintf('Test directory must be writable: %s', $this->directory));
        }
    }

    /**
     * Tear down test environment.
     */
    public function tearDown()
    {
        $filename = $this->getFilename();

        if (file_exists($filename)) {
            unlink($filename);
        }
    }

    /** +++++++++++++++++++++++++++++++++++ */
    /** ++++++++++++++ TESTS ++++++++++++++ */
    /** +++++++++++++++++++++++++++++++++++ */

    /**
     * Test invalid argument exception is thrown if procedure
     * file is not local.
     */
    public function testInvalidArgumentExceptionIsThrownIfProcedureFileIsNotLocal()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $procedureFactory = $this->getProcedureFactory();
        $fileLocator = $this->getFileLocator();

        $fileLocator->method('locate')
            ->will($this->returnValue('http://example.com/index.html'))
        ;

        $procedureLoader = $this->getProcedureLoader($procedureFactory, $fileLocator);
        $procedureLoader->load('test');
    }

    /**
     * Test load throws not exists exception if
     * if procedure file does not exist.
     */
    public function testNotExistsExceptionIsThrownIfProcedureFileDoesNotExist()
    {
        $this->setExpectedException('\JonnyW\PhantomJs\Exception\NotExistsException');

        $procedureFactory = $this->getProcedureFactory();
        $fileLocator = $this->getFileLocator();

        $fileLocator->method('locate')
            ->will($this->returnValue('/invalid/file.proc'))
        ;

        $procedureLoader = $this->getProcedureLoader($procedureFactory, $fileLocator);
        $procedureLoader->load('test');
    }

    /**
     * Test procedure can be laoded.
     */
    public function testProcedureCanBeLoaded()
    {
        $body = 'TEST_PROCEDURE';
        $file = $this->writeProcedure($body);

        $procedureFactory = $this->getProcedureFactory();
        $fileLocator = $this->getFileLocator();

        $fileLocator->method('locate')
            ->will($this->returnValue($file))
        ;

        $procedureLoader = $this->getProcedureLoader($procedureFactory, $fileLocator);

        $this->assertInstanceOf('\JonnyW\PhantomJs\Procedure\ProcedureInterface', $procedureLoader->load('test'));
    }

    /**
     * Test procedure template is set in procedure
     * instance.
     */
    public function testProcedureTemplateIsSetInProcedureInstance()
    {
        $body = 'TEST_PROCEDURE';
        $file = $this->writeProcedure($body);

        $procedureFactory = $this->getProcedureFactory();
        $fileLocator = $this->getFileLocator();

        $fileLocator->method('locate')
            ->will($this->returnValue($file))
        ;

        $procedureLoader = $this->getProcedureLoader($procedureFactory, $fileLocator);

        $this->assertSame($body, $procedureLoader->load('test')->getTemplate());
    }

    /**
     * Test procedure template can be loaded.
     */
    public function testProcedureTemplateCanBeLoaded()
    {
        $body = 'TEST_PROCEDURE';
        $file = $this->writeProcedure($body);

        $procedureFactory = $this->getProcedureFactory();
        $fileLocator = $this->getFileLocator();

        $fileLocator->method('locate')
            ->will($this->returnValue($file))
        ;

        $procedureLoader = $this->getProcedureLoader($procedureFactory, $fileLocator);

        $this->assertNotNull($procedureLoader->loadTemplate('test'));
    }

    /**
     * Get test filename.
     *
     * @return string
     */
    public function getFilename()
    {
        return sprintf('%1$s/%2$s', $this->directory, $this->filename);
    }

    /**
     * Write procedure body to file.
     *
     * @param mixed $procedure
     *
     * @return string
     */
    public function writeProcedure($procedure)
    {
        $filename = $this->getFilename();

        file_put_contents($filename, $procedure);

        return $filename;
    }

    /** +++++++++++++++++++++++++++++++++++ */
    /** ++++++++++ TEST ENTITIES ++++++++++ */
    /** +++++++++++++++++++++++++++++++++++ */

    /**
     * Get procedure loader instance.
     *
     * @return \JonnyW\PhantomJs\Procedure\ProcedureLoader
     */
    protected function getProcedureLoader(ProcedureFactoryInterface $procedureFactory, FileLocatorInterface $locator)
    {
        return new ProcedureLoader($procedureFactory, $locator);
    }

    /**
     * Get procedure factory instance.
     *
     * @return \JonnyW\PhantomJs\Procedure\ProcedureFactory
     */
    protected function getProcedureFactory()
    {
        $engine = $this->getEngine();
        $parser = $this->getParser();
        $cache = $this->getCache();
        $renderer = $this->getRenderer();

        return new ProcedureFactory($engine, $parser, $cache, $renderer);
    }

    /**
     * Get engine.
     *
     * @return \JonnyW\PhantomJs\Engine
     */
    protected function getEngine()
    {
        return new Engine();
    }

    /**
     * Get parser.
     *
     * @return \JonnyW\PhantomJs\Parser\JsonParser
     */
    protected function getParser()
    {
        return new JsonParser();
    }

    /**
     * Get cache.
     *
     * @param string $cacheDir  (default: '')
     * @param string $extension (default: 'proc')
     *
     * @return \JonnyW\PhantomJs\Cache\FileCache
     */
    protected function getCache($cacheDir = '', $extension = 'proc')
    {
        return new FileCache($cacheDir ? $cacheDir : sys_get_temp_dir(), 'proc');
    }

    /**
     * Get template renderer.
     *
     * @return \JonnyW\PhantomJs\Template\TemplateRenderer
     */
    protected function getRenderer()
    {
        $twig = new \Twig\Environment(
            new \Twig\Loader\StringLoader()
        );

        return new TemplateRenderer($twig);
    }

    /** +++++++++++++++++++++++++++++++++++ */
    /** ++++++++++ MOCKS / STUBS ++++++++++ */
    /** +++++++++++++++++++++++++++++++++++ */

    /**
     * Get file locator.
     *
     * @return \Symfony\Component\Config\FileLocatorInterface
     */
    protected function getFileLocator()
    {
        return $this->getMock('\Symfony\Component\Config\FileLocatorInterface');
    }
}
