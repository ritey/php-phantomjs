<?php

/*
 * This file is part of the php-phantomjs.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JonnyW\PhantomJs\Tests\Unit\Procedure;

use JonnyW\PhantomJs\Cache\CacheInterface;
use JonnyW\PhantomJs\Cache\FileCache;
use JonnyW\PhantomJs\Engine;
use JonnyW\PhantomJs\Parser\JsonParser;
use JonnyW\PhantomJs\Parser\ParserInterface;
use JonnyW\PhantomJs\Procedure\Input;
use JonnyW\PhantomJs\Procedure\Output;
use JonnyW\PhantomJs\Procedure\Procedure;
use JonnyW\PhantomJs\Template\TemplateRenderer;
use JonnyW\PhantomJs\Template\TemplateRendererInterface;

/**
 * PHP PhantomJs.
 *
 * @author Jon Wenmoth <contact@jonnyw.me>
 *
 * @internal
 *
 * @coversNothing
 */
class ProcedureTest extends \PHPUnit_Framework_TestCase
{
    /** +++++++++++++++++++++++++++++++++++ */
    /** ++++++++++++++ TESTS ++++++++++++++ */
    /** +++++++++++++++++++++++++++++++++++ */

    /**
     * Test procedure template can be
     * set in procedure.
     */
    public function testProcedureTemplateCanBeSetInProcedure()
    {
        $template = 'PROCEDURE_TEMPLATE';

        $engne = $this->getEngine();
        $parser = $this->getParser();
        $cache = $this->getCache();
        $renderer = $this->getRenderer();

        $procedure = $this->getProcedure($engne, $parser, $cache, $renderer);
        $procedure->setTemplate($template);

        $this->assertSame($procedure->getTemplate(), $template);
    }

    /**
     * Test procedure can be compiled.
     */
    public function testProcedureCanBeCompiled()
    {
        $template = 'TEST_{{ input.get("uncompiled") }}_PROCEDURE';

        $engne = $this->getEngine();
        $parser = $this->getParser();
        $cache = $this->getCache();
        $renderer = $this->getRenderer();

        $input = $this->getInput();
        $input->set('uncompiled', 'COMPILED');

        $procedure = $this->getProcedure($engne, $parser, $cache, $renderer);
        $procedure->setTemplate($template);

        $this->assertSame('TEST_COMPILED_PROCEDURE', $procedure->compile($input));
    }

    /**
     * Test not writable exception is thrown if procedure
     * script cannot be written to file.
     */
    public function testNotWritableExceptionIsThrownIfProcedureScriptCannotBeWrittenToFile()
    {
        $this->setExpectedException('\JonnyW\PhantomJs\Exception\NotWritableException');

        $engne = $this->getEngine();
        $parser = $this->getParser();
        $renderer = $this->getRenderer();

        $cache = $this->getCache('/an/invalid/dir');

        $input = $this->getInput();
        $output = $this->getOutput();

        $procedure = $this->getProcedure($engne, $parser, $cache, $renderer);
        $procedure->run($input, $output);
    }

    /**
     * Test procedure failed exception is thrown if procedure
     * cannot be run.
     */
    public function testProcedureFailedExceptionIsThrownIfProcedureCannotBeRun()
    {
        $this->setExpectedException('\JonnyW\PhantomJs\Exception\ProcedureFailedException');

        $parser = $this->getParser();
        $cache = $this->getCache();
        $renderer = $this->getRenderer();
        $input = $this->getInput();
        $output = $this->getOutput();

        $engne = $this->getEngine();
        $engne->method('getCommand')
            ->will($this->throwException(new \Exception()))
        ;

        $procedure = $this->getProcedure($engne, $parser, $cache, $renderer);
        $procedure->run($input, $output);
    }

    /** +++++++++++++++++++++++++++++++++++ */
    /** ++++++++++ TEST ENTITIES ++++++++++ */
    /** +++++++++++++++++++++++++++++++++++ */

    /**
     * Get procedure instance.
     *
     * @return \JonnyW\PhantomJs\Procedure\Procedure
     */
    protected function getProcedure(Engine $engine, ParserInterface $parser, CacheInterface $cacheHandler, TemplateRendererInterface $renderer)
    {
        return new Procedure($engine, $parser, $cacheHandler, $renderer);
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

    /**
     * Get input.
     *
     * @return \JonnyW\PhantomJs\Procedure\Input
     */
    protected function getInput()
    {
        return new Input();
    }

    /**
     * Get output.
     *
     * @return \JonnyW\PhantomJs\Procedure\Output
     */
    protected function getOutput()
    {
        return new Output();
    }

    /** +++++++++++++++++++++++++++++++++++ */
    /** ++++++++++ MOCKS / STUBS ++++++++++ */
    /** +++++++++++++++++++++++++++++++++++ */

    /**
     * Get engine.
     *
     * @return \JonnyW\PhantomJs\Engine
     */
    protected function getEngine()
    {
        return $this->getMock('\JonnyW\PhantomJs\Engine');
    }
}
