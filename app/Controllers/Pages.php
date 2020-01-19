<?php


namespace App\Controllers;
use CodeIgniter\Controller;

class Pages extends Controller {
    public function index(){
        echo 111;
    }
    public function view($page = 'home'){
        if ( ! file_exists(APPPATH.'/Views/Pages/'.$page.'.php'))
        {
            // Whoops, we don't have a page for that!
            throw new \CodeIgniter\PageNotFoundException($page);
        }
        //首字母大写
        $data['title'] = ucfirst($page); // Capitalize the first letter

        echo view('Templates/Header', $data);
        echo view('Pages/'.$page, $data);
        echo view('Templates/Footer', $data);
    }
}