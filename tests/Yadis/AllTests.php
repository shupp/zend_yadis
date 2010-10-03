<?php

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Yadis_AllTests::main');
}

require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'TestHelper.php';
require_once 'XrdsTest.php';
require_once 'Xrds/AllTests.php';

class Yadis_AllTests
{
    public static function main()
    {
        $parameters = array();

        if (TESTS_GENERATE_REPORT && extension_loaded('xdebug')) {
            $parameters['reportDirectory'] = TESTS_GENERATE_REPORT_TARGET;
        }
        PHPUnit_TextUI_TestRunner::run(self::suite(), $parameters);
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('PEAR - Services_Yadis/Yadis');

        $suite->addTestSuite(Yadis_Xrds_AllTests::suite());
        $suite->addTestSuite('Services_Yadis_XrdsTest');

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Yadis_AllTests::main') {
    AllTests::main();
}
