<?php
	
session_start();

//Include cielo
require "../includes/include.php";
include("../../../include/language.php");

// Resgata último pedido feito da SESSION
$cod = (int) $_GET['c'];

//-----------------------------------------------------------------------------//

if(!empty($cod) /*&& isset($_SESSION['usuario-cod'])*/) {

	//Buscamos no banco o ultimo pedido
	$sql_co = sqlsrv_query($conexao, "SELECT TOP (1) * FROM loja WHERE LO_COD=$cod", $conexao_params, $conexao_options);
	if(sqlsrv_num_rows($sql_co) > 0) {
		
		$co = sqlsrv_fetch_array($sql_co);
		$co_xml = $co['LO_XML'];
		$co_delivery = (bool) $co['LO_DELIVERY'];

		$co_cliente = $co['LO_CLIENTE'];
		$co_parceiro = $co['LO_PARCEIRO'];
		$co_valor = $co['LO_VALOR_TOTAL'];
		
		$Pedido = new Pedido();
		$Pedido->FromString($co_xml);
		
		// Consulta situação da transação
		$objResposta = $Pedido->RequisicaoConsulta();
		
		// Atualiza status
		$Pedido->status = $objResposta->status;
		
		/*if($Pedido->status == '4' || $Pedido->status == '6')
			$finalizacao = true;
		else
			$finalizacao = false;*/
		
		// Atualiza Pedido
		$StrPedido = $Pedido->ToString();
		$co_status = $Pedido->status;

		$cliente = (isset($_SESSION['usuario-cod'])) ? $_SESSION['usuario-cod'] : 0;

		//-----------------------------------------------------------------------------//

		$link = "";
		$resposta = "";

		$co_delete = '';
		$co_delete_bool = false;

		switch($co_status) {
			case 3:
			case 5:
			case 8:			
				$resposta = "O pedido não foi autorizada pela administradora do cartão de crédito e sua compra pedido foi cancelada. Entre em contato com a operadora e efetue a compra novamente.";
				$co_delete = ", D_E_L_E_T_='1' ";
				$co_delete_bool = true;
			break;
			
			case 4:				
			case 6:				
				$resposta = "Recebemos seu pedido, em breve confirmaremos sua compra";
				$link = 'minhas-compras/';
			break;
			
			case 9:			
				$resposta = "O pedido foi cancelado pela administradora do cartão de crédito e sua compra foi cancelada. Entre em contato com a operadora e efetue a compra novamente.";
				$co_delete = ", D_E_L_E_T_='1' ";
				$co_delete_bool = true;
			break;
		}

		//$_SESSION["pedidos"]->offsetSet($ultimoPedido, $StrPedido);
		$sql_up = sqlsrv_query($conexao, "UPDATE TOP (1) loja SET LO_XML='$StrPedido', LO_STATUS_TRANSACAO='$co_status' $co_delete WHERE LO_COD=$cod", $conexao_params, $conexao_options);
		
		//-----------------------------------------------------------------------------//
		
		//Se a compra foi cancelada excluímos
		if($co_delete_bool) {

			$sql_del_adicionais = sqlsrv_query($conexao, "UPDATE loja_itens_adicionais SET D_E_L_E_T_='1' WHERE LIA_COMPRA='$cod'", $conexao_params, $conexao_options);
			$sql_del_item = sqlsrv_query($conexao, "UPDATE loja_itens SET D_E_L_E_T_='1' WHERE LI_COMPRA='$cod'", $conexao_params, $conexao_options);
			$sql_del_compra = sqlsrv_query($conexao, "UPDATE loja SET D_E_L_E_T_='1' WHERE LO_COD='$cod'", $conexao_params, $conexao_options);
			$sql_del_cupom = sqlsrv_query($conexao, "UPDATE cupom SET CP_UTILIZADO=0, CP_DATA_UTILIZACAO=NULL WHERE CP_COMPRA='$cod'", $conexao_params, $conexao_options);

			$sql_log = sqlsrv_query($conexao, "INSERT INTO loja_excluidas (LE_COMPRA, LE_USUARIO, LE_DATA) VALUES ('$cod', '$cliente', GETDATE())", $conexao_params, $conexao_options);

		} else if($co_status == '6'){

			// $sql_cp = sqlsrv_query($conexao, "UPDATE loja SET LO_PAGO='*', LO_DATA_PAGAMENTO=GETDATE(), LO_DATA_ENTREGA = DATE_ADD(GETDATE(), INTERVAL LO_TEMPO_ENTREGA DAY) WHERE LO_COD=$cod");
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

			//"INSERT INTO [SANKHYA_TESTE].[sankhya].[TGFFIN] (NUFIN, RECDESP, CODEMP, NUMNOTA, DTNEG, DTALTER, DHMOV, CODNAT, VLRDESDOB, CODPARC) VALUES ((SELECT ISNULL(MAX(NUFIN),0) + 1 FROM [SANKHYA_TESTE].[sankhya].[TGFFIN]),'1', '2', 0, GETDATE(), GETDATE(), GETDATE(), 0, '800.00', '1')
			//110101

			$sql_fin = sqlsrv_query($conexao_sankhya, "INSERT INTO TGFFIN (NUFIN, RECDESP, CODEMP, NUMNOTA, DTNEG, DTALTER, DHMOV, CODNAT, VLRDESDOB, CODPARC) VALUES ((SELECT ISNULL(MAX(NUFIN),0) + 1 FROM TGFFIN),'1', 1, 0, GETDATE(), GETDATE(), GETDATE(), '110101', '$co_valor', '$co_cliente')", $conexao_params, $conexao_options);


			/*---------------------------------------------------------------------/
			
				Enviar email avisando
			
			/---------------------------------------------------------------------*/

			//Buscar informações do cliente
			$sql_cliente = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 NOMEPARC, EMAIL FROM TGFPAR WHERE CODPARC='$co_cliente'", $conexao_params, $conexao_options);
			if(sqlsrv_num_rows($sql_cliente) > 0) {

				$cliente = sqlsrv_fetch_array($sql_cliente);
				$nome = $cliente['NOMEPARC'];
				$email = $cliente['EMAIL'];

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
				$mail->SetFrom("central@foliatropical.com.br",utf8_decode("Folia Tropical"));    //E-MAIL DO REMETENTE, NOME DO REMETENTE
				
				$mail->SMTPSecure = "tls";
				$mail->Host       = "smtp.gmail.com";
				$mail->Port       = 587;	
				
				$mail->AddAddress($email, $nome); //E-MAIL DO DESINATÁRIO, NOME DO DESINATÁRIO
				#$mail->AddBCC('thalles@fococomunicacao.com', ''); //E-MAIL DO DESINATÁRIO, NOME DO DESINATÁRIO
				// $mail->AddReplyTo($email,utf8_decode($nome)); //CONFIGURA O E-MAIL QUE RECEBERÁ A RESPOSTA DESTA MENSAGEM
				
				$mail->Subject = utf8_decode("Confirmação de pagamento do pedido $cod");  //ASSUNTO DA MENSAGEM
				$mail->Body    = utf8_decode($msg); //CONTEÚDO DA MENSAGEM
				
				$mail->Send();

			}
			
		} else if($co_status == 4) {
			$sql_cp = sqlsrv_query($conexao, "UPDATE TOP (1) loja SET LO_PAGO=0, LO_DATA_PAGAMENTO=GETDATE() WHERE LO_COD=$cod", $conexao_params, $conexao_options);
		}

		//-----------------------------------------------------------------------------//

		

		//-----------------------------------------------------------------//

		//Apenas para homologação

		if($co_delete_bool) $link = 'minhas-compras/';

		switch($co_status) {
			case 0:
				$resposta = $lg['retorno_resposta0'];
				break;	
			case 1:			
				$resposta = $lg['retorno_resposta1'];
				break;
			case 2:
				$resposta = $lg['retorno_resposta2'];
				break;
			case 3:
				$resposta = $lg['retorno_resposta3'];
				break;
			case 5:
				$resposta = $lg['retorno_resposta5'];
				break;		
			case 4:				
			case 6:				
				$resposta = $lg['retorno_resposta4'];
			break;
			case 9:			
				$resposta = $lg['retorno_resposta9'];
			break;
			case 10:			
				$resposta = $lg['retorno_resposta10'];
			break;
			case 12:			
				$resposta = $lg['retorno_resposta12'];
			break;
		}

		//-----------------------------------------------------------------//


		header('Content-Type: text/html; charset=utf-8');

		//Incluir arquivos de layout
		include("../../include/head.php");

		?>
		<script>
		fbq('track', 'Purchase');
		</script>

		<script type="text/javascript">
		/* <![CDATA[ */
		var google_conversion_id = 868380864;
		var google_custom_params = window.google_tag_params;
		var google_remarketing_only = true;
		/* ]]> */
		</script>
		<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
		</script>
		<noscript>
		<div style="display:inline;">
		<img height="1" width="1" style="border-style:none;" alt="" src="//googleads.g.doubleclick.net/pagead/viewthroughconversion/868380864/?guid=ON&amp;script=0"/>
		</div>
		</noscript>

		<!-- Google Code for Vendas - Folia Conversion Page -->
		<script type="text/javascript">
		/* <![CDATA[ */
		var google_conversion_id = 868380864;
		var google_conversion_label = "usXQCO_FjGwQwOGJngM";
		var google_remarketing_only = false;
		/* ]]> */
		</script>
		<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
		</script>
		<noscript>
		<div style="display:inline;">
		<img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/868380864/?label=usXQCO_FjGwQwOGJngM&amp;guid=ON&amp;script=0"/>
		</div>
		</noscript>

		<section id="resposta">
			<a href="<? echo str_replace('controle2014/', '', SITE); ?>" id="logo"></a>
			<div class="wrapper">

		        <header>
					<h2>Voucher #<? echo str_pad($cod,6,'0',STR_PAD_LEFT); ?></h2>
					<p><? echo $resposta; ?></p>
				</header>
		    </div>
		    <a href="<? echo str_replace('controle2014/', '', SITE).$link; ?>" class="voltar button"><?php echo $lg['retorno_voltar'] ?></a>
		    
		</section>
		</body>
		</html>
		<?

		//Incluimos o rodape
		//include("../../include/footer.php");

	}
}
?>