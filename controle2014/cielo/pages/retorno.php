<?php
	
session_start();

//Include cielo
require "../includes/include.php";

// $evento = (int) $_POST['evento'];


/* if(!checklogado()){
// ?>
// <script type="text/javascript">
// 	location.href='<? echo SITE; ?>';
// </script>
// <?
// 	exit();
// } */

// Resgata último pedido feito da SESSION
$cod = (int) $_GET['c'];

//-----------------------------------------------------------------------------//

if(!empty($cod)) {

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


		//$_SESSION["pedidos"]->offsetSet($ultimoPedido, $StrPedido);
		$sql_up = sqlsrv_query($conexao, "UPDATE TOP (1) loja SET LO_XML='$StrPedido', LO_STATUS_TRANSACAO='$co_status' WHERE LO_COD=$cod", $conexao_params, $conexao_options);
		
		//-----------------------------------------------------------------------------//
		
		//Se a compra foi cancelada devolvemos ao estoque
		if($co_status == '6'){

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
			}


			
		} else if($co_status == 4) {
			$sql_cp = sqlsrv_query($conexao, "UPDATE TOP (1) loja SET LO_PAGO=0, LO_DATA_PAGAMENTO=GETDATE() WHERE LO_COD=$cod", $conexao_params, $conexao_options);
		}

		//-----------------------------------------------------------------------------//

		//-----------------------------------------------------------------------------//
		
		$link = "";
		$resposta = "";

		switch($co_status) {
			case 0:			
				$resposta = "O pagamento do pedido ainda não foi processado pela cielo. Será necessário realizar o pagamento novamente caso já tenha feito!";
			break;
			case 3:
			case 5:
			case 8:			
				$resposta = "O pedido não foi autorizado pela administradora do cartão de crédito. Entre em contato com a operadora e efetue o pagamento novamente.";			
			break;
			
			case 4:				
			case 6:				
				$link = "financeiro/pendentes/";
				$resposta = "Pedido autorizado.";
			break;
			
			case 9:			
				$link = "financeiro/pendentes/";
				$resposta = "O pedido foi cancelado pela administradora do cartão de crédito.";
			break;
		}
		
		$transfer = false;

		$sql_compra_transfer = sqlsrv_query($conexao, "
			DECLARE @adicional INT;
			SET @adicional = (SELECT VA_COD FROM vendas_adicionais WHERE VA_NOME_EXIBICAO='delivery');
			SELECT * FROM loja_itens_adicionais WHERE LIA_COMPRA='$cod' AND LIA_ADICIONAL=@adicional", $conexao_params, $conexao_options);

		if(sqlsrv_num_rows($sql_compra_transfer) !== false) {
			$transfer = true;
			$link = 'agendamentos/editar/'.$cod.'/';
		}

		//Delivery
		if($co_delivery) {
			$link = 'compras/delivery/'.$cod.'/';
			if($transfer) $link .= 'transfer/';
		}	

		//-----------------------------------------------------------------//

		//Apenas para homologação

		$link = 'financeiro/pendentes/';

		//-----------------------------------------------------------------//


		header('Content-Type: text/html; charset=utf-8');

		//Incluir arquivos de layout
		include("../../include/head.php");

		?>
		<section id="resposta">
			<a href="<? echo SITE; ?>" id="logo"></a>
			<div class="wrapper">

		        <header>
					<h2>Pedido #<? echo str_pad($cod,6,'0',STR_PAD_LEFT); ?></h2>
					<p><? echo $resposta; ?></p>
				</header>
		    </div>
		    <a href="<? echo SITE.$link; ?>" class="voltar button">Voltar</a>
		    
		</section>
		</body>
		</html>
		<?

		//Incluimos o rodape
		//include("../../include/footer.php");

	}
}
?>