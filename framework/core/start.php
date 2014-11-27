<?php

// Register semua komponen yang dibutuhkan sistem

$list = require COREPATH.'autoload.php'; 
define('VENDORPATH', COREPATH.'vendor/'); 

foreach($list['vendors'] as $vendor)
{
	if (file_exists(VENDORPATH.$vendor.'.php')) require_once VENDORPATH.$vendor.'.php';
}

foreach($list['folders'] as $directory) 
{
	$dir = @dir($directory);
	while (false !== ($entry = $dir->read()) ) {
		if ( '.' == $entry || '..' == $entry )
					continue;
		require_once $directory.$entry;
	}
}

foreach($list['files'] as $file)
{
	if (file_exists($file)) require_once $file;
}

// -----------------------------------------------------------------------------------------
