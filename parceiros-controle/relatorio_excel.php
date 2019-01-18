<?

//-----------------------------------------------------------------//
// Funções básicas
//-----------------------------------------------------------------//
	include("include/includes.php");

//-----------------------------------------------------------------//
 	
 	$cod_parceiro = $_SESSION['us-par-parceiro'];

//-----------------------------------------------------------------//
// Formas de pagamento
//-----------------------------------------------------------------//
	$sql_formas_pagamento = sqlsrv_query($conexao, "SELECT FP_COD, FP_NOME FROM formas_pagamento WHERE D_E_L_E_T_=0 ORDER BY FP_NOME ASC", $conexao_params, $conexao_options);

	if(sqlsrv_num_rows($sql_formas_pagamento))
	{
		while ($ar_formas_pagamento = sqlsrv_fetch_array($sql_formas_pagamento)) 
		{ 
			$forma_pagamento = $ar_formas_pagamento['FP_NOME'];
			$formas_pagamento[$ar_formas_pagamento['FP_COD']] = ($forma_pagamento == utf8_decode('Cartão de Crédito')) ? utf8_decode('Cartão Crédito') : $forma_pagamento;
		}
	}

//-----------------------------------------------------------------//
// Dados da compra
//-----------------------------------------------------------------//
	$sql_loja = sqlsrv_query($conexao, "SELECT
	LO_COD, 
	LO_CLIENTE, 
	LO_PARCEIRO, 
	LO_FORMA_PAGAMENTO, 
	LO_STATUS_TRANSACAO, 
	LO_VALOR_INGRESSOS,
	LO_VALOR_DESCONTO,
	LO_VALOR_OVER_INTERNO,
	LO_VALOR_OVER_EXTERNO,
	LO_ENVIADO,
	LO_DATA_COMPRA,
	LO_COMISSAO,
	LO_COMISSAO_RETIDA,
	LO_COMISSAO_PAGA,
	LO_ORIGEM,
	(CASE WHEN LO_PAGO=1 THEN 'Pago' ELSE 'Reserva' END) AS STATUS,
	(CONVERT(VARCHAR, LO_DATA_COMPRA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, LO_DATA_COMPRA, 108),1,5)) AS DATA, 
	(CONVERT(VARCHAR, LO_DATA_PAGAMENTO, 103)+' '+SUBSTRING(CONVERT(VARCHAR, LO_DATA_PAGAMENTO, 108),1,5)) AS DATA_PAGAMENTO,
	(SUBSTRING(CONVERT(VARCHAR, LO_DATA_COMPRA, 103),1,5)+' '+SUBSTRING(CONVERT(VARCHAR, LO_DATA_COMPRA, 108),1,5)) AS DATA_MINI, 
	(SUBSTRING(CONVERT(VARCHAR, LO_DATA_PAGAMENTO, 103),1,5)+' '+SUBSTRING(CONVERT(VARCHAR, LO_DATA_PAGAMENTO, 108),1,5)) AS DATA_PAGAMENTO_MINI, 
	ISNULL(DATEDIFF (DAY, LO_DATA_PAGAMENTO, GETDATE()), 6) AS DIFERENCA
	FROM loja (NOLOCK) WHERE LO_COMISSAO>0 AND LO_BLOCK='0' AND LO_PARCEIRO=$cod_parceiro AND D_E_L_E_T_='0' AND LO_DATA_COMPRA > '2017-04-02'", $conexao_params, $conexao_options);


	$html = '<?xml version="1.0"?><?mso-application progid="Excel.Sheet"?>
	<Workbook xmlns:x="urn:schemas-microsoft-com:office:excel"
	  xmlns="urn:schemas-microsoft-com:office:spreadsheet"
	  xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">

	    <Styles>
	        <Style ss:ID="Header">
	            <Font ss:Bold="1" />
	        </Style>
	        <Style ss:ID="Default" />
	        <Style ss:ID="Date" ss:Name="Date">
	            <NumberFormat ss:Format="Short Date" />
	        </Style>
	        <Style ss:ID="DateTime">
	            <NumberFormat ss:Format="General Date" />
	        </Style>
	        <Style ss:ID="Time">
	            <NumberFormat ss:Format="Long Time" />
	        </Style>
	        <Style ss:ID="Weight">
	            <NumberFormat ss:Format="0.000" />
	        </Style>
	        <Style ss:ID="Currency">
	            <NumberFormat ss:Format="Currency" />
	        </Style>
	        <Style ss:ID="Percent">
				<NumberFormat ss:Format="0.0%"/>
			</Style>
	        <Style ss:ID="Hyperlink">
	            <Font ss:Color="#0000FF" ss:Underline="Single" />
	        </Style>
	    </Styles>
	';

	$html .= '<Worksheet ss:Name="Vendas com cupom de desconto" ss:Description="Vendas com cupom de desconto"><ss:Table>';

	$html .= '<ss:Row ss:StyleID="Header">';
	$html .= '<ss:Cell><Data ss:Type="String">VHC</Data></ss:Cell>' . "\n";
	$html .= '<ss:Cell><Data ss:Type="String">Cliente</Data></ss:Cell>' . "\n";
	$html .= '<ss:Cell><Data ss:Type="String">Data Compra</Data></ss:Cell>' . "\n";
	$html .= '<ss:Cell><Data ss:Type="String">Forma Pgto</Data></ss:Cell>' . "\n";
	$html .= '<ss:Cell><Data ss:Type="String">Total (R$)</Data></ss:Cell>' . "\n";
	$html .= '<ss:Cell><Data ss:Type="String">Comissão (R$)</Data></ss:Cell>' . "\n";
	$html .= '<ss:Cell><Data ss:Type="String">Comissão (%)</Data></ss:Cell>' . "\n";
	$html .= '<ss:Cell><Data ss:Type="String">Status</Data></ss:Cell>' . "\n";
	$html .= '<ss:Cell><Data ss:Type="String">Origem</Data></ss:Cell>' . "\n";
	$html .= '</ss:Row>';


	while($loja = sqlsrv_fetch_array($sql_loja)) 
	{
					
		$loja_cod = $loja['LO_COD'];
		$loja_data = $loja['DATA'];
		$loja_cliente_cod = $loja['LO_CLIENTE'];
		$loja_parceiro_cod = $loja['LO_PARCEIRO'];
		$loja_tipo_pagamento = $loja['LO_FORMA_PAGAMENTO'];
		$loja_comissao = $loja['LO_COMISSAO'];
		$loja_valor = $loja['LO_VALOR_INGRESSOS'];
		$loja_valor_desconto = $loja['LO_VALOR_DESCONTO'];
		$loja_comissao_retida = (bool) $loja['LO_COMISSAO_RETIDA'];
		$loja_comissao_paga = (bool) $loja['LO_COMISSAO_PAGA'];
		$loja_block = (bool) $loja['LO_BLOCK'];
		$entrega = ($loja_entrega) ? 'ativo' : 'ativar';	
		$loja_status = $loja['STATUS'];		
		
		$loja_origem = $loja['LO_ORIGEM'];

		$sql_valor_ingressos = sqlsrv_query($conexao, "SELECT SUM(LI_VALOR_TABELA) AS INGRESSOS FROM loja_itens WHERE LI_COMPRA='$loja_cod' AND D_E_L_E_T_=0", $conexao_params, $conexao_options);
	  	if(sqlsrv_num_rows($sql_valor_ingressos) > 0) {
	  		$loja_valor_ingressos_ar = sqlsrv_fetch_array($sql_valor_ingressos);
	  		$loja_valor_ingressos = $loja_valor_ingressos_ar['INGRESSOS'];
	  	}

		$loja_valor_total = number_format(($loja_valor_ingressos - $loja_valor_desconto + $loja_over), 2, ",", ".");

		$loja_comissao_valor = number_format((($loja_valor_ingressos - $loja_valor_desconto + $loja_over) * $loja_comissao / 100), 2, ",", ".");
		
		$loja_forma_pagamento = utf8_encode($formas_pagamento[$loja_tipo_pagamento]);					

		$sql_cliente = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 NOMEPARC, CODPARC FROM TGFPAR WHERE CODPARC IN ('$loja_cliente_cod','$loja_parceiro_cod') AND (CLIENTE='S' OR VENDEDOR='S') AND BLOQUEAR='N' ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);

		if(sqlsrv_num_rows($sql_cliente) > 0) {
			while($loja_cliente_ar = sqlsrv_fetch_array($sql_cliente)) {
				
				switch ($loja_cliente_ar['CODPARC']) {
					case $loja_cliente_cod:
						$loja_cliente = trim($loja_cliente_ar['NOMEPARC']);
						//$loja_cliente_exibir = (strlen($loja_cliente) > 20) ? substr($loja_cliente, 0, 20)."..." : $loja_cliente;									
					break;

					case $loja_parceiro_cod:
						$loja_parceiro = trim($loja_cliente_ar['NOMEPARC']);
						//$loja_parceiro_exibir = (strlen($loja_cliente) > 20) ? substr($loja_parceiro, 0, 20)."..." : $loja_parceiro;									
					break;
				}							
			}
		}


		//buscar itens
		$sql_itens = sqlsrv_query($conexao, "SELECT * FROM loja_itens WHERE LI_COMPRA='$loja_cod' AND D_E_L_E_T_='0'", $conexao_params, $conexao_options);
		
		$html .= '<ss:Row>';
		$html .= '<ss:Cell><Data ss:Type="String">'.$loja_cod.'</Data></ss:Cell>' . "\n";
		$html .= '<ss:Cell><Data ss:Type="String">'.$loja_cliente.'</Data></ss:Cell>' . "\n";
		$html .= '<ss:Cell><Data ss:Type="String">'.$loja_data.'</Data></ss:Cell>' . "\n";
		$html .= '<ss:Cell><Data ss:Type="String">'.$loja_forma_pagamento.'</Data></ss:Cell>' . "\n";
		$html .= '<ss:Cell><Data ss:Type="String">'.$loja_valor_total.'</Data></ss:Cell>' . "\n";
		$html .= '<ss:Cell><Data ss:Type="String">'.$loja_comissao_valor.'</Data></ss:Cell>' . "\n";
		$html .= '<ss:Cell><Data ss:Type="String">'.$loja_comissao.'</Data></ss:Cell>' . "\n";
		$html .= '<ss:Cell><Data ss:Type="String">'.$loja_status.'</Data></ss:Cell>' . "\n";
		$html .= '<ss:Cell><Data ss:Type="String">'.$loja_origem.'</Data></ss:Cell>' . "\n";
		$html .= '</ss:Row>';




	}
	
	$html .= '</ss:Table></Worksheet>'; 
	$html .= '</Workbook>';	

	$nome_export = 'Relatorio_Vendas.xls';


	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");
	header("Content-Type: text/html; charset=utf-8");
	header("Content-type: application/excel");
	header("Content-Disposition: attachment; filename=\"{$nome_export}\"");
	header("Content-Description: PHP Generated Data");

	
	echo $html;
	exit();

?>