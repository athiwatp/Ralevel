<?php

// Register semua komponen yang dibutuhkan sistem

$list = require 'framework/core/autoload.php'; 
$vendor_loc = 'framework/core/vendor/'; 

foreach($list['vendors'] as $vendor)
{
	if (file_exists($vendor_loc.$vendor.'.php')) require_once $vendor_loc.$vendor.'.php';
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

require_once 'framework/app/routers.php';

Route::start();