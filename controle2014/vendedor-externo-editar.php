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

$cod = (int) $_GET['c'];
$sql_vendedor_externo = sqlsrv_query($conexao, "SELECT TOP 1 * FROM vendedor_externo WHERE VE_COD='$cod'", $conexao_params, $conexao_options);

?>
<section id="conteudo">
	<header class="titulo">
		<h1>Editar <span>Vendedor Externo</span></h1>		
	</header>
	<?
	if(sqlsrv_num_rows($sql_vendedor_externo) > 0) {

		$vendedor_externo = sqlsrv_fetch_array($sql_vendedor_externo);
		$vendedor_externo_cod = $vendedor_externo['VE_COD'];
		$vendedor_externo_parceiro = $vendedor_externo['VE_PARCEIRO'];
		$vendedor_externo_nome = utf8_encode($vendedor_externo['VE_NOME']);
		$vendedor_externo_email = utf8_encode($vendedor_externo['VE_EMAIL']);
		$vendedor_externo_telefone = utf8_encode($vendedor_externo['VE_TEL']);
		
	?>
	<form id="cadastro-vendedor-externo" class="cadastro" method="post" action="<? echo SITE; ?>vendedor-externo/cadastro/post/">

		<input type="hidden" name="editar" value="true">
		<input type="hidden" name="cod" value="<? echo $cod; ?>">

		<section class="secao">
			
			<section id="vendedor-externo-parceiro" class="selectbox">
				<h3>Parceiro:</h3>
				<a href="#" class="arrow"><strong>Selecione o parceiro</strong><span></span></a>
				<ul class="drop">
					<?

					// $sql_parceiros = sqlsrv_query($conexao, "SELECT PA_COD, PA_NOME FROM parceiros WHERE PA_BLOCK=0 AND D_E_L_E_T_=0 ORDER BY PA_NOME ASC", $conexao_params, $conexao_options);
					$sql_parceiros = sqlsrv_query($conexao_sankhya, "SELECT CODPARC, NOMEPARC, CGC_CPF, EMAIL, AD_COMISSAO FROM TGFPAR WHERE VENDEDOR='S' AND BLOQUEAR='N' $search ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
					if(sqlsrv_num_rows($sql_parceiros)){

						while ($ar_parceiros = sqlsrv_fetch_array($sql_parceiros)) {
							
							$parceiros_cod = $ar_parceiros['CODPARC'];
							$parceiros_nome = utf8_encode(trim($ar_parceiros['NOMEPARC']));
							$parceiros_comissao = trim($ar_parceiros['AD_COMISSAO']);
							
						?>
						<li><label class="item"><input type="radio" name="parceiro" value="<? echo $parceiros_cod; ?>" alt="<? echo $parceiros_nome; ?>" rel="<? echo $parceiros_comissao; ?>" /><? echo $parceiros_nome; ?></label></li>
						<?

						}
					}

					?>
				</ul>
				<div class="clear"></div>
			</section>

			<p>
				<label for="vendedor-externo-nome">Nome:</label>
				<input type="text" name="nome" class="input" id="vendedor-externo-nome" value="<? echo $vendedor_externo_nome; ?>" />
			</p>
			
			<p>
				<label for="vendedor-externo-email">Email:</label>
				<input type="text" name="email" class="input" id="vendedor-externo-email" value="<? echo $vendedor_externo_email ?>" />
			</p>
			<p>
				<label for="vendedor-externo-telefone">Telefone:</label>
				<input type="text" name="telefone" class="input pequeno" id="vendedor-externo-telefone" value="<? echo $vendedor_externo_telefone; ?>" />
			</p>
			
			<div class="clear"></div>
		</section>
		
		<footer class="controle">
			<input type="submit" class="submit coluna" value="Inserir" />
			<a href="#" class="cancel coluna">Cancelar</a>
			<div class="clear"></div>
		</footer>
	</form>

	<script type="text/javascript">
	$(document).ready(function(){
		$("form#cadastro-vendedor-externo").find("input[name='parceiro']").radioSel('<? echo $vendedor_externo_parceiro; ?>');
	});
	</script>
	<? } ?>
</section>
<?

//-----------------------------------------------------------------//

include('include/footer.php');

//Fechar conexoes
include("conn/close.php");
include("conn/close-sankhya.php");

?>