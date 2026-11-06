<?php
namespace App\MobileScreens;

use Nucleo\Core\ScreenBase;

/** El frontal móvil tiene sus propias pantallas y plantillas, y llama a los mismos Domain que el de escritorio. */
class IndexScreen extends ScreenBase
{
    public function index()
    {
        $this->rendertemplate('index.html');
    }
}
