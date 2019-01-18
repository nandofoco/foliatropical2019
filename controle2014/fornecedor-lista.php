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
if(!empty($q)) $search = is_numeric($q) ? " AND CODPARC='$q' " : " AND (NOMEPARC LIKE '%$q%' OR EMAIL LIKE '%$q%' OR CGC_CPF LIKE '%$q%') ";

$sql_fornecedores = sqlsrv_query($conexao_sankhya, "SELECT p.CODPARC, p.NOMEPARC, p.RAZAOSOCIAL, p.IDENTINSCESTAD, p.EMAIL, p.TELEFONE, p.CGC_CPF, p.TIPPESSOA, p.CEP, p.CODEND, p.NUMEND, p.COMPLEMENTO, p.CODBAI, p.CODCID, p.FORNECEDOR, p.CODBCO, p.CODAGE, p.CODCTABCO, p.DTCAD, p.DTALTER, p.BLOQUEAR, c.CODCID, c.NOMECID, c.UF, u.CODUF, u.UF FROM TGFPAR p, TSICID c, TSIUFS u WHERE p.CODCID=c.CODCID AND c.UF=u.CODUF AND p.FORNECEDOR='S' $search ORDER BY p.NOMEPARC ASC", $conexao_params, $conexao_options);
$n_fornecedores = sqlsrv_num_rows($sql_fornecedores);


?>
<section id="conteudo">
	<header class="titulo">
		<h1>Fornecedores <span>Listagem</span></h1>
		<form id="busca-lista" class="busca-lista" method="get" action="<? echo SITE; ?>fornecedores/">
			<a href="<? echo SITE; ?>fornecedores/cadastro/" class="adicionar">+</a>
			<p class="coluna">
				<label for="busca-lista-input" class="infield">Pesquisar</label>
				<? if(!empty($q)){ ?><a href="<? echo SITE; ?>fornecedores/" class="limpar-busca">&times;</a><? } ?>
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
					<th><strong>CPF/CNPJ</strong><span></span></th>
					<th><strong>Cidade/UF</strong><span></span></th>
					<th>&nbsp;</th>
				</tr>
				<tr class="spacer"><td colspan="6">&nbsp;</td></tr>
			</thead>
			<tbody>
			<?
			
			if($n_fornecedores > 0)	 {

				$i=1;
				while($fornecedores = sqlsrv_fetch_array($sql_fornecedores)) {

					$fornecedores_cod = trim($fornecedores['CODPARC']);
					$fornecedores_nome = trim($fornecedores['NOMEPARC']);
					$fornecedores_email = trim($fornecedores['EMAIL']);
					$fornecedores_cpf = formatCPFCNPJ(trim($fornecedores['CGC_CPF']));
					$fornecedores_cidade = trim($fornecedores['NOMECID']);
					$fornecedores_estado = trim($fornecedores['UF']);
					$fornecedores_block = (bool) (trim($fornecedores['BLOQUEAR']) == 'S');
					$acao = ($fornecedores_block) ? 'desbloquear' : 'bloquear';

					$fornecedores_nome_exibir = (strlen($fornecedores_nome) > 25) ? substr($fornecedores_nome, 0, 25)."..." : $fornecedores_nome;
					?>
					<tr <? if($fornecedores_block) { echo 'class="block"'; } ?>>
						<td class="block"><a href="<? echo SITE; ?>e-fornecedor-gerenciar.php?c=<? echo $fornecedores_cod; ?>&a=<? echo $acao; ?>" class="block confirm" title="Tem certeza que deseja <? echo $acao; ?> esse fornecedor?"></a></td>
						<td <? if($fornecedores_nome != $fornecedores_nome_exibir) { echo 'title="'.utf8_encode($fornecedores_nome).'"'; } ?>>
							<? echo utf8_encode($fornecedores_nome_exibir); ?>
						</td>
						<td><? echo $fornecedores_email; ?></td>
						<td><? echo $fornecedores_cpf; ?></td>
						<td><? echo $fornecedores_cidade."/".$fornecedores_estado; ?></td>
						<td class="ctrl">
							<a href="<? echo SITE; ?>fornecedores/editar/<? echo $fornecedores_cod; ?>/" class="ver"></a>
							<a href="<? echo SITE; ?>e-fornecedor-gerenciar.php?c=<? echo $fornecedores_cod; ?>&a=deletar" class="excluir confirm" title="Tem certeza que deseja excluir esse fornecedor?"></a>
						</td>
					</tr>
					<?
					$i++;
				}
			} else {
			?>
				<tr>
					<td colspan="6" class="nenhum">Nenhum fornecedor encontrado.</td>
				</tr>
			<?
			}
			?>
			</tbody>
		</table>
		<? if ($n_fornecedores > 0) { ?>
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