<?php 
	
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

//Include cielo
require "../includes/include.php";

// $evento = (int) $_POST['evento'];


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

		if(!empty($co_xml)){
			$Pedido->FromString($co_xml);

			$objResposta = $Pedido->RequisicaoCancelamento();
			
			$Pedido->status = $objResposta->status;

			$StrPedido = $Pedido->ToString();
			$co_status = $Pedido->status;

			if($co_status==9){

				//$_SESSION["pedidos"]->offsetSet($ultimoPedido, $StrPedido);
				// $sql_up = sqlsrv_query($conexao, "UPDATE TOP (1) loja SET LO_XML='$StrPedido', LO_STATUS_TRANSACAO='$co_status' WHERE LO_COD=$cod", $conexao_params, $conexao_options);


				//alteração para excluir do banco de dados(estava no arquivo excluir v2)
				$sql_del_adicionais = sqlsrv_query($conexao, "UPDATE loja_itens_adicionais SET D_E_L_E_T_='1' WHERE LIA_COMPRA='$cod'", $conexao_params, $conexao_options);

				// $sql_del_comentarios = sqlsrv_query($conexao, "UPDATE loja_comentarios SET D_E_L_E_T_='1' WHERE LC_COMPRA='$cod'", $conexao_params, $conexao_options);

				$sql_del_item = sqlsrv_query($conexao, "UPDATE loja_itens SET D_E_L_E_T_='1' WHERE LI_COMPRA='$cod'", $conexao_params, $conexao_options);

				//$sql_del_compra = sqlsrv_query($conexao, "UPDATE loja SET LO_XML='$StrPedido', LO_STATUS_TRANSACAO='$co_status', LO_DATA_PAGAMENTO=NULL, D_E_L_E_T_='1',LO_PAGO='0' WHERE LO_COD='$cod'", $conexao_params, $conexao_options);

				$sql_del_compra = sqlsrv_query($conexao, "UPDATE loja SET LO_XML='$StrPedido', LO_STATUS_TRANSACAO='$co_status', D_E_L_E_T_='1' WHERE LO_COD='$cod'", $conexao_params, $conexao_options);

				$sql_del_cupom = sqlsrv_query($conexao, "UPDATE cupom SET CP_UTILIZADO=0, CP_DATA_UTILIZACAO=NULL WHERE CP_COMPRA='$cod'", $conexao_params, $conexao_options);

				$sql_log = sqlsrv_query($conexao, "INSERT INTO loja_excluidas (LE_COMPRA, LE_USUARIO, LE_DATA) VALUES ('$cod', 'Cliente', GETDATE())", $conexao_params, $conexao_options);

			}

		}	
		//-----------------------------------------------------------------//

			// Enviar email
			require(__DIR__."/../../include/class.phpmailer.php");

			$sql_nome_cliente = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 NOMEPARC, TELEFONE, EMAIL,PAIS_SIGLA FROM TGFPAR WHERE CODPARC='$co_cliente' AND CLIENTE='S' AND BLOQUEAR='N' ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
			
			if(sqlsrv_num_rows($sql_nome_cliente) > 0) {
				$ar_nome_cliente = sqlsrv_fetch_array($sql_nome_cliente);
				$nome_cliente = trim($ar_nome_cliente['NOMEPARC']);
				$email_cliente = trim($ar_nome_cliente['EMAIL']);
				$pais_cliente = $ar_nome_cliente['PAIS_SIGLA'];

				$session_language =  ($pais_cliente!="BR") ? 'US' : 'BR';

				if($session_language == 'US') {
					$titulo = "Your reservation (number ".$cod.") has been canceled";
					$resposta_titulo = "Dear Guest,";
					$resposta_texto = "<td align='left' valign='top'><font face='Arial, Helvetica, sans-serif' color='#666666' size='2'>
							Your reservation (number ".$cod.") has been canceled.<br />
							We noticed that there was an attempt to purchase tickets. Could we help you with something?<br /><br />
							Call Center: 21 3202 6000</font></td>";
				} else {
					$titulo = "A sua reserva (nº ".$cod.") foi cancelada.";
					$resposta_titulo = "Prezado (a),";
					$resposta_texto = "<td align='left' valign='top'><font face='Arial, Helvetica, sans-serif' color='#666666' size='2'>
							A sua reserva (nº ".$cod.") foi cancelada.<br />
							Notamos que houve tentativa de compra de ingressos. Poderíamos te ajudar em algo?<br /><br />
							Central de Atendimento: 21 3202 6000</font></td>";
				}
				
				$msg = "<body>
						<table width='350' border='0' align='center' cellpadding='0' cellspacing='0'>
						  <tr>
							<td height='150' align='center' valign='top'><img src='".SITE."img/logo-email.png' width='200'height='150'></td>
						  </tr>
						  <tr>
							<td align='left' valign='top'>&nbsp;</td>
						  </tr>
						  <tr>
							<td align='left' valign='top'><font face='Arial, Helvetica, sans-serif' color='#666666' size='2'>$resposta_titulo</font></td>
						  </tr>
						  <tr>
							<td align='left' valign='top'>&nbsp;</td>
						  </tr>
						  <tr>
							$resposta_texto
						  </tr>
						  <tr>
							<td align='left' valign='top'>&nbsp;</td>
						  </tr>
						  <tr>
							<td align='center' height='30'><a href='".SITE."' target='_blank' style='text-decoration: none; color: #999;'><font face='Arial, Helvetica, sans-serif' color='#999' size='1'><strong>www.foliatropical.com.br</strong></font></a></td>
						  </tr>
						</table>
						</body>";

				$remetente_nome = utf8_decode("Grupo Pacífica");
				$remetente_email = 'central@grupopacifica.com.br';
				$destinatario_nome = utf8_decode($nome_cliente);
				$destinatario_email = strtolower($email_cliente);
				$assunto = utf8_decode($titulo);
				$mensagem = utf8_decode($msg);

				enviarEmail($remetente_nome, $remetente_email, $destinatario_nome, $destinatario_email, $assunto, $mensagem);
			}
?>
<html>
	<head>
		<title>Cancelamento</title>		
	</head>
	<body>

	<script type="text/javascript">
		// alert('Pagamento confirmado!');
		window.location.href="<? echo SITE; ?>compras/excluir/cliente/<? echo $cod; ?>/";
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