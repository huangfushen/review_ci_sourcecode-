<?php namespace App\Controllers;

class Home extends BaseController
{
	public function index()
	{
        //define('WEEKA', 6048001);
        //var_dump(WEEKA);
	    echo view('welcome_message');
		//return view('welcome_message');
	}

	//--------------------------------------------------------------------

}
