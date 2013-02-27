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
 * @package   rampage.orm
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\orm\db\platform;

/**
 * Platform interface
 */
interface PlatformInterface
{
    /**
     * Must return the platform name
     *
     * @return string
     */
    public function getName();

    /**
     * Allow to set the platform name
     *
     * @param string $name
     */
    public function setName($name);

    /**
     * Return the table name for the given entity name
     *
     * @param string $entity
     */
    public function getTable($entity);

    /**
     * Get the field mapper for the given entity
     *
     * @param string $entity
     * @return FieldMapper
     */
    public function getFieldMapper($entity);

    /**
     * Fetch the hydrator for the given entity
     *
     * @param string $entity
     * @return \Zend\Stdlib\Hydrator\HydratorInterface
     */
    public function getHydrator($entity);

    /**
     * Returns a platform constraint mapper
     *
     * @param string $constraint
     * @return \rampage\orm\db\platform\ConstraintMapperInterface|null
     */
    public function getConstraintMapper($constraint);
}