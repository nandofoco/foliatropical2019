<?php

session_start();
require "../includes/include.php";

$Pedido = new Pedido();
$Pedido->FromString('<?xml version="1.0" encoding="ISO-8859-1" ?><objeto-pedido><tid>10605381376CM0O4R8MB</tid><status></status><dados-ec>
      <numero>1060538137</numero>
      <chave>ada401374b980f0cc7f0da53e6b6491d0ae9c114cdb9421560e804574890c8f9</chave>
   </dados-ec><dados-pedido>
      <numero>17987</numero>
      <valor>138000</valor>
      <moeda>986</moeda>
      <data-hora>2017-02-20T17:26:43</data-hora>
      <idioma>PT</idioma>
   </dados-pedido><forma-pagamento>
      <bandeira></bandeira>
      <produto>2</produto>
      <parcelas>6</parcelas>
   </forma-pagamento></objeto-pedido>');

// Consulta situação da transação
$objResposta = $Pedido->RequisicaoConsulta();

//arquivos de layout
// phpinfo();
?>
<html>
	<head>
		<title>Loja Exemplo : Fechamento pedido</title>
	</head>
	<body>
	<center>
		<h3>Fechamento (<?php echo date("D M d H:i:s T Y")?>)</h3>
		<table border="1">
			<tr>
				<th>Número pedido</th>
				<th>Finalizado com sucesso?</th>
				<th>Transação</th>
				<th>Status transação</th>
			</tr>
			<tr>
				<td><?php echo $Pedido->dadosPedidoNumero; ?></td>
				<td><?php echo $finalizacao ? "sim" : "não"; ?></td>
				<td><?php echo $Pedido->tid; ?></td>
				<td style="color: red;"><?php echo $Pedido->getStatus(); ?></td>
			</tr>			
		</table>				
		<h3>XML</h3>
		<textarea name="xmlRetorno" cols="80" rows="25" readonly="readonly">
			<?php echo $objResposta->asXML(); ?>
		</textarea>
	</center>
	</body>
</html>