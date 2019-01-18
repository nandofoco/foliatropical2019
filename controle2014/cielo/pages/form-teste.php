<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title></title>
	<link rel="stylesheet" href="">
</head>
<body>
	<form action="e-compra-cartao-teste.php" method="post">
		Código Compra
		<input type="text" name="codigoCompra"></br>
		Bandeira
		<input autocomplete="off" type="text" name="codigoBandeira" id="bandeira" value="amex"></br>
		Número Cartão
		<input autocomplete="off" type="text" id="numero_cartao" name="cartaoNumero" value></br>
		Mês Validade
		<input autocomplete="off" type="text" id="mes_validade" name="mesValidade" maxlength class="input" value></br>
		Ano Validade
		<input autocomplete="off" type="text" id="ano_validade" name="anoValidade" maxlength></br>
		Código de Segurança
		<input autocomplete="off" type="text" id="codigo_seguranca" name="cartaoCodigoSeguranca"></br>
		Nome
		<input autocomplete="off" type="text" id="nome_titular" name="nomeTitular" class="input"></br>
		CPF/CNPJ
		<input autocomplete="off" type="text" id="cpfcnpj" name="cpfcnpj" class="input" value></br>
		Forma Pagamento
		<input type="text" name="formaPagamento" value="1" alt="1"></br>
		<input type="submit" value="Enviar">
	</form>
</body>
</html>