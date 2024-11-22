<?php
	session_start();
	if(!isset($_SESSION['usuario'])){
		header ("Location: ../index.php");
	}

	if (!isset($_SESSION['beneficio'])) {
    $id_funcionario=$_GET['id_funcionario'];
    header('Location: ../controle/control.php?metodo=listarBeneficio&nomeClasse=FuncionarioControle&nextPage=../html/editar_beneficio.php?id_funcionario='.$id_funcionario.'&id_funcionario='.$id_funcionario);
   }
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
    $mysqli = new mysqli(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
    $beneficios = $mysqli->query("SELECT * FROM beneficios");
	   
	$conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	$id_pessoa = $_SESSION['id_pessoa'];
	$resultado = mysqli_query($conexao, "SELECT * FROM funcionario WHERE id_pessoa=$id_pessoa");
	if(!is_null($resultado)){
		$id_cargo = mysqli_fetch_array($resultado);
		if(!is_null($id_cargo)){
			$id_cargo = $id_cargo['id_cargo'];
		}
		$resultado = mysqli_query($conexao, "SELECT * FROM permissao WHERE id_cargo=$id_cargo and id_recurso=11");
		if(!is_bool($resultado) and mysqli_num_rows($resultado)){
			$permissao = mysqli_fetch_array($resultado);
			if($permissao['id_acao'] < 5){
        $msg = "Você não tem as permissões necessárias para essa página.";
        header("Location: ./home.php?msg_c=$msg");
			}
			$permissao = $permissao['id_acao'];
		}else{
        	$permissao = 1;
          $msg = "Você não tem as permissões necessárias para essa página.";
          header("Location: ./home.php?msg_c=$msg");
		}	
	}else{
		$permissao = 1;
    $msg = "Você não tem as permissões necessárias para essa página.";
    header("Location: ./home.php?msg_c=$msg");
	}	

	// Adiciona a Função display_campo($nome_campo, $tipo_campo)
	require_once "personalizacao_display.php";
?>

<!doctype html>
<html class="fixed">
<head>
	<!-- Basic -->
	<meta charset="UTF-8">

	<title>Editar Benefício</title>

	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

	<!-- Vendor CSS -->
	<link rel="stylesheet" href="../assets/vendor/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="../assets/vendor/font-awesome/css/font-awesome.css" />
	<link rel="stylesheet" href="../assets/vendor/magnific-popup/magnific-popup.css" />
	<link rel="stylesheet" href="../assets/vendor/bootstrap-datepicker/css/datepicker3.css" />
	<link rel="icon" href="<?php display_campo("Logo",'file');?>" type="image/x-icon" id="logo-icon">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">

	<!-- Theme CSS -->
	<link rel="stylesheet" href="../assets/stylesheets/theme.css" />

	<!-- Skin CSS -->
	<link rel="stylesheet" href="../assets/stylesheets/skins/default.css" />

	<!-- Theme Custom CSS -->
	<link rel="stylesheet" href="../assets/stylesheets/theme-custom.css">

	<!-- Head Libs -->
	<script src="../assets/vendor/modernizr/modernizr.js"></script>

	<!-- Vendor -->
	<script src="../assets/vendor/jquery/jquery.min.js"></script>
	<script src="../assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
	<script src="../assets/vendor/bootstrap/js/bootstrap.js"></script>
	<script src="../assets/vendor/nanoscroller/nanoscroller.js"></script>
	<script src="../assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
	<script src="../assets/vendor/magnific-popup/magnific-popup.js"></script>
	<script src="../assets/vendor/jquery-placeholder/jquery.placeholder.js"></script>
		
	<!-- Specific Page Vendor -->
	<script src="../assets/vendor/jquery-autosize/jquery.autosize.js"></script>	
	<!-- Theme Base, Components and Settings -->
	<script src="../assets/javascripts/theme.js"></script>	
	<!-- Theme Custom -->
	<script src="../assets/javascripts/theme.custom.js"></script>
	
	<!-- Theme Initialization Files -->
	<script src="../assets/javascripts/theme.init.js"></script>

	<!-- javascript functions -->
	<script src="../Functions/onlyNumbers.js"></script> 
	<script	src="../Functions/onlyChars.js"></script>
	<script	src="../Functions/mascara.js"></script>
	<script src="https://igorescobar.github.io/jQuery-Mask-Plugin/js/jquery.mask.min.js"></script>
	<script>
		
	function alterardate(data){
        var date=data.split("/")
        return date[2]+"-"+date[1]+"-"+date[0];
    }

		function editar_beneficios(){
            $("#ibeneficios").prop('disabled', false);
            $("#beneficios_status").prop('disabled', false);
            $("#inicio").prop('disabled', false);
            $("#data_fim").prop('disabled', false);
            $("#valor").prop('disabled', false);

            $("#botaoEditarBeneficios").html('Cancelar');
            $("#botaoSalvarBeneficios").prop('disabled', false);
            $("#botaoEditarBeneficios").removeAttr('onclick');
            $("#botaoEditarBeneficios").attr('onclick', "return cancelar_beneficios()");
       	}

        function cancelar_beneficios(){
            $("#ibeneficios").prop('disabled', true);
            $("#beneficios_status").prop('disabled', true);
            $("#inicio").prop('disabled', true);
            $("#data_fim").prop('disabled', true);
            $("#valor").prop('disabled', true);
         
            $("#botaoEditarBeneficios").html('Editar');
            $("#botaoSalvarBeneficios").prop('disabled', true);
            $("#botaoEditarBeneficios").removeAttr('onclick');
            $("#botaoEditarBeneficios").attr('onclick', "return editar_beneficios()");
         
        }

        $(function(){
            
            var beneficio = <?php echo $_SESSION['beneficio'];?>;
            <?php unset($_SESSION['beneficio']); ?>;
            console.log(beneficio);
            $.each(beneficio,function(i,item){
            	$("#ibeneficios").val(item.id_beneficios).prop('disabled', true);
                $("#beneficios_status").val(item.beneficios_status).prop('disabled', true);
                $("#inicio").val(alterardate(item.data_inicio)).prop('disabled', true);
                $("#data_fim").val(alterardate(item.data_fim)).prop('disabled', true);
                $("#valor").val(item.valor).prop('disabled', true);
         	})
        });

		function clicar(id){
			window.location.href = "../html/profile_funcionario.php?id_funcionario="+id;
		}

		function gerarBeneficios(){
          url = '../dao/exibir_beneficios.php';
          $.ajax({
          data: '',
          type: "POST",
          url: url,
          async: true,
          success: function(response){
          	
            var beneficios = response;
            $('#ibeneficios').empty();
            $('#ibeneficios').append('<option selected disabled>Selecionar</option>');
            $.each(beneficios,function(i,item){
				$('#ibeneficios').append('<option value="' + item.id_beneficios + '">' + item.descricao_beneficios + '</option>');
			});
            },
            dataType: 'json'
          });
        }

        function adicionar_beneficios(){
          url = '../dao/adicionar_beneficios.php';
          var beneficios = window.prompt("Cadastre um Novo Benefício:");
          if(!beneficios){return}
          situacao = beneficios.trim();
          if(beneficios == ''){return}  
            data = 'beneficios=' +beneficios; 
            console.log(data);
            $.ajax({
            type: "POST",
            url: url,
            data: data,
            success: function(response){
              gerarBeneficios();
            },
            dataType: 'text'
          })
        }

		$(function () {
		    $("#header").load("header.php");
		    $(".menuu").load("menu.php");
		});
	</script>
</head>
<body>
	<section class="body">
			<!-- start: header -->
		<div id="header"></div>
		<!-- end: header -->

		<div class="inner-wrapper">
			<!-- start: sidebar -->
			<aside id="sidebar-left" class="sidebar-left menuu"></aside>
				
			<section role="main" class="content-body">
				<header class="page-header">
					<h2>Editar</h2>
				
					<div class="right-wrapper pull-right">
						<ol class="breadcrumbs">
							<li>
								<a href="home.php">
									<i class="fa fa-home"></i>
								</a>
							</li>
							<li><span>Editar</span></li>
							<li><span>Benefício</span></li>
						</ol>
				
						<a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
					</div>
				</header>

				<!-- start: page -->
				<div class="row" >
					<div class="col-md-4 col-lg-2" style=" visibility: hidden;"></div>
					<div class="col-md-8 col-lg-8" >
						<div class="tabs"  >
							<ul class="nav nav-tabs tabs-primary">
								<li class="active">
									<a href="#overview" data-toggle="tab">Editar Benefício</a>
								</li>
							</ul>
							<div class="tab-content">
								<div id="overview" class="tab-pane active">
									<form class="form-horizontal" method="POST" action="../controle/control.php">
		                            <input type="hidden" name="nomeClasse" value="FuncionarioControle">
		                            <input type="hidden" name="metodo" value="alterarBeneficiados">
		                            <h4 class="mb-xlg">Benefícios</h4>
		                            <div id="beneficio" class="tab-pane">
		                              <div class="form-group">
		                                  <label class="col-md-3 control-label" for="inputSuccess">Benefícios</label>
		                                  <a onclick="adicionar_beneficios()"><i class="fas fa-plus w3-xlarge" style="margin-top: 0.75vw"></i></a>
		                                  <div class="col-md-6">
		                                     <select class="form-control input-lg mb-md" name="ibeneficios" id="ibeneficios">
		                                        <option selected disabled>Selecionar</option>
		                                        <?php 
		                                        while($row = $beneficios->fetch_array(MYSQLI_NUM)){
		                                          echo "<option value=".$row[0].">".$row[1]."</option>";
		                                        }?>
		                                     </select>
		                                  </div>
		                               </div>

		                               <div class="form-group">
		                                  <label class="col-md-3 control-label" for="inputSuccess">Benefícios Status</label>
		                                  <div class="col-md-6">
		                                     <select class="form-control input-lg mb-md" name="beneficios_status" id="beneficios_status">
		                                        <option selected disabled>Selecionar</option>
		                                        <option value="Ativo">Ativo</option>
		                                        <option value="Inativo">Inativo</option>
		                                     </select>
		                                  </div>
		                               </div>
		                              <div class="form-group">
		                                <label class="col-md-3 control-label" for="profileCompany">Data Início</label>
		                                <div class="col-md-8">
		                                  <input type="date" placeholder="dd/mm/aaaa" maxlength="10" class="form-control" name="data_inicio" id="inicio" max=<?php echo date('Y-m-d'); ?> >
		                                </div>
		                              </div>
		                              <div class="form-group">
		                                <label class="col-md-3 control-label" for="profileCompany">Data Fim</label>
		                                <div class="col-md-8">
		                                  <input type="date" placeholder="dd/mm/aaaa" maxlength="10" class="form-control" name="data_fim" id="data_fim">
		                                </div>
		                              </div>
		                              <div class="form-group">
				                          <label class="col-md-3 control-label" for="profileCompany">Valor</label>
				                          <div class="col-md-8">
				                             <input type="text" name="valor" class="dinheiro form-control" id="valor" maxlength="13" placeholder="Ex: 22.00" onkeypress="return Onlynumbers(event)">
				                          </div>
				                       </div>
		                            </div>
		                            <br>
		                            <input type="hidden" name="id_funcionario" value=<?php echo $_GET['id_funcionario'] ?>>
					                <a onclick="clicar(<?php echo $_GET['id_funcionario'] ?>)"><input type="button" class="btn btn-primary" value="Voltar" style="background-color: #5bc0de; border-color: #5bc0de; text-decoration:none;"></a>
					                <button type="button" class="btn btn-primary" id="botaoEditarBeneficios" onclick="return editar_beneficios()">Editar</button>
                            		<input type="submit" class="btn btn-primary" disabled="true"  value="Salvar" id="botaoSalvarBeneficios" disabled="true">
		                          </form>
								</div>
							</div>
						</div>
					</div>
				</div>
					<!-- end: page -->
			</section>
		</div>
	</section>

</body>
</html>