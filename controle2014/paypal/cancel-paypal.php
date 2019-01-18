<?

define('PGINCLUDE', 'true');
define('NOCHECK', 'true');

//-----------------------------------------------------------------//

header('Content-Type: text/html; charset=utf-8');

//Banco de dados
include("../conn/conn.php");

//Incluir arquivos de layout
include("../include/head.php");

?>
<section id="resposta">
	<a href="<? echo str_replace('controle2014/', '', SITE); ?>" id="logo"></a>
	<div class="wrapper">
        <header>
			<h2>Pedido cancelado</h2>
		</header>
    </div>
    <!-- <a href="#" class="voltar button">Voltar</a> -->
    
</section>
</body>
</html>