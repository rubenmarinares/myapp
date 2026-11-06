# Api

Manejadores de los frontales JSON (`api`, `conector`). Una pantalla sin plantilla: reciben JSON, llaman a un Domain y devuelven datos. (Detalle: skill `nucleo`, `references/api.md`.)

## Reglas duras

- Heredan de `Nucleo\Core\ApiBase`. **No usan `Domain\Models` ni SQL.**
- Devuelven un **array**; el kernel lo envuelve en `{"ok": true, "datos": ...}`. Otro estado: `RespuestaJson::datos([...], 201)`.
- Errores previstos con `throw new ErrorDominio(ErrorDominio::NO_ENCONTRADO)` (o `DATOS_INVALIDOS`, `PROHIBIDO`, `CONFLICTO`…). Salen como `{"ok": false, "error": {...}}` con su estado HTTP.
- **El token solo en la cabecera `Authorization: Bearer …`.** Nunca `?token=` en la URL. Lo valida el guard del frontal (`BearerGuard`, o `TokenGuard` en `conector`).
- Datos en bruto: importes en diezmilésimas, fechas en entero o ISO.
- Ni `echo`, ni `header()`, ni `json_encode()` a mano.

## Ejemplo

```php
<?php
namespace App\Api;

use App\Domain\ArticuloDomain;
use Nucleo\Core\ApiBase;
use Nucleo\Core\ErrorDominio;
use Nucleo\Core\RespuestaJson;

class ArticulosApi extends ApiBase
{
    public function show($id)
    {
        $item = ArticuloDomain::get($this->app, intval($id));
        if (!$item) {
            throw new ErrorDominio(ErrorDominio::NO_ENCONTRADO);
        }
        return ['item' => $item];
    }

    public function create()
    {
        $cuerpo = $this->cuerpo();
        $id = ArticuloDomain::save($this->app, [
            'name' => $this->campotexto($cuerpo, 'name'),
            'stock' => $this->campoentero($cuerpo, 'stock', 0),
        ]);
        return RespuestaJson::datos(['idarticulo' => $id], 201);
    }
}
```

`cuerpo()` lee el JSON de la petición; `campotexto()` y `campoentero()` validan un campo y lanzan `DATOS_INVALIDOS` si no encaja.

## Rutas

En `src/RoutesApi.php` (o `RoutesConector.php`), nuevas en inglés. `'public' => true` solo para lo que de verdad es público (estado, webhooks verificados por firma), y con el porqué.

## Errores comunes

| Mal | Bien |
|---|---|
| `$this->app->request->get('token')` | Cabecera `Authorization`; el guard ya la valida |
| `return json_encode($datos)` | `return $datos` |
| `'total' => '12,50 €'` | `'total' => 125000` |
| `$_POST['name']` | `$this->campotexto($this->cuerpo(), 'name')` |
| `Articulo::search($this->app->db, …)` | `ArticuloDomain::list($this->app, …)` |
