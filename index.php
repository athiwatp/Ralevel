<?php


define('BASEURL', '');
define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));
define('COREPATH', 'framework/core/');
define('APPPATH', 'framework/app/');

require COREPATH.'start.php';

View::make('layout.app');

// echo Url::baseUrl();
$tes = new Maen;
// $tes->nama = 'ROfiudin';
// $tes->alamat = 'Batang';
// echo $tacho = $tes->toString();

echo $tes->find('1, 20');
// print_r($tacho);

// echo Maen::select('nama', 'alamat')->take(10)->get();

