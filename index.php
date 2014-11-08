<?php


define('BASEURL', '');
define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));
define('COREPATH', 'framework/core/');
define('APPPATH', 'framework/app');

require BASEPATH.'start.php';

View::make('layouts.app');

echo Url::baseUrl();