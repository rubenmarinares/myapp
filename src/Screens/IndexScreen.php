<?php
namespace App\Screens;

use Nucleo\Core\ScreenBase;

class IndexScreen extends ScreenBase
{
    public function index()
    {
        $this->rendertemplate('index.html');
    }
}
