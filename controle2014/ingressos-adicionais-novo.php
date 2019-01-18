<?

//Incluir funções básicas
include("include/includes.php");

//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");
include("include/header.php");

//-----------------------------------------------------------------//

//Pagina atual
define('PGATUAL', 'ingressos/adicionais/novo/');

$tipo_ingresso = format($_GET['t']);
$evento = (int) $_SESSION['usuario-carnaval'];

?>
<section id="conteudo">
	<form id="ingresso-adicionais-novo" method="post" action="<? echo SITE; ?>ingressos/adicionais/novo/post/">
		<header class="titulo">
			<h1>Ingresso Adicionais <span>Novo</span></h1>
		</header>
		
		<section class="secao">
			<p>
				<label for="adicional-nome">Nome</label>
				<input type="text" name="nome" class="input full" id="adicional-nome" />
			</p>

		</section>


		<footer class="controle">
			<input type="submit" class="submit coluna" value="Inserir" />
			<a href="#" class="cancel coluna">Cancelar</a>
			<div class="clear"></div>
		</footer>

	</form>

</section>
<?

//-----------------------------------------------------------------//

include('include/footer.php');

//Fechar conexoes
include("conn/close.php");

?>