<?php 

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);
extract($_REQUEST);
session_start();

   	if(!isset($_SESSION['usuario'])){
   		header ("Location: ../index.php");
   	}

     if(!isset($_SESSION['id_fichamedica']))	{
      header('Location: ../../controle/control.php?metodo=listarUm&nomeClasse=SaudeControle&nextPage=../html/saude/profile_paciente.php');
    }

    $config_path = "config.php";
    if (file_exists($config_path)) {
      require_once($config_path);
    } else {
      while (true) {
        $config_path = "../" . $config_path;
        if (file_exists($config_path)) break;
      }
      require_once($config_path);
    }

  
require_once "../../dao/Conexao.php";
$pdo = Conexao::connect();

$conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$id_pessoa = $_SESSION['id_pessoa'];
$resultado = mysqli_query($conexao, "SELECT * FROM funcionario WHERE id_pessoa=$id_pessoa");
if(!is_null($resultado)){
    $id_cargo = mysqli_fetch_array($resultado);
    if(!is_null($id_cargo)){
    $id_cargo = $id_cargo['id_cargo'];
    }
    $resultado = mysqli_query($conexao, "SELECT * FROM permissao p JOIN acao a ON(p.id_acao=a.id_acao) JOIN recurso r ON(p.id_recurso=r.id_recurso) WHERE id_cargo=$id_cargo AND a.descricao = 'LER, GRAVAR E EXECUTAR' AND r.descricao='Ficha do paciente'");
    if(!is_bool($resultado) and mysqli_num_rows($resultado)){
    $permissao = mysqli_fetch_array($resultado);
    if($permissao['id_acao'] < 7){
    $msg = "Você não tem as permissões necessárias para essa página.";
    header("Location: ../home.php?msg_c=$msg");
    }
    $permissao = $permissao['id_acao'];
    }else{
        $permissao = 1;
        $msg = "Você não tem as permissões necessárias para essa página.";
        header("Location: ../home.php?msg_c=$msg");
    }	
}else{
    $permissao = 1;
$msg = "Você não tem as permissões necessárias para essa página.";
header("Location: ../home.php?msg_c=$msg");
}	


  include_once '../../classes/Cache.php';    
  require_once "../personalizacao_display.php";

  require_once ROOT."/controle/SaudeControle.php";
   
  $id=$_GET['id_fichamedica']; 
  $cache = new Cache();
  $teste = $cache->read($id);
  require_once "../../dao/Conexao.php";
  $pdo = Conexao::connect();

  if (!isset($teste)) 
  {
   		header('Location: ../../controle/control.php?metodo=listarUm&nomeClasse=SaudeControle&nextPage=../html/saude/profile_paciente.php?id_fichamedica='.$id.'&id='.$id);
  }

  $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

  $docfuncional = $pdo->query("SELECT * FROM saude_exames se JOIN saude_exame_tipos st ON se.id_exame_tipos  = st.id_exame_tipo WHERE id_fichamedica= ".$_GET['id_fichamedica']);
  $docfuncional = $docfuncional->fetchAll(PDO::FETCH_ASSOC);
  foreach ($docfuncional as $key => $value) {
    $docfuncional[$key]["arquivo"] = gzuncompress($value["arquivo"]);
    //formata data
    $data = new DateTime($value['data']);
    $docfuncional[$key]['data'] = $data->format('d/m/Y');
  }
  $docfuncional = json_encode($docfuncional);

  $enfermidades = $pdo->query("SELECT sf.id_CID, sf.data_diagnostico, sf.status, stc.descricao FROM saude_enfermidades sf JOIN saude_tabelacid stc ON sf.id_CID = stc.id_CID WHERE stc.CID NOT LIKE 'T78.4%' AND sf.status = 1 AND id_fichamedica= ".$_GET['id_fichamedica']);
  $enfermidades = $enfermidades->fetchAll(PDO::FETCH_ASSOC);
  $enfermidades = json_encode($enfermidades);

  $alergias = $pdo->query("SELECT sf.id_CID, sf.data_diagnostico, sf.status, stc.descricao FROM saude_enfermidades sf JOIN saude_tabelacid stc ON sf.id_CID = stc.id_CID WHERE stc.CID LIKE 'T78.4%' AND sf.status = 1 AND id_fichamedica= ".$_GET['id_fichamedica']);
  $alergias = $alergias->fetchAll(PDO::FETCH_ASSOC);
  $alergias = json_encode($alergias);

  $sinaisvitais = $pdo->query("SELECT data, saturacao, pressao_arterial, frequencia_cardiaca, frequencia_respiratoria, temperatura, hgt, p.nome, p.sobrenome FROM saude_sinais_vitais sv JOIN funcionario f ON(sv.id_funcionario = f.id_funcionario) JOIN pessoa p ON (f.id_pessoa = p.id_pessoa) WHERE sv.id_fichamedica = " .$id)->fetchAll(PDO::FETCH_ASSOC);

  foreach($sinaisvitais as $key => $value){
    //formata data
    $data = new DateTime($value['data']);
    $sinaisvitais[$key]['data'] = $data->format('d/m/Y h:i:s');
  }

  $sinaisvitais = json_encode($sinaisvitais);

  $descricao_medica = $pdo->query("SELECT descricao, data_atendimento FROM saude_atendimento WHERE id_fichamedica= ".$_GET['id_fichamedica']);
  $descricao_medica = $descricao_medica->fetchAll(PDO::FETCH_ASSOC);

  foreach($descricao_medica as $key => $value){
    //formata data
    $data = new DateTime($value['data_atendimento']);
    $descricao_medica[$key]['data_atendimento'] = $data->format('d/m/Y');
  }

  $descricao_medica = json_encode($descricao_medica);
  
  $exibimed = $pdo->query("SELECT id_medicacao, data_atendimento, medicamento, dosagem, horario, duracao, st.descricao FROM saude_atendimento sa JOIN saude_medicacao sm ON (sa.id_atendimento=sm.id_atendimento) JOIN saude_medicacao_status st ON (sm.saude_medicacao_status_idsaude_medicacao_status = st.idsaude_medicacao_status)  WHERE id_fichamedica= ".$_GET['id_fichamedica']);
  $exibimed = $exibimed->fetchAll(PDO::FETCH_ASSOC);

  foreach($exibimed as $key => $value){
    //formata data
    $data = new DateTime($value['data_atendimento']);
    $exibimed[$key]['data_atendimento'] = $data->format('d/m/Y');
  }

  $exibimed = json_encode($exibimed);

  $prontuariopublico = $pdo->query("SELECT descricao FROM saude_fichamedica_descricoes WHERE id_fichamedica= ".$_GET['id_fichamedica']);
  $prontuariopublico = $prontuariopublico->fetchAll(PDO::FETCH_ASSOC);
  $prontuarioPHP = $prontuariopublico;
  $prontuariopublico = json_encode($prontuariopublico);

  $medaplicadas = $pdo->query("SELECT medicamento, aplicacao, p.nome as nomeFuncionario FROM saude_medicacao sm JOIN saude_medicamento_administracao sa ON (sm.id_medicacao = sa.saude_medicacao_id_medicacao) join saude_atendimento saa on(saa.id_atendimento=sm.id_atendimento)
  JOIN funcionario f ON (sa.funcionario_id_funcionario=f.id_funcionario) JOIN pessoa p ON (p.id_pessoa=f.id_pessoa) WHERE saa.id_fichamedica= '$id' ORDER BY aplicacao DESC");
  $medaplicadas = $medaplicadas->fetchAll(PDO::FETCH_ASSOC);

  foreach($medaplicadas as $key => $value){
    //formata data
    $data = new DateTime($value['aplicacao']);
    $medaplicadas[$key]['aplicacao'] = $data->format('d/m/Y h:i:s');
  }

  $medaplicadas = json_encode($medaplicadas);

  $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
  $tabelacid_enfermidades = $mysqli->query("SELECT * FROM saude_tabelacid WHERE CID NOT LIKE 'T78.4%'");
  $tabelacid_alergias = $mysqli->query("SELECT * FROM saude_tabelacid WHERE CID LIKE 'T78.4%'");
  $ultima_alergia = $mysqli->query("SELECT * FROM saude_tabelacid WHERE CID LIKE 'T78.4%' ORDER BY CID DESC LIMIT 1");
  $cargoMedico = $mysqli->query("SELECT * FROM pessoa p JOIN funcionario f ON (p.id_pessoa=f.id_pessoa) WHERE f.id_cargo = 3");
  $cargoEnfermeiro = $mysqli->query("SELECT * FROM pessoa p JOIN funcionario f ON (p.id_pessoa=f.id_pessoa) WHERE f.id_cargo = 4");
  //$tipoexame = $mysqli->query("SELECT * FROM saude_exame_tipos");
  $tipoexame = $pdo->query("SELECT * FROM saude_exame_tipos ORDER BY descricao ASC")->fetchAll(PDO::FETCH_ASSOC);
  $medicamentoenfermeiro = $mysqli->query("SELECT * FROM saude_medicacao"); 
  //$descparaenfermeiro = $mysqli->query("SELECT descricao FROM saude_fichamedica"); ficha médica não possuí mais descrição
  $medstatus = $mysqli->query("SELECT * FROM saude_medicacao_status");

  $teste1 = $pdo->query("SELECT nome FROM pessoa p JOIN funcionario f ON(p.id_pessoa = f.id_pessoa) WHERE f.id_pessoa = " .$_SESSION['id_pessoa'])->fetchAll(PDO::FETCH_ASSOC);
  $id_funcionario = $teste1[0]['nome'];

  try{
    $stmtProcuraIdPaciente = $pdo->prepare("SELECT id_pessoa FROM saude_fichamedica WHERE id_fichamedica =:idFicha");
    $idFicha = $_GET['id_fichamedica'];
    
    $stmtProcuraIdPaciente->bindParam(':idFicha', $idFicha);
    $stmtProcuraIdPaciente->execute();
    $idPaciente = $stmtProcuraIdPaciente->fetch(PDO::FETCH_ASSOC)['id_pessoa'];
  }catch(PDOException $e){
    echo $e->getMessage();
  }
   
 
?>
    <!-- Vendor -->
    <script src="<?php echo WWW;?>assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?php echo WWW;?>assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
    <script src="<?php echo WWW;?>assets/vendor/bootstrap/js/bootstrap.js"></script>
    <script src="<?php echo WWW;?>assets/vendor/nanoscroller/nanoscroller.js"></script>
    <script src="<?php echo WWW;?>assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
    <script src="<?php echo WWW;?>assets/vendor/magnific-popup/magnific-popup.js"></script>
    <script src="<?php echo WWW;?>assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>
        
    <!-- Specific Page Vendor -->
    <script src="<?php echo WWW;?>assets/vendor/jquery-autosize/jquery.autosize.js"></script>
        
    <!-- Theme Base, Components and Settings -->
    <!-- <script src="<?php echo WWW;?>assets/javascripts/theme.js"></script> -->
        
    <!-- Theme Custom -->
    <script src="<?php echo WWW;?>assets/javascripts/theme.custom.js"></script>
        
    <!-- Theme Initialization Files -->
    <script src="<?php echo WWW;?>assets/javascripts/theme.init.js"></script>

    <!-- javascript functions -->
    <script src="<?php echo WWW;?>Functions/onlyNumbers.js"></script>
    <script src="<?php echo WWW;?>Functions/onlyChars.js"></script>
    <script src="<?php echo WWW;?>Functions/mascara.js"></script>

    <!-- jkeditor -->
    <script src="<?php echo WWW;?>assets/vendor/ckeditor/ckeditor.js"></script>
        
    <!-- Specific Page Vendor CSS -->
	  <link rel="stylesheet" href="../../assets/vendor/select2/select2.css" />
	  <link rel="stylesheet" href="../../assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />


    <script>
      
        $(function(){
          localStorage.setItem("id_ficha_medica",'null')
           
            $("#header").load("../header.php");
            $(".menuu").load("../menu.php");            

            var editor = CKEDITOR.replace('despacho');
            editor.on('required', function(e){
                alert("Por favor, informe a descrição!");
                e.cancel();
            });

        });

    </script>
    
    <style type="text/css">
        .select{
            position: absolute;
            width: 235px;
        }
        .select-table-filter{
            width: 140px;
            float: left;
        }
        .panel-body{
            margin-bottom: 15px;
        }
        #div_texto
        {
            width: 100%;
        }
        #cke_despacho
        {
            height: 500px;
        }
        .cke_inner
        {
            height: 500px;
        }
        #cke_1_contents
        {
            height: 455px !important;
        }
        .col-md-3 {
            width: 10%;
        }

        .titulo-prontuario{
            font-weight: bold;
        }

    </style>


<!DOCTYPE html>
<html class="fixed">
   <head>
      <!-- Basic -->
      <meta charset="UTF-8">
      <title>Informações paciente</title>
      <!-- Mobile Metas -->
      <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
      <!-- Web Fonts  -->
      <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">
      <!-- Vendor CSS -->
      <link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.css" />
      <link rel="stylesheet" href="../../assets/vendor/font-awesome/css/font-awesome.css" />
      <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
      <link rel="stylesheet" href="../../assets/vendor/magnific-popup/magnific-popup.css" />
      <link rel="stylesheet" href="../../assets/vendor/bootstrap-datepicker/css/datepicker3.css" />

      <link rel="stylesheet" type="text/css" href="../../css/profile-theme.css"> 
      </script> <script src="../../assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>  
      <script src="../../assets/vendor/nanoscroller/nanoscroller.js"></script>
      <script src="../../assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
      <script src="../../assets/vendor/magnific-popup/magnific-popup.js"></script>
      <script src="../../assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>
      <!-- Theme CSS -->
      <link rel="stylesheet" href="../../assets/stylesheets/theme.css" />
      <!-- Skin CSS -->
      <link rel="stylesheet" href="../../assets/stylesheets/skins/default.css" />
      <!-- Theme Custom CSS -->
      <link rel="stylesheet" href="../../assets/stylesheets/theme-custom.css">
      <!-- Head Libs -->
      <script src="../../assets/vendor/modernizr/modernizr.js"></script>
      <script src="../../Functions/lista.js"></script>
      <!-- JavaScript Functions -->
	    <script src="../../Functions/enviar_dados.js"></script>
      <script src="../../Functions/mascara.js"></script>
      <link rel="icon" href="<?php display_campo("Logo",'file');?>" type="image/x-icon" id="logo-icon">
      <script>
        
        function excluirimg(id)
            {
               $("#excluirimg").modal('show');
               $('input[name="id_documento"]').val(id);
            }
         function editimg(id,descricao)
            {
               $('#teste').val(descricao).prop('selected', true);
               $('input[name="id_documento"]').val(id);
               $("#editimg").modal('show');
            }
        
         $(function(){
          // pega no SaudeControle, listarUm //
            var interno = <?php echo $_SESSION['id_fichamedica']; ?>;

         	  $.each(interno,function(i,item){
              if(i=1)
              {
                $("#formulario").append($("<input type='hidden' name='id_fichamedica' value='"+item.id+"'>"));
                $("#nome").text("Nome: "+item.nome+' '+item.sobrenome);
                $("#nome").val(item.nome + " " + item.sobrenome);

                if(item.imagem!="" && item.imagem!=null){
                      $("#imagem").attr("src","data:image/gif;base64,"+item.imagem);
                    }else{
                      $("#imagem").attr("src","../../img/semfoto.png");
                    }
                if(item.sexo=="m")
                {
                  $("#sexo").html("Sexo: <i class='fa fa-male'></i>  Masculino");
                  $("#radioM").prop('checked',true);
                }
                else if(item.sexo=="f")
                {
                  $("#sexo").html("Sexo: <i class='fa fa-female'>  Feminino");
                  $("#radioF").prop('checked',true);
                }
              
                $("#nascimento").text("Data de nascimento: "+item.data_nascimento);
                $("#nascimento").val(item.data_nascimento);
                
                 if(item.tipo_sanguineo == null || item.tipo_sanguineo == ""){
                   $("#adicionartipo").show(); 
                  
                 }
                 else if(item.tipo_sanguineo !=null && item.tipo_sanguineo != "")
                 {
                   $("#sangue").text("Sangue: "+item.tipo_sanguineo);
                   $("#sangue").val(item.tipo_sanguineo);
                   $("#exibirtipo").show();
                 }
              }
            });
          });
         
          // exame // 
          $(function() {
          var docfuncional = <?= $docfuncional ?>;
          $.each(docfuncional, function(i, item) {
            $("#dep-tab")
              .append($("<tr>")
                .append($("<td>").text(item.arquivo_nome))
                .append($("<td>").text(item.descricao))
                .append($("<td>").text(item.data))
                .append($("<td style='display: flex; justify-content: space-evenly;'>")
                  .append($("<a href='exame_download.php?id_doc=" + item.id_exame + "' title='Visualizar ou Baixar'><button class='btn btn-primary'><i class='fas fa-download'></i></button></a>"))
                  .append($("<a onclick='removerFuncionarioDocs("+item.id_exame+")' href='#' title='Excluir'><button class='btn btn-danger'><i class='fas fa-trash-alt'></i></button></a>"))
                )
              )
            });
          });
          // importante para remover exame //
          function listarFunDocs(docfuncional){
                  $("#dep-tab").empty();
                $.each(docfuncional, function(i, item) {
                  $("#dep-tab")
                    .append($("<tr>")
                      .append($("<td>").text(item.arquivo_nome))
                      .append($("<td>").text(item.descricao))
                      .append($("<td>").text(item.data))
                      .append($("<td style='display: flex; justify-content: space-evenly;'>")
                        .append($("<a href='exame_download.php?id_doc=" + item.id_exame + "' title='Visualizar ou Baixar'><button class='btn btn-primary'><i class='fas fa-download'></i></button></a>"))
                        .append($("<a onclick='removerFuncionarioDocs("+item.id_exame+")' href='#' title='Excluir'><button class='btn btn-danger'><i class='fas fa-trash-alt'></i></button></a>"))
                      )
                    )
                });
          }

          // enfermidade //
          $(function() {
          var enfermidades = <?= $enfermidades ?>;
            $.each(enfermidades, function(i, item) {
              $("#doc-tab")
                .append($("<tr>")
                  .append($("<td>").text(item.descricao))
                  .append($("<td>").text(item.data_diagnostico))
                  .append($("<td style='display: flex; justify-content: space-evenly;'>")
                    .append($("<a onclick='removerEnfermidade("+item.id_CID+")' href='#' title='Inativar'><button class='btn btn-dark'><i class='glyphicon glyphicon-remove'></i></button></a>"))
                  
                  )
                )
              });

           let alergias = <?= $alergias ?>;
            $.each(alergias, function(i, item) {
              $("#doc-tab-alergias")
                .append($("<tr>")
                  .append($("<td>").text(item.descricao))
                  .append($("<td style='display: flex; justify-content: space-evenly;'>")
                    .append($("<a onclick='removerEnfermidade("+item.id_CID+")' href='#' title='Inativar'><button class='btn btn-dark'><i class='glyphicon glyphicon-remove'></i></button></a>"))
                  
                  )
                )
              });
          });
        
          function listarEnfermidades(enfermidades){
                  $("#doc-tab").empty();
                $.each(enfermidades, function(i, item) {
                  $("#doc-tab")
                    .append($("<tr>")
                      .append($("<td>").text(item.descricao))
                      .append($("<td>").text(item.data_diagnostico))
                      .append($("<td style='display: flex; justify-content: space-evenly;'>")
                  .append($("<a onclick='removerEnfermidade("+item.id_CID+")' href='#' title='Inativar'><button class='btn btn-primary'><i class='glyphicon glyphicon-remove'></i></button></a>"))
                )
              )
            });
          }

          //descricao medica 
          $(function() {
          var descricao_medica = <?= $descricao_medica ?>;
          $.each(descricao_medica, function(i, item) {
            $("#de-tab")
              .append($("<tr>")
                .append($("<td>").html(item.descricao))
                .append($("<td>").text(item.data_atendimento))
              )
            });
          });

          $(function() {
          var exibimed = <?= $exibimed ?>;
          $.each(exibimed, function(i, item) {
            $("#exibimed")
              .append($("<tr>")
              .append($("<td>").text(item.data_atendimento))
                .append($("<td>").text(item.medicamento + ", " + item.dosagem + ", " + item.horario + ", " + item.duracao + "."))
                .append($("<td>").text(item.descricao))
                .append($("<td style='display: flex; justify-content: space-evenly;'>")
                  .append($("<a onclick='editarStatusMedico("+item.id_medicacao+")' href='#'title='Editar'><button class='btn btn-primary' id='teste'><i class='glyphicon glyphicon-pencil'></i></button></a>"))
                )
              )
            });
          });

          // listar aplicacao enfermeiro
          $(function() {
          var medaplicadas = <?= $medaplicadas ?>;
          $.each(medaplicadas, function(i, item) {
            $("#exibiaplicacao")
              .append($("<tr>")
                .append($("<td>").text(item.nomeFuncionario))
                .append($("<td>").text(item.medicamento))
                .append($("<td>").text(item.aplicacao))
              )
            });
          });

          $(function() {
              $('#datatable-docfuncional').DataTable({
                  "order": [
                  [0, "asc"]
                  ]
                });
                $('.datatable-docfuncional').DataTable({
                  "order": [
                  [0, "asc"]
                  ]
                });
          });

        function escrevermed() {
           
          let nome_medicacao = window.prompt("Informe a medicação:");
          $("#primeira_medicacao").remove();
          $("#mais_medicacoes").show();
          $(".meddisabled").val(nome_medicacao);
        }
        /*$(function() {
        var selects = $('select#tipoDocumento');
        for(let n = 0; n < selects.length; n++){
          var options = $('select#tipoDocumento:eq('+n+') option')
          var arr = options.map(function(_, o) {
            return {
                t: $(o).text().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, ""),
                tf: $(o).text(),
                v: o.value
            };
            }).get();
            arr.sort(function(o1, o2) {
                if(o1.t !== 'selecionar')
                  return o1.t > o2.t ? 1 : o1.t < o2.t ? -1 : 0;
            });
            options.each(function(i, o) {
                o.value = arr[i].v;
                $(o).text(arr[i].tf);
            });
          }
      });*/
      </script>
      <style type="text/css">
      .obrig {
        color: rgb(255, 0, 0);
      }

      .btn-edicaoProntuario{
        margin-top: 10px;
      }

      .hidden{
        display: none;
      }
      </style>
        
   </head>
   <body>
      <section class="body">
         <div id="header"></div>
            <!-- end: header -->
            <div class="inner-wrapper">
               <!-- start: sidebar -->
               <aside id="sidebar-left" class="sidebar-left menuu"></aside>
         <!-- end: sidebar -->
         <section role="main" class="content-body">
            <header class="page-header">
               <h2>Informações paciente</h2>
               <div class="right-wrapper pull-right">
                  <ol class="breadcrumbs">
                     <li>
                        <a href="../index.php">
                        <i class="fa fa-home"></i>
                        </a>
                     </li>
                     <li><span>Informações paciente</span></li>
                  </ol>
                  <a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
               </div>
            </header>
            <!-- start: page -->
            <div class="row">
            <div class="col-md-4 col-lg-3">
               <section class="panel">
                        <div class="panel-body">
                            <div class="thumb-info mb-md">
                                  <img id="imagem" alt="John Doe">
                            </div>
                        </div>
               </section>
            </div>
            <div class="col-md-8 col-lg-8">
            <?php 
              if(isset($_SESSION['msg']) && !empty($_SESSION['msg'])){
                $mensagem = $_SESSION['msg'];
                if($mensagem == 'Prontuário público adicionado ao histórico com sucesso'){
                  echo "<div class=\"alert alert-success\" role=\"alert\">
                  $mensagem
                  <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">
                    <span aria-hidden=\"true\">&times;</span>
                  </button>
                </div>";
                }else{
                  echo "<div class=\"alert alert-danger\" role=\"alert\">
                  $mensagem
                  <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">
                    <span aria-hidden=\"true\">&times;</span>
                  </button>
                </div>";
                }

                unset($_SESSION['msg']);

              }
            //echo 'teste';
            ?>
            <div class="tabs">
            <ul class="nav nav-tabs tabs-primary">
               <li class="active">
                  <a href="#overview" data-toggle="tab">Informações Pessoais</a>
               </li>
               <li>
                  <a href="#cadastro_comorbidades" data-toggle="tab">Cadastro de Comorbidades</a>
               </li>
               <li>
                  <a href="#arquivo" data-toggle="tab">Cadastro de Exames</a>
               </li>
               <li>
                  <a href="#historico_medico" data-toggle="tab">Histórico Médico</a>
               </li>
               <li>
               <li>
                  <a href="#atendimento_medico" data-toggle="tab">Atendimento Médico</a>
               </li>
               <li>
                  <a href="#medicacoes_aplicadas" data-toggle="tab">Medicações Aplicadas</a>
               </li>
               <li>
                  <a href="#sinais_vitais" data-toggle="tab">Sinais Vitais</a>
               </li>
            </ul>
          
            <div class="tab-content">
              
                <div id="overview" class="tab-pane active">
                  <form class="form-horizontal" method="post" action="../../controle/control.php">
                    <input type="hidden" name="nomeClasse" value="SaudeControle">
                    <!-- <input type="hidden" name="metodo" value="alterarInfPessoal"> -->
                    <section class="panel">
                      <header class="panel-heading">
                        <div class="panel-actions">
                        <a href="#" class="fa fa-caret-down"></a>
                        </div>
                      <h2 class="panel-title">Informações pessoais</h2>
                      </header>
                    
                    <div class="panel-body">
                    <hr class="dotted short">
                    <fieldset>
                      
                    <div class="form-group">
                        <label class="col-md-3 control-label" for="profileFirstName">Nome</label>
                        <div class="col-md-8">
                          <input type="text" class="form-control" disabled name="nome" id="nome">
                        </div>
                    </div>

                    <div class="form-group">
                      <label class="col-md-3 control-label" for="profileLastName">Sexo</label>
                        <div class="col-md-8">
                          <label><input type="radio" name="gender" id="radioM" id="M" disabled value="m" style="margin-top: 10px; margin-left: 15px;" onclick="return exibir_reservista()"> <i class="fa fa-male" style="font-size: 20px;"> </i></label>
                          <label><input type="radio" name="gender" id="radioF" disabled id="F" value="f" style="margin-top: 10px; margin-left: 15px;" onclick="return esconder_reservista()"> <i class="fa fa-female" style="font-size: 20px;"> </i> </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label" for="profileCompany">Nascimento</label>
                        <div class="col-md-8">
                          <input type="date" placeholder="dd/mm/aaaa" maxlength="10" class="form-control" name="nascimento" disabled id="nascimento" max=<?php echo date('Y-m-d'); ?>>
                        </div>
                    </div>

                    <!-- caso o paciente já tenha o tipo sanguíneo definido -->
                      <div class="form-group" id="exibirtipo" style="display:none;">
                        <label class="col-md-3 control-label" for="inputSuccess">Tipo sanguíneo</label>
                        <div class="col-md-6">
                          <input class="form-control input-lg mb-md" name="tipoSanguineo" disabled id="sangue">
                        </div>
                      </div> 

                      <!-- caso o paciente não tenha o tipo sanguineo definido -->
                      <div id="adicionartipo"  style="display:none;" class="form-group">
                      <input type="hidden" name="metodo" value="alterarInfPessoal">

                      <label class="col-md-3 control-label" for="inputSuccess">Tipo sanguíneo</label>
                        <div class="col-md-6">
                          <select class="form-control input-lg mb-md" name="tipoSanguineo" id="tipoSanguineo">
                            <option selected>Selecionar</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                          </select>
                        </div>
                      <!-- </div> -->
                      <input type="hidden" name="id_fichamedica" value=<?php echo $_GET['id_fichamedica'] ?>>
                     <!-- <div class="col-md-9 col-md-offset-3"> -->
                      <input type="submit" class="btn btn-primary" value="Salvar" id="botaoSalvarTipoSanguineo">
                     <!-- </div>  -->

                      <br>
                      <br>
                      <!-- <div class="form-group"> -->
                      <!-- </div>   -->
                      
                     </div>
                    
                        <div class="form-group" id="exibiralergias">
                          <table class="table table-bordered table-striped" id="datatable-alergias">
                            <thead>
                              <tr style="font-size:15px;">
                                <th>Alergias</th>    
                                </tr>
                            </thead>
                            <tbody id="doc-tab-alergias">
                            </tbody>
                          </table>
                          <br>
                         
                          <form action ='alergia_upload.php' method='post' id='funcionarioDoc'>
                            <div class='col-md-12' id="div_alergia" style="display: none;">
                              <div class="form-group">
                                  <label class="col-md-3 control-label" for="inputSuccess">Alergias</label>                    
                                  <div class="col-md-6">
                                    <select class="form-control input-lg mb-md" name="id_CID_alergia" id="id_CID_alergia">
                                      <option selected disabled>Selecionar</option>
                                      <?php
                                       $alergias_decoded = json_decode($alergias, true);
                                      while ($row = $tabelacid_alergias->fetch_array(MYSQLI_NUM)) {
                                        $rowIdCID = $row[0];
                                        $found = false;
                                        foreach($alergias_decoded as $alergia){
                                          var_dump($alergia['id_CID']);
                                          if(isset($alergia['id_CID']) && $alergia['id_CID'] == $rowIdCID){
                                            $found = true;
                                            break;
                                          }
                                        }
                                        if(!$found)
                                        {
                                           echo "<option value=" . $row[0] . ">" . $row[2] . "</option>";
                                        }
                                      }?>
                                    </select>
                                  </div>
                                <a onclick="adicionar_alergia()"><i class="fas fa-plus w3-xlarge" style="margin-top: 0.75vw"></i></a>
                                <!-- <input type="number" name="id_fichamedica" value="<?= $_GET['id_fichamedica']; ?>" style='display: none;'> -->
                                
                              </div>
                              <div class="form-group">
                                <input type="button" onclick="alergia_upload()" class="btn btn-primary" id="salvarAlergia" value="Salvar" style="display: none;">
                              </div>
                            </div>
                          </form>
                        </div>
                                       
                    <br>
                    <br>

                    </div>
                    </section>
                  </form>
                  <form action="../../controle/control.php" method="POST" id="editarProntuario">
                      <input type="hidden" name="nomeClasse" value="SaudeControle">
                      <input type="hidden" name="metodo" value="alterarProntuario">
                      <input type="hidden" name="id_fichamedica" value="<?php echo $_GET['id_fichamedica'] ?>">
                    
                    <label for="textoProntuario" class="titulo-prontuario">Prontuário Público</label>
                    <textarea name="textoProntuario" class="form-control" required id="prontuario" cols="30" rows="10"><?php
                        $stringConcatenada = '';

                        foreach($prontuarioPHP as $prontuario){
                          $stringConcatenada .= $prontuario['descricao'];
                        }

                        echo $stringConcatenada;
                    ?></textarea>
                    <button id="btn-editarProntuario" class="btn btn-primary btn-edicaoProntuario" onclick="event.preventDefault(); editarProntuario();">Editar Prontuário</button>
                    <button id="btn-cancelarEdicao" class="btn btn-danger btn-edicaoProntuario hidden" onclick="event.preventDefault(); cancelarEdicao()" >Cancelar</button>
                    <button type="submit" id="btn-confirmarEdicao" class="btn btn-success btn-edicaoProntuario hidden">Salvar</button>
                  </form>

                  <form action="../../controle/control.php" method="POST">
                      <input type="hidden" name="nomeClasse" value="SaudeControle">
                      <input type="hidden" name="metodo" value="adicionarProntuarioAoHistorico">
                      <input type="hidden" name="id_fichamedica" value="<?php echo $_GET['id_fichamedica'] ?>">
                      <input type="hidden" name="id_paciente" value="<?= $idPaciente?>">
                  
                    <button id="btn-adicionarAoHistorico" class="btn btn-primary btn-edicaoProntuario">Adicionar versão ao histórico</button>

                    <button id="btn-listarDoHistorico" class="btn btn-primary btn-edicaoProntuario" onclick="event.preventDefault(); listarProntuariosDoHistorico()">Listar prontuários do histórico</button>
                  </form>

                  
                </div>

                    
    <!-- Aba  de  comorbidades -->
    <div id="cadastro_comorbidades" class="tab-pane">
      <section class="panel">
        <header class="panel-heading">
            <div class="panel-actions">
                  <a href="#" class="fa fa-caret-down"></a>
            </div>
            <h2 class="panel-title">Cadastro de comorbidades</h2>
        </header>
        <div class="panel-body">
          <hr class="dotted short">

            <table class="table table-bordered table-striped mb-none" id="datatable-dependente">
                <thead>
                    <tr style="font-size:15px;">
                      <th>Comorbidades</th>    
                      <th>Data</th>
                      <th>Status</th>
                    </tr>
                </thead>
                    <tbody id="doc-tab">
                            
                    </tbody>
            </table>

            <br> 
            <form action='enfermidade_upload.php' method='post' enctype='multipart/form-data' id='funcionarioDocForm'>
              <div class="form-group">
                <div class="col-md-6">
                   <h5 class="obrig">Campos Obrigatórios(*)</h5>
                </div>
              </div>
                 
             

                  <div class="form-group">
                    <label class="col-md-3 control-label" for="inputSuccess">Enfermidades<sup class="obrig">*</sup></label>
                    <!-- <a onclick="adicionar_enfermidade()"><i class="fas fa-plus w3-xlarge" style="margin-top: 0.75vw"></i></a> -->
                    <div class="col-md-6">
                    
                      <select class="form-control input-lg mb-md" name="id_CID" id="id_CID" required>
                        <option selected disabled>Selecionar</option>
                        <?php
                        while ($row = $tabelacid_enfermidades->fetch_array(MYSQLI_NUM)) {
                          echo "<option value=" . $row[0] . ">" . $row[2] . "</option>";
                        }                            ?>
                      </select>
                    </div>
                    <a onclick="adicionar_enfermidade()"><i class="fas fa-plus w3-xlarge" style="margin-top: 0.75vw"></i></a>
                  </div>
                    
                       
                       <div class="form-group">
                        <label class="col-md-3 control-label" for="profileCompany" id="data_diagnostico">Data do diagnóstico<sup class="obrig">*</sup></label>
                        <div class="col-md-6">
                        <input type="date" placeholder="dd/mm/aaaa" maxlength="10" class="form-control" name="data_diagnostico" id="data_diagnostico" max=<?php echo date('Y-m-d'); ?> required>
                      </div>
                     </div>

                        <div class="form-group">
                          <label class="col-md-3 control-label" for="inputSuccess">Status<sup class="obrig">*</sup></label>
                          <div class="col-md-6">
                          <select class="form-control input-lg mb-md" name="intStatus" id="intStatus" required> 
                            <option selected disabled>Selecionar</option>
                            <option value="1">Ativo</option>
                            <option value="0">Inativo</option>
                          </select>
                        </div>
                        </div>
                        
                        <div class="form-group">
                        <div class="col-md-6">
                        <input type="number" name="id_fichamedica" value="<?= $_GET['id_fichamedica']; ?>" style='display: none;'>
                          <input type="hidden" name="id_fichamedica" value=<?php echo $_GET['id_fichamedica'] ?>>
                          <input type="submit" class="btn btn-primary" value="Cadastrar" id="botaoSalvarIP">
                        </div> 
                        </div>
                    </form>
                  </div>
              </section>
         </div>

        <!-- Aba de exames -->
          <div id="arquivo" class="tab-pane">
              <section class="panel">
                <header class="panel-heading">
                 <div class="panel-actions">
                   <a href="#" class="fa fa-caret-down"></a>
                  </div>
                  <h2 class="panel-title">Exames</h2>
                </header>
                <div class="panel-body">
                <hr class="dotted short">
                      <table class="table table-bordered table-striped mb-none">
                        <thead>
                          <tr style="font-size:15px;">
                            <th>Arquivo</th>
                            <th>Tipo exame</th>
                            <th>Data exame</th>
                            <th>Ação</th>
                          </tr>
                        </thead>
                        <tbody id="dep-tab" style="font-size:15px">
                          
                        </tbody>
                      </table>
                      <br>
                      <!-- Button trigger modal -->
                      <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#docFormModal">
                        Adicionar
                      </button>
                      <div class="modal fade" id="docFormModal" tabindex="-1" role="dialog" aria-labelledby="docFormModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                          <div class="modal-content">

                            <div class="modal-header" style="display: flex;justify-content: space-between;">
                              <h5 class="modal-title" id="exampleModalLabel">Adicionar exame</h5>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>

                            <form action='exame_upload.php' method='post' enctype='multipart/form-data' id='funcionarioDocForm'>
                              <div class="modal-body" style="padding: 15px 40px">
                                <div class="form-group" style="display: grid;">

                                  <div class="form-group">
                                    <label for="arquivoDocumento">Exame</label>
                                    <input name="arquivo" type="file" class="form-control-file" id="id_documento" accept="png;jpeg;jpg;pdf;docx;doc;odp" required>
                                  </div>

                              <div class="form-group">

                                      <label for="arquivoDocumento">Tipo de exame</label>
                                      
                                    <div style="display: flex;">
                                    
                                        <select class="form-control input-lg mb-md" name="id_docfuncional" id="tipoDocumento" style="width:170px;" required> 
                                        <option value="" selected disabled>Selecionar</option>
                                          <?php
                                          /*while ($row = $tipoexame->fetch_array(MYSQLI_NUM)) {
                                          echo "<option value=" . $row[0] . ">" . $row[1] . "</option>";
                                          }*/
                                          foreach($tipoexame as $tipo){
                                            $id = $tipo['id_exame_tipo'];
                                            $descricao = $tipo['descricao'];
                                            echo "<option value=\"$id\">$descricao</option>";
                                          }
                                          ?>
                                        </select>
                                        <a onclick="adicionar_exame()"><i class="fas fa-plus w3-xlarge" style="margin: 15px 15px 15px 15px;"></i></a>
                                  </div>
                                  
                                </div>

                                
                               
                              <input type="number" name="id_fichamedica" value="<?= $_GET['id_fichamedica']; ?>" style='display: none;'>
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                <input type="submit" value="Enviar" class="btn btn-primary">
                              </div>
                            </form>
                            </div>
                          </div>
                        </div>
                    <br/>
                </section>
           </div>
       
      <!-- aba de atendimento médico -->
       <div id="historico_medico" class="tab-pane">
         <section class="panel">
            <header class="panel-heading">
               <div class="panel-actions">
                  <a href="#" class="fa fa-caret-down"></a>
               </div>
               <h2 class="panel-title">Histórico Médico</h2>
            </header>
               
            <div class="panel-body">
            <hr class="dotted short">
              <div class="form-group">
                <table class="table table-bordered table-striped mb-none">
                  <thead>
                    <tr style="font-size:15px;">
                      <th>Descrições</th>
                      <th>Data do atendimento</th>
                    </tr>
                  </thead>
                  <tbody id="de-tab" style="font-size:15px">                
                  
                 </tbody>
               </table>
             </div>
              
             <div class="form-group">
             <hr class="dotted short">
                <table class="table table-bordered table-striped mb-none datatable-docfuncional">
                  <thead>
                    <tr style="font-size:15px;">
                      <th>Data do atendimento</th>
                      <th>Medicações</th>
                      <th>Status</th>
                      <th>Ação</th>
                    </tr>
                  </thead>
                  <tbody id="exibimed" style="font-size:15px">                
                  
                 </tbody>
               </table>
                </div>
              
                <div class="modal fade" id="testemed" tabindex="-1" role="dialog" aria-labelledby="docFormModalLabel" aria-hidden="true" style="display:none;">
                  <div class="modal-dialog" role="document">
                     <div class="modal-content">

                        <div class="modal-header" style="display: flex;justify-content: space-between;">
                          <h5 class="modal-title" id="exampleModalLabel">Alterar Status</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <form action='status_update.php' method='post' enctype='multipart/form-data' id='funcionarioDocForm'>
                              <div class="modal-body" style="padding: 15px 40px">
                                <div class="form-group" style="display: grid;">
                                   

                                  <label class="my-1 mr-2" for="tipoDocumento">Status</label><br>
                                  <div style="display: flex;">
                                    <input type="hidden" name="id_fichamedica" id="id_fichamedica" value="<?php echo $_GET['id_fichamedica']; ?>"/>
                                    <select class="form-control input-lg mb-md" name="id_status" id="id_status" style="width:170px;" required>
                                    <option selected disabled>Selecionar</option>
                                      <?php
                                      while ($row = $medstatus->fetch_array(MYSQLI_NUM)) {
                                      echo "<option value=" . $row[0] . ">" . $row[1] . "</option>";
                                      }
                                      ?>
                                    </select>
                                  </div>
                                </div>
                               
                              <!-- <input type="number" name="id_fichamedica" value="<?= $_GET['id_fichamedica']; ?>" style='display: none;'> -->
                                <!-- <input type="number" name="id_interno" value="" style='display: none;'> -->
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                <input type="hidden" class="statusDoenca" name="id_medicacao">
                                <input type="submit" value="Enviar" class="btn btn-primary">
                              </div>
                            </form>
                            </div>
                          </div>
                        </div>
              

            </div>
          </section>
       </div>

      <!-- aba de cadastro médico -->
      <div id="atendimento_medico" class="tab-pane">
         <section class="panel" id="medicacao">
             <header class="panel-heading">
                <div class="panel-actions">
                    <a href="#" class="fa fa-caret-down"></a>
                </div>

             <h2 class="panel-title">Atendimento médico</h2>
             </header>
             <div class="panel-body">
             <div class="form-group" id="escondermedicacao">

             <form action='atendmedico_upload.php' method='post' enctype='multipart/form-data' id='funcionarioDocForm'>
               <hr class="dotted short">
                 <div class="form-group">
                  <div class="col-md-6">
                   <h5 class="obrig">Campos Obrigatórios(*)</h5>
                  </div>
                 </div>
              
                   <div class="form-group">
                     <label class="col-md-3 control-label" for="profileCompany" id="data_atendimento">Data do atendimento:<sup class="obrig">*</sup></label>
                     <div class="col-md-6">
                     <input type="date" class="form-control" maxlength="10" placeholder="dd/mm/aaaa" name="data_atendimento" id="data_atendimento" max=<?php echo date('Y-m-d'); ?> required>
                     </div>
                    
                   </div>
                   
                   <!-- listar o funcionario, pessoa nome onde cargo = 3 -->
                   <div class="form-group">
                        <label class="col-md-3 control-label" for="inputSuccess">Médico:</label>
                        <div class="col-md-8">
                          <input class="form-control" style="width:230px;" name="medico" id="medico" value="<?php echo $id_funcionario; ?>" disabled="true">
                          </div>
                      </div>

                   <div class="form-group">
                     <label class="col-md-3 control-label" for="profileCompany" for="texto">Descrição:<sup class="obrig">*</sup></label>
                       <div class='col-md-6' id='div_texto' style="height: 499px;">
                        <textarea cols='30' rows='3' id='despacho' name='texto' class='form-control' value="teste" placeholder="teste" required></textarea>                        
                        </div>
                      </div>

                      <br>

                      <div class="form-group" id="primeira_medicacao">
                        <label class="col-md-3 control-label" for="inputSuccess">Medicamento:<sup class="obrig">*</sup></label>
                        <!-- <a onclick="escrevermed()"><i class="fas fa-plus w3-xlarge" style="margin-top: 0.75vw"></i></a> -->
                        <div class="col-md-6">
                          <input type="text" class="form-control meddisabled" name="nome_medicacao" id="nome_medicacao">
                        </div>
                        
                      </div>
                      </div>

                      <!--                                     
                      caso não tenha a medicacao no select
                      <div id="mais_medicacoes" style="display:none;">
                        <div class="form-group">
                        <label class="col-md-3 control-label" for="profileCompany">Medicamento:<sup class="obrig">*</sup></label>
                        <a onclick="escrevermed()"><i class="fas fa-plus w3-xlarge" style="margin-top: 0.75vw"></i></a>
                        <div class="col-md-6">
                          <input type="text" class="form-control meddisabled" name="nome_medicacao" id="nome_medicacao">
                        </div>
                        </div>
                        <br>
                      </div> -->
                    
                    <!-- <div class="form-group">
                      <label class="col-md-3 control-label" for="profileCompany">Laboratório:</label>
                      <div class="col-md-6">
                        <input type="text" class="form-control" name="laboratorio" id="laboratorio">
                      </div>
                    </div> -->

                    <div class="form-group">
                      <label class="col-md-3 control-label" for="profileCompany">Dosagem:<sup class="obrig">*</sup></label>
                      <div class="col-md-6">
                        <input type="text" class="form-control" name="dosagem" id="dosagem">
                      </div>
                     </div>

                     <div class="form-group">
                      <label class="col-md-3 control-label" for="profileCompany">Horário:<sup class="obrig">*</sup></label>
                      <div class="col-md-6">
                        <input type="time" class="form-control" name="horario_medicacao" id="horario_medicacao">
                      </div>
                   </div>

                   <div class="form-group">
                      <label class="col-md-3 control-label" for="profileCompany">Duração:<sup class="obrig">*</sup></label>
                     <div class="col-md-6">
                       <input type="text" class="form-control" name="duracao_medicacao" id="duracao_medicacao">
                     </div>
                   </div>
                  
                   <br>
                   <hr class="dotted short">
                      <table class="table table-bordered table-striped mb-none datatable-docfuncional" id="tabmed">
                        <thead>
                          <tr style="font-size:15px;">
                            <th>Medicação</th>
                            <th>Dosagem</th>
                            <th>Horário</th>
                            <th>Duração</th>
                            <th>Ação</th>
                          </tr>
                        </thead>
                        <tbody id="dep-tab" style="font-size:15px">
                          
                      </tbody>
                      </table>
                      <br> 
                   <br>
                   <button type="button" class="btn btn-success" id="botao">Prescrever medicação</button>  
                 <br>
                 <br>
                 <input type="number" name="id_fichamedica" value="<?= $_GET['id_fichamedica']; ?>" style='display: none;'>
                <input type="hidden" name="acervo">
                <!-- value=<?php echo $_GET['id_fichamedica'] ?>-->
                <input type="submit" class="btn btn-primary" value="Cadastrar" id="salvar_bd">
              <!-- id="salvar_bd" -->
            </div>
          </form>
          </section>
       </div>
      
       <!-- Aba de medicações aplicadas -->
       <div id="medicacoes_aplicadas" class="tab-pane">                           
            <section class="panel">
              <header class="panel-heading">
                <div class="panel-actions">
                    <a href="#" class="fa fa-caret-down"></a>
                </div>
                <h2 class="panel-title">Medicações aplicadas</h2>
              </header>
              
              <div class="panel-body">
              <hr class="dotted short">
                
                <table class="table table-bordered table-striped mb-none" id="enf">
                <thead>
                  <tr style="font-size:15px;">
                    <th>Responsável pela aplicação</th>
                    <th>Medicações aplicadas</th>
                    <th>Horário da aplicação</th>
                  </tr>
                </thead>
                <tbody id="exibiaplicacao" style="font-size:15px">                
                
              </tbody>
            </table>
              
            <br>
            <br>
            
            <input type="hidden" name="a_enf">
         </section>
       </div>  
       
       <!-- Aba de medicações aplicadas -->
      <div id="sinais_vitais" class="tab-pane">
        <header class="panel-heading">
          <div class="panel-actions">
              <a href="#" class="fa fa-caret-down"></a>
          </div>
          <h2 class="panel-title">Sinais Vitais Aferidos</h2>
        </header>
            
            <div class="panel-body">
            <hr class="dotted short">
              
              <table class="table table-bordered table-striped mb-none" id="tab-sin-vit">
              <thead>
                <tr style="font-size:15px;">
                  <th>Data</th>
                  <th>Aferidor</th>
                  <th>Saturação</th>
                  <th>Pressão arterial</th>
                  <th>Frequência cardíaca</th>
                  <th>Frequência repiratória</th>
                  <th>Temperatura</th>
                  <th>HGT</th>
                </tr>
              </thead>
              <tbody id="exibe-sinais-vitais" style="font-size:15px">                
              
            </tbody>
          </table>
            
          <br>
          <br>
          
          <input type="hidden" name="a_enf">
      </div>
      
       

       
         <aside id="sidebar-right" class="sidebar-right">
            <div class="nano">
               <div class="nano-content">
                  <a href="#" class="mobile-close visible-xs">
                  Collapse <i class="fa fa-chevron-right"></i>
                  </a>
                  <div class="sidebar-right-wrapper">
                     <div class="sidebar-widget widget-calendar">
                        <h6>Upcoming Tasks</h6>
                        <div data-plugin-datepicker data-plugin-skin="dark" ></div>
                        <ul>
                           <li>
                              <time datetime="2014-04-19T00:00+00:00">04/19/2014</time>
                              <span>Company Meeting</span>
                           </li>
                        </ul>
                     </div>
                     <div class="sidebar-widget widget-friends">
                        <h6>Friends</h6>
                        <ul>
                           <li class="status-online">
                              <figure class="profile-picture">
                                 <img src="../../img/semfoto.png" alt="Joseph Doe" class="img-circle">
                              </figure>
                              <div class="profile-info">
                                 <span class="name">Joseph Doe Junior</span>
                                 <span class="title">Hey, how are you?</span>
                              </div>
                           </li>
                           <li class="status-online">
                              <figure class="profile-picture">
                                 <img src="../../img/semfoto.png" alt="Joseph Doe" class="img-circle">
                              </figure>
                              <div class="profile-info">
                                 <span class="name">Joseph Doe Junior</span>
                                 <span class="title">Hey, how are you?</span>
                              </div>
                           </li>
                           <li class="status-offline">
                              <figure class="profile-picture">
                                 <img src="../../img/semfoto.png" alt="Joseph Doe" class="img-circle">
                              </figure>
                              <div class="profile-info">
                                 <span class="name">Joseph Doe Junior</span>
                                 <span class="title">Hey, how are you?</span>
                              </div>
                           </li>
                           <li class="status-offline">
                              <figure class="profile-picture">
                                 <img src="../../img/semfoto.png" alt="Joseph Doe" class="img-circle">
                              </figure>
                              <div class="profile-info">
                                 <span class="name">Joseph Doe Junior</span>
                                 <span class="title">Hey, how are you?</span>
                              </div>
                           </li>
                        </ul>
                     </div>
                  </div>
               </div>
            </div>
         </aside>
      </section>
		<!-- Vendor -->
		  <script src="../../assets/vendor/select2/select2.js"></script>
        <script src="../../assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
        <script src="../../assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
        <script src="../../assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
        <!-- Theme Custom -->
        <script src="../../assets/javascripts/theme.custom.js"></script>
        <!-- Theme Initialization Files -->
        <!-- Examples -->
        <script src="../../assets/javascripts/tables/examples.datatables.default.js"></script>
        <script src="../../assets/javascripts/tables/examples.datatables.row.with.details.js"></script>
        <script src="../../assets/javascripts/tables/examples.datatables.tabletools.js"></script>
         <div class="modal fade" id="excluirimg" role="dialog">
         <div class="modal-dialog">
         <!-- Modal content-->
         <div class="modal-content">
         <div class="modal-header">
	      <button type="button" class="close" data-dismiss="modal">×</button>
	      <h3>Excluir um Documento</h3>
         </div>
         <div class="modal-body">
         <p> Tem certeza que deseja excluir a imagem desse documento? Essa ação não poderá ser desfeita! </p>
         <form action="../../controle/control.php" method="GET">
            <input type="hidden" name="id_documento" id="excluirdoc">
            <input type="hidden" name="nomeClasse" value="DocumentoControle">
            <input type="hidden" name="metodo" value="excluir">
            <input type="hidden" name="id" value="">
            <input type="submit" value="Confirmar" class="btn btn-success">
            <button button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
         </form>
         </div>
         </div>
         </div>
         </div>
         <iv class="modal fade" id="editimg" role="dialog">
         <div class="modal-dialog">
         <!-- Modal content-->
         <div class="modal-content">
         <div class="modal-header">
         <button type="button" class="close" data-dismiss="modal">×</button>
         <h3>Alterar um Documento</h3>
         </div>
         <div class="modal-body">
         <p> Selecione o benefício referente a nova imagem</p>
         <form action="../../controle/control.php" method="POST" enctype="multipart/form-data">
            <select name="descricao" id="teste">
               <option value="Certidão de Nascimento">Certidão de Nascimento</option>
               <option value="Certidão de Casamento">Certidão de Casamento</option>
               <option value="Curatela">Curatela</option>
               <option value="INSS">INSS</option>
               <option value="LOAS">LOAS</option>
               <option value="FUNRURAL">FUNRURAL</option>
               <option value="Título de Eleitor">Título de Eleitor</option>
               <option value="CTPS">CTPS</option>
               <option value="SAF">SAF</option>
               <option value="SUS">SUS</option>
               <option value="BPC">BPC</option> 
               <option value="CPF">CPF</option>
               <option value="Registro Geral">RG</option>
            </select><br/>
            
            <p> Selecione a nova imagem</p>
            <div class="col-md-12">
               <input type="file" name="doc" size="60"  class="form-control" > 
            </div><br/>
            <input type="hidden" name="id_documento" id="id_documento">
            <input type="hidden" name="id" value="">
            <input type="hidden" name="nomeClasse" value="DocumentoControle">
            <input type="hidden" name="metodo" value="alterar">
            <input type="submit" value="Confirmar" class="btn btn-success">
            <button button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
         </form>
         </div>
         </div>
         </div>
         </div>
         
      <script>
          function removerFuncionarioDocs(id_doc) {
                if (!window.confirm("Tem certeza que deseja remover esse exame?")){
                  return false;
                }
                let url = "exame_excluir.php?id_doc="+id_doc+"&id_fichamedica=<?= $_GET['id_fichamedica'] ?>";
                let data = "";
                post(url, data, listarFunDocs);
                console.log(listarFunDocs);
            } 
            function removerEnfermidade(id_doc) {
                if (!window.confirm("Tem certeza que deseja inativar essa enfermidade?")){
                  return false;
                }
                let url = "enfermidade_excluir.php?id_doc="+id_doc+"&id_fichamedica=<?= $_GET['id_fichamedica'] ?>";
                let data = "";
                post(url, data, listarEnfermidades);
                console.log(listarEnfermidades);
            } 
            function editarStatusMedico(id_medicacao)
            {
              $("#testemed").modal('show');
              // $(".statusDoenca").append("<input type='hidden' value="+id_medicacao+">");
              $(".statusDoenca").val(id_medicacao);

            }            
            function aplicarMedicacao(id_doc) {
                if (!window.confirm("Tem certeza que deseja aplicar essa medicação?")){
                  return false;
                }
                //document.querySelector(".bot click").style.background = 'Red';
                let url = "mudarcor.php?id_doc="+id_doc+"&id_fichamedica=<?= $_GET['id_fichamedica'] ?>";
                let data = "";
                post(url, data);
                

            } 


            //Adicionar alergias
            $(document).ready(function(){
              $("#exibiralergias").append("<div class='col-md-6'><input type='button' class='btn btn-success' value='Adicionar alergia' id='addAlergia'></div>");
              $("#addAlergia").on('click', function(){
                $("#addAlergia").hide();
                $("#div_alergia").css("display","block");
                $("#salvarAlergia").css("display","block");
              })
            });





            function gerarExame() {
            url = 'exibir_exame.php';
            $.ajax({
              data: '',
              type: "POST",
              url: url,
              async: true,
              success: function(response) {
                var situacoes = response;
                $('#tipoDocumento').empty();
                $('#tipoDocumento').append('<option selected disabled>Selecionar</option>');
                $.each(situacoes, function(i, item) {
                  $('#tipoDocumento').append('<option value="' + item.id_exame_tipo + '">' + item.descricao + '</option>');
                });
              },
              dataType: 'json'
            });
          }

          function adicionar_exame() {
            url = 'adicionar_exame.php';
            let situacao = window.prompt("Cadastre uma novo exame:");
            if (!situacao) {
              return
            }
            situacao = situacao.trim();
            if (situacao == '') {
              return
            }

            data = 'situacao=' + situacao;

            console.log(data);
            $.ajax({
              type: "POST",
              url: url,
              data: data,
              success: function(response) {
                gerarExame();
              },
              dataType: 'text'
            })
          }


          function gerarEnfermidade() {
            url = 'exibir_enfermidade.php';
            $.ajax({
              data: '',
              type: "POST",
              url: url,
              async: true,
              success: function(response) {
                
                var situacoes = response;
                console.log("situacoes",situacoes)
                let index = situacoes.length - 1;
                $('#id_CID').append('<option value="' + situacoes[index].id_CID + '">' + situacoes[index].descricao + '</option>');
              },
              dataType: 'json'
            });
          }

          function adicionar_enfermidade() {
            url = 'adicionar_enfermidade.php';
            let nome_enfermidade = window.prompt("Insira o nome da enfermidade:");
            let cid_enfermidade = window.prompt("Insira o CID da enfermidade:");
            let situacao = [cid_enfermidade, nome_enfermidade] 

            if (!nome_enfermidade || !cid_enfermidade) {
              return
            }
            if (nome_enfermidade == '' || cid_enfermidade == '') {
              return
            }

            data = {cid:cid_enfermidade,nome:nome_enfermidade};
            console.log(data);
            $.ajax({
              type: "POST",
              url: url,
              data: data,
              success: function(response) {
                gerarEnfermidade();
              },
            })
          }
          


          function gerar_alergia(){
            url = 'exibir_alergia.php';
            $.ajax({
              data: '',
              type: "POST",
              url: url,
              async: true,
              success: function(response) {
                var situacoes_alergia = response;
                let alergias = <?= $alergias;?>;
                console.log(alergias)
                console.log(situacoes_alergia);
                $('#id_CID_alergia').empty();
                $('#id_CID_alergia').append('<option selected disabled>Selecionar</option>');
                $.each(situacoes_alergia, function(i, item) {
                  if(!(alergias.includes(item))){
                    $('#id_CID_alergia').append('<option value="' + item.id_CID + '">' + item.descricao + '</option>');
                  }
                  
                   
                });
              },
              dataType: 'json'
            });
          }


          function adicionar_alergia() {
            url = 'adicionar_alergia.php';
            let nome_alergia = window.prompt("Insira o nome da alergia:");

            if (!nome_alergia || nome_alergia == '') {
              return;
            }
            data = {nome:nome_alergia};
            $.ajax({
              type: "POST",
              url: url,
              data: data,
              success: function(response) {
                gerar_alergia();
              },
              error: function(response){
                console.log(response);
              },
            })
          }

          function alergia_upload(){
            url = 'alergia_upload.php';
            let id_CID_alergia = $("#id_CID_alergia").val();
            let id_fichamedica = "<?= $_GET['id_fichamedica']; ?>" ;
            let data ={id_CID_alergia:id_CID_alergia, id_fichamedica:id_fichamedica};
            $.post({
              url: url,
              data: data,
              success: function(response){
                location.reload();
              }
            })  
          }

            // codigo para inserir medicacao na tabela do medico
            $(function(){

                let tabela_medicacao = new Array();
                $("#botao").click(function(){
                
                let medicamento = $("#nome_medicacao").val();
                let dose = $("#dosagem").val();
                let horario = $("#horario_medicacao").val();
                let duracao =  $("#duracao_medicacao").val();
                
                if(medicamento == "" || medicamento == null || dose == "" || horario == "" || duracao =="")
                {
                  alert("Por favor, informe a medicação corretamente!");
                
                }
                else{
                $("#tabmed").find(".dataTables_empty").hide();
                  
                $("#tabmed").append($("<tr>").addClass("tabmed")
                  .append($("<td>") .text(medicamento) )
                  .append($("<td>") .text(dose) )
                  .append($("<td>") .text(horario) )
                  .append($("<td>") .text(duracao) )
                  .append($("<td style='display: flex; justify-content: space-evenly;'>")
                  .append($("<button class='btn btn-danger'><i class='fas fa-trash-alt'></i></button>"))));
               
                        let tabela = {
                                "nome_medicacao": medicamento,
                                "dosagem": dose,
                                "horario": horario,
                                "tempo": duracao
                            };
                        tabela_medicacao.push(tabela);
                        $("#nome_medicacao").val("");
                        $("#dosagem").val("");
                        $("#horario_medicacao").val(""); 
                        $("#duracao_medicacao").val("");
                }
                })
                $("#tabmed").on("click", "button", function(){

                    let tamanho = tabela_medicacao.length;
                    
                    let dados = tabela_medicacao[0].medicamento + tabela_medicacao[0].dose + tabela_medicacao[0].horario + tabela_medicacao[0].duracao;
                    let dados_existentes = $(this).parents("#tabmed tr").text();
                    if(dados == dados_existentes)
                    {
                        tabela_medicacao.splice(0,1);
                    }
                    else
                    {
                        let i;
                        for(i=1;i<tamanho;i++)
                        {
                            let dd = tabela_medicacao[i].medicamento + tabela_medicacao[i].dose + tabela_medicacao[i].horario + tabela_medicacao[i].duracao;
                            if(dd == dados_existentes)
                            {   
                                tabela_medicacao.splice(i,1);
                            }
                        }
                    }
                    $(this).parents("#tabmed tr").remove();
                })

                  $("#salvar_bd").click(function(){
                    $("input[name=acervo]").val(JSON.stringify(tabela_medicacao)).val();
                  })

                });


            $(function() {
              var sinaisvitais = <?= $sinaisvitais ?>;
              $("#sin-vit-tab").empty();
              /*$.each(sinaisvitais, function (i,item){ // Transforma o formato de data recebido para o formato utilizado no Brasil
                item.data = item.data.split(" ")[0];
                partesData = item.data.split("-");
                item.data = partesData[2]+"-"+partesData[1]+"-"+partesData[0];  
              })*/
              $.each(sinaisvitais, function(i, item) {
                $("#exibe-sinais-vitais")
                  .append($("<tr>")
                    .append($("<td>").text(item.data))
                    .append($("<td>").text(item.nome+" "+(item.sobrenome !== null ? item.sobrenome : "")))
                    .append($("<td>").text(item.saturacao))
                    .append($("<td>").text(item.pressao_arterial))
                    .append($("<td>").text(item.frequencia_cardiaca))
                    .append($("<td>").text(item.frequencia_respiratoria))
                    .append($("<td>").text(item.temperatura))
                    .append($("<td>").text(item.hgt))
                  )
              });
            });
            $(document).ready(function() {
                $('#tab-sin-vit').DataTable({
                    "order": [
                    [0, "desc"]
                    ]
                  });
              });



        var editor2 = CKEDITOR.replace('prontuario', {
          readOnly:true
        });

        editor2.on('required', function(e){
          alert("Por favor, informe a descrição!");
          e.cancel();
        });


        function editarProntuario(){
          editor2.setReadOnly(false);
          document.getElementById('btn-editarProntuario').classList.add('hidden');
          document.getElementById('btn-adicionarAoHistorico').classList.add('hidden');
          document.getElementById('btn-listarDoHistorico').classList.add('hidden');
          document.getElementById('btn-cancelarEdicao').classList.remove('hidden');
          document.getElementById('btn-confirmarEdicao').classList.remove('hidden');
        }
            
        function cancelarEdicao(){
          editor2.setReadOnly(true);
          document.getElementById('btn-editarProntuario').classList.remove('hidden');
          document.getElementById('btn-adicionarAoHistorico').classList.remove('hidden');
          document.getElementById('btn-listarDoHistorico').classList.remove('hidden');
          document.getElementById('btn-cancelarEdicao').classList.add('hidden');
          document.getElementById('btn-confirmarEdicao').classList.add('hidden');
          location.reload();
        }

        function listarProntuariosDoHistorico(){
          const idPaciente = <?= $idPaciente?>;
          window.location.href = `./historico_prontuarios.php?id_paciente=${idPaciente}`
        }

        </script>
        
        <!-- Vendor -->
        <script src="<?php echo WWW;?>assets/vendor/select2/select2.js"></script>
        <script src="<?php echo WWW;?>assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
        <script src="<?php echo WWW;?>assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
        <script src="<?php echo WWW;?>assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
        
        <!-- Theme Custom -->
        <script src="<?php echo WWW;?>assets/javascripts/theme.custom.js"></script>

        <script src="<?php echo WWW;?>assets/javascripts/theme.js"></script>
        
        <!-- Theme Initialization Files -->
        <!-- Examples -->
        <script src="<?php echo WWW;?>assets/javascripts/tables/examples.datatables.default.js"></script>
        <script src="<?php echo WWW;?>assets/javascripts/tables/examples.datatables.row.with.details.js"></script>
        <script src="<?php echo WWW;?>assets/javascripts/tables/examples.datatables.tabletools.js"></script>
        
        <!-- importante para a aba de exames -->
        <script src="../geral/post.js"></script>
        <script src="../geral/formulario.js"></script>
	
	<div align="right">
	<iframe src="https://www.wegia.org/software/footer/saude.html" width="200" height="60" style="border:none;"></iframe>
	</div>
  </body>
</html> 