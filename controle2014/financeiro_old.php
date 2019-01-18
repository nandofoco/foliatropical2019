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

//-----------------------------------------------------------------//

$q = format($_GET['q']);
if(!empty($q)) {

	if(!is_numeric($q)) {

		// $search_query = is_numeric($q) ? " AND CODPARC='$q' " : " AND NOMEPARC LIKE '%$q%' ";
		$search_query = " AND NOMEPARC LIKE '%$q%' ";

		$sql_search = sqlsrv_query($conexao_sankhya, "SELECT CODPARC FROM TGFPAR WHERE CLIENTE='S' AND BLOQUEAR='N' $search_query ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_search) > 0) {
			$ar_cods = array();
			while ($cods = sqlsrv_fetch_array($sql_search)) array_push($ar_cods, $cods['CODPARC']);
			$cods = implode(",", $ar_cods);
			$search = " AND l.LO_CLIENTE IN ($cods) ";
		} else {
			$search = " AND l.LO_CLIENTE IN ('') ";
		}

	} else {
		$search = " AND l.LO_COD='$q' ";
	}
}

$sql_loja = sqlsrv_query($conexao, "SELECT l.*, p.FP_NOME, (CONVERT(VARCHAR, l.LO_DATA_COMPRA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, l.LO_DATA_COMPRA, 108),1,5)) AS DATA, (CONVERT(VARCHAR, l.LO_DATA_PAGAMENTO, 103)+' '+SUBSTRING(CONVERT(VARCHAR, l.LO_DATA_PAGAMENTO, 108),1,5)) AS DATA_PAGAMENTO FROM loja l, formas_pagamento p WHERE l.LO_PAGO='1' AND l.LO_EVENTO='$evento' AND l.LO_BLOCK='0' AND l.D_E_L_E_T_='0' AND  l.LO_FORMA_PAGAMENTO=p.FP_COD $search ORDER BY l.LO_DATA_COMPRA DESC", $conexao_params, $conexao_options);
$n_loja = sqlsrv_num_rows($sql_loja);

?>
<section id="conteudo">
	<header class="titulo">
		<h1>Pagamentos <span>Confirmados</span></h1>
		<form id="busca-lista" class="busca-lista" method="get" action="<? echo SITE; ?>financeiro/">
			<p class="coluna">
				<label for="busca-lista-input" class="infield">Pesquisar</label>
				<? if(!empty($q)){ ?><a href="<? echo SITE; ?>financeiro/" class="limpar-busca">&times;</a><? } ?>
				<input type="text" name="q" class="input" id="busca-lista-input" value="<? echo utf8_encode($q); ?>" />
			</p>
			<input type="submit" class="submit" value="" />
		</form>
	</header>
	<section class="secao bottom">
		<table class="lista tablesorter">
			<thead>
				<tr>
					<th class="first"><strong>VCH</strong><span></span></th>
					<th><strong>Cliente</strong><span></span></th>
					<th><strong>Data da Compra</strong><span></span></th>
					<th><strong>Data do Pgto</strong><span></span></th>
					<th><strong>Itens</strong><span></span></th>
					<th><strong>Forma Pgto</strong><span></span></th>
					<th class="right"><span></span><strong>Valor (R$)</strong></th>
					<th>&nbsp;</th>
				</tr>
				<tr class="spacer"><td colspan="6">&nbsp;</td></tr>
			</thead>
			<tbody>
			<?
			
			if($n_loja > 0)	 {

				$i=1;
				while($loja = sqlsrv_fetch_array($sql_loja)) {

					$loja_cod = $loja['LO_COD'];
					$loja_data = $loja['DATA'];
					$loja_cliente_cod = $loja['LO_CLIENTE'];
					$loja_data_pagamento = $loja['DATA_PAGAMENTO'];
					$loja_forma_pagamento = utf8_encode($loja['FP_NOME']);
					$loja_valor = number_format($loja['LO_VALOR_TOTAL'], 2, ",", ".");
					$loja_entrega = (bool) $loja['LO_ENVIADO'];
					$loja_block = (bool) $loja['LO_BLOCK'];
					$entrega = ($loja_entrega) ? 'ativo' : 'ativar';			
					$acao_entrega = ($loja_entrega) ? 'cancelar' : 'confirmar';			

					unset($loja_cliente);
					
					// $loja_cliente = utf8_encode($loja['CL_NOME']);
					$sql_cliente = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 NOMEPARC FROM TGFPAR WHERE CODPARC='$loja_cliente_cod' AND CLIENTE='S' AND BLOQUEAR='N' ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
					if(sqlsrv_num_rows($sql_cliente) > 0) {
						$loja_cliente_ar = sqlsrv_fetch_array($sql_cliente);
						$loja_cliente = utf8_encode(trim($loja_cliente_ar['NOMEPARC']));
					}


					//buscar itens
					$sql_itens = sqlsrv_query($conexao, "SELECT * FROM loja_itens WHERE LI_COMPRA='$loja_cod' AND D_E_L_E_T_='0'", $conexao_params, $conexao_options);
					$n_itens = sqlsrv_num_rows($sql_itens);
					?>
						<tr <? if ($loja_block){ echo 'class="block"'; } ?>>	
							<td class="first"><? echo $loja_cod; ?></td>
							<td><? echo $loja_cliente; ?></td>
							<td><? echo $loja_data; ?></td>
							<td><? echo $loja_data_pagamento; ?></td>
							<td><? echo $n_itens; ?></td>
							<td><? echo $loja_forma_pagamento; ?></td>
							<td class="valor"><? echo $loja_valor; ?></td>
							<td class="ctrl big">
								<a href="<? echo SITE; ?>e-entrega-gerenciar.php?c=<? echo $loja_cod; ?>&a=<? echo $acao_entrega; ?>" class="<? echo $entrega; ?> confirm" title="<? echo ucfirst($acao_entrega); ?> a entrega do item <? echo $loja_cod; ?>?"></a>
								<a href="<? echo SITE; ?>e-financeiro-gerenciar.php?c=<? echo $loja_cod; ?>&a=bloquear" class="block confirm" title="Bloquear o pagamento do item <? echo $loja_cod; ?>?"></a>
								<a href="<? echo SITE; ?>financeiro/detalhes/<? echo $loja_cod; ?>/" class="ver"></a>
							</td>
						</tr>
					<?
					$i++;
				}
			} 
			if($n_loja == 0) {
			?>
				<tr>
					<td colspan="7" class="nenhum">Nenhum pagamento confirmado.</td>
				</tr>
			<?
			}
			?>
			</tbody>
		</table>
		<? if ($n_loja > 0) { ?>
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

include('include/footer.php');

//Fechar conexoes
include("conn/close.php");
include("conn/close-sankhya.php");

?>