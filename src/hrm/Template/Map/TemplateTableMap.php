<?php

namespace hrm\Template\Map;

use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\InstancePoolTrait;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\DataFetcher\DataFetcherInterface;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\RelationMap;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Map\TableMapTrait;
use hrm\Template\Template;
use hrm\Template\TemplateQuery;


/**
 * This class defines the structure of the 'template' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 *
 */
class TemplateTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = 'hrm.Template.Map.TemplateTableMap';

    /**
     * The default database name for this class
     */
    const DATABASE_NAME = 'hrm_ng';

    /**
     * The table name for this class
     */
    const TABLE_NAME = 'template';

    /**
     * The related Propel class for this table
     */
    const OM_CLASS = '';

    /**
     * A class that can be returned by this tableMap
     */
    const CLASS_DEFAULT = 'hrm.Template.Template';

    /**
     * The total number of columns
     */
    const NUM_COLUMNS = 3;

    /**
     * The number of lazy-loaded columns
     */
    const NUM_LAZY_LOAD_COLUMNS = 0;

    /**
     * The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS)
     */
    const NUM_HYDRATE_COLUMNS = 3;

    /**
     * the column name for the id field
     */
    const COL_ID = 'template.id';

    /**
     * the column name for the template_id field
     */
    const COL_TEMPLATE_ID = 'template.template_id';

    /**
     * the column name for the class_key field
     */
    const COL_CLASS_KEY = 'template.class_key';

    /**
     * The default string format for model objects of the related table
     */
    const DEFAULT_STRING_FORMAT = 'YAML';

    /** A key representing a particular subclass */
    const CLASSKEY_1 = '1';

    /** A key representing a particular subclass */
    const CLASSKEY_IMAGETEMPLATE = '\\hrm\\Template\\ImageTemplate';

    /** A class that can be returned by this tableMap. */
    const CLASSNAME_1 = '\\hrm\\Template\\ImageTemplate';

    /** A key representing a particular subclass */
    const CLASSKEY_2 = '2';

    /** A key representing a particular subclass */
    const CLASSKEY_RESTORATIONTEMPLATE = '\\hrm\\Template\\RestorationTemplate';

    /** A class that can be returned by this tableMap. */
    const CLASSNAME_2 = '\\hrm\\Template\\RestorationTemplate';

    /** A key representing a particular subclass */
    const CLASSKEY_3 = '3';

    /** A key representing a particular subclass */
    const CLASSKEY_ANALYSISTEMPLATE = '\\hrm\\Template\\AnalysisTemplate';

    /** A class that can be returned by this tableMap. */
    const CLASSNAME_3 = '\\hrm\\Template\\AnalysisTemplate';

    /**
     * holds an array of fieldnames
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldNames[self::TYPE_PHPNAME][0] = 'Id'
     */
    protected static $fieldNames = array (
        self::TYPE_PHPNAME       => array('Id', 'TemplateId', 'ClassKey', ),
        self::TYPE_CAMELNAME     => array('id', 'templateId', 'classKey', ),
        self::TYPE_COLNAME       => array(TemplateTableMap::COL_ID, TemplateTableMap::COL_TEMPLATE_ID, TemplateTableMap::COL_CLASS_KEY, ),
        self::TYPE_FIELDNAME     => array('id', 'template_id', 'class_key', ),
        self::TYPE_NUM           => array(0, 1, 2, )
    );

    /**
     * holds an array of keys for quick access to the fieldnames array
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldKeys[self::TYPE_PHPNAME]['Id'] = 0
     */
    protected static $fieldKeys = array (
        self::TYPE_PHPNAME       => array('Id' => 0, 'TemplateId' => 1, 'ClassKey' => 2, ),
        self::TYPE_CAMELNAME     => array('id' => 0, 'templateId' => 1, 'classKey' => 2, ),
        self::TYPE_COLNAME       => array(TemplateTableMap::COL_ID => 0, TemplateTableMap::COL_TEMPLATE_ID => 1, TemplateTableMap::COL_CLASS_KEY => 2, ),
        self::TYPE_FIELDNAME     => array('id' => 0, 'template_id' => 1, 'class_key' => 2, ),
        self::TYPE_NUM           => array(0, 1, 2, )
    );

    /**
     * Initialize the table attributes and columns
     * Relations are not initialized by this method since they are lazy loaded
     *
     * @return void
     * @throws PropelException
     */
    public function initialize()
    {
        // attributes
        $this->setName('template');
        $this->setPhpName('Template');
        $this->setIdentifierQuoting(false);
        $this->setClassName('\\hrm\\Template\\Template');
        $this->setPackage('hrm.Template');
        $this->setUseIdGenerator(true);
        $this->setSingleTableInheritance(true);
        // columns
        $this->addPrimaryKey('id', 'Id', 'INTEGER', true, null, null);
        $this->addForeignKey('template_id', 'TemplateId', 'INTEGER', 'userdata', 'id', false, null, null);
        $this->addColumn('class_key', 'ClassKey', 'INTEGER', false, null, null);
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('User', '\\hrm\\User\\User', RelationMap::MANY_TO_ONE, array (
  0 =>
  array (
    0 => ':template_id',
    1 => ':id',
  ),
), null, null, null, false);
    } // buildRelations()

    /**
     * Retrieves a string version of the primary key from the DB resultset row that can be used to uniquely identify a row in this table.
     *
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, a serialize()d version of the primary key will be returned.
     *
     * @param array  $row       resultset row.
     * @param int    $offset    The 0-based offset for reading from the resultset row.
     * @param string $indexType One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM
     *
     * @return string The primary key hash of the row
     */
    public static function getPrimaryKeyHashFromRow($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {
        // If the PK cannot be derived from the row, return NULL.
        if ($row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)] === null) {
            return null;
        }

        return null === $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)] || is_scalar($row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)]) || is_callable([$row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)], '__toString']) ? (string) $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)] : $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
    }

    /**
     * Retrieves the primary key from the DB resultset row
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, an array of the primary key columns will be returned.
     *
     * @param array  $row       resultset row.
     * @param int    $offset    The 0-based offset for reading from the resultset row.
     * @param string $indexType One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM
     *
     * @return mixed The primary key of the row
     */
    public static function getPrimaryKeyFromRow($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {
        return (int) $row[
            $indexType == TableMap::TYPE_NUM
                ? 0 + $offset
                : self::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)
        ];
    }

    /**
     * The returned Class will contain objects of the default type or
     * objects that inherit from the default.
     *
     * @param array   $row ConnectionInterface result row.
     * @param int     $colnum Column to examine for OM class information (first is 0).
     * @param boolean $withPrefix Whether or not to return the path with the class name
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     *
     * @return string The OM class
     */
    public static function getOMClass($row, $colnum, $withPrefix = true)
    {
        try {

            $omClass = null;
            $classKey = $row[$colnum + 2];

            switch ($classKey) {

                case TemplateTableMap::CLASSKEY_1:
                    $omClass = TemplateTableMap::CLASSNAME_1;
                    break;

                case TemplateTableMap::CLASSKEY_2:
                    $omClass = TemplateTableMap::CLASSNAME_2;
                    break;

                case TemplateTableMap::CLASSKEY_3:
                    $omClass = TemplateTableMap::CLASSNAME_3;
                    break;

                default:
                    $omClass = TemplateTableMap::CLASS_DEFAULT;

            } // switch
            if (!$withPrefix) {
                $omClass = preg_replace('#\.#', '\\', $omClass);
            }

        } catch (\Exception $e) {
            throw new PropelException('Unable to get OM class.', $e);
        }

        return $omClass;
    }

    /**
     * Populates an object of the default type or an object that inherit from the default.
     *
     * @param array  $row       row returned by DataFetcher->fetch().
     * @param int    $offset    The 0-based offset for reading from the resultset row.
     * @param string $indexType The index type of $row. Mostly DataFetcher->getIndexType().
                                 One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     * @return array           (Template object, last column rank)
     */
    public static function populateObject($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {
        $key = TemplateTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = TemplateTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + TemplateTableMap::NUM_HYDRATE_COLUMNS;
        } elseif (null == $key) {
            // empty resultset, probably from a left join
            // since this table is abstract, we can't hydrate an empty object
            $obj = null;
            $col = $offset + TemplateTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = static::getOMClass($row, $offset, false);
            /** @var Template $obj */
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            TemplateTableMap::addInstanceToPool($obj, $key);
        }

        return array($obj, $col);
    }

    /**
     * The returned array will contain objects of the default type or
     * objects that inherit from the default.
     *
     * @param DataFetcherInterface $dataFetcher
     * @return array
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function populateObjects(DataFetcherInterface $dataFetcher)
    {
        $results = array();

        // populate the object(s)
        while ($row = $dataFetcher->fetch()) {
            $key = TemplateTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = TemplateTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                // class must be set each time from the record row
                $cls = static::getOMClass($row, 0);
                $cls = preg_replace('#\.#', '\\', $cls);
                /** @var Template $obj */
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                TemplateTableMap::addInstanceToPool($obj, $key);
            } // if key exists
        }

        return $results;
    }
    /**
     * Add all the columns needed to create a new object.
     *
     * Note: any columns that were marked with lazyLoad="true" in the
     * XML schema will not be added to the select list and only loaded
     * on demand.
     *
     * @param Criteria $criteria object containing the columns to add.
     * @param string   $alias    optional table alias
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function addSelectColumns(Criteria $criteria, $alias = null)
    {
        if (null === $alias) {
            $criteria->addSelectColumn(TemplateTableMap::COL_ID);
            $criteria->addSelectColumn(TemplateTableMap::COL_TEMPLATE_ID);
            $criteria->addSelectColumn(TemplateTableMap::COL_CLASS_KEY);
        } else {
            $criteria->addSelectColumn($alias . '.id');
            $criteria->addSelectColumn($alias . '.template_id');
            $criteria->addSelectColumn($alias . '.class_key');
        }
    }

    /**
     * Returns the TableMap related to this object.
     * This method is not needed for general use but a specific application could have a need.
     * @return TableMap
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function getTableMap()
    {
        return Propel::getServiceContainer()->getDatabaseMap(TemplateTableMap::DATABASE_NAME)->getTable(TemplateTableMap::TABLE_NAME);
    }

    /**
     * Add a TableMap instance to the database for this tableMap class.
     */
    public static function buildTableMap()
    {
        $dbMap = Propel::getServiceContainer()->getDatabaseMap(TemplateTableMap::DATABASE_NAME);
        if (!$dbMap->hasTable(TemplateTableMap::TABLE_NAME)) {
            $dbMap->addTableObject(new TemplateTableMap());
        }
    }

    /**
     * Performs a DELETE on the database, given a Template or Criteria object OR a primary key value.
     *
     * @param mixed               $values Criteria or Template object or primary key or array of primary keys
     *              which is used to create the DELETE statement
     * @param  ConnectionInterface $con the connection to use
     * @return int             The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                         if supported by native driver or if emulated using Propel.
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
     public static function doDelete($values, ConnectionInterface $con = null)
     {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(TemplateTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \hrm\Template\Template) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(TemplateTableMap::DATABASE_NAME);
            $criteria->add(TemplateTableMap::COL_ID, (array) $values, Criteria::IN);
        }

        $query = TemplateQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) {
            TemplateTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) {
                TemplateTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the template table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(ConnectionInterface $con = null)
    {
        return TemplateQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a Template or Criteria object.
     *
     * @param mixed               $criteria Criteria or Template object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed           The new primary key.
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(TemplateTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from Template object
        }

        if ($criteria->containsKey(TemplateTableMap::COL_ID) && $criteria->keyContainsValue(TemplateTableMap::COL_ID) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.TemplateTableMap::COL_ID.')');
        }


        // Set the correct dbName
        $query = TemplateQuery::create()->mergeWith($criteria);

        // use transaction because $criteria could contain info
        // for more than one table (I guess, conceivably)
        return $con->transaction(function () use ($con, $query) {
            return $query->doInsert($con);
        });
    }

} // TemplateTableMap
// This is the static code needed to register the TableMap for this table with the main Propel class.
//
TemplateTableMap::buildTableMap();
