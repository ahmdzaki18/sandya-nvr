<?php
namespace App\Controllers;

use App\Models\CameraModel;

class Stream extends BaseController
{
    public function play($id)
    {
        $cam = (new CameraModel())->find($id);
        if (!$cam) return redirect()->to('/')->with('error','Camera not found');
        return view('stream/play', [
            'title' => 'Live: '.$cam['name'],
            'cam'   => $cam,
        ]);
    }
}
