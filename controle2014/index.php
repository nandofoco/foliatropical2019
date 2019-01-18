<?

define("NOCHECK","true");

//-----------------------------------------------------------------------------//

//Incluir funções básicas
include("include/includes.php");

//Redirecionar para a pagina de mensagens se estiver logado
if(checklogado()) {


	//$link = is_numeric($_SESSION['us-parceiro']) || ($_SESSION['us-grupo'] == 'VIN') ? 'compras/novo/' : 'relatorios/';
	switch (true) {
		case (is_numeric($_SESSION['us-parceiro']) || ($_SESSION['us-grupo'] == 'VIN')):
			$link = 'compras/novo/';
		break;

		case ($_SESSION['us-grupo'] == 'ATE'):
			$link = 'ingressos/expedicao/';
		break;
		
		default:
			$link = 'relatorios/';
		break;
	}
	
    ?>
    <script type="text/javascript">
    	location.href='<? echo SITE.$link; ?>';
    </script>
    <?
    exit();
}

//-----------------------------------------------------------------------------//


//Incluir arquivos de layout
include("include/head.php");

?>
<header id="topo" class="index"></header>

<section id="page-login">

	<form name="login" class="infield" id="form-login" method="post" action="<? echo SITE; ?>acessa/">

		<section class="wrapper">
			<h1>Entrar</h1>

		    <p>
		    	<label for="login" class="infield">Login</label>
		    	<input type="text" name="login" id="login" class="input" /></p>
	        <p>
	        	<label for="senha" class="infield">Senha</label>
	        	<input type="password" name="senha" id="senha" class="input" />
	        	<input type="submit" value="Ok" class="submit" />
	        </p>
        </section>
        
        <? if ($_GET['erro']) { ?><span class="erro">Dados Incorretos</span><? } ?>
    </form>
</section>
<?

//-----------------------------------------------------------------------------//

include('include/footer.php');

//Fechar conexoes
include("conn/close.php");


?>