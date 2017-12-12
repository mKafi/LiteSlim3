<?php 
$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();
$container['db'] = function ($container) use ($capsule) {
    return $capsule;
};
$container['flash'] = function () {
    return new \Slim\Flash\Messages();
};
$container[Controller::class] = function ($container) {     
    return new \App\Controllers\Admin\Controller($container);
};
$container[HomeController::class] = function ($container) {     
    return new \App\Controllers\Admin\HomeController($container);
};
$container[SignupController::class] = function ($container) {     
    return new \App\Controllers\Admin\Auth\SignupController($container);
};
$container[ImportPlansController::class] = function ($container) {     
    return new \App\Controllers\Admin\Import\ImportPlansController($container);
};
$container[ImportOptionsController::class] = function($container){
    return new \App\Controllers\Admin\Import\ImportOptionsController($container);
};
$container[ToolBox::class] = function ($container) {     
    return new \App\Controllers\API\ToolBox;
};
$container[SettingsController::class] = function ($container) {     
    return new \App\Controllers\API\SettingsController;
};
$container[OtpController::class] = function($container){
    return new App\Controllers\API\OtpController($container);
};
$container[SubscribePlanController::class] = function($container){
    return new App\Controllers\API\SubscribePlanController($container);
};
$container[Plans::class] = function($container){
    return new App\Controllers\Admin\Plans($container);
};
$container[PortWalletController::class] = function($container){
    return new App\Controllers\Admin\PortWalletController($container);
};
$container[ConfigurationController::class] = function($container){
    return new App\Controllers\Admin\ConfigurationController($container);
};
$container[CustomerController::class] = function($container){
    return new App\Controllers\Admin\CustomerController($container);
};
$container[UserController::class] = function($container){
    return new App\Controllers\Admin\UserController($container);
};

/* keep it at bottom */
$container['view'] = function($container){
    $view = new \Slim\Views\Twig(__DIR__.'/../resources/views',[
        'cache' => FALSE,
    ]);
    $view->addExtension(new \Slim\Views\TwigExtension($container->router,$container->request->getUri()));
    $view->getEnvironment()->addGlobal('flash',$container->flash);
    $view->getEnvironment()->addGlobal('uid', !empty($_SESSION['uid']) ? $_SESSION['uid'] : '');
    return $view;
};