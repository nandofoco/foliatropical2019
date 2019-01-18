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

$tipo = $_GET['tipo'];
$dia = (int) $_GET['dia'];
$setor = (int) $_GET['setor'];
$fila = format($_GET['fila']);
$acao = format($_GET['a']);


$item_link;

if(!empty($tipo)) { $item_link .= !empty($item_link) ? '&' : '?';  $item_link .= 'tipo='.$tipo; }
if(!empty($acao)) { $item_link .= !empty($item_link) ? '&' : '?';  $item_link .= 'a='.$acao; }
if(!empty($dia)) { $item_link .= !empty($item_link) ? '&' : '?';  $item_link .= 'dia='.$dia; }
if(!empty($setor)) { $item_link .= !empty($item_link) ? '&' : '?';  $item_link .= 'setor='.$setor; }
if(!empty($fila)) { $item_link .= !empty($item_link) ? '&' : '?';  $item_link .= 'fila='.$fila; }

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

//buscar diferenca de datas
$sql_diferenca = sqlsrv_query($conexao, "SELECT 
	TOP 1 DATEDIFF(day, lo.LO_DATA_COMPRA, GETDATE()) as diferenca_datas,
	* 
	
	FROM foliatropical.dbo.loja lo,
	foliatropical.dbo.loja_itens li,
	foliatropical.dbo.vendas ve 
	
	WHERE lo.LO_COD=li.LI_COMPRA
	AND li.D_E_L_E_T_='0'
	AND lo.LO_BLOCK='0'
	AND lo.D_E_L_E_T_='0'
	AND li.LI_INGRESSO=ve.VE_COD
	AND ve.VE_EVENTO='$evento'
	AND ve.VE_BLOCK='0'
	AND ve.D_E_L_E_T_='0'
	AND lo.LO_EVENTO='$evento' 

	ORDER BY lo.LO_DATA_COMPRA ASC", $conexao_params, $conexao_options);

$ar_diferenca = sqlsrv_fetch_array($sql_diferenca, SQLSRV_FETCH_ASSOC);

$diferenca = $ar_diferenca['diferenca_datas'];

$p = (int) $_GET['p'];
if(!($p > 0)) $p = 1;
$limite = 30;
$inicio = (($p*$limite)-($limite-1));
$fim = $inicio+$limite;

//array dos últimos 6 dias
$lista_dias = array();

for($i=$inicio; $i<$fim; $i++) {
	$add_dia = strtotime(date('Y-m-d') . ' -'.($i-1).' day');
	// $add_dia = strtotime(date('2014-09-13') . ' -'.$i.' day');
	$dia = date("d/m/Y",$add_dia);

	array_push($lista_dias, "'$dia'");
	
	$query_dias .= "SUM(CASE WHEN data='".$dia."' THEN valor_dia ELSE 0 END) AS valor_dia".$i.",";
 	$query_dias .= "SUM(CASE WHEN data='".$dia."' THEN qtde_dia ELSE 0 END) AS qtde_dia".$i.",";
 	$query_dias .= "'".$dia."' AS dia".$i.",";
}

$lista_dias = implode(", ", $lista_dias);

//busca dados por dia
$sql_relatorio_dias = sqlsrv_query($conexao, "SELECT
	$query_dias
	MAX(data)

	FROM (
		SELECT SUM((li.LI_VALOR_TABELA - li.LI_DESCONTO + li.LI_OVER_INTERNO) - ((li.LI_VALOR_TABELA - li.LI_DESCONTO + li.LI_OVER_INTERNO) * lo.LO_COMISSAO / 100) - ISNULL(CASE WHEN tx.TX_TAXA IS NOT NULL THEN li.LI_VALOR * (tx.TX_TAXA / 100) ELSE 0 END,0)) AS valor_dia,
		COUNT(lo.LO_COD) AS qtde_dia,
		MAX(CONVERT(VARCHAR, lo.LO_DATA_COMPRA, 103)) as data
		FROM loja_itens li, vendas ve, loja lo
		LEFT JOIN taxa_cartao tx
			ON (lo.LO_FORMA_PAGAMENTO=1 AND lo.LO_CARTAO=tx.TX_CARTAO AND lo.LO_PARCELAS >= tx.TX_PARCELAS_INICIO AND lo.LO_PARCELAS <= tx.TX_PARCELAS_FIM)
			OR (lo.LO_FORMA_PAGAMENTO=6 AND tx.TX_CARTAO='pos')
			OR (lo.LO_FORMA_PAGAMENTO=2014 AND tx.TX_CARTAO='pos')

		WHERE lo.LO_COD=li.LI_COMPRA
		AND li.D_E_L_E_T_='0'
		AND lo.LO_BLOCK='0'
		AND lo.D_E_L_E_T_='0'
		AND li.LI_INGRESSO=ve.VE_COD
		AND ve.VE_EVENTO='$evento'
		AND ve.VE_BLOCK='0'
		AND ve.D_E_L_E_T_='0'
		AND lo.LO_EVENTO='$evento'
		$search
		GROUP BY lo.LO_DATA_COMPRA
	) S WHERE data IN($lista_dias)

	", $conexao_params, $conexao_options);

$n_dias = sqlsrv_num_rows($sql_relatorio_dias);
$ar_relatorio_dias = sqlsrv_fetch_array($sql_relatorio_dias, SQLSRV_FETCH_ASSOC);


?>
<section id="conteudo" class="relatorio-lista-voucher wide">
	<!-- <header class="titulo">
		<h1>Vouchers <span>Confirmados</span></h1>
	</header> -->
	<section class="secao bottom">
		<table class="lista mini tablesorter-nopager">
			<thead>
				<tr>
					<th class="first">&nbsp;</th>
					<th><strong>Data</strong><span></span></th>
					<th><strong>Quantidade</strong><span></span></th>
					<th><strong>Valor</strong><span></span></th>
				</tr>
				<tr class="spacer"><td colspan="<? echo ($adm) ? '6' : '5' ; ?>">&nbsp;</td></tr>
			</thead>
			<tbody>
			<?
			
			if($n_dias !== false) {

				$lista_dias = explode(", ", $lista_dias);
				$lista_dias = str_replace("'", "", $lista_dias);

				for($i=$inicio; $i<$fim; $i++) {

				?>
				<tr>
					<td class="ctrl first">
						<a href="<? echo SITE; ?>relatorios-lista-produtos-dias-detalhes.php<? echo $item_link; echo !empty($item_link) ? '&' : '?'; ?>data=<? echo urlencode($lista_dias[$i-$inicio]); ?>" class="ver"></a>
					</td>
					<td><? echo $lista_dias[$i-$inicio]; ?></td>
					<td><? echo (int) $ar_relatorio_dias['qtde_dia'.$i]; ?></td>
					<td>R$ <? echo number_format($ar_relatorio_dias['valor_dia'.$i], 2, ',', '.'); ?></td>
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
			
			// $total_paginas = ceil($diferenca/$limite);
			
		?>
        <? /*<div class="pager-tablesorter">
	        <a href="<? echo SITE; ?>relatorios-lista-produtos-dias.php<? echo $item_link; ?>" class="first"></a>
	        <a href="<? echo SITE; ?>relatorios-lista-produtos-dias.php<? echo $item_link; echo !empty($item_link) ? '&' : '?'; ?>p=<? echo ($p > 1) ? ($p - 1) : 1; ?>" class="prev"></a>
	        <span class="pagedisplay"><? echo $p; ?>/<? echo $total_paginas; ?></span>
	        <a href="<? echo SITE; ?>relatorios-lista-produtos-dias.php<? echo $item_link; echo !empty($item_link) ? '&' : '?'; ?>p=<? echo ($p < $total_paginas) ? ($p + 1) : $total_paginas; ?>" class="next"></a>
	        <a href="<? echo SITE; ?>relatorios-lista-produtos-dias.php<? echo $item_link; echo !empty($item_link) ? '&' : '?'; ?>p=<? echo $total_paginas; ?>" class="last"></a>
	        <!-- <input type="hidden" class="pagesize" value="30" /> -->
        </div>*/
		
		$pagina_de = date("d/m/y",strtotime(' -'.($inicio-1).' day'));
		$pagina_ate = date("d/m/y",strtotime(' -'.($inicio+$limite-2).' day'));
		
		?>

		<div class="pager-tablesorter big">
	        <a href="<? echo SITE; ?>relatorios-lista-produtos-dias.php<? echo $item_link; ?>" class="first"></a>
	        <a href="<? echo SITE; ?>relatorios-lista-produtos-dias.php<? echo $item_link; echo !empty($item_link) ? '&' : '?'; ?>p=<? echo ($p > 1) ? ($p - 1) : 1; ?>" class="prev"></a>
	        <span class="pagedisplay"><? echo $pagina_de; ?> a <? echo $pagina_ate; ?></span>
	        <a href="<? echo SITE; ?>relatorios-lista-produtos-dias.php<? echo $item_link; echo !empty($item_link) ? '&' : '?'; ?>p=<? echo $p + 1; ?>" class="next"></a>
	        <!--<a href="<? echo SITE; ?>relatorios-lista-produtos-dias.php<? echo $item_link; echo !empty($item_link) ? '&' : '?'; ?>p=<? echo $total_paginas; ?>" class="last"></a>-->

        </div>

        <? } ?>
	</section>

	<a href="javascript:history.back(1);">Voltar</a>
	
</section>
<?

//-----------------------------------------------------------------//

include('include/footer.php');

//Fechar conexoes
include("conn/close.php");
include("conn/close-sankhya.php");

?>