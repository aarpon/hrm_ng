<?php

namespace hrm\Template\Base;

use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Connection\ConnectionInterface;
use hrm\Template\Map\TemplateTableMap;

/**
 * Skeleton subclass for representing a query for one of the subclasses of the 'template' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class RestorationTemplateQuery extends TemplateQuery
{

    /**
     * Returns a new \hrm\Template\RestorationTemplateQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return \hrm\Template\RestorationTemplateQuery
     */
    public static function create($modelAlias = null, Criteria $criteria = null)
    {
        if ($criteria instanceof \hrm\Template\RestorationTemplateQuery) {
            return $criteria;
        }
        $query = new \hrm\Template\RestorationTemplateQuery();
        if (null !== $modelAlias) {
            $query->setModelAlias($modelAlias);
        }
        if ($criteria instanceof Criteria) {
            $query->mergeWith($criteria);
        }

        return $query;
    }

    /**
     * Filters the query to target only RestorationTemplate objects.
     */
    public function preSelect(ConnectionInterface $con)
    {
        $this->addUsingAlias(TemplateTableMap::COL_CLASS_KEY, TemplateTableMap::CLASSKEY_2);
    }

    /**
     * Filters the query to target only RestorationTemplate objects.
     */
    public function preUpdate(&$values, ConnectionInterface $con, $forceIndividualSaves = false)
    {
        $this->addUsingAlias(TemplateTableMap::COL_CLASS_KEY, TemplateTableMap::CLASSKEY_2);
    }

    /**
     * Filters the query to target only RestorationTemplate objects.
     */
    public function preDelete(ConnectionInterface $con)
    {
        $this->addUsingAlias(TemplateTableMap::COL_CLASS_KEY, TemplateTableMap::CLASSKEY_2);
    }

    /**
     * Issue a DELETE query based on the current ModelCriteria deleting all rows in the table
     * Having the RestorationTemplate class.
     * This method is called by ModelCriteria::deleteAll() inside a transaction
     *
     * @param ConnectionInterface $con a connection object
     *
     * @return integer the number of deleted rows
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        // condition on class key is already added in preDelete()
        return parent::delete($con);
    }

} // RestorationTemplateQuery
