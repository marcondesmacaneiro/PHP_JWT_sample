<?php

date_default_timezone_set("America/Sao_Paulo");

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/JWTWrapper.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Silex\Application;

$app = new Silex\Application();

$app->post('/login', function(Request $request) use ($app) {
    $dados = json_decode($request->getContent(), true);

    if ($dados['usuario'] == 'marcondes' && $dados['password'] == 'marcondes') {

        $jwt = JWTWrapper::encode([
                    'expiration_sec' => 3600,
                    'iss' => 'marcondesmacaneiro.com',
                    'userdata' => [
                        'id' => 1,
                        'name' => 'Marcondes Macaneiro'
                    ]
        ]);
        return $app->json([
                    'login' => 'true',
                    'access_token' => $jwt
        ]);
    }
    return $app->json([
                'login' => 'false',
                'message' => 'Login Inálido'
    ]);
});

// verificar autenticacao
$app->before(function(Request $request, Application $app) {
    $route = $request->get('_route');

    if ($route != 'POST_login') {
        $authorization = $request->headers->get("jwt-token");
        list($jwt) = sscanf($authorization, 'Bearer %s');

        if ($jwt) {
            try {
                $app['jwt'] = JWTWrapper::decode($jwt);
            } catch (Exception $ex) {
                // nao foi possivel decodificar o token jwt
                return new Response('Acesso nao autorizado', 400);
            }
        } else {
            // nao foi possivel extrair token do header Authorization
            return new Response('Token nao informado', 400);
        }
    }
});

// rota deve ser acessada somente por usuario autorizado com jwt
$app->get('/home', function(Application $app) {
    return new Response('Olá ' . $app['jwt']->data->name);
});

$app->run();
