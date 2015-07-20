<?php

/**
 * Populate the database with the required values.
 */

// Bootstrap
require_once dirname(__FILE__) . '/../src/bootstrap.php';

/*
 * Create a test user
 */

$user = new \hrm\User\User();
$user->setName("TestUser");
$user->setPasswordHash("TestPassword");
$user->setEmail("test@email.com");
$user->setResearchGroup("TestGroup");
$user->setAuthentication("integrated");
$user->setRole("manager");
$user->setCreationDate(new DateTime());
$user->setLastAccessDate(new DateTime());
$user->setStatus("active");
$user->save();

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

