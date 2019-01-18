<?

//Incluir funções básicas
include("include/includes.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");

//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");
include("include/header.php");

//-----------------------------------------------------------------//

$evento = (int) $_SESSION['usuario-carnaval'];
$administrador = ($_SESSION['us-grupo'] == 'ADM') ? true : false;
$atendente = ($_SESSION['us-grupo'] == 'ATE') ? true : false;

//-----------------------------------------------------------------//

$tipo = format($_GET['t']);
switch ($tipo) {
	case 'pendentes': $search_pendentes = " AND LO_ENTREGUE='0' "; break;
	case 'entregues': $search_pendentes = " AND LO_ENTREGUE='1' "; break;
}

//-----------------------------------------------------------------//

$q = format($_GET['q']);
if(!empty($q)) {

	if(!is_numeric($q)) {
		// $search_query = is_numeric($q) ? " AND CODPARC='$q' " : " AND NOMEPARC LIKE '%$q%' ";
		$search_query = " AND NOMEPARC LIKE '%$q%' ";

		$sql_search = sqlsrv_query($conexao_sankhya, "SELECT CODPARC, CLIENTE, VENDEDOR FROM TGFPAR WHERE (CLIENTE='S' OR VENDEDOR='S') AND BLOQUEAR='N' $search_query ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
		// $sql_search = sqlsrv_query($conexao_sankhya, "SELECT CODPARC FROM TGFPAR WHERE CLIENTE='S' AND BLOQUEAR='N' $search_query ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_search) > 0) {
			$ar_clientes_cods = $ar_parceiros_cods = array();
			while ($cods = sqlsrv_fetch_array($sql_search)) {
				if($cods['CLIENTE'] == 'S') array_push($ar_clientes_cods, $cods['CODPARC']);
				if($cods['VENDEDOR'] == 'S') array_push($ar_parceiros_cods, $cods['CODPARC']);
			}
			
			$search = " AND (";

			if(count($ar_clientes_cods) > 0) {
				$clientes_cods = implode(",", $ar_clientes_cods);
				$search .= " LO_CLIENTE IN ($clientes_cods) ";
			}
			if(count($ar_parceiros_cods) > 0) {
				$parceiros_cods = implode(",", $ar_parceiros_cods);
				if(count($ar_clientes_cods) > 0) $search .= " OR ";
				$search .= " LO_PARCEIRO IN ($parceiros_cods) ";
			}

			$search .= ") ";
		} else {
			// $search = " AND LO_CLIENTE IN ('') ";
			$nosearch = true;
		}
	} else {
		$search = " AND LO_COD='$q' ";
	}
}

//-----------------------------------------------------------------//

$p = (int) $_GET['p'];
if(!($p > 0)) $p = 1;

 // LO_COD, LO_CLIENTE, LO_FORMA_PAGAMENTO, LO_STATUS_TRANSACAO, LO_VALOR_TOTAL, (CONVERT(VARCHAR, LO_DATA_COMPRA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, LO_DATA_COMPRA, 108),1,5)) AS DATA, (CONVERT(VARCHAR, LO_DATA_PAGAMENTO, 103)+' '+SUBSTRING(CONVERT(VARCHAR, LO_DATA_PAGAMENTO, 108),1,5)) AS DATA_PAGAMENTO, ISNULL(DATEDIFF (DAY, LO_DATA_PAGAMENTO, GETDATE()), 6) AS DIFERENCA

if(!$nosearch) {

	$sql_loja = sqlsrv_query($conexao, "

	DECLARE @PageNumber INT;
	DECLARE @PageSize INT;
	DECLARE @TotalPages INT;

	SET @PageSize = 20;
	SET @PageNumber = $p;

	IF @PageNumber = 0 BEGIN
	SET @PageNumber = 1
	END;

	SET @TotalPages = CEILING(CONVERT(NUMERIC(20,10), ISNULL((SELECT COUNT(*) FROM loja (NOLOCK) WHERE 
	LO_EVENTO='$evento' AND LO_BLOCK='0' AND D_E_L_E_T_='0' $search $search_pendentes), 0)) / @PageSize);

	WITH cadastro(NumeroLinha, LO_COD, LO_CLIENTE, LO_PARCEIRO, LO_FORMA_PAGAMENTO, LO_STATUS_TRANSACAO, LO_VALOR_TOTAL, LO_DATA_COMPRA, LO_PAGO, LO_DELIVERY, LO_CLI_PERIODO, LO_RETIRADA, LO_ENTREGUE, LO_ENCAMINHADO, LO_RECEBIDO, DATA, DATA_PAGAMENTO, DATA_MINI, DATA_PAGAMENTO_MINI, DIFERENCA)
	AS (
	SELECT ROW_NUMBER() OVER (ORDER BY LO_COD DESC) AS NumeroLinha,
	LO_COD, 
	LO_CLIENTE,
	LO_PARCEIRO, 
	LO_FORMA_PAGAMENTO, 
	LO_STATUS_TRANSACAO, 
	LO_VALOR_TOTAL, 
	LO_DATA_COMPRA,
	LO_PAGO,
	LO_DELIVERY,
	LO_CLI_PERIODO,
	LO_RETIRADA,
	LO_ENTREGUE,
	LO_ENCAMINHADO,
	LO_RECEBIDO,
	(CONVERT(VARCHAR, LO_DATA_COMPRA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, LO_DATA_COMPRA, 108),1,5)) AS DATA, 
	(CONVERT(VARCHAR, LO_DATA_PAGAMENTO, 103)+' '+SUBSTRING(CONVERT(VARCHAR, LO_DATA_PAGAMENTO, 108),1,5)) AS DATA_PAGAMENTO, 
	(SUBSTRING(CONVERT(VARCHAR, LO_DATA_COMPRA, 103),1,5)+' '+SUBSTRING(CONVERT(VARCHAR, LO_DATA_COMPRA, 108),1,5)) AS DATA_MINI, 
	(SUBSTRING(CONVERT(VARCHAR, LO_DATA_PAGAMENTO, 103),1,5)+' '+SUBSTRING(CONVERT(VARCHAR, LO_DATA_PAGAMENTO, 108),1,5)) AS DATA_PAGAMENTO_MINI, 
	ISNULL(DATEDIFF (DAY, LO_DATA_PAGAMENTO, GETDATE()), 6) AS DIFERENCA
	FROM loja (NOLOCK) WHERE 
	LO_EVENTO='$evento' AND LO_BLOCK='0' AND D_E_L_E_T_='0' $search $search_pendentes
	)
	
	SELECT @TotalPages AS TOTAL, @PageNumber AS PAGINA, NumeroLinha, LO_COD, LO_CLIENTE, LO_PARCEIRO, LO_FORMA_PAGAMENTO, LO_STATUS_TRANSACAO, LO_VALOR_TOTAL, LO_PAGO, LO_DELIVERY, LO_CLI_PERIODO, LO_RETIRADA, LO_ENTREGUE, LO_ENCAMINHADO, LO_RECEBIDO, DATA, DATA_PAGAMENTO, DATA_MINI, DATA_PAGAMENTO_MINI, DIFERENCA
	FROM cadastro
	WHERE NumeroLinha BETWEEN ( ( ( @PageNumber - 1 ) * @PageSize ) + 1 ) AND ( @PageNumber * @PageSize )
	ORDER BY LO_DATA_COMPRA DESC

	", $conexao_params, $conexao_options);
 $n_loja = sqlsrv_num_rows($sql_loja);

} else {
	$n_loja = false;
}

?>
<section id="conteudo">
	<header class="titulo">
		<h1>
			Expedição 
			<span class="expedicao">
				<a href="<? echo SITE; ?>ingressos/expedicao/pendentes/" <? if($tipo == 'pendentes') { echo 'class="checked"'; } ?>>Pendentes</a> • 
				<a href="<? echo SITE; ?>ingressos/expedicao/entregues/" <? if($tipo == 'entregues') { echo 'class="checked"'; } ?>>Entregues</a>
			</span>
		</h1>
		<form id="busca-lista" class="busca-lista" method="get" action="<? echo SITE; ?>ingressos/expedicao/">
			<p class="coluna">
				<label for="busca-lista-input" class="infield">Pesquisar</label>
				<? if(!empty($q)){ ?><a href="<? echo SITE; ?>ingressos/expedicao/" class="limpar-busca">&times;</a><? } ?>
				<input type="text" name="q" class="input" id="busca-lista-input" value="<? echo utf8_encode($q); ?>" />
			</p>
			<input type="submit" class="submit" value="" />
		</form>
	</header>
	<section class="secao bottom">
		<table class="lista mini tablesorter-nopager">
			<thead>
				<tr>
					<th class="first"><strong>VCH</strong><span></span></th>
					<th><strong>Cliente</strong><span></span></th>
					<th><strong>parceiro</strong><span></span></th>
					<th><strong>Dt. Compra</strong><span></span></th>
					<th class="right"><span></span><strong>Valor (R$)</strong></th>
					<th class="center padding" title="Encaminhado"><strong>Enc.</strong></th>
					<th class="center" title="Recebido"><strong>Rec.</strong></th>
					<th class="center" title="Entregue/Retirado"><strong>E/R</strong></th>
					<? if(!$atendente) { ?><th>&nbsp;</th><? } ?>
				</tr>
				<tr class="spacer"><td colspan="<? echo ($atendente) ? '8' : '9'; ?>">&nbsp;</td></tr>
			</thead>
			<tbody>
			<?
			
			if($n_loja !== false) {

				$i=1;
				while($loja = sqlsrv_fetch_array($sql_loja)) {
					
					//Total de paginas
					$total_paginas = $loja['TOTAL'];

					$loja_cod = $loja['LO_COD'];
					$loja_data = $loja['DATA'];
					$loja_data_mini = $loja['DATA_MINI'];
					$loja_data_pagamento_mini = $loja['DATA_PAGAMENTO_MINI'];
					$loja_cliente_cod = $loja['LO_CLIENTE'];
					$loja_parceiro_cod = $loja['LO_PARCEIRO'];
					$loja_tipo_pagamento = $loja['LO_FORMA_PAGAMENTO'];
					$loja_status_transacao = $loja['LO_STATUS_TRANSACAO'];
					$loja_diferenca_dias = (5 - $loja['DIFERENCA']);
					$loja_pago = (bool) $loja['LO_PAGO'];

					$loja_delivery = (bool) $loja['LO_DELIVERY'];
					$loja_delivery_periodo = $loja['LO_CLI_PERIODO'];
					$loja_retirada = $loja['LO_RETIRADA'];
					$loja_entregue = (bool) $loja['LO_ENTREGUE'];
					$loja_encaminhado = (bool) $loja['LO_ENCAMINHADO'];
					$loja_recebido = (bool) $loja['LO_RECEBIDO'];

					$cartao_credito = ($loja_tipo_pagamento == 1) ? true : false;
					$faturado = ($loja_tipo_pagamento == 7) ? true : false;
					$reserva = ($loja_tipo_pagamento == 5) ? true : false;

					// $loja_forma_pagamento = utf8_encode($loja['FP_NOME']);
					$loja_forma_pagamento = utf8_encode($formas_pagamento[$loja_tipo_pagamento]);
					$loja_valor = number_format($loja['LO_VALOR_TOTAL'], 2, ",", ".");				

					unset($loja_cliente, $loja_cliente_exibir, $loja_parceiro, $loja_parceiro_exibir);

					// $loja_cliente = utf8_encode($loja['CL_NOME']);
					$sql_cliente = sqlsrv_query($conexao_sankhya, "SELECT TOP 2 NOMEPARC, CODPARC FROM TGFPAR WHERE CODPARC IN ('$loja_cliente_cod','$loja_parceiro_cod') AND (CLIENTE='S' OR VENDEDOR='S') AND BLOQUEAR='N' ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
					if(sqlsrv_num_rows($sql_cliente) > 0) {
						while($loja_cliente_ar = sqlsrv_fetch_array($sql_cliente)) {
							switch ($loja_cliente_ar['CODPARC']) {
								case $loja_cliente_cod:
									$loja_cliente = trim($loja_cliente_ar['NOMEPARC']);
									$loja_cliente_exibir = (strlen($loja_cliente) > 20) ? substr($loja_cliente, 0, 20)."..." : $loja_cliente;									
								break;

								case $loja_parceiro_cod:
									$loja_parceiro = trim($loja_cliente_ar['NOMEPARC']);
									$loja_parceiro_exibir = (strlen($loja_parceiro) > 20) ? substr($loja_parceiro, 0, 20)."..." : $loja_parceiro;									
								break;
							}							
						}
					}

					//buscar itens
					$sql_itens = sqlsrv_query($conexao, "SELECT * FROM loja_itens WHERE LI_COMPRA='$loja_cod' AND D_E_L_E_T_='0'", $conexao_params, $conexao_options);
					$n_itens = sqlsrv_num_rows($sql_itens);

					$alterar_pagamento = ($cartao_credito && (($loja_status_transacao == 4) && ($loja_diferenca_dias > -1))) ? false : true;
					$alterar_pagamento_classe = '';

					?>
						<tr <? if(!$loja_pago) { echo 'class="block" title="Pagamento pendente"'; } ?>>	
							<td class="first detalhes-voucher" data-cod="<? echo $loja_cod; ?>" data-cancelado="false">
								<div class="relative">
									<? echo $loja_cod; ?>
									<section class="detalhes"></section>
								</div>
							</td>
							<td <? if($loja_cliente != $loja_cliente_exibir) { echo 'title="'.utf8_encode($loja_cliente).'"'; } ?>>
								<? echo utf8_encode($loja_cliente_exibir); ?>
							</td>
							<td <? if($loja_parceiro != $loja_parceiro_exibir) { echo 'title="'.utf8_encode($loja_parceiro).'"'; } ?>>
								<? echo utf8_encode($loja_parceiro_exibir); ?>
							</td>
							<td title="<? echo $loja_data; ?>"><? echo $loja_data_mini; ?></td>
							<td class="valor"><? echo $loja_valor; ?></td>
							<td class="ctrl small marcar"><a href="<? echo SITE; ?>ingressos/expedicao/confirmar/<? echo $loja_cod; ?>/encaminhado/" class="<? if($loja_encaminhado) { echo 'ativo '; } ?>ativar fancybox fancybox.iframe width600"></a></td>
							<td class="ctrl small marcar no-padding"><a href="<? echo SITE; ?>ingressos/expedicao/confirmar/<? echo $loja_cod; ?>/recebido/" class="<? if($loja_recebido) { echo 'ativo '; } ?>ativar fancybox fancybox.iframe width600"></a></td>
							<td class="ctrl small marcar no-padding"><a href="<? echo SITE; ?>ingressos/expedicao/confirmar/<? echo $loja_cod; ?>/" class="<? if($loja_entregue) { echo 'ativo '; } ?>ativar fancybox fancybox.iframe width600"></a></td>
							<? if(!$atendente) { ?><td class="ctrl small no-padding detalhes"><a href="<? echo SITE; ?>financeiro/detalhes/<? echo $loja_cod; ?>/" class="ver"></a></td><? } ?>
						</tr>
					<?
					$i++;
				}

				$exibe_loja = true;
			} 
			if(!$exibe_loja) {
			?>
				<tr>
					<td colspan="<? echo ($atendente) ? '8' : '9'; ?>" class="nenhum">Nenhum pagamento pendente.</td>
				</tr>
			<?
			}
			?>
			</tbody>
		</table>
		<?
		if ($exibe_loja) {

			
			
		?>
        <div class="pager-tablesorter">
	        <a href="<? echo SITE; ?>ingressos/expedicao/<? if(!empty($tipo)) echo $tipo.'/'; if(!empty($q)) echo '?q='.urlencode(utf8_encode($q)); ?>" class="first"></a>
	        <a href="<? echo SITE; ?>ingressos/expedicao/<? if(!empty($tipo)) echo $tipo.'/'; ?>?<? if(!empty($q)) echo 'q='.urlencode(utf8_encode($q)).'&'; ?>p=<? echo ($p > 1) ? ($p - 1) : 1; ?>" class="prev"></a>
	        <span class="pagedisplay"><? echo $p; ?>/<? echo $total_paginas; ?></span>
	        <a href="<? echo SITE; ?>ingressos/expedicao/<? if(!empty($tipo)) echo $tipo.'/'; ?>?<? if(!empty($q)) echo 'q='.urlencode(utf8_encode($q)).'&'; ?>p=<? echo ($p < $total_paginas) ? ($p + 1) : $total_paginas; ?>" class="next"></a>
	        <a href="<? echo SITE; ?>ingressos/expedicao/<? if(!empty($tipo)) echo $tipo.'/'; ?>?<? if(!empty($q)) echo 'q='.urlencode(utf8_encode($q)).'&'; ?>p=<? echo $total_paginas; ?>" class="last"></a>
	        <!-- <input type="hidden" class="pagesize" value="30" /> -->
        </div>
        <? } ?>
	</section>
</section>
<?

//-----------------------------------------------------------------//

include('include/footer.php');

//Fechar conexoes
include("conn/close.php");
include("conn/close-sankhya.php");

?>