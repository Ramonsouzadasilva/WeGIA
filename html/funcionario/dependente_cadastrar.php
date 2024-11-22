<?php

session_start();
if (!isset($_SESSION["usuario"])){
    header("Location: ../../index.php");
}

// Verifica Permissão do Usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 11, 7);
require_once '../../dao/Conexao.php';
$pdo = Conexao::connect();

// Pessoa

$cpf = $_POST['cpf'];
$nome = $_POST['nome'];
$sobrenome = $_POST['sobrenome'];
$sexo = $_POST['sexo'];
$telefone = $_POST['telefone'];
$data_nascimento = $_POST['nascimento'];
$registro_geral = $_POST['rg'];
$orgao_emissor = $_POST['orgao_emissor'];
$data_expedicao = $_POST['data_expedicao'];

define("NOVA_PESSOA", "INSERT IGNORE INTO pessoa (cpf, nome, sobrenome, sexo, telefone, data_nascimento, registro_geral, orgao_emissor, data_expedicao) VALUES 
                            (:cpf, :nome, :sobrenome, :sexo, :telefone, :data_nascimento, :registro_geral, :orgao_emissor, :data_expedicao)");
try {
    $pessoa = $pdo->prepare(NOVA_PESSOA);
    $pessoa->bindValue(":cpf", $cpf);
    $pessoa->bindValue(":nome", $nome);
    $pessoa->bindValue(":sobrenome", $sobrenome);
    $pessoa->bindValue(":sexo", $sexo);
    $pessoa->bindValue(":telefone", $telefone);
    $pessoa->bindValue(":data_nascimento", $data_nascimento);
    $pessoa->bindValue(":registro_geral", $registro_geral);
    $pessoa->bindValue(":orgao_emissor", $orgao_emissor);
    $pessoa->bindValue(":data_expedicao", $data_expedicao);
    $pessoa->execute();
} catch (PDOException $th) {
    echo "Houve um erro ao inserir a pessoa no banco de dados: $th";
    die();
}


// Dependente

$id_parentesco = $_POST['id_parentesco'];
$id_funcionario = $_POST['id_funcionario'];
try {
    $sql = "SELECT id_pessoa FROM pessoa WHERE cpf =:cpf";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':cpf', $cpf);
    $stmt->execute();
    $id_pessoa = $stmt->fetch(PDO::FETCH_ASSOC)["id_pessoa"];
} catch (PDOException $th) {
    echo "Houve um erro ao obter o id da pessoa do banco de dados: $th";
    die();
}

try {
    $id_funcionario = trim($id_funcionario);
    $id_pessoa = trim($id_pessoa);
    $id_parentesco = trim($id_parentesco);

    if(!is_numeric($id_funcionario) || !is_numeric($id_pessoa) || !is_numeric($id_parentesco)){
        echo 'Erro: Os parâmetros informados para a consulta não correspondem a um tipo válido de ID';
        die();
    }
    $sql = "INSERT IGNORE INTO funcionario_dependentes (id_funcionario, id_pessoa, id_parentesco) VALUES (:id_funcionario, :id_pessoa, :id_parentesco)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_funcionario', $id_funcionario);
    $stmt->bindParam(':id_pessoa', $id_pessoa);
    $stmt->bindParam(':id_parentesco', $id_parentesco);
    $stmt->execute();
} catch (PDOException $th) {
    echo "Houve um erro ao adicionar o dependente ao banco de dados: $th";
    die();
}

header("Location: profile_funcionario.php?id_funcionario=$id_funcionario");