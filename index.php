<?php

require 'Slim/Slim.php';
require 'vendor/ActiveRecord.php';
require 'Views/TwigView.php';

// initialize ActiveRecord
// change the connection settings to whatever is appropriate for your mysql server 
ActiveRecord\Config::initialize(function($cfg)
{
    $cfg->set_model_directory('models');
    $cfg->set_connections(array(
        'development' => 'mysql://serverside:password@127.0.0.1/slimactiverecord'
    ));
});

$app = new Slim(array(
    'view' => 'TwigView'
));

// Configure Twig
TwigView::$twigDirectory = dirname(__FILE__) . '/vendor/Twig';

$app->get('/', function () use ($app) {
    $data['tasks'] = Task::find('all');
    $app->render('task/index.html', $data);
})->name('tasks');

$app->post('/task/new/', function () use ($app) {
    $task = new Task();
    $task->name = "My New Task";
    $task->done = 0;
    $task->save();
    if($task->id > 0)
    {
        $app->redirect($app->urlFor('tasks'));
    }
})->name('task_new');

$app->get('/task/:id/edit', function ($id) use ($app) {
    $data['task'] = Task::find($id);
    $app->render('task/edit.html', $data);
})->name('task_edit');

$app->post('/task/:id/edit', function ($id) use ($app) {
    $task = Task::find($id);
    $task->name = $app->request()->post('name');
    $task->done = $app->request()->post('done') === '1' ? 1 : 0;
    $task->save();
    if($task->id > 0)
    {
        $app->redirect($app->urlFor('tasks'));
    }
})->name('task_edit_post');

$app->get('/task/:id/delete', function ($id) use ($app) {
    $task = Task::find($id);
    $task->delete();
    $app->redirect($app->urlFor('tasks'));
})->name('task_delete');

$app->run();