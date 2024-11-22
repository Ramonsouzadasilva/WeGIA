<?php
$config_path = "config.php";
if(file_exists($config_path)){
    require_once($config_path);
}else{
    while(true){
        $config_path = "../" . $config_path;
        if(file_exists($config_path)) break;
    }
    require_once($config_path);
}

//Inicia a sessão
session_start();

//Ao inicar a sessão, redireciona o usuário para a página principal
if(!isset($_SESSION['usuario'])){
	header ("Location: ".WWW."index.php");
}

require_once ROOT."/controle/memorando/AnexoControle.php";

$id_anexo = $_GET['id_anexo'];
$extensao = $_GET['extensao'];
$nome = $_GET['nome'];

//Cria um novo objeto (Anexo de controle)
$AnexoControle = new AnexoControle;
$AnexoControle->listarAnexo($id_anexo);

header('Content-Type: application/force-download');
header('Content-Disposition: attachment; filename="' . $nome . '.' . $extensao . '"');

//Header('Content-Disposition: attachment; filename="'.$nome.'.'.$extensao);
echo $_SESSION['arq'][0]['anexo'];
?>