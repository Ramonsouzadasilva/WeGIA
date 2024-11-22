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

//include_once "/dao/Conexao.php";
require_once ROOT.'/classes/SinaisVitais.php';
require_once ROOT.'/dao/SinaisVitaisDAO.php';
include_once ROOT.'/classes/Cache.php';
include_once ROOT."/dao/Conexao.php";

class SinaisVitaisControle 
{  
    public function verificar(){
        $id_fichamedica = trim($_REQUEST['id_fichamedica']);
        $id_funcionario = trim($_REQUEST['id_funcionario']);
        $data_afericao = trim($_REQUEST['data_afericao']);
        $saturacao = trim($_REQUEST['saturacao']);
        $pres_art = trim($_REQUEST['pres_art']);
        $freq_card = trim($_REQUEST['freq_card']);
        $freq_resp = trim($_REQUEST['freq_resp']);
        $temperatura = trim($_REQUEST['temperatura']);
        $hgt = trim($_REQUEST['hgt']);

        if((!isset($id_fichamedica)) || (empty($id_fichamedica))){
            $id_fichamedica = "";
        }else $id_fichamedica = intval($id_fichamedica);

        if((!isset($id_funcionario)) || (empty($id_funcionario))){
            $id_funcionario = "";
        }else $id_funcionario = intval($id_funcionario);

        if((!isset($data_afericao)) || (empty($data_afericao))){
            $data_afericao = "";
        }else{
            $timestamp = strtotime($data_afericao);
            $data_afericao = date('Y-m-d\TH:i', $timestamp);
        } 

        if((!isset($saturacao)) || (empty($saturacao))){
            $saturacao= "";
        } else{
            $saturacao = str_replace(',','.', $saturacao);
            $saturacao = floatval($saturacao);
        } 

        if((!isset($pres_art)) || (empty($pres_art))){
            $pres_art= "";
        }

        if((!isset($freq_card)) || (empty($freq_card))){
            $freq_card= "";
        }else $freq_card = intval($freq_card);

        if((!isset($freq_resp)) || (empty($freq_resp))){
            $freq_resp= "";
        }else $freq_resp = intval($freq_resp);

        if((!isset($temperatura)) || (empty($temperatura))){
            $temperatura= "";
        }else{
            $temperatura = str_replace(',','.', $temperatura);
            $temperatura = floatval($temperatura);
        } 

        if((!isset($hgt)) || (empty($hgt))){
            $hgt= "";
        }else{
            $hgt = str_replace(',','.', $hgt);
            $hgt = floatval($hgt);
        } 
        
        $sinaisvitais = new SinaisVitais($id_fichamedica, $id_funcionario, $data_afericao, $saturacao, $pres_art, $freq_card, $freq_resp, $temperatura, $hgt);

        $sinaisvitais->setIdFuncionario($id_funcionario);
        $sinaisvitais->setIdFichamedica($id_fichamedica);
        $sinaisvitais->setData($data_afericao);
        $sinaisvitais->setSaturacao($saturacao);
        $sinaisvitais->setPressaoArterial($pres_art);
        $sinaisvitais->setFrequenciaCardiaca($freq_card);
        $sinaisvitais->setFrequenciaRespiratoria($freq_resp);
        $sinaisvitais->setTemperatura($temperatura);
        $sinaisvitais->setHgt($hgt);

        return $sinaisvitais;
    }

    public function incluir(){
        $id_fichamedica = trim($_REQUEST['id_fichamedica']);

        if(!$id_fichamedica || !is_numeric($id_fichamedica)){
            http_response_code(400);
            exit('Erro, o id da ficha médica não pode ser nulo ou diferente de um número');
        }

        $sinaisvitais = $this->verificar();
        $sinVitDAO = new SinaisVitaisDAO();
        
        try{
            $intDAO=$sinVitDAO->incluir($sinaisvitais);
            $_SESSION['msg']="Ficha médica cadastrada com sucesso!";
            $_SESSION['proxima']="Cadastrar outra ficha.";
            $_SESSION['link']="../html/saude/cadastro_ficha_medica.php";
            header("Location: ../html/saude/historico_paciente.php?id_fichamedica=".$id_fichamedica);
        } catch (PDOException $e){
            $msg= htmlspecialchars("Não foi possível registrar o paciente <form> <input type='button' value='Voltar' onClick='history.go(-1)'> </form>"."<br>".$e->getMessage());
            echo $msg;
        }
    }
}