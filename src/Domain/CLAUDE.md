# Domain

Las reglas de negocio. Clases sin estado con métodos `static public` que reciben `$app` primero y hablan con los modelos a través de `$app->db`. (Detalle: skill `nucleo`, `references/domain.md`.)

## Reglas duras

- **`$app` primero**: `static public function save($app, $item)`; dentro, `Articulo::insert($app->db, $fila)`.
- **No toca la petición ni la presentación**: ni `$_POST`, `$_GET`, `$_SESSION`, ni Twig, `echo`, `header()`, redirecciones o CSRF. El hook de capas lo comprueba.
- **Devuelve datos en bruto**: importes en diezmilésimas, fechas en entero `AAAAMMDD`, estados como código. Nada de `date()`, `number_format()` ni `getformatted…()`.
- **No construye SQL**: arma filtros de `search()`; si no basta, pide un método al modelo.
- **Lista blanca**: la constante `EDITABLE` dice qué campos acepta de un formulario o de la API. Nunca pases la petición entera a `insert()`.
- **Valida pronto** y señala lo previsto con `ErrorDominio(código, detalle)`.
- **Transacciones aquí**, con `static::entransaccion($app->db, fn () => ...)`, alrededor de todos los modelos que participan.
- Constantes con nombre para estados y tipos; la pantalla no las usa para montar consultas: el Domain expone el método (`confirmed($app)`).
- Antes de llamar a un método, comprueba que existe.

## Ejemplo

```php
<?php
namespace App\Domain;

use App\Domain\Models\Articulo;
use Nucleo\Core\DomainCRUD;
use Nucleo\Core\ErrorDominio;

class ArticuloDomain extends DomainCRUD
{
    static public $entity = Articulo::class;

    const EDITABLE = ['name', 'stock'];

    static public function save($app, $item)
    {
        $fila = array_intersect_key((array) $item, array_flip(self::EDITABLE));
        $id = intval($item['idarticulo'] ?? 0);
        if ($id === 0 || array_key_exists('name', $fila)) {
            $fila['name'] = trim((string) ($fila['name'] ?? ''));
            if ($fila['name'] === '') {
                throw new ErrorDominio(ErrorDominio::DATOS_INVALIDOS, ['campo' => 'name', 'mensaje' => 'El nombre es obligatorio.']);
            }
        }
        if ($id === 0) {
            return Articulo::insert($app->db, $fila);
        }
        Articulo::update($app->db, ['idarticulo' => $id] + $fila);
        return $id;
    }

    static public function lowstock($app, $minimo = 5)
    {
        return Articulo::search($app->db, ['stock' => ['mode' => 'LT', 'values' => intval($minimo)]]);
    }
}
```

`DomainCRUD` ya da `list`, `get`, `save`, `delete`, `getascombo` y `getcombos`; con `$referencias`, `enuso()` y `eliminar()` se niegan a borrar lo que está en uso.

## Detalles

- Una lectura que cruza dos agregados se compone aquí con los dos modelos, no con un JOIN desde uno de ellos.
- Un bloqueo `FOR UPDATE` lo hace un método del modelo; la transacción la abre el Domain.
- Servicios externos detrás de una interfaz, con un doble en los tests. Claves en `.env`.
- El usuario en curso, si hace falta: `Runtime::userid()` o un parámetro. La auditoría (`createdby`, `modifiedby`) la pone `ModelBase`.

## Errores comunes

| Mal | Bien |
|---|---|
| `static function save($item)` | `static public function save($app, $item)` |
| `$app->db->run("UPDATE …")` | `Articulo::update($app->db, [...])` o un método del modelo |
| `return number_format($total / 10000, 2)` | `return $total` |
| `$_SESSION['user']['id']` | `Runtime::userid()` o un parámetro |
| `Articulo::insert($app->db, $item)` con todo lo recibido | `array_intersect_key($item, array_flip(self::EDITABLE))` |
| Decorar filas a posteriori (último acceso, totales) | Enriquecer en el modelo para que la forma del dato sea siempre la misma |
