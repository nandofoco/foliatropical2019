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
$p = (int) $_GET['p'];
if(!($p > 0)) $p = 1;


$q = format($_GET['q']);


$search = is_numeric($q) ? " AND CODPARC='$q' " : " AND (NOMEPARC LIKE '%$q%' OR EMAIL LIKE '%$q%' OR CGC_CPF LIKE '%$q%') ";

$sql_clientes = sqlsrv_query($conexao_sankhya, "SELECT CODPARC, NOMEPARC, CGC_CPF, EMAIL, CONVERT(CHAR, DTNASC, 103) AS dtnasc, BLOQUEAR FROM TGFPAR WHERE CLIENTE='S' $search ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);

$n_clientes = sqlsrv_num_rows($sql_clientes);


if(!$q)
{
	$sql_clientes = sqlsrv_query($conexao_sankhya, "


	DECLARE @PageNumber INT;
	DECLARE @PageSize INT;
	DECLARE @TotalPages INT;

	SET @PageSize = 20;
	SET @PageNumber = $p;

	IF @PageNumber = 0 BEGIN
	SET @PageNumber = 1
	END;

	SET @TotalPages = CEILING(CONVERT(NUMERIC(20,10), ISNULL((SELECT COUNT(*) FROM TGFPAR), 0)) / @PageSize);

	WITH parceiro(NumeroLinha, CODPARC, NOMEPARC, CGC_CPF, EMAIL, DTNASC, BLOQUEAR,CLIENTE)
	AS (
	SELECT ROW_NUMBER() OVER (ORDER BY NOMEPARC) AS NumeroLinha,
	CODPARC,
	NOMEPARC,
	CGC_CPF,
	EMAIL, 
	CONVERT(CHAR, DTNASC, 103) AS dtnasc, 
	BLOQUEAR,
	CLIENTE	
	FROM TGFPAR)

	SELECT @TotalPages AS TOTAL, @PageNumber AS PAGINA, NumeroLinha, CODPARC, NOMEPARC, CGC_CPF, EMAIL, CONVERT(CHAR, DTNASC, 103) AS dtnasc, BLOQUEAR,CLIENTE 
	FROM parceiro
	WHERE  NumeroLinha BETWEEN ( ( ( @PageNumber - 1 ) * @PageSize ) + 1 ) AND ( @PageNumber * @PageSize ) 
	AND CLIENTE='S' ORDER BY NOMEPARC  

	", 	$conexao_params, $conexao_options);
}





?>
<section id="conteudo">
	<header class="titulo">
		<h1>Clientes <span>Listagem</span></h1>
		<form id="busca-lista" class="busca-lista" method="get" action="<? echo SITE; ?>clientes/">
			<a href="<? echo SITE; ?>clientes/cadastro/" class="adicionar">+</a>
			<p class="coluna">
				<label for="busca-lista-input" class="infield">Pesquisar</label>
				<? if(!empty($q)){ ?><a href="<? echo SITE; ?>clientes/" class="limpar-busca">&times;</a><? } ?>
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
					<th><strong>Data de Nascimento</strong><span></span></th>
					<th><strong>CPF/CNPJ</strong> <span></span></th>
					<th>&nbsp;</th>
					<th>&nbsp;</th>
				</tr>
				<tr class="spacer"><td colspan="6">&nbsp;</td></tr>
			</thead>
			<tbody>
			<?
			
			if($n_clientes > 0)	 {

				$i=1;
				while($clientes = sqlsrv_fetch_array($sql_clientes)) {

					$total_paginas = $clientes['TOTAL'];

					$clientes_cod = trim($clientes['CODPARC']);
					$clientes_nome = trim($clientes['NOMEPARC']);
					$clientes_email = trim($clientes['EMAIL']);
					$clientes_dtnasc = trim($clientes['dtnasc']);
					$clientes_cpf = formatCPFCNPJ(trim($clientes['CGC_CPF']));
					$clientes_block = (bool) ($clientes['BLOQUEAR'] == 'S');
					$acao = ($clientes_block) ? 'desbloquear' : 'bloquear';

					$clientes_nome_exibir = (strlen($clientes_nome) > 20) ? substr($clientes_nome, 0, 20)."..." : $clientes_nome;
					$clientes_email_exibir = (strlen($clientes_email) > 20) ? substr($clientes_email, 0, 20)."..." : $clientes_email;

					?>
					<tr <? if($clientes_block) { echo 'class="block"'; } ?>>
						<td class="block"><a href="<? echo SITE; ?>e-cliente-gerenciar.php?c=<? echo $clientes_cod; ?>&a=<? echo $acao; ?>" class="block confirm" title="Tem certeza que deseja <? echo $acao; ?> esse cliente?"></a></td>
						<td <? if($clientes_nome != $clientes_nome_exibir) { echo 'title="'.utf8_encode($clientes_nome).'"'; } ?>>
							<? echo utf8_encode($clientes_nome_exibir); ?>
						</td>
						<td <? if($clientes_email != $clientes_email_exibir) { echo 'title="'.utf8_encode($clientes_email).'"'; } ?>>
							<? echo utf8_encode($clientes_email_exibir); ?>
						</td>
						<td><? echo $clientes_dtnasc; ?></td>
						<td><? echo $clientes_cpf; ?></td>
						<td class="log">
							<a href="<? echo SITE; ?>relatorios/exportar/logs/?cliente=<? echo $clientes_cod; ?>" class="liberar" target="_blank">Log</a>
						</td>
						<td class="ctrl">
							<a href="<? echo SITE; ?>clientes/editar/<? echo $clientes_cod; ?>/" class="ver"></a>
							<a href="<? echo SITE; ?>e-cliente-gerenciar.php?c=<? echo $clientes_cod; ?>&a=deletar" class="excluir confirm" title="Tem certeza que deseja excluir esse cliente?"></a>
						</td>
					</tr>
					<?
					$i++;

					$exibe_loja =  true;
				}
			} else {
			?>
			<
				<tr>
					<td colspan="7" class="nenhum">Nenhum cliente encontrado.</td>
				</tr>
			<?
			}
			?>
			</tbody>
		</table>

		<? if ($n_clientes > 0 && $total_paginas > 0) { ?>
        <div class="pager-tablesorter">
	        <a href="<? echo SITE; ?>clientes/<? if(!empty($q)) echo '?q='.urlencode(utf8_encode($q)); ?>" class="first"></a>
	        <a href="<? echo SITE; ?>clientes/?<? if(!empty($q)) echo 'q='.urlencode(utf8_encode($q)).'&'; ?>p=<? echo ($p > 1) ? ($p - 1) : 1; ?>" class="prev"></a>
	        <span class="pagedisplay"><? echo $p; ?>/<? echo $total_paginas; ?></span>
	        <a href="<? echo SITE; ?>clientes/?<? if(!empty($q)) echo 'q='.urlencode(utf8_encode($q)).'&'; ?>p=<? echo ($p < $total_paginas) ? ($p + 1) : $total_paginas; ?>" class="next"></a>
	        <a href="<? echo SITE; ?>clientes/?<? if(!empty($q)) echo 'q='.urlencode(utf8_encode($q)).'&'; ?>p=<? echo $total_paginas; ?>" class="last"></a>
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