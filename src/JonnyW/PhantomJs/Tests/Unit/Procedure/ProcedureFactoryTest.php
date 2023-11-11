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
use JonnyW\PhantomJs\Procedure\ProcedureFactory;
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
class ProcedureFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** +++++++++++++++++++++++++++++++++++ */
    /** ++++++++++++++ TESTS ++++++++++++++ */
    /** +++++++++++++++++++++++++++++++++++ */

    /**
     * Test factory can create instance of
     * procedure.
     */
    public function testFactoryCanCreateInstanceOfProcedure()
    {
        $engine = $this->getEngine();
        $parser = $this->getParser();
        $cache = $this->getCache();
        $renderer = $this->getRenderer();

        $procedureFactory = $this->getProcedureFactory($engine, $parser, $cache, $renderer);

        $this->assertInstanceOf('\JonnyW\PhantomJs\Procedure\Procedure', $procedureFactory->createProcedure());
    }

    /** +++++++++++++++++++++++++++++++++++ */
    /** ++++++++++ TEST ENTITIES ++++++++++ */
    /** +++++++++++++++++++++++++++++++++++ */

    /**
     * Get procedure factory instance.
     *
     * @return \JonnyW\PhantomJs\Procedure\ProcedureFactory
     */
    protected function getProcedureFactory(Engine $engine, ParserInterface $parser, CacheInterface $cacheHandler, TemplateRendererInterface $renderer)
    {
        return new ProcedureFactory($engine, $parser, $cacheHandler, $renderer);
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
}
