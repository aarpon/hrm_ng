<?php

/**
 * Populate the database with the required values.
 */

// Bootstrap
require_once dirname(__FILE__) . '/../src/bootstrap.php';

/*
 * Fill the NumericalParameterType table
 */

// Number of iterations
$numIterationsType = new \hrm\Param\Core\NumericalParameterType();
$numIterationsType->setName("NumberOfIterations");
$numIterationsType->setDescription("Number of iterations");
$numIterationsType->setMinValue(1);
$numIterationsType->save();

// Numerical Aperture
$NAType = new \hrm\Param\Core\NumericalParameterType();
$NAType->setName("NumericalAperture");
$NAType->setDescription("Numerical aperture");
$NAType->setMinValue(0.3);
$NAType->setMaxValue(1.5);
$NAType->save();

