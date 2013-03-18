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

namespace rampage\orm\entity\type;

use rampage\orm\RepositoryInterface;

/**
 * Entity type
 */
class EntityType
{
    /**
     * Attributes
     *
     * @var array
     */
    private $attributes = array();

    /**
     * References
     *
     * @var array
     */
    private $references = array();

    /**
     * Identifier
     *
     * @var array|\rampage\orm\entity\type\Attribute
     */
    private $identifier = null;

    /**
     * Indexes
     *
     * @var array
     */
    private $indexes = array();

    /**
     * Entity type name
     *
     * @var string
     */
    private $name = null;

    /**
     * Full entity name including the repository name
     *
     * @var string
     */
    private $fullName = null;

    /**
     * Repository
     *
     * @var \rampage\orm\RepositoryInterface
     */
    private $repository = null;

    /**
     * Entity class name
     *
     * @var string
     */
    private $class = null;

    /**
     * Flag is generated ids are used
     *
     * @var bool
     */
    protected $hasGeneratedId = null;

    /**
     * Construct
     *
     * @param string $name
     * @param RepositoryInterface $repository
     */
    public function __construct($name, RepositoryInterface $repository, ConfigInterface $config)
    {
        $this->name = (string)$name;
        $this->repository = $repository;

        $config->configureEntityType($this);
    }

    /**
     * @return the $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Unqualified entity name
     *
     * @return string
     */
    public function getUnqualifiedName()
    {
        $name = $this->getName();
        if (strpos($name, ':') !== false) {
            @list($repo, $name) = explode(':', $name, 2);
        }

        return $name;
    }

    /**
     * Returns the full entity name including the repository
     *
     * @return string
     */
    public function getFullName()
    {
        if ($this->fullName !== null) {
            return $this->fullName;
        }

        $name = $this->getName();
        if (strpos($name, ':') === false) {
            $name = $this->getRepository()->getName() . ':' . $name;
        }

        $this->fullName = $name;
        return $name;
    }

    /**
     * Repository
     *
     * @return \rampage\orm\RepositoryInterface
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Default implementation class name
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

	/**
     * Default implementing class name
     *
     * @param string $class
     */
    public function setClass($class)
    {
        $this->class = (string)$class;
        return $this;
    }

	/**
     * Add an attribute
     *
     * @param Attribute $attribute
     * @return \rampage\orm\entity\type\EntityType
     */
    public function addAttribute(Attribute $attribute)
    {
        $this->attributes[$attribute->getName()] = $attribute;
        return $this;
    }

    /**
     * Add an index
     *
     * @param string $name
     * @param array $attributes
     */
    public function addIndex($name, array $attributes)
    {
        $this->indexes[$name] = $attributes;
        return $this;
    }

    /**
     * Add a reference
     *
     * @param string $name
     * @param string $entity
     * @param array $localAttributes
     * @param array $referencedAttributes
     * @return \rampage\orm\entity\type\EntityType
     */
    public function addReference($name, $entity, array $localAttributes, array $referencedAttributes)
    {
        $this->references[$name] = array(
            'attributes' => $localAttributes,
            'references' => array(
                'entity' => $entity,
                'attributes' => $referencedAttributes
            )
        );

        return $this;
    }

    /**
     * @return the $attributes
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Returns a specific attribute
     *
     * @param string $name
     * @return \rampage\orm\entity\type\Attribute|null
     */
    public function getAttribute($name)
    {
        if (!isset($this->attributes[$name])) {
            return null;
        }

        return $this->attributes[$name];
    }

    /**
     * @return the $references
     */
    public function getReferences()
    {
        return $this->references;
    }

    /**
     * Get the identifier definition for this entity
     *
     * @return \rampage\orm\entity\type\Identitfier
     */
    public function getIdentifier()
    {
        if ($this->identifier !== null) {
            return $this->identifier;
        }

        $identifier = new Identifier($this);
        $this->identifier = $identifier;

        return $identifier;
    }

    /**
     * Check for generated ids
     *
     * @return bool
     */
    public function usesGeneratedId()
    {
        return $this->getIdentifier()->isGenerated();
    }

    /**
     * @return the $idexes
     */
    public function getIndexes()
    {
        return $this->indexes;
    }
}