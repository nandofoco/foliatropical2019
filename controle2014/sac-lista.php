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

$q = format($_GET['q']);
if(!empty($q)) $search = "AND (CO_NOME LIKE '%$q%' OR CO_SETOR LIKE '%$q%' OR CO_ATENDENTE LIKE '%$q%' OR CO_RESPONSAVEL LIKE '%$q%')";

$sql_contatos = mysql_query("SELECT *, DATE_FORMAT(CO_DATA, '%d/%m/%Y') as data FROM contato WHERE D_E_L_E_T_<>'*' $search ORDER BY CO_DATA DESC");
$n_contatos = mysql_num_rows($sql_contatos);

?>
<section id="conteudo" class="larger">
	<header class="titulo">
		<h1>SAC <span>Listagem</span></h1>
		<form id="busca-lista" class="busca-lista" method="get" action="<? echo SITE; ?>sac/">
			<a href="<? echo SITE; ?>sac/cadastro/" class="adicionar">+</a>
			<p class="coluna">
				<label for="busca-lista-input" class="infield">Pesquisar</label>
				<? if(!empty($q)){ ?><a href="<? echo SITE; ?>sac/" class="limpar-busca">&times;</a><? } ?>
				<input type="text" name="q" class="input" id="busca-lista-input" value="<? echo utf8_encode($q); ?>" />
			</p>
			<input type="submit" class="submit" value="" />
		</form>
	</header>
	<section class="secao bottom">
		<table class="lista tablesorter">
			<thead>
				<tr>
					<th><strong>Data da Solicitação</strong><span></span></th>
					<th><strong>Status</strong><span></span></th>
					<th><strong>Contato Via</strong><span></span></th>
					<th><strong>Cliente</strong><span></span></th>
					<th><strong>Assunto</strong> <span></span></th>
					<th><strong>Setor</strong> <span></span></th>
					<th><strong>Responsável</strong> <span></span></th>
					<th>&nbsp;</th>
				</tr>
				<tr class="spacer"><td colspan="6">&nbsp;</td></tr>
			</thead>
			<tbody>
			<?
			
			if($n_contatos > 0)	 {

				$i=1;
				while($contatos = mysql_fetch_array($sql_contatos)) {

					$contato_cod = utf8_encode($contatos['CO_COD']);
					$contato_status = utf8_encode($contatos['CO_STATUS']);
					$contato_data = ($contatos['data']);
					$contato_nome = utf8_encode($contatos['CO_NOME']);
					$contato_via = utf8_encode($contatos['CO_VIA']);
					$contato_assunto = utf8_encode($contatos['CO_ASSUNTO']);
					$contato_setor = utf8_encode($contatos['CO_SETOR']);
					$contato_atendente = utf8_encode($contatos['CO_ATENDENTE']);
					$contato_responsavel = utf8_encode($contatos['CO_RESPONSAVEL']);

					$contato_nome_exibir = (strlen($contato_nome) > 25) ? substr($contato_nome, 0, 25)."..." : $contato_nome;

					?>
					<tr>
						<td class="first"><? echo $contato_data; ?></td>
						<td><? echo $contato_status; ?></td>
						<td><? echo $contato_via; ?></td>
						<td><? echo $contato_nome_exibir; ?></td>
						<td><? echo $contato_assunto; ?></td>
						<td><? echo $contato_setor; ?></td>
						<td><? echo $contato_responsavel; ?></td>
						<td class="ctrl">
							<a href="<? echo SITE; ?>sac/editar/<? echo $contato_cod; ?>/" class="ver"></a>
							<a href="<? echo SITE; ?>e-sac-gerenciar.php?c=<? echo $contato_cod; ?>&a=deletar" class="excluir confirm" title="Tem certeza que deseja excluir esse contato?"></a>
						</td>
					</tr>
					<?
					$i++;
				}
			} else {
			?>
				<tr>
					<td colspan="5" class="nenhum">Nenhum atendimento encontrado.</td>
				</tr>
			<?
			}
			?>
			</tbody>
		</table>

		<? if ($n_contatos > 0) { ?>
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