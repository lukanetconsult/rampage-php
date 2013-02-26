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

use Zend\Stdlib\Hydrator\HydratorInterface;

/**
 * Platform config interface
 */
interface ConfigInterface
{
    /**
     * Configure the platform service locator
     *
     * @param ServiceLocator $locator
     */
    public function configurePlatformServiceLocator(ServiceLocator $locator);

    /**
     * Returns the db table for the given entity
     *
     * @param string $entity
     */
    public function getTable($entity);

    /**
     * Returns the hydrator class
     */
    public function getHydratorClass();

    /**
     * Returns the fieldmap for the given entity
     *
     * @param string $entity
     */
    public function configureFieldMapper(FieldMapper $mapper, $entity);

    /**
     * Configure hydrator
     *
     * @param Hydrator $entity
     */
    public function configureHydrator(HydratorInterface $hydrator, $entity);
}