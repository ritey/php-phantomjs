<?php

/*
 * This file is part of the php-phantomjs.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JonnyW\PhantomJs\Template;

/**
 * PHP PhantomJs.
 *
 * @author Jon Wenmoth <contact@jonnyw.me>
 */
class TemplateRenderer implements TemplateRendererInterface
{
    /**
     * Twig environment instance.
     *
     * @var \Twig\Environment
     */
    protected $twig;

    /**
     * Internal constructor.
     */
    public function __construct(\Twig\Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * Render template.
     *
     * @param string $template
     * @param array  $context  (default: array())
     *
     * @return string
     */
    public function render($template, array $context = [])
    {
        $template = $this->twig->createTemplate($template);

        return $template->render($context);
    }
}
