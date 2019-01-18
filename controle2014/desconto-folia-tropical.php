<?

//Incluir funções básicas
include("include/includes.php");

//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");
include("include/header.php");

//-----------------------------------------------------------------//
if (!empty($_POST['desconto'])) 
{
	$desconto = $_POST['desconto'];
	$sql_desconto = sqlsrv_query($conexao, "UPDATE desconto_folia_tropical SET DES_VALOR=$desconto", $conexao_params, $conexao_options);

	//echo "<script>alert('Alterado com sucesso!');</script>";
	//header(SITE.'desconto');
}

$sql_desconto = sqlsrv_query($conexao, "SELECT DES_VALOR FROM desconto_folia_tropical", $conexao_params, $conexao_options);
		
$ar_desconto = sqlsrv_fetch_array($sql_desconto);




?>
<section id="conteudo">
	<header class="titulo">
		<h1>Desconto <span>Folia Tropical</span></h1>
	</header>
	<section class="padding" style="margin-left: 10px;">
		<form id="desconto-novo" method="post" action="">
			<br>
			<h3>Desconto aplicado na compra de dois ingressos diferentes em dias diferentes.</h3>
			<br>
			<br>
			<p class="coluna-desconto" style="font-size: 15px;">
				<label for="desconto">Adicione o valor:</label>
				<input type="text" name="desconto" class="input" style="width: 2%" value="<?=$ar_desconto['DES_VALOR']?>" />%
				<input type="submit" class="submit" value="+" style="width: 4%; margin-left: 15px;"/>
			</p>			
		</form>		
	</section>
</section>
<?

//-----------------------------------------------------------------//

include('include/footer.php');

//Fechar conexoes
include("conn/close.php");

?>