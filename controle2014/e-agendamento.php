<?

//Verificamos o dominio
include("include/includes.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");

//-----------------------------------------------------------------------------//

$cod = (int) $_POST['cod'];
$loja = (int) $_POST['loja'];
$multiplo = (bool) $_POST['multiplo'];
$item_nome = format($_POST['item-nome']);
$item_horario = format($_POST['item-horario']);

//campos para editar
$editar = $_POST['editar'];
$agendamento = $_POST['agendamento'];

$resposta = 'Ocorreu um erro, tente novamente!';

//-----------------------------------------------------------------------------//

if(!empty($cod) && !empty($item_horario)) {

	if(!$editar) {
	
		$sql_insert = sqlsrv_query($conexao, "INSERT INTO transportes_agendamento (TA_HORARIO, TA_ITEM) VALUES ('$item_horario', '$cod')", $conexao_params, $conexao_options);
		$sql_update_item = sqlsrv_query($conexao, "UPDATE TOP(1) loja_itens SET LI_NOME='$item_nome' WHERE LI_COD='$cod'", $conexao_params, $conexao_options);

		$resposta = "Agendamento cadastrado com sucesso.";
		$sucesso = true;
	
	} elseif($editar && !empty($agendamento)) {

		$sql_update = sqlsrv_query($conexao, "UPDATE TOP(1) transportes_agendamento SET TA_HORARIO='$item_horario' WHERE TA_COD='$agendamento'", $conexao_params, $conexao_options);
		$sql_update_item = sqlsrv_query($conexao, "UPDATE TOP(1) loja_itens SET LI_NOME='$item_nome' WHERE LI_COD='$cod'", $conexao_params, $conexao_options);

		$resposta = "Agendamento alterado com sucesso.";
		$sucesso = true;
	
	}

	if($sucesso) {

		//buscar itens
		$sql_itens = sqlsrv_query($conexao, "SELECT * FROM loja_itens WHERE LI_COMPRA='$loja' AND D_E_L_E_T_='0'", $conexao_params, $conexao_options);
		$n_itens = sqlsrv_num_rows($sql_itens);

		if($n_itens > 0) {
			$agendamentos = 0;
			while ($itens = sqlsrv_fetch_array($sql_itens)) {
				$itens_cod = $itens['LI_COD'];
				//busca agendamentos
				$sql_agendamentos = sqlsrv_query($conexao, "SELECT * FROM transportes_agendamento WHERE TA_ITEM='$itens_cod' AND D_E_L_E_T_='0'", $conexao_params, $conexao_options);
				if(sqlsrv_num_rows($sql_agendamentos)) {
					$agendamentos++;
				}
			}
		}

		if(($n_itens-$agendamentos) <= 0) {

			//Buscar cliente
			$sql_loja = sqlsrv_query($conexao, "SELECT TOP 1 LO_CLIENTE FROM loja WHERE LO_COD='$loja'", $conexao_params, $conexao_options);
			if(sqlsrv_num_rows($sql_loja) > 0) {
				$cliente_ar = sqlsrv_fetch_array($sql_loja);
				$cliente = $cliente_ar['LO_CLIENTE'];
			}

			$sql_cliente = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 NOMEPARC, EMAIL, TELEFONE FROM TGFPAR WHERE CODPARC='$cliente'", $conexao_params, $conexao_options);
			if(sqlsrv_num_rows($sql_cliente) > 0) {

				$cliente = sqlsrv_fetch_array($sql_cliente);
				$nome = $cliente['NOMEPARC'];
				$email = $cliente['EMAIL'];
				$telefone = $cliente['TELEFONE'];
				
				//-----------------------------------------------------------------//

				//Envio de SMS
				$sms = "Recebemos o agendamento do seu transporte. Central de Atendimento Folia Tropical: 21 3202 6000";

				require("include/directcall-envio-sms.php");
				directcall($telefone, $sms);
				
				//-----------------------------------------------------------------//

				include("include/class.phpmailer.php");

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
							Recebemos o agendamento do seu transporte.<br /><br />
							Qualquer dúvida ou problema, entre em contato.</font></td>
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

				/*$mail = new PHPMailer();
				$mail->IsSMTP();        //ENVIAR VIA SMTP
				$mail->SMTPAuth = true; //ATIVA O SMTP AUTENTICADO
				$mail->IsHTML(true);        //ATIVA MENSAGEM NO FORMATO TXT, SE true ATIVA NO FORMATO HTML
				
				$mail->Host     = "smtp.gmail.com";     //SERVIDOR DE SMTP, USE mail.SeuDominio.com OU smtp.dominio.com.br	
				$mail->Username = "sistema@grupopacifica.com.br"; //EMAIL PARA SMTP AUTENTICADO (pode ser qualquer conta de email do seu domínio)
				$mail->Password = "sistema2013";                //SENHA DO EMAIL PARA SMTP AUTENTICADO
				$mail->SetFrom("central@grupopacifica.com.br",utf8_decode("Grupo Pacífica"));    //E-MAIL DO REMETENTE, NOME DO REMETENTE
				
				$mail->SMTPSecure = "tls";
				$mail->Host       = "smtp.gmail.com";
				$mail->Port       = 587;	
				
				$mail->AddAddress($email, $nome); //E-MAIL DO DESINATÁRIO, NOME DO DESINATÁRIO
				// $mail->AddReplyTo($email,utf8_decode($nome)); //CONFIGURA O E-MAIL QUE RECEBERÁ A RESPOSTA DESTA MENSAGEM
				
				$mail->Subject = utf8_decode("Confirmação de agendamento de transporte do pedido $cod");  //ASSUNTO DA MENSAGEM
				$mail->Body    = utf8_decode($msg); //CONTEÚDO DA MENSAGEM
				
				$mail->Send();*/

				$remetente_nome = utf8_decode("Grupo Pacífica");
				$remetente_email = 'central@grupopacifica.com.br';
				$destinatario_nome = utf8_decode($nome);
				$destinatario_email = $email;
				$assunto = utf8_decode("Confirmação de agendamento de transporte do pedido $loja");
				$mensagem = utf8_decode($msg);

				enviarEmail($remetente_nome, $remetente_email, $destinatario_nome, $destinatario_email, $assunto, $mensagem);

			}

		}
	}

}

//Fechar conexoes
include("conn/close.php");
include("conn/close-sankhya.php");


?>
<script type="text/javascript">
	alert('<? echo $resposta; ?>');
	location.href='<? echo SITE; ?>agendamentos/editar/<? echo $loja; ?>/<? if($multiplo) { echo "multiplo/"; } ?>';
</script>