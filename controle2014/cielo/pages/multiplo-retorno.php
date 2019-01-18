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

// Resgata último pedido feito da SESSION
$cod = (int) $_GET['c'];

//-----------------------------------------------------------------------------//

if(!empty($cod)) {

	//Buscamos no banco o ultimo pedido
	$sql_co = sqlsrv_query($conexao, "SELECT TOP (1) * FROM loja_pagamento_multiplo WHERE PM_COD=$cod", $conexao_params, $conexao_options);
	if(sqlsrv_num_rows($sql_co) > 0) {

		$co = sqlsrv_fetch_array($sql_co);
		$co_xml = $co['PM_XML'];
		$co_valor = $co['PM_VALOR'];
		$co_compra = $co['PM_LOJA'];
		
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
		$sql_up = sqlsrv_query($conexao, "UPDATE TOP (1) loja_pagamento_multiplo SET PM_XML='$StrPedido', PM_STATUS_TRANSACAO='$co_status' WHERE PM_COD=$cod", $conexao_params, $conexao_options);
		
		//-----------------------------------------------------------------------------//
		
		//Se a compra foi cancelada devolvemos ao estoque
		if($co_status == '6'){

			// $sql_cp = sqlsrv_query($conexao, "UPDATE loja SET LO_PAGO='*', LO_DATA_PAGAMENTO=GETDATE(), LO_DATA_ENTREGA = DATE_ADD(GETDATE(), INTERVAL LO_TEMPO_ENTREGA DAY) WHERE LO_COD=$cod");
			$sql_cp = sqlsrv_query($conexao, "UPDATE TOP (1) loja_pagamento_multiplo SET PM_PAGO=1, PM_DATA_PAGAMENTO=GETDATE() WHERE PM_COD=$cod", $conexao_params, $conexao_options);

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

			#$sql_fin = sqlsrv_query($conexao_sankhya, "INSERT INTO TGFFIN (NUFIN, RECDESP, CODEMP, NUMNOTA, DTNEG, DTALTER, DHMOV, CODNAT, VLRDESDOB, CODPARC) VALUES ((SELECT ISNULL(MAX(NUFIN),0) + 1 FROM TGFFIN),'1', 1, 0, GETDATE(), GETDATE(), GETDATE(), '110101', '$co_valor', '$co_cliente')", $conexao_params, $conexao_options);

			
		} else if($co_status == 4) {
			$sql_cp = sqlsrv_query($conexao, "UPDATE TOP (1) loja_pagamento_multiplo SET PM_PAGO=0, PM_DATA_PAGAMENTO=GETDATE() WHERE PM_COD=$cod", $conexao_params, $conexao_options);
		}


		$sql_pagos = sqlsrv_query($conexao, "SELECT CASE WHEN (SUM(CASE WHEN PM_PAGO=1 THEN 1 ELSE 0 END) = COUNT(PM_COD)) THEN 1 ELSE 0 END AS PAGO FROM loja_pagamento_multiplo WHERE PM_LOJA='$co_compra'", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_pagos) > 0) {

			$arpagos = sqlsrv_fetch_array($sql_pagos);			
			
			$pago_pago = ($arpagos['PAGO'] == 1) ? '1' : '0' ;
			$pago_data = ($arpagos['PAGO'] == 1) ? 'GETDATE()' : 'NULL' ;

			$sql_update = sqlsrv_query($conexao, "UPDATE loja SET LO_PAGO=$pago_pago, LO_DATA_PAGAMENTO=$pago_data WHERE LO_COD='$co_compra'", $conexao_params, $conexao_options);

		}

		//-----------------------------------------------------------------------------//

		//-----------------------------------------------------------------------------//
		
		$link = "";
		$resposta = "";

		switch($co_status) {
			case 0:			
				$resposta = "O pagamento do pedido ainda não foi realizado";
			break;
			case 3:
			case 5:
			case 8:			
				$resposta = "O pedido não foi autorizado pela administradora do cartão de crédito. Entre em contato com a operadora e efetue o pagamento novamente.";			
			break;
			
			case 4:				
			case 6:				
				#$link = "financeiro/pendentes/";
				$resposta = "Pedido autorizado.";
			break;
			
			case 9:			
				#$link = "financeiro/pendentes/";
				$resposta = "O pedido foi cancelado pela administradora do cartão de crédito.";
			break;
		}

		//-----------------------------------------------------------------//

		//Apenas para homologação

		$link = 'compras/pagamento-multiplo/'.$co_compra.'/';

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