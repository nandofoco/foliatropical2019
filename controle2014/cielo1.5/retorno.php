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
			<h2>Pedido recebido</h2>
			<p>Recebemos seu pedido, em breve confirmaremos sua compra.<br />Após a confirmação você deve imprimir o seu <strong>Voucher</strong> acessando o menu <strong>Minhas Compras</strong>.</p>
		</header>
    </div>
    <!-- <a href="#" class="voltar button">Voltar</a> -->
    
</section>
</body>
</html>