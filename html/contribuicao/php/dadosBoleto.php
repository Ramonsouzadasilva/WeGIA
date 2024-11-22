<?php

    $dados = $_GET['dados'];
    $idSistema = $_GET['idSistema'];
    $idRegras = $_GET['idRegras'];
   
        if($dados == 0)
        {
            insereDados($idSistema);
        }else{
            atualizaDados($idSistema, $idRegras);
        }

    function insereDados($idSistema)
    {
        require_once('conexao.php');
        $banco = new Conexao;

        $MinValUnic = $_POST['minval'];
            if($MinValUnic == '')
            {
                $MinValUnic = 0;
            }
        $MensalDiasV =$_POST['mensaldiasv'];
            if($MensalDiasV == '')
            {
                $MensalDiasV = 0;
            }
        $juros = $_POST['juros'];
            if($juros == '')
            {
                $juros = 0;
            }
        $multa =$_POST['multa'];
            if($multa == '')
            {
                $multa = 0;
            }
        $MaiValParc = $_POST['maivalparc'];
            if($MaiValParc == ''){
                $MaiValParc = 0;
            }
        $MinValParc = $_POST['minvalparc'];
            if($MinValParc == ''){
                $MinValParc = 0;
            }
        $agradecimento = $_POST['agradecimento'];
            if($agradecimento == ''){
                $agradecimento = 0;
            }
        $UnicDiasV =$_POST['unicdiasv'];
            if($UnicDiasV == ''){
                $UnicDiasV = 0;
            }
        $opVenc1 = $_POST['op01'];
        $opVenc2 = $_POST['op02'];
        $opVenc3 = $_POST['op03'];
        $opVenc4 = $_POST['op04'];
        $opVenc5 = $_POST['op05'];
        $opVenc6 = $_POST['op06'];
            if($opVenc1 == ''){
                 $opVenc1 = 0;
            }if($opVenc2 == ''){
                $opVenc2 = 0;
            }if($opVenc3 == ''){ 
                $opVenc3 = 0;}
            if($opVenc4 == ''){
                $opVenc4 = 0;}
            if($opVenc5 == ''){
                $opVenc5 = 0;}
            if($opVenc6 == ''){
                $opVenc6 = 0;}
        $API = $_POST['api'];
        $token = $_POST['token_api'];
       
                   
                    $banco->query("CALL insregras ('$MinValUnic', '$MensalDiasV','$juros','$multa','$MaiValParc','$MinValParc','$agradecimento','$UnicDiasV', '$opVenc1', '$opVenc2', '$opVenc3', '$opVenc4', '$opVenc5', '$opVenc6')");
                        
                    $banco->querydados("SELECT id FROM doacao_boleto_regras ORDER BY id DESC LIMIT 1");
                    $dados = $banco->result();
                    $cod = $dados['id'];
                        
                        
                    $banco->query("INSERT INTO doacao_boleto_info (api, token_api, id_sistema, id_regras) VALUES ('$API', '$token', '$idSistema', '$cod')");

    }

    function atualizaDados($idSistema, $idRegras)
    {
        require_once('conexao.php');
        $banco = new Conexao;

        $MinValUnic = $_POST['minval'];
            if($MinValUnic == '')
            {
                $MinValUnic = 0;
            }
        $MensalDiasV =$_POST['mensaldiasv'];
            if($MensalDiasV == '')
            {
                $MensalDiasV = 0;
            }
        $juros = $_POST['juros'];
            if($juros == '')
            {
                $juros = 0;
            }
        $multa =$_POST['multa'];
            if($multa == '')
            {
                $multa = 0;
            }
        $MaiValParc = $_POST['maivalparc'];
            if($MaiValParc == ''){
                $MaiValParc = 0;
            }
        $MinValParc = $_POST['minvalparc'];
            if($MinValParc == ''){
                $MinValParc = 0;
            }
        $agradecimento = $_POST['agradecimento'];
            if($agradecimento == ''){
                $agradecimento = 0;
            }
        $UnicDiasV =$_POST['unicdiasv'];
            if($UnicDiasV == ''){
                $UnicDiasV = 0;
            }
        $opVenc1 = $_POST['op01'];
        $opVenc2 = $_POST['op02'];
        $opVenc3 = $_POST['op03'];
        $opVenc4 = $_POST['op04'];
        $opVenc5 = $_POST['op05'];
        $opVenc6 = $_POST['op06'];
            if($opVenc1 == ''){
                 $opVenc1 = 0;
            }if($opVenc2 == ''){
                $opVenc2 = 0;
            }if($opVenc3 == ''){ 
                $opVenc3 = 0;}
            if($opVenc4 == ''){
                $opVenc4 = 0;}
            if($opVenc5 == ''){
                $opVenc5 = 0;}
            if($opVenc6 == ''){
                $opVenc6 = 0;}

        $API = $_POST['api'];
        $token = $_POST['token_api'];

        $banco->query("UPDATE  doacao_boleto_regras as regras JOIN doacao_boleto_info as info ON (info.id_regras = regras.id) SET min_boleto_uni = '$MinValUnic', max_dias_venc = '$MensalDiasV', juros = '$juros', multa = '$multa', max_parcela = '$MaiValParc', min_parcela = '$MinValParc', agradecimento = '$agradecimento', dias_boleto_a_vista = '$UnicDiasV', dias_venc_carne_op1 = '$opVenc1', dias_venc_carne_op2 = '$opVenc2', dias_venc_carne_op3 = '$opVenc3', dias_venc_carne_op4 = '$opVenc4', dias_venc_carne_op5 = '$opVenc5', dias_venc_carne_op6 = '$opVenc6', api = '$API', token_api = '$token' WHERE id_regras = '$idRegras' AND id_sistema = '$idSistema'");

    }
    
    header("Location: configuracao_doacao.php");

    

?>