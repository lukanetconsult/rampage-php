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

namespace rampage\orm\db\ddl;

/**
 * Reference defintion
 */
class ReferenceDefinition extends NamedDefintion
{
    /**
     * Local fields
     *
     * @var string
     */
    private $fields = array();

    /**
     * Referenced fields
     *
     * @var array
     */
    private $referenceFields = array();

    /**
     * The referneced entity
     *
     * @var string
     */
    private $referenceEntity = null;

    /**
     * Is a reference to a primary key
     *
     * @var bool
     */
    private $referencesPrimary = true;

    /**
     * Construct
     *
     * @param string $name
     * @param array $fields
     * @param string $referenceEntity
     * @param array $referenceFields
     */
    public function __construct($name, array $fields, $referenceEntity, array $referenceFields, $referencesPrimary = true)
    {
        $this->setName($name);

        $this->fields = $fields;
        $this->referenceEntity = (string)$referenceEntity;
        $this->referenceFields = $referenceFields;
        $this->referencesPrimary = (bool)$referencesPrimary;
    }

    /**
     * returns the local field names
     *
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * returns the referenced field names
     *
     * @return array
     */
    public function getReferenceFields()
    {
        return $this->referenceFields;
    }

    /**
     * Returns the referenced entity name
     *
     * @return string
     */
    public function getReferenceEntity()
    {
        return $this->referenceEntity;
    }

    /**
     * Flag if this is a reference to a PK.
     *
     * Some DBMS don't support referencing Non-PK fields (i.E. Oracle)
     * These should ignore this reference ift this method returns true
     *
     * @return boolean
     */
    public function isReferenceToPrimary()
    {
        return $this->referencesPrimary;
    }
}