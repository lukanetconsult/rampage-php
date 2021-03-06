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

namespace rampage\core\di\definition;

use Zend\Di\Definition\RuntimeDefinition as DefaultRuntimeDefinition;
use Zend\Code\Reflection\ClassReflection;
use Zend\Code\Reflection\MethodReflection;
use Zend\Code\Reflection\ParameterReflection;

/**
 * Runtime definition for dependency injection
 */
class RuntimeDefinition extends DefaultRuntimeDefinition
{
    /**
     * (non-PHPdoc)
     * @see \Zend\Di\Definition\RuntimeDefinition::hasClass()
     */
    public function hasClass($class)
    {
        if (strpos($class, '.') !== false) {
            $class = strtr($class, '.', '\\');
        }

        return parent::hasClass($class);
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\Di\Definition\RuntimeDefinition::processClass()
     */
    protected function processClass($class)
    {
        if (strpos($class, '.') === false) {
            return parent::processClass($class);
        }

        $className = str_replace('.', '\\', $class);
        if (!isset($this->classes[$className])) {
            parent::processClass($className);
        }

        $this->classes[$class] = &$this->classes[$className];
    }

	/**
     * Returns the injection type
     *
     * @param MethodReflection $method
     * @param ParameterReflection $parameter
     * @return string
     */
    protected function getInjectType(MethodReflection $method, ParameterReflection $parameter, &$required)
    {
        $doc = $method->getDocComment();
        $name = $parameter->getName();
        $pattern = '~@service\s+(([a-zA-Z][a-zA-Z0-9.\\\\]*)\s+)?\$' . $name . '(\s+force)?~';
        $m = null;

        if (preg_match($pattern, $doc, $m)) {
            if (isset($m[3]) && !empty($m[3])) {
                $required = true;
            }

            if (!isset($m[2]) && !$parameter->getClass()) {
                return null;
            } else if (isset($m[2])) {
                return $m[2];
            }
        }

        if (!$parameter->getClass()) {
            $type = ($parameter->isArray())? null : false;
            return $type;
        }

        return $parameter->getClass()->getName();
    }


    /**
     * Process method parameters
     *
     * @param array $def
     * @param \Zend\Code\Reflection\ClassReflection  $rClass
     * @param \Zend\Code\Reflection\MethodReflection $rMethod
     */
    protected function processParams(&$def, ClassReflection $rClass, MethodReflection $rMethod)
    {
        if (count($rMethod->getParameters()) === 0) {
            return;
        }

        $methodName = $rMethod->getName();
        $def['parameters'][$methodName] = array();

        foreach ($rMethod->getParameters() as $p) {

            /** @var $p \ReflectionParameter  */
            $actualParamName = $p->getName();
            $fqName = $rClass->getName() . '::' . $rMethod->getName() . ':' . $p->getPosition();
            $optional = $p->isOptional();
            $required = !$optional;

            $def['parameters'][$methodName][$fqName] = array();

            // set the class name, if it exists
            $def['parameters'][$methodName][$fqName][] = $actualParamName;
            $def['parameters'][$methodName][$fqName][] = $this->getInjectType($rMethod, $p, $required);
            $def['parameters'][$methodName][$fqName][] = $required;
            $def['parameters'][$methodName][$fqName][] = ($optional)? $p->getDefaultValue() : null;
        }
    }
}