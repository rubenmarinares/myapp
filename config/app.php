<?php
use Modules\Users\Domain\AuthDomain;
use Nucleo\Core\BearerGuard;
use Nucleo\Core\Env;
use Nucleo\Core\ModuleRegistry;
use Nucleo\Core\SessionGuard;
use Nucleo\Core\TokenGuard;

/** @var string $basepath directorio de la aplicación; lo pone Boot */

// La sesión se comprueba contra la base de datos en cada petición: desactivar un usuario
// o bajarle los permisos tiene efecto en su siguiente clic.
$sesion = new SessionGuard([AuthDomain::class, 'refresh']);

return [
    'name' => Env::get('APP_NAME', 'aplicacion'),
    'debug' => Env::bool('APP_DEBUG'),
    'timezone' => 'Europe/Madrid',
    'home' => '/app/',

    'db' => [
        'dsn' => Env::required('DB_DSN'),
        'user' => Env::get('DB_USERNAME', ''),
        'password' => Env::get('DB_PASSWORD', ''),
    ],

    // Tablas propias de la aplicación. Las de los módulos las aporta cada módulo.
    'schema' => $basepath . '/src/dbschema.php',

    // Un frontal por público: mismo Domain, distintas pantallas.
    'fronts' => [
        'app' => [
            'prefix' => '/app',
            'kind' => 'html',
            'guard' => $sesion,
            'login' => 'login',
            'views' => [$basepath . '/src/Screens/Views'],
            'layout' => 'layout.html',
            'routes' => $basepath . '/src/RoutesApp.php',
            'menu' => $basepath . '/src/Menu.php',
        ],
        'mobile' => [
            'prefix' => '/mobile',
            'kind' => 'html',
            'guard' => $sesion,
            'login' => '/app/login',
            'views' => [$basepath . '/src/MobileScreens/Views'],
            'layout' => 'layout.html',
            'routes' => $basepath . '/src/RoutesMobile.php',
        ],
        'api' => [
            'prefix' => '/api',
            'kind' => 'json',
            'guard' => new BearerGuard(Env::required('JWT_KEY'), 'api', [AuthDomain::class, 'fromtoken']),
            'routes' => $basepath . '/src/RoutesApi.php',
        ],
        // Para otros servidores (el plugin de WordPress): una clave compartida en una cabecera. Sin CONECTOR_TOKEN queda cerrado.
        'conector' => [
            'prefix' => '/conector',
            'kind' => 'json',
            'guard' => new TokenGuard('X-Nucleo-Token', (string) Env::get('CONECTOR_TOKEN', '')),
            'routes' => $basepath . '/src/RoutesConector.php',
        ],
    ],

    // Módulos cargados: los incluidos en el paquete y los propios de modules/.
    'modules' => [
        ModuleRegistry::builtin('users'),
        ModuleRegistry::builtin('notes'),
    ],
];
