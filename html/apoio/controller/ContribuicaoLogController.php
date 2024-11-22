<?php
//Requisições necessárias
require_once '../model/ContribuicaoLog.php';
require_once '../dao/ContribuicaoLogDAO.php';
require_once '../model/Socio.php';
require_once '../dao/SocioDAO.php';
require_once '../dao/MeioPagamentoDAO.php';
require_once '../dao/GatewayPagamentoDAO.php';
require_once '../model/GatewayPagamento.php';
require_once '../model/ContribuicaoLogCollection.php';
require_once '../../../config.php';

class ContribuicaoLogController
{

    private $pdo;

    public function __construct()
    {
        $this->pdo = ConexaoDAO::conectar(); //Considerar implementar injeção de dependência caso a aplicação precise de mais flexibilidade
    }

    /**
     * Cria um objeto do tipo ContribuicaoLog, chama o serviço de boleto registrado no banco de dados
     * e insere a operação na tabela de contribuicao_log caso o serviço seja executado com sucesso.
     */
    public function criarBoleto() //Talvez seja melhor separar em: criarBoleto, criarCarne e criarPix
    {
        $valor = filter_input(INPUT_POST, 'valor');
        $documento = filter_input(INPUT_POST, 'documento_socio');
        $formaPagamento = 'Boleto';

        //Verificar se existe um sócio que possua de fato o documento
        try {
            $socioDao = new SocioDAO($this->pdo);
            $socio = $socioDao->buscarPorDocumento($documento);

            if (is_null($socio)) {
                echo json_encode(['erro' => 'Sócio não encontrado']);
                exit();
            }

            $meioPagamentoDao = new MeioPagamentoDAO();
            $meioPagamento = $meioPagamentoDao->buscarPorNome($formaPagamento);

            if (is_null($meioPagamento)) {
                echo json_encode(['erro' => 'Meio de pagamento não encontrado']);
                exit();
            }

            //Procura pelo serviço de pagamento através do id do gateway de pagamento
            $gatewayPagamentoDao = new GatewayPagamentoDAO();
            $gatewayPagamentoArray = $gatewayPagamentoDao->buscarPorId($meioPagamento->getGatewayId());
            $gatewayPagamento = new GatewayPagamento($gatewayPagamentoArray['plataforma'], $gatewayPagamentoArray['endPoint'], $gatewayPagamentoArray['token'], $gatewayPagamentoArray['status']);

            //Requisição dinâmica e instanciação da classe com base no nome do gateway de pagamento
            $requisicaoServico = '../service/' . $gatewayPagamento->getNome() . $formaPagamento . 'Service' . '.php';

            if (!file_exists($requisicaoServico)) {
                echo json_encode(['erro' => 'Arquivo não encontrado']);
                exit();
            }

            require_once $requisicaoServico;

            $classeService = $gatewayPagamento->getNome() . $formaPagamento . 'Service';

            if (!class_exists($classeService)) {
                echo json_encode(['erro' => 'Classe não encontrada']);
                exit();
            }

            $servicoPagamento = new $classeService;
        } catch (PDOException $e) {
            //implementar tratamento de erro
            echo json_encode(['erro' => $e->getMessage()]);
            exit();
        }

        //Verificar qual fuso horário será utilizado posteriormente

        if (isset($_POST['dia']) && !empty($_POST['dia'])) {
            require_once '../../permissao/permissao.php';

            session_start();
            permissao($_SESSION['id_pessoa'], 4);

            $dataGeracao = date('Y-m-d');
            $dataVencimento = $_POST['dia'];
        } else {
            $dataGeracao = date('Y-m-d');
            $dataVencimento = date_modify(new DateTime(), '+7 day')->format('Y-m-d');
        }

        $contribuicaoLog = new ContribuicaoLog();
        $contribuicaoLog
            ->setValor($valor)
            ->setCodigo($contribuicaoLog->gerarCodigo())
            ->setDataGeracao($dataGeracao)
            ->setDataVencimento($dataVencimento)
            ->setSocio($socio);

        try {
            /*Controle de transação para que o log só seja registrado
            caso o serviço de pagamento tenha sido executado*/
            $this->pdo->beginTransaction();
            $contribuicaoLogDao = new ContribuicaoLogDAO($this->pdo);
            $contribuicaoLogDao->criar($contribuicaoLog);

            //Registrar na tabela de socio_log
            $mensagem = "Boleto gerado recentemente";
            $socioDao->registrarLog($contribuicaoLog->getSocio(), $mensagem);

            //Chamada do método de serviço de pagamento requisitado
            if (!$servicoPagamento->gerarBoleto($contribuicaoLog)) {
                $this->pdo->rollBack();
            } else {
                $this->pdo->commit();
            }
        } catch (PDOException $e) {
            //implementar tratamento de erro
            echo json_encode(['erro' => $e->getMessage()]);
        }
    }

    /**
     * Cria um objeto do tipo ContribuicaoLog, chama o serviço de carne registrado no banco de dados
     * e insere a operação na tabela de contribuicao_log caso o serviço seja executado com sucesso.
     */
    public function criarCarne()
    {
        $valor = filter_input(INPUT_POST, 'valor', FILTER_VALIDATE_FLOAT);
        $documento = filter_input(INPUT_POST, 'documento_socio');
        $qtdParcelas = filter_input(INPUT_POST, 'parcelas', FILTER_VALIDATE_INT);
        $diaVencimento = filter_input(INPUT_POST, 'dia', FILTER_VALIDATE_INT);
        $formaPagamento = 'Carne';

        //Verificar se existe um sócio que possua de fato o documento
        try {
            $socioDao = new SocioDAO($this->pdo);
            $socio = $socioDao->buscarPorDocumento($documento);

            if (is_null($socio)) {
                echo json_encode(['erro' => 'Sócio não encontrado']);
                exit();
            }

            $meioPagamentoDao = new MeioPagamentoDAO();
            $meioPagamento = $meioPagamentoDao->buscarPorNome($formaPagamento);

            if (is_null($meioPagamento)) {
                echo json_encode(['erro' => 'Meio de pagamento não encontrado']);
                exit();
            }

            //Procura pelo serviço de pagamento através do id do gateway de pagamento
            $gatewayPagamentoDao = new GatewayPagamentoDAO();
            $gatewayPagamentoArray = $gatewayPagamentoDao->buscarPorId($meioPagamento->getGatewayId());
            $gatewayPagamento = new GatewayPagamento($gatewayPagamentoArray['plataforma'], $gatewayPagamentoArray['endPoint'], $gatewayPagamentoArray['token'], $gatewayPagamentoArray['status']);

            //Requisição dinâmica e instanciação da classe com base no nome do gateway de pagamento
            $requisicaoServico = '../service/' . $gatewayPagamento->getNome() . $formaPagamento . 'Service' . '.php';

            if (!file_exists($requisicaoServico)) {
                echo json_encode(['erro' => 'Arquivo não encontrado']);
                exit();
            }

            require_once $requisicaoServico;

            $classeService = $gatewayPagamento->getNome() . $formaPagamento . 'Service';

            if (!class_exists($classeService)) {
                echo json_encode(['erro' => 'Classe não encontrada']);    
                exit();
            }

            $servicoPagamento = new $classeService;
        } catch (PDOException $e) {
            //implementar tratamento de erro
            echo json_encode(['erro' => $e->getMessage()]);
            exit();
        }

        //Criar coleção de contribuições
        $contribuicaoLogCollection = new ContribuicaoLogCollection();

        if (!$qtdParcelas || $qtdParcelas < 2) {
            //implementar mensagem de erro
            exit('O mínimo de parcelas deve ser 2');
        }

        // Pegar a data atual
        $dataAtual = new DateTime();

        if (isset($_POST['tipoGeracao']) && !empty($_POST['tipoGeracao'])) {
            //verificar autenticação do funcionário
            require_once '../../permissao/permissao.php';

            session_start();
            permissao($_SESSION['id_pessoa'], 4);

            //escolher qual ação tomar
            $tipoGeracao = $_POST['tipoGeracao'];

            //chamar funções
            require_once '../helper/Util.php';

            $datasVencimento;

            $diaVencimento = ($_POST['dia']);

            $qtd_p = intval($_POST['parcelas']);

            switch ($tipoGeracao) {
                case '1':
                    $datasVencimento = Util::mensalidadeInterna(1, $qtd_p, $diaVencimento);
                    break;
                case '2':
                    $datasVencimento = Util::mensalidadeInterna(2, $qtd_p, $diaVencimento);
                    break;
                case '3':
                    $datasVencimento = Util::mensalidadeInterna(3, $qtd_p, $diaVencimento);
                    break;
                case '6':
                    $datasVencimento = Util::mensalidadeInterna(6, $qtd_p, $diaVencimento);
                    break;
                default:
                    echo json_encode(['erro' => 'O tipo de geração é inválido.']);
                    exit();
            }

            foreach ($datasVencimento as $dataVencimento) {
                $contribuicaoLog = new ContribuicaoLog();
                $contribuicaoLog
                    ->setValor($valor)
                    ->setCodigo($contribuicaoLog->gerarCodigo())
                    ->setDataGeracao($dataAtual->format('Y-m-d'))
                    ->setDataVencimento($dataVencimento)
                    ->setSocio($socio);

                //Inserir na coleção
                $contribuicaoLogCollection->add($contribuicaoLog);
            }
        } else {

            // Verificar se o dia informado já passou neste mês
            if ($diaVencimento <= $dataAtual->format('d')) {
                // Se o dia informado já passou, começar a partir do próximo mês
                $dataAtual->modify('first day of next month');
            }

            for ($i = 0; $i < $qtdParcelas; $i++) {
                // Clonar a data atual para evitar modificar o objeto original
                $dataVencimento = clone $dataAtual;

                // Adicionar os meses de acordo com o índice da parcela
                $dataVencimento->modify("+{$i} month");

                // Definir o dia do vencimento para o dia informado
                $dataVencimento->setDate($dataVencimento->format('Y'), $dataVencimento->format('m'), $diaVencimento);

                // Ajustar a data caso o mês não tenha o dia informado (por exemplo, 30 de fevereiro)
                if ($dataVencimento->format('d') != $diaVencimento) {
                    $dataVencimento->modify('last day of previous month');
                }

                $contribuicaoLog = new ContribuicaoLog();
                $contribuicaoLog
                    ->setValor($valor)
                    ->setCodigo($contribuicaoLog->gerarCodigo())
                    ->setDataGeracao($dataAtual->format('Y-m-d'))
                    ->setDataVencimento($dataVencimento->format('Y-m-d'))
                    ->setSocio($socio);

                //Inserir na coleção
                $contribuicaoLogCollection->add($contribuicaoLog);
            }
        }

        try {
            /*Controle de transação para que o log só seja registrado
            caso o serviço de pagamento tenha sido executado*/
            $this->pdo->beginTransaction();

            foreach ($contribuicaoLogCollection as $contribuicaoLog) {
                $contribuicaoLogDao = new ContribuicaoLogDAO($this->pdo);
                $contribuicaoLogDao->criar($contribuicaoLog);
            }

            //Registrar na tabela de socio_log
            $mensagem = "Carnê gerado recentemente";
            $socioDao->registrarLog($contribuicaoLog->getSocio(), $mensagem);

            //Chamada do método de serviço de pagamento requisitado
            $caminhoCarne = $servicoPagamento->gerarCarne($contribuicaoLogCollection); 
            if (!$caminhoCarne || empty($caminhoCarne)) {
                $this->pdo->rollBack();
            } else {
                $this->pdo->commit();

                echo json_encode(['link' => WWW . 'html/apoio/' . $caminhoCarne]);
            }
        } catch (PDOException $e) {
            //implementar tratamento de erro
            echo json_encode(['erro' => $e->getMessage()]);
        }
    }

    /**
     * Cria um objeto do tipo ContribuicaoLog, chama o serviço de pix registrado no banco de dados
     * e insere a operação na tabela de contribuicao_log caso o serviço seja executado com sucesso.
     */
    public function criarQrCode()
    {
        $valor = filter_input(INPUT_POST, 'valor');
        $documento = filter_input(INPUT_POST, 'documento_socio');
        $formaPagamento = 'Pix';

        //Verificar se existe um sócio que possua de fato o documento
        try {
            $socioDao = new SocioDAO();
            $socio = $socioDao->buscarPorDocumento($documento);

            if (is_null($socio)) {
                //Colocar uma mensagem para informar que o sócio não existe
                exit('Sócio não encontrado');
            }

            $meioPagamentoDao = new MeioPagamentoDAO();
            $meioPagamento = $meioPagamentoDao->buscarPorNome($formaPagamento);

            if (is_null($meioPagamento)) {
                //Colocar uma mensagem para informar que o meio de pagamento não existe
                exit('Meio de pagamento não encontrado');
            }

            //Procura pelo serviço de pagamento através do id do gateway de pagamento
            $gatewayPagamentoDao = new GatewayPagamentoDAO();
            $gatewayPagamentoArray = $gatewayPagamentoDao->buscarPorId($meioPagamento->getGatewayId());
            $gatewayPagamento = new GatewayPagamento($gatewayPagamentoArray['plataforma'], $gatewayPagamentoArray['endPoint'], $gatewayPagamentoArray['token'], $gatewayPagamentoArray['status']);

            //Requisição dinâmica e instanciação da classe com base no nome do gateway de pagamento
            $requisicaoServico = '../service/' . $gatewayPagamento->getNome() . $formaPagamento . 'Service' . '.php';

            if (!file_exists($requisicaoServico)) {
                //implementar feedback
                exit('Arquivo não encontrado');
            }

            require_once $requisicaoServico;

            $classeService = $gatewayPagamento->getNome() . $formaPagamento . 'Service';

            if (!class_exists($classeService)) {
                //implementar feedback
                exit('Classe não encontrada');
            }

            $servicoPagamento = new $classeService;
        } catch (PDOException $e) {
            //implementar tratamento de erro
            echo 'Erro: ' . $e->getMessage();
            exit();
        }

        //Verificar qual fuso horário será utilizado posteriormente
        $dataGeracao = date('Y-m-d');
        $dataVencimento = date_modify(new DateTime(), '+1 day')->format('Y-m-d');

        $contribuicaoLog = new ContribuicaoLog();
        $contribuicaoLog
            ->setValor($valor)
            ->setCodigo($contribuicaoLog->gerarCodigo())
            ->setDataGeracao($dataGeracao)
            ->setDataVencimento($dataVencimento)
            ->setSocio($socio);

        try {
            /*Controle de transação para que o log só seja registrado
            caso o serviço de pagamento tenha sido executado*/
            $this->pdo->beginTransaction();
            $contribuicaoLogDao = new ContribuicaoLogDAO($this->pdo);
            $contribuicaoLogDao->criar($contribuicaoLog);

            //Registrar na tabela de socio_log
            $mensagem = "Pix gerado recentemente";
            $socioDao->registrarLog($contribuicaoLog->getSocio(), $mensagem);

            //Chamada do método de serviço de pagamento requisitado
            if (!$servicoPagamento->gerarQrCode($contribuicaoLog)) {
                $this->pdo->rollBack();
            } else {
                $this->pdo->commit();
            }
        } catch (PDOException $e) {
            //implementar tratamento de erro
            echo 'Erro: ' . $e->getMessage();
        }
    }

    /**
     * Extraí o id da requisição POST e muda o status de pagamento da contribuição correspondente.
     */
    public function pagarPorId()
    {
        $idContribuicaoLog = filter_input(INPUT_POST, 'id_contribuicao');

        if (!$idContribuicaoLog || $idContribuicaoLog < 1) {
            http_response_code(400);
            exit('O id fornecido não é válido'); //substituir posteriormente por redirecionamento com mensagem de feedback
        }

        try {
            $contribuicaoLogDao = new ContribuicaoLogDAO();
            $contribuicaoLogDao->pagarPorId($idContribuicaoLog);
        } catch (PDOException $e) {
            echo 'Erro: ' . $e->getMessage(); //substituir posteriormente por redirecionamento com mensagem de feedback
        }
    }
}
