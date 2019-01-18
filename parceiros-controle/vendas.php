<?

//-----------------------------------------------------------------//
// Funções básicas
//-----------------------------------------------------------------//
	include("include/includes.php");

//-----------------------------------------------------------------//
// Arquivos de layout
//-----------------------------------------------------------------//
	include("include/head.php");
	include("include/header.php");

//-----------------------------------------------------------------//
 	
 	$cod_parceiro = $_SESSION['us-par-parceiro'];

 	$html_excel = "";

//-----------------------------------------------------------------//
// Formas de pagamento
//-----------------------------------------------------------------//
	$sql_formas_pagamento = sqlsrv_query($conexao, "SELECT FP_COD, FP_NOME FROM formas_pagamento WHERE D_E_L_E_T_=0 ORDER BY FP_NOME ASC", $conexao_params, $conexao_options);

	if(sqlsrv_num_rows($sql_formas_pagamento))
	{
		while ($ar_formas_pagamento = sqlsrv_fetch_array($sql_formas_pagamento)) 
		{ 
			$forma_pagamento = $ar_formas_pagamento['FP_NOME'];
			$formas_pagamento[$ar_formas_pagamento['FP_COD']] = ($forma_pagamento == utf8_decode('Cartão de Crédito')) ? utf8_decode('Cartão Crédito') : $forma_pagamento;
		}
	}

//-----------------------------------------------------------------//
// Dados da compra
//-----------------------------------------------------------------//
	$sql_loja = sqlsrv_query($conexao, "SELECT
	LO_COD, 
	LO_CLIENTE, 
	LO_PARCEIRO, 
	LO_FORMA_PAGAMENTO, 
	LO_STATUS_TRANSACAO, 
	LO_VALOR_INGRESSOS,
	LO_VALOR_DESCONTO,
	LO_VALOR_OVER_INTERNO,
	LO_VALOR_OVER_EXTERNO,
	LO_ENVIADO,
	LO_DATA_COMPRA,
	LO_COMISSAO,
	LO_COMISSAO_RETIDA,
	LO_COMISSAO_PAGA,
	LO_ORIGEM,
	(CASE WHEN LO_PAGO=1 THEN 'Pago' ELSE 'Reserva' END) AS STATUS,
	(CONVERT(VARCHAR, LO_DATA_COMPRA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, LO_DATA_COMPRA, 108),1,5)) AS DATA, 
	(CONVERT(VARCHAR, LO_DATA_PAGAMENTO, 103)+' '+SUBSTRING(CONVERT(VARCHAR, LO_DATA_PAGAMENTO, 108),1,5)) AS DATA_PAGAMENTO,
	(SUBSTRING(CONVERT(VARCHAR, LO_DATA_COMPRA, 103),1,5)+' '+SUBSTRING(CONVERT(VARCHAR, LO_DATA_COMPRA, 108),1,5)) AS DATA_MINI, 
	(SUBSTRING(CONVERT(VARCHAR, LO_DATA_PAGAMENTO, 103),1,5)+' '+SUBSTRING(CONVERT(VARCHAR, LO_DATA_PAGAMENTO, 108),1,5)) AS DATA_PAGAMENTO_MINI, 
	ISNULL(DATEDIFF (DAY, LO_DATA_PAGAMENTO, GETDATE()), 6) AS DIFERENCA
	FROM loja (NOLOCK) WHERE LO_COMISSAO>0 AND LO_BLOCK='0' AND LO_PARCEIRO=$cod_parceiro AND D_E_L_E_T_='0' AND LO_DATA_COMPRA > '2017-04-02' AND LO_EVENTO=1007", $conexao_params, $conexao_options);
	

	$n_loja = sqlsrv_num_rows($sql_loja);

?>

<section id="conteudo">
	<header class="titulo">
		<h1>Vendas com <span>cupom de desconto</span></h1>	
	</header>
	<section class="secao bottom">

		<? if($n_loja > 0): ?>
		<table class="lista mini tablesorter-nopager">
			<thead>
				<tr>
					<th class="first"><strong>VCH</strong><span></span></th>
					<th><strong>Cliente</strong><span></span></th>
					<th><strong>Data Compra</strong><span></span></th>
					<th><strong>Forma Pgto</strong><span></span></th>
					<th class="right"><span></span><strong>Total (R$)</strong></th>
					<th class="right"><span></span><strong>Comissão (R$)</strong></th>
					<th class="right"><span></span><strong>Comissao (%)</strong></th>
					<th class="center"><strong>Status</strong></th>
					<th class="center"><strong>Origem</strong></th>
					<th>&nbsp;</th>
				</tr>
				<tr class="spacer"><td colspan="9">&nbsp;</td></tr>
			</thead>
			<tbody>
			<?
			
			if($n_loja !== false) {

				$i = 1;

				while($loja = sqlsrv_fetch_array($sql_loja)) {
					
					$loja_cod = $loja['LO_COD'];
					$loja_data = $loja['DATA'];
					$loja_cliente_cod = $loja['LO_CLIENTE'];
					$loja_parceiro_cod = $loja['LO_PARCEIRO'];
					$loja_tipo_pagamento = $loja['LO_FORMA_PAGAMENTO'];
					$loja_comissao = $loja['LO_COMISSAO'];
					$loja_valor = $loja['LO_VALOR_INGRESSOS'];
					$loja_valor_desconto = $loja['LO_VALOR_DESCONTO'];
					$loja_comissao_retida = (bool) $loja['LO_COMISSAO_RETIDA'];
					$loja_comissao_paga = (bool) $loja['LO_COMISSAO_PAGA'];
					$loja_block = (bool) $loja['LO_BLOCK'];
					$entrega = ($loja_entrega) ? 'ativo' : 'ativar';
					$loja_status = $loja['STATUS'];			
					
					$loja_origem = $loja['LO_ORIGEM'];

					$sql_valor_ingressos = sqlsrv_query($conexao, "SELECT SUM(LI_VALOR_TABELA) AS INGRESSOS FROM loja_itens WHERE LI_COMPRA='$loja_cod' AND D_E_L_E_T_=0", $conexao_params, $conexao_options);
			      	if(sqlsrv_num_rows($sql_valor_ingressos) > 0) {
			      		$loja_valor_ingressos_ar = sqlsrv_fetch_array($sql_valor_ingressos);
			      		$loja_valor_ingressos = $loja_valor_ingressos_ar['INGRESSOS'];
			      	}

					$loja_valor_total = number_format(($loja_valor_ingressos - $loja_valor_desconto + $loja_over), 2, ",", ".");

					$loja_comissao_valor = number_format((($loja_valor_ingressos - $loja_valor_desconto + $loja_over) * $loja_comissao / 100), 2, ",", ".");
					
					$loja_forma_pagamento = utf8_encode($formas_pagamento[$loja_tipo_pagamento]);					

					$sql_cliente = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 NOMEPARC, CODPARC FROM TGFPAR WHERE CODPARC IN ('$loja_cliente_cod','$loja_parceiro_cod') AND (CLIENTE='S' OR VENDEDOR='S') AND BLOQUEAR='N' ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);

					if(sqlsrv_num_rows($sql_cliente) > 0) {
						while($loja_cliente_ar = sqlsrv_fetch_array($sql_cliente)) {
							
							switch ($loja_cliente_ar['CODPARC']) {
								case $loja_cliente_cod:
									$loja_cliente = trim($loja_cliente_ar['NOMEPARC']);
									//$loja_cliente_exibir = (strlen($loja_cliente) > 20) ? substr($loja_cliente, 0, 20)."..." : $loja_cliente;									
								break;

								case $loja_parceiro_cod:
									$loja_parceiro = trim($loja_cliente_ar['NOMEPARC']);
									//$loja_parceiro_exibir = (strlen($loja_cliente) > 20) ? substr($loja_parceiro, 0, 20)."..." : $loja_parceiro;									
								break;
							}							
						}
					}


					//buscar itens
					$sql_itens = sqlsrv_query($conexao, "SELECT * FROM loja_itens WHERE LI_COMPRA='$loja_cod' AND D_E_L_E_T_='0'", $conexao_params, $conexao_options);
					
					$n_itens = sqlsrv_num_rows($sql_itens);

					?>
						<tr <? if ($loja_block){ echo 'class="block"'; } ?>>
							<td class="dtl-voucher" data-cod="<? echo $loja_cod; ?>" data-cancelado="false">
								<div class="">
									<? echo $loja_cod; ?>
									<section class="detalhes"></section>
								</div>
							</td>
							
							<td >
								<? echo utf8_encode($loja_cliente); ?>
							</td>
							<td><? echo $loja_data; ?></td>
							<td><? echo $loja_forma_pagamento; ?></td>
							<td class="valor"><? echo $loja_valor_total; ?></td>
							<td class="valor"><? echo $loja_comissao_valor; ?></td>
							<td class="valor"><? echo $loja_comissao; ?>%</td>
							<td class="valor"><? echo $loja_status; ?></td>		
							<td class="valor"><? echo $loja_origem; ?></td>							
						</tr>
					<?
					$i++;

					
				}

			} 
			
			?>
			</tbody>
		</table>
		<br>

		<!-- <a href="https://ingressos.foliatropical.com.br/parceiros-controle/relatorio_excel.php" class="submit">Exportar para excel</a> -->
		<a href="https://ingressos.foliatropical.com.br/parceiros-controle/relatorio_excel.php" class="submit">Exportar para excel</a>

		<? else: ?>
			<p class="nenhuma-compra">Nenhuma compra confirmada.</p>
		<? endif; ?>

		<? if ($n_cupons > 0) { ?>
        <div class="pager-tablesorter">
	        <a href="#" class="first"></a>
	        <a href="#" class="prev"></a>
	        <span class="pagedisplay"></span>
	        <a href="#" class="next"></a>
	        <a href="#" class="last"></a>
	        <input type="hidden" class="pagesize" value="30" />
        </div>
        <? } ?>
	</section>
</section>
<?


//-----------------------------------------------------------------//
// Rodapé
//-----------------------------------------------------------------//
	include('include/footer.php');

//-----------------------------------------------------------------//
// Fechar conexoes
//-----------------------------------------------------------------//
	include("conn/close.php");

?>