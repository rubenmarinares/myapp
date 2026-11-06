<?php
namespace App\Api;

use Nucleo\Core\ApiBase;

class StatusApi extends ApiBase
{
    public function show()
    {
        return ['aplicacion' => (string) ($this->app->config['name'] ?? ''), 'hora' => date('c')];
    }
}
