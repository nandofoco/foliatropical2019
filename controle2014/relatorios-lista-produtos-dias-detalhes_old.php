<?

//Incluir funções básicas
include("include/includes.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");

//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");

//-----------------------------------------------------------------//

$evento = (int) $_SESSION['usuario-carnaval'];
$adm = ($_SESSION['us-grupo'] == 'ADM') ? true : false;

define('CODRESERVA','5');
define('CODPERMUTA','8,9');

//-----------------------------------------------------------------//

$data = todate(format($_GET['data']), 'ddmmaaaa');

/*if(!empty($tipo)) $search .= " AND v.VE_TIPO='$tipo' ";
if(!empty($dia)) $search .= " AND v.VE_DIA='$dia' ";
if(!empty($setor)) $search .= " AND v.VE_SETOR='$setor' ";
if(!empty($fila)) $search .= " AND v.VE_FILA='$fila' ";

switch ($acao) {
	case 'pagos':
		$search_acao = "AND LO_PAGO='1' AND LO_FORMA_PAGAMENTO NOT IN (".CODPERMUTA.") ";
	break;	
	case 'aguardando':
		$search_acao = "AND LO_PAGO='0' AND LO_FORMA_PAGAMENTO NOT IN (".CODPERMUTA.") AND LO_FORMA_PAGAMENTO<>'".CODRESERVA."' ";
	break;
	case 'reservados':
		$search_acao = "AND LO_PAGO='0' AND LO_FORMA_PAGAMENTO NOT IN (".CODPERMUTA.") AND LO_FORMA_PAGAMENTO='".CODRESERVA."' ";
	break;
	case 'permuta':
		$search_acao = "AND LO_FORMA_PAGAMENTO IN (".CODPERMUTA.") ";
	break;
}*/

include("include/relatorios-parametros.php");

if(!empty($filtros['tipos'][$tipo])) $search .= " AND ".$filtros['tipos'][$tipo];
if(!empty($dia)) $search .= " AND VE_DIA=".$dia;

$search_acao = str_replace('lo.', '', $search_acao);

//busca dados por dia
$sql_relatorio_dias = sqlsrv_query($conexao, "SELECT 
    MAX(li.LI_COMPRA) AS VOUCHER,
    MAX(li.LI_VALOR_TABELA) AS VALOR_UNITARIO_TABELA, 
    SUM(li.LI_DESCONTO) AS DESCONTO,
    MAX(ve.VE_TIPO) AS TIPO,
    MAX(ve.VE_DIA) AS DIA, 
    COUNT(li.LI_INGRESSO) AS QUANTIDADE,
    MAX(li.LI_NOME) AS CLIENTE,
    MAX(lo.LO_VENDEDOR) AS VENDEDOR,
    MAX(lo.LO_PAGO) AS PAGO,
    SUM((li.LI_VALOR_TABELA - li.LI_DESCONTO + li.LI_OVER_INTERNO) - ((li.LI_VALOR_TABELA - li.LI_DESCONTO + li.LI_OVER_INTERNO) * lo.LO_COMISSAO / 100) - ISNULL(CASE WHEN tx.TX_TAXA IS NOT NULL THEN li.LI_VALOR * (tx.TX_TAXA / 100) ELSE 0 END,0)) AS valor_compra
	
	FROM loja_itens li, vendas ve, loja lo
	
	LEFT JOIN taxa_cartao tx 
		ON (lo.LO_FORMA_PAGAMENTO=1 AND lo.LO_CARTAO=tx.TX_CARTAO AND lo.LO_PARCELAS >= tx.TX_PARCELAS_INICIO AND lo.LO_PARCELAS <= tx.TX_PARCELAS_FIM)
		OR (lo.LO_FORMA_PAGAMENTO=6 AND tx.TX_CARTAO='pos') 

	WHERE lo.LO_COD=li.LI_COMPRA AND li.D_E_L_E_T_='0' AND lo.LO_BLOCK='0' AND lo.D_E_L_E_T_='0' AND li.LI_INGRESSO=ve.VE_COD AND ve.VE_EVENTO='$evento' AND ve.VE_BLOCK='0' AND ve.D_E_L_E_T_='0' 

	AND lo.LO_EVENTO='$evento' AND CONVERT(DATE, lo.LO_DATA_COMPRA) = '$data' GROUP BY li.LI_INGRESSO", $conexao_params, $conexao_options);

	//CONVERT(VARCHAR, lo.LO_DATA_COMPRA, 103) = '$data'

$n_dias = sqlsrv_num_rows($sql_relatorio_dias);
// $ar_relatorio_dias = sqlsrv_fetch_array($sql_relatorio_dias, SQLSRV_FETCH_ASSOC);

?>
<section id="conteudo" class="relatorio-lista-voucher">
	<!-- <header class="titulo">
		<h1>Vouchers <span>Confirmados</span></h1>
	</header> -->
	<section class="secao bottom">
		<table class="lista mini tablesorter-nopager">
			<thead>
				<tr>
					<th class="first"><strong>Voucher</strong><span></span></th>
					<th><strong>Cliente</strong><span></span></th>
					<th><strong>Valor Un. Tabela</strong><span></span></th>
					<th><strong>Qtd.</strong><span></span></th>
					<th><strong>Desconto</strong><span></span></th>
					<th><strong>Valor Tabela</strong><span></span></th>
					<th><strong>Valor Total</strong><span></span></th>
					<th><strong>Pago</strong><span></span></th>
				</tr>
				<tr class="spacer"><td colspan="<? echo ($adm) ? '6' : '5' ; ?>">&nbsp;</td></tr>
			</thead>
			<tbody>
			<?
			
			if($n_dias > 0) {

				while($dados = sqlsrv_fetch_array($sql_relatorio_dias, SQLSRV_FETCH_ASSOC)) {


					$dados_voucher = utf8_encode($dados['VOUCHER']);
					$dados_cliente = utf8_encode($dados['CLIENTE']);
					$dados_valor_unitario_tabela = $dados['VALOR_UNITARIO_TABELA'];
					$dados_valor_unitario_tabela = $dados['VALOR_UNITARIO_TABELA'];
					$dados_quantidade = $dados['QUANTIDADE'];
					$dados_desconto = $dados['DESCONTO'];
					$dados_valor_compra = $dados['valor_compra'];
					$dados_pago = ((bool) $dados['PAGO']) ? 'Sim' : '';
				?>
				<tr>
					<td class="first"><? echo $dados_voucher ?></td>
					<td><? echo $dados_cliente ?></td>
					<td>R$ <? echo number_format($dados_valor_unitario_tabela, 2, ',', '.'); ?></td>
					<td><? echo (int) $dados_quantidade; ?></td>
					<td>R$ <? echo number_format(($dados_valor_unitario_tabela*$dados_quantidade), 2, ',', '.'); ?></td>
					<td>R$ <? echo number_format(($dados_desconto*$dados_quantidade), 2, ',', '.'); ?></td>
					<td>R$ <? echo number_format(($dados_valor_compra), 2, ',', '.'); ?></td>
					<td><? echo $dados_pago ?></td>
				</tr>
				<?
				}
				$exibe_loja = true;
			} else {
			?>
				<tr>
					<td colspan="<? echo ($adm) ? '6' : '5' ; ?>" class="nenhum">Nenhum voucher encontrado</td>
				</tr>
			<?
			}
			?>
			</tbody>
		</table>
		<?
		if ($exibe_loja) {
			
			$total_paginas = ceil($diferenca/$limite);
			$item_link;

			// if(!empty($tipo)) { $item_link .= !empty($item_link) ? '&' : '?';  $item_link .= 'tipo='.$tipo; }
			// if(!empty($acao)) { $item_link .= !empty($item_link) ? '&' : '?';  $item_link .= 'a='.$acao; }
			// if(!empty($dia)) { $item_link .= !empty($item_link) ? '&' : '?';  $item_link .= 'dia='.$dia; }
			// if(!empty($setor)) { $item_link .= !empty($item_link) ? '&' : '?';  $item_link .= 'setor='.$setor; }
			// if(!empty($fila)) { $item_link .= !empty($item_link) ? '&' : '?';  $item_link .= 'fila='.$fila; }

		?>
        <div class="pager-tablesorter">
	        <a href="<? echo SITE; ?>relatorios-lista-produtos-dias.php<? echo $item_link; ?>" class="first"></a>
	        <a href="<? echo SITE; ?>relatorios-lista-produtos-dias.php<? echo $item_link; echo !empty($item_link) ? '&' : '?'; ?>p=<? echo ($p > 1) ? ($p - 1) : 1; ?>" class="prev"></a>
	        <span class="pagedisplay"><? echo $p; ?>/<? echo $total_paginas; ?></span>
	        <a href="<? echo SITE; ?>relatorios-lista-produtos-dias.php<? echo $item_link; echo !empty($item_link) ? '&' : '?'; ?>p=<? echo ($p < $total_paginas) ? ($p + 1) : $total_paginas; ?>" class="next"></a>
	        <a href="<? echo SITE; ?>relatorios-lista-produtos-dias.php<? echo $item_link; echo !empty($item_link) ? '&' : '?'; ?>p=<? echo $total_paginas; ?>" class="last"></a>
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