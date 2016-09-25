<?php

/*
|--------------------------------------------------------------------------
| Timezone
|--------------------------------------------------------------------------
|
| Set the default timezone.
|
*/

use Slim\Http\Request;
use Slim\Http\Response;

date_default_timezone_set('Europe/Brussels');

/*
|--------------------------------------------------------------------------
| Time language
|--------------------------------------------------------------------------
|
| Set the time language.
|
*/

setlocale(LC_TIME, 'nl_NL');

/*
|--------------------------------------------------------------------------
| Error reporting
|--------------------------------------------------------------------------
|
| Show error reports or not.
|
*/

ini_set('display_errors', 'On');
libxml_use_internal_errors(true);

/*
|--------------------------------------------------------------------------
| Root URL
|--------------------------------------------------------------------------
|
| Set up the root URL as a constant.
|
*/

define('INC_ROOT', dirname(__DIR__));

/*
|--------------------------------------------------------------------------
| Dependencies
|--------------------------------------------------------------------------
|
| Autoload the dependencies from the vendor folder.
|
*/

require INC_ROOT . '/vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Route argument
|--------------------------------------------------------------------------
|
| TODO
|
*/

array_shift($argv); // Discard the filename

$pathInfo = array_shift($argv);

if (empty($pathInfo)) {
    $pathInfo = '';
}

/*
|--------------------------------------------------------------------------
| Slim instance
|--------------------------------------------------------------------------
|
| Create a new slim instance and define the current application mode.
| Pull in Twig and define the views path.
|
*/

$app = new Slim\App([
    'settings' => [
        'displayErrorDetails' => true,
        'mode' => file_get_contents(INC_ROOT . '/mode.php'),
    ],
    'environment' => \Slim\Http\Environment::mock([
        'REQUEST_URI' => '/' . $pathInfo,
    ]),
]);

$container = $app->getContainer();

//$app->subRequest('GET', '/home');

/*
|--------------------------------------------------------------------------
| Load config
|--------------------------------------------------------------------------
|
| Load config into Slim.
|
*/

$container['config'] = function($container) {
    return \Noodlehaus\Config::load(INC_ROOT . '/config/' . trim($container->settings->get('mode')) . '.php');
};

/*
|--------------------------------------------------------------------------
| Eloquent
|--------------------------------------------------------------------------
|
| Boot up Eloquent for further use.
| New models will be able to extend Eloquent.
|
*/

$capsule = new Illuminate\Database\Capsule\Manager();
$capsule->addConnection($container->config->get('db'));
$capsule->setAsGlobal();
$capsule->bootEloquent();

$container['db'] = function($container) use ($capsule) {
    return $capsule;
};

/*
|--------------------------------------------------------------------------
| Not found handles
|--------------------------------------------------------------------------
| TODO
|
*/

$container['notFoundHandler'] = function($container) {
    return function(Request $request, Response $response) use($container) {

        $response->getBody()->write('Route not found');

        return $response;
    };
};

/*
|--------------------------------------------------------------------------
| Routes
|--------------------------------------------------------------------------
| TODO
|
*/

require INC_ROOT . '/bootstrap/routes.php';