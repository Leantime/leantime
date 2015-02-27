<?php

require_once('PEAR/PackageFileManager2.php');

PEAR::setErrorHandling(PEAR_ERROR_DIE);

/**
 * Package Options
 */
$package        = 'OCF';
$baseInstallDir = 'OCF';
$channel        = 'pear.php.net';

$description = 'Open Flash Charts interface library';
$dirRoles = array(
// dirname=> role
    'simpletest'=> 'test',
    'examples'=> 'data',
);

$exceptions = array(
// filename=> role
    'build_package.php'=> 'data',
);

$ignore = array(
// file|dir/
    'tmp/',
);

$roles = array(
// fileext=> role
    'php'=> 'php',
);

$category    = 'Libraries';

$license = 'PHP';
$notes  =  'Helper library for working with Open Flash Charts';

$version     = '2.0.0';
$apiVersion  = '2.0.0';

$simpleoutput = true;
$state = 'beta';
$summary = 'Open Flash Charts interface library';


/**
 * Package metadata
 */

$releaseStability = 'beta';
$apiStability     = 'stable';

$maintainers = array(
//  role, username on PEAR.net,full name, email
array('lead', 'open-flash-chart', 'John Glazebrook', 'open-flash-chart@teethgrinder.co.uk'),
);




$packageSourceDirectory = dirname(__FILE__);

$options = array(
    'baseinstalldir'    => $baseInstallDir,
    'dir_roles'         => $dirRoles,
    'exceptions'        => $exceptions,
    'filelistgenerator' => 'File',
    'ignore'            => $ignore,
    'packagedirectory'  => $packageSourceDirectory,
    'pathtopackagefile' => dirname(__FILE__),
    'roles'             => $roles,
    'simpleoutput'      => $simpleoutput,
    'state'             => $state,
    'version'           => $version,
);

$pkg = new PEAR_PackageFileManager2();

handleError($pkg->setOptions($options));

// Set misc package information
$pkg->setPackage($package);
$pkg->setSummary($summary);
$pkg->setDescription($description);
$pkg->setChannel($channel);

$pkg->setReleaseStability($releaseStability);
$pkg->setAPIStability($apiStability);
$pkg->setReleaseVersion($version);
$pkg->setAPIVersion($apiVersion);

$pkg->setLicense($license);
$pkg->setNotes($notes);



$pkg->setPackageType('php');
$pkg->setPhpDep('5.0.0');
$pkg->setPearinstallerDep('1.4.9');

// Require custom file role for our web installation
// $pkg->addPackageDepWithChannel('required', 'Role_Web', 'pearified.com');

// Define that we will use our custom file role in this script
// $pkg->addUsesRole('web', 'Webfiles');

// Create the current release and add it to the package definition
$pkg->addRelease();

handleError($pkg->generateContents());

// Package release needs a maintainer
foreach($maintainers as $m) {
	handleError($pkg->addMaintainer($m[0], $m[1], $m[2], $m[3]));
}

if($argv[1] === 'write') {
	handleError($pkg->writePackageFile());
	exit(1);
}

handleError($pkg->debugPackageFile());


/**
 * Simple error handler
 *
 * @param Exception $e
 */
function handleError($e) {
	if(PEAR::isError($e)) {
		die($e->getMessage());
	}
}

