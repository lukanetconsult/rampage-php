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

use rampage\core\modules\AggregatedXmlConfig;
use rampage\core\ObjectManagerInterface;
use rampage\core\ModuleRegistry;
use rampage\core\PathManager;

use rampage\orm\entity\type\ConfigInterface as EntityTypeConfigInterface;
use rampage\orm\entity\type\EntityType;
use rampage\orm\entity\type\Attribute;
use rampage\orm\exception\InvalidConfigException;
use rampage\core\xml\mergerule\UniqueAttributeRule;

/**
 * Config
 */
class Config extends AggregatedXmlConfig implements ConfigInterface, EntityTypeConfigInterface
{
    /**
     * Object manager instance
     *
     * @var \rampage\core\ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * All repository names
     *
     * @var array
     */
    private $repositoryNames = null;

    /**
     * (non-PHPdoc)
     * @see \rampage\core\modules\AggregatedXmlConfig::getGlobalFilename()
     */
    protected function getGlobalFilename()
    {
        return 'repository.xml';
    }

	/**
     * (non-PHPdoc)
     * @see \rampage\core\modules\AggregatedXmlConfig::getModuleFilename()
     */
    protected function getModuleFilename()
    {
        return 'etc/repository.xml';
    }

	/**
     * (non-PHPdoc)
     * @see \rampage\core\xml\Config::__construct()
     */
    public function __construct(ModuleRegistry $registry, PathManager $pathManager, ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
        parent::__construct($registry, $pathManager);
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\core\xml\Config::getDefaultMergeRulechain()
     */
    protected function getDefaultMergeRulechain()
    {
        $rules = parent::getDefaultMergeRulechain();
        $rules->add(new UniqueAttributeRule('~/reference/attribute$', 'local'))
            ->add(new UniqueAttributeRule('~/(repository|entity|attribute|index|reference)$~', 'name'))
            ->add(new UniqueAttributeRule('~/constraint$', 'type'));

        return $rules;
    }

	/**
     * Object Manager instance
     *
     * @return \rampage\core\ObjectManagerInterface
     */
    protected function getObjectManager()
    {
        return $this->objectManager;
    }

	/**
     * Returns the repository class name
     *
     * @param string $name
     * @return string|null
     */
    protected function getRepositoryClass($name)
    {
        $node = $this->getNode("repository[@name='$name']");
        if (!$node) {
            return null;
        }

        $class = isset($node['class'])? (string)$node['class'] : (string)$node['name'];
        return $class;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\ConfigInterface::hasRepositoryConfig()
     */
    public function hasRepositoryConfig($name)
    {
        $class = $this->getRepositoryClass($name);
        return !empty($class);
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\ConfigInterface::getRepositoryNames()
     */
    public function getRepositoryNames()
    {
        if ($this->repositoryNames !== null) {
            return $this->repositoryNames;
        }

        foreach ($this->getXml()->xpath('./repository[@name != ""]') as $node) {
            $this->repositoryNames[] = (string)$node['name'];
        }

        return $this->repositoryNames;
    }

	/**
     * Returns the adapter name for the given repository name
     *
     * @return string|null
     */
    protected function getRepositoryAdapterName($repositoryName)
    {
        $repositoryName = $this->xpathQuote($repositoryName);
        $node = $this->getNode("repository[@name='$repositoryName']/adapter");
        if (!$node || !isset($node['service'])) {
            return null;
        }

        return trim((string)$node['service']);
    }

    /**
     * Returns the config class for the given repository or null if there is none defined
     *
     * @param string $repositoryName
     * @return string|null
     */
    protected function getRepositoryConfigClass($repositoryName)
    {
        $repositoryName = $this->xpathQuote($repositoryName);
        $node = $this->getNode("repository[@name = '$repositoryName' and @config != '']");
        $class = ($node !== null)? (string)$node['class'] : null;

        if (!$class) {
            return null;
        }

        return $class;
    }

    /**
     * (non-PHPdoc)
     * @see \rampage\orm\ConfigInterface::configureRepository()
     */
    public function configureRepository(RepositoryInterface $repository)
    {
        $name = $this->getRepositoryAdapterName($repository->getName());
        if ($name) {
            $repository->setAdapterName($name);
        }

        $configClass = $this->getRepositoryConfigClass($repository->getName());
        if ($configClass) {
            $config = $this->getObjectManager()->get($configClass, array('ormConfig' => $this));

            if (!$config instanceof ConfigInterface) {
                throw new InvalidConfigException(sprintf(
                    'Invalid repository config for "%s": Expected implementation of rampage\orm\ConfigInterface, but %s doesn\'t implement it.',
                    $repository->getName(), is_object($config)? get_class($config) : gettype($config)
                ));
            }

            $config->configureRepository($repository);
        }

        return $this;
    }

    /**
     * Configure entity type
     */
    public function configureEntityType(EntityType $type)
    {
        list($repoName, $typeName) = explode(':', $type->getFullName(), 2);

        $repoName = $this->xpathQuote($repoName);
        $typeName = $this->xpathQuote($typeName);
        $xpath = "./repository[@name = $repoName]/entity[@name = $typeName]";
        $xml = $this->getNode($xpath);

        if ($xml === null) {
            return $this;
        }

        if (isset($xml['class'])) {
            $type->setClass((string)$xml['class']);
        }

        foreach ($xml->xpath('./attribute[@name != ""]') as $node) {
            $attribute = new Attribute(
                (string)$node['name'],
                (string)$node['type'],
                $node->toValue('bool', 'primary'),
                $node->toValue('bool', 'identity'),
                $node->toValue('bool', 'nullable')
            );

            $type->addAttribute($attribute);
        }

        foreach ($xml->xpath('./index[@name != ""]') as $node) {
            $index = array();
            foreach ($node->xpath('./attribute[@name != ""]') as $attributeNode) {
                $name = (string)$attributeNode['name'];
                $index[$name] = $name;
            }

            if (!empty($index)) {
                $type->addIndex((string)$node['name'], $index);
            }
        }

        // TODO: references
    }
}