<?

//Incluir funções básicas
include("include/includes.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");

unset($_SESSION['roteiro-itens']);


//busca paises
$sql_paises= sqlsrv_query($conexao_sankhya, "SELECT * FROM pais", $conexao_params, $conexao_options);
$paises=array();
while($linha = sqlsrv_fetch_array($sql_paises)){
	array_push($paises, $linha);
}

// $sigla = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 PAIS_SIGLA FROM TGFPAR WHERE CODPARC='$loja_cliente' AND CLIENTE='S' AND BLOQUEAR='N' ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
// $sigla_cliente = sqlsrv_fetch_array($sigla);


//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");
include("include/header.php");

//-----------------------------------------------------------------//

$evento = (int) $_SESSION['usuario-carnaval'];
$administrador = ($_SESSION['us-grupo'] == 'ADM') ? true : false;
$cod = (int) $_GET['c'];


//-----------------------------------------------------------------//
//clearsale

require __DIR__.'/ClearSale/vendor/autoload.php';

use ClearSale\ClearSaleAnalysis;
use ClearSale\Environment\Sandbox;
use ClearSale\XmlEntity\Response\OrderReturn;


$entityCode = CLEARSALE_ENTITY_CODE;
$environment = new Sandbox($entityCode);

//-----------------------------------------------------------------//


$sql_loja = sqlsrv_query($conexao, "SELECT TOP 1 l.*, (CONVERT(VARCHAR, l.LO_DATA_COMPRA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, l.LO_DATA_COMPRA, 108),1,5)) AS DATA, (CONVERT(VARCHAR, l.LO_DATA_ENTREGA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, l.LO_DATA_ENTREGA, 108),1,5)) AS DATA_ENTREGA, ISNULL(DATEDIFF (DAY, LO_DATA_PAGAMENTO, GETDATE()), 6) AS DIFERENCA, CONVERT(CHAR, l.LO_CLI_DATA_ENTREGA, 103) AS DATA_PARA_ENTREGA FROM loja l WHERE l.LO_EVENTO='$evento' AND l.LO_BLOCK='0' AND l.LO_COD='$cod'", $conexao_params, $conexao_options);

$sql_cod_cliente = sqlsrv_query($conexao, "select LO_CLIENTE from loja where LO_COD='$cod'",$conexao_params, $conexao_options);

if(sqlsrv_num_rows($sql_cod_cliente) > 0) {
	$cliente = sqlsrv_fetch_array($sql_cod_cliente);
	$cod_cliente = $cliente['LO_CLIENTE'];
}
?>

<section id="overlay" class="fechar-modal"><span class="loader"></span></section>
<section class="modal-box" id="modal">
	<section class="modal-dialog">
		<section class="modal-content">
			<section id="endereco-box">
				<header>
					<h1>Alterar endereço da compra</h1>
					<a href="#" class="fechar-modal">&times;</a>
				</header>
				<section id="conteudo">
					<form name="endereco" class="cadastro controle" id="cadastro-endereco">
						<input type="hidden" id="total" value="<? echo $loja_valor_total; ?>">
						<input type="hidden" name="cod" id="cod" value="<? echo $cod; ?>" />
						<input type="hidden" name="cliente" value="<? echo $loja_cliente; ?>" />

						<p class="form-group">
							<label>Tipo de Endereço</label>
							<select name="pais" class="drop" id="pais" style="width: 340px; margin: 0; padding: 10px 19px; border: 1px #bcd0ec solid; text-decoration: none; font: 600 14px/1em 'Open Sans', sans-serif; color: #16b2ff; background: #fff; border-radius: 6px;">
							<?php foreach ($paises as $key => $pais) { ?>
								<option value="<?php echo $pais['PAIS_SIGLA'] ?>"><?php echo $pais['PAIS_NOME'] ?></option>
							<? } ?>
							</select>
						</p>
						

						<p class="cep form-group">
							<label for="cep">CEP</label>
							<input type="text" name="cep" class="input pequeno" id="cep" required>
							<a class="busca-cep" href="http://www.buscacep.correios.com.br/" target="_blank">Não sei meu CEP</a>
						</p>
						<p class="zipcode form-group" style="display: none;">
							<label for="cep">Zipcode</label>
							<input type="text" name="zipcode" class="input pequeno" id="zipcode" required>
						</p>

						<div class="coluna">
							<p class="cidade form-group">
								<label for="cidade" class="control-label">Cidade</label>
								<input type="text" name="cidade" class="input" id="cidade" required>
							</p>
							<p class="estado form-group">
								<label for="estado" class="control-label">Estado</label>
								<input type="text" name="estado" class="input" id="estado" required>
							</p>
							<div class="clear"></div>
						</div>

						<p class="form-group">
							<label for="bairro">Bairro</label>
							<input type="text" name="bairro" class="input" id="bairro" required>
						</p>


						<p class="form-group">
							<label for="endereco">Endereço</label>
							<input type="text" name="endereco" class="input" id="endereco" required>
						</p>

						<p class="numero form-group">
							<label for="numero" class="control-label">Número</label>
							<input type="number" name="numero" class="input" id="numero">
						</p>

						<p class="complemento form-group">
							<label for="complemento">Complemento</label>
							<input type="text" name="complemento" class="input complemento" id="complemento" />
						</p>

						<section id="escolher-endereços">
				<h2 class="escolha-endereco">Ou escolha um endereço do cadastro:</h2>

				

			<? 
                $sql_enderecos =sqlsrv_query($conexao_sankhya, "SELECT *
											                    FROM
											                        clientes_enderecos
											                    WHERE
											                        CE_CLIENTE=$cod_cliente
											                        AND CE_BLOCK='0'
											                        AND D_E_L_E_T_='0'
											                    ORDER BY
											                        CE_ULTIMA_ENTREGA DESC",
                $conexao_params, $conexao_options);

                $numRows = sqlsrv_num_rows($sql_enderecos);

                if($numRows > 0):

                    $i = 2;

                    $count = 1;

                    while ($endereco = sqlsrv_fetch_array($sql_enderecos)):                        
                        $endereco_cod = $endereco['CE_COD'];
                        $endereco_pais = $endereco['CE_PAIS'];
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

            <? if ($i  % 2 == 0): ?>
            	<div class="todos-enderecos">
            <? endif; ?>

		            <div class="endereco-div">
		            	<input type="radio" name="check-endereco" class="check-endereco" value="<?=$endereco_cod;?>">
		            	<p><?=$endereco_logradouro;?>, <?=$endereco_numero;?> - <?=$endereco_bairro;?></p>
		            	<p>Cep <?=$endereco_cep;?></p>
		            	<p><?=$endereco_cidade;?> - <?=$endereco_estado;?></p>
		            </div>

		    <? if ($count  % 2 == 0): ?>
				</div>
			<? endif; ?>
			
			<?			
				$i += 1;
            	$count ++;
                    endwhile; //endereço
            	endif; //numRows
            ?>

            </section>
        </div>
            
					
									
						<footer>
							<!-- <input type="submit" class="input submit" value="Salvar endereço" /> -->
							<!-- <a href="" class="submit" id="salva_endereco" style="width: 190px;float: right;    background-position: center right;">Salvar endereço</a> -->
							<a href="#" id="salva_endereco">Salvar endereço</a>
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

		//paises
	    $sql_paises = sqlsrv_query($conexao_sankhya, "SELECT PAIS_SIGLA,PAIS_NOME,PAIS_PHONECODE FROM pais ORDER BY PAIS_NOME", $conexao_params, $conexao_options);
	    $paises=array();
	    while ($linha = sqlsrv_fetch_array($sql_paises)) {
	        $paises[$linha['PAIS_SIGLA']]=array("nome"=>$linha['PAIS_NOME'],"ddi"=>$linha['PAIS_PHONECODE']);
	    }

	    utf8_encode_deep($paises);

		$loja_qtde_folia = 0;
		$loja_qtde_frisa = 0;
		$loja_enable_frisa = false;

		//Novos combos
		$loja_qtde_combo = array();

		$loja = sqlsrv_fetch_array($sql_loja);

		$loja_cod = $loja['LO_COD'];
		$loja_cliente = $loja['LO_CLIENTE'];
		$loja_parceiro = $loja['LO_PARCEIRO'];
		$loja_forma = $loja['LO_FORMA_PAGAMENTO'];
		$loja_diferenca_dias = (15 - $loja['DIFERENCA']);
		$loja_status_transacao = $loja['LO_STATUS_TRANSACAO'];
		$loja_pago = (bool) $loja['LO_PAGO'];
		$loja_delivery = (bool) $loja['LO_DELIVERY'];
		$loja_desconto = (bool) $loja['LO_DESCONTO'];
		if(!$loja_delivery) $loja_retirada = $loja['LO_RETIRADA'];
		if(!$loja_delivery) $loja_data_retirada = utf8_encode($loja['DATA_PARA_ENTREGA']);
		$loja_periodo = utf8_encode($loja['LO_CLI_PERIODO']);
		$loja_comissao_retida = (bool) $loja['LO_COMISSAO_RETIDA'];
		$loja_comissao_paga = (bool) $loja['LO_COMISSAO_PAGA'];
		$loja_cancelado = (bool) $loja['D_E_L_E_T_'];
		$loja_cancelado_int = $loja_cancelado ? 1 : 0;
		$loja_vendedor = (empty($loja['LO_VENDEDOR']) || $loja['LO_VENDEDOR'] == 0) ? false : true;

		///////////
		$loja_parcelas = $loja['LO_PARCELAS'];

		$loja_data = $loja['LO_DATA_COMPRA'];
		$anterior = (strtotime($loja_data->format('Y-m-d')) < strtotime('2015-10-15')) ? true : false;
		$loja_desconto_folia = ($anterior) ? 1 : (bool) $loja['LO_DESCONTO_FOLIA'];
		$loja_desconto_frisa = ($anterior) ? 1 : (bool) $loja['LO_DESCONTO_FRISA'];
		
		$cartao_credito = ($loja_forma == 1) ? true : false;
		$multiplo = ($loja_forma == 10) ? true : false;
		$faturado = ($loja_forma == 7) ? true : false;
		$reserva = ($loja_forma == 5) ? true : false;

		// $loja_cliente = utf8_encode($loja['CL_NOME']);
		
		$sql_cliente = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 NOMEPARC, TELEFONE, CELULAR, EMAIL,DDI,DDD,DDI_CELULAR,DDD_CELULAR FROM TGFPAR WHERE CODPARC='$loja_cliente' AND CLIENTE='S' AND BLOQUEAR='N' ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);

		//$sql_cliente = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 NOMEPARC, TELEFONE, CELULAR, EMAIL,DDI,DDD,DDI_CELULAR,DDD_CELULAR FROM TGFPAR WHERE CODPARC='$loja_cliente' AND BLOQUEAR='N' ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);

		if(sqlsrv_num_rows($sql_cliente) > 0) $loja_cliente_ar = sqlsrv_fetch_array($sql_cliente);

		$loja_nome = utf8_encode(trim($loja_cliente_ar['NOMEPARC']));
		$loja_ddi = utf8_encode(trim($loja_cliente_ar['DDI']));
		$loja_ddd = utf8_encode(trim($loja_cliente_ar['DDD']));
		$loja_telefone = utf8_encode(trim($loja_cliente_ar['TELEFONE']));
		$loja_email = utf8_encode(trim($loja_cliente_ar['EMAIL']));
		$loja_ddi_celular = utf8_encode(trim($loja_cliente_ar['DDI_CELULAR']));
		$loja_ddd_celular = utf8_encode(trim($loja_cliente_ar['DDD_CELULAR']));
		$loja_celular = utf8_encode(trim($loja_cliente_ar['CELULAR']));

		$loja_valor_total = $loja['LO_VALOR_TOTAL'];
		$loja_valor_total_f = number_format($loja_valor_total, 2, ',','.');

		if($loja_delivery) {
			$lo_valor_delivery = $loja['LO_VALOR_DELIVERY'];
			$lo_valor_delivery_f = number_format($lo_valor_delivery, 2, ',','.');			
		}
		
		$loja_entregue = (bool) $loja['LO_ENTREGUE'];
		if($loja_entregue) {
			$loja_data_entrega = $loja['DATA_ENTREGA'];
			$loja_entregue_nome = utf8_encode($loja['LO_ENTREGUE_NOME']);			
		}

		//Forma de pagamento
		// LO_FORMA_PAGAMENTO
		$sql_forma = sqlsrv_query($conexao, "SELECT TOP 1 FP_NOME FROM formas_pagamento WHERE FP_COD='$loja_forma'", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_forma) > 0) {
			$loja_forma_ar = sqlsrv_fetch_array($sql_forma);
			$loja_forma_pagamento = utf8_encode($loja_forma_ar['FP_NOME']);
		}

		//Se for cartão de credito
		if($loja_forma == 1) {

			//Buscar a bandeira
			$loja_cartao = $loja['LO_CARTAO'];

			//XML
			$loja_xml = $loja['LO_XML'];

			if(!empty($loja_xml)) {
				$xml = new SimpleXMLElement($loja_xml);
				$loja_parcelas = $xml->{'forma-pagamento'}->parcelas;
  			}
			
		}


		//Cielo V2
		$loja_cielo_v2 = (bool) $loja['LO_CARTAO_V2'];
		if(/*$loja_cielo_v2*/true) {
			$loja_cielo_v2_numero_cartao = utf8_encode($loja['LO_CARTAO_BANDEIRA']);
			$loja_cielo_v2_nome = utf8_encode($loja['LO_CARTAO_NOME']);
			// $loja_cielo_v2_cpf = formatCPFCNPJ(preg_replace( "@[./-]@", "", $loja['LO_CARTAO_CPF']));
			$loja_cielo_v2_cpf = preg_replace( "@[./-]@", "", $loja['LO_CARTAO_CPF']);
			// $loja_cielo_v2_email = utf8_encode($loja['LO_CARTAO_EMAIL']);
			// $loja_cielo_v2_telefone = formatTelefone($loja['LO_CARTAO_TELEFONE']);
			$loja_cielo_v2_antifraude = $loja['LO_CARTAO_ANTIFRAUDE'];
			$loja_parcelas = $loja['LO_PARCELAS'];
			$loja_checkoutid = $loja['LO_CARTAO_CHECKOUTID'];

			$loja_pais=utf8_encode($loja['LO_CLI_PAIS']);
			$loja_cep=utf8_encode($loja['LO_CLI_CEP']);
			$loja_estado=utf8_encode($loja['LO_CLI_ESTADO']);
			$loja_cidade=utf8_encode($loja['LO_CLI_CIDADE']);
			$loja_bairro=utf8_encode($loja['LO_CLI_BAIRRO']);
			$loja_endereco=utf8_encode($loja['LO_CLI_ENDERECO']);
			$loja_numero=utf8_encode($loja['LO_CLI_NUMERO']);
			$loja_complemento=utf8_encode($loja['LO_CLI_COMPLEMENTO']);

			$loja_antifraude_status = $loja['LO_ANTIFRAUDE_STATUS'];
			$loja_antifraude_score = $loja['LO_ANTIFRAUDE_SCORE'];
			// $loja_antifraude_score=$loja_antifraude_score*100;
			$loja_antifraude_quiz_url = $loja['LO_ANTIFRAUDE_QUIZ_URL'];

			switch ($loja_cielo_v2_antifraude) {
				case 1:
					$loja_cielo_v2_antifraude_classe = 'baixo';
					$loja_cielo_v2_antifraude_texto = 'Baixo Risco';
				break;
				case 2:
					$loja_cielo_v2_antifraude_classe = 'alto';
					$loja_cielo_v2_antifraude_texto = 'Alto Risco';
				break;
				case 3:
					$loja_cielo_v2_antifraude_classe = 'nao-finalizado';
					$loja_cielo_v2_antifraude_texto = 'Não Finalizado';
				break;
				case 4:
					$loja_cielo_v2_antifraude_classe = 'moderado';
					$loja_cielo_v2_antifraude_texto = 'Risco Moderado';
				break;
				default:
					$loja_cielo_v2_antifraude_classe = '';
					$loja_cielo_v2_antifraude_texto = 'N/A';
				break;
			}
		}

		$loja_camisas = true;

		//Verificar se tem um folia tropical do dia 01/03;
		//$loja_folia_item = ($_SERVER['SERVER_NAME'] == "server") ? 28 :  176;
		//$sql_folia = sqlsrv_query($conexao, "SELECT LI_COD FROM loja_itens WHERE LI_COMPRA='$loja_cod' AND LI_INGRESSO<>'$loja_folia_item' AND D_E_L_E_T_='$loja_cancelado_int'", $conexao_params, $conexao_options);
		$sql_folia = sqlsrv_query($conexao, "SELECT LI_COD FROM loja_itens WHERE LI_COMPRA='$loja_cod' AND LI_INGRESSO NOT IN (SELECT v.VE_COD FROM vendas v LEFT JOIN eventos_dias d ON d.ED_COD=v.VE_DIA WHERE ((v.VE_TIPO=4 AND d.ED_DATA IN ('2015-02-13', '2015-02-14', '2016-02-05', '2016-02-06')) OR (v.VE_TIPO IN (1,2))) AND v.VE_BLOCK=0 AND v.D_E_L_E_T_=0) AND D_E_L_E_T_='$loja_cancelado_int'", $conexao_params, $conexao_options);

		if(sqlsrv_num_rows($sql_folia) == 0) $loja_camisas = false;

		$loja_tid = $loja['LO_TID'];

		//clearsale
		if($cartao_credito/* && $loja_cielo_v2*/) {
			//iniciar variavel para analise da clearsale
			$clearSale = new ClearSaleAnalysis($environment);

			//variavel de retorno com os dados se ja tiver consultado antes e estiver no banco
			$orderReturn = new OrderReturn($loja_cod,$loja_antifraude_status,$loja_antifraude_score);

			if($clearSale->approvedReturn($orderReturn)){
				$antifraudeAprovado=true;
				//cor do score do pedido
				switch (true) {
					case $loja_antifraude_score<30:
						$aprovado_score_cor="verde";
						break;
					case $loja_antifraude_score<60:
						$aprovado_score_cor="laranja";
						break;

					case $loja_antifraude_score<90:
						$aprovado_score_cor="vermelho";
						break;
					case $loja_antifraude_score<100:
						$aprovado_score_cor="vermelho";
						break;
				} 
			}else if($clearSale->notApprovedReturn($orderReturn)){
				$antifraudeReprovado=true;
			} else if($clearSale->waitingForApprovalReturn($orderReturn)){
				$antifraudeAnalisando=true;
			}
		}

	?>
		<header class="titulo">
			<h1>Detalhes da <span>Compra</span></h1>
			<div class="valor-total">R$ <? echo $loja_valor_total_f; ?></div>
		</header>
		<section class="padding">
			<section class="secao" id="compra-dados">
				<aside><? echo $loja_cod; ?></aside>
				<section>
					<h1><? echo $loja_nome; ?></h1>
					<p><? echo $loja_email; ?></p>

					<? if(!empty($loja_telefone)): ?>					
						<p><? echo "+".$paises[$loja_ddi]['ddi']." ".$loja_ddd." ".$loja_telefone; ?></p>
					<? endif; ?>

					<? if(!empty($loja_celular)): ?>
						<p><? echo "+".$paises[$loja_ddi_celular]['ddi']." ".$loja_ddd_celular." ".$loja_celular; ?></p>
					<? endif; ?>

				</section>

				<div class="informacoes-compra">
					<p><? echo $loja_forma_pagamento; ?></p>
					
					<?
					if(!$loja_cancelado && $cartao_credito && !empty($loja_cartao)) { ?><p class="cartao"><span class="<? echo $loja_cartao; ?>"></span> <? echo $loja_parcelas; ?>x R$ <? echo number_format(($loja_valor_total / $loja_parcelas), 2, ",", "."); ?> <strong> • <? echo $loja_cartao; ?></strong></p><? }
					
					//Faturado
					if($faturado) {
						
						$faturas_total = 0;

						//Buscar faturas
						$sql_faturas = sqlsrv_query($conexao, "SELECT LF_VALOR, COUNT(LF_COD) AS PARCELAS FROM loja_faturadas WHERE LF_COMPRA='$cod' AND D_E_L_E_T_='0' GROUP BY LF_VALOR", $conexao_params, $conexao_options);
						$n_faturas = sqlsrv_num_rows($sql_faturas);

						if($n_faturas > 0) {
							?>
							<p class="faturado">
							<?
							$ifaturas = 1;
							while ($faturas = sqlsrv_fetch_array($sql_faturas)) {
								
								$faturas_parcelas = $faturas['PARCELAS'];
								$faturas_valor = number_format($faturas['LF_VALOR'], 2, ",", ".");

								if($ifaturas > 1) echo ' + ';
								echo $faturas_parcelas.'x R$ '.$faturas_valor;
								$faturas_total += $faturas_parcelas;

								$ifaturas++;
							}
							?>
							</p>
							<?
						}

					}
					
					//Drop down para imprimir
					if(!$loja_cancelado) {
					
					?>
					<section class="menu-impressao fade">
						<a href="#" class="arrow"></a>

						<ul class="drop">
							<li><a href="<? echo SITE; ?>financeiro/etiqueta/<? echo $loja_cod; ?>/" class="etiqueta" title="Imprimir envelope do voucher <? echo $loja_cod; ?>?" target="_blank">Envelope do voucher</a></li>
							<li><a href="<? echo SITE; ?>financeiro/imprimir/<? echo $loja_cod; ?>/" class="print" title="Imprimir voucher <? echo $loja_cod; ?>?" target="_blank">Voucher impressão</a></li>
							<li><a href="<? echo SITE; ?>financeiro/imprimir/<? echo $loja_cod; ?>/entrega/" class="print entrega" title="Imprimir voucher entrega <? echo $loja_cod; ?>?" target="_blank">Voucher entrega</a></li>
							<li><a href="<? echo SITE; ?>financeiro/caderno/<? echo $loja_cod; ?>/" class="print entrega" title="Imprimir voucher caderno <? echo $loja_cod; ?>?" target="_blank">Controle interno</a></li>
							<li><a href="<? echo SITE; ?>financeiro/recibo/<? echo $loja_parceiro; ?>/" class="recibo" title="Imprimir recibo de comissão do parceiro?" target="_blank">Recibo de comissão</a></li>
							<? if(!empty($loja_tid)) { ?><li><span class="tid">TID: <? echo $loja_tid; ?></span></li><? } ?>
						</ul>
					</section>
					<?

					} elseif(!empty($loja_tid)) {

					?>
					<section class="menu-impressao fade">
						<a href="#" class="arrow"></a>

						<ul class="drop">
							<li><span class="tid">TID: <? echo $loja_tid; ?></span></li>
						</ul>
					</section>
					<?
					}

					if($cartao_credito/* && $loja_cielo_v2*/) {?>
						<section class="menu-cartao fade">
							<a href="#" class="liberar arrow">Info. cartão</a>

							<ul class="drop">
								<li><strong>Cartão de crédito:</strong> <? echo substr($loja_cielo_v2_numero_cartao, 0,4)." ".substr($loja_cielo_v2_numero_cartao, 4,4)." ".substr($loja_cielo_v2_numero_cartao, 8,4)." ".substr($loja_cielo_v2_numero_cartao, 12); ?></li>
								<li><strong>Nome do cliente:</strong> <? echo $loja_cielo_v2_nome; ?></li>
								<li><strong>CPF/CNPJ:</strong> <? echo $loja_cielo_v2_cpf; ?></li>
								<li><strong>E-mail:</strong> <? echo $loja_email; ?></li>
								
								<? if(!empty($loja_telefone)): ?>
									<li><strong>Telefone:</strong> <? echo "+".$paises[$loja_ddi]['ddi']." ".$loja_ddd." ".$loja_telefone; ?></li>
								<? endif; ?>
								<? if(!empty($loja_celular)): ?>
									<li><strong>Celular:</strong> <? echo "+".$paises[$loja_ddi_celular]['ddi']." ".$loja_ddd_celular." ".$loja_celular; ?></li>
								<? endif; ?>


								<? if(!empty($loja_tid)) { ?><li class="tid"><strong class="tid">TID:</strong> <? echo $loja_tid; ?></li><? } ?>
								<li><strong>Endereço de Cobrança:</strong>
								<?php 
									if(!empty($loja_cep))
									{ 
										echo $loja_pais=="BR"?"CEP: ":"Zipcode: ";
									}
									echo $loja_cep."</br>".$loja_endereco.", ".$loja_numero.", ".$loja_bairro."</br>"; 
									echo $loja_cidade." - ".$loja_estado;
									echo !empty($loja_complemento)?"</br>".$loja_complemento:"";
								?>
								</li>
								<?php  ?>
								<li class="antifraude detalhes">
									<?php if($antifraudeAprovado){ ?>
											<strong>Anti Fraude:</strong>
											<span class="status <?php echo $aprovado_score_cor ?>"></span>
											<span class="titulo">Analisado</span>
											<p class="score"><b>Score: </b><?php echo number_format($loja_antifraude_score, 2, ',', '.')."%"; ?> 
											<?php switch (true) {
												case $loja_antifraude_score<30:
													echo "(Risco Baixo)";
													break;
												case $loja_antifraude_score<60:
													echo "(Risco Médio)";
													break;

												case $loja_antifraude_score<90:
													echo "(Risco Alto)";
													break;

												case $loja_antifraude_score<100:
													echo "(Risco Crítico)";
													break;
												
												default:
													echo "(Risco Desconhecido)";
													break;
											} ?></p>

											<button  title="Atualizar Antifraude" class="acao pos-atualizacao" data-cod="<?php echo $loja_cod ?>" data-acao="consultar"><img src="<?php echo SITE ?>img/verificar_compra.png"></button>

									<? }else if($antifraudeReprovado){?>
											<strong>Anti Fraude:</strong>
											<span class="status nao-analisado"></span>
											<span class="titulo">Reprovado</span>
											<p class="score">Score: <b><?php echo $loja_antifraude_score; ?> 
											<?php switch (true) {
												case $loja_antifraude_score<30:
													echo "(Risco Baixo)";
													break;
												case $loja_antifraude_score<60:
													echo "(Risco Médio)";
													break;

												case $loja_antifraude_score<90:
													echo "(Risco Alto)";
													break;

												case $loja_antifraude_score<100:
													echo "(Risco Crítico)";
													break;
												
												default:
													echo "(Risco Desconhecido)";
													break;
											} ?></b></p>

											<button  title="Atualizar Antifraude" class="acao pos-atualizacao" data-cod="<?php echo $loja_cod ?>" data-acao="consultar"><img src="<?php echo SITE ?>img/verificar_compra.png"></button>

									<?php } else if($antifraudeAnalisando){ ?>
											<strong>Anti Fraude:</strong>
											<span class="status nao-analisado"></span>
											<span class="titulo">Aguardando análise</span>
											<button  title="Atualizar Antifraude" class="acao" data-cod="<?php echo $loja_cod ?>" data-acao="consultar"><img src="<?php echo SITE ?>img/verificar_compra.png"></button>
											<?php if(!empty($loja_antifraude_quiz_url)){ ?>
												<a href="<?php echo SITE ?>ClearSale/paginas/enviar_questionario.php?cod=<?php echo $loja_cod ?>" class="btn-quiz quiz active">Enviar questionário ao cliente</a>
											<?php } ?>
											
									<?php } else { ?>
											<strong>Anti Fraude:</strong>
											<span class="status nao-analisado"></span>
											<span class="titulo">Não analisado</span>
											<button  title="Atualizar Antifraude" class="acao" data-cod="<?php echo $loja_cod ?>" data-acao="enviar"><img src="<?php echo SITE ?>img/verificar_compra.png"></button>
											<p class="score"></p>
											<a href="<?php echo SITE ?>ClearSale/paginas/enviar_questionario.php?cod=<?php echo $loja_cod ?>" class="btn-quiz quiz">Enviar questionário ao cliente</a>
											
									<? } ?>
									
								</li>
							</ul>
						</section>
						<?
					}

					if(!$loja_cancelado && $administrador && $cartao_credito && !$loja_pago)  {

						//Captura de pagamento
						if(($loja_status_transacao == 4) && ($loja_diferenca_dias > -1)) {

							if($loja_cielo_v2) {
								?>
								<!-- <a href="https://cieloecommerce.cielo.com.br/Backoffice/Merchant/Order?OrderNumber=<? echo $loja_checkoutid; ?>&PageSize=50&PageIndex=1" target="_blank" class="liberar confirm" title="Confirmar o pagamento da compra <? echo $loja_cod; ?> (<? echo $loja_diferenca_dias; ?> dia<? echo $loja_diferenca_dias != 1 ? 's' : ''; ?> para expirar o prazo de confirmação)?">Confirmar (<? echo $loja_diferenca_dias; ?>)</a> -->
								<a href="<?php echo SITE."compra/captura/$loja_cod/" ?>" class="liberar confirm 
								<?php if($antifraudeAprovado){ 
									echo "aprovado";
								}else if($antifraudeReprovado){
									echo "reprovado";
								} else if($antifraudeAnalisando){ 
									echo "analisando";
								} else { 
									echo "nao-analisado";
								} 
								?>
								" title="Confirmar o pagamento da compra <? echo $loja_cod; ?> (<? echo $loja_diferenca_dias; ?> dia<? echo $loja_diferenca_dias != 1 ? 's' : ''; ?> para expirar o prazo de confirmação)?">Confirmar (<? echo $loja_diferenca_dias; ?>)</a>
								<?
							} else {							
								?>
								<a href="<? echo SITE; ?>compra/captura/<? echo $loja_cod; ?>/" class="liberar confirm 
								<?php if($antifraudeAprovado){ 
									echo "aprovado";
								}else if($antifraudeReprovado){
									echo "reprovado";
								} else if($antifraudeAnalisando){ 
									echo "analisando";
								} else { 
									echo "nao-analisado";
								} 
								?>" title="Confirmar o pagamento da compra <? echo $loja_cod; ?> (<? echo $loja_diferenca_dias; ?> dia<? echo $loja_diferenca_dias != 1 ? 's' : ''; ?> para expirar o prazo de confirmação)?">Confirmar (<? echo $loja_diferenca_dias; ?>)</a>
								<?
							}

						//Pagar compra sem status
						} else {
							?>
							<a href="<? echo SITE; ?>compras/pagamento/v2/<? echo $loja_cod; ?>/" class="liberar pagar confirm" title="Realizar o pagamento da compra <? echo $loja_cod; ?>?">Pagar</a>
							<?

							// Permitir apenas compras do Folia Tropical e Super Folia 

							$sql_item_paypal = sqlsrv_query($conexao, " SELECT COUNT(LI_COD) AS QTDE, LI_VALOR, LI_INGRESSO, MIN(LI_COD) AS COD, MAX(LI_EXCLUSIVIDADE) AS EXCLUSIVIDADE, MAX(LI_EXCLUSIVIDADE_VAL) AS EXCLUSIVIDADE_VAL FROM loja_itens WHERE LI_COMPRA='$loja_cod' AND D_E_L_E_T_='$loja_cancelado_int' GROUP BY LI_INGRESSO, LI_VALOR", $conexao_params, $conexao_options);
							$n_paypal_folia = $n_paypal_outros = 0;


							if(sqlsrv_num_rows($sql_item_paypal) > 0) {
								
								while ($item_paypal = sqlsrv_fetch_array($sql_item_paypal)) {
										
									$item_paypal_ingresso = $item_paypal['LI_INGRESSO'];
									
									//Informações adicionais do item
									$sql_info_item_paypal = sqlsrv_query($conexao, "
									SELECT tp.TI_TAG 
									FROM vendas v, eventos_setores es, eventos_dias ed, tipos tp 
									WHERE v.VE_COD='$item_paypal_ingresso' AND es.ES_COD=v.VE_SETOR AND ed.ED_COD=v.VE_DIA AND v.VE_TIPO=tp.TI_COD", $conexao_params, $conexao_options);

									if(sqlsrv_num_rows($sql_info_item_paypal) > 0) {
										$info_item_paypal = sqlsrv_fetch_array($sql_info_item_paypal);
									
										$item_paypal_tipo_tag = $info_item_paypal['TI_TAG'];
										
										if(($item_paypal_tipo_tag == 'lounge') || ($item_paypal_tipo_tag == 'super')) $n_paypal_folia++;											
										else $n_paypal_outros++;
									}
								}
							}

							if(($n_paypal_folia > 0) && ($n_paypal_outros == 0)) {
							?>
							<a href="<? echo SITE; ?>compras/pagamento/paypal/<? echo $loja_cod; ?>/" class="liberar pagar confirm" title="Realizar o pagamento da compra <? echo $loja_cod; ?>?">Paypal</a>
							<?							
							}

						}					
						
						//Atualizar informações da compra
						//if(!empty($loja_xml) && !(($loja_status_transacao == 4) && ($loja_diferenca_dias > -1))) {
						if(!empty($loja_xml)) {
						?>
						<a href="<? echo SITE; ?>compra/atualizar/<? echo $loja_cod; ?>/" class="atualizar confirm" title="Atualizar as informações do pagamento da compra <? echo $loja_cod; ?>?"></a>
						<?
						}
					}

					/*if($multiplo && !$loja_cancelado && !$loja_pago && $administrador) {
						?>
							<a href="<? echo SITE; ?>compras/pagamento-multiplo/<? echo $loja_cod; ?>/" class="liberar confirm" title="Confirmar o pagamento da compra <? echo $loja_cod; ?>?">Editar</a>
						<?
					}*/
					
					//Faturado
					if($faturado) {
						
						/*$faturas_total = 0;

						//Buscar faturas
						$sql_faturas = sqlsrv_query($conexao, "SELECT LF_VALOR, COUNT(LF_COD) AS PARCELAS FROM loja_faturadas WHERE LF_COMPRA='$cod' AND D_E_L_E_T_='0' GROUP BY LF_VALOR", $conexao_params, $conexao_options);
						$n_faturas = sqlsrv_num_rows($sql_faturas);

						if($n_faturas > 0) {
							?>
							<p class="faturado">
							<?
							$ifaturas = 1;
							while ($faturas = sqlsrv_fetch_array($sql_faturas)) {
								
								$faturas_parcelas = $faturas['PARCELAS'];
								$faturas_valor = number_format($faturas['LF_VALOR'], 2, ",", ".");

								if($ifaturas > 1) echo ' + ';
								echo $faturas_parcelas.'x R$ '.$faturas_valor;
								$faturas_total += $faturas_parcelas;

								$ifaturas++;
							}
							?>
							</p>
							<?
						}*/
						
						if(!$loja_cancelado && !$loja_pago && $administrador){
						?>
						<a href="<? echo SITE; ?>financeiro/faturado/<? echo $loja_cod; ?>/" class="liberar" title="Confirmar o pagamento da compra <? echo $loja_cod; ?>?">Confirmar</a>
						<?						
						}

					}

					if(!$loja_cancelado && !$loja_pago && !$cartao_credito && !$multiplo && !$faturado && !$reserva && $administrador) {
						?>
						<a href="<? echo SITE; ?>e-financeiro-gerenciar.php?c=<? echo $loja_cod; ?>&a=confirmar" class="liberar confirm" title="Confirmar o pagamento da compra <? echo $loja_cod; ?>?">Confirmar</a>
						<?
					}

					
					if ($loja_pago){ ?> <span class="pago">Pago</span><? }

					

					if ($multiplo){ ?><a class="liberar" href="https://ingressos.foliatropical.com.br/controle2014/compras/pagamento-multiplo/<?=$loja_cod?>" style="margin-right: 10px">Editar</a><? }

					/*if ($loja_forma==10 && $loja_pago){ ?><a class="liberar" href="https://ingressos.foliatropical.com.br/controle2014/compras/pagamento-multiplo/<?=$loja_cod?>" style="margin-right: 10px">Editar</a><? }*/
					if ($loja_cancelado){ ?> <span class="cancelado">Cancelado</span><? } ?>

				</div>

				<div class="clear"></div>
			</section>

			<?

			if(!$loja_pago) {
				$cupom_permitir = true;

				if(($loja_status_transacao == 4) && ($loja_diferenca_dias > -1)) $cupom_permitir = false;
				if($faturado && ($faturas_total > 0)) $cupom_permitir = false;
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

			} 

			
			
			//if (!$loja_desconto || $loja_camisas){
			?>
			<section id="financeiro-cupom-camisas">

				<section id="comissao-retida" class="checkbox verify coluna">
					<ul><li><a href="<? echo SITE; ?>e-comissao-gerenciar.php?c=<? echo $loja_cod; ?>&a=<? echo $loja_comissao_retida ? 'cancelar' : 'confirmar' ; ?>" class="item <? if($loja_comissao_retida) { echo 'checked'; } ?>">Comissão retida</a></li></ul>
					<div class="clear"></div>
				</section>

				<? if((!$loja_comissao_retida && !$loja_comissao_paga) || !$loja_vendedor) { ?>
				<section id="cupom-parceiro" class="financeiro">
					<form class="controle" id="form-cupom-parceiro" action="<? echo SITE; ?>financeiro/detalhes/cupom-parceiro/" method="post">
						<input type="hidden" name="cod" value="<? echo $cod; ?>" />
						<input type="hidden" name="financeiro" value="true" />
						<p>
							<label for="compra-parceiro" class="infield">Código parceria:</label>
							<input type="text" name="cupom" class="input" id="compra-parceiro" />
							<input type="submit" class="submit adicionar" value="Ok" />
						</p>
					</form>
				</section>
				<? } ?>
				
				<? if (!$loja_desconto){ ?>
				<section id="cupom-pagamento" class="financeiro">
					<? if ($cupom_cod > 0){ ?>					
						<span class="cupom">
							<? echo $cupom_nome; ?> •  <? echo $cupom_codigo; ?> <? if (!(0 === strpos($cupom_codigo, 'FOLIA'))) { ?> • Desconto de  <? echo ($cupom_tipo == 1) ? round($cupom_valor)."%" : 'R$ '.number_format($cupom_valor, 2, ',', '.'); } ?>
							<? if($cupom_permitir) { ?><a href="<? echo SITE; ?>financeiro/detalhes/cupom/remover/<? echo $cupom_cod; ?>/<? echo $cod; ?>/" class="excluir confirm" title="Deseja remover o cupom &rdquo;<? echo $cupom_nome; ?>&ldquo;">&times;</a><? } ?>
						</span>
					<? } elseif($cupom_permitir && !$loja_cancelado) { ?>
					<form class="controle" id="form-cupom-pagamento" action="<? echo SITE; ?>financeiro/detalhes/cupom/" method="post">
						<input type="hidden" name="cod" value="<? echo $cod; ?>" />
						<input type="hidden" name="cliente" value="<? echo $loja_cliente; ?>" />
						<input type="hidden" name="financeiro" value="true" />
						<p>
							<label for="compra-cupom">Cupom de desconto:</label>
							<input type="text" name="cupom" class="input" id="compra-cupom" />
							<input type="submit" class="submit adicionar" value="Ok" />
						</p>
					</form>
					<? } ?>
				</section>
				<? } ?>

				<? if (!$loja_cancelado && $loja_camisas){ ?><a href="<? echo SITE; ?>financeiro/detalhes/camisas/<? echo $loja_cod; ?>/" class="cadastrar-camisas fancybox fancybox.iframe width600"></a><? } ?>

			</section>
			<? //} ?>

			<section id="financeiro-detalhes-itens" class="secao">
			<?

			$sql_item = sqlsrv_query($conexao, " SELECT COUNT(LI_COD) AS QTDE, LI_OVER_INTERNO, LI_VALOR_TRANSFER, LI_VALOR, LI_INGRESSO, MIN(LI_COD) AS COD, MAX(LI_EXCLUSIVIDADE) AS EXCLUSIVIDADE, MAX(LI_EXCLUSIVIDADE_VAL) AS EXCLUSIVIDADE_VAL FROM loja_itens WHERE LI_COMPRA='$loja_cod' AND D_E_L_E_T_='$loja_cancelado_int' GROUP BY LI_INGRESSO, LI_VALOR, LI_OVER_INTERNO, LI_VALOR_TRANSFER", $conexao_params, $conexao_options);

			if(sqlsrv_num_rows($sql_item) > 0) {
				$i = 1;
				$item_count = 1;

				while ($item = sqlsrv_fetch_array($sql_item)) {
						
					// $item_id = $item['LI_ID'];
					// $item_nome = utf8_encode($item['LI_NOME']);

					$item_cod = $item['COD'];
					$item_qtde = $item['QTDE'];
					$item_ingresso = $item['LI_INGRESSO'];
					$item_valor =  number_format($item['LI_VALOR'], 2, ",", ".");
					$item_valor_f =  $item['LI_VALOR'];
					$item_over_interno = $item['LI_OVER_INTERNO'];
					$item_transfer = $item['LI_VALOR_TRANSFER'];
					$item_exclusividade = (bool) $item['EXCLUSIVIDADE'];
					$item_exclusividade_val = $item['EXCLUSIVIDADE_VAL'];

					//Informações adicionais do item
					$sql_info_item = sqlsrv_query($conexao, "
					SELECT v.VE_DIA, v.VE_SETOR, v.VE_FILA, v.VE_VAGAS, v.VE_TIPO_ESPECIFICO, es.ES_NOME, ed.ED_NOME, ed.ED_DATA, SUBSTRING(CONVERT(VARCHAR, ed.ED_DATA, 103), 1, 5) AS dia, tp.TI_NOME, tp.TI_TAG 
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

						$item_fechado = (($item_vaga > 0) && ($item_tipo_especifico == 'fechado')) ? true : false;

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

						if($evento > 1) {
							unset($loja_atual_frisa);
							if($item_tipo_tag == 'frisa'){

								$loja_frisa_fechadas = floor($item_qtde / 6);
								if($loja_frisa_fechadas > 0) {
									$loja_qtde_frisa = $loja_qtde_frisa + $loja_frisa_fechadas;
									$loja_enable_frisa = true;
								}

							}
						}
					}

					// loja_itens_adicionais
					if($item_fechado) { 
						$item_qtde = $item_qtde / $item_vaga;
						$item_valor = $item_valor_f-$item_over_interno+($item_over_interno*$item_vaga)+($item_transfer*$item_vaga);
						$item_valor = number_format($item_valor, 2, ",", ".");
					}

				?>
				<section class="item-carrinho">
					
					<header>
						<strong>Qtde. <? echo $item_qtde; ?></strong> &ndash; 
						<?
							echo $item_tipo;
							if(!empty($item_fila)) { echo " ".$item_fila; }
							if(!empty($item_tipo_especifico)) { echo " ".$item_tipo_especifico; }
							if($item_fechado) { echo " (".$item_vaga." vagas)"; }
						?>
						<span class="valor">R$ <? echo $item_valor; ?></span>
					</header>
					
					<div class="cliente">
						<? echo $item_dia; ?> dia (<? echo $item_data; ?>) &ndash; Setor <? echo $item_setor; ?>
						<a href="<? echo SITE; ?>ingressos/comentario/novo/<? echo $item_cod; ?>/" class="comentario fancybox fancybox.iframe width600"></a>
					</div>
					
					<table class="lista compras-adicionais">
						<tbody>
						<?

						//Exclusividade
						if($item_exclusividade) {
						?>
						<tr class="incluso">
							<td class="check">&nbsp;</td>
							<td class="nome">Exclusividade - <? echo $item_tipo; ?> <? if(!empty($item_exclusividade_val)) { ?> na fila <? echo utf8_encode($item_exclusividade_val); } ?></td>
						</tr>
						<?
						}

						// $sql_adicionais = sqlsrv_query($conexao, "SELECT lia.LIA_COD, v.* FROM loja_itens_adicionais lia, vendas_adicionais v WHERE v.VA_COD=lia.LIA_ADICIONAL AND lia.LIA_COMPRA='$cod' AND lia.LIA_ITEM='$item_cod'", $conexao_params, $conexao_options);
						$sql_adicionais = sqlsrv_query($conexao, "SELECT MAX(lia.LIA_COD) AS LIA_COD, COUNT(lia.LIA_COD) AS QTDE, MAX(v.VA_LABEL) AS VA_LABEL, MAX(v.VA_NOME_EXIBICAO) AS VA_NOME_EXIBICAO
							FROM loja_itens_adicionais lia, vendas_adicionais v 
							WHERE v.VA_COD=lia.LIA_ADICIONAL AND lia.LIA_COMPRA='$cod' 
							AND lia.LIA_ITEM IN (SELECT LI_COD FROM loja_itens WHERE LI_COMPRA='$loja_cod' AND LI_INGRESSO='$item_ingresso' AND D_E_L_E_T_='0')
							AND lia.D_E_L_E_T_='0'
							GROUP BY VA_COD
							", $conexao_params, $conexao_options);
						if(sqlsrv_num_rows($sql_adicionais) > 0) {

							while ($vendas_adicionais = sqlsrv_fetch_array($sql_adicionais)) {
								$vendas_adicionais_label = utf8_encode($vendas_adicionais['VA_LABEL']);
								$vendas_adicionais_nome_exibicao = $vendas_adicionais['VA_NOME_EXIBICAO'];
								$vendas_adicionais_qtde = $vendas_adicionais['QTDE'];
								
								if($vendas_adicionais_nome_exibicao == 'delivery'){
									$vendas_adicionais_delivery['label'] = $vendas_adicionais_label;
								} else {


							?>
							<tr class="incluso">
								<td class="check">&nbsp;</td>
								<td class="nome"><? if ($item_fechado && ($vendas_adicionais_nome_exibicao == 'transfer')){ echo "Qtde. ".$vendas_adicionais_qtde." - "; } echo $vendas_adicionais_label; ?></td>
							</tr>								
							<?
								}

							}

						}
						
						?>
						</tbody>
					</table>

				</section>


				<? } ?>

				<section class="secao comentarios_internos" style="padding: 0 !important">
				<?

				//-------------------------------------------------------
				//Exibe os comentários quando a compra estiver cancelada
				//-------------------------------------------------------

				if ($loja_cancelado):

				//Buscar comentarios
				$sql_item_comentario = sqlsrv_query($conexao, "SELECT TOP 1 LC_COMENTARIO FROM loja_comentarios WHERE LC_COMPRA=$loja_cod ORDER BY LC_COD DESC", $conexao_params, $conexao_options);

				if(sqlsrv_num_rows($sql_item_comentario) > 0)
				{
					$ar_item_comentario = sqlsrv_fetch_array($sql_item_comentario);						
				}


				//Buscar comentarios internos 
				$sql_item_comentario_interno = sqlsrv_query($conexao, "SELECT TOP 1 LC_COMENTARIO FROM loja_comentarios_internos WHERE LC_COMPRA=$loja_cod ORDER BY LC_COD DESC", $conexao_params, $conexao_options);

				if(sqlsrv_num_rows($sql_item_comentario_interno) > 0)
				{
					$ar_item_comentario_interno = sqlsrv_fetch_array($sql_item_comentario_interno);					
				}

					
				?>

				<p class="comentarios">
					<label for="carrinho-comentarios-<? echo $key; ?>">Comentários sobre <? echo $item_tipo;
							if(!empty($item_fila)) { echo " ".$item_fila; }
							if(!empty($ingressos_tipo_especifico)) { echo " ".$item_tipo_especifico; }
							if(($item_vaga > 0) && ($item_tipo_especifico == 'fechado')) { echo " (".$item_vaga." vagas)"; }
						?>:
					</label>
					<textarea readonly name="comentarios[<? echo $key; ?>]" class="input" id="carrinho-comentarios-<? echo $key; ?>" rows="3"><? echo utf8_encode($ar_item_comentario['LC_COMENTARIO']); ?></textarea>
				</p>
				<p class="comentarios interno">
					<label for="carrinho-comentarios-internos-<? echo $key; ?>">Comentários Internos:</label>
					<textarea readonly name="comentariosinternos[<? echo $key; ?>]" class="input" id="carrinho-comentarios-internos-<? echo $key; ?>" rows="3"><? echo utf8_encode($ar_item_comentario_interno['LC_COMENTARIO']); ?></textarea>							
				</p>
			<?
			endif;
			}
			?>

			</section>

			<? if($vendas_adicionais_delivery || $loja_desconto || !empty($loja_retirada) || $loja_entregue) {
			?>
			<section class="item-carrinho extra">
				<header>Informações extra</header>
				<table class="lista compras-adicionais">
					<tbody>
						<? if($vendas_adicionais_delivery) { ?>
						<tr class="incluso">
							<td class="check">&nbsp;</td>
							<td class="nome"><? echo $vendas_adicionais_delivery['label']; ?></td>
							<td class="valor">R$ <? echo $lo_valor_delivery_f; ?></td>
						</tr>
						<? }

						if(!empty($loja_retirada)) {
						?>
						<tr class="incluso">
							<td class="check">&nbsp;</td>
							<td class="nome" colspan="2">
								Retirada do Ingresso: <? echo $loja_data_retirada; ?> - <? echo ucfirst($loja_retirada); ?> - <? echo ucfirst($loja_periodo); ?>
								<? if($loja_entregue) echo '('.$loja_data_entrega.' - '.$loja_entregue_nome.')'; ?>
							</td>
						</tr>
						<?
						} elseif($loja_entregue) {
						?>
						<tr class="incluso">
							<td class="check">&nbsp;</td>
							<td class="nome" colspan="2">
								Entregue: <? echo $loja_data_entrega.' - '.$loja_entregue_nome; ?>
							</td>
						</tr>
						<?
						}

						if($loja_entregue) {
							$loja_data_entrega = $loja['DATA_ENTREGA'];
							$loja_entregue_nome = utf8_encode($loja['LO_ENTREGUE_NOME']);			
						}
						
						/*if($loja_desconto) {
						?>
						<tr class="incluso">
							<td class="check">&nbsp;</td>
							<td class="nome">Combo 2 dias na Folia (Desconto de 10%)</td>
						</tr>
						<? }*/

						//if($loja_parceiro == 54) {

							$loja_combo_desconto = 0;

							/*if($loja_qtde_folia >= 2) {
								$loja_combo_desconto = 10;
								$loja_combo_nome = "Combo 2 dias na Folia (Desconto de 10%)";
							} else {*/
								foreach ($loja_qtde_combo as $k => $r) {
									if(($r == $combo_dias[$k]['total']) && ($combo_dias[$k]['desconto'] > $loja_combo_desconto)) {
										$loja_combo_desconto = $combo_dias[$k]['desconto'];
										$loja_combo_nome = $combo_dias[$k]['nome'].' (Desconto de '.str_replace('.', ',', round($loja_combo_desconto, 1)).'%)';
									}
								}
							/*}*/

							
							if($loja_desconto_folia && ($loja_combo_desconto > 0)) {
							?>
							<tr class="incluso">
								<td class="check">&nbsp;</td>
								<td class="nome"><? echo $loja_combo_nome; ?></td>
								<td class="valor">- <? echo str_replace('.', ',', round($loja_combo_desconto, 1)); ?>%</td>
							</tr>
							<?
							}

						//}
						
						if($loja_desconto_frisa && $loja_enable_frisa) {
						?>
						<tr class="incluso">
							<td class="check">&nbsp;</td>
							<td class="nome">Desconto para Frisa fechada</td>
							<td class="valor">- R$ <? echo number_format(($loja_qtde_frisa * 50), 2, ',', '.'); ?></td>
						</tr>
						<?
						}

						?>
					</tbody>
				</table>
			</section>
			<?
			}
			?>
			</section>
			<footer class="controle">
				<? if (!$loja_cancelado && $administrador){ ?>
					<a href="<? echo SITE; ?>compras/alterar/<? echo $loja_cod; ?>/" class="button coluna big">Alterar tipos</a>
					<? /*if($reserva) {*/ ?><a href="<? echo SITE; ?>compras/modificar/<? echo $loja_cod; ?>/limpar/" class="button coluna modificar big">Modificar compra</a><? /*}*/ ?>
					
					<?
					//validacao antiga paga cielo não v2
					// $link_cancelar = ($loja_pago && $cartao_credito && ($loja_status_transacao == 6) && !$loja_cielo_v2) ? 'cancelar' : 'excluir';
					// $texto_cancelar = ($loja_pago && $cartao_credito && ($loja_status_transacao == 6) && $loja_cielo_v2) ? ' O pedido deverá ser cancelado também no Backoffice da Cielo' : ''; 
					?>

					<a href="<? echo SITE; ?>compras/cancelar/<? echo $loja_cod; ?>/" class="button cancelar-compra coluna big confirm" title="Deseja realmente cancelar a compra?<? echo $texto_cancelar; ?>">Cancelar compra</a>
				<? } if($loja_cancelado && $administrador) { ?>
					<a href="<? echo SITE; ?>compras/reativar/<? echo $loja_cod; ?>/" class="button coluna big confirm" title="Deseja realmente reativar a compra?">Reativar</a>			
				<? } ?>
				<a href="<? echo $_SERVER['HTTP_REFERER'];  /*strpos($_SERVER['HTTP_REFERER'], 'financeiro') ? $_SERVER['HTTP_REFERER'] : SITE.'financeiro/';*/ ?>" class="cancel coluna">Voltar</a>
				<div class="clear"></div>
			</footer>

			<section id="financeiro-pendencias">
				<form name="pendencias" method="post" action="<? echo SITE; ?>compras/pendencias/">
					
					<input type="hidden" name="cod" value="<? echo $loja_cod; ?>">

					<h2>Pendências</h3>
					<section id="compras-pendencias" class="checkbox verify">
						<ul>
							<?

							//Buscar pendencias
							$sql_pendencias = sqlsrv_query($conexao, "SELECT * FROM pendencias WHERE PE_BLOCK=0 AND D_E_L_E_T_=0", $conexao_params, $conexao_options);
							if(sqlsrv_num_rows($sql_pendencias) > 0) {

								while ($pendencias = sqlsrv_fetch_array($sql_pendencias)) {
									
									$pendencias_cod = $pendencias['PE_COD'];
									$pendencias_nome = utf8_encode($pendencias['PE_NOME']);

									//Buscar pendencias
									$sql_pendencias_ins = sqlsrv_query($conexao, "SELECT LP_COD FROM loja_pendencias WHERE LP_COMPRA='$loja_cod' AND LP_PENDENCIA='$pendencias_cod' AND LP_BLOCK=0 AND D_E_L_E_T_=0", $conexao_params, $conexao_options);
									$pendencias_check = (sqlsrv_num_rows($sql_pendencias_ins) > 0) ? true : false;

									?>
									<li><label class="item <? if($pendencias_check) { echo 'checked'; } ?>"><input type="checkbox" name="pendencias[]" value="<? echo $pendencias_cod; ?>" <? if($pendencias_check) { echo 'checked="checked"'; } ?> /><? echo $pendencias_nome; ?></label></li>
									<?
								}
							}

							?>
						</ul>
						<div class="clear"></div>
					</section>

					<footer class="controle">
						<input type="submit" class="submit" value="Confirmar" />
						<div class="clear"></div>
					</footer>
				</form>
			</section>

			<!-- <section id="financeiro-documentos">
				<form name="documentos" method="post" action="<? echo SITE; ?>e-documento.php" enctype="multipart/form-data">
					
					<input type="hidden" name="cod" value="<? echo $loja_cod; ?>">

					<h2>Documentos</h3>

					<?
					//buscar documentos cadastrados nessa compra
					$sql_documentos = sqlsrv_query($conexao, "SELECT * FROM loja_documentos WHERE DO_COMPRA=$cod ORDER BY DO_DATA DESC", $conexao_params, $conexao_options);
					$n_documentos = sqlsrv_num_rows($sql_documentos);

					if($n_documentos > 0) {
					?>
					<ul class="lista-documentos">
					<?
						while ($documentos = sqlsrv_fetch_array($sql_documentos)) {
							$documento_tipo = utf8_encode($documentos['DO_TIPO']);
							$documento_arquivo = $documentos['DO_ARQUIVO'];
						?>
						<li>
							<a href="<? echo SITE; ?>documentos/<? echo $documento_arquivo; ?>" target="_blank">
								<div class="img"><img src="<? echo SITE; ?>documentos/<? echo $documento_arquivo; ?>" /></div>
								<p><? echo $documento_tipo; ?></p>
							</a>
						</li>
						<?
						}
					?>
						<div class="clear"></div>
					</ul>
					<?
					}
					?>

					<section class="selectbox coluna" id="tipo">
						<h3>Tipo</h3>

						<a href="#" class="arrow"><strong>CPF</strong><span></span></a>
						<ul class="drop">
							<li><label class="item checked"><input type="radio" name="tipo" value="CPF" alt="CPF" checked="checked" />CPF</label></li>
							<li><label class="item"><input type="radio" name="tipo" value="ID" alt="ID" />ID</label></li>
							<li><label class="item"><input type="radio" name="tipo" value="Passaporte" alt="Passaporte" />Passaporte</label></li>
							<li><label class="item"><input type="radio" name="tipo" value="Cartão de Crédito" alt="Cartão de Crédito" />Cartão de Crédito</label></li>
							<li><label class="item"><input type="radio" name="tipo" value="Outros" alt="Outros" />Outros</label></li>
						</ul>
					</section>

					<section class="coluna">
						<h3>Arquivo</h3>
						<input type="file" name="arquivo" class="arquivo" />
					</section>

					<footer class="controle">
						<input type="submit" class="submit" value="Enviar" />
						<div class="clear"></div>
					</footer>
				</form>
			</section> -->
			<?

			if($loja_delivery) {

			?>
			<section id="financeiro-delivery" class="secao">
				<h2>Delivery</h2>
				<?

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

					if(!empty($loja_endereco) && !empty($loja_numero) && !empty($loja_bairro)) {

					?>
					<p><? echo $loja_endereco; ?>, <? echo $loja_numero; ?> <? if (!empty($loja_complemento)){ echo '- '.$loja_complemento; } ?></p>
					<p><? if(!empty($loja_cep)) { ?>CEP: <? echo $loja_cep; ?> - <? } echo $loja_bairro; ?>, <? echo $loja_referencia; ?></p>
					<p><? echo $loja_data_para_entrega ?> - Período: <? echo $loja_periodo; ?></p>
					<p>A/C.: <? echo $loja_cuidados; ?> - <? echo $loja_celular; ?></p>
					<? if(!$loja_cancelado) { ?><a href="<? echo SITE; ?>compras/delivery/<? echo $loja_cod; ?>/detalhes/" class="button alterar">Alterar endereço de entrega</a><? } ?>
					<?

					} elseif(!$loja_cancelado) {
					?>
					<a href="<? echo SITE; ?>compras/delivery/<? echo $loja_cod; ?>/detalhes/" class="button">Cadastrar endereço de entrega</a>
					<?
					}

				?>
			</section>
			<?
			
			}

	}

	?>	
</section>
	
	<?
//Exibir endereços do cliente
$sql_enderecos = sqlsrv_query($conexao, "SELECT LO_CLIENTE, LO_CLI_ENDERECO, LO_CLI_NUMERO, LO_CLI_COMPLEMENTO, LO_CLI_BAIRRO, LO_CLI_CIDADE, LO_CLI_ESTADO, LO_CLI_CEP, LO_CLI_PAIS FROM loja WHERE LO_COD='$cod' AND LO_BLOCK='0' AND D_E_L_E_T_='0'", $conexao_params, $conexao_options);

?>
<section id="cliente-enderecos">
	<h1>Endereço da compra</h1>

	<ul>

	<?
		$enderecos = sqlsrv_fetch_array($sql_enderecos);

		$endereco_cod = utf8_encode($enderecos['LO_CLIENTE']);
		$endereco_endereco = utf8_encode($enderecos['LO_CLI_ENDERECO']);
		$endereco_numero = utf8_encode($enderecos['LO_CLI_NUMERO']);
		$endereco_complemento = utf8_encode($enderecos['LO_CLI_COMPLEMENTO']);
		$endereco_bairro = utf8_encode($enderecos['LO_CLI_BAIRRO']);
		$endereco_cidade = utf8_encode($enderecos['LO_CLI_CIDADE']);
		$endereco_estado = utf8_encode($enderecos['LO_CLI_ESTADO']);
		$endereco_cep = utf8_encode($enderecos['LO_CLI_CEP']);
		$endereco_pais = utf8_encode($enderecos['LO_CLI_PAIS']);
	
		if(!empty($enderecos['LO_CLI_ENDERECO'])):
	?>
		<li class="endereco open-modal" data-cod="<?php echo $cod ?>" data-endereco="<?php echo $endereco_endereco ?>" data-numero="<?php echo $endereco_numero ?>" data-complemento="<?php echo $endereco_complemento ?>" data-bairro="<?php echo $endereco_bairro ?>" data-cidade="<?php echo $endereco_cidade ?>" data-estado="<?php echo $endereco_estado ?>" data-cep="<?php echo $endereco_cep ?>" data-pais="<?php echo $endereco_pais ?>" data-tipo-endereco="<?php echo $endereco_tipo_endereco ?>">
			<!-- <h3><? echo $endereco_nome_destinatario; ?></h3> -->
			<p><? echo $endereco_endereco.', '.$endereco_numero; ?><br />
			<? echo $endereco_bairro; ?></p>
			<p><? echo $endereco_complemento; ?></p>
			<? if($endereco_cep): ?><p>CEP <? echo $endereco_cep; ?></p><? endif; ?>
			<p><? echo $endereco_cidade.', '.$endereco_estado; ?></p>
		</li>
	
	<? else: ?>
		<li class="endereco open-modal" data-cod="<?php echo $cod ?>" data-endereco="<?php echo $endereco_endereco ?>" data-numero="<?php echo $endereco_numero ?>" data-complemento="<?php echo $endereco_complemento ?>" data-bairro="<?php echo $endereco_bairro ?>" data-cidade="<?php echo $endereco_cidade ?>" data-estado="<?php echo $endereco_estado ?>" data-cep="<?php echo $endereco_cep ?>" data-pais="<?php echo $endereco_pais ?>" data-tipo-endereco="<?php echo $endereco_tipo_endereco ?>">
			<p>Incluir endereço da compra</p>
		</li>

	<? endif; ?>	

	</ul>
	</section>
</section>



<script>
	 $(document).on('click','.open-modal',function(){
        $("body").addClass("modal-open");
        $("#overlay,#modal").fadeIn("fast");
    });
    $(document).on('click', 'a.fechar-modal', function(){
        $("body").removeClass('modal-open');
        $("#overlay").fadeOut("fast");
        $("#modal").fadeOut("fast");
        return false;
    });

    $(document).ready(function() {

    	$('body #cliente-enderecos').on('click','.endereco',function(){
        //preencher o formulário
        $this=$(this);
        
        $('#modal #endereco-box').find('input[name="cod"]').val($this.data('cod')).blur();

        $('#modal #endereco-box').find('select[name="pais"]').val($this.data('pais')).trigger('change');
        // $('#modal #endereco-box').find('select[name="pais"]').select2("val", $this.data('pais'));
        // $('#modal #endereco-box').find('select[name="pais"]').trigger('change.select2');
        // $('#modal #endereco-box').find('select[name="pais"]').trigger('change');
        // console.log($this.data('pais'),$('#modal #endereco-box').find('select[name="pais"]').val());

        $('#modal #endereco-box').find('input[name="zipcode"]').val($this.data('cep'))

        if($this.data('pais')!="BR"){
        	$('#modal #endereco-box').find('input[name="cep"]').val('');
        	$('#modal #endereco-box').find('input[name="zipcode"]').val($this.data('cep'));
        }else{
        	$('#modal #endereco-box').find('input[name="cep"]').val($this.data('cep'));
        	$('#modal #endereco-box').find('input[name="zipcode"]').val('')
        }
        
        $('#modal #endereco-box').find('input[name="endereco"]').val($this.data('endereco')).blur();
        $('#modal #endereco-box').find('input[name="numero"]').val($this.data('numero')).blur();
        $('#modal #endereco-box').find('input[name="complemento"]').val($this.data('complemento')).blur();
        $('#modal #endereco-box').find('input[name="bairro"]').val($this.data('bairro')).blur();
        $('#modal #endereco-box').find('input[name="cidade"]').val($this.data('cidade')).blur();
        $('#modal #endereco-box').find('input[name="estado"]').val($this.data('estado')).blur();
        $('#modal #endereco-box').find('input[name="tipo_endereco"][value="'+$this.data('tipo-endereco')+'"]').trigger('click');
    });

        
        $('#salva_endereco').click(function() {

        	var checado = $(".check-endereco").is(":checked");
        	var id_endereco = $("input:radio:checked").val();

        	var erro = false;

            var cep = $('#cep').val();
            var zipcode = $('#zipcode').val();
            var cidade = $('#cidade').val();
            var estado = $('#estado').val();
            var bairro = $('#bairro').val();
            var endereco = $('#endereco').val();
            var numero = $('#numero').val();
            var complemento = $('#complemento').val();
            var cod = $('#cod').val();
            var pais = $('#pais').val();

            if (pais == "BR" && cep == "" && checado != true) {
            	erro = true;
            	//alert("Por gentileza, insira o cep!");
            	swal('','Por gentileza, insira o cep!','error');
            	
            }

             if (pais != "BR" && zipcode == "" && checado != true) {
            	erro = true;
            	//alert("Por gentileza, insira o cep!");
            	swal('','Por gentileza, insira o zipcode!','error');
            	
            }

            if (cidade == "" && checado != true) {
            	erro = true;
            	//alert("Por gentileza, insira a cidade!");
            	swal('','Por gentileza, insira a cidade!','error');
            	
            }

            if (estado == "" && checado != true) {
            	erro = true;
            	//alert("Por gentileza, insira o estado!");
            	swal('','Por gentileza, insira o estado!','error');
            	
            }

            if (pais == "BR" && bairro == "" && checado != true) {
            	erro = true;
            	//alert("Por gentileza, insira o bairro!");
            	swal('','Por gentileza, insira o bairro!','error');
            	
            }

            if (endereco == "" && checado != true) {
            	erro = true;
            	//alert("Por gentileza, insira o endereço!");
            	swal('','Por gentileza, insira o endereço!','error');
            	
            }

            // if (numero == "" && checado != true) {
            // 	erro = true;
            // 	//alert("Por gentileza, insira o número!");
            // 	swal('','Por gentileza, insira o número!','error');

            	
            // } 



            if (checado != true) {
	            var dados = "cep="+cep+"&zipcode="+zipcode+"&cidade="+cidade+"&estado="+estado+"&bairro="+bairro+"&endereco="+endereco+"&numero="+numero+"&complemento="+complemento+"&cod="+cod+"&pais="+pais;	
            } else {
            	var dados = "id_endereco="+id_endereco+"&cod="+cod;
            }


            if (erro == false) {
            	$.ajax({
	                type: 'POST',
	                dataType: 'json',
	                url: '<? echo SITE; ?>atualizar-endereco-compra.php',
	                async: true,
	                data: dados,
	                success: function(response) {

	                    if (response == true) {
	                    	//alert("Alterado com sucesso!");

	                    	swal('','Alterado com sucesso!' ,'success');
	                    	location.reload();

	                    } else {
	                    	//alert("Ocorreu um erro! Tente novamente.");
	                    	swal('','Ocorreu um erro! Tente novamente.','error');
	                    }
	                }
	            });
            }
        });

        $('#atualizar-risco').click(function() {

        	var cod = $('#cod').val();

        	$('.antifraude').empty();

        	$.ajax({
                type: 'POST',
                dataType: 'json',
                url: '<? echo SITE; ?>ClearSale/paginas/atualizar.php',
                async: true,
                data: 'cod='+cod,
                success: function(response) {
                  	console.log(response.score);                    
                }
            });
        });	
    });

</script>


<?

//-----------------------------------------------------------------//

include('include/footer.php');

//Fechar conexoes
include("conn/close.php");
include("conn/close-sankhya.php");

?>