<?php

define('BASEURL', '');
define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));
define('COREPATH', 'framework/core/');
define('APPPATH', 'framework/app/');

require COREPATH.'start.php';

$app = new Ralevel;

$app->run();