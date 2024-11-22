<?php
include("../php/conexao.php");
include("../php/preencheForm.php");
include("../php/logo_titulo.php");
ini_set('display_errors', 0);
ini_set('display_startup_erros', 0);

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
	<title>Seja um Sócio Contribuidor</title>
	<meta charset="UTF-8">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>

	<script type="text/javascript" src="../js/outros.js"></script>
	<script type="text/javascript" src="../js/geraboleto.js"></script>
	<script type="text/javascript" src="../js/verificar.js"></script>
	<script type="text/javascript" src="../js/validacpfcnpj.js"></script>
	<script type="text/javascript" src="../js/retornadia.js"></script>
	<script type="text/javascript" src="../js/cadastroSocio.js"></script>
	<script type="text/javascript" src="../js/transicoes.js"></script>
	<script type="text/javascript" src="../js/formDoacaoMod.js"></script>
	<script type="text/javascript" src="../../socio/sistema/controller/script/valida_cpf_cnpj.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
	<link rel="stylesheet" type="text/css" href="../outros/css/index.css">

	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="https://fonts.googleapis.com/css?family=Bitter&display=swap" rel="stylesheet">
	<!--
=========================================================================================-->

	<link rel="stylesheet" type="text/css" href="../outros/vendor/bootstrap/css/bootstrap.min.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="../outros/fonts/font-awesome-4.7.0/css/font-awesome.min.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="../outros/fonts/iconic/css/material-design-iconic-font.min.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="../outros/vendor/animate/animate.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="../outros/vendor/animsition/css/animsition.min.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="../outros/vendor/select2/select2.min.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="../outros/vendor/daterangepicker/daterangepicker.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="../outros/vendor/noui/nouislider.min.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="../outros/css/util.css">
	<link rel="stylesheet" type="text/css" href="../outros/css/main.css">
	<link rel="stylesheet" type="text/css" href="../outros/css/donation.css">

	<!--===============================================================================================-->
	<style>
		#logo_img {
			display: block;
			margin-left: auto;
			margin-right: auto;
		}

		#avisoPf {
			font-size: 20px;
			color: red;
			display: block;
			margin-left: auto;
			margin-right: auto;
		}

		#avisoPj {
			font-size: 20px;
			color: red;
			display: block;
			margin-left: auto;
			margin-right: auto;
		}

		.disabled {
			pointer-events: none;
			opacity: 0.5;
		}

		.pultima_div {
			margin-left: auto;
			margin-right: auto;
		}

		.loader {
			border: 1px solid #f3f3f3;
			border-radius: 50%;
			border-top: 1px solid #3498db;
			width: 20px;
			height: 20px;
			-webkit-animation: spin 2s linear infinite;
			animation: spin 2s linear infinite;
			margin: 0 auto !important;
		}

		@-webkit-keyframes spin {
			0% {
				-webkit-transform: rotate(0deg);
			}

			100% {
				-webkit-transform: rotate(360deg);
			}
		}

		@keyframes spin {
			0% {
				transform: rotate(0deg);
			}

			100% {
				transform: rotate(360deg);
			}
		}
	</style>
</head>

<body>
	<div class="container-contact100">
		<div class="wrap-contact100">
			<form class="contact100-form validate-form" method="POST" name="f2">
				<span id="logo_img"><?php resgataImagem(); ?></span>
				<span class="contact100-form-title" id="titulo_pag"><?php resgataParagrafo(); ?></span>

				<input type="hidden" name="forma-contribuicao" id="forma-contribuicao" value="boleto">

				<div id="pag1" class="wrap-input100">

					<div id="doacao_boleto">

						<div id="input" class="wrap-input100 validate-input bg1">
							<span class="label-input100">Digite um valor</span>
							<input class="input100" type='number' id='v' name='v' placeholder="Digite um valor de doação única." onblur="toReal(v);" required min="<?= $minvalunic ?>">
							<input type='hidden' id='valunic' value='<?php echo $minvalunic ?>'>

							<p id="avisa_valor"></p>
						</div>

						<div class="container-contact100-form-btn">
							<button class="contact100-form-btn" id="avanca-novo">
								AVANÇAR
								<i class="fa fa-long-arrow-right m-l-7" aria-hidden="true"></i>
							</button>
						</div>

					</div>
				</div>

				<div id="verifica_socio" class="wrap-input100">
					<input class="radio" type="radio" id="op_cpf" value="fisica" name="opcao" onblur="fisjur(f2.opcao)" checked><label class="label" for="op_cpf">PESSOA FÍSICA</label>
					<input class="radio" type="radio" id="op_cnpj" value="juridica" name="opcao" onblur="fisjur(f2.opcao)"><label class="label" for="op_cnpj">PESSOA JURÍDICA</label><br><br>

					<div id="cpf" class="wrap-input100 validate-input bg1" data-validate="Digite um documento válido!">
						<span class="label-input100">Digite um documento CPF*</span>
						<input class="input100" type="text" name="dcpf" id="dcpf" class="text required" placeholder="Ex: 222.222.222-22" onkeypress="return Onlynumbers(event)" onkeyup="mascara('###.###.###-##',this,event)" required><span id="avisa_cpf"></span>
					</div>

					<div id="cnpj" class="wrap-input100 validate-input bg1" data-validate="Digite um documento válido!">
						<span class="label-input100"> Digite um documento CNPJ *</span>
						<input class="input100" type="text" name="dcpf" id="dcnpj" onkeyup="FormataCnpj(this,event)" maxlength="18" class="form-control input-md" ng-m placeholder="22.222.222/2222-22"><span id="avisa_cnpj"></span>
					</div>
					<div class="container-contact100-form-btn">
						<button class="contact100-form-btn" id="volta_btn">
							<i style="margin-right: 15px; " class="fa fa-long-arrow-left m-l-7" aria-hidden="true"></i> VOLTAR
						</button>
					</div>
					<div class="container-contact100-form-btn">
						<button class="contact100-form-btn" id="verifica_socio_btn" onClick="doc_cadastrado();">
							AVANÇAR
							<i class="fa fa-long-arrow-right m-l-7" aria-hidden="true"></i>
						</button>
					</div>
				</div>

				<div id="pag2" class="wrap-input100">
					<h3>INFORMAÇÕES PESSOAIS</h3><br>

					<div class="wrap-input100 validate-input bg1" data-validate="Por Favor Digite seu Nome" id="nc">
						<span class="label-input100">NOME COMPLETO *</span>
						<input class="input100" type="text" name="nome" id="nome" class="text required" placeholder="Digite seu Nome" required>
					</div>

					<div class="wrap-input100 validate-input bg1" data-validate="Por Favor Digite o Nome" id="jnome">
						<span class="label-input100">NOME *</span>
						<input class="input100" type="text" name="cnpj_nome" id="cnpj_nome" placeholder="Digite seu nome" required>
					</div>

					<div class="wrap-input100 validate-input bg1" style="height: 90px" id="nascimento">
						<span class="label-input100">DATA DE NASCIMENTO *</span><br>

						<select style="width: 30%" class="wrap-input100 validate-input bg1" name="dia" id="dia_n" onblur="valida_data(f2.dia)" class="text required">
							<option value="">Dia</option>
							<?php

							for ($i = 1; $i <= 31; $i++) {
								if ($i < 10) {
									echo ("<option value='0" . $i . "'>0" . $i . "</option>");
								} else {
									echo ("<option value='" . $i . "'>" . $i . "</option>");
								}
							}
							?>
						</select>
						<select style="width: 33%" class="wrap-input100 validate-input bg1" name="mes" id="mes" onblur="valida_data(f2.mes)">
							<option value="">Mês</option>
							<option value="01">Janeiro</option>
							<option value="02">Fevereiro</option>
							<option value="03">Março</option>
							<option value="04">Abril</option>
							<option value="05">Maio</option>
							<option value="06">Junho</option>
							<option value="07">Julho</option>
							<option value="08">Agosto</option>
							<option value="09">Setembro</option>
							<option value="10">Outubro</option>
							<option value="11">Novembro</option>
							<option value="12">Dezembro</option>
						</select>
						<select style="width: 30%" class="wrap-input100 validate-input bg1" name="ano" id="ano" onblur="valida_data(f2.ano)">
							<option value="">Ano</option>
							<?php
							for ($i = date('Y') - 10; $i >= (date('Y') - 100); $i--) {
								echo ("<option value='" . $i . "'>" . $i . "</option>");
							}
							?>
						</select>
						<span id="aviso_data" class="label-input100"></span>
					</div>

					<div class="wrap-input100 validate-input bg1" data-validate="Digite um telefone Válido">
						<span class="label-input100">TELEFONE *</span>
						<input class="input100" type="text" name="telefone" id="telefone" onblur="valida_telefone(f2.telefone)" class="text required" placeholder="(22)22222-2222" onkeypress="return Onlynumbers(event)" onkeyup="mascara('(##)#####-####',this,event)" required>
					</div>

					<div class="wrap-input100 validate-input bg1">
						<span class="label-input100">E-mail*</span>
						<input class="input100" type="text" name="email" id="email" class="text required" placeholder="Digite seu e-mail" onblur="valida_email(this.value)" required>
						<p id="avisa_email"></p>
					</div>

					<p id="avisoPf"></p>
					<p id="avisoPj"></p>

					<br>
					<div class="container-contact100-form-btn">
						<button class="contact100-form-btn" id="volta">
							<i style="margin-right: 15px; " class="fa fa-long-arrow-left m-l-7" aria-hidden="true"></i> VOLTAR
						</button>
					</div>

					<div class="container-contact100-form-btn">
						<button class="contact100-form-btn" id="avanca2">
							AVANÇAR
							<i class="fa fa-long-arrow-right m-l-7" aria-hidden="true"></i>
						</button>
					</div>

				</div>

				<div class="wrap-input100" id="pag3">
					<h3>ENDEREÇO</h3><br>
					<div class="wrap-input100 validate-input bg1" data-validate="Digite um CEP válido">
						<span class="label-input100">CEP *</span>
						<input class="input100" type="text" id="cep" name="cep" onkeypress="$(this).mask('00000-000')" onblur="valida_cep(f2.cep)" class="text required" placeholder="Digite um CEP" required>
					</div>
					<div class="wrap-input100 validate-input bg1">
						<span class="label-input100">LOGRADOURO *</span>
						<input class="input100" type="text" id="rua" name="rua" onblur="valida_endereco(f2.rua)" class="text required" placeholder="Digite um Logradouro" required>
					</div>
					<div class="wrap-input100 bg1">
						<span class="label-input100">NÚMERO *</span>
						<input class="input100" type="text" id="numero" name="numero" class="text required" placeholder="Digite o Número" required>
					</div>
					<div class="wrap-input100 bg1">
						<span class="label-input100">COMPLEMENTO </span>
						<input class="input100" type="text" id="complemento" name="complemento" placeholder="Digite o Complemento">
					</div>
					<div class="wrap-input100 validate-input bg1">
						<span class="label-input100">BAIRRO *</span>
						<input class="input100" type="text" id="bairro" name="bairro" onblur="valida_endereco(f2.bairro)" class="text required" placeholder="Digite um Bairro" required>
					</div>
					<div class="wrap-input100 validate-input bg1">
						<span class="label-input100">CIDADE *</span>
						<input class="input100" type="text" id="localidade" name="localidade" onblur="valida_endereco(f2.localidade)" class="text required" placeholder="Digite a Cidade" required>
					</div>
					<div class="wrap-input100 validate-input bg1">
						<span class="label-input100">ESTADO *</span>
						<select class="wrap-input100 validate-input bg1" id="uf" name="uf" onblur="valida_endereco(f2.estado); geraArquivo()" class="text required">
							<option value="" disabled></option>
							<option value="AC">Acre</option>
							<option value="AL">Alagoas</option>
							<option value="AP">Amapá</option>
							<option value="AM">Amazonas</option>
							<option value="BA">Bahia</option>
							<option value="CE">Ceará</option>
							<option value="DF">Distrito Federal</option>
							<option value="ES">Espírito Santo</option>
							<option value="GO">Goiás</option>
							<option value="MA">Maranhão</option>
							<option value="MT">Mato Grosso</option>
							<option value="MS">Mato Grosso do Sul</option>
							<option value="MG">Minas Gerais</option>
							<option value="PA">Pará</option>
							<option value="PB">Paraíba</option>
							<option value="PR">Paraná</option>
							<option value="PE">Pernambuco</option>
							<option value="PI">Piauí</option>
							<option value="RJ">Rio de Janeiro</option>
							<option value="RN">Rio Grande do Norte</option>
							<option value="RS">Rio Grande do Sul</option>
							<option value="RO">Rondônia</option>
							<option value="RR">Roraima</option>
							<option value="SC">Santa Catarina</option>
							<option value="SP">São Paulo</option>
							<option value="RS">Sergipe</option>
							<option value="TO">Tocantins</option>
						</select><br>
					</div>

					<p id="lista" name="lista"></p>

					<p id="aviso"></p>

					<div class="container-contact100-form-btn">
						<button class="contact100-form-btn" id="volta2">
							<i style="margin-right: 15px; " class="fa fa-long-arrow-left m-l-7" aria-hidden="true"></i>
							VOLTAR
						</button>
					</div>
					<div class="container-contact100-form-btn">
						<span class="contact100-form-btn" id="salvar_infos">
							<i style="margin-right: 15px; " class="fa fa-long-arrow m-l-7" aria-hidden="true"></i>
							SALVAR INFORMAÇÕES
						</span>
					</div>
					<div class="container-contact100-form-btn">
						<button class="contact100-form-btn" value="GERAR BOLETO" id="avanca3" onClick="setLoader(this)"><i style="margin-right: 15px; " class="fa fa-long-arrow-right m-l-7"aria-hidden="true"></i>GERAR BOLETO</button>
					</div>
				</div>
				<div class="pultima_div wrap-input100" id="form2"></div>
				<div class="ultima_div wrap-input100" id="form3"></div>

			</form>
		</div>
	</div>


	<!--===============================================================================================-->
	<!--script src="outros/vendor/daterangepicker/moment.min.js"></script>
	<script src="outros/vendor/daterangepicker/daterangepicker.js"></script-->
	<!--===============================================================================================-->
	<!--script src="outros/vendor/countdowntime/countdowntime.js"></script-->
	<!--===============================================================================================-->
	<!--script src="outros/vendor/noui/nouislider.min.js"></script-->
	<!--===============================================================================================-->
	<!--script src="outros/vendor/jquery/jquery-3.2.1.min.js"></script-->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.15/jquery.mask.min.js"></script>
	<!--===============================================================================================-->
	<!--script src="../outros/vendor/animsition/js/animsition.min.js"></script-->
	<!--===============================================================================================-->
	<script src="../outros/vendor/bootstrap/js/bootstrap.min.js"></script>
	<!--===============================================================================================-->
	<script src="../outros/vendor/select2/select2.min.js"></script>
	<script>
		$(document).ready(function() {
			$("#field").keyup(function() {
				$("#field").val(this.value.match(/[0-9]*/));
			});
		});

		$(document).ready(function() {
			$("#dcnpj").mask("99.999.999/9999-99");
		});

		$(".js-select2").each(function() {
			$(this).select2({
				minimumResultsForSearch: 20,
				dropdownParent: $(this).next('.dropDownSelect2')
			});


			$(".js-select2").each(function() {
				$(this).on('select2:close', function(e) {
					if ($(this).val() == "Please chooses") {
						$('.js-show-service').slideUp();
					} else {
						$('.js-show-service').slideUp();
						$('.js-show-service').slideDown();
					}
				});
			});
		})
	</script>
	<script>
		var filterBar = document.getElementById('filter-bar');

		noUiSlider.create(filterBar, {
			start: [1500, 3900],
			connect: true,
			range: {
				'min': 1500,
				'max': 7500
			}
		});

		var skipValues = [
			document.getElementById('value-lower'),
			document.getElementById('value-upper')
		];

		filterBar.noUiSlider.on('update', function(values, handle) {
			skipValues[handle].innerHTML = Math.round(values[handle]);
			$('.contact100-form-range-value input[name="from-value"]').val($('#value-lower').html());
			$('.contact100-form-range-value input[name="to-value"]').val($('#value-upper').html());
		});
	</script>
	<!--===============================================================================================-->
	<script src="../outros/js/main.js"></script>
	<script src="../outros/js/mascara.js"></script>

	<!-- Global site tag (gtag.js) - Google Analytics -->


	<script async src="https://www.googletagmanager.com/gtag/js?id=UA-23581568-13"></script>

	<script>
		window.dataLayer = window.dataLayer || [];

		function gtag() {
			dataLayer.push(arguments);
		}
		gtag('js', new Date());

		gtag('config', 'UA-23581568-13');
	</script>

	<script>
		$(document).ready(function() {
			transicoes();
		});
	</script>

	<script>
		$(document).ready(function() {
			$(".input-donation-method").hide();
		});

		// $("#tipo2").change(function (){
		// 	if ($(this).is(':checked')) {
		// 		$("#switch-donation-method").hide();
		// 		$(".input-donation-method").hide();
		// 		$(".input-donation-method").val("");
		// })



		// seleciona entre select ou input no valor de doacao
		$("#switch-donation-method").click(function() {
			$(".input-donation-method").show();
			$("#valores").val("");
			$("#valores").removeAttr("required");
		});

		$('#valores').change(function() {
			$(".input-donation-method").hide();
		});

		function setLoader(btn) {
			// Esconde o primeiro elemento filho (ícone)
			btn.firstElementChild.style.display = "none";

			// Remove o texto do botão sem remover os elementos filhos
			btn.childNodes.forEach(node => {
				if (node.nodeType === Node.TEXT_NODE) {
					node.textContent = '';
				}
			});

			// Adiciona o loader se não houver outros elementos filhos além do ícone
			if (btn.childElementCount == 1) {
				var loader = document.createElement("DIV");
				loader.className = "loader";
				btn.appendChild(loader);
			}
		}
	</script>


</body>

</html>