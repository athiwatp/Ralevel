<?php

Route::add('', 'home', function(){
	return View::make('layout/wow', ['title' => 'tesssssss', 'menus' => [['name' => 'dua']]]);
});