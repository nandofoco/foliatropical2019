<?

define("NOCHECK","true");

//-----------------------------------------------------------------------------//

//Incluir funções básicas
include("include/includes.php");


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