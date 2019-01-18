<?php 
	
session_start();

//Include cielo
require "../includes/include.php";

// $evento = (int) $_POST['evento'];

if(!checklogado()){
?>
<script type="text/javascript">
	location.href='<? echo SITE; ?>';
</script>
<?
	exit();
}

//------------------------------------------------------------------------//

// Resgata último pedido feito da SESSION
$cod = (int) $_GET['c'];

//-----------------------------------------------------------------------------//

if(!empty($cod)) {


	$cliente = $_SESSION['us-cod'];

	$sql_compra = sqlsrv_query($conexao, "SELECT TOP 1 * FROM loja WHERE LO_COD=$cod", $conexao_params, $conexao_options);
	if(sqlsrv_num_rows($sql_compra) > 0) {
		$co = sqlsrv_fetch_array($sql_compra);
		
		$co_xml = $co['LO_XML'];
		$co_cliente = $co['LO_CLIENTE'];
		$co_parceiro = $co['LO_PARCEIRO'];
		$co_valor = $co['LO_VALOR_TOTAL'];
		
		$Pedido = new Pedido();
		$Pedido->FromString($co_xml);

		$PercentualCaptura = $Pedido->dadosPedidoValor;
		$objResposta = $Pedido->RequisicaoCaptura($PercentualCaptura, null);

		
		$Pedido->status = $objResposta->status;

		$StrPedido = $Pedido->ToString();
		$co_status = $Pedido->status;

		//$_SESSION["pedidos"]->offsetSet($ultimoPedido, $StrPedido);
		$sql_up = sqlsrv_query($conexao, "UPDATE TOP (1) loja SET LO_XML='$StrPedido', LO_STATUS_TRANSACAO='$co_status' WHERE LO_COD=$cod", $conexao_params, $conexao_options);

		//------------------------------------------------------------------------//

		if($co_status == '6') {
			$sql_cp = sqlsrv_query($conexao, "UPDATE TOP (1) loja SET LO_PAGO=1, LO_DATA_PAGAMENTO=GETDATE() WHERE LO_COD=$cod", $conexao_params, $conexao_options);

			// RECDESP		- Receita Despesa
			// CODEMP		- Empresa
			// DTNEG		- Data Negociação
			// NUMNOTA		- Número da Nota
			// VLRDESDOB	- Valor Desdobramento
			// DTVENC		- Data do Vencimento
			// CODPARC		- Parceiro
			// CODBCO		- Banco
			// CODTIPTIT	- Tipo de Título
			// CODNAT		- Natureza

			// //Inserir no financeiro da Sankhya
			// $sql_fin = sqlsrv_query($conexao_sankhya, "INSERT INTO TGFFIN (NUFIN, RECDESP, CODEMP, NUMNOTA, DTNEG, DTALTER, DHMOV, CODNAT, VLRDESDOB, CODPARC) VALUES ((SELECT ISNULL(MAX(NUFIN),0) + 1 FROM TGFFIN),'1', 1, 0, GETDATE(), GETDATE(), GETDATE(), '110101', '$co_valor', '$co_cliente')", $conexao_params, $conexao_options);

			//Buscar informações do cliente
			$sql_cliente = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 NOMEPARC, EMAIL, TELEFONE FROM TGFPAR WHERE CODPARC='$co_cliente'", $conexao_params, $conexao_options);
			if(sqlsrv_num_rows($sql_cliente) > 0) {

				$cliente = sqlsrv_fetch_array($sql_cliente);
				$nome = $cliente['NOMEPARC'];
				$email = $cliente['EMAIL'];
				$telefone = $cliente['TELEFONE'];

				//-----------------------------------------------------------------//

				//Envio de SMS
				$sms = "O pagamento do pedido n. $cod foi confirmado. Central de Atendimento Folia Tropical: 21 3202 6000";

				#require("../../include/directcall-envio-sms.php");
				#directcall($telefone, $sms);

				//-----------------------------------------------------------------//

				include("../../include/class.phpmailer.php");

				$msg = "<body>
						<table width='350' border='0' align='center' cellpadding='0' cellspacing='0'>
						  <tr>
							<td height='150' align='center' valign='top'><img src='".SITE."img/logo-email.png' width='200'height='150'></td>
						  </tr>
						  <tr>
							<td align='left' valign='top'>&nbsp;</td>
						  </tr>
						  <tr>
							<td align='left' valign='top'><font face='Arial, Helvetica, sans-serif' color='#666666' size='2'>Seu pedido de Nº $cod teve o pagamento confirmado!</font></td>
						  </tr>
						  <tr>
							<td align='left' valign='top'>&nbsp;</td>
						  </tr>
						  <tr>
							<td align='left' valign='top'><font face='Arial, Helvetica, sans-serif' color='#666666' size='2'>
							- Para download do voucher:<br />
							1) Fazer o login na conta: <a href='https://ingressos.foliatropical.com.br/compras/#login' target='_blank'>ingressos.foliatropical.com.br/compras/#login</a><br />
							2) Ao passar o mouse sobre o seu nome no menu, será exibida a opção &ldquo;Minhas Compras&rdquo;.<br />
							3) Os vouchers pagos estarão com o botão de imprimir habilitado na forma de uma impressora. <br /><br />

							Os seguintes documentos devem ser apresentados, na retirada dos ingressos, pelo titular do cadastro no site:<br />
							- Documento oficial com foto do titular do cadastro no site;<br />
							- Cartão de crédito utilizado na compra;<br /><br /><br />

							Central de atendimento: (21) 3202-6000<br /><br />

							central@foliatropical.com.br
							</font></td>
						  </tr>
						  <tr>
							<td align='left' valign='top'>&nbsp;</td>
						  </tr>
						  <tr>
							<td align='center' height='30'><a href='https://ingressos.foliatropical.com.br/compras/' target='_blank' style='text-decoration: none; color: #999;'><font face='Arial, Helvetica, sans-serif' color='#999' size='1'><strong>ingressos.foliatropical.com.br</strong></font></a></td>
						  </tr>
						</table>
						</body>";

				
				//-----------------------------------------------------------//

				$mail = new PHPMailer();
				$mail->IsSMTP();        //ENVIAR VIA SMTP
				$mail->SMTPAuth = true; //ATIVA O SMTP AUTENTICADO
				$mail->IsHTML(true);        //ATIVA MENSAGEM NO FORMATO TXT, SE true ATIVA NO FORMATO HTML
				
				$mail->Host     = "smtp.gmail.com";     //SERVIDOR DE SMTP, USE mail.SeuDominio.com OU smtp.dominio.com.br	
				$mail->Username = "sistema@grupopacifica.com.br"; //EMAIL PARA SMTP AUTENTICADO (pode ser qualquer conta de email do seu domínio)
    			$mail->Password = "dayse2015";
				$mail->SetFrom("central@grupopacifica.com.br",utf8_decode("Grupo Pacífica"));    //E-MAIL DO REMETENTE, NOME DO REMETENTE
				
				$mail->SMTPSecure = "tls";
				$mail->Host       = "smtp.gmail.com";
				$mail->Port       = 587;	
				
				$mail->AddAddress($email, $nome); //E-MAIL DO DESINATÁRIO, NOME DO DESINATÁRIO
				#$mail->AddBCC('thalles@fococomunicacao.com', ''); //E-MAIL DO DESINATÁRIO, NOME DO DESINATÁRIO
				// $mail->AddReplyTo($email,utf8_decode($nome)); //CONFIGURA O E-MAIL QUE RECEBERÁ A RESPOSTA DESTA MENSAGEM
				
				$mail->Subject = utf8_decode("Confirmação de pagamento do pedido $cod");  //ASSUNTO DA MENSAGEM
				$mail->Body    = utf8_decode($msg); //CONTEÚDO DA MENSAGEM
				
				$mail->Send();


				// Log de liberacao do pagamento
				$sql_exist =  sqlsrv_query($conexao, "SELECT LG_COD FROM log WHERE LG_VOUCHER = '$cod' AND LG_ACAO=N'Pagamento liberado' AND D_E_L_E_T_='0'", $conexao_params, $conexao_options);
				if(sqlsrv_num_rows($sql_exist) == 0) $sql_log = sqlsrv_query($conexao, "INSERT INTO log (LG_VOUCHER, LG_USUARIO, LG_NOME, LG_ACAO, LG_DATA) VALUES ('$cod', '".$_SESSION['us-cod']."', '".$_SESSION['us-nome']."', 'Pagamento liberado', GETDATE())", $conexao_params, $conexao_options);

			}
		}


?>
<html>
	<head>
		<title>Pagamento</title>		
	</head>
	<body>

	<script type="text/javascript">
		alert('Pagamento capturado com sucesso e confirmado!');
		window.location.href="<? echo SITE; ?>financeiro/";
	</script>
	</body>
</html>
<?
		exit();

	}

}
?>
<script type="text/javascript">
	alert('Ocorreu um erro, por favor tente novamente');
	location.href='<? echo SITE; ?>';
</script>