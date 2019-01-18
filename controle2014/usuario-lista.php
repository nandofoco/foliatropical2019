<?

//Incluir funções básicas
include("include/includes.php");

//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");
include("include/header.php");

//-----------------------------------------------------------------//

$q = format($_GET['q']);
if(!empty($q)) $search = is_numeric($q) ? " AND US_COD='$q' " : " AND (US_NOME LIKE '%$q%' OR US_EMAIL LIKE '%$q%') ";

$sql_usuarios = sqlsrv_query($conexao, "SELECT * FROM usuarios WHERE D_E_L_E_T_='0' $search ORDER BY US_NOME ASC", $conexao_params, $conexao_options);
$n_usuarios = sqlsrv_num_rows($sql_usuarios);

?>
<section id="conteudo">
	<header class="titulo">
		<h1>Usuários <span>Listagem</span></h1>
		<form id="busca-lista" class="busca-lista" method="get" action="<? echo SITE; ?>usuarios/">
			<a href="<? echo SITE; ?>usuarios/cadastro/" class="adicionar">+</a>
			<p class="coluna">
				<label for="busca-lista-input" class="infield">Pesquisar</label>
				<? if(!empty($q)){ ?><a href="<? echo SITE; ?>usuarios/" class="limpar-busca">&times;</a><? } ?>
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
					<th><strong>Email</strong><span></span></th>
					<th><strong>Grupo</strong> <span></span></th>
					<th><strong>Código</strong><span></span></th>
					<th>&nbsp;</th>
				</tr>
				<tr class="spacer"><td colspan="6">&nbsp;</td></tr>
			</thead>
			<tbody>
			<?
			
			if($n_usuarios > 0)	 {

				$i=1;
				while($usuarios = sqlsrv_fetch_array($sql_usuarios)) {

					$usuarios_cod = $usuarios['US_COD'];
					$usuarios_nome = utf8_encode($usuarios['US_NOME']);
					$usuarios_email = $usuarios['US_EMAIL'];
					$usuarios_grupo = $usuarios['US_GRUPO'];
					$usuarios_block = (bool) $usuarios['US_BLOCK'];
					$acao = ($usuarios_block) ? 'desbloquear' : 'bloquear';

					?>
					<tr <? if($usuarios_block) { echo 'class="block"'; } ?>>
						<td class="block"><a href="<? echo SITE; ?>e-usuario-gerenciar.php?c=<? echo $usuarios_cod; ?>&a=<? echo $acao; ?>" class="block confirm" title="Tem certeza que deseja <? echo $acao; ?> esse usuario?"></a></td>
						<td><? echo $usuarios_nome; ?></td>
						<td><? echo $usuarios_email; ?></td>
						<td><? echo $usuarios_grupo; ?></td>
						<td>USUARIO<? echo str_pad($usuarios_cod, 3, '0', STR_PAD_LEFT); ?></td>
						<td class="ctrl">
							<a href="<? echo SITE; ?>usuarios/editar/<? echo $usuarios_cod; ?>/" class="ver"></a>
							<a href="<? echo SITE; ?>e-usuario-gerenciar.php?c=<? echo $usuarios_cod; ?>&a=deletar" class="excluir confirm" title="Tem certeza que deseja excluir esse usuario?"></a>
						</td>
					</tr>
					<?
					$i++;
				}
			} else {
			?>
				<tr>
					<td colspan="5" class="nenhum">Nenhum usuário encontrado.</td>
				</tr>
			<?
			}
			?>
			</tbody>
		</table>

		<? if ($n_usuarios > 0) { ?>
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

?>