<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="../assets/vendor/bootstrap/css/bootstrap.css" />
    <title>Upload</title>
</head>
<body>
    <div>
        
    <?php
    session_start();
    if (!isset($_SESSION["usuario"])){
        header("Location: ../index.php");
    }

    require_once "../dao/Conexao.php";

    $pdo = Conexao::connect();


    if ( isset( $_FILES[ 'img_file' ][ 'name' ] ) && $_FILES[ 'img_file' ][ 'error' ] == 0 ) {

        /*
        echo 'Você enviou o arquivo: <strong>' . $_FILES[ 'img_file' ][ 'name' ] . '</strong><br />';
        echo 'Este arquivo é do tipo: <strong > ' . $_FILES[ 'img_file' ][ 'type' ] . ' </strong ><br />';
        echo 'Temporáriamente foi salvo em: <strong>' . $_FILES[ 'img_file' ][ 'tmp_name' ] . '</strong><br />';
        echo 'Seu tamanho é: <strong>' . $_FILES[ 'img_file' ][ 'size' ] . '</strong> Bytes<br /><br />';
        */

        $arquivo_tmp = $_FILES[ 'img_file' ][ 'tmp_name' ];
        $nome = $_FILES[ 'img_file' ][ 'name' ];
        
        // Pega a extensão
        $extensao = pathinfo ( $nome, PATHINFO_EXTENSION );
        
        // Converte a extensão para minúsculo
        $extensao = strtolower ( $extensao );

        $nome = str_replace( '.'.$extensao, '', $nome);
        
        // Somente imagens, .jpg;.jpeg;.gif;.png
        if ( strstr ( '.jpg;.jpeg;.gif;.png;.bin', $extensao ) ) {

            $img = gzcompress(base64_encode(file_get_contents(addslashes($arquivo_tmp))));
            $nome = addslashes($nome);

            $cmd = $pdo->prepare("select nome from imagem where nome=:n");
            $cmd->bindValue(":n",$nome);
            $cmd->execute();
            $nome_existente = $cmd->fetchAll(PDO::FETCH_ASSOC);

            if (sizeof($nome_existente) == 0){
                $cmd = $pdo->prepare("insert into imagem values (default, :n, :i, :e)");
                $cmd->bindValue(":n",$nome);
                $cmd->bindValue(":i",$img);
                $cmd->bindValue(":e",$extensao);
                $cmd->execute();
            }

            if (isset($_POST["id_campo"])){
                $id_campo = $_POST["id_campo"];
                $cmd = $pdo->prepare("select id_imagem from imagem where nome=:n");
                $cmd->bindValue(":n",$nome);
                $cmd->execute();
                $id_imagem = $cmd->fetchAll(PDO::FETCH_ASSOC);
                $id_imagem = $id_imagem[0]["id_imagem"];


                $res = $pdo->query("select id_relacao from tabela_imagem_campo where id_campo='$id_campo'");
                $relacao = $res->fetchAll(PDO::FETCH_ASSOC);
                if (!!$relacao){
                    $pdo->query("update tabela_imagem_campo set id_imagem='$id_imagem' where id_campo='$id_campo';");
                }else{
                    $pdo->query("insert into tabela_imagem_campo values (default, '$id_campo', '$id_imagem');");
                }
            }

            if (sizeof($nome_existente) == 0){
                if (isset($_POST['source'])){
                    header ("Location: personalizacao_imagem.php?msg=success");
                }else{
                    header ("Location: personalizacao.php?msg=success");
                }
            }else{
                if (isset($_POST['source'])){
                    header ("Location: personalizacao_imagem.php?msg=error&err=Já existe uma imagem com esse nome registrada");
                }else{
                    header ("Location: personalizacao.php?msg=error&err=Já existe uma imagem com esse nome registrada");
                }
            }
        }
        else{
            header ("Location: personalizacao.php?msg=error&err=Tipo de arquivo inválido. Tipos válidos:.jpg .jpeg .gif .png");
        }
    }elseif (isset($_POST["txt"]) && isset($_POST["id"])){
        $txt = $_POST["txt"];
        $id = $_POST["id"];

        $res = $pdo->prepare("update selecao_paragrafo set paragrafo= :txt where id_selecao = :id;");
        $res->bindValue(":txt", $txt);
        $res->bindValue(":id", $id);
        $res->execute();
        header ("Location: personalizacao.php?msg=success");
    }elseif (isset($_POST["imagem_0"])){
        $cont = 0;
        $nome_car = $_POST["nome_car"];


        $res = $pdo->prepare("select id_campo from campo_imagem where nome_campo=:n");
        $res->bindValue(":n",$nome_car);
        $res->execute();
        $carIdQuery= $res->fetchAll(PDO::FETCH_ASSOC);
        $carId = $carIdQuery[0]["id_campo"];

        $res = $pdo->query("select id_imagem as id from tabela_imagem_campo where id_campo=$carId");
        $carrossel = $res->fetchAll(PDO::FETCH_ASSOC);
        foreach ($carrossel as $key => $value){
            $carrossel[$key]['action'] = 'drop';
        }
        
        while (isset($_POST["imagem_" . $cont])){
            $res = $pdo->prepare("select id_imagem from imagem where nome=:a");
            $res->bindValue(":a",$_POST["imagem_" . $cont]);
            $res->execute();
            $id_imagem = $res->fetchAll(PDO::FETCH_ASSOC);
            $file[$cont]['id'] = $id_imagem[0]["id_imagem"];
            $file[$cont]["action"] = 'add';
            $cont ++;
        }

        foreach ($file as $f_key => $f_val){
            foreach($carrossel as $c_key => $c_val){
                if ($f_val['id'] == $c_val['id'] && $c_val['action'] == 'drop'){
                    $carrossel[$c_key]['action'] = 'keep';
                    $file[$f_key]['action'] = 'discart';
                }
            }
        }

        $cont = 0;
        foreach ($carrossel as $key => $val){
            if ($val["action"] == 'keep'){
                unset($carrossel[$key]);
            }else{
                if ($cont == $key){
                    $cont++;
                } elseif ($cont != $key){
                    $carrossel[$cont] = $carrossel[$key];
                    unset($carrossel[$key]);
                    $cont ++;
                }
            }
        }

        $cont = 0;
        foreach ($file as $key => $val){
            if ($val["action"] == 'discart'){
                unset($file[$key]);
            }else{
                if ($cont == $key){
                    $cont++;
                } elseif ($cont != $key){
                    $file[$cont] = $file[$key];
                    unset($file[$key]);
                    $cont ++;
                }
            }
        }

        if (count($carrossel) >= count($file)){
            foreach ($file as $key => $val){
                $carrossel[$key]['id_novo'] = $file[$key]['id'];
                unset($file[$key]);
                $carrossel[$key]["action"] = 'change';
            }
        }else{
            foreach ($carrossel as $key => $val){
                $carrossel[$key]['id_novo'] = $file[$key]['id'];
                unset($file[$key]);
                $carrossel[$key]["action"] = 'change';
            }
            foreach ($file as $key => $val){
                $carrossel[] = $val;
            }
        }
        unset($file);

        if (count($carrossel) == 0){
            header("location: personalizacao.php?msg=success");
        }

        foreach ($carrossel as $key => $val){
            $action = $val['action'];
            $id = $val['id'];
            switch ($action){
                case 'add':
                    $pdo->query("insert into tabela_imagem_campo values (default, $carId, $id)");
                break;
                case 'change':
                    $id_novo = $val["id_novo"];
                    $pdo->query("update tabela_imagem_campo set id_imagem=$id_novo where id_campo=$carId and id_imagem=$id");
                break;
                case 'drop':
                    $pdo->query("delete from tabela_imagem_campo where id_campo=$carId and id_imagem=$id");
                break;
            }
        }
        header("location: personalizacao.php?msg=success");



    }elseif (isset($_POST["selecao"])){
        $selecao = $_POST["selecao"];
        $campo = $_POST["campo"];

        $res = $pdo->query("select id_relacao from tabela_imagem_campo");
        $relacao = $res->fetchAll(PDO::FETCH_ASSOC);

        if (!!$relacao){
            $pdo->query("update tabela_imagem_campo set id_imagem='$selecao' where id_campo='$campo';");
        }else{
            $pdo->query("insert into tabela_imagem_campo values (default, '$campo', '$selecao');");
        }
        header ("Location: personalizacao.php?msg=success");
    }
    ?>
    <div class="alert alert-danger">Houve um erro na comunicação com o servidor.</div>
    <p><a href="./personalizacao.php">Retornar</a></p>
    </div>
</body>
</html>