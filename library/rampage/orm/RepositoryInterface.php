<?php
/**
 * This is part of @application_name@
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
 * @package   @package_name@
 * @author    Axel Helmert
 * @copyright Copyright (c) 2012 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\orm;

/**
 * Interface for repositories
 */
interface RepositoryInterface
{
    /**
     * Returns the repository name
     *
     * @return string
     */
    public function getName();

    /**
     * Set the repository name
     *
     * @param string $name
     * @return \rampage\orm\RepositoryInterface $this Provide a fluent interface
     */
    public function setName($name);

    /**
     * Returns the entity type instance
     *
     * @param EntityInterface|EntityType|string $name
     * @throws RuntimeException
     * @return \rampage\orm\entity\type\EntityType
     */
    public function getEntityType($name);

    /**
     * Set the name of the adapter service
     *
     * @param string $name
     */
    public function setAdapterName($name);

    /**
     * Set configuration
     *
     * @param ConfigInterface $config
     */
    public function setConfig(ConfigInterface $config);
}