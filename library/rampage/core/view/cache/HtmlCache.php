<?php
/**
 * This is part of rampage.php
 * Copyright (c) 2013 Axel Helmert
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

namespace rampage\core\view\cache;

use Zend\Cache\Pattern\AbstractPattern;
use Zend\Cache\Storage\TaggableInterface;
use Zend\View\Renderer\RendererInterface;
use rampage\core\view\features\CachableViewInterface;

/**
 * HTML view cache
 */
class HtmlCache extends AbstractPattern
{
    /**
     * Cache storage
     *
     * @return \Zend\Cache\Storage\StorageInterface
     */
    protected function getStorage()
    {
        return $this->getOptions()->getStorage();
    }

	/**
     * Fetch html for the given view
     *
     * @param RenderStrategyInterface $renderer
     * @param string $view
     */
    public function fetch(RendererInterface $renderer, $view)
    {
        if (($view instanceof CachableViewInterface) || !$view->isCachable()) {
            return false;
        }

        $success = null;
        $data = $this->getStorage()->getItem($view->getCacheId(), $success);

        if (!$success) {
            return false;
        }

        return $data;
    }

    /**
     * Store html
     *
     * @param RenderStrategyInterface $renderer
     * @param object $view
     * @param string $html
     * @return \rampage\core\view\cache\HtmlCache
     */
    public function store(RendererInterface $renderer, $view, &$html)
    {
        if (($view instanceof CachableViewInterface) || !$view->isCachable()) {
            return $this;
        }

        $storage = $this->getStorage();
        $options = $storage->getOptions();
        $cacheId = $view->getCacheId();
        $oldTtl = $options->getTtl();

        $options->setTtl($view->getCacheLifetime());
        $storage->setItem($view->getCacheId(), $html);
        $options->setTtl($oldTtl);

        if (!$storage instanceof TaggableInterface) {
            $storage->setTags($cacheId, $view->getCacheTags());
        }

        return $this;
    }
}