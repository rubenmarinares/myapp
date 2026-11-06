# Views

Plantillas Twig `.html` con autoescape. Las de la aplicación se nombran tal cual (`articles/edit.html`); las de un módulo, `@modulo/…`. (Detalle: skill `nucleo`, `references/views.md`.)

## Reglas duras

- Una página completa empieza con `{% extends layout %}` (o el nombre real del layout: compruébalo; uno inexistente es un 500). Contenido en `{% block content %}`; el layout ya trae menú, avisos y pie.
- **Fragmentos y paneles laterales** (cargados por AJAX) no hacen `{% extends %}` y **nunca llevan `<script>`**: insertados con `innerHTML`, no se ejecutan. El comportamiento se engancha por clase o `data-*` desde la inicialización global del layout. El JS de una página va en `public/js/<nombre>.js`.
- Todo `<form method="post">` lleva `{{ print_csrf_token() }}`.
- **El formato se pone aquí**, con filtros: `|monedaatexto`, `|dateinttofrontend`, `|datesqltofrontend`. El Domain entrega valores en bruto.
- `{% if can('modulo', 'write') %}` solo oculta: el servidor comprueba igual.
- Poca lógica: la pantalla calcula un booleano y la plantilla lo consulta.
- Variables en minúsculas sin separadores.
- Al tocar una plantilla existente, no quites ni simplifiques su HTML/CSS.

## Variables globales

`layout`, `APP_PATH`, `PUBLIC_PATH`, `currentuser`, `logado`, `menu`, `messages`, `today`, `thisyear`. `app` y `app.user` no existen. URL con `{{ APP_PATH }}articles/{{ item.idarticulo }}` o `{{ url('articles') }}`.

## Ejemplo

```twig
{% extends layout %}
{% block content %}
<h1 class="h3 mb-3">{{ item.idarticulo ? item.name : 'Añadir' }}</h1>
<form method="post" action="{{ APP_PATH }}articles/{{ item.idarticulo }}">
  {{ print_csrf_token() }}
  <label class="form-label" for="name">Nombre</label>
  <input class="form-control" id="name" name="name" value="{{ item.name }}" required>
  <p class="text-muted">Precio: {{ item.precio|monedaatexto }} € · Alta: {{ item.fechaalta|dateinttofrontend }}</p>
  {% if can('almacen', 'write') %}
  <button class="btn btn-primary" type="submit">Guardar</button>
  {% endif %}
</form>
{% endblock %}
```

## Filtros

| Filtro | Qué hace |
|---|---|
| `monedaatexto`, `monedaatextous` | Diezmilésimas → `1.234,56` / `1,234.56` |
| `monedaaentero` | Texto → diezmilésimas |
| `dateinttofrontend` | `20261016` → `16/10/2026` |
| `datesqltofrontend` | `2026-10-16` → `16/10/2026` |
| `datetoint`, `datesqltoint` | Texto → `AAAAMMDD` |
| `truncate(n)`, `json_decode`, `filesizetotext` | Utilidades |

Un `<input type="date">` necesita `AAAA-MM-DD`, no lo que da `dateinttofrontend`.

## Errores comunes

| Mal | Bien |
|---|---|
| `value="{{ csrf_token }}"` | `{{ print_csrf_token() }}` |
| `{{ app.user.name }}` | `{{ currentuser.name }}` |
| `<script>` en un panel lateral | Clase o `data-*` + inicialización global |
| `{{ item.precio / 10000 }}` | `{{ item.precio\|monedaatexto }}` |
| `{% include 'layout/foot.html' %}` al final | El layout ya lo incluye |
| `{{ dato\|raw }}` con datos del usuario | Autoescape; `raw` solo para HTML propio |
