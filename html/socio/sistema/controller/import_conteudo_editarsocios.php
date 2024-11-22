<section class="body">

		<!-- start: header -->
		<header id="header" class="header">
			
		<!-- end: search & user box -->
		</header>
		<!-- end: header -->
		<div class="inner-wrapper">
			<!-- start: sidebar -->
			<aside id="sidebar-left" class="sidebar-left menuu"></aside>
			<!-- end: sidebar -->

			<section role="main" class="content-body">
				<header class="page-header">
					<h2>Sócios</h2>
					
					<div class="right-wrapper pull-right">
						<ol class="breadcrumbs">
							<li>
								<a href="home.php">
									<i class="fa fa-home"></i>
								</a>
							</li>
							<li><span>Páginas</span></li>
							<li><span>Sócios</span></li>
						</ol>
					
						<a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
					</div>
				</header>

				<!-- start: page -->
				<div class="row">
        <div class="box box-info box-solid socioModal">
            <div class="box-header">
              <h3 class="box-title"><i class="fa fa-user-plus"></i> Editar sócio</h3>
            </div>
            <div class="box-body">
            <form id="frm_editar_socio" method="POST">
            <input type="hidden" id="id_socio" name="id_socio" value="<?php echo($_GET['socio']); ?>">
            <?php
        $id_socio = $_GET['socio'];
        $resultado = mysqli_query($conexao, "SELECT * FROM socio AS s LEFT JOIN pessoa AS p ON s.id_pessoa = p.id_pessoa LEFT JOIN socio_tipo st ON st.id_sociotipo = s.id_sociotipo WHERE id_socio = $id_socio");
        $registro = mysqli_fetch_array($resultado);
        $nome_socio = $registro['nome'];
        $email = $registro['email'];
        $telefone = $registro['telefone'];
        $status = $registro['id_sociostatus'];
        $data_nasc = $registro['data_nascimento'];
        $cpf_cnpj = $registro['cpf'];
        $logradouro = $registro['logradouro'];
        $numero = $registro['numero_endereco'];
        if($registro['id_sociotag'] == null || $registro['id_sociotag'] == ""){
            $tag = "";
        }else $tag = $registro['id_sociotag'];
        $complemento = $registro['complemento'];
        $cep = $registro['cep'];
        $socio_tipo = $registro['id_sociotipo'];
        $socio_tipo_str = $registro['tipo'];
        $bairro = $registro['bairro'];
        $cidade = $registro['cidade'];
        $estado = $registro['estado'];

        $data_referencia = $registro['data_referencia'];
        $valor_periodo = $registro['valor_periodo'];
    ?>
        <div class="row">
        <div class="form-group mb-2 col-xs-5">
                  <label for="nome_cliente">Nome sócio</label>
                  <input type="text" class="form-control" id="socio_nome" name="socio_nome" value="<?php echo($nome_socio); ?>" placeholder="" required>
              </div>
        <div class="form-group col-xs-3">
          <label for="pessoa">Pessoa</label>
          <select class="form-control" name="pessoa" id="pessoa">
          <?php
                if(strpos($socio_tipo_str, "Física") !== false){
                  $pessoa = "fisica";
                }else $pessoa = "juridica";
                if($pessoa == "fisica"){
          ?>
                    <option value="fisica" selected>Física</option>
                    <option value="juridica">Jurídica</option>
          <?php
                }else{
          ?>
                <option value="fisica">Física</option>
                <option value="juridica" selected>Jurídica</option>
          <?php
                }
          ?>
          </select>
        </div>
        <div class="form-group col-xs-4 cpf_div">
        <div class="form-check">
    <input type="checkbox" class="form-check-input" id="check_veri_cpf" checked>
    <label class="form-check-label" for="exampleCheck1">Deslig. Verif. Cpf</label>
  </div>
          <label id="label_cpf_cnpj" for="valor">CPF</label>
          <input type="text"  class="form-control" value="<?php echo($cpf_cnpj); ?>" id="cpf_cnpj" name="cpf" required>
        </div>
        </div>
        <div class="row">
        <div class="form-group col-xs-6">
          <label for="obs">E-mail</label>
          <input type="email" class="form-control" id="email" value="<?php echo($email); ?>" name="email" placeholder="">
        </div>
        <div class="form-group col-xs-6">
          <label for="valor">Telefone</label>
          <input type="tel" min="0"  class="form-control" id="telefone" value="<?php echo($telefone); ?>" name="telefone">
        </div>
        </div>
       
         <div class="row">
        <div class="form-group col-xs-4">
          <label for="pessoa">Contribuinte</label>
          <select class="form-control" name="contribuinte" id="contribuinte">
                    <option value="mensal">Mensal</option>
                    <option value="bimestral">Bimestral</option>
                    <option value="trimestral">Trimestral</option>
                    <option value="semestral">Semestral</option>
                    <option value="casual">Casual (avulso)</option>
                    <option value="si">Sem informação</option>
          </select>
        </div>
        <div class="form-group col-xs-4">
          <label for="pessoa">Status</label>
          <select class="form-control" name="status" id="status">
                    <option value="0">Ativo</option>
                    <option value="1">Inativo</option>
                    <option value="2">Inadimplente</option>
                    <option value="3">Inativo temporariamente</option>
                    <option value="4">Sem informação</option>
          </select>
        </div>
        <div class="div_nasc">
        <?php
              if($pessoa == "fisica"){
        ?>
          
          <div class="form-group col-xs-4">
            <label for="valor">Data de nascimento</label>
            <input type="date" class="form-control" id="data_nasc" value="<?php echo($data_nasc); ?>" name="data_nasc">
          </div>
        
          <?php
              }
        ?>
          </div>
        </div>
        <div class="row">
        <div class="form-group col-xs-6">
            <label for="valor">Data referência (ínicio contribuição)</label>
            <input type="date" class="form-control" id="data_referencia" name="data_referencia" value="<?php echo($data_referencia); ?>">
          </div>
          <div class="form-group col-xs-6">
            <label for="valor">Valor/período em R$</label>
            <input type="number" class="form-control" id="valor_periodo" name="valor_periodo" value="<?php echo($valor_periodo); ?>">
          </div>
        </div>
        <div class="row">
        <div class="form-group col-xs-12">
            <label for="valor">Tipo de contribuição</label>
            <select class="form-control" name="tipo_contribuicao" id="tipo_contribuicao">
                    <option value="1">Boleto</option>
                    <option value="2">Cartão de crédito</option>
                    <option value="3">Outros</option>
          </select>
          </div>
        </div>
        <div class="row">
          <div style="margin-bottom:  1em" class="form-group col-xs-12 mb-2">
            <label for="valor">Grupo</label>
            <a onclick="adicionar_tag()">
							<i class="fas fa-plus w3-xlarge" style="margin-top: 0.75vw"></i>
						</a>
            <select class="form-control" name="tags" id="tags">
            <option value="none">Selecionar Grupo</option>
            <?php
                  $tags = mysqli_query($conexao, "SELECT * FROM socio_tag");
                  while($row = $tags->fetch_array(MYSQLI_NUM))
                  {
                      if($row[0] == $tag){
                        echo("<option value=".$row[0]." selected>".$row[1]."</option>");
                      }else echo ("<option value=".$row[0].">".$row[1]."</option>");
                  }
           
            ?>
            </select>
          </div>
        </div>
        
        <div class="box box-info endereco">
            <div class="box-header with-border">
              <h3 class="box-title">Endereço</h3>
            </div>
            <div class="box-body">
            <div class="row">
            <div class="form-group mb-2 col-xs-6">
              <label for="cep">CEP</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-search"></i></span>
                <input type="text" id="cep" class="form-control" value="<?php echo($cep); ?>" placeholder="">
              </div>
              <div class="status_cep col-xs-12"></div>
              </div>
            </div>
            <div class="row">
              <div class="form-group mb-2 col-xs-8">
                        <label for="nome_cliente">Rua</label>
                        <input type="text" class="form-control" id="rua" name="nome"value="<?php echo($logradouro); ?>" placeholder="">
                    </div>
              <div class="form-group col-xs-4">
                <label for="data_corte">Número</label>
                <input type="text" class="form-control" id="numero" name="numero" value="<?php echo($numero); ?>" placeholder="">
              </div>
            </div>
            <div class="row">
            <div class="form-group mb-2 col-xs-6">
                        <label for="nome_cliente">Complemento</label>
                        <input type="text" class="form-control" id="complemento" name="complemento" value="<?php echo($complemento); ?>" placeholder="">
                    </div>
              <div class="form-group col-xs-6">
                <label for="data_corte">Bairro</label>
                <input type="text" class="form-control" id="bairro" name="bairro" value="<?php echo($bairro); ?>" placeholder="">
              </div>
            </div>
            <div class="row">
            <div class="form-group mb-2 col-xs-6">
                        <label for="nome_cliente">Estado</label>
                        <input type="text" class="form-control" id="estado" name="estado" value="<?php echo($estado); ?>" placeholder="">
                    </div>
              <div class="form-group col-xs-6">
                <label for="data_corte">Cidade</label>
                <input type="text" class="form-control" id="cidade" name="cidade" value="<?php echo($cidade); ?>" placeholder="">
              </div>
            </div>
            <div class="pull-right">
            <a href="./" id="btn_reset" type="reset" class="btn btn-danger">Cancelar</a>
        <button type="submit" class="btn btn-primary btn_salvar_socio">Salvar sócio</button>
            </div>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>  
      </div>
			<!-- end: page -->
			</section>
		</div>	
	</section>
</body>
<script>
        var sociotipo = <?php echo($socio_tipo); ?>;
        var status = <?php echo($status); ?>;
    
        var tag = <?php if($tag != "" && $tag != null) echo($tag); else echo("''");?>;
        $("#tags").val(tag);

        $("#status").val(status);
        if(status == 4){
          $("#contribuinte").val("si");
        }

        switch(sociotipo){
          case 0: case 1: case 20: case 21: case 40: case 41: 
              $("#contribuinte").val("casual");
              break;
          case 2: case 3: case 22: case 23: case 42: case 43:
              $("#contribuinte").val("mensal");
              break;
          case 6: case 7: case 24: case 25: case 44: case 45:
              $("#contribuinte").val("bimestral");
              break;
          case 8: case 9: case 26: case 27: case 46: case 47:
              $("#contribuinte").val("trimestral");
              break;
          case 10: case 11: case 28: case 29: case 48: case 49:
              $("#contribuinte").val("semestral");
              break;
          default:
              $("#contribuinte").val("si");
              break;
        }

        if(sociotipo >= 0  && sociotipo <= 13){
          console.log("boleto");
        }else if(sociotipo >= 10 && sociotipo <= 31){
          $("#tipo_contribuicao").val("2");
          console.log("cartão");
        }else{
          $("#tipo_contribuicao").val("3");
          console.log("outro");
        }
</script>
