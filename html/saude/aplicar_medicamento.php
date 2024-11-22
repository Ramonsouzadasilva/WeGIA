<?php 

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);
extract($_REQUEST);
session_start();

//print_r($_SESSION);exit;

    //Mudar para session
   	if(!isset($_SESSION['usuario'])){
   		header ("Location: ../index.php");
   	}

    if(!isset($_SESSION['id_fichamedica']))	{
      header('Location: ../../controle/control.php?metodo=listarUm&nomeClasse=SaudeControle&nextPage=../html/saude/aplicar_medicamento.php');
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
		$resultado = mysqli_query($conexao, "SELECT * FROM permissao p JOIN recurso r ON(p.id_recurso=r.id_recurso) WHERE id_cargo=$id_cargo AND r.descricao='Módulo Saúde'");
		if(!is_bool($resultado) and mysqli_num_rows($resultado)){
			$permissao = mysqli_fetch_array($resultado);
			if($permissao['id_acao'] < 5){
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
    header("Location: ../../home.php?msg_c=$msg");
	}	

  include_once '../../classes/Cache.php';    
  require_once "../personalizacao_display.php";

  require_once ROOT."/controle/SaudeControle.php";
   
  $id=$_GET['id_fichamedica']; 
  $cache = new Cache();
  $teste = $cache->read($id);
  $_SESSION['id_upload_med'] = $id;
  require_once "../../dao/Conexao.php";
  $pdo = Conexao::connect();
   
  if (!isset($teste)) 
  {
   		header('Location: ../../controle/control.php?metodo=listarUm&nomeClasse=SaudeControle&nextPage=../html/saude/aplicar_medicamento.php?id_fichamedica='.$id.'&id='.$id);
  }
  $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

  $exibimedparaenfermeiro = $pdo->query("SELECT * FROM saude_medicacao sm JOIN saude_atendimento sa ON(sm.id_atendimento=sa.id_atendimento) JOIN saude_fichamedica sf ON(sa.id_fichamedica=sf.id_fichamedica) WHERE sm.saude_medicacao_status_idsaude_medicacao_status = 1 and sf.id_fichamedica=".$_GET['id_fichamedica']);
  $exibimedparaenfermeiro = $exibimedparaenfermeiro->fetchAll(PDO::FETCH_ASSOC);
  $exibimedparaenfermeiro = json_encode($exibimedparaenfermeiro);

  $a = $_GET['id_fichamedica'];

  $medaplicadas = $pdo->query("SELECT medicamento, aplicacao, p.nome as nomeFuncionario FROM saude_medicacao sm JOIN saude_medicamento_administracao sa ON (sm.id_medicacao = sa.saude_medicacao_id_medicacao) join saude_atendimento saa on(saa.id_atendimento=sm.id_atendimento)
  JOIN funcionario f ON (sa.funcionario_id_funcionario=f.id_funcionario) JOIN pessoa p ON (p.id_pessoa=f.id_pessoa) WHERE saa.id_fichamedica= '$a' ORDER BY aplicacao DESC");
  $medaplicadas = $medaplicadas->fetchAll(PDO::FETCH_ASSOC);

  foreach($medaplicadas as $key => $value){
    //formatar data
    $data = new DateTime($value['aplicacao']);
    $medaplicadas[$key]['aplicacao'] = $data->format('d/m/Y h:i:s'); 
  }

  $medaplicadas = json_encode($medaplicadas);

  $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
  $medicamentoenfermeiro = $mysqli->query("SELECT * FROM saude_medicacao"); 
  //$descparaenfermeiro = $mysqli->query("SELECT descricao FROM saude_fichamedica"); A tabela saude_fichamedica não possui mais o campo de descrição
  $medstatus = $mysqli->query("SELECT * FROM saude_medicacao_status");

  $teste = $pdo->query("SELECT nome FROM pessoa p JOIN funcionario f ON(p.id_pessoa = f.id_pessoa) WHERE f.id_pessoa = " .$_SESSION['id_pessoa'])->fetchAll(PDO::FETCH_ASSOC);
  $id_funcionario = $teste[0]['nome'];

  $prontuariopublico = $pdo->query("SELECT descricao FROM saude_fichamedica_descricoes WHERE id_fichamedica= ".$_GET['id_fichamedica']);
  $prontuariopublico = $prontuariopublico->fetchAll(PDO::FETCH_ASSOC);
  $prontuariopublico = json_encode($prontuariopublico);

  
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
           
            $("#header").load("../header.php");
            $(".menuu").load("../menu.php");
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
    </style>


<!doctype html>
<html class="fixed">
   <head>
      <!-- Basic -->
      <meta charset="UTF-8">
      <title>Aplicar medicamento</title>
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
      
      <script>
        
        
         $(function(){
          // pega no SaudeControle, listarUm //

            if(localStorage.getItem("currentTab") === "2") {
              $("#tab1").removeClass("active");
              $("#tab2").addClass("active");
              $("#overview").removeClass("active");
              $("#atendimento_enfermeiro").addClass("active");

            }

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
                
                $("#exibirtipo").show();
                $("#sangue").text("Sangue: "+item.tipo_sanguineo);
                $("#sangue").val(item.tipo_sanguineo);
                
              }
            });
          });
         
          $(function(){
            var exibimedparaenfermeiro = <?= $exibimedparaenfermeiro ?>;
            $.each(exibimedparaenfermeiro,function(i,item){
              $("#tabela")
              .append($("<tr class='item "+item.medicamento+"'>")
                .append($("<td class='txt-center'>")
                  .text(item.medicamento)
                )
                .append($("<td class='txt-center'>")
                  .text(item.dosagem)
                )
                .append($("<td class='txt-center'>")
                  .text(item.horario)
                )
                .append($("<td class='txt-center'>")
                  .text(item.duracao)
                )
                .append($("<td style='display: flex; justify-content: space-evenly;'>")
                  .append($("<a href='aplicacao_upload.php?id_medicacao=" + item.id_medicacao +"&id_pessoa="+item.id_pessoa+"&id_funcionario="+item.id_funcionario+"' title='Aplicar medicamento'><button class='btn btn-primary' onclick='variosMed();'><i class='glyphicon glyphicon-hand-up'></i></button></a>"))
                 
                )
              )
            });
          });

          $(function() {
          var medaplicadas = <?= $medaplicadas ?>;
          //console.log(medaplicadas);
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
            });
          
         $(function() {
          var prontuariopublico = <?= $prontuariopublico ?>;
          stringConcatenada = "";
          $.each(prontuariopublico, function(i, item) {
            stringConcatenada += item.descricao; 
            });
          $("#prontuario_publico")
              .append($("<tr>")
                .append($("<td>")).html(stringConcatenada)
            )
          });
       
      </script>
      <style type="text/css">
      .obrig {
        color: rgb(255, 0, 0);
      }

      #prontuario_publico tr p{
        max-width: 450px;
        word-wrap: break-word;
      }
      @media(max-width:768px){
        #prontuario_publico tr p{
        max-width: 250px;
        word-wrap: break-word;
      }
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
               <h2>Aplicar medicamento</h2>
               <div class="right-wrapper pull-right">
                  <ol class="breadcrumbs">
                     <li>
                        <a href="../index.php">
                        <i class="fa fa-home"></i>
                        </a>
                     </li>
                     <li><span>Aplicar medicamento</span></li>
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
            <div class="tabs">
            <ul class="nav nav-tabs tabs-primary">
               <li class="active" id="tab1">
                  <a href="#overview" data-toggle="tab">Informações Pessoais</a>
               </li>
               <li id="tab2">
                  <a href="#atendimento_enfermeiro" data-toggle="tab">Aplicações enfermeiro</a>
               </li>
            </ul>
          
            <div class="tab-content">
                <div id="overview" class="tab-pane active">
                  <form class="form-horizontal" method="post" action="../../controle/control.php">
                    <input type="hidden" name="nomeClasse" value="SaudeControle">
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
                          <label><input type="radio" name="gender" id="radioM" id="M" disabled value="m" style="margin-top: 10px; margin-left: 15px;"> <i class="fa fa-male" style="font-size: 20px;"> </i></label>
                          <label><input type="radio" name="gender" id="radioF" disabled id="F" value="f" style="margin-top: 10px; margin-left: 15px;"> <i class="fa fa-female" style="font-size: 20px;"> </i> </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label" for="profileCompany">Nascimento</label>
                        <div class="col-md-8">
                          <input type="date" placeholder="dd/mm/aaaa" maxlength="10" class="form-control" name="nascimento" disabled id="nascimento" max=<?php echo date('Y-m-d'); ?>>
                        </div>
                    </div>

                      <div class="form-group" id="exibirtipo" style="display:none;">
                        <label class="col-md-3 control-label" for="inputSuccess">Tipo sanguíneo</label>
                        <div class="col-md-6">
                          <input class="form-control input-lg mb-md" name="tipoSanguineo" disabled id="sangue">
                        </div>
                      </div> 

                      <div class="col-md-12">
                        <table class="table table-bordered table-striped mb-none">
                        <thead>
                          <tr style="font-size:15px;">
                            <th>Prontuário público</th>
                          </tr>
                        </thead>
                        <tbody id="prontuario_publico" style="font-size:15px">
                          
                        </tbody>
                      </table>
                    </div>
                      
                     </div>
                    </section>
                  </form>
                </div>

       
       <!-- aba de atendimento enfermeiro -->
       <div id="atendimento_enfermeiro" class="tab-pane">                           
            <section class="panel">
                <header class="panel-heading">
                  <div class="panel-actions">
                      <a href="#" class="fa fa-caret-down"></a>
                  </div>
                  <h2 class="panel-title">Aplicar medicamento</h2>
                </header>
                   
                <div class="panel-body">
                <hr class="dotted short">
                   
            <table class="table table-bordered table-striped mb-none" id="datatable-default">
				    <thead>
						<tr>
						  <th class='txt-center' width='30%' id="id_medicacao">Medicações</th>
							<th class='txt-center' width='15%'>Dosagem</th>
							<th class='txt-center' width='15%'>Horário</th>
							<th class='txt-center' width='15%'>Duração</th>
							<th class='txt-center'>Aplicar</th>
						</tr>
					</thead>
					<tbody id="tabela">

					</tbody>
				</table>
        
                     <br />
                    
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
                  
            function aplicarMedicacao() {
                if (!window.confirm("Tem certeza que deseja aplicar essa medicação?")){
                  return false;
                }
            } 
           
            function variosMed()
            {
                localStorage.setItem("currentTab","2");
                alert("Medicamento aplicado com sucesso!");
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
