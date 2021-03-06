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

namespace rampage\core\di;

use rampage\core\ServiceManager;
use rampage\core\ObjectManagerInterface;
use rampage\core\exception;
use rampage\core\service\exception\CircularReferenceException;
use Zend\ServiceManager\InitializerInterface;

/**
 * Peering service manager for retrieving package services
 */
class ObjectManager extends ServiceManager implements ObjectManagerInterface
{
    /**
     * Service name regex
     */
    const CREATE_NAME_REGEX = '~^[a-z_][a-z0-9_]*(\.[a-z_][a-z0-9_]*)+$~i';

    /**
     * Parent service manager
     *
     * @var \rampage\core\ServiceManager
     */
    private $parent = null;

    /**
     * Di locator
     *
     * @var \rampage\core\di\Di
     */
    private $di = null;

    /**
     * Package aliases
     *
     * @param array $config
     */
    public function __construct(ServiceManager $parent, $config)
    {
        $this->shareByDefault = false;
        $this->allowOverride = true;
        $this->parent = $parent;

        if (!isset($config['packages']['aliases'])
          || (!is_array($config['packages']['aliases'])
          && (!$config['packages']['aliases'] instanceof \Traversable))) {
            return;
        }

        $this->addAliases($config['packages']['aliases']);
    }

    /**
     * Returns the parent service manager
     *
     * @return \rampage\core\ServiceManager
     */
    protected function getParentServiceManager()
    {
        return $this->parent;
    }

    /**
     * Add aliases
     *
     * @param array|\Traversable $config
     * @throws \rampage\core\exception\InvalidArgumentException
     */
    public function addAliases($config)
    {
        if (!is_array($config) && (!$config instanceof \Traversable)) {
            throw new exception\InvalidArgumentException('Aliases must be an array or implement Traversable');
        }

        foreach ($config as $alias => $class) {
            $this->setAlias($alias, $class);
        }
    }

	/**
     * Check if service is creatable
     *
     * @param string $name
     * @return bool
     */
    protected function _canCreate($name)
    {
        $cName = $this->canonicalizeName($name);
        if (isset($this->instances[$cName])) {
            return true;
        }

        return (isset($this->aliases[$cName]) || preg_match(self::CREATE_NAME_REGEX, $name));
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\ServiceManager\ServiceManager::canCreate()
     */
    public function canCreate($name, $checkAbstractFactories = true)
    {
        if (is_array($name)) {
            list($cName, $rName) = $name;
            $name = $rName;
        }

        return $this->_canCreate($name);
    }

	/**
     * di factory
     *
     * @param Di $di
     * @return \rampage\core\di\ObjectManager
     */
    public function setDi(Di $di)
    {
        $instanceManager = $di->instanceManager();
        if ($instanceManager instanceof InstanceManager) {
            $instanceManager->setObjectManager($this);
        }

        $this->di = $di;
        return $this;
    }

    /**
     * Get the DI Container
     *
     * @return \rampage\core\di\Di
     */
    protected function getDi()
    {
        if (!$this->di) {
            $this->setDi($this->getParentServiceManager()->get('di'));
        }

        return $this->di;
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\ServiceManager\ServiceLocatorInterface::has()
     */
    public function has($name, $checkAbstractFactories = true, $usePeeringServiceManagers = true)
    {
        return $this->_canCreate($name) || $this->getParentServiceManager()->has($name, false);
    }

    /**
     * Initialize instance
     *
     * @param array|Traversable $initializers
     * @param object $instance
     */
    protected function initInstance($initializers, $instance)
    {
        foreach ($initializers as $initializer) {
            if ($initializer instanceof InitializerInterface) {
                $initializer->initialize($instance, $this);
            } elseif (is_object($initializer) && is_callable($initializer)) {
                $initializer($instance, $this);
            } else {
                call_user_func($initializer, $instance, $this);
            }
        }

        return $this;
    }

    /**
     * Resolve alias
     *
     * @param string $name
     * @return string
     */
    protected function resolveAlias($name)
    {
        $class = $name;
        $name = $this->canonicalizeName($name);
        $stack = array(); // cycle check

        while (isset($this->aliases[$name])) {
            if (isset($stack[$name])) {
                throw new CircularReferenceException('Circular alias reference: ' . implode(' -> ', $stack));
            }

            $stack[$name] = "$name ($class)";
            $class = $this->aliases[$name];
            $name = $this->canonicalizeName($class);
        }

        return $class;
    }

    /**
     * Resolve a class name
     *
     * @param string $class
     * @return string
     */
    public function resolveClassName($name)
    {
        $class = $this->resolveAlias($name);
        $class = trim(str_replace('.', '\\', $class), '\\');

        return $class;
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\ServiceManager\ServiceLocatorInterface::get()
     */
    public function get($name, array $params = array(), $callInitializers = true)
    {
        if ($this->getParentServiceManager()->has($name, false)) {
            return $this->getParentServiceManager()->get($name);
        }

        $cName = $this->canonicalizeName($name);
        if (isset($this->instances[$cName])) {
            return $this->instances[$cName];
        }

        $class = $this->resolveClassName($name);
        $instance = $this->getDi()->get($class, $params);

        if ($callInitializers) {
            // apply initializers of the parent service manager and this one
            $this->initInstance($this->getParentServiceManager()->initializers, $instance)
                 ->initInstance($this->initializers, $instance);
        }

        if ($this->isShared($cName, $name)) {
            $this->instances[$cName] = $instance;
        }

        return $instance;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\core\ObjectManagerInterface::newInstance()
     */
    public function newInstance($name, array $params = array())
    {
        $class = $this->resolveClassName($name);
        return $this->getDi()->newInstance($class, $params, false);
    }
}