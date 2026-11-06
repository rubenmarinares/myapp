# MobileScreens

Pantallas del frontal móvil (`/mobile`). Mismas reglas que `src/Screens` —el hook de capas revisa esta carpeta igual— y llaman a **los mismos Domain** que las de escritorio. (Detalle: skill `nucleo`, `references/mobilescreens.md`.)

## Reglas duras

- No usan `Domain\Models` ni ejecutan SQL.
- Sin lógica de negocio: si la pantalla móvil necesita algo distinto, el Domain ofrece un método; no se duplica la regla aquí.
- Sin formato: valores en bruto, filtros en Twig.
- `$this->rendertemplate('x.html', [...])` con plantillas de `src/MobileScreens/Views/`.
- Rutas en `src/RoutesMobile.php`; las nuevas, en inglés.

## Ejemplo

```php
<?php
namespace App\MobileScreens;

use App\Domain\ArticuloDomain;
use Nucleo\Core\ScreenBase;

class StockScreen extends ScreenBase
{
    public function index()
    {
        $this->rendertemplate('stock.html', ['items' => ArticuloDomain::lowstock($this->app)]);
    }
}
```

## Qué cambia respecto a escritorio

- Comparte el guard de sesión con `app`; el login es el de escritorio.
- Formularios cortos y una acción principal por pantalla.
- Enlaces con `{{ APP_PATH }}` del propio frontal, nunca escritos a mano.

## Errores comunes

| Mal | Bien |
|---|---|
| Copiar la lógica de la pantalla de escritorio | Moverla al Domain y llamarla desde las dos |
| Un `ArticuloMobileDomain` | Un único Domain; cambia la presentación |
| `use App\Domain\Models\…` | Un método del Domain |
| `<script>` en un fragmento cargado por AJAX | Clase o `data-*` y la inicialización global del layout |
