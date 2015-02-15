<?php

use aarpon\hrm\Sample;

class SampleTest extends PHPUnit_Framework_TestCase {

  public function testAnswer()
  {
    $sample = new Sample;
    $this->assertTrue($sample->answer());
  }

}

