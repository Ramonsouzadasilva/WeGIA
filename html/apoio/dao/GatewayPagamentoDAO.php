<?php
//requisitar arquivo de conexão

require_once '../dao/ConexaoDAO.php';

class GatewayPagamentoDAO
{

    private $pdo;

    public function __construct()
    {
        $this->pdo = ConexaoDAO::conectar();
    }

    /**
     * Inseri um gateway de pagamento no banco de dados da aplicação
     */
    public function cadastrar($nome, $endpoint, $token, $status)
    {
        /*Lógica da aplicação */
        //definir consulta SQL
        $sqlCadastrar = "INSERT INTO contribuicao_gatewayPagamento (plataforma, endpoint, token, status) 
        VALUES (:plataforma, :endpoint, :token, :status)";
        //utilizar prepared statements
        $stmt = $this->pdo->prepare($sqlCadastrar);
        $stmt->bindParam(':plataforma', $nome);
        $stmt->bindParam(':endpoint', $endpoint);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':status', $status);
        //executar
        $stmt->execute();
    }

    /**
     * Busca os gateways de pagamento registrados no banco de dados da aplicação
     */
    public function buscaTodos()
    {
        //definir consulta sql
        $sqlBuscaTodos = "SELECT * from contribuicao_gatewayPagamento";
        //executar
        $resultado = $this->pdo->query($sqlBuscaTodos)->fetchAll(PDO::FETCH_ASSOC);
        //retornar resultado
        return $resultado;
    }

    /**
     * Remover o gateway de pagamento que possuí id equivalente no banco de dados da aplicação
     */
    public function excluirPorId($id)
    {
        //definir consulta sql
        $sqlExcluirPorId = "DELETE FROM contribuicao_gatewayPagamento WHERE id=:id";
        //utilizar prepared statements
        $stmt = $this->pdo->prepare($sqlExcluirPorId);
        $stmt->bindParam(':id', $id);
        //executar
        $stmt->execute();

        //verificar se algum elemento foi de fato excluído
        $gatewayExcluido = $stmt->rowCount();

        if ($gatewayExcluido < 1) {
            throw new Exception();
        }
    }

    /**
     * Modifica os campos da tabela contribuicao_gatewaypagamento relacionados ao id informado
     */
    public function editarPorId($id, $nome, $endpoint, $token)
    {
        //definir consulta sql
        $sqlEditarPorId = "UPDATE contribuicao_gatewayPagamento SET plataforma =:nome, endpoint =:endpoint, token =:token WHERE id=:id";
        //utilizar prepared statements
        $stmt = $this->pdo->prepare($sqlEditarPorId);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':endpoint', $endpoint);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':id', $id);
        //executar
        $stmt->execute();

        //verificar se algum elemento foi de fato alterado
        $gatewayExcluido = $stmt->rowCount();

        if ($gatewayExcluido < 1) {
            throw new Exception();
        }
    }

    /**
     * Modifica o campo status da tabela contribuica_gatewayPagamento de acordo com o id fornecido
     */
    public function alterarStatusPorId($status, $gatewayId)
    {
        //definir consulta sql
        $sqlAlterarStatusPorId = "UPDATE contribuicao_gatewayPagamento SET status =:status WHERE id=:gatewayId";
        //utilizar prepared statements
        $stmt = $this->pdo->prepare($sqlAlterarStatusPorId);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':gatewayId', $gatewayId);
        //executar
        $stmt->execute();

        //verificar se algum elemento foi de fato alterado
        $gatewayAlterado = $stmt->rowCount();

        if ($gatewayAlterado < 1) {
            throw new Exception();
        }
    }

    public function buscarPorId($id)
    {
        //definir consulta sql
        $sqlBuscarPorId = "SELECT * FROM contribuicao_gatewayPagamento WHERE id=:id";
        //utilizar prepared statements
        $stmt = $this->pdo->prepare($sqlBuscarPorId);
        $stmt->bindParam(':id', $id);
        //executar
        $stmt->execute();
        //resultado
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        //retornar resultado
        return $resultado;
    }
}
