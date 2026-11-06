# Screens

Una pantalla lee la petición, llama a un Domain y pinta una plantilla o redirige. Nada más. (Detalle: skill `nucleo`, `references/screens.md`.)

## Reglas duras

- **No usa `Domain\Models`** ni ejecuta SQL (`->run(`, `->runone(`, `->query(`…). Si el modelo ya tiene el método, crea igualmente el envoltorio en el Domain. El hook de capas lo comprueba.
- **Sin lógica de negocio**: ni sumas, ni recuentos, ni condiciones de negocio, ni constantes del Domain para montar filtros.
- **Sin formato**: pasa valores en bruto; el formato lo pone Twig con filtros.
- Pinta con `$this->rendertemplate('x.html', [...])`; las plantillas son `.html`.
- Variables de la vista en minúsculas sin separadores (`relatedprojects`).
- Rutas nuevas en inglés; las existentes no se renombran.

## El método, en seis pasos

1. Parámetros de la ruta (argumentos) y de la petición (`$this->app->request->get()`, `->post()`).
2. Permisos que la ruta no cubre (`$this->can('modulo', 'write')`) y pertenencia del recurso.
3. CSRF: lo comprueba el kernel en todo POST de un frontal `html`.
4. Llamada al Domain con `$this->app`.
5. Datos para la vista, sin formatear.
6. `rendertemplate()`, o `$this->app->redirect('ruta')` tras escribir (POST → redirect → GET).

```php
<?php
namespace App\Screens;

use App\Domain\ArticuloDomain;
use Nucleo\Core\HttpError;
use Nucleo\Core\ScreenBase;

class ArticulosScreen extends ScreenBase
{
    public function edit($id = 0)
    {
        $item = intval($id) > 0 ? ArticuloDomain::get($this->app, intval($id)) : ['idarticulo' => 0];
        if (!$item) {
            throw new HttpError(404, 'El artículo no existe.');
        }
        $this->rendertemplate('articles/edit.html', ['item' => $item]);
    }

    public function save($id = 0)
    {
        ArticuloDomain::save($this->app, ['idarticulo' => intval($id)] + (array) $this->app->request->post());
        $this->app->addmessage('Cambios guardados.');
        $this->app->redirect('articles');
    }
}
```

## Detalles

- Una ruta sin `{id}` llama al método sin argumentos: declara `edit($id = 0)` si sirve a las dos.
- Fechas de formulario: convierte aquí el texto a entero `AAAAMMDD`; el Domain lo valida.
- Errores previstos: el Domain lanza `ErrorDominio`; el kernel vuelve al formulario con el aviso. Un recurso inexistente es `HttpError(404, …)`.
- Webhooks: ruta `'public' => true, 'csrf' => false` y verificación de la firma en el manejador.

## Errores comunes

| Mal | Bien |
|---|---|
| `use App\Domain\Models\Articulo;` | `ArticuloDomain::get($this->app, $id)` |
| `$this->app->db->run('SELECT …')` | Un método del Domain |
| `'total' => number_format($t / 10000, 2)` | `'total' => $t` y `{{ total\|monedaatexto }}` en la vista |
| `Domain::save($this->app, $_POST)` | `->request->post()`; el Domain filtra con `EDITABLE` |
| `$this->render('x.twig')` | `$this->rendertemplate('x.html')` |
| Variable calculada que no llega a `rendertemplate()` | Repasar que la vista recibe todo lo que usa |
