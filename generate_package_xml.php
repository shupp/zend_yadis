<?php
require_once('PEAR/PackageFileManager2.php');
PEAR::setErrorHandling(PEAR_ERROR_DIE);

$packagefile = dirname(__FILE__) . '/package.xml';

$options = array(
    'filelistgenerator' => 'svn',
    'changelogoldtonew' => false,
    'simpleoutput'      => true,
    'baseinstalldir'    => '/',
    'packagedirectory'  => dirname(__FILE__),
    //'packagefile'       => $packagefile,
    'clearcontents'     => true,
    'ignore'            => array('generate_package_xml.php', '.svn', '.cvs*'),
    'dir_roles'         => array(
        'docs'     => 'doc',
        'examples' => 'doc',
        'tests'    => 'test',
    ),
);

$packagexml = &PEAR_PackageFileManager2::importOptions($packagefile, $options);
$packagexml->setPackageType('php');

$packagexml->setPackage('Services_Yadis');
$packagexml->setSummary('Implementation of the Yadis Specification 1.0 protocol for PHP5.');
$packagexml->setDescription("Implementation of the Yadis Specification 1.0 protocol allowing a client to discover a list of Services a Yadis Identity Provider offers.");

$packagexml->setChannel('pear.php.net');

$notes = <<<EOT
* Fixed #17829 - redirect loop was happening when X-XRDS-Location was present with the document itself
* Removed dependency on PEAR_Exception
EOT;
$packagexml->setNotes($notes);

$packagexml->setPhpDep('5.2.0');
$packagexml->setPearinstallerDep('1.4.0b1');
$packagexml->addPackageDepWithChannel('required', 'HTTP_Request2', 'pear.php.net', '0.5.1');
$packagexml->addPackageDepWithChannel('required', 'Validate', 'pear.php.net');
$packagexml->addPackageDepWithChannel('required', 'Net_URL2', 'pear.php.net');
$packagexml->addExtensionDep('required', 'simplexml');
$packagexml->addExtensionDep('required', 'date');
$packagexml->addExtensionDep('required', 'dom');

$packagexml->addMaintainer('lead', 'shupp', 'Bill Shupp', 'shupp@php.net');
$packagexml->setLicense('New BSD License', 'http://opensource.org/licenses/bsd-license.php');

$packagexml->addRelease();
$packagexml->generateContents();

$packagexml->setAPIVersion('0.5.0');
$packagexml->setReleaseVersion('0.5.0');
$packagexml->setReleaseStability('beta');
$packagexml->setAPIStability('beta');

if (isset($_GET['make']) || (isset($_SERVER['argv']) && @$_SERVER['argv'][1] == 'make')) {
    $packagexml->writePackageFile();
} else {
    $packagexml->debugPackageFile();
}
