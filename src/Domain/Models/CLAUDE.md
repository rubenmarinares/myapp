# Domain\Models

Una clase por tabla sobre `ModelBase`. El esquema (`src/dbschema.php`) dice cómo es la tabla; el modelo solo dice cuál es. Reciben `$db`, no `$app`. (Detalle: skill `nucleo`, `references/models.md`.)

## Reglas duras

- `static public $tablename = 'almacen_articulos';` con el nombre exacto (en plural). Primaria, obligatorios y tipos, en el esquema.
- **Sin tipos** en propiedades ni en métodos heredados (`static function search($db, $filters = [])`): las aplicaciones existentes los sobrescriben así.
- Sin sesión, petición, redirecciones ni Twig.
- **API heredada antes que SQL**: `search`, `searchlimit`, `findone`, `count`, `get`, `getall`, `exists`, `getascombo`, `insert`, `update`, `upsert`, `delete`.
- **SQL a mano solo aquí**, en un método, con parámetros (`?`). Nada de `" . $valor . "`, `sprintf('%s')` ni `implode()` de valores. Identificadores desde el esquema o con `Dialect::assertidentifier()`. El hook de SQL lo comprueba.
- **Columna nueva**: en el esquema y en la migración, en el mismo cambio. El esquema refleja cómo ve la aplicación el dato; no se regenera en bloque desde la base.
- Sin formato: nada de `*_fmt` en `enrichvalues`; el formato es de Twig.

## Ejemplo

```php
<?php
namespace App\Domain\Models;

use Nucleo\Core\ModelBase;

class Articulo extends ModelBase
{
    static public $tablename = 'almacen_articulos';

    static public $joins = [['almacen_familias', 'familiaid', 'idfamilia', ['familia']]];

    static public $combos = ['status' => [['id' => 1, 'label' => 'Activo'], ['id' => 2, 'label' => 'Descatalogado']]];

    static function customfilter($db, &$filters)
    {
        $texto = trim((string) ($filters['q'] ?? ''));
        unset($filters['q']);
        if ($texto === '') {
            return ['sql' => '', 'params' => []];
        }
        return ['sql' => ' AND (almacen_articulos.name LIKE ? OR almacen_articulos.code LIKE ?) ', 'params' => ["%$texto%", "%$texto%"]];
    }

    /** Se llama dentro de la transacción que abre el Domain. */
    static function lockstock($db, $idarticulo)
    {
        return intval($db->runone('SELECT stock FROM almacen_articulos WHERE idarticulo = ? FOR UPDATE', [$idarticulo])['stock'] ?? 0);
    }
}
```

## Filtros de search()

`['col' => valor]`, `['col' => [lista]]` y `['clave' => ['mode' => 'LIKE', 'values' => $texto, 'field' => 'col', 'table' => 'tabla']]`. Modos: `EQUAL`, `NOT_EQUAL`, `LIKE` (el `%` lo pone `ModelBase`), `IN`, `BETWEEN`, `GT`, `LT`, `GTE`, `LTE`. Tabla y columna se comprueban contra el esquema. `customfilter()` solo para `OR`, subconsultas o agregados, y quitando su clave de `$filters`.

Cláusula `IN` a mano: `$marcas = implode(',', array_fill(0, count($ids), '?'));` y `$db->run("… IN ($marcas)", $ids)`.

## Enriquecer

- `enrichvalues($row)`: lo que se calcula de la propia fila (disponible = stock − reservado, flags). `status_label` ya lo pone `ModelBase` desde `$combos`.
- Datos de otra tabla para cada fila: sobrescribe `search()`, llama a `parent::search()` y lanza una segunda consulta con los ids; nada de N+1.

## Errores comunes

| Mal | Bien |
|---|---|
| `static string $table; static array $primaries` | `static public $tablename` y la primaria en el esquema |
| `static function search($db, array $filters = []): array` | Sin tipos |
| `$sql .= " AND name LIKE '%$texto%'"` | `['name' => ['mode' => 'LIKE', 'values' => $texto]]` |
| `"… IN (" . implode(',', $ids) . ")"` | Marcadores y `$ids` como parámetros |
| `$row['precio_fmt'] = Money::toStr($row['precio'])` | `{{ item.precio\|monedaatexto }}` |
| Tabla en singular deducida | El nombre exacto del esquema |
