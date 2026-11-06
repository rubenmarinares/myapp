<?php
use App\Screens\IndexScreen;

// [métodos, patrón, [Clase, método], opciones]. Opciones: 'public', 'perm', 'module', 'csrf'.
return [
    [['GET'], '', [IndexScreen::class, 'index']],
];
