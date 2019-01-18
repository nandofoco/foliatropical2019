<?

define('PGINCLUDE', 'true');

//Verificamos o dominio
include("../include/checkwww.php");

//Banco de dados
include("../conn/conn.php");
include("../conn/conn-sankhya.php");

//Incluir funções básicas
include("../include/funcoes.php");

//Incluir função para url amigável
include("../include/toascii.php");

//-----------------------------------------------------------------------------//

$order_number = (int) $_POST['order_number'];
$checkout_cielo_order_number = format($_POST['checkout_cielo_order_number']);
$customer_name = format($_POST['customer_name']);
$customer_identity = format($_POST['customer_identity']);
$customer_email = format($_POST['customer_email']);
$customer_phone = format($_POST['customer_phone']);
$payment_method_type = format($_POST['payment_method_type']);
$payment_method_brand = format($_POST['payment_method_brand']);
$payment_maskedcreditcard = format($_POST['payment_maskedcreditcard']);
$payment_installments = format($_POST['payment_installments']);
$payment_antifraudresult = format($_POST['payment_antifraudresult']);
$payment_status = format($_POST['payment_status']);
$tid = format($_POST['tid']);


if(($order_number > 4800) && (date('Y') < 2016)) {
	$cod = $order_number;
	$tabela = 'foliatropical2014';
} else {
	$sql_exist_cod = sqlsrv_query($conexao, "SELECT * FROM loja_modalidade WHERE LM_COD='$order_number' AND D_E_L_E_T_=0", $conexao_params, $conexao_options);
	if(sqlsrv_num_rows($sql_exist_cod) > 0) {
		$exist_cod = sqlsrv_fetch_array($sql_exist_cod);
		
		$cod = $exist_cod['LM_COMPRA'];
		$modalidade = $exist_cod['LM_MODALIDADE'];

		switch ($modalidade) {
			case 'carnaval': $tabela = 'foliatropical2014'; break;
			case 'rockinrio': $tabela = 'rockinrio'; break;
		}
	}
}

if(!empty($cod) && !empty($tabela)) {

	switch ($payment_status) {		
		case 1: $status = '0'; break; // Pendente (Para todos os meios de pagamento)
		case 2: $status = '6'; break; // Pago (Para todos os meios de pagamento)
		case 3: $status = '3'; break; // Negado (Somente para Cartão Crédito)
		case 5: $status = '9'; break; // Cancelado (Para cartões de crédito)
		case 6: $status = '1'; break; // Não Finalizado (Todos os meios de pagamento)
		case 7: $status = '4'; break; // Autorizado (somente para Cartão de Crédito)
	}

	if($status == 6) $update_pagamento =  " LO_PAGO=1, LO_DATA_PAGAMENTO=GETDATE(), ";
	if($status == 4) $update_pagamento =  " LO_PAGO=0, LO_DATA_PAGAMENTO=GETDATE(), ";

	switch ($payment_method_brand) {
		case 1: $cartao = 'visa'; break; //Visa
		case 2: $cartao = 'mastercard'; break; //Mastercad
		case 3: $cartao = 'amex'; break; //AmericanExpress
		case 4: $cartao = 'diners'; break; //Diners
		case 5: $cartao = 'elo'; break; //Elo
		case 6: $cartao = 'aura'; break; //Aura
		case 7: $cartao = 'jcb'; break; //JCB
	}

	$sql_itens = sqlsrv_query($conexao, "UPDATE [$tabela].[dbo].[loja] SET
		$update_pagamento
		LO_CARTAO_CHECKOUTID='$checkout_cielo_order_number', 
		LO_PARCELAS='$payment_installments',
		LO_STATUS_TRANSACAO='$status',
		LO_CARTAO_BANDEIRA='$payment_maskedcreditcard',
		LO_CARTAO_NOME='$customer_name',
		LO_CARTAO_CPF='$customer_identity',
		LO_CARTAO_EMAIL='$customer_email',
		LO_CARTAO_TELEFONE='$customer_phone',
		LO_CARTAO_ANTIFRAUDE='$payment_antifraudresult',
		LO_TID='$tid',
		LO_CARTAO='$cartao',
		LO_CARTAO_V2=1
	WHERE LO_COD='$cod'", $conexao_params, $conexao_options);


	// Email
	/*switch($status) {
		case 3: case 5: case 8: $texto_status = utf8_decode("Pagamento não Aprovado"); break;		
		case 6: $texto_status = "Pagamento Aprovado"; break;		
		case 9: $texto_status = "Pagamento Negado"; break;
	}*/

	if($status == 6) {

		//Banco de dados
		include("../conn/conn-sankhya.php");

		//Buscar informações do cliente
		$sql_co = sqlsrv_query($conexao, "SELECT TOP (1) LO_CLIENTE FROM [$tabela].[dbo].[loja] WHERE LO_COD=$cod", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_co) > 0) {
			$co = sqlsrv_fetch_array($sql_co);
			$co_cliente = $co['LO_CLIENTE'];
		}
		

		$sql_cliente = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 NOMEPARC, EMAIL, TELEFONE FROM TGFPAR WHERE CODPARC='$co_cliente'", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_cliente) > 0) {

			$cliente = sqlsrv_fetch_array($sql_cliente);
			$nome = $cliente['NOMEPARC'];
			$email = $cliente['EMAIL'];
			$telefone = $cliente['TELEFONE'];

			//-----------------------------------------------------------------//

			//Envio de SMS
			$sms = "O pagamento do pedido n. $cod foi confirmado. Central de Atendimento Folia Tropical: 21 3202 6000";

			require("../../include/directcall-envio-sms.php");
			directcall($telefone, $sms);

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
						<td align='left' valign='top'><font face='Arial, Helvetica, sans-serif' color='#666666' size='2'>Prezado (a),</font></td>
					  </tr>
					  <tr>
						<td align='left' valign='top'>&nbsp;</td>
					  </tr>
					  <tr>
						<td align='left' valign='top'><font face='Arial, Helvetica, sans-serif' color='#666666' size='2'>
						Seu pagamento (pedido nº.$cod) foi confirmado, agora é só cair na Folia!<br /><br />
						Clique <a href='http://www.foliatropical.com.br/minhas-compras/imprimir/$cod/'>aqui</a> para imprimir o seu voucher ou acesse o menu Minhas Compras no www.foliatropical.com.br<br /><br />
						Central de Atendimento: 21 3202 6000</font></td>
					  </tr>
					  <tr>
						<td align='left' valign='top'>&nbsp;</td>
					  </tr>
					  <tr>
						<td align='center' height='30'><a href='http://www.foliatropical.com.br' target='_blank' style='text-decoration: none; color: #999;'><font face='Arial, Helvetica, sans-serif' color='#999' size='1'><strong>www.foliatropical.com.br</strong></font></a></td>
					  </tr>
					</table>
					</body>";

			
			//-----------------------------------------------------------//

			$remetente_nome = utf8_decode("Grupo Pacífica");
			$remetente_email = 'central@grupopacifica.com.br';
			$destinatario_nome = utf8_decode($nome);
			$destinatario_email = $email;
			$assunto = utf8_decode("Confirmação de pagamento do pedido $cod");
			$mensagem = utf8_decode($msg);

			enviarEmail($remetente_nome, $remetente_email, $destinatario_nome, $destinatario_email, $assunto, $mensagem);

			include("../conn/close-sankhya.php");

		}
	}

}

//-----------------------------------------------------------------------------//

echo '<status>OK</status>';

?>