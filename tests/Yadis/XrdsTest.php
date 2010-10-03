<?php

require_once 'Services/Yadis/Xrds.php';
require_once 'PHPUnit/Framework/TestCase.php';

class Services_Yadis_XrdsTest extends PHPUnit_Framework_TestCase
{
    protected $_namespace = null;

    public function setUp()
    {
        $this->_namespace = $this->getMock('Services_Yadis_Xrds_Namespace');
    }

    public function test()
    {
    }

}