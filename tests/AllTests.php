<?php

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Services_Yadis_AllTests::main');
}

require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'TestHelper.php';
require_once 'Yadis/AllTests.php';

class Services_Yadis_AllTests
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
        $suite = new PHPUnit_Framework_TestSuite('PEAR - Services_Yadis');

        $suite->addTestSuite(Yadis_AllTests::suite());

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Services_Yadis_AllTests::main') {
    Services_Yadis_AllTests::main();
}
