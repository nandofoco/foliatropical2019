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
$p = (int) $_GET['p'];
if(!($p > 0)) $p = 1;


$q = format($_GET['q']);
if(!empty($q)) $search = is_numeric($q) ? " AND CODPARC='$q' " : " AND (NOMEPARC LIKE '%$q%' OR EMAIL LIKE '%$q%' OR CGC_CPF LIKE '%$q%') ";



	$sql_parceiros = sqlsrv_query($conexao_sankhya, "SELECT p.CODPARC, p.NOMEPARC, p.RAZAOSOCIAL, p.IDENTINSCESTAD, p.EMAIL, p.TELEFONE, p.CGC_CPF, p.TIPPESSOA, p.CEP, p.CODEND, p.NUMEND, p.COMPLEMENTO, p.CODBAI, p.CODCID, p.VENDEDOR, p.CODBCO, p.CODAGE, p.CODCTABCO, p.DTCAD, p.DTALTER, p.BLOQUEAR, c.CODCID, c.NOMECID, c.UF, u.CODUF, u.UF FROM TGFPAR p, TSICID c, TSIUFS u WHERE p.CODCID=c.CODCID AND c.UF=u.CODUF AND p.VENDEDOR='S' $search ORDER BY p.NOMEPARC ASC", $conexao_params, $conexao_options);

	$n_parceiros = sqlsrv_num_rows($sql_parceiros);

if (!$q)
{
	$sql_parceiros = sqlsrv_query($conexao_sankhya, "	
	DECLARE @PageNumber INT;
	DECLARE @PageSize INT;
	DECLARE @TotalPages INT;

	SET @PageSize = 20;
	SET @PageNumber = $p;

	IF @PageNumber = 0 BEGIN
	SET @PageNumber = 1
	END;

	SET @TotalPages = CEILING(CONVERT(NUMERIC(20,10), ISNULL((SELECT COUNT(*) FROM PARCEIROS), 0)) / @PageSize);

	WITH parceiro(NumeroLinha, CODPARC, NOMEPARC, RAZAOSOCIAL, IDENTINSCESTAD, EMAIL, TELEFONE, CGC_CPF, TIPPESSOA, CEP, CODEND, NUMEND, COMPLEMENTO, NOMECID, UF, CODBAI, CODCID, VENDEDOR, CODBCO, CODAGE, CODCTABCO, DTCAD, DTALTER, BLOQUEAR)
	AS (
	SELECT ROW_NUMBER() OVER (ORDER BY NOMEPARC) AS NumeroLinha,
	
	CODPARC, 
	NOMEPARC, 
	RAZAOSOCIAL, 
	IDENTINSCESTAD, 
	EMAIL, 
	TELEFONE, 
	CGC_CPF, 
	TIPPESSOA, 
	CEP, 
	CODEND, 
	NUMEND, 
	COMPLEMENTO, 
	NOMECID,
	UF,
	CODBAI, 
	CODCID, 
	VENDEDOR, 
	CODBCO, 
	CODAGE, 
	CODCTABCO,
	CONVERT(CHAR, DTCAD, 103) AS DTCAD, 
	CONVERT(CHAR, DTALTER, 103) AS DTALTER, 
	BLOQUEAR
	FROM PARCEIROS)
	SELECT @TotalPages AS TOTAL, @PageNumber AS PAGINA, NumeroLinha, CODPARC, NOMEPARC, RAZAOSOCIAL, IDENTINSCESTAD, EMAIL, TELEFONE, CGC_CPF, TIPPESSOA, CEP, CODEND, NUMEND, COMPLEMENTO, NOMECID,UF, CODBAI, CODCID, VENDEDOR, CODBCO, CODAGE, CODCTABCO, DTCAD, DTALTER, BLOQUEAR
	FROM parceiro
	WHERE  NumeroLinha BETWEEN ( ( ( @PageNumber - 1 ) * @PageSize ) + 1 ) AND ( @PageNumber * @PageSize ) ORDER BY NOMEPARC;

	", $conexao_params, $conexao_options);
}


?>
<section id="conteudo">
	<header class="titulo">
		<h1>Parceiros <span>Listagem</span></h1>
		<form id="busca-lista" class="busca-lista" method="get" action="<? echo SITE; ?>parceiros/">
			<a href="<? echo SITE; ?>parceiros/cadastro/" class="adicionar">+</a>
			<p class="coluna">
				<label for="busca-lista-input" class="infield">Pesquisar</label>
				<? if(!empty($q)){ ?><a href="<? echo SITE; ?>parceiros/" class="limpar-busca">&times;</a><? } ?>
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
			
			if($n_parceiros > 0) {

				$i=1;
				while($parceiros = sqlsrv_fetch_array($sql_parceiros)) {

					$total_paginas = $parceiros['TOTAL'];

					$parceiros_cod = $parceiros['CODPARC'];
					// $parceiros_nome = trim(utf8_encode($parceiros['NOMEPARC']));
					$parceiros_nome = trim($parceiros['NOMEPARC']);
					$parceiros_email = trim($parceiros['EMAIL']);
					$parceiros_cpf = formatCPFCNPJ(trim($parceiros['CGC_CPF']));
					$parceiros_cidade = trim(utf8_encode($parceiros['NOMECID']));
					$parceiros_estado = trim(utf8_encode($parceiros['UF']));
					$parceiros_block = (bool) ($parceiros['BLOQUEAR'] == 'S');
					$acao = ($parceiros_block) ? 'desbloquear' : 'bloquear';

					$parceiros_nome_exibir = (strlen($parceiros_nome) > 25) ? substr($parceiros_nome, 0, 25)."..." : $parceiros_nome;

					?>
					<tr <? if($parceiros_block) { echo 'class="block"'; } ?>>
						<td class="block"><a href="<? echo SITE; ?>e-parceiro-gerenciar.php?c=<? echo $parceiros_cod; ?>&a=<? echo $acao; ?>" class="block confirm" title="Tem certeza que deseja <? echo $acao; ?> esse parceiro?"></a></td>
						<td <? if($parceiros_nome != $parceiros_nome_exibir) { echo 'title="'.utf8_encode($parceiros_nome).'"'; } ?>>
							<? echo utf8_encode($parceiros_nome_exibir); ?>
						</td>
						<td><? echo $parceiros_email; ?></td>
						<td><? echo $parceiros_cpf; ?></td>
						<td><? echo $parceiros_cidade."/".$parceiros_estado; ?></td>
						<td class="ctrl">
							<a href="<? echo SITE; ?>parceiros/editar/<? echo $parceiros_cod; ?>/" class="ver"></a>
							<a href="<? echo SITE; ?>e-parceiro-gerenciar.php?c=<? echo $parceiros_cod; ?>&a=deletar" class="excluir confirm" title="Tem certeza que deseja excluir esse parceiro?"></a>
						</td>
					</tr>
					<?
					$i++;
				}
			} else {
			?>
				<tr>
					<td colspan="6" class="nenhum">Nenhum parceiro encontrado.</td>
				</tr>
			<?
			}
			?>
			</tbody>
		</table>
		<? if ($n_parceiros > 0 && $total_paginas > 0) { ?>
        <div class="pager-tablesorter">
	        <a href="<? echo SITE; ?>parceiros/<? if(!empty($q)) echo '?q='.urlencode(utf8_encode($q)); ?>" class="first"></a>
	        <a href="<? echo SITE; ?>parceiros/?<? if(!empty($q)) echo 'q='.urlencode(utf8_encode($q)).'&'; ?>p=<? echo ($p > 1) ? ($p - 1) : 1; ?>" class="prev"></a>
	        <span class="pagedisplay"><? echo $p; ?>/<? echo $total_paginas; ?></span>
	        <a href="<? echo SITE; ?>parceiros/?<? if(!empty($q)) echo 'q='.urlencode(utf8_encode($q)).'&'; ?>p=<? echo ($p < $total_paginas) ? ($p + 1) : $total_paginas; ?>" class="next"></a>
	        <a href="<? echo SITE; ?>parceiros/?<? if(!empty($q)) echo 'q='.urlencode(utf8_encode($q)).'&'; ?>p=<? echo $total_paginas; ?>" class="last"></a>
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