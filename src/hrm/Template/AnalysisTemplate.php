<?php

namespace hrm\Template;

use hrm\Template\Map\TemplateTableMap;


/**
 * Skeleton subclass for representing a row from one of the subclasses of the 'template' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class AnalysisTemplate extends Template
{

    /**
     * Constructs a new AnalysisTemplate class, setting the class_key column to TemplateTableMap::CLASSKEY_3.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setClassKey(TemplateTableMap::CLASSKEY_3);
    }

} // AnalysisTemplate
