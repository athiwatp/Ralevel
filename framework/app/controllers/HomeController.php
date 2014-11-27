<?php


class HomeController extends Controller {

	public function hello()
	{
		return HomeController::tes();
		return View::make('layout/wow', ['title' => 'Hello World']);
	}

	public function tes()
	{
		return Tes::find(3)->rr();
	}

}