<?php

session_start();
if (!isset($_SESSION["usuario"])){
    header("Location: ../../index.php");
}

// Verifica Permissão do Usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 11, 7);

require_once "../../dao/Conexao.php";

$id_dependente = $_POST["id_dependente"];

try {
    $pdo = Conexao::connect();
    
    $pdo->query("DELETE FROM funcionario_dependentes WHERE id_dependente = $id_dependente;");
    
    $response = $pdo->query("SELECT 
    fdep.id_dependente AS id_dependente, p.nome AS nome, p.cpf AS cpf, par.descricao AS parentesco
    FROM funcionario_dependentes fdep
    LEFT JOIN funcionario f ON f.id_funcionario = fdep.id_funcionario
    LEFT JOIN pessoa p ON p.id_pessoa = fdep.id_pessoa
    LEFT JOIN funcionario_dependente_parentesco par ON par.id_parentesco = fdep.id_parentesco
    WHERE fdep.id_funcionario = ".$_POST['id_funcionario']);
    $response = $response->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($response);
} catch (PDOException $th) {
    echo json_encode($th);
}

die();