<?php
require_once '../../classes/session.php';
require_once "./controleSaudePet.php";

$post = json_decode(file_get_contents("php://input"));
$dado = [];

foreach( $post as $valor){
    $dado[] = $valor;
}

$c = new controleSaudePet();
$metodo = $dado[1];
$dado = $c->$metodo($dado[0]);

echo json_encode($dado);