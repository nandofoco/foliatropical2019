<?

define('PGBLOQUEIO', 'true');

//Incluir funções básicas
include("include/includes.php");

/*if ($SERVER_NAME != "bruno"){

	?>
	<script type="text/javascript">
	location.href='<? echo SITE; ?>carnaval/lista/';
	</script>
	<?
	exit();
}*/


//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");
include("include/header.php");

//-----------------------------------------------------------------//

?>
<section id="conteudo">
	<section class="bloqueado">
		<h1>Acesso não permitido</h1>
	</section>
</section>
<?


//-----------------------------------------------------------------//

include('include/footer.php');

//Fechar conexoes
include("conn/close.php");

?>