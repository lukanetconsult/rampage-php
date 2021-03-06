<?php
/**
 * This is part of rampage.php
 * Copyright (c) 2012 Axel Helmert
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @category  library
 * @package   rampage.core
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\gui\view\html;

use rampage\core\view\Template;
use rampage\core\resource\UrlLocatorInterface;
use rampage\core\exception\RuntimeException;

/**
 * Html header view
 */
class Head extends Template
{
    /**
     * Javascript
     *
     * @var array
     */
    protected $js = array();

    /**
     * Css
     *
     * @var array
     */
    protected $css = array();

    /**
     * Title fragments
     *
     * @var array
     */
    protected $titles = array();

    /**
     * Url locator
     *
     * @var \rampage\core\resource\UrlLocatorInterface
     */
    private $urlLocator = null;

    /**
     * Construct
     */
    public function __construct(UrlLocatorInterface $urlLocator)
    {
        $this->urlLocator = $urlLocator;
        $this->setTemplate('core::html/head');
    }

    /**
     * Url locator
     *
     * @return \rampage\core\resource\UrlLocatorInterface
     */
    protected function getUrlLocator()
    {
        return $this->urlLocator;
    }

    /**
     * Title prefix
     *
     * @return string
     */
	public function getTitlePrefix()
    {
        return (string)$this->get('title_prefix');
    }

    /**
     * Title suffix
     *
     * @return string
     */
    public function getTitleSuffix()
    {
        return (string)$this->get('title_suffix');
    }

    /**
     * Title separator
     *
     * @return string
     */
    public function getTitleSeparator()
    {
        return (string)$this->get('title_separator');
    }

    /**
     * Add a title fragment
     *
     * @param string $title
     * @return \rampage\gui\view\html\Head
     */
    public function addTitle($title)
    {
        $title = (string)$title;
        $this->titles[$title] = $title;

        return $this;
    }

    /**
     * Returns the page title
     *
     * @param callable $translateHelper The helper invokable to use for translation.
     */
    public function title($translateHelper = null)
    {
        $fragments = $this->titles;
        array_unshift($fragments, $this->getTitlePrefix());

        $fragments[] = (string)$this->get('title');
        $fragments[] = $this->getTitleSuffix();
        $fragments = array_filter($fragments);

        if (is_callable($translateHelper)) {
            $fragments = array_map($translateHelper, $fragments);
        }

        return implode(' ' . $this->getTitleSeparator() . ' ', $fragments);
    }

	/**
     * Add a javascript
     *
     * @param string $file
     */
    public function addJs($file)
    {
        $this->js[$file] = $file;
    }

    /**
     * Add a css file
     *
     * @param string $file
     */
    public function addCss($file, $media = null)
    {
        $this->css[$file] = array($file, $media);
    }

    /**
     * Render CSS html
     */
    public function cssHtml()
    {
        $html = array();

        foreach ($this->css as $css) {
            try {
                @list($file, $media) = $css;
                $url = $this->getUrlLocator()->getUrl($file);
            } catch (RuntimeException $e) {
                continue;
            }

            $media = ($media)?: 'screen';
            $html[] = '<link rel="stylesheet" type="text/css" href="' . $url . '" media="' . $media . '" />';
        }

        return implode("\n", $html);
    }

    /**
     * Returns Javascript HTML
     */
    public function jsHtml()
    {
        $html = array();

        foreach ($this->js as $file) {
            try {
                $url = $this->getUrlLocator()->getUrl($file);
            } catch (RuntimeException $e) {
                continue;
            }

            $html[] = '<script type="text/javascript" src="' . $url . '"></script>';
        }

        return implode("\n", $html);
    }
}