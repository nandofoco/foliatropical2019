<?

//Verificamos o dominio
include("include/includes.php");

//Conexão com o banco de dados do sqlserver
include("conn/conn-mssql.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");

//Definir o carnaval ativo
include("include/setcarnaval.php");

DEFINE('PGNEUTRA', true);

unset($_SESSION['roteiro-itens']);

//-----------------------------------------------------------------//

if(!checklogado()){
?>
<script type="text/javascript">
	location.href='<? echo SITE.$link_lang; ?>';
</script>
<?
	exit();
}

//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");
include("include/header-geral.php");

//-----------------------------------------------------------------//

$evento = setcarnaval();
$cod = (int) $_GET['c'];
$transfer = (bool) $_GET['transfer'];

$usuario_cod = $_SESSION['usuario-cod'];

$parcelas = array(3);

//-----------------------------------------------------------------//

$sql_loja = sqlsrv_query($conexao, "SELECT TOP 1 l.*, (CONVERT(VARCHAR, l.LO_DATA_COMPRA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, l.LO_DATA_COMPRA, 108),1,5)) AS DATA FROM loja l WHERE l.LO_EVENTO='$evento' AND l.LO_CLIENTE='$usuario_cod' AND l.LO_BLOCK='0' AND l.D_E_L_E_T_='0' AND l.LO_COD='$cod'", $conexao_params, $conexao_options);

?>
<section id="conteudo">
	<div id="breadcrumb" itemprop="breadcrumb"> 
		<a href="<? echo SITE.$link_lang; ?>"><? echo $lg['menu_inicio']; ?></a> &rsaquo; 
		<a href="<? echo SITE.$link_lang; ?>minhas-compras/"><? echo $lg['menu_minhas_compras']; ?></a> &rsaquo; 
		<a href="<? echo SITE.$link_lang; ?>minhas-compras/detalhes/<? echo $cod; ?>/"><? echo $lg['minhas_compras_detalhes']; ?></a> &rsaquo; 
		<? echo $lg['minhas_compras_pagamento']; ?>
	</div>
	<section id="compre-aqui">

	<?
	if(sqlsrv_num_rows($sql_loja) > 0) {

		$loja = sqlsrv_fetch_array($sql_loja);

		$loja_cod = $loja['LO_COD'];
		$loja_cliente = $loja['LO_CLIENTE'];
		$loja_desconto = (bool) $loja['LO_DESCONTO'];

		// $loja_cliente = utf8_encode($loja['CL_NOME']);
		$sql_cliente = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 NOMEPARC, TELEFONE, EMAIL, CGC_CPF FROM TGFPAR WHERE CODPARC='$loja_cliente' AND CLIENTE='S' AND BLOQUEAR='N' ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_cliente) > 0) $loja_cliente_ar = sqlsrv_fetch_array($sql_cliente);

		$loja_nome = utf8_encode(trim($loja_cliente_ar['NOMEPARC']));
		$loja_telefone = utf8_encode(trim($loja_cliente_ar['TELEFONE']));
		$loja_email = utf8_encode(trim($loja_cliente_ar['EMAIL']));
		$loja_cpf_cnpj = utf8_encode(trim($loja_cliente_ar['CGC_CPF']));

		$loja_valor_total = $loja['LO_VALOR_TOTAL'];
		$loja_valor_ingressos = $loja['LO_VALOR_INGRESSOS'];
		$loja_valor_adicionais = $loja['LO_VALOR_ADICIONAIS'];

		$loja_comissao_retida = (bool) $loja['LO_COMISSAO_RETIDA'];
		$loja_comissao_paga = (bool) $loja['LO_COMISSAO_PAGA'];
		$loja_vendedor = (empty($loja['LO_VENDEDOR']) || $loja['LO_VENDEDOR'] == 0) ? false : true;

		//Total formatado
		$loja_valor_total_f = number_format($loja_valor_total, 2, ',','.');

		//Data da compra
		$loja_data = $loja['LO_DATA_COMPRA'];
		$anterior = (strtotime($loja_data->format('Y-m-d')) < strtotime('2015-10-15')) ? true : false;
		$loja_desconto_folia = ($anterior) ? 1 : (bool) $loja['LO_DESCONTO_FOLIA'];
		$loja_desconto_frisa = ($anterior) ? 1 : (bool) $loja['LO_DESCONTO_FRISA'];
		
		?>
		<header class="titulo">
			<h1><? echo $lg['minhas_compras_pagamento']; ?></h1>
			<div class="valor-total">R$ <? echo $loja_valor_total_f; ?></div>
		</header>
		<section class="padding">
		
			<section class="secao relative" id="compra-dados">
				<div>
					<aside><? echo $loja_cod; ?></aside>
					<section>
						<h3><? echo $loja_nome; ?></h3>
						<p><? echo $loja_email; ?></p>
						<p><? echo formatTelefone($loja_telefone); ?></p>
					</section>

					<div class="clear"></div>
				</div>
				<div class="informacoes-compra">
					<? /*<a href="<? echo SITE.$link_lang; ?>minhas-compras/excluir/<? echo $loja_cod; ?>/" class="button cancelar-compra coluna confirm" title="Deseja realmente cancelar a compra?">Cancelar compra</a>*/ ?>
				</div>
			</section>		
				
			<section id="financeiro-lista" class="secao label-top">
				<table class="lista">
					<thead>
						<tr>
							<th class="first"><strong>VCH</strong></th>
							<th><strong>Cliente</strong></th>
							<th><strong>Tipo</strong></th>
							<th><strong>Dia</strong></th>
							<th><strong>Setor</strong></th>
							<th class="right"><strong>Valor (R$)</strong></th>
						</tr>
						<tr class="spacer"><td colspan="6">&nbsp;</td></tr>
					</thead>
					<tbody>
					<?

					$ingressos_outros = array();

					$loja_folia = false;
					$loja_outros = false;

					$sql_itens = sqlsrv_query($conexao, "SELECT li.*, v.VE_DIA, v.VE_SETOR, es.ES_NOME, ed.ED_DATA, SUBSTRING(CONVERT(VARCHAR, ed.ED_DATA, 103), 1, 5) AS dia, tp.TI_NOME, tp.TI_TAG FROM loja_itens li, vendas v, eventos_setores es, eventos_dias ed, tipos tp WHERE li.LI_COMPRA='$loja_cod' AND li.LI_INGRESSO=v.VE_COD AND es.ES_COD=v.VE_SETOR AND ed.ED_COD=v.VE_DIA AND v.VE_TIPO=tp.TI_COD AND li.D_E_L_E_T_='0' ORDER BY LI_COD ASC", $conexao_params, $conexao_options);
					if(sqlsrv_num_rows($sql_itens) > 0) {
						$i = 1;
						while ($item = sqlsrv_fetch_array($sql_itens)) {
							$item_cod = $item['LI_COD'];
							$item_id = $item['LI_ID'];
							$item_nome = utf8_encode($item['LI_NOME']);
							$item_tipo = utf8_encode($item['TI_NOME']);
							$item_tag = $item['TI_TAG'];
							$item_dia = utf8_encode($item['dia']);
							$item_setor = $item['ES_NOME'];
							$item_valor = number_format($item['LI_VALOR'], 2, ",", ".");

							$item_data_n = $item['ED_DATA'];
							$item_data_n = (string) date('Y-m-d', strtotime($item_data_n->format('Y-m-d')));

							if(($item_tag == 'lounge') && !in_array($item_data_n, $dias_candybox)) {
								$loja_exist_folia = true;
								$loja_folia = true;
							} else {
								$item_tipo_nome = (($item_tag == 'lounge') && in_array($item_data_n, $dias_candybox)) ? 'Candybox' : $item_tipo;
								array_push($ingressos_outros, $item_tipo_nome);
								$loja_outros = true;
							}


							switch($item_tag) {
								case 'lounge':
									if(in_array($item_data_n, $dias_candybox)) {
										array_push($parcelas, 3);
										$ingresso_candybox = true;
									} else array_push($parcelas, 10);
								break;
								case 'arquibancada':
									array_push($parcelas, 6);
								break;
								case 'frisa':
									array_push($parcelas, 10);
								break;
								case 'camarote':
									array_push($parcelas, 10);
								break;

							}


							?>
								<tr>	
									<td class="first"><? echo $loja_cod."/".$item_id; ?></td>
									<td><? echo $item_nome; ?></td>
									<td><? echo $item_tipo; ?></td>
									<td><? echo $item_dia; ?></td>
									<td><? echo $item_setor; ?></td>
									<td class="valor"><? echo $item_valor; ?></td>
								</tr>
							<?
							$i++;
						}
					}

					if($loja_folia && $loja_outros) $loja_folia = false;
					if(!$loja_folia && $loja_outros) $loja_folia = false;
					elseif($loja_folia && !$loja_outros) $loja_folia = true;


					?>
					</tbody>
				</table>
					<div class="clear"></div>
			</section>

			<?

			if(!$loja_desconto) {

				//Petros
				$sql_exist_cupom_petros = sqlsrv_query($conexao, "SELECT TOP 1 * FROM loja_cupom_petros WHERE LCP_COMPRA='$loja_cod' AND LCP_USUARIO='$usuario_cod' ", $conexao_params, $conexao_options);
				if(sqlsrv_num_rows($sql_exist_cupom_petros) > 0) {

					$cupom_utilizado = true;
					$cupom_petros = true;

					$cupom_cod = 1;
					$cupom_nome = 'PETROS';
					$cupom_codigo = 'PETROS';
					$cupom_valor = 10;
					$cupom_tipo = 1;

				} elseif($_SESSION['compra-cupom-petros']['usuario'] == $usuario_cod) {

					$_SESSION['compra-cupom-petros']['compra'] = $cod;

					$cupom_delete = true;
					$cupom_petros = true;

					$cupom_cod = 1;
					$cupom_nome = 'PETROS';
					$cupom_codigo = 'PETROS';
					$cupom_valor = 10;
					$cupom_tipo = 1;

					$loja_valor_ingressos = $loja_valor_ingressos - (($cupom_valor * $loja_valor_ingressos) / 100);				
					$loja_valor_total = $loja_valor_ingressos + $loja_valor_adicionais;
					
					//Total formatado
					$loja_valor_total_f = number_format($loja_valor_total, 2, ',','.');


				} else { //-----------------------------------------------------------------------------//

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
						if($_SESSION['compra-cupom']['usuario'] == $usuario_cod) {
							
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

								//$dias_candybox
								if((substr($cupom_codigo, 0, 4) == 'KMIP') && (!$loja_folia || $ingresso_candybox)) {

									$cupom_apenas_folia = $cupom_cod;

									unset(
										$_SESSION['compra-cupom']['usuario'], 
										$_SESSION['compra-cupom']['cod'], 
										$_SESSION['compra-cupom']['compra'], 
										$cupom_delete,
										$cupom_cod,
										$cupom_nome,
										$cupom_codigo,
										$cupom_valor,
										$cupom_tipo
									);


								} else {

									// 1 Porcentagem
									// 2 Valor

									$_SESSION['compra-cupom']['usuario'] = $usuario_cod;
									$_SESSION['compra-cupom']['cod'] = $cupom_cod;
									$_SESSION['compra-cupom']['compra'] = $cod;

									/*switch ($cupom_tipo) {
										case 1:
											$loja_valor_total = $loja_valor_total - (($cupom_valor * $loja_valor_total) / 100);
										break;
										
										case 2:
											if($loja_valor_total >= $cupom_valor) $loja_valor_total = $loja_valor_total - $cupom_valor;
											else unset($_SESSION['compra-cupom'], $cupom_cod);
										break;
									}*/

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
					}
				}

			}


			?>
			
			<section id="compra-pagamento">
				<? if((!$loja_comissao_retida && !$loja_comissao_paga) || !$loja_vendedor) { ?>
				<section id="cupom-parceiro">
					<form class="controle" id="form-cupom-parceiro" action="<? echo SITE.$link_lang; ?>ingressos/pagamento/v2/cupom-parceiro/" method="post">
						<input type="hidden" name="cod" value="<? echo $cod; ?>" />
						<p>
							<label for="compra-parceiro" class="infield">Código parceria:</label>
							<input type="text" name="cupom" class="input" id="compra-parceiro" />
							<input type="submit" class="submit adicionar" value="Ok" />
						</p>
					</form>
				</section>
				<? } ?>

				<section id="cupom-pagamento">
					<? if(!$loja_desconto) { ?>
						<? if ($cupom_cod > 0){ ?>
							<span class="cupom">
								<? echo $cupom_nome; ?> •  <? echo $cupom_codigo; ?> <? if (!(0 === strpos($cupom_codigo, 'FOLIA'))) { ?>• Desconto de  <? echo ($cupom_tipo == 1) ? round($cupom_valor)."%" : 'R$ '.number_format($cupom_valor, 2, ',', '.'); } ?>
								<? if ($cupom_delete){ ?><a href="<? echo SITE.$link_lang; ?>ingressos/pagamento/v2/cupom/remover/<? echo $cupom_cod; ?>/<? echo $cod; ?>/" class="excluir confirm" title="Deseja remover o cupom &rdquo;<? echo $cupom_nome; ?>&ldquo;">&times;</a><? } ?>
							</span>
						<? } /*else { ?>
						<form class="controle" id="form-cupom-pagamento" action="<? echo SITE.$link_lang; ?>ingressos/pagamento/v2/cupom/" method="post">
							<input type="hidden" name="cod" value="<? echo $cod; ?>" />
							<input type="hidden" name="v2" value="true" />
							<p>
								<label class="infield" for="compra-cupom">Cupom de desconto:</label>
								<input type="text" name="cupom" class="input" id="compra-cupom" />
								<input type="submit" class="submit adicionar" value="Ok" />

								<span class="matricula">
									<span></span>
									<label class="infield" for="compra-matricula">Matrícula:</label>
									<input type="text" name="matricula" class="input" disabled="disabled" id="compra-matricula" />
								</span>
							</p>
						</form>
						<? } */ ?>
					<? } else { ?>&nbsp;<? } ?>
				</section>

				<? /*<form class="controle" id="form-compra-pagamento" action="<? echo SITE.$link_lang; ?>ingressos/pagamento/confirmacao/" method="post">

		        	<input type="submit" class="submit" value="Pagar" />

		        	<section id="compra-cartao" class="radio coluna">
						<!-- <h3>Selecione o cartão de crédito</h3> -->
						<ul>
							<li><label class="item visa"><input type="radio" name="codigoBandeira" value="visa" /></label></li>
							<li><label class="item mastercard"><input type="radio" name="codigoBandeira" value="mastercard" /></label></li>
							<li><label class="item diners"><input type="radio" name="codigoBandeira" value="diners" /></label></li>
							<li><label class="item discover"><input type="radio" name="codigoBandeira" value="discover" /></label></li>
							<li><label class="item elo"><input type="radio" name="codigoBandeira" value="elo" /></label></li>
							<li><label class="item amex"><input type="radio" name="codigoBandeira" value="amex" /></label></li>
							<!-- <li><label class="item aura"><input type="radio" name="codigoBandeira" value="aura" /></label></li>
							<li><label class="item jcb"><input type="radio" name="codigoBandeira" value="jcb" /></label></li> -->
						</ul>
					</section>

		            <section id="compra-forma-pagamento" class="selectbox coluna">
						<a href="#" class="arrow"><strong>Parcelamento</strong><span></span></a>
						<ul class="drop">
							<?

		                    $parcelas = max($parcelas);

		                    //Formas de parcelamento
		                    for($p=1;$p<=$parcelas;$p++){
		                    	
			                    $valor_parcela = number_format(($loja_valor_total / $p), 2, ",", ".");
		                    ?>
		                    <li><label class="item"><input type="radio" name="formaPagamento" value="<? echo $p; ?>" alt="<? echo $p; ?>x R$ <? echo $valor_parcela; ?>"><? echo $p; ?>x R$ <? echo $valor_parcela; ?></label></li>
		                    <?
		                    }
		                    ?>
						</ul>
					</section>
		            
		            <div class="clear"></div>

		            <input type="hidden" name="produto" value="<? echo $loja_valor_total; ?>" />
			        <input type="hidden" name="capturarAutomaticamente" value="false" />
			        <input type="hidden" name="indicadorAutorizacao" value="2" />
			        <input type="hidden" name="tipoParcelamento" value="2" />
			        <input type="hidden" name="compra" value="<? echo $cod; ?>" />
			    </form>*/ ?>

			        <form class="form-padrao controle" id="form-compra-pagamento" action="https://cieloecommerce.cielo.com.br/Transactional/Order/Index" method="post">

			    		<input type="hidden" name="Merchant_Id" value="e1490bd8-6818-4a0e-a749-1c4ca660b0b7" />
			    		
    		    		<?

    					//Criar um código unico de produto já que temos 2 tabelas e a Cielo não permite enviar uma observação

    					//Verificar existencia
    					$sql_exist_cod = sqlsrv_query($conexao, "SELECT * FROM loja_modalidade WHERE LM_COMPRA='$loja_cod' AND LM_MODALIDADE='carnaval' AND D_E_L_E_T_=0", $conexao_params, $conexao_options);
    					if(sqlsrv_num_rows($sql_exist_cod) > 0) {
    						$ar_order_number = sqlsrv_fetch_array($sql_exist_cod);
    						$order_number = $ar_order_number['LM_COD'];
    					} else {
    						//Inserir
    						$sql_insert_cod = sqlsrv_query($conexao, "INSERT INTO loja_modalidade (LM_COMPRA, LM_MODALIDADE, LM_DATA) VALUES ('$loja_cod', 'carnaval', GETDATE())", $conexao_params, $conexao_options);
    						$order_number = getLastId();
    					}

    					?>
    					<input type="hidden" name="Order_Number" value="<? echo $order_number; ?>" />	
    		    		

    		    		<?

			    		
			    		//Buscar nome do cliente
			    		$valor = 0.00;
			    		$valor_final = 0.00;
			    		$valor_ingressos = 0.00;
			    		$valor_ingressos_base = 0.00;
			    		$valor_adicionais = 0.00;
			    		$valor_desconto = 0.00;
			    		$valor_transfer = 0.00;
			    		$valor_over_interno = 0.00;
			    		$valor_over_externo = 0.00;

			    		$loja_qtde_folia = 0;
			    		$loja_qtde_frisa = 0;

			    		//Novos combos
			    		$loja_qtde_combo = array();

			    		//-----------------------------------------------------------------------------//

			    		$sql_item = sqlsrv_query($conexao, " SELECT COUNT(LI_COD) AS QTDE, LI_VALOR, LI_INGRESSO, LI_DESCONTO, LI_OVER_INTERNO, LI_OVER_EXTERNO, MIN(LI_COD) AS COD, MAX(LI_EXCLUSIVIDADE) AS EXCLUSIVIDADE, MAX(LI_EXCLUSIVIDADE_VAL) AS EXCLUSIVIDADE_VAL FROM loja_itens WHERE LI_COMPRA='$loja_cod' AND D_E_L_E_T_='0' GROUP BY LI_INGRESSO, LI_VALOR, LI_DESCONTO, LI_OVER_INTERNO, LI_OVER_EXTERNO", $conexao_params, $conexao_options);
			    		
			    		if(sqlsrv_num_rows($sql_item) > 0) {
			    			
			    			$i = 1;
			    			$item_count = 1;

			    			while ($item = sqlsrv_fetch_array($sql_item)) {

			    				// $item_id = $item['LI_ID'];
			    				// $item_nome = utf8_encode($item['LI_NOME']);

			    				$item_cod = $item['COD'];
			    				$item_qtde = $item['QTDE'];
			    				$item_ingresso = $item['LI_INGRESSO'];
			    				$item_valor =  $item['LI_VALOR'];
			    				$item_desconto =  $item['LI_DESCONTO'];
			    				$item_overinterno =  $item['LI_OVER_INTERNO'];
			    				$item_overexterno =  $item['LI_OVER_EXTERNO'];
			    				$item_exclusividade = (bool) $item['EXCLUSIVIDADE'];
			    				$item_exclusividade_val = $item['EXCLUSIVIDADE_VAL'];


			    				//for ($iitem=1; $iitem <=$carrinho['qtde'] ; $iitem++) {
			    				
			    				//-----------------------------------------------------------------------------//

			    				$item_valores = $item_valor * $item_qtde;
			    				$valor_ingressos += $item_valores;
			    				
			    				//-----------------------------------------------------------------------------//
			    				
			    				//Procurar o overpricing
			    				$item_valor_tabela = 0.00;
			    				$item_valor_adicionais = 0.00;
			    				$item_valor_transfer = 0.00;
			    				$item_vagas = 1;

			    				//Informações adicionais do item
			    				$sql_info_item = sqlsrv_query($conexao, "
			    				SELECT v.VE_DIA, v.VE_SETOR, v.VE_FILA, v.VE_VAGAS, v.VE_TIPO_ESPECIFICO, v.VE_VALOR_EXCLUSIVIDADE, es.ES_NOME, ed.ED_NOME, ed.ED_DATA, SUBSTRING(CONVERT(VARCHAR, ed.ED_DATA, 103), 1, 5) AS dia, tp.TI_NOME, tp.TI_TAG 
			    				FROM vendas v, eventos_setores es, eventos_dias ed, tipos tp 
			    				WHERE v.VE_COD='$item_ingresso' AND es.ES_COD=v.VE_SETOR AND ed.ED_COD=v.VE_DIA AND v.VE_TIPO=tp.TI_COD", $conexao_params, $conexao_options);

			    				if(sqlsrv_num_rows($sql_info_item) > 0) {
			    					$info_item = sqlsrv_fetch_array($sql_info_item);

			    					$item_setor = utf8_encode($info_item['ES_NOME']);
			    					$item_dia = utf8_encode($info_item['ED_NOME']);
			    					$item_data = utf8_encode($info_item['dia']);
			    					$item_data_n = $info_item['ED_DATA'];
			    					$item_tipo = utf8_encode($info_item['TI_NOME']);
			    					$item_tipo_tag = $info_item['TI_TAG'];
			    					
			    					$item_fila = utf8_encode($info_item['VE_FILA']);
			    					$item_vaga = utf8_encode($info_item['VE_VAGAS']);
			    					$item_tipo_especifico = utf8_encode($info_item['VE_TIPO_ESPECIFICO']);
			    					$item_valor_exclusividade = $info_item['VE_VALOR_EXCLUSIVIDADE'];

			    					$item_fechado = (($item_vaga > 0) && ($item_tipo_especifico == 'fechado')) ? true : false;
			    					if($item_fechado) $item_vagas = utf8_encode($ar_item_infos['VE_VAGAS']);

			    					$item_data_n = (string) date('Y-m-d', strtotime($item_data_n->format('Y-m-d')));

			    					if(($item_tipo_tag == 'lounge')) {

			    						if($loja_cod <= $combo_dias_limite) {
			    							if(in_array($item_data_n, $dias_principais)){
			    								//Adicionamos na quantidade e excluimos do array
			    								$loja_qtde_folia++;
			    								foreach ($dias_principais as $key_dia => $item_dia_atual) {
			    									if ($item_dia_atual == $item_data_n) unset($dias_principais[$key_dia]);
			    								}
			    							}
			    							
			    						} else {

			    							//loja_qtde_combo
			    							if(count($combo_dias) > 0) {

			    								// Limite
												$loja_data_limite = (string) date('Y-m-d', strtotime($loja_data->format('Y-m-d')));

			    								foreach ($combo_dias as $k => $c) {
			    									//Verificar cada ocorrencia
			    									// if(in_array($item_data_n, $c['dias'])) {
													// Modificacao por causa da data de compra
													
													if(in_array($item_data_n, $c['dias']) && ($loja_data_limite >= $c['limite'][0]) && ($loja_data_limite <= $c['limite'][1])) {

			    										$loja_qtde_combo[$k] = 1 + ((int) $loja_qtde_combo[$k]);

			    										//Retiramos do combo o valor encontrado
			    										foreach ($c['dias'] as $kd => $ingressos_dia_atual) {
			    											if ($ingressos_dia_atual == $item_data_n) unset($combo_dias[$k]['dias'][$kd]);
			    										}
			    									}									
			    								}
			    							}
			    							
			    						}
			    					}



			    					$produto_nome = $item_tipo;
			    					if(!empty($item_fila)) { $produto_nome .= " ".$item_fila; }
			    					if(!empty($item_tipo_especifico)) { $produto_nome.= " ".$item_tipo_especifico; }
			    					if($item_fechado) { $produto_nome .= " (".$item_vaga." vagas)"; }
			    					$produto_descricao = $produto_nome ." - ".$item_dia." dia - Setor: ".$item_setor;
			    				}

			    				$excl = ($item_exclusividade) ? true : false;

			    				for ($iitemvaga=1; $iitemvaga <= $item_vagas; $iitemvaga++) { 

			    					$item_id = ($item_vagas > 1) ? $iitemvaga : $iitem;

			    					//$valor_desconto += $item_desconto;
			    					$valor_over_interno += $item_overinterno;
			    					$valor_over_externo += $item_overexterno;

			    					$excl = ($excl == true) ? 1 : 0;
			    					
			    					//Adicionar valor exclusividade
			    					if(($iitemvaga == 1) && $excl) {
			    						$valor_adicionais += $item_valor_exclusividade;
			    						$item_valor_adicionais += $item_valor_exclusividade;
			    					}

			    					//-----------------------------------------------------------------------------//

			    					// $sql_adicionais = sqlsrv_query($conexao, "SELECT lia.LIA_COD, v.* FROM loja_itens_adicionais lia, vendas_adicionais v WHERE v.VA_COD=lia.LIA_ADICIONAL AND lia.LIA_COMPRA='$cod' AND lia.LIA_ITEM='$item_cod'", $conexao_params, $conexao_options);
			    					$sql_adicionais = sqlsrv_query($conexao, "SELECT lia.LIA_COD, v.*, vv.*
			    						FROM loja_itens_adicionais lia, vendas_adicionais v, vendas_adicionais_valores vv 
			    						WHERE v.VA_COD=lia.LIA_ADICIONAL AND lia.LIA_COMPRA='$loja_cod' AND vv.VAV_ADICIONAL=v.VA_COD 
			    						AND lia.LIA_ITEM IN (SELECT LI_COD FROM loja_itens WHERE LI_COMPRA='$loja_cod' AND LI_INGRESSO='$item_ingresso' AND D_E_L_E_T_='0')
			    						AND lia.D_E_L_E_T_='0' AND vv.VAV_BLOCK=0 AND vv.D_E_L_E_T_=0 AND v.VA_BLOCK=0 AND v.D_E_L_E_T_=0
			    						ORDER BY vv.VAV_INCLUSO DESC

			    						", $conexao_params, $conexao_options);

			    					if(sqlsrv_num_rows($sql_adicionais) !== false) {

			    						while ($vendas_adicionais = sqlsrv_fetch_array($sql_adicionais)) {

			    							$vendas_adicionais_cod = $vendas_adicionais['VA_COD'];
			    							$vendas_adicionais_tipo = $vendas_adicionais['VA_TIPO'];
			    							$vendas_adicionais_label = utf8_encode($vendas_adicionais['VA_LABEL']);
			    							$vendas_adicionais_nome_exibicao = $vendas_adicionais['VA_NOME_EXIBICAO'];
			    							$vendas_adicionais_nome_insercao = $vendas_adicionais['VA_NOME_INSERCAO'];
			    							$vendas_adicionais_multi = (bool) $vendas_adicionais['VA_VALOR_MULTI'];

			    							$vendas_adicionais_opcoes_cod = $vendas_adicionais['VAV_COD'];
			    							$vendas_adicionais_opcoes_valor = $vendas_adicionais['VAV_VALOR'];
			    							$vendas_adicionais_opcoes_incluso = (bool) $vendas_adicionais['VAV_INCLUSO'];
			    							$vendas_adicionais_opcoes_incluso_int = $vendas_adicionais_opcoes_incluso ? 1 : 0;

			    							if($vendas_adicionais_opcoes_incluso) $vendas_adicionais_opcoes_incluso_ar[$item_cod][$vendas_adicionais_nome_exibicao] = true;

			    							if($vendas_adicionais_nome_exibicao == 'delivery'){

			    								if(!$vendas_adicionais_delivery['incluso'] || $vendas_adicionais_opcoes_incluso || ($vendas_adicionais_opcoes_valor > $vendas_adicionais_delivery['valor'])){

			    									$delivery = true;
			    									$vendas_adicionais_delivery['incluso'] = ($vendas_adicionais_opcoes_incluso) ? 1 : 0;
			    									$vendas_adicionais_delivery['cod'] = $vendas_adicionais_cod;
			    									$vendas_adicionais_delivery['label'] = $vendas_adicionais_label;
			    									$vendas_adicionais_delivery['valor'] = $vendas_adicionais_opcoes_valor;

			    								}

			    							} else {

			    								$adicional_enable = true;

			    								//Limitamos o transfer
			    								if(!$vendas_adicionais_multi && ($item_ingresso == $item_anterior)) $adicional_enable = false;
			    								
			    								if(!$vendas_adicionais_opcoes_incluso_ar[$item_cod][$vendas_adicionais_nome_exibicao] && $adicional_enable) {

			    									if($vendas_adicionais_nome_exibicao == 'transfer') $item_valor_adicionais = $vendas_adicionais_opcoes_valor;
			    									else $item_valor_adicionais += $vendas_adicionais_opcoes_valor;

			    									$valor_adicionais += $vendas_adicionais_opcoes_valor;
			    									
			    								}										

			    							}

			    						}

			    					}

			    					#$valor_transfer += $item_valor_transfer;

			    					//-----------------------------------------------------------------------------//
			    					
			    					//Atualizar
			    					$item_anterior = $item_ingresso;

			    				} //for iitemvaga

			    				//-----------------------------------------------------------------------------//

			    				$item_total_valores = $item_valores + $item_valor_adicionais;

			    				$valor_final += ($item_total_valores);
			    				$produto_valor_unitario = number_format(($item_total_valores / $item_qtde), 2, '', '');
			    				
			    				?>
			    				<input type="hidden" name="cart_<? echo $i; ?>_name" value="<? echo $produto_descricao; ?>" />
			    				<input type="hidden" name="cart_<? echo $i; ?>_description" value="<? echo $produto_descricao; ?>" />
			    				<input type="hidden" name="cart_<? echo $i; ?>_unitprice" value="<? echo $produto_valor_unitario; ?>" />
			    				<input type="hidden" name="cart_<? echo $i; ?>_quantity" value="<? echo $item_qtde; ?>" />
			    				<input type="hidden" name="cart_<? echo $i; ?>_type" value="3" />
			    				<?

			    				$i++;
			    			}

			    		}
			    		
			    		if($delivery) {

			    			if(!$vendas_adicionais_delivery['incluso']) $valor_adicionais += $vendas_adicionais_delivery['valor'];

			    		}				
			    		
			    		//$valor = $valor_parcial = $valor_ingressos + $valor_adicionais + $delivery_valor;
			    		$valor = $valor_parcial = $valor_ingressos + $valor_adicionais + $delivery_valor;

			    		$desconto = 0;				

			    		if($cupom_valor_desconto > 0) $desconto = 1;
			    		
			    		$loja_combo_desconto = 0;
			    		#if($loja_parceiro == 54) {

			    			foreach ($loja_qtde_combo as $k => $r) {
			    				if(($r == $combo_dias[$k]['total']) && ($combo_dias[$k]['desconto'] > $loja_combo_desconto)) {
			    					$loja_combo_desconto = $combo_dias[$k]['desconto'];
			    					$loja_combo_nome = $combo_dias[$k]['nome'];
			    				}
			    			}

			    			if($loja_desconto_folia && ($loja_combo_desconto > 0)) {
			    				$desconto = 1;
			    				$desconto_especial_folia = ($loja_combo_desconto * $valor) / 100;
			    				$valor = $valor - $desconto_especial_folia;
			    			}
			    			
			    		#}
			    		
			    		if($loja_desconto_frisa && ($loja_qtde_frisa > 0)) {
			    			$desconto = 1;
			    			// $valor = $valor - ($loja_qtde_frisa * 50);
			    			$desconto_especial_frisa = $loja_qtde_frisa * 50;
			    			$valor = $valor - $desconto_especial_frisa;
			    		}

			    		if($desconto) {

			    			$desconto_valores = $valor_desconto + $desconto_especial_folia + $desconto_especial_frisa + $cupom_valor_desconto;
							$valor_final -= $desconto_valores;

							$valor_desconto = number_format($desconto_valores, 2, '', '');

			    			?>
			    			<input type="hidden" name="Discount_Type" value="1" />
			    			<input type="hidden" name="Discount_Value" value="<? echo $valor_desconto; ?>" />
			    			<?
			    		}
			    		
			    		if($delivery) {

			    			//$delivery_valor;

			    			$loja_endereco = utf8_encode($loja['LO_CLI_ENDERECO']);
			    			$loja_numero = utf8_encode($loja['LO_CLI_NUMERO']);
			    			$loja_complemento = utf8_encode($loja['LO_CLI_COMPLEMENTO']);
			    			$loja_bairro = utf8_encode($loja['LO_CLI_BAIRRO']);
			    			$loja_cidade = utf8_encode($loja['LO_CLI_CIDADE']);
			    			$loja_estado = utf8_encode($loja['LO_CLI_ESTADO']);
			    			$loja_cep = utf8_encode($loja['LO_CLI_CEP']);
			    			$loja_data_para_entrega = utf8_encode($loja['DATA_PARA_ENTREGA']);
			    			$loja_cuidados = utf8_encode($loja['LO_CLI_CUIDADOS']);
			    			$loja_celular = utf8_encode($loja['LO_CLI_CELULAR']);
			    			$loja_referencia = utf8_encode($loja['LO_CLI_PONTO_REFERENCIA']);

			    			$valor_final += $delivery_valor;

			    			?>
			    			<input type="hidden" name="Shipping_Type" value="2" />
			    			<input type="hidden" name="Shipping_1_Price" value="<? echo number_format($delivery_valor, 2, '', ''); ?>" />
			    			<input type="hidden" name="Shipping_1_Name" value="Frete" />
			    			<input type="hidden" name="Shipping_Address_Name" value="<? echo $loja_endereco; ?>" />
			    			<input type="hidden" name="Shipping_Address_Number" value="<? echo $loja_numero; ?>" />
			    			<input type="hidden" name="Shipping_Address_Complement" value="<? echo $loja_complemento; ?>" />
			    			<input type="hidden" name="Shipping_Address_District" value="<? echo $loja_bairro; ?>" />
			    			<input type="hidden" name="Shipping_Address_City" value="<? echo $loja_cidade; ?>" />
			    			<input type="hidden" name="Shipping_Address_State" value="<? echo $loja_estado; ?>" />
			    			<input type="hidden" name="Shipping_ZipCode" value="<? echo $loja_cep; ?>" />
			    			<?

			    		} else { 
			    			?>
			    			<input type="hidden" name="Shipping_Type" value="4" />
			    			<?
			    		}
			    		
			    		//Validar CPF ou CNPJ
						// $loja_cpf_cnpj_teste = formatCPFCNPJ($loja_cpf_cnpj);
						// switch (strlen($loja_cpf_cnpj_teste)) {
						// 	case '14':
						// 		if(!validaCPF($loja_cpf_cnpj_teste)) unset($loja_cpf_cnpj);
						// 	break;

						// 	case '18':
						// 		if(!validaCNPJ($loja_cpf_cnpj_teste)) unset($loja_cpf_cnpj);
						// 	break;

						// 	default: 
						// 		unset($loja_cpf_cnpj);
						// 	break;					
						// }

						$loja_telefone = str_replace(' ', '', $loja_telefone);
						$loja_telefone = str_replace('+', '', $loja_telefone);
						if(strlen($loja_telefone) < 10) unset($loja_telefone);

						if(!empty($loja_nome)) { ?><input type="hidden" name="Customer_Name" value="<? echo $loja_nome; ?>" /><? }
						if(!empty($loja_email)) { ?><input type="hidden" name="Customer_Email" value="<? echo $loja_email; ?>" /><? }
						if(!empty($loja_cpf_cnpj)) { ?><input type="hidden" name="Customer_Identity" value="<? echo $loja_cpf_cnpj; ?>" /><? }
						if(!empty($loja_telefone)) { ?><input type="hidden" name="Customer_Phone" value="<? echo $loja_telefone; ?>" /><? }
						
						?>
			    		
			    		<input type="hidden" name="Antifraude_Enabled" value="true" />

			    		<input type="submit" class="submit cielo" value="Realizar pagamento" />

			    		<h3>R$ <? echo number_format($valor_final, 2, ',', '.'); ?></h3>
			    	</form>

			    <div class="clear"></div>

			</section>
			
			<article class="aviso">
				Após a confirmação do pagamento você deve imprimir o seu <strong>Voucher</strong> acessando o menu <strong>Minhas Compras</strong>.
			</article>

		</section>
	<?
	}
	?>	
	</section>
</section>
<?
if($cupom_apenas_folia) {
?>
<section id="overlay-cupom">
	<article>
		<h2>KM de Vantagens Ipiranga</h2>
		<p>Esta promoção é exclusiva para ingressos do Folia Tropical<? if($ingresso_candybox) { echo ', exceto Candybox'; } ?>. O desconto não será válido se houverem outros tipos de ingresso na compra.</p>

		<? if($loja_exist_folia) { ?>
		<h3>Deseja retirar &ldquo;<? echo implode('&ldquo;, &rdquo;', $ingressos_outros); ?>&rdquo;?</h3>
		<a href="#" class="fechar nao ctrl">Não</a>
		<a href="<? echo SITE.$link_lang; ?>ingressos/pagamento/v2/<? echo $cod; ?>/remover/<? echo $cupom_apenas_folia; ?>/" class="sim ctrl">Sim</a>
		<? } else { ?>
		<a href="#" class="fechar voltar ctrl">Voltar</a>
		<? }?>
	</article>
</section>
<?
}

//-----------------------------------------------------------------//

include('include/footer.php');

//fechar conexao com o banco
include("conn/close.php");
include("conn/close-mssql.php");
include("conn/close-sankhya.php");

?>