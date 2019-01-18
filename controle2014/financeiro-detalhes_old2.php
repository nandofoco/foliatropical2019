<?

//Incluir funções básicas
include("include/includes.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");

unset($_SESSION['roteiro-itens']);

//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");
include("include/header.php");

//-----------------------------------------------------------------//

$evento = (int) $_SESSION['usuario-carnaval'];
$cod = (int) $_GET['c'];

//-----------------------------------------------------------------//

$sql_loja = sqlsrv_query($conexao, "SELECT TOP 1 l.*, (CONVERT(VARCHAR, l.LO_DATA_COMPRA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, l.LO_DATA_COMPRA, 108),1,5)) AS DATA, ISNULL(DATEDIFF (DAY, LO_DATA_PAGAMENTO, GETDATE()), 6) AS DIFERENCA FROM loja l, clientes c WHERE l.LO_EVENTO='$evento' AND l.LO_BLOCK='0' AND l.D_E_L_E_T_='0' AND l.LO_COD='$cod'", $conexao_params, $conexao_options);

?>
<section id="conteudo">
	<?
	if(sqlsrv_num_rows($sql_loja) > 0) {

		$loja = sqlsrv_fetch_array($sql_loja);

		$loja_cod = $loja['LO_COD'];
		$loja_cliente = $loja['LO_CLIENTE'];
		$loja_forma = $loja['LO_FORMA_PAGAMENTO'];
		$loja_diferenca_dias = (5 - $loja['DIFERENCA']);
		$loja_status_transacao = $loja['LO_STATUS_TRANSACAO'];
		$loja_pago = (bool) $loja['LO_PAGO'];
		$loja_delivery = (bool) $loja['LO_DELIVERY'];

		$cartao_credito = ($loja_forma == 1) ? true : false;
		$faturado = ($loja_forma == 7) ? true : false;
		$reserva = ($loja_forma == 5) ? true : false;

		// $loja_cliente = utf8_encode($loja['CL_NOME']);
		$sql_cliente = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 NOMEPARC, TELEFONE, EMAIL FROM TGFPAR WHERE CODPARC='$loja_cliente' AND CLIENTE='S' AND BLOQUEAR='N' ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_cliente) > 0) $loja_cliente_ar = sqlsrv_fetch_array($sql_cliente);

		$loja_nome = utf8_encode(trim($loja_cliente_ar['NOMEPARC']));
		$loja_telefone = utf8_encode(trim($loja_cliente_ar['TELEFONE']));
		$loja_email = utf8_encode(trim($loja_cliente_ar['EMAIL']));

		$loja_valor_total = $loja['LO_VALOR_TOTAL'];
		$loja_valor_total_f = number_format($loja['LO_VALOR_TOTAL'], 2, ',','.');

		//Forma de pagamento
		// LO_FORMA_PAGAMENTO
		$sql_forma = $sql_cliente = sqlsrv_query($conexao, "SELECT TOP 1 FP_NOME FROM formas_pagamento WHERE FP_COD='$loja_forma'", $conexao_params, $conexao_options);
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
				<p><? echo $loja_telefone; ?></p>
			</section>

			<div class="informacoes-compra">
				<p><? echo $loja_forma_pagamento; ?></p>
				<? if($cartao_credito && !empty($loja_cartao)) { ?><p class="cartao"><span class="<? echo $loja_cartao; ?>"></span> <? echo $loja_parcelas; ?>x R$ <? echo number_format(($loja_valor_total / $loja_parcelas), 2, ",", "."); ?> <strong> • <? echo $loja_cartao; ?></strong></p><? } ?>
				<?
				if($cartao_credito && !$loja_pago)  {
					if(($loja_status_transacao == 4) && ($loja_diferenca_dias > -1)) {
					?>
					<a href="<? echo SITE; ?>compra/captura/<? echo $loja_cod; ?>/" class="liberar confirm" title="Confirmar o pagamento da compra <? echo $loja_cod; ?> (<? echo $loja_diferenca_dias; ?> dia<? echo $loja_diferenca_dias != 1 ? 's' : ''; ?> para expirar o prazo de confirmação)?">Confirmar (<? echo $loja_diferenca_dias; ?>)</a>
					<?
					} else {
					?>
					<a href="<? echo SITE; ?>compras/pagamento/<? echo $loja_cod; ?>/" class="liberar pagar confirm" title="Realizar o pagamento da compra <? echo $loja_cod; ?>?">Pagar</a>
					<?
					}					
				}
				
				//Faturado
				if($faturado) {

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

							$ifaturas++;
						}
						?>
						</p>
						<?
					}
					
					if(!$loja_pago){
					?>
					<a href="<? echo SITE; ?>financeiro/faturado/<? echo $loja_cod; ?>/" class="liberar" title="Confirmar o pagamento da compra <? echo $loja_cod; ?>?">Confirmar</a>
					<?						
					}

				}

				if(!$loja_pago && !$cartao_credito && !$faturado && !$reserva) {
					?>
					<a href="<? echo SITE; ?>e-financeiro-gerenciar.php?c=<? echo $loja_cod; ?>&a=confirmar" class="liberar confirm" title="Confirmar o pagamento da compra <? echo $loja_cod; ?>?">Confirmar</a>
					<?
				}

				?>
				<a href="#" class="print" title="Imprimir compra <? echo $loja_cod; ?>?"></a>
			</div>

			<div class="clear"></div>
		</section>		

		<section id="financeiro-detalhes-itens" class="secao">
		<?

		$sql_item = sqlsrv_query($conexao, "
			SELECT li.*, v.VE_DIA, v.VE_SETOR, v.VE_FILA, v.VE_VAGAS, v.VE_TIPO_ESPECIFICO, es.ES_NOME, ed.ED_NOME, ed.ED_DATA, SUBSTRING(CONVERT(VARCHAR, ed.ED_DATA, 103), 1, 5) AS dia, tp.TI_NOME 
			FROM loja_itens li, vendas v, eventos_setores es, eventos_dias ed, tipos tp 
			WHERE li.LI_COMPRA='$loja_cod' AND li.LI_INGRESSO=v.VE_COD AND es.ES_COD=v.VE_SETOR AND ed.ED_COD=v.VE_DIA AND v.VE_TIPO=tp.TI_COD AND li.D_E_L_E_T_='0' 
			ORDER BY LI_COD ASC", $conexao_params, $conexao_options);

		if(sqlsrv_num_rows($sql_item) > 0) {
			$i = 1;
			$item_count = 1;

			while ($item = sqlsrv_fetch_array($sql_item)) {
					
				$item_cod = $item['LI_COD'];
				$item_id = $item['LI_ID'];
				$item_nome = utf8_encode($item['LI_NOME']);
				$item_setor = utf8_encode($item['ES_NOME']);
				$item_dia = utf8_encode($item['ED_NOME']);
				$item_tipo = utf8_encode($item['TI_NOME']);
				$item_valor =  number_format($item['LI_VALOR'], 2, ",", ".");
				$item_exclusividade = $item['LI_EXCLUSIVIDADE'];
				$item_exclusividade_val = $item['LI_EXCLUSIVIDADE_VAL'];
				
				$item_fila = utf8_encode($item['VE_FILA']);
				$item_vaga = utf8_encode($item['VE_VAGAS']);
				$item_tipo_especifico = utf8_encode($item['VE_TIPO_ESPECIFICO']);

				$item_fechado = (($item_vaga > 0) && ($item_tipo_especifico == 'fechado')) ? true : false;

				// loja_itens_adicionais
				if(!$item_fechado) $item_count = 1;

			?>
			<section class="item-carrinho">
				
				<?
				if(!$item_fechado || ($item_fechado && ($item_count == 1))) {
				?>
				<header>
					<? echo $item_dia; ?> dia &ndash; Setor <? echo $item_setor; ?> &ndash; 
					<?
						echo $item_tipo;
						if(!empty($item_fila)) { echo " ".$item_fila; }
						if(!empty($item_tipo_especifico)) { echo " ".$item_tipo_especifico; }
						if($item_fechado) { echo " (".$item_vaga." vagas)"; }
					?>
					<span class="valor">R$ <? echo $item_valor; ?></span>
				</header>
				<?
					if($item_fechado) { $item_count++; }
				}
				?>

				<div class="cliente <? if($item_fechado) { echo 'fechado'; } ?>">
					<? if($item_fechado) { ?><span class="vch"><? echo $loja_cod."/".$item_id; ?></span><? } ?>
					<? echo $item_nome; ?>

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

					$sql_adicionais = sqlsrv_query($conexao, "SELECT lia.LIA_COD, v.* FROM loja_itens_adicionais lia, vendas_adicionais v WHERE v.VA_COD=lia.LIA_ADICIONAL AND lia.LIA_COMPRA='$cod' AND lia.LIA_ITEM='$item_cod'", $conexao_params, $conexao_options);
					if(sqlsrv_num_rows($sql_adicionais) > 0) {

						while ($vendas_adicionais = sqlsrv_fetch_array($sql_adicionais)) {
							$vendas_adicionais_label = utf8_encode($vendas_adicionais['VA_LABEL']);
							$vendas_adicionais_nome_exibicao = $vendas_adicionais['VA_NOME_EXIBICAO'];
							
							if($vendas_adicionais_nome_exibicao == 'delivery'){
								$vendas_adicionais_delivery['label'] = $vendas_adicionais_label;
							} else {


						?>
						<tr class="incluso">
							<td class="check">&nbsp;</td>
							<td class="nome"><? echo $vendas_adicionais_label; ?></td>
						</tr>								
						<?
							}

						}

					}
					
					?>
					</tbody>
				</table>

				<?/*<p class="comentarios">
					<label for="carrinho-comentarios-<? echo $key; ?>">Comentários sobre <?
						echo $item_tipo;
						if(!empty($item_fila)) { echo " ".$item_fila; }
						if(!empty($item_tipo_especifico)) { echo " ".$item_tipo_especifico; }
						if(!empty($item_vaga) && ($item_tipo_especifico == 'fechado')) { echo " (".$item_vaga." vagas)"; }
					?>:</label>
					<textarea name="comentarios[<? echo $key; ?>]" class="input" id="carrinho-comentarios-<? echo $key; ?>" rows="3"></textarea>
				</p>*/?>

			</section>
			<?
			}		

		}
		
		if($vendas_adicionais_delivery) {
		?>
		<section class="item-carrinho extra">
			<header>Informações extra</header>
			<table class="lista compras-adicionais">
				<tbody>
					<tr class="incluso">
						<td class="check">&nbsp;</td>
						<td class="nome"><? echo $vendas_adicionais_delivery['label']; ?></td>
					</tr>								
				</tbody>
			</table>
		</section>
		<?
		}
		?>
		</section>
		<footer class="controle">
			<a href="<? echo SITE; ?>compras/alterar/<? echo $loja_cod; ?>/" class="button coluna big">Alterar tipos</a>
			<a href="<? echo SITE; ?>compras/excluir/<? echo $loja_cod; ?>/" class="button cancelar-compra coluna big confirm" title="Deseja realmente cancelar a compra?">Cancelar compra</a>
			<a href="<? echo strpos($_SERVER['HTTP_REFERER'], 'financeiro') ? $_SERVER['HTTP_REFERER'] : SITE.'financeiro/'; ?>" class="cancel coluna">Voltar</a>
			<div class="clear"></div>
		</footer>
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

				if(!empty($loja_endereco) && !empty($loja_numero) && !empty($loja_bairro) && !empty($loja_cidade) && !empty($loja_estado) && !empty($loja_cep)) {

				?>
				<p><? echo $loja_endereco; ?>, <? echo $loja_numero; ?> <? if (!empty($loja_complemento)){ echo '- '.$loja_complemento; } ?></p>
				<p>CEP: <? echo $loja_cep; ?> - <? echo $loja_bairro; ?>, <? echo $loja_cidade; ?> / <? echo $loja_estado; ?></p>

				<a href="<? echo SITE; ?>compras/delivery/<? echo $loja_cod; ?>/detalhes/" class="button alterar">Alterar endereço de entrega</a>
				<?

				} else {
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
</section>
<?

//-----------------------------------------------------------------//

include('include/footer.php');

//Fechar conexoes
include("conn/close.php");
include("conn/close-sankhya.php");

?>