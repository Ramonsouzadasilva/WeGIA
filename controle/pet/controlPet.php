<?php
require_once '../../classes/session.php';
ini_set('display_erros', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);
extract($_REQUEST);

 include_once $nomeClasse . '.php';
 $objeto = new $nomeClasse();
 $objeto->$metodo();

?>