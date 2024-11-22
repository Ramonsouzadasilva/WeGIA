<?php
require_once 'ApiPixServiceInterface.php';
require_once '../helper/Util.php';
class PagarMePixService implements ApiPixServiceInterface
{
    public function gerarQrCode(ContribuicaoLog $contribuicaoLog)
    {
        //Validar regras

        //Buscar Url da API e token no BD
        try {
            $gatewayPagamentoDao = new GatewayPagamentoDAO();
            $gatewayPagamento = $gatewayPagamentoDao->buscarPorId(1); //Pegar valor do id dinamicamente
        } catch (PDOException $e) {
            //Implementar tratamento de erro
            echo 'Erro: ' . $e->getMessage();
            return false;
        }

        // Configuração dos dados para a API
        $description = 'Doação';
        $expires_in = 3600;

        $headers = [
            'Authorization: Basic ' . base64_encode($gatewayPagamento['token'] . ':'),
            `uri: {$gatewayPagamento['endPoint']}`,
            'Content-Type: application/json;charset=UTF-8'
        ];

        //Configura os dados a serem enviados

        //gerar um número aleatório para o parâmetro code
        $code = $contribuicaoLog->getCodigo();
        $cpfSemMascara = Util::limpaCpf($contribuicaoLog->getSocio()->getDocumento());
        $telefone = Util::limpaTelefone($contribuicaoLog->getSocio()->getTelefone());

        $data = [
            'items' => [
                [
                    'amount' => intval($contribuicaoLog->getValor() * 100),
                    'description' => $description,
                    'quantity' => 1,
                    "code" => $code
                ]
            ],
            'customer' => [
                'name' => $contribuicaoLog->getSocio()->getNome(),
                'email' => $contribuicaoLog->getSocio()->getEmail(),
                'type' => 'individual',
                'document' => $cpfSemMascara,
                'phones' => [
                    'mobile_phone' => [
                        'country_code' => '55',
                        'area_code' => substr($telefone, 0, 2),
                        'number' => substr($telefone, 2)
                    ]
                ],
            ],
            'payments' => [
                [
                    'payment_method' => 'pix',
                    'pix' => [
                        'expires_in' => $expires_in,
                        'additional_information' => [
                            [
                                'name' => 'Teste',
                                'value' => '1'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        //print_r($data);
        // Converte os dados para JSON
        $jsonData = json_encode($data);

        // Inicia a requisição cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $gatewayPagamento['endPoint']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

        $response = curl_exec($ch);

        // Verifica por erros no cURL
        if (curl_errno($ch)) {
            echo json_encode(['erro' => curl_error($ch)]);
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
        } else {
            echo json_encode(['erro' => 'A API retornou o código de status HTTP ' . $httpCode]);
            return false;
            // Verifica se há mensagens de erro na resposta JSON
            $responseData = json_decode($response, true);
            if (isset($responseData['errors'])) {
                //echo 'Detalhes do erro:';
                foreach ($responseData['errors'] as $error) {
                    //echo '<br>- ' . htmlspecialchars($error['message']);
                }
            }
        }

        //Verifica se o status é 'pending'
        if ($responseData['status'] === 'pending') {
            // Gera um qr_code
            $qr_code_url = $responseData['charges'][0]['last_transaction']['qr_code_url'];
            $qr_code = file_get_contents($qr_code_url);
            //envia o link da url
            echo json_encode(['qrcode' => base64_encode($qr_code)]); //enviar posteriormente a cópia do QR para área de transferência junto
        } else {
            echo json_encode(["erro" => "Houve um erro ao gerar o QR CODE de pagamento. Verifique se as informações fornecidas são válidas."]);
            return false;
        }

        return true;
    }
}
