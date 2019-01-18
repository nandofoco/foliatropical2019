<?

//Incluir funções básicas
include("include/includes.php");

//conexao Sankhya
include("conn/conn-sankhya.php");

//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");
include("include/header.php");

//-----------------------------------------------------------------//

$q = format($_GET['q']);
if(!empty($q)) {

	if(!is_numeric($q)) {

		// $search_query = is_numeric($q) ? " AND CODPARC='$q' " : " AND NOMEPARC LIKE '%$q%' ";
		$search_query = " AND NOMEPARC LIKE '%$q%' ";

		//$sql_search = sqlsrv_query($conexao_sankhya, "SELECT CODPARC, CLIENTE, VENDEDOR FROM TGFPAR WHERE (CLIENTE='S' OR VENDEDOR='S') AND BLOQUEAR='N' $search_query ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
		$sql_search = sqlsrv_query($conexao_sankhya, "SELECT CODPARC, CLIENTE, VENDEDOR FROM TGFPAR WHERE VENDEDOR='S' AND BLOQUEAR='N' $search_query ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_search) > 0) {
			$ar_clientes_cods = $ar_parceiros_cods = array();
			while ($cods = sqlsrv_fetch_array($sql_search)) {
				if($cods['CLIENTE'] == 'S') array_push($ar_clientes_cods, $cods['CODPARC']);
				if($cods['VENDEDOR'] == 'S') array_push($ar_parceiros_cods, $cods['CODPARC']);
			}
			
			$search = " AND (";

			if(count($ar_parceiros_cods) > 0) {
				$parceiros_cods = implode(",", $ar_parceiros_cods);
				if(count($ar_clientes_cods) > 0) $search .= " OR ";
				$search .= " VE_PARCEIRO IN ($parceiros_cods) ";
			}

			$search .= ") ";

		} else {
			// $search = " AND LO_CLIENTE IN ('') ";
			$nosearch = true;
		}

	} else {
		$search = " AND VE_COD='$q' ";
	}
}

$sql_vendedor_externo = sqlsrv_query($conexao, "SELECT * FROM vendedor_externo WHERE D_E_L_E_T_=0 $search ORDER BY VE_NOME ASC", $conexao_params, $conexao_options);
$n_vendedor_externo = sqlsrv_num_rows($sql_vendedor_externo);

?>
<section id="conteudo">
	<header class="titulo">
		<h1>Vendedor Externo <span>Listagem</span></h1>
		<form id="busca-lista" class="busca-lista" method="get" action="<? echo SITE; ?>vendedor-externo/">
			<a href="<? echo SITE; ?>vendedor-externo/cadastro/" class="adicionar">+</a>
			<p class="coluna">
				<label for="busca-lista-input" class="infield">Pesquisar</label>
				<? if(!empty($q)){ ?><a href="<? echo SITE; ?>vendedor-externo/" class="limpar-busca">&times;</a><? } ?>
				<input type="text" name="q" class="input" id="busca-lista-input" value="<? echo utf8_encode($q); ?>" />
			</p>
			<input type="submit" class="submit" value="" />
		</form>
	</header>
	<section class="secao bottom">
		<table class="lista tablesorter">
			<thead>
				<tr>
					<th>&nbsp;</th>
					<th><strong>Nome</strong><span></span></th>
					<th><strong>Parceiro</strong><span></span></th>
					<th><strong>E-mail</strong><span></span></th>
					<th><strong>Telefone</strong><span></span></th>
					<th>&nbsp;</th>
				</tr>
				<tr class="spacer"><td colspan="6">&nbsp;</td></tr>
			</thead>
			<tbody>
			<?
			
			if($n_vendedor_externo > 0) {

				$i=1;
				while($vendedor_externo = sqlsrv_fetch_array($sql_vendedor_externo)) {

					$vendedor_externo_cod = $vendedor_externo['VE_COD'];
					$vendedor_externo_parceiro_cod = $vendedor_externo['VE_PARCEIRO'];
					$vendedor_externo_nome = utf8_encode($vendedor_externo['VE_NOME']);
					$vendedor_externo_email = utf8_encode($vendedor_externo['VE_EMAIL']);
					$vendedor_externo_telefone = utf8_encode($vendedor_externo['VE_TEL']);
					$vendedor_externo_block = (bool) ($vendedor_externo['VE_BLOCK'] == '1');
					$acao = ($vendedor_externo_block) ? 'desbloquear' : 'bloquear';

					unset($loja_parceiro, $loja_parceiro_exibir);
					
					// $loja_cliente = utf8_encode($loja['CL_NOME']);
					$sql_parceiro = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 NOMEPARC, CODPARC FROM TGFPAR WHERE CODPARC='$vendedor_externo_parceiro_cod' AND VENDEDOR='S' AND BLOQUEAR='N' ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
					if(sqlsrv_num_rows($sql_parceiro) > 0) {
						while($vendedor_externo_parceiro_ar = sqlsrv_fetch_array($sql_parceiro)) {
							$vendedor_externo_parceiro = trim($vendedor_externo_parceiro_ar['NOMEPARC']);
							$vendedor_externo_parceiro_exibir = (strlen($vendedor_externo_parceiro) > 20) ? substr($vendedor_externo_parceiro, 0, 20)."..." : $vendedor_externo_parceiro;					
						}
					}
					
					$vendedor_externo_nome_exibir = (strlen($vendedor_externo_nome) > 20) ? substr($vendedor_externo_nome, 0, 20)."..." : $vendedor_externo_nome;				

					
					?>
					<tr <? if($vendedor_externo_block) { echo 'class="block"'; } ?>>
						<td class="block"><a href="<? echo SITE; ?>e-vendedor-externo-gerenciar.php?c=<? echo $vendedor_externo_cod; ?>&a=<? echo $acao; ?>" class="block confirm" title="Tem certeza que deseja <? echo $acao; ?> esse vendedor externo?"></a></td>
						<td <? if($vendedor_externo_nome != $vendedor_externo_nome_exibir) { echo 'title="'.utf8_encode($vendedor_externo_nome).'"'; } ?>>
							<? echo utf8_encode($vendedor_externo_nome_exibir); ?>
						</td>
						<td <? if($vendedor_externo_parceiro != $vendedor_externo_parceiro_exibir) { echo 'title="'.utf8_encode($vendedor_externo_parceiro).'"'; } ?>>
							<? echo utf8_encode($vendedor_externo_parceiro_exibir); ?>
						</td>
						<td><? echo $vendedor_externo_email; ?></td>
						<td><? echo $vendedor_externo_telefone; ?></td>
						<td class="ctrl">
							<a href="<? echo SITE; ?>vendedor-externo/editar/<? echo $vendedor_externo_cod; ?>/" class="ver"></a>
							<a href="<? echo SITE; ?>e-vendedor-externo-gerenciar.php?c=<? echo $vendedor_externo_cod; ?>&a=deletar" class="excluir confirm" title="Tem certeza que deseja excluir esse vendedor externo?"></a>
						</td>
					</tr>
					<?
					$i++;
				}
			} else {
			?>
				<tr>
					<td colspan="6" class="nenhum">Nenhum vendedor externo encontrado.</td>
				</tr>
			<?
			}
			?>
			</tbody>
		</table>
		<? if ($n_vendedor_externo > 0) { ?>
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