<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Phalcon\Loader;
use Phalcon\Mvc\Micro;
use Phalcon\Di\FactoryDefault;
use Phalcon\Db\Adapter\Pdo\Mysql as PdoMysql;
use Phalcon\Http\Response;

$loader = new Loader();

$loader->registerNamespaces(
    [
        "Store\\Toys" => __DIR__ . "/models/",
    ]
);

$loader->register();

$di = new FactoryDefault();

$di->set(
    "db",
    function() {
        return new PdoMysql(
            [
                "host" => "127.0.0.1",
                "username" => "calhilcaw",
                "password" => "",
                "dbname" => "c9",
            ]
        );
    }
);

$app = new Micro($di);


// Define the routes here

$app->get(
    "/api/robots",
    function() use ($app) {
        
        $phql = "SELECT * FROM Store\\Toys\\Robots ORDER BY name";
        
        $robots = $app->modelsManager->executeQuery($phql);
        
        $data = [];
        
        foreach($robots as $robot) {
            $data[] = [
                "id" => $robot->id,
                "name" => $robot->name,
            ];
        }
        
        echo json_encode($data);
    }
);

$app->get(
    "/api/robots/search/{name}",
    function($name) use ($app) {
        $phql = "SELECT * FROM Store\\Toys\\Robots WHERE name LIKE :name: ORDER BY name";
        
        $robots = $app->modelsManager->executeQuery(
            $phql,
                [
                    "name" => "%" . $name . "%"
                ]
        );
        
        $data = [];
        
        foreach($robots as $robot) {
            $data[] = [
                "id" => $robot->id,
                "name" => $robot->name,
            ];
        }
        
        echo json_encode($data);
    }
);

$app->get(
    "/api/robots/{id:[0-9]+}",
    function($id) use ($app) {
        $phql = "SELECT * FROM Store\\Toys\\Robots WHERE id = :id:";
        
        $robot = $app->modelsManager->executeQuery(
            $phql,
            [
                "id" => $id,    
            ]
        )->getFirst();
        
        $response = new Response();
        
        if($robot === false) {
            $response->setJsonContent(
                [
                    "status" => "NOT FOUND",  
                ]
            );
        } else {
            $response->setJsonContent(
                [
                    "status" => "FOUND",
                    "data" => [
                        "id" => $robot->id,
                        "name" => $robot->name,
                    ]
                ]
            );
        }
        
        return $response;
    }
);

$app->post(
    "/api/robots",
    function() use ($app) {
        $robot = $app->request->getJsonRawBody();
        
        $phql = "INSERT INTO Store\\Toys\\Robots (name, type, year) VALUES (:name:, :type:, :year:)";
        
        $status = $app->modelsManager->executeQuery(
            $phql,
                [
                    "name" => $robot->name,
                    "type" => $robot->type,
                    "year" => $robot->year,
                ]
        );
        
        $response = new Response();
        
        if($status->success() === true) {
            $response->setStatusCode(201, "Created");
            
            $robot->id = $status->getModel()->id;
            
            $response->setJsonContent(
                [
                    "status" => "OK",
                    "data" => $robot,
                ]
            );
        } else {
            $response->setStatusCode(409, "Conflict");
            
            $errors = [];
            
            foreach($status->getMessages() as $message) {
                $errors[] = $message->getMessage();
            }
            
            $response->setJsonContent(
                [
                    "status" => "ERROR",
                    "messages" => $errors,
                ]    
            );
        }
        
        return $response;
    }
);

$app->put(
    "/api/robots/{id:[0-9]+}",
    function($id) use ($app) {
        $robot = $app->request->getJsonRawBody();
        
        $phql = "UPDATE Store\\Toys\\Robots SET name = :name:, type = :type:, year = :year: WHERE id = :id:";
        
        $status->modelsManager->executeQuery(
            $phql,
                [
                    "id" => $id,
                    "name" => $robot->name,
                    "type" => $robot->type,
                    "year" => $robot->year,
                ]
        );
        
        $response = new Response();
        
        if($status->success() === true) {
            $response->setJsonContent(
                [
                    "status" => "DON'T BE A TWAT, TWAT",    
                ]    
            );
        } else {
            $response->setStatusCode(409, "Conflict");
            
            $errors = [];
            
            foreach($status->getMessages() as $message) {
                $errors[] = $message->getMessage();
            }
            
            $reponse->setJsonContent(
                [
                    "status" => "DON'T BE A TWAT, TWAT",
                    "messages" => $errors,
                ]    
            );
        }
        
        return $response;
    }
);

$app->delete(
    "/api/robots/{id:[0-9]+}",
    function($id) use ($app) {
        $phql = "DELETE FROM Store\\Toys\\Robots WHERE id = :id:";
        
        $status = $app->modelsManager->executeQuery(
            $phql,
                [
                    "id" => $id,    
                ]
        );
        
        $response = new Response();
        
        if($status->success() === true) {
            $response->setJsonContent(
                [
                    "status" => "OK",    
                ]  
            );
        } else {
            $response->setStatusCode(409, "Conflict");
            
            $errors = [];
            
            foreach($status->getMessages() as $message) {
                $error[] = $message->getMessage();
            }
            
            $response->setJsonContent(
                [
                    "status" => "ERROR",
                    "messages" => $errors,
                ]  
            );
        }
        
        return $response;
    }
);

$app->notFound(function () use ($app) {
    $app->response->setStatusCode(404, "Not Found")->sendHeaders();
    echo 'This is crazy, but this page was not found!';
});

$app->handle();