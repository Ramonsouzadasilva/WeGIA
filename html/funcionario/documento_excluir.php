<?php

session_start();
if (!isset($_SESSION["usuario"])){
    header("Location: ../../index.php");
}

// Verifica Permissão do Usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 11, 7);

require_once "../../dao/Conexao.php";
require_once "./Documento.php";

extract($_GET);

$arquivo = new DocumentoFuncionario($id_doc);
if (!$arquivo->getException()){
    $arquivo->delete();
    $sql = "SELECT f.id_fundocs, f.`data`, docf.nome_docfuncional FROM funcionario_docs f JOIN funcionario_docfuncional docf ON f.id_docfuncional = docf.id_docfuncional WHERE id_funcionario = " . $_GET['id_funcionario'] . ";";
    $pdo = Conexao::connect();
    $docfuncional = $pdo->query($sql);
    $docfuncional = $docfuncional->fetchAll(PDO::FETCH_ASSOC);
    $docfuncional = json_encode($docfuncional);
    echo $docfuncional;
}else{
    echo $arquivo->getException();
}

die();

