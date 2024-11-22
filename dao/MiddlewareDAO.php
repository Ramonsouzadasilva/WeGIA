<?php

require_once '../dao/Conexao.php';

class MiddlewareDAO{

    private $pdo;

    public function __construct()
    {
        $this->pdo = Conexao::connect();
    }

    public function verificarPermissao($idPessoa, $controladora, $controladorasRecursos):bool{

        $permissao = false;

        //print_r($controladorasRecursos);
        //echo $controladora;

        $controladoraRecursos = $controladorasRecursos[$controladora];

        //print_r($controladoraRecursos);exit;

        $sqlCargo = 'SELECT id_cargo FROM funcionario WHERE id_pessoa=:idPessoa';
        $stmtCargo = $this->pdo->prepare($sqlCargo);
        $stmtCargo->bindParam(':idPessoa', $idPessoa);

        $stmtCargo->execute();

        $idCargo = $stmtCargo->fetch(PDO::FETCH_ASSOC)['id_cargo'];

        foreach($controladoraRecursos as $recurso){
            $sqlRecurso = 'SELECT * FROM permissao WHERE id_cargo=:idCargo and id_recurso=:idRecurso';

            $stmtRecurso = $this->pdo->prepare($sqlRecurso);
            $stmtRecurso->bindParam(':idCargo', $idCargo);
            $stmtRecurso->bindParam(':idRecurso', $recurso);

            $stmtRecurso->execute();

            if($stmtRecurso->rowCount() > 0){
                $permissao = true;break;
            }
        }

        return $permissao;
    }
}