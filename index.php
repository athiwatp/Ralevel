<?php


define('BASEURL', '');
define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));
define('COREPATH', 'framework/core/');
define('APPPATH', 'framework/app/');

require COREPATH.'start.php';

View::make('layout.app');

// echo Url::baseUrl();
$tes = (new Maen())->find(5);
echo $tes->nama;
$tes->nama = 'ROfiudin';
$tes->alamat = 'Batang';
echo $tes->toString();



// echo Maen::select('nama', 'alamat')->take(10)->get();

