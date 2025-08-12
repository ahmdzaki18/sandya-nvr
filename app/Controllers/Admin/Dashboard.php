<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Dashboards extends BaseController
{
    public function index()
    {
        return view('admin/dashboards/index', ['title'=>'Dashboards (Coming Soon)']);
    }
}
