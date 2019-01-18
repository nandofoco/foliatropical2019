<?

//Incluir funções básicas
include("include/includes.php");

//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");
include("include/header.php");

//-----------------------------------------------------------------//

?>
<section id="conteudo">
	<header class="titulo">
		<h1>Carnaval <span>Listagem</span></h1>
	</header>
	<section class="secao bottom">
		<?
		//busca eventos
		$sql_eventos = sqlsrv_query($conexao, "SELECT * FROM eventos WHERE D_E_L_E_T_='0' ORDER BY EV_ANO ASC", $conexao_params, $conexao_options);
		$nev = sqlsrv_num_rows($sql_eventos);
		?>
		<table class="lista">
			<tbody>
				<?
				if($nev > 0) {
					while ($eventos = sqlsrv_fetch_array($sql_eventos)) {
						$eventos_cod = $eventos['EV_COD'];
						$eventos_ano = $eventos['EV_ANO'];
						$eventos_nome = utf8_encode($eventos['EV_NOME']);
						$eventos_block = (bool) $eventos['EV_BLOCK'];
						$acao = ($eventos_block) ? "desbloquear" : "bloquear";
						
					?>
						<tr <? if($eventos_block) echo "class='block'"; ?>>
							<td class="block"><a href="<? echo SITE; ?>e-carnaval-gerenciar.php?c=<? echo $eventos_cod; ?>&a=<? echo $acao; ?>" class="block"></a></td>
							<td class="ano"><h2><? echo $eventos_ano; ?></h2></td>
							<td class="nome"><h3><? echo $eventos_nome; ?></h3></td>
							<td><? if($_SESSION['usuario-carnaval'] == $eventos_cod) { ?><span class="ativo"></span><? } else {?><a href="<? echo SITE; ?>e-carnaval-gerenciar.php?c=<? echo $eventos_cod; ?>&a=ativar" class="ativar"></a><? } ?></td>
							<td><a href="<? echo SITE; ?>carnaval/editar/<? echo $eventos_cod; ?>/" class="ver"></a></td>
							<td class="last"><a href="<? echo SITE; ?>e-carnaval-gerenciar.php?c=<? echo $eventos_cod; ?>&a=excluir" class="excluir confirm" title="Tem certeza que deseja excluir o <? echo $eventos_nome; ?>"></a></td>
						</tr>
					<?
					}
				} else {
				?>
					<tr>
						<td colspan="6" class="nenhum">Nenhum evento encontrado.</td>
					</tr>
				<?
				}
				?>
			</tbody>
		</table>
	</section>
</section>
<?

//-----------------------------------------------------------------//

include('include/footer.php');

//Fechar conexoes
include("conn/close.php");

?>