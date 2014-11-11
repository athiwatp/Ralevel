<?php


define('BASEURL', '');
define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));
define('COREPATH', 'framework/core/');
define('APPPATH', 'framework/app/');

require COREPATH.'start.php';

// View::make('layout.app');

// // echo Url::baseUrl();
$tes = new Tes;
echo $tes->find(33);
// echo strtoupper('');
// $tes->create();
// // echo $tes->numRows();
// $tes->row(1);
// $tes->nama = 'Jhonathan Smith';
// $tes->alamat = 'New York';
// $tes->row(3);
// $tes->nama = 'Jhon Dow';
// $tes->alamat = 'California';
// echo $tes->save();

// echo $tes->delete();


// echo $tes->toString();

// // echo $tacho = $tes->toString();

// echo $tes->create([['nama' => 'abdurrozak', 'alamat' => 'Barca'], ['nama' => 'robin', 'alamat' => 'england']]);
// $tacho = $tes->find('20, 18')->update(['alamat' => 'Semarang'])->tesEscape();
// echo ($tacho);

// // echo Tes::select('nama', 'alamat')->take(10)->get();

