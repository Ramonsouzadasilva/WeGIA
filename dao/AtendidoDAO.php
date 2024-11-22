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
require_once ROOT."/dao/Conexao.php";
require_once ROOT."/classes/Atendido.php";
require_once ROOT."/Functions/funcoes.php";

class AtendidoDAO
{
    public function formatoDataDMY($data)
    {
        $data_arr = explode("-", $data);
        
        $datad = $data_arr[2] . '/' . $data_arr[1] . '/' . $data_arr[0];
        
        return $datad;
    }

    public function selecionarCadastro($cpf){
        $pdo = Conexao::connect();
        $valor = 0;
        $consultaFunc = $pdo->query("select pessoa_id_pessoa from atendido where pessoa_id_pessoa = (SELECT id_pessoa from pessoa where cpf = '$cpf')")->fetchAll(PDO::FETCH_ASSOC);
        if($consultaFunc == null){
            $consultaCPF = $pdo->query("select cpf,id_pessoa from pessoa;")->fetchAll(PDO::FETCH_ASSOC);
            foreach ( $consultaCPF as $key => $value ){
                if($cpf == $value['cpf']){
                    $valor++;
                }
            }
            if($valor == 0){
                header("Location: ../html/atendido/Cadastro_Atendido.php?cpf=$cpf");
            }
            else
            {
                header("Location: ../html/atendido/cadastro_atendido_pessoa_existente.php?cpf=$cpf");
                // header("Location: ../controle/control.php?metodo=listarPessoaExistente($cpf)&nomeClasse=FuncionarioControle&nextPage=../html/funcionario/cadastro_funcionario_pessoa_existente.php?cpf=$cpf");
            
            }
        }
        else{
            header("Location: ../html/atendido/pre_cadastro_atendido.php?msg_e=Erro, Atendido já cadastrado no sistema.");
        }
        
    }

	public function incluir($atendido, $cpf)
    {               
        try {
            $sql = "call cadatendido(:strNome,:strSobrenome,:strCpf,:strSexo,:strTelefone,:dateNascimento, :intStatus, :intTipo)";
            //$sql = str_replace("'", "\'", $sql); 
            $pdo = Conexao::connect();
            $stmt = $pdo->prepare($sql);

            
            $nome=$atendido->getNome();
            $sobrenome=$atendido->getSobrenome();
            $cpf=$atendido->getCpf();
            $sexo=$atendido->getSexo();
            $telefone=$atendido->getTelefone();
            $dataNascimento=$atendido->getDataNascimento();
            $intTipo=$atendido->getIntTipo();
            $intStatus=$atendido->getIntStatus();

            $stmt->bindParam(':strNome',$nome);
            $stmt->bindParam(':strSobrenome',$sobrenome);
            $stmt->bindParam(':strCpf',$cpf);
            $stmt->bindParam(':strSexo',$sexo);
            $stmt->bindParam(':strTelefone',$telefone);
            $stmt->bindParam(':dateNascimento',$dataNascimento);
            $stmt->bindParam(':intStatus',$intStatus);
            $stmt->bindParam(':intTipo',$intTipo);
            $stmt->execute();
            
        } catch (PDOException $e) {
            echo 'Error: <b>  na tabela pessoas = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        }
    }
     // incluirExistente

     public function incluirExistente($atendido,$idPessoa, $sobrenome)
     {
         try {
             $sql = "UPDATE pessoa set sobrenome=:sobrenome, sexo=:sexo WHERE id_pessoa=:id_pessoa;";
              
             $sql2 = "INSERT INTO atendido(pessoa_id_pessoa,atendido_tipo_idatendido_tipo,atendido_status_idatendido_status)
             values(:id_pessoa,:intTipo,:intStatus)";
             
             // $sql = str_replace("'", "\'", $sql);
             $pdo = Conexao::connect();
             $stmt = $pdo->prepare($sql);
             $stmt2 = $pdo->prepare($sql2);
 
             $nome=$atendido->getNome();
             $sobrenome=$atendido->getSobrenome();
             $cpf=$atendido->getCpf();
             $sexo=$atendido->getSexo();
             $telefone=$atendido->getTelefone();
             $nascimento=$atendido->getDataNascimento();
             $tipo=$atendido->getIntTipo();
             $status=$atendido->getIntStatus();
 
             $stmt->bindParam(':id_pessoa',$idPessoa);
             $stmt->bindParam(':sobrenome',$sobrenome);
             $stmt->bindParam(':sexo',$sexo);
 
             $stmt2->bindParam(':id_pessoa',$idPessoa);
             $stmt2->bindParam(':intStatus',$status);
             $stmt2->bindParam(':intTipo',$tipo);
            
 
             $stmt->execute();
             $stmt2->execute();
 
         } catch (PDOException $e) {
             echo 'Error: <b>  na tabela pessoas1 = ' . $sql . '</b> <br /><br />' . $e->getMessage();
         }
     }

   

    //reformular o método, não deve ser possível deletar um atendido da base de dados.
    public function excluir($id)
    {
        /*
        try {
            
            //echo $id;
            //$sql = 'call excluiratendido(:idatendido)';
            //$sql = str_replace("'", "\'", $sql);

            $sql1 = "DELETE FROM atendido WHERE idatendido=$id";
            $sql2 = "DELETE FROM atendido_ocorrencia WHERE atendido_idatendido=$id";
            //$sql3 = "DELETE p FROM pessoa as p join atendido as a on p.id_pessoa=a.pessoa_id_pessoa";
            $sqlAux1 = "SELECT pessoa_id_pessoa FROM atendido WHERE idatendido=$id";
            $sql3 = "DELETE FROM pessoa WHERE id_pessoa=:idPessoa";
            $sql4 = "DELETE * FROM atendido_documentacao WHERE atendido_idatendido=$id";

            $pdo = Conexao::connect();

            $pdo->beginTransaction();

            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmtAux1 = $pdo->prepare($sqlAux1);
            $stmtAux1->execute();
            $pessoaID = $stmtAux1->fetch(PDO::FETCH_ASSOC);
            $pessoaID = $pessoaID['pessoa_id_pessoa'];
            //echo $pessoaID;
            //print_r($pessoaID);

            $stmt3 = $pdo->prepare($sql3);
            $stmt3->bindParam(':idPessoa', $pessoaID);

            $stmt2 = $pdo->prepare($sql2);
            $stmt2->execute();
            
            $stmt = $pdo->prepare($sql1);

            //$stmt->bindParam(':idatendido', $id);
            
            if($stmt->execute()){
                $stmt3->execute();
                $pdo->commit();
            }else{
                $pdo->rollBack();
            }

            $pdo = null;
        } catch (PDOException $e) {
            echo 'Error: <b>  na tabela pessoas = ' . $sql1 . '</b> <br /><br />' . $e->getMessage();
        }*/
    }

    public function alterarImagem($idatendido, $imagem)
    {
        $imagem = base64_encode($imagem);
        try {
            $pdo = Conexao::connect();
            $id_pessoa = (($pdo->query("SELECT pessoa_id_pessoa FROM atendido WHERE idatendido=$idatendido"))->fetch(PDO::FETCH_ASSOC))["pessoa_id_pessoa"];
            
            $sql = "UPDATE pessoa SET imagem = :imagem WHERE id_pessoa = :pessoa_id_pessoa;";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':pessoa_id_pessoa', $id_pessoa);
            $stmt->bindValue(':imagem',$imagem);
            $stmt->execute();
        } catch (PDOException $e) {
            echo 'Error: <b>  na tabela pessoa = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        }
    }

    // Editar
    public function alterar($atendido)
    {
        try {
            $sql = 'update pessoa as p inner join atendido as a on p.id_pessoa=a.pessoa_id_pessoa set p.senha=:senha,p.nome=:nome, p.sobrenome=:sobrenome,p.cpf=:cpf,p.sexo=:sexo,p.telefone=:telefone,data_nascimento=:data_nascimento,p.imagem=:imagem,p.cep=:cep,p.estado=:estado,p.cidade=:cidade,p.bairro=:bairro,p.logradouro=:logradouro,p.numero_endereco=:numero_endereco,p.complemento=:complemento,p.ibge=:ibge,p.registro_geral=:registro_geral,p.orgao_emissor=:orgao_emissor,p.data_expedicao=:data_expedicao,p.nome_pai=:nome_pai,p.nome_mae=:nome_mae,p.intTipo_sanguineo=:intTipo_sanguineo,i.nome_contato_urgente=:nome_contato_urgente,i.strTelefone_contato_urgente_1=:strTelefone_contato_urgente_1,i.strTelefone_contato_urgente_2=:strTelefone_contato_urgente_2,i.strTelefone_contato_urgente_3=:strTelefone_contato_urgente_3,i.observacao=:observacao,i.certidao_nascimento=:certidao,i.curatela=:curatela,i.inss=:inss,i.loas=:loas,i.bpc=:bpc,i.funrural=:funrural,i.saf=:saf,i.sus=:sus,i.certidao_casamento=:certidao_casamento,i.ctps=:ctps,i.titulo=:titulo where a.pessoa_id_pessoa=:id_pessoa';
            
            $sql = str_replace("'", "\'", $sql);
            $pdo = Conexao::connect();
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $nome=$atendido->getNome();
            $sobrenome=$atendido->getSobrenome();
            $cpf=$atendido->getCpf();
            $sexo=$atendido->getSexo();
            $telefone=$atendido->getTelefone();
            $nascimento=$atendido->getDataNascimento();

            $stmt = $pdo->prepare($sql);

            $stmt->bindParam('nome',$nome);
            $stmt->bindParam('sobrenome',$sobrenome);
            $stmt->bindParam(':cpf',$cpf);
            $stmt->bindParam(':sexo',$sexo);
            $stmt->bindParam(':telefone',$telefone);
            $stmt->bindParam(':data_nascimento',$nascimento);
            $stmt->execute();
            
        } catch (PDOException $e) {
            echo 'Error: <b>  na tabela pessoas = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        }
    }
    public function listarTodos($status){

        try{
            if(!isset($status))
                $status_selecionado = 1;
            
            else
                $status_selecionado = $status;
            $atendidos=array();
            $pdo = Conexao::connect();

            $consulta = $pdo->prepare("SELECT p.nome,p.sobrenome,p.cpf,a.idatendido FROM pessoa p INNER JOIN atendido a 
            ON p.id_pessoa = a.pessoa_id_pessoa WHERE a.atendido_status_idatendido_status = ?");
             $consulta->bindParam(1,$status_selecionado);
             $consulta->execute();
            //$produtos = Array();
            $x=0;
            while($linha = $consulta->fetch(PDO::FETCH_ASSOC)){
                if ($linha['cpf']==="Não informado") {
                    $atendidos[$x]=array('cpf'=>$linha['cpf'],'nome'=>$linha['nome'],'sobrenome'=>$linha['sobrenome'],'id'=>$linha['id_pessoa']);
                }
                else{
                $atendidos[$x]=array('cpf'=>$linha['cpf'],'nome'=>$linha['nome'],'sobrenome'=>$linha['sobrenome'],'id'=>$linha['idatendido']);
                }
                $x++;
            }
           // $pdo->commit();
            //$pdo->close();
            } catch (PDOException $e){
                echo 'Error:' . $e->getMessage();
            }
            return json_encode($atendidos);
            var_dump($status_selecionado);
        }


    public function listarTodos2(){

        try{
            $pessoas=array();
            $pdo = Conexao::connect();
            $consulta = $pdo->query("SELECT p.nome,p.sobrenome,p.cpf,i.id_pessoa FROM pessoa p INNER JOIN pessoa i ON p.id_pessoa = i.id_pessoa");
            $produtos = Array();
            $x=0;
            while($linha = $consulta->fetch(PDO::FETCH_ASSOC)){
                if ($linha['cpf']==="Não informado") {
                    $pessoas[$x]=array('cpf'=>$linha['cpf'],'nome'=>$linha['nome'],'sobrenome'=>$linha['sobrenome'],'id'=>$linha['id_pessoa']);
                }
                else{
                $pessoas[$x]=array('cpf'=>$linha['cpf'],'nome'=>$linha['nome'],'sobrenome'=>$linha['sobrenome'],'id'=>$linha['id_pessoa']);
                }
                $x++;
            }
            // $pdo = Conexao::flush();
            // $pdo = Conexao::close();
            } catch (PDOException $e){
                echo 'Error:' . $e->getMessage();
            }
            return $pessoas;
        }

    public function listar($id){
        try{
            echo $id;
            $pdo = Conexao::connect();
            /*$sql = "SELECT p.imagem,p.nome,p.sobrenome,p.cpf, p.senha, p.sexo, p.telefone,p.data_nascimento, p.cep,p.estado,p.cidade,p.bairro,p.logradouro,p.numero_endereco,p.complemento,p.ibge,p.registro_geral,p.orgao_emissor,p.data_expedicao,p.nome_pai,p.nome_mae,p.tipo_sanguineo, d.id_documento FROM pessoa p LEFT JOIN atendido a ON p.id_pessoa = a.pessoa_id_pessoa left join documento d on p.id_pessoa=d.id_pessoa WHERE a.idatendido=:id";*/

            $sql = "SELECT p.imagem,p.nome,p.sobrenome,p.cpf, p.senha, p.sexo, p.telefone,p.data_nascimento, p.cep,p.estado,p.cidade,p.bairro,p.logradouro,p.numero_endereco,p.complemento,p.ibge,p.registro_geral,p.orgao_emissor,p.data_expedicao,p.nome_pai,p.nome_mae,p.tipo_sanguineo FROM pessoa p LEFT JOIN atendido a ON p.id_pessoa = a.pessoa_id_pessoa WHERE a.idatendido=:id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id',$id);

            $stmt->execute();
            $pessoa=array();
            while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if ($linha['cpf']==="Não informado") {
                $pessoa[]=array('imagem'=>$linha['imagem'],'nome'=>$linha['nome'],'sobrenome'=>$linha['sobrenome'],'cpf'=>$linha['cpf'], 'senha'=>$linha['senha'], 'sexo'=>$linha['sexo'], 'telefone'=>$linha['telefone'],'data_nascimento'=>$linha['data_nascimento'], 'cep'=>$linha['cep'], 'estado'=>$linha['estado'],'cidade'=>$linha['cidade'],'bairro'=>$linha['bairro'],'logradouro'=>$linha['logradouro'],'numero_endereco'=>$linha['numero_endereco'],'complemento'=>$linha['complemento'],'ibge'=>$linha['ibge'],'registro_geral'=>$linha['registro_geral'],'orgao_emissor'=>$linha['orgao_emissor'],'data_expedicao'=>$linha['data_expedicao'],'nome_pai'=>$linha['nome_pai'],'nome_mae'=>$linha['nome_mae'],'tipo_sanguineo'=>$linha['tipo_sanguineo'],'idatendido'=>$linha['pessoa_id_pessoa'],'imgdoc'=>$linha['imgdoc'],'descricao'=>$linha['descricao'],'id_documento'=>$linha['id_documento']);
                
            }
            else{
                $pessoa[]=array('imagem'=>$linha['imagem'],'nome'=>$linha['nome'],'sobrenome'=>$linha['sobrenome'],'cpf'=>$linha['cpf'], 'senha'=>$linha['senha'], 'sexo'=>$linha['sexo'], 'telefone'=>$linha['telefone'],'data_nascimento'=>$linha['data_nascimento'], 'cep'=>$linha['cep'],'estado'=>$linha['estado'],'cidade'=>$linha['cidade'],'bairro'=>$linha['bairro'],'logradouro'=>$linha['logradouro'],'numero_endereco'=>$linha['numero_endereco'],'complemento'=>$linha['complemento'],'ibge'=>$linha['ibge'],'registro_geral'=>$linha['registro_geral'],'orgao_emissor'=>$linha['orgao_emissor'],'data_expedicao'=>$linha['data_expedicao'],'nome_pai'=>$linha['nome_pai'],'nome_mae'=>$linha['nome_mae'],'tipo_sanguineo'=>$linha['tipo_sanguineo'],'idatendido'=>$linha['pessoa_id_pessoa'],'imgdoc'=>$linha['imgdoc'],'descricao'=>$linha['descricao'],'id_documento'=>$linha['id_documento']);
            }
        }
        }catch (PDOException $e){
            echo 'Error: ' .  $e->getMessage();
        }
        return json_encode($pessoa);
    }

    public function listarcpf()
    {
        try
        {
            $cpfs = array();
            $pdo = Conexao::connect();
            $consulta = $pdo->query("SELECT cpf from pessoa p INNER JOIN atendido a ON(p.id_pessoa=a.pessoa_id_pessoa)");
            $x=0;
            while($linha = $consulta->fetch(PDO::FETCH_ASSOC)){
                $cpfs[$x] = array('cpf'=>$linha['cpf']);
                $x++;
            }
        }
        catch(PDOException $e)
        {
            echo 'Error:' . $e->getMessage();
        }
        /////////////////$pdo = Conexao::flush();
        //$pdo = Conexao::close();
        return json_encode($cpfs);
    }
    public function alterarInfPessoal($atendido)
    {
        try {
            $sql = 'update pessoa as p inner join atendido as a on p.id_pessoa=a.pessoa_id_pessoa set nome=:nome,sobrenome=:sobrenome,sexo=:sexo,telefone=:telefone,data_nascimento=:data_nascimento,nome_pai=:nome_pai,nome_mae=:nome_mae,tipo_sanguineo=:tipo_sanguineo where idatendido=:idatendido';

            $sql = str_replace("'", "\'", $sql);
            $pdo = Conexao::connect();
            $stmt = $pdo->prepare($sql);

            $stmt = $pdo->prepare($sql);
            $nome=$atendido->getNome();
            $sobrenome=$atendido->getSobrenome();
            $id=$atendido->getIdatendido();
            $sexo=$atendido->getSexo();
            $telefone=$atendido->getTelefone();
            $nascimento=$atendido->getDataNascimento();
            $nomePai=$atendido->getNomePai();        
            $nomeMae=$atendido->getNomeMae();
            $sangue=$atendido->getTipoSanguineo();

            $stmt->bindParam(':nome',$nome);
            $stmt->bindParam(':sobrenome',$sobrenome);
            $stmt->bindParam(':idatendido',$id);
            $stmt->bindParam(':sexo',$sexo);
            $stmt->bindParam(':telefone',$telefone);
            $stmt->bindParam(':data_nascimento',$nascimento);
            $stmt->bindParam(':nome_pai',$nomePai);        
            $stmt->bindParam(':nome_mae',$nomeMae);
            $stmt->bindParam(':tipo_sanguineo',$sangue);
            $stmt->execute();

        } catch (PDOException $e) {
            echo 'Error: <b>  na tabela pessoas = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        }
    }

    public function alterarDocumentacao($atendido)
    {
        try {

            $sql = 'update pessoa as p inner join atendido as a on p.id_pessoa=a.pessoa_id_pessoa set registro_geral=:registro_geral,orgao_emissor=:orgao_emissor,data_expedicao=:data_expedicao,cpf=:cpf where idatendido=:idatendido';
            
            $sql = str_replace("'", "\'", $sql);

            $pdo = Conexao::connect();
            $stmt = $pdo->prepare($sql);

            $cpf=$atendido->getCpf();
            $idatendido=$atendido->getIdatendido();
            $registro_geral=$atendido->getRegistroGeral();
            $orgao_emissor=$atendido->getOrgaoEmissor();
            $data_expedicao=$atendido->getDataExpedicao();

            /*if(count($data_expedicao) <10){
                $data_expedicao= null;
            }*/

           /* $cpf='065.123.587-16';
            $idatendido=1;
            $registro_geral='22.555.555-7';
            $orgao_emissor='detram';
            $data_expedicao='2003-11-28';*/

            $stmt->bindParam(':cpf',$cpf);
            $stmt->bindParam(':idatendido',$idatendido);
            $stmt->bindParam(':registro_geral',$registro_geral);
            $stmt->bindParam(':orgao_emissor',$orgao_emissor);
            $stmt->bindParam(':data_expedicao',$data_expedicao);
            $stmt->execute();
        
        } catch (PDOException $e) {
            echo 'Error: <b>  na tabela pessoas = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        }
    }
    public function alterarEndereco($atendido)
    {
        try {
            $sql = 'update pessoa as p inner join atendido as a on p.id_pessoa=a.pessoa_id_pessoa set cep=:cep,estado=:estado,cidade=:cidade,bairro=:bairro,logradouro=:logradouro,numero_endereco=:numero_endereco,complemento=:complemento,ibge=:ibge where idatendido=:idatendido';
            
           $sql = str_replace("'", "\'", $sql);

            $pdo = Conexao::connect();
            $stmt = $pdo->prepare($sql);

            $idatendido=$atendido->getIdatendido();
            $cep=$atendido->getCep();
            $estado=$atendido->getEstado();
            $cidade=$atendido->getCidade();
            $bairro=$atendido->getBairro();
            $logradouro=$atendido->getLogradouro();
            $numero_endereco=$atendido->getNumeroEndereco();        
            $complemento=$atendido->getComplemento();
            $ibge=$atendido->getIbge();

            $stmt->bindParam(':idatendido',$idatendido);
            $stmt->bindParam(':cep',$cep);
            $stmt->bindParam(':estado',$estado);
            $stmt->bindParam(':cidade',$cidade);
            $stmt->bindParam(':bairro',$bairro);
            $stmt->bindParam(':logradouro',$logradouro);
            $stmt->bindParam(':numero_endereco',$numero_endereco);        
            $stmt->bindParam(':complemento',$complemento);
            $stmt->bindParam(':ibge',$ibge);
            $stmt->execute();
        
        } catch (PDOException $e) {
            echo 'Error: <b>  na tabela pessoas = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        }
    }

    public function listarSobrenome($cpf)
    {
        try{
            $pessoa=array();
            $pdo = Conexao::connect();
            $consulta = $pdo->query("SELECT sobrenome from pessoa WHERE cpf='$cpf'");
            $linha = $consulta->fetch(PDO::FETCH_ASSOC);
            // $x=0;
            // while($linha = $consulta->fetch(PDO::FETCH_ASSOC)){
            //     $pessoa[$x]=$linha['id_pessoa'];
            //     $x++;
            // }
            } catch (PDOException $e){
                echo 'Error:' . $e->getMessage();
            }
            // return $pessoa;
            return $linha['sobrenome'];
    }


    public function listarIdPessoa($cpf)
    {
        try{
            $pessoa=array();
            $pdo = Conexao::connect();
            $consulta = $pdo->query("SELECT id_pessoa from pessoa WHERE cpf='$cpf'");
            $linha = $consulta->fetch(PDO::FETCH_ASSOC);
            // $x=0;
            // while($linha = $consulta->fetch(PDO::FETCH_ASSOC)){
            //     $pessoa[$x]=$linha['id_pessoa'];
            //     $x++;
            // }
            } catch (PDOException $e){
                echo 'Error:' . $e->getMessage();
            }
            // return $pessoa;
            return $linha['id_pessoa'];
    }

    public function listarPessoaExistente($cpf){
        try{
            
            $pdo = Conexao::connect();
            $sql = "SELECT id_pessoa,nome,sobrenome,sexo,telefone,data_nascimento,cpf FROM `pessoa` WHERE cpf = :cpf";
            // $cpf = '577.153.780-20';
            // echo file_put_contents('ar.txt', $cpf);
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':cpf',$cpf);
            // echo file_put_contents('ar.txt', $sql);

            $stmt->execute();
            $funcionario=array();
            
            while($linha = $stmt->fetch(PDO::FETCH_ASSOC)){
                $funcionario[] = array('id_pessoa'=>$linha['id_pessoa'],'cpf'=>$linha['cpf'],'nome'=>$linha['nome'],'sobrenome'=>$linha['sobrenome'],'sexo'=>$linha['sexo'],'data_nascimento'=>$this->formatoDataDMY($linha['data_nascimento']),'telefone'=>$linha['telefone']);
            }

        }catch (PDOException $e){
            echo 'Error: ' .  $e->getMessage();
        }
        return json_encode($funcionario);
    }

    

}
?>