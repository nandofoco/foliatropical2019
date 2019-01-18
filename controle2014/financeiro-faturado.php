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

$vendedor = ($_SESSION['us-grupo'] == 'VIN') ? true : false;
$usuario = (int) $_SESSION['us-cod'];

// Se o usuário for vendedor interno, ver apenas as suas vendas
if($vendedor) $search_vendedor = " AND l.LO_VENDEDOR='$usuario' ";

//-----------------------------------------------------------------//

$sql_loja = sqlsrv_query($conexao, "SELECT TOP 1 l.*, (CONVERT(VARCHAR, l.LO_DATA_COMPRA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, l.LO_DATA_COMPRA, 108),1,5)) AS DATA FROM loja l WHERE l.LO_EVENTO='$evento' AND l.LO_BLOCK='0' AND l.D_E_L_E_T_='0' AND l.LO_COD='$cod' $search_vendedor", $conexao_params, $conexao_options);
?>
<section id="conteudo">
	<?
	if(sqlsrv_num_rows($sql_loja) > 0) {

		$loja = sqlsrv_fetch_array($sql_loja);

		$loja_cod = $loja['LO_COD'];
		$loja_cliente = $loja['LO_CLIENTE'];

		// $loja_cliente = utf8_encode($loja['CL_NOME']);
		$sql_cliente = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 NOMEPARC, TELEFONE, EMAIL FROM TGFPAR WHERE CODPARC='$loja_cliente' AND CLIENTE='S' AND BLOQUEAR='N' ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_cliente) > 0) $loja_cliente_ar = sqlsrv_fetch_array($sql_cliente);

		$loja_nome = utf8_encode(trim($loja_cliente_ar['NOMEPARC']));
		$loja_telefone = utf8_encode(trim($loja_cliente_ar['TELEFONE']));
		$loja_email = utf8_encode(trim($loja_cliente_ar['EMAIL']));

		$loja_valor_total = $loja['LO_VALOR_TOTAL'];
		$loja_valor_total_f = number_format($loja['LO_VALOR_TOTAL'], 2, ',','.');
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

			<div class="clear"></div>
		</section>		
		<section id="financeiro-lista" class="secao label-top">
			<table class="lista">
				<thead>
					<tr>
						<? /*<th class="first"><strong>VCH</strong></th>*/ ?>
						<th class="first"><strong>Cliente</strong></th>
						<th><strong>Tipo</strong></th>
						<th><strong>Dia</strong></th>
						<th><strong>Setor</strong></th>
						<th class="right"><strong>Valor (R$)</strong></th>
					</tr>
					<tr class="spacer"><td colspan="5">&nbsp;</td></tr>
				</thead>
				<tbody>
				<?

				$item_count = 1;

				$sql_itens = sqlsrv_query($conexao, "SELECT li.*, v.VE_DIA, v.VE_SETOR, v.VE_FILA, v.VE_VAGAS, v.VE_TIPO_ESPECIFICO, es.ES_NOME, ed.ED_DATA, SUBSTRING(CONVERT(VARCHAR, ed.ED_DATA, 103), 1, 5) AS dia, tp.TI_NOME FROM loja_itens li, vendas v, eventos_setores es, eventos_dias ed, tipos tp WHERE li.LI_COMPRA='$loja_cod' AND li.LI_INGRESSO=v.VE_COD AND es.ES_COD=v.VE_SETOR AND ed.ED_COD=v.VE_DIA AND v.VE_TIPO=tp.TI_COD AND li.D_E_L_E_T_='0' ORDER BY LI_COD ASC", $conexao_params, $conexao_options);
				if(sqlsrv_num_rows($sql_itens) > 0) {
					$i = 1;
					while ($item = sqlsrv_fetch_array($sql_itens)) {
						$item_cod = $item['LI_COD'];
						$item_id = $item['LI_ID'];
						$item_nome = utf8_encode($item['LI_NOME']);
						$item_tipo = utf8_encode($item['TI_NOME']);
						$item_dia = utf8_encode($item['dia']);
						$item_setor = $item['ES_NOME'];
						$item_valor = number_format($item['LI_VALOR'], 2, ",", ".");

						$item_fila = utf8_encode($item['VE_FILA']);
						$item_vaga = utf8_encode($item['VE_VAGAS']);
						$item_tipo_especifico = utf8_encode($item['VE_TIPO_ESPECIFICO']);

						$item_fechado = (($item_vaga > 0) && ($item_tipo_especifico == 'fechado')) ? true : false;

						if(!$item_fechado) $item_count = 1;

						?>
							<tr>	
								<? /*<td class="first"><? echo $loja_cod."/".$item_id; ?></td>*/ ?>
								<td class="first"><? echo $item_nome; ?></td>
								<td>
									<?
									echo $item_tipo;
									if(!empty($item_fila)) { echo " ".$item_fila; }
									if(!empty($item_tipo_especifico)) { echo " ".$item_tipo_especifico; }
									if($item_fechado) { echo " (".$item_id."/".$item_vaga.")"; }
									?>
								</td>
								<td><? echo $item_dia; ?></td>
								<td><? echo $item_setor; ?></td>
								<td class="valor"><? echo (!$item_fechado || ($item_fechado && ($item_count == 1))) ? $item_valor : '--'; ?></td>
							</tr>
						<?
						$i++;
						if($item_fechado) $item_count++;
					}
				}
				?>
				</tbody>
			</table>
				<div class="clear"></div>
		</section>

		<?

		$loja_valor_pendente = $loja_valor_total;

		$sql_faturas = sqlsrv_query($conexao, "SELECT *, CONVERT(VARCHAR, LF_DATA_VENCIMENTO, 103) AS DATA_VENCIMENTO,  CONVERT(VARCHAR, LF_DATA_PAGAMENTO, 103) AS DATA_PAGAMENTO FROM loja_faturadas WHERE LF_COMPRA='$cod'", $conexao_params, $conexao_options);
		$n_faturas = sqlsrv_num_rows($sql_faturas);

		if($n_faturas > 0) {

		?>
		<section id="financeiro-lista-fatura" class="secao label-top">
			<h2>Boletos</h2>
			<table class="lista">
				<thead>
					<tr>
						<th class="first"><strong>Pago</strong></th>
						<th class="first"><strong>Parcela</strong></th>
						<th><strong>Data vencimento</strong></th>
						<th><strong>Data pagamento</strong></th>
						<th class="right"><strong>Valor (R$)</strong></th>
						<th class="ctrl"></th>
					</tr>
					<tr class="spacer"><td colspan="6">&nbsp;</td></tr>
				</thead>
				<tbody>
				<?
				
				$fatura_nao_pago_exist = false;

				while ($faturas = sqlsrv_fetch_array($sql_faturas)) {
					$faturas_cod = $faturas['LF_COD'];
					$faturas_compra = $faturas['LF_COMPRA'];
					$faturas_boleto = $faturas['LF_BOLETO'];
					$faturas_parcela = $faturas['LF_PARCELA'];
					$faturas_valor_n = $faturas['LF_VALOR'];
					$faturas_valor = number_format($faturas_valor_n, 2, ",", ".");
					$faturas_pago = (bool) $faturas['LF_PAGO'];
					$faturas_excluido = (bool) $faturas['D_E_L_E_T_'];
					$faturas_data_vencimento = $faturas['DATA_VENCIMENTO'];
					$faturas_data_pagamento = $faturas['DATA_PAGAMENTO'];

					if(!$faturas_pago && !$faturas_excluido) $fatura_nao_pago_exist = true;

					if(!$faturas_excluido) $loja_valor_pendente = $loja_valor_pendente - $faturas_valor_n;

					?>
					<tr <? if ($faturas_pago){ echo 'class="pago"'; } if($faturas_excluido) { echo 'class="block"'; } ?>>
						<td class="check"></td>
						<td class="first"><? echo $faturas_parcela; ?></td>
						<td><? echo $faturas_data_vencimento; ?></td>
						<td><? echo $faturas_data_pagamento; ?></td>
						<td class="valor"><? echo $faturas_valor; ?></td>
						<td class="ctrl small">
							<?
							if($faturas_excluido){
								/*?><a href="<? echo SITE; ?>e-financeiro-fatura.php?c=<? echo $faturas_cod; ?>&l=<? echo $faturas_compra; ?>&a=reativar" class="ativar confirm" title="Deseja reativar o boleto?"></a><?*/
							} else {
								if (!$faturas_pago){ ?><a href="<? echo SITE; ?>e-financeiro-fatura.php?c=<? echo $faturas_cod; ?>&l=<? echo $faturas_compra; ?>&a=confirmar" class="ativar confirm" title="Confirmar o pagamento do boleto?"></a><? } 
								else { ?><a href="<? echo SITE; ?>e-financeiro-fatura.php?c=<? echo $faturas_cod; ?>&l=<? echo $faturas_compra; ?>&a=cancelar" class="excluir confirm" title="Cancelar o pagamento do boleto?"></a><? }
							}
							?>
						</td>
					</tr>
					<?
				
				}

				?>
				</tbody>
			</table>
			
			<? if ($fatura_nao_pago_exist){ ?><a href="<? echo SITE; ?>e-financeiro-fatura.php?t=todas&l=<? echo $faturas_compra; ?>" class="excluir-todos button cancelar-compra confirm" title="Deseja realmente excluir os boletos pendentes">Excluir boletos pendentes</a><? } ?>
			
			<div class="clear"></div>
		</section>

		<?
		}

		if($loja_valor_pendente > 0) {
		?>
		<form id="cadastro-boleto" class="cadastro" method="post" action="<? echo SITE; ?>financeiro/faturado/post/">
			<input type="hidden" name="compra" value="<? echo $cod; ?>" />
			<input type="hidden" name="valor" value="<? echo $loja_valor_pendente; ?>" />

			<h2>Gerar Boletos</h2>
			<section class="secao">

				<p class="valor">
					R$ <? echo number_format($loja_valor_pendente, 2, ",", "."); ?>
				</p>

				<p>
					<label for="boletos-quantidade">Quantidade:</label>
					<input type="text" name="quantidade" class="input" id="boletos-quantidade" value="1" />
				</p>

				<p>
					<label for="boletos-data">Data 1º vencimento:</label>
					<input type="text" name="data" class="input" id="boletos-data" value="<? echo date('d/m/Y', strtotime('+10 days')); ?>" />
				</p>
				
				<input type="submit" class="submit coluna" value="Gerar" />

				<div class="clear"></div>

			</section>
		</form>
		<? } ?>
		
	</section>
	<?
	}
	?>	
</section>
<?

//-----------------------------------------------------------------//

include('include/footer.php');

//Fechar conexoes
include("conn/close.php");
include("conn/close-sankhya.php");

?>