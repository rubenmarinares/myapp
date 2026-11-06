<?php
namespace App\Api;

use Nucleo\Core\ApiBase;

/** Lo que consultan otros servidores en nombre de sus visitantes. `ping` es lo que usa el plugin para probar la conexión. */
class ConectorApi extends ApiBase
{
    public function ping()
    {
        return ['aplicacion' => (string) ($this->app->config['name'] ?? ''), 'hora' => date('c')];
    }
}
