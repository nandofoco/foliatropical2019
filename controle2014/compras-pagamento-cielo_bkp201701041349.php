<?

//Incluir funções básicas
include("include/includes.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");

unset($_SESSION['roteiro-itens']);

//-----------------------------------------------------------------//
// echo var_dump($_POST);
//arquivos de layout
include("include/head.php");
include("include/header.php");

//-----------------------------------------------------------------//

$evento = (int) $_SESSION['usuario-carnaval'];
$cod = (int) $_GET['c'];

//busca paises
$sql_paises= sqlsrv_query($conexao_sankhya, "SELECT * FROM pais", $conexao_params, $conexao_options);
$paises=array();
while($linha = sqlsrv_fetch_array($sql_paises)){
	array_push($paises, $linha);
}


//AVISO DO PAGAMENTO NO CARTÃO
switch ($_SESSION['ALERT-PAGAMENTO-CARTAO'][0]) {
  case 'sucesso':
    echo '<script>'.'swal("Sucesso", "'.$_SESSION['ALERT-PAGAMENTO-CARTAO'][1].'", "success")'.'</script>';
    break;
  case 'erro':
    echo '<script>'.'swal("Erro", "'.$_SESSION['ALERT-PAGAMENTO-CARTAO'][1].'", "error")'.'</script>';
    break;
}

//-----------------------------------------------------------------//
$parcelas = array(3);
$sql_loja = sqlsrv_query($conexao, "SELECT TOP 1 l.*, (CONVERT(VARCHAR, l.LO_DATA_COMPRA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, l.LO_DATA_COMPRA, 108),1,5)) AS DATA FROM loja l WHERE l.LO_EVENTO='$evento' AND l.LO_BLOCK='0' AND l.D_E_L_E_T_='0' AND l.LO_COD='$cod'", $conexao_params, $conexao_options);

if(sqlsrv_num_rows($sql_loja) > 0) {
	$loja = sqlsrv_fetch_array($sql_loja);

	$loja_cod = $loja['LO_COD'];
	$loja_cliente = $loja['LO_CLIENTE'];
	$loja_parceiro = $loja['LO_PARCEIRO'];
	$loja_desconto = (bool) $loja['LO_DESCONTO'];

	// $loja_cliente = utf8_encode($loja['CL_NOME']);
	$sql_cliente = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 NOMEPARC, TELEFONE, EMAIL, CGC_CPF,TIPPESSOA,AD_IDENTIFICACAO,PAIS_SIGLA,DTNASC,DDD,TELEFONE FROM TGFPAR WHERE CODPARC='$loja_cliente' AND CLIENTE='S' AND BLOQUEAR='N' ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
	if(sqlsrv_num_rows($sql_cliente) > 0) $loja_cliente_ar = sqlsrv_fetch_array($sql_cliente);

	$loja_nome = utf8_encode(trim($loja_cliente_ar['NOMEPARC']));
	$loja_telefone = utf8_encode(trim($loja_cliente_ar['TELEFONE']));
	$loja_email = utf8_encode(trim($loja_cliente_ar['EMAIL']));
	$loja_cpf_cnpj = utf8_encode(trim($loja_cliente_ar['CGC_CPF']));


	//verifica se tem as informações básicas para continuar (telefone,data de nascimento, cpf/cnpj, passaporte)

	$cliente_data_nascimento = $loja_cliente_ar['DTNASC'];
	$cliente_ddd = trim($loja_cliente_ar['DDD']);
	$cliente_telefone = trim($loja_cliente_ar['TELEFONE']);
	$cliente_pessoa = utf8_encode(trim($loja_cliente_ar['TIPPESSOA']));
	$cliente_cpf_cnpj = trim($loja_cliente_ar['CGC_CPF']);
	$cliente_passaporte = trim($loja_cliente_ar['AD_IDENTIFICACAO']);
	$cliente_pais = trim($loja_cliente_ar['PAIS_SIGLA']);


	if(!empty($cliente_data_nascimento)&&!empty($cliente_ddd)&&!empty($cliente_telefone)&&($cliente_pais == 'BR'&&(!empty($cliente_cpf_cnpj)&&($cliente_pessoa=="F"&&validaCPF($cliente_cpf_cnpj))||($cliente_pessoa=="J"&&validaCNPJ($cliente_cpf_cnpj))))||($cliente_pais != 'BR' && !empty($cliente_passaporte))){
			//continuar
	}else{
		$_SESSION['ALERT'] = array('aviso','A informação do Cliente: Data de Nascimento,CPF,CNPJ,(DDD)Telefone ou Passaporte está vazia ou é inválida. Complete suas informações!');
		header("Location: ".$_SERVER['HTTP_REFERER']);
		exit();
	}



	//informação do tipo da pessoa cadastrada no banco
	$cliente_pessoa = utf8_encode(trim($loja_cliente_ar['TIPPESSOA']));
	$cliente_cpf_cnpj = preg_replace( "@[./-]@", "", trim($loja_cliente_ar['CGC_CPF']));
	$cliente_passaporte = trim($loja_cliente_ar['AD_IDENTIFICACAO']);
	$cliente_pais = $loja_cliente_ar['PAIS_SIGLA'];

	// if(!empty($cliente_passaporte)) $session_language = 'US';
	$session_language = ($cliente_pais!="BR") ? 'US' : 'BR';

	$loja_valor_total = $loja['LO_VALOR_TOTAL'];
	$loja_valor_ingressos = $loja['LO_VALOR_INGRESSOS'];
	$loja_valor_adicionais = $loja['LO_VALOR_ADICIONAIS'];
	$loja_valor_total_f = number_format($loja['LO_VALOR_TOTAL'], 2, ',','.');

	$loja_comissao_retida = (bool) $loja['LO_COMISSAO_RETIDA'];
	$loja_comissao_paga = (bool) $loja['LO_COMISSAO_PAGA'];
	$loja_vendedor = (empty($loja['LO_VENDEDOR']) || $loja['LO_VENDEDOR'] == 0) ? false : true;
	
	$loja_data = $loja['LO_DATA_COMPRA'];
	//delivery
	$del_ar = array();
	$vendas_adicionais_delivery['valor'] = '0.00';
}

//Verificar a existencia de cupom de desconto para essa compra
$sql_exist_cupom = sqlsrv_query($conexao, "SELECT TOP 1 * FROM cupom WHERE CP_COMPRA='$loja_cod' AND CP_BLOCK='0' AND D_E_L_E_T_='0' AND CP_UTILIZADO='1' ", $conexao_params, $conexao_options);
if(sqlsrv_num_rows($sql_exist_cupom) > 0) {

	$cupom_utilizado = true;
	$cupom = sqlsrv_fetch_array($sql_exist_cupom);

	$cupom_cod = $cupom['CP_COD'];
	$cupom_nome = utf8_encode($cupom['CP_NOME']);
	$cupom_codigo = $cupom['CP_CUPOM'];
	$cupom_valor = $cupom['CP_DESCONTO'];
	$cupom_tipo = $cupom['CP_TIPO'];

} else {

	//Verificar a existencia de cupom de desconto
	if($_SESSION['compra-cupom']['usuario'] == $loja_cliente) {
		
		$cupom_cod = $_SESSION['compra-cupom']['cod'];
		$cupom_delete = true;

		$sql_cupom = sqlsrv_query($conexao, "SELECT TOP 1 * FROM cupom WHERE CP_COD='$cupom_cod' AND CP_BLOCK='0' AND D_E_L_E_T_='0' AND CP_UTILIZADO='0' AND CP_DATA_VALIDADE >= GETDATE() ", $conexao_params, $conexao_options);
		$n_cupom = sqlsrv_num_rows($sql_cupom);

		if($n_cupom > 0) {

			$cupom = sqlsrv_fetch_array($sql_cupom);

			$cupom_cod = $cupom['CP_COD'];
			$cupom_nome = utf8_encode($cupom['CP_NOME']);
			$cupom_codigo = $cupom['CP_CUPOM'];
			$cupom_valor = $cupom['CP_DESCONTO'];
			$cupom_tipo = $cupom['CP_TIPO'];

			// 1 Porcentagem
			// 2 Valor

			$_SESSION['compra-cupom']['usuario'] = $loja_cliente;
			$_SESSION['compra-cupom']['cod'] = $cupom_cod;
			$_SESSION['compra-cupom']['compra'] = $cod;

			switch ($cupom_tipo) {
				case 1:
					/*$loja_valor_ingressos = $loja_valor_ingressos - (($cupom_valor * $loja_valor_ingressos) / 100);*/
					$cupom_valor_desconto = (($cupom_valor * $loja_valor_ingressos) / 100);
				break;
				
				case 2:
					if($loja_valor_ingressos >= $cupom_valor) $cupom_valor_desconto = $cupom_valor; /*$loja_valor_ingressos = $loja_valor_ingressos - $cupom_valor;*/
					else unset($_SESSION['compra-cupom'], $cupom_cod);
				break;
			}
			
			$loja_valor_total = $loja_valor_ingressos + $loja_valor_adicionais;

			//Total formatado
			$loja_valor_total_f = number_format($loja_valor_total, 2, ',','.');
		}

	}
}
?>
<section id="overlay" class="fechar-modal"><span class="loader"></span></section>
<section class="modal-box" id="modal">
	<section class="modal-dialog">
		<section class="modal-content">
			<section id="endereco-box">
				<header>
					<h1>Cadastrar endereço de cobrança</h1>
					<a href="#" class="fechar-modal">&times;</a>
				</header>
				<section id="conteudo">
					<form name="endereco" class="cadastro controle" method="post" id="cadastro-endereco" action="<? echo SITE; ?>checkout-endereco.php?t=cadastrar" data-toggle="validator" role="form">
						<input type="hidden" id="total" value="<? echo $loja_valor_total; ?>">
						<input type="hidden" name="cod" value="<? echo $cod; ?>" />
						<input type="hidden" name="cliente" value="<? echo $loja_cliente; ?>" />
						
						<p class="form-group">
							<label>Tipo de Endereço</label>
							<select name="pais" class="drop" style="width: 340px;">
							<?php foreach ($paises as $key => $pais) { ?>
								<option value="<?php echo $pais['PAIS_SIGLA'] ?>" <?php echo $pais['PAIS_SIGLA']=="BR"?"selected":"" ?>><?php echo $pais['PAIS_NOME'] ?></option>
							<? } ?>
							</select>
						</p>

						<p class="cep form-group">
							<label for="cep">CEP</label>
							<input type="text" name="cep" class="input pequeno" id="cep" value="" required>
							<a class="busca-cep" href="http://www.buscacep.correios.com.br/" target="_blank">Não sei meu CEP</a>
						</p>
						<p class="zipcode form-group" style="display: none;">
							<label for="cep">Zipcode</label>
							<input type="text" name="zipcode" class="input pequeno" id="zipzode" value="" required>
						</p>

						<div class="coluna">
							<p class="cidade form-group">
								<label for="cidade" class="control-label">Cidade</label>
								<input type="text" name="cidade" class="input" id="cidade" value="<? echo $endereco_cidade; ?>" required>
							</p>
							<p class="estado form-group">
								<label for="estado" class="control-label">Estado</label>
								<input type="text" name="estado" class="input" id="estado" value="<? echo $endereco_estado; ?>" required>
							</p>
							<div class="clear"></div>
						</div>

						<p class="form-group">
							<label for="bairro">Bairro</label>
							<input type="text" name="bairro" class="input" id="bairro" value="<? echo $endereco_bairro; ?>" required>
						</p>


						<p class="form-group">
							<label for="endereco">Endereço</label>
							<input type="text" name="endereco" class="input" id="endereco" value="<? echo $endereco_logradouro; ?>" required>
						</p>

						<p class="numero form-group">
							<label for="numero" class="control-label">Número</label>
							<input type="text" name="numero" class="input" id="numero" value="<? echo $endereco_numero; ?>" required>
						</p>
						<p class="complemento form-group">
							<label for="complemento">Complemento</label>
							<input type="text" name="complemento" class="input complemento" id="complemento" value="<? echo $endereco_complemento; ?>" />
						</p>
					
						<div class="selectbox coluna pequeno form-group" id="usuario-filial">
							<h3>Tipo de Endereço</h3>
							<a href="#" class="arrow"><strong></strong><span></span></a>
							<ul class="drop">
								<li><label class="item"><input type="radio" name="tipo_endereco" alt="Comercial" value="Comercial" required>Comercial</label></li>
								<li><label class="item"><input type="radio" name="tipo_endereco" alt="Residencial"  value="Residencial" required>Residencial</label></li>
							</ul>
							<div class="clear"></div>
						</div>						
						<footer>
							<input type="submit" class="input submit" value="Salvar endereço" />
							<a href="#" class="cancel no-cancel coluna fechar-modal">Cancelar</a>
						</footer>
						<div class="clear"></div>
					</form>		
				</section>
			</section>
		</section>
	</section>
</section>

<section id="conteudo">
	<?
	if(sqlsrv_num_rows($sql_loja) > 0) {

	?>

	<!-- <header class="titulo">
		<h1>Dados do <span>cartão</span></h1>

		<div class="valor-total">R$ <? echo $loja_valor_total_f; ?></div>
	</header> -->
	<section class="padding">		
		<section id="conteudo" class="label-top">
			<form method="POST" id="pagamento-cartao" name="pagamento-cartao" action="<?echo SITE?>cielo/pages/e-compra-cartao.php">
				<input type="hidden" id="total" value="<? echo $loja_valor_total; ?>">
				<input type="hidden" name="cod" value="<? echo $cod; ?>"/>
				<input type="hidden" name="cliente" value="<? echo $loja_cliente; ?>" />
				<section class="secao enderecos">
					<section>
						<h1>Endereço de cobrança</h1>
						<ul class="enderecos">
							<li class="novo">
								<a href="#" class="open-modal" data-width="650" data-url="<? echo SITE; ?>checkout-endereco-cadastro.php?t=cadastrar">Novo Endereço</a>
							</li>

							<?php 
							$sql_enderecos =sqlsrv_query($conexao_sankhya, "SELECT * FROM clientes_enderecos WHERE CE_CLIENTE=$loja_cliente AND CE_BLOCK='0' AND D_E_L_E_T_='0' ORDER BY CE_ULTIMA_ENTREGA DESC", $conexao_params, $conexao_options);

							if(sqlsrv_num_rows($sql_enderecos) > 0) {

								$i = 2;
								while ($endereco = sqlsrv_fetch_array($sql_enderecos)) {
									
									$endereco_cod = $endereco['CE_COD'];
									$endereco_nome_destinatario = utf8_encode($endereco['CE_NOME_DESTINATARIO']);
									$endereco_cep = $endereco['CE_CEP'];
									$endereco_logradouro = utf8_encode($endereco['CE_ENDERECO']);
									$endereco_numero = $endereco['CE_NUMERO'];
									$endereco_complemento = $endereco['CE_COMPLEMENTO'];
									$endereco_bairro = utf8_encode($endereco['CE_BAIRRO']);
									$endereco_cidade = utf8_encode($endereco['CE_CIDADE']);
									$endereco_estado = utf8_encode($endereco['CE_ESTADO']);
									$endereco_tipo_endereco = utf8_encode($endereco['CE_TIPO_ENDERECO']);
									$endereco_ponto_referencia = utf8_encode($endereco['CE_PONTO_REFERENCIA']);

									?>
									<li class="<? if($i%3 == 0) echo 'last'; ?><?php echo $_SESSION['FORM-PAGAMENTO-CARTAO']['endereco']==$endereco_cod?"checked":""; ?>">
										<a href="#" title="Alterar Endereço" class="open-modal modal editar" data-width="650" data-cod="<?php echo $endereco_cod ?>" data-cep="<?php echo $endereco_cep ?>" data-endereco="<?php echo $endereco_logradouro ?>" data-numero="<?php echo $endereco_numero ?>" data-complemento="<?php echo $endereco_complemento ?>" data-bairro="<?php echo $endereco_bairro ?>" data-cidade="<?php echo $endereco_cidade ?>" data-estado="<?php echo $endereco_estado ?>" data-tipo-endereco="<?php echo $endereco_tipo_endereco ?>" data-referencia="<?php echo $endereco_ponto_referencia ?>"></a>
										<h3><? /*echo $endereco_nome_destinatario;*/ ?></h3>
										<p><? echo $endereco_logradouro.', '.$endereco_numero; ?><br/>
										<? echo $endereco_bairro; ?><br/>
										CEP <? echo $endereco_cep; ?><br/>
										<? echo $endereco_cidade.', '.$endereco_estado; ?></p>
										<label type="button" class="utilizar"><input type="radio" name="endereco" value="<? echo $endereco_cod; ?>" <?php echo $_SESSION['FORM-PAGAMENTO-CARTAO']['endereco']==$endereco_cod?"checked":""; ?>>Utilizar este endereço</label>
									</li>
							<?}
							} ?>
						</ul>
					</section>
					<div class="clear"></div>
				</section>
				<section class="secao">	
					<input type="hidden" name="codigoBandeira" id="bandeira">
					<ul class="list-cards">
						<li><label><img id="cardvisa" alt="visa" src="<?echo SITE?>img/card-visa.png" class="cartoes opacity"> </label>	</li>
						<li><label><img id="cardamex" alt="amex" src="<?echo SITE?>img/card-amex.png" class="cartoes opacity"> </label></li>
						<li><label><img id="cardmaster" alt="master" src="<?echo SITE?>img/card-master.png" class="cartoes opacity"> </label></li>
						
						<!--<li> <label> <img id="cardhiper" alt="hiper" src="<?echo SITE?>img/card-hiper.jpg" class="cartoes opacity"> </label></li>>-->

						<li> <label> <img id="carddiscover" alt="elo" src="<?echo SITE?>img/card-discover.png" class="cartoes opacity"> </label></li>

						<li> <label> <img id="carddiners" alt="diners" src="<?echo SITE?>img/card-diners.png" class="cartoes opacity"> </label></li>

						<li><label><img id="cardelo" alt="elo" src="<?echo SITE?>img/card-elo.png" class="cartoes opacity"> </label></li>
						<!-- <li><label><img id="cardaura" alt="elo" src="<?echo SITE?>img/card-aura.png" class="cartoes opacity"> </label></li>	 -->									

						<!-- <input type="hidden" id="card" name="card" value="">
						<input type="hidden" id="total" name="total" value="<?echo $total;?>">
						<input type="hidden" id="tentarAutenticar" name="tentarAutenticar" value="nao">
						<input type="hidden" id="tipoParcelamento" name="tipoParcelamento" value="2">
						<input type="hidden" id="capturarAutomaticamente" name="capturarAutomaticamente" value="false">
						<input type="hidden" id="indicadorAutorizacao" name="indicadorAutorizacao" value="3"> -->
						
						<div class="clear"></div>
					</ul>
					<div class="metade">
						<p>
							<label id="ncard">Número do cartão</label>
							<input autocomplete="off" type="text" id="numero_cartao" name="cartaoNumero" class="input" value="<?php echo $_SESSION['FORM-PAGAMENTO-CARTAO']['cartaoNumero']; ?>">
						</p>

						
						<p class="validade">
							<label>Mês de Validade</label>
							<input autocomplete="off" type="text" id="mes_validade" name="mesValidade" maxlength=""  class="input" value="<?php echo $_SESSION['FORM-PAGAMENTO-CARTAO']['mesValidade']; ?>">
						</p>

						<p class="validade" style="margin-left: 10px;">
							<label>Ano de Validade</label>
							<input autocomplete="off" type="text" id="ano_validade" name="anoValidade" maxlength=""  class="input" value="<?php echo $_SESSION['FORM-PAGAMENTO-CARTAO']['anoValidade']; ?>">
						</p>

						<p class="codigo">
							<label>Código de Segurança</label>
							<input autocomplete="off" type="text" id="codigo_seguranca" name="cartaoCodigoSeguranca" maxlength="4" class="input min" value="<?php echo $_SESSION['FORM-PAGAMENTO-CARTAO']['cartaoCodigoSeguranca']; ?>">
							<img class="imgcodseg" src="<?echo SITE?>img/numseg.png" >
						</p>
						
						<p>
							<label>Nome do Titular</label>
							<input autocomplete="off" type="text" id="nome_titular" name="nomeTitular" class="input" value="<?php echo $_SESSION['FORM-PAGAMENTO-CARTAO']['nomeTitular']; ?>">
						</p>
					</div>
					<div class="metade">
						<section class="selectbox coluna">
							<h3>Documento do titular</h3>
							<a href="#" class="arrow"><strong>
								<?php 
								switch ($_SESSION['FORM-PAGAMENTO-CARTAO']['documento']) {
									case 'cpf':
										echo "CPF";
										break;
									case 'cnpj':
										echo "CNPJ";
										break;
									case 'passaporte':
										echo "Passaporte";
										break;
									default:
										echo "";
										break;
								}
								?>
							</strong><span></span></a>
							<ul class="drop">
							<?php if($cliente_pais=="BR"){ ?>
								<li><label class="item "><input type="radio" name="documento" value="cpf" alt="CPF">CPF</label></li>
								<li><label class="item "><input type="radio" name="documento" value="cnpj" alt="CNPJ">CNPJ</label></li>
							<?php }else{ ?>
								<li><label class="item"><input type="radio" name="documento" value="passaporte" alt="Passaporte">Passaporte</label></li>
							<?php } ?>
							</ul>
						</section>
						<p>
							<label>Número do documento</label>
							<input autocomplete="off" type="text" id="cpfcnpj" name="cpfcnpj" class="input" value="<?php 
							if(empty($_SESSION['FORM-PAGAMENTO-CARTAO']['cpfcnpj'])){
								if($cliente_pais=="BR"){
									echo $cliente_cpf_cnpj;
								}else{
									echo $cliente_passaporte;
								}
							}else{
								$_SESSION['FORM-PAGAMENTO-CARTAO']['cpfcnpj'];
							} ?>">
						</p>
						<section class="selectbox coluna parcelas">
							<h3>Número de parcelas</h3>
							<a href="#" class="arrow"><strong>Informe o número do cartão</strong><span></span></a>
							<ul class="drop">
								<li><label class="item"><input type="radio" name="formaPagamento" value="" alt="Informe o número do cartão"/>Informe o número do cartão</label></li>
								<? /* for ($i=1; $i<=6 ; $i++) { ?>
									<li>
										<label class="item"><input type="radio" name="formaPagamento" value="<? echo $i; ?>" alt="<? echo $i; ?>" <?php echo $i==$_SESSION['FORM-PAGAMENTO-CARTAO']['formaPagamento']?"checked":"";?> /><? echo $i; ?>
										</label>
									</li>
								<? }*/ ?>
							</ul>
						</section>
						<!-- <section class="selectbox coluna endereco">
							<h3>Endereço de Cobrança</h3>
							<a href="#" class="arrow"><strong></strong><span></span></a>
							<ul class="drop">
								<?//busca enderecos desse cliente
								$sql_enderecos =sqlsrv_query($conexao_sankhya, "SELECT * FROM clientes_enderecos WHERE CE_CLIENTE=$loja_cliente AND CE_BLOCK='0' AND D_E_L_E_T_='0' ORDER BY CE_ULTIMA_ENTREGA DESC", $conexao_params, $conexao_options);

								if(sqlsrv_num_rows($sql_enderecos) > 0) {

									$i = 2;
									while ($endereco = sqlsrv_fetch_array($sql_enderecos)) {
										
										$endereco_cod = $endereco['CE_COD'];
										$endereco_nome_destinatario = utf8_encode($endereco['CE_NOME_DESTINATARIO']);
										$endereco_cep = $endereco['CE_CEP'];
										$endereco_logradouro = utf8_encode($endereco['CE_ENDERECO']);
										$endereco_numero = $endereco['CE_NUMERO'];
										$endereco_complemento = $endereco['CE_COMPLEMENTO'];
										$endereco_bairro = utf8_encode($endereco['CE_BAIRRO']);
										$endereco_cidade = utf8_encode($endereco['CE_CIDADE']);
										$endereco_estado = utf8_encode($endereco['CE_ESTADO']);
										$endereco_tipo_endereco = utf8_encode($endereco['CE_TIPO_ENDERECO']);
										$endereco_ponto_referencia = utf8_encode($endereco['CE_PONTO_REFERENCIA']);

										?>
										<li>
											<label class="item"><input type="radio" name="endereco-cobranca" value="<?echo $endereco_cod?>"/><?echo $endereco_nome_destinatario?> - <?echo $endereco_logradouro?>, <?echo $endereco_numero?> <?if(!empty($endereco_complemento)){echo $endereco_complemento;}?> <?echo $endereco_cidade?>
											</label>
										</li>
									<?}
								}else {	?>
									<li>
										<label class="item"><input type="radio" name="" value=""/>Não há endereços cadastrados
										</label>
									</li>
								<?php } ?>		
							</ul>
						</section> -->
						<!-- <input type="button" class="open-modal submit adicionar" value="+" autocomplete="off"> -->
					</div>
					<section id="compra-pagamento">
						<!-- CIELO -->

							<input type="submit" class="submit cielo" value="Finalizar"/>

					    <div class="clear"></div>
					</section>
					<div class="clear"></div>
				</section>
			</form>
			<div class="clear"></div>
		</section>
		
	</section>
	<?
	}
	?>	
</section>
<?

//-----------------------------------------------------------------//

include('include/footer.php');

?>
<script>
	var site = $("#base-site").val();
	
	$('select[name="pais"]').select2();

	$('form input[name="cep"]:text').mask('99999-999');
    $(document).on('click','.open-modal',function(){
        $("body").addClass("modal-open");
        $("#modal header h1").html("Cadastrar endereço de combrança");
        $("#overlay,#modal").fadeIn("fast");
        $('form#cadastro-endereco')[0].reset();
        $('#modal #endereco-box').find('select[name="pais"]').val('BR').trigger('change');
        $('#modal #endereco-box').find('form').attr('action',site+'checkout-endereco.php?t=cadastrar');
    });
    $(document).on('click', 'a.fechar-modal', function(){
        $("body").removeClass('modal-open');
        $("#overlay").fadeOut("fast");
        $("#modal").fadeOut("fast");
        return false;
    });
    $(document).on('click','#modal .modal-dialog',function(event){
       //event.stopPropagation();
    });
    $(document).on('click', '#modal', function(){
        //$("body").removeClass('modal-open');
        //$("#overlay").fadeOut("fast");
        //$("#modal").fadeOut("fast");
        //return false;
    });
    $(document).on('click','.open-modal.editar',function(){
        //preencher o formulário
        $form=$(this);
        $("#modal header h1").html("Alterar endereço de combrança");
        $('#modal #endereco-box').find('input[name="cod"]').val($form.data('cod')).blur();
        $('#modal #endereco-box').find('select[name="pais"]').val($form.data('pais')).trigger('change');
        $('#modal #endereco-box').find('input[name="zipcode"]').val($form.data('cep'))
        if($form.data('pais')!="BR"){
        	$('#modal #endereco-box').find('input[name="cep"]').val('');
        	$('#modal #endereco-box').find('input[name="zipcode"]').val($form.data('cep'));
        }else{
        	$('#modal #endereco-box').find('input[name="cep"]').val($form.data('cep'));
        	$('#modal #endereco-box').find('input[name="zipcode"]').val('')
        }
        
        $('#modal #endereco-box').find('input[name="endereco"]').val($form.data('endereco')).blur();
        $('#modal #endereco-box').find('input[name="numero"]').val($form.data('numero')).blur();
        $('#modal #endereco-box').find('input[name="complemento"]').val($form.data('complemento')).blur();
        $('#modal #endereco-box').find('input[name="bairro"]').val($form.data('bairro')).blur();
        $('#modal #endereco-box').find('input[name="cidade"]').val($form.data('cidade')).blur();
        $('#modal #endereco-box').find('input[name="estado"]').val($form.data('estado')).blur();
        $('#modal #endereco-box').find('input[name="tipo_endereco"][value="'+$form.data('tipo-endereco')+'"]').trigger('click');
        $('#modal #endereco-box').find('form').attr('action',site+'checkout-endereco.php?t=editar');
    });


    //--------------controle dos enderecos-------------------------------------------
    $('section.enderecos ul.enderecos').on('change','li label.utilizar input[name="endereco"]',function(event) {
        $('section.enderecos ul.enderecos').find("li.checked").removeClass("checked");
        $('section.enderecos ul.enderecos').find("li").each(function(){
            if($(this).find("input[type='radio']").is(":checked")) $(this).addClass("checked");
        });
    });
    // $('form#cadastro-endereco').submit(function(event) {
    //     var $form=$(this);
    //     if(!$form.hasClass('return-false')){
    //         $.ajax({
    //             url : $form.attr('action'),
    //             method : 'POST',
    //             data : $form.serialize(),
    //             success : function(resposta){
    //                 //resposta=JSON.parse(resposta);
    //                 if(resposta.sucesso){
    //                     // alert();
    //                     $form[0].reset();
    //                    //$('a.fechar-modal').trigger('click');
    //                     getEnderecos($form.find('input[name="cliente"]').val());
    //                     $('a.fechar-modal').trigger('click');
    //                 }
    //             }
    //         });
    //     }

    //     return false;
    // });
    //Criando os requires
    // $('form#cadastro-endereco').validation({
    //     rules: {
    //         cep: { tipo: 'cep' },
    //         endereco: { required: true },
    //         numero: { required: true },
    //         complemento: { required: false },
    //         bairro: { required: true },
    //         cidade: { required: true },
    //         estado: { required: true },
    //         tipo_endereco: { required: true },
    //         referencia: { required: false }
    //     }
    // });
    $("#ano_validade").mask("9999");
    $("#mes_validade").mask("99");
    $("#codigo_seguranca").mask("9?999");
    
    $('form#pagamento-cartao').validation({
        rules: {
            numero_cartao: { required: true},
            ano_validade: { required: true },
            mes_validade: { required: true },
            codigo_seguranca: { required: true },
            cpfcnpj: { required: true },
            parcelas: { required: true },
            endereco: { required: true },
            documento: { required: true}
        }
    });
    var $cadastro = $("form");

    $cadastro.find("input[name='cep']").blur(function(){
        
        var site = $("#base-site").val();
        
        // Pegamos o valor do input CEP
        var cep = $cadastro.find("input[name='cep']").val();
        
        // Se o CEP nÃ£o estiver em branco
        if(cep != '') {

            // Adiciona imagem de "Loading"
            $cadastro.find(".endereco input[name='cep']").addClass('loading');
            
            $.getJSON(site + "include/busca-cep.php", {
                cep: cep
            }, function(resultado) {                
                $cadastro.find(".endereco input[name='cep']").removeClass('loading');

                //Valores
                $cadastro.find("input[name='endereco']").val(resultado.logradouro).blur();
                $cadastro.find("input[name='bairro']").val(resultado.bairro).blur();
                $cadastro.find("input[name='cidade']").val(resultado.cidade).blur();

                if($cadastro.find("input[name='estado']").data('uf') != null) $cadastro.find("input[name='estado'][data-uf='"+resultado.uf+"']").trigger('click');
                else $cadastro.find("input[name='estado']").radioSel(resultado.uf);

                //alteração para o cadastro de enderecos do pagamento
                $cadastro.find("input[id='estado']").val(resultado.uf);

                $cadastro.find("input[name='numero']").focus();
            });
        } else {
            // Se o campo CEP estiver em branco, apresenta mensagem de erro
            // alert('Para que o endereÃ§o seja completado automaticamente vocÃª deve preencher o campo CEP!');
        }
        return false;
    });
    $('#numero_cartao').keyup(function(){
        var value = $(this).val();
        getCreditCardLabel(value);
    });
    if($('#numero_cartao').val()!=""){
        $('#numero_cartao').trigger('keyup');
    }
        //-------------------------------------------------------------------//

    function getCreditCardLabel(cardNumber){

	    var site = $("#base-site").val();

	    // $.ajax({
	    //   url : site + 'verifycard.php',
	    //   method : 'POST',
	    //   data : { card : cardNumber },
	    //   success : function(html){
	    var bandeira='';
	    switch(true){
	        case (/^(636368|438935|504175|451416|636297)/).test(cardNumber) :
	            bandeira = 'elo';  
	        break;
	     
	        case (/^(606282)/).test(cardNumber) :
	        bandeira = 'hipercard';    
	        break;
	     
	        case (/^(5067|5090|4576|4011)/).test(cardNumber) :
	        bandeira = 'elo';  
	        break;
	     
	        case (/^(3841)/).test(cardNumber) :
	        bandeira = 'hipercard';    
	        break;
	     
	        case (/^(6011)/).test(cardNumber) :
	        bandeira = 'discover'; 
	        break;
	     
	        case (/^(622)/).test(cardNumber) :
	        bandeira = 'discover'; 
	        break;
	     
	        case (/^(301|305)/).test(cardNumber) :
	        bandeira = 'diners';   
	        break;
	     
	        case (/^(34|37)/).test(cardNumber) :
	        bandeira = 'amex'; 
	        break;
	     
	        case (/^(36|38)/).test(cardNumber) :
	        bandeira = 'diners';   
	        break;
	     
	        case (/^(64|65)/).test(cardNumber) :
	        bandeira = 'discover'; 
	        break;
	     
	        case (/^(50)/).test(cardNumber) :
	        bandeira = 'aura'; 
	        break;
	     
	        case (/^(35)/).test(cardNumber) :
	        bandeira = 'jcb';  
	        break;
	     
	        case (/^(60)/).test(cardNumber) :
	        bandeira = 'hipercard';    
	        break;
	     
	        case (/^(4)/).test(cardNumber) :
	        bandeira = 'visa'; 
	        break;
	     
	        case (/^(5)/).test(cardNumber) :
	        bandeira = 'mastercard';   
	        break;
	    }
	        // console.log(bandeira);
	        // if(html!=''){

	            $('#ncard').html('Número do cartão');
	            var verifytotal = $('#total').val();
	            var options = '';

	            if( (bandeira=='mastercard') || (bandeira=='diners') || (bandeira=='visa') || (bandeira=='elo') ){
	                
	                for(i=1;i<=6;i++){
	                    options = options + '<li><label class="item"><input type="radio" name="formaPagamento" value="'+i+'" alt="'+i+'"/>'+i+'x de R$ '+ parseFloat( (verifytotal / i) ).toFixed(2).replace('.',',') +'</label></li>';
	                }

	            }
	            if( (bandeira=='amex') || (bandeira=='aura')){
	                
	                for(i=1;i<=3;i++){
	                    options = options + '<li><label class="item"><input type="radio" name="formaPagamento" value="'+i+'" alt="'+i+'"/>'+i+'x de R$ '+ parseFloat( (verifytotal / i) ).toFixed(2).replace('.',',') +'</label></li>';
	                }

	            }
	            if(bandeira=='discover'){
	                for(i=1;i<=1;i++){
	                    options = options + '<li><label class="item"><input type="radio" name="formaPagamento" value="'+i+'" alt="'+i+'"/>'+i+'x de R$ '+ parseFloat( (verifytotal / i) ).toFixed(2).replace('.',',') +'</label></li>';
	                }  
	            }

	            $('.selectbox.parcelas').find('strong').html('');
	            $('.selectbox.parcelas').find('.drop').html(options);
	            // $('#pnparcelas').html('<label>Número de parcelas</label><select id="formaPagamento" name="formaPagamento">'+options+'</select>');
	            
	            /*if( (bandeira=='discover') || (bandeira=='hiper') || (bandeira=='diners')  ){
	                $('#numero-cartao').val('');
	                $('#pnparcelas').html('<label>Número de parcelas</label><select id="formaPagamento" name="formaPagamento"><option value="">Informe o número do cartão</option></select>');
	            }*/

	            if(bandeira=='elo'){
	               $('#bandeira').val('elo');
	               $('img.cartoes').removeClass('opacity');
	               $('img.cartoes').addClass('opacity');
	               $('img#cardelo').removeClass('opacity');
	               //altera a imagem
	               $('.imgcodseg').attr('src',site+'img/numseg.png');
	               return 'elo';
	              }

	              if(bandeira=='visa'){
	               $('#bandeira').val('visa');
	               $('img.cartoes').removeClass('opacity');
	               $('img.cartoes').addClass('opacity');
	               $('img#cardvisa').removeClass('opacity');
	               //altera a imagem
	               $('.imgcodseg').attr('src',site+'img/numseg.png');
	               return 'visa';
	              }

	              if(bandeira=='aura'){
	               $('#bandeira').val('aura');
	               $('img.cartoes').removeClass('opacity');
	               $('img.cartoes').addClass('opacity');
	               $('img#cardaura').removeClass('opacity');
	               //altera a imagem
	               $('.imgcodseg').attr('src',site+'img/numseg.png');
	               return 'aura';
	              }

	              if(bandeira=='mastercard'){
	                $('#bandeira').val('mastercard');
	               $('img.cartoes').removeClass('opacity');
	               $('img.cartoes').addClass('opacity');
	               $('img#cardmaster').removeClass('opacity');
	               //altera a imagem
	               $('.imgcodseg').attr('src',site+'img/numseg.png');  
	               return 'mastercard';
	              }
	              if(bandeira=='amex'){
		               $('#bandeira').val('amex');
		               $('img.cartoes').removeClass('opacity');
		               $('img.cartoes').addClass('opacity');
		               $('img#cardamex').removeClass('opacity');
		               //altera a imagem
		               $('.imgcodseg').attr('src',site+'img/numseg2.png');
	               return 'amex';
	              }
	              if(bandeira=='diners'){
		               $('#bandeira').val('diners');
		               $('img.cartoes').removeClass('opacity');
		               $('img.cartoes').addClass('opacity');
		               $('img#carddiners').removeClass('opacity');
		               //altera a imagem
		               $('.imgcodseg').attr('src',site+'img/numseg.png');
	               	return 'diners';
	              }
	              if(bandeira=='discover'){
		               $('#bandeira').val('discover');
		               $('img.cartoes').removeClass('opacity');
		               $('img.cartoes').addClass('opacity');
		               $('img#carddiscover').removeClass('opacity');
		               //altera a imagem
		               $('.imgcodseg').attr('src',site+'img/numseg.png');
	               	return 'diners';
	              }
	              $('#ncard').html('Número do cartão <span style="color:red">(Bandeira inválida)</span>');
	              $('.selectbox.parcelas').find('strong').html('Informe o número do cartão');
	              $('.selectbox.parcelas').find('.drop').html('<li><label class="item"><input type="radio" name="formaPagamento" value="" alt="Informe o número do cartão"/>Informe o número do cartão</label></li>');
	              $('img.cartoes').removeClass('opacity');
	              $('img.cartoes').addClass('opacity');
	              $('#bandeira').val('');
	              return '';
		        // }

		  //   }
		  // });
		  
		return false;
	}
	//pagamento cartao
    $('form#pagamento-cartao').submit(function(event) {
        var $form=$(this);
        if(!$form.hasClass('return-false')){
        }else{
            if($('form#pagamento-cartao').find('input[name="endereco"]:checked').length!=1){
                swal("Aviso", "Selecione o endereço de pagamento!", "warning");
            }
            return false;
        }
    });
</script>

<?

//limpar sessao com aviso e formulario preenchido
unset($_SESSION['ALERT-PAGAMENTO-CARTAO'],$_SESSION['FORM-PAGAMENTO-CARTAO']);

//Fechar conexoes
include("conn/close.php");
include("conn/close-sankhya.php");

?>