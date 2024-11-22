<?php
require_once 'ApiBoletoServiceInterface.php';
require_once '../model/ContribuicaoLog.php';
require_once '../dao/GatewayPagamentoDAO.php';
require_once '../helper/Util.php';
class PagarMeBoletoService implements ApiBoletoServiceInterface
{
    public function gerarBoleto(ContribuicaoLog $contribuicaoLog)
    {
        //gerar um número para o documento
        $numeroDocumento = Util::gerarNumeroDocumento(16);

        //Tipo do boleto
        $type = 'DM';

        //Validar regras

        //Buscar Url da API e token no BD
        try {
            $gatewayPagamentoDao = new GatewayPagamentoDAO();
            $gatewayPagamento = $gatewayPagamentoDao->buscarPorId(1); //Pegar valor do id dinamicamente
        } catch (PDOException $e) {
            //Implementar tratamento de erro
            echo 'Erro: ' . $e->getMessage();
        }

        //Buscar mensagem de agradecimento no BD
        $msg = 'Agradecimento';
        //Configurar cabeçalho da requisição
        $headers = [
            'Authorization: Basic ' . base64_encode($gatewayPagamento['token'] . ':'),
            'Content-Type: application/json;charset=utf-8',
        ];

        //Montar array de Boleto

        $cpfSemMascara = Util::limpaCpf($contribuicaoLog->getSocio()->getDocumento());//preg_replace('/\D/', '', $contribuicaoLog->getSocio()->getDocumento());

        $boleto = [
            "items" => [
                [
                    "amount" => $contribuicaoLog->getValor() * 100,
                    "description" => "Donation",
                    "quantity" => 1,
                    "code" => $contribuicaoLog->getCodigo()
                ]
            ],
            "customer" => [
                "name" => $contribuicaoLog->getSocio()->getNome(),
                "email" => $contribuicaoLog->getSocio()->getEmail(),
                "document_type" => "CPF",
                "document" => $cpfSemMascara,
                "type" => "Individual",
                "address" => [
                    "line_1" => $contribuicaoLog->getSocio()->getLogradouro() . ", n°" . $contribuicaoLog->getSocio()->getNumeroEndereco() . ", " . $contribuicaoLog->getSocio()->getBairro(),
                    "line_2" => $contribuicaoLog->getSocio()->getComplemento(),
                    "zip_code" => $contribuicaoLog->getSocio()->getCep(),
                    "city" => $contribuicaoLog->getSocio()->getCidade(),
                    "state" => $contribuicaoLog->getSocio()->getEstado(),
                    "country" => "BR"
                ],
            ],
            "payments" => [
                [
                    "payment_method" => "boleto",
                    "boleto" => [
                        "instructions" => $msg,
                        "document_number" => $numeroDocumento,
                        "due_at" => $contribuicaoLog->getDataVencimento(),
                        "type" => $type
                    ]
                ]
            ]
        ];

        // Transformar o boleto em JSON
        $boleto_json = json_encode($boleto);
        //echo $boleto_json;

        //Iniciar requisição

        // Iniciar a requisição cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $gatewayPagamento['endPoint']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $boleto_json);

        // Executar a requisição cURL
        $response = curl_exec($ch);

        // Lidar com a resposta da API (mesmo código de tratamento que você já possui)

        // Verifica por erros no cURL
        if (curl_errno($ch)) {
            echo 'Erro na requisição: ' . curl_error($ch);
            curl_close($ch);
            return false;
        }

        // Obtém o código de status HTTP
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Fecha a conexão cURL
        curl_close($ch);

        // Verifica o código de status HTTP
        if ($httpCode === 200 || $httpCode === 201) {
            $responseData = json_decode($response, true);
            $pdf_link = $responseData['charges'][0]['last_transaction']['pdf'];

            //armazena copía para segunda via
            $this->guardarSegundaVia($pdf_link, $contribuicaoLog);

            //envia resposta para o front-end
            echo json_encode(['link' => $pdf_link]);
        } else {
            echo json_encode(['Erro' => 'A API retornou o código de status HTTP ' . $httpCode]);
            return false;
            // Verifica se há mensagens de erro na resposta JSON
            $responseData = json_decode($response, true);
            if (isset($responseData['errors'])) {
                //echo 'Detalhes do erro:';
                foreach ($responseData['errors'] as $error) {
                    //echo '<br> ' . htmlspecialchars($error['message']);
                }
            }
        }

        return true;
    }
    public function guardarSegundaVia($pdf_link, ContribuicaoLog $contribuicaoLog)
    {
        // Diretório onde os arquivos serão armazenados
        $saveDir = '../pdfs/';

        // Verifica se o diretório existe, se não, cria o diretório
        if (!is_dir($saveDir)) {
            mkdir($saveDir, 0755, true);
        }

        $cpfSemMascara = Util::limpaCpf($contribuicaoLog->getSocio()->getDocumento());//preg_replace('/\D/', '', $contribuicaoLog->getSocio()->getDocumento());

        //$numeroAleatorio = gerarCodigoAleatorio();
        $ultimaDataVencimento = $contribuicaoLog->getDataVencimento();
        $ultimaDataVencimento = str_replace('-', '', $ultimaDataVencimento);
        $nomeArquivo = $saveDir . $contribuicaoLog->getCodigo() . '_' . $cpfSemMascara . '_' . $ultimaDataVencimento . '_' . $contribuicaoLog->getValor() . '.pdf';

        // Inicia uma sessão cURL
        $ch = curl_init($pdf_link);

        // Configurações da sessão cURL
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);

        // Executa a sessão cURL e obtém a resposta com cabeçalhos
        $response = curl_exec($ch);

        // Verifica se ocorreu algum erro durante a execução do cURL
        if (curl_errno($ch)) {
            echo json_encode('Erro ao baixar o arquivo.'); //. curl_error($ch) . PHP_EOL;
            exit();
        } else {
            // Verifica o código de resposta HTTP
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($httpCode == 200) {
                // Separa os cabeçalhos do corpo da resposta
                $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                $headers = substr($response, 0, $headerSize);
                $fileContent = substr($response, $headerSize);

                // Verifica o tipo de conteúdo
                if (strpos($headers, 'Content-Type: application/pdf') !== false) {
                    // Salva o conteúdo do arquivo no diretório especificado
                    file_put_contents($nomeArquivo, $fileContent);
                    //$arquivos []= $savePath;
                } else {
                    //echo "Erro: O conteúdo da URL não é um PDF." . PHP_EOL;
                }
            } else {
                echo json_encode("Erro ao baixar o arquivo: HTTP $httpCode");
                exit();
            }
        }

        // Fecha a sessão cURL
        curl_close($ch);
    }
}
