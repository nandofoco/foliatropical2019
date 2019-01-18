<?

//Incluir funções básicas
include("include/includes.php");

//Conexão com o banco de dados da Sankhya
//include("conn/conn-sankhya.php");

$cod_parceiro = $_SESSION['us-par-parceiro'];

//-----------------------------------------------------------------//

if ($_SERVER['SERVER_NAME'] == "server"){
	$connectionInfo = array( "Database"=>"foliatropical", "UID"=>"sa", "PWD"=>"dedland33#e");
	$conexao = sqlsrv_connect("200.152.124.108", $connectionInfo);
}

$cod = format($_GET['c']);
$cliente = (int) $_GET['cliente'];
$evento = (int) $_SESSION['usuario-carnaval'];

$vendedor = ($_SESSION['us-grupo'] == 'VIN') ? true : false;
$usuario = (int) $_SESSION['us-cod'];

switch ($cod) {
	
	case 'mv':
		$permitir = true;
		$include = 'include/relatorios/relatorio-movimentacao.sql';
		$nome_export = 'Relatorio_Movimentacao.xls';
		$titulo = 'Movimentação Diária';
	break;	

}

if($permitir && ($evento > 0)) {

	$include = BASE.$include;
	$export_base = BASE.$nome_export;
	$export_site = SITE.$nome_export;

	$fp = fopen($include, 'r');
	$relatorio_sql = fread($fp,filesize($include));
	$relatorio_sql = str_replace('%evento%', $evento, $relatorio_sql);

	if(!empty($cliente)) $relatorio_sql = str_replace('%cliente%', $cliente, $relatorio_sql);
	
	fclose($fp);

	// Se o usuário for vendedor interno, ver apenas as suas vendas
	if($vendedor) $relatorio_sql = str_replace('/*vendedor*/', " AND l.LO_VENDEDOR='$usuario' ", $relatorio_sql);

	$sql_relatorio = sqlsrv_query($conexao, $relatorio_sql, $conexao_params, $conexao_options);
	
	$rows = array();
	while(sqlsrv_next_result($sql_relatorio)) {
		$irelatorio = 1;
		while($relatorio = sqlsrv_fetch_array($sql_relatorio)) {

			//Crianmos as colunas
			if($irelatorio == 1) $arkeys = array_keys($relatorio);

			//Criamos um array da linha
			$row = array();
			foreach ($relatorio as $key => $value) {
				//if(!is_numeric($key)) array_push($row, $value);
				if(!is_numeric($key)) $row[$key] = $value;
			}

			//Adicionamos ao array das linhas
			array_push($rows, $row);
			unset($row);
		}
	}

	//Retiramos as colunas duplicadas que tem numero como indice
	$keys = array();
	if(count($arkeys) > 0) {
		foreach ($arkeys as $value) {
			if(!is_numeric($value)) array_push($keys, $value);
		}		
	}
	unset($arkeys);

	//-----------------------------------------------------------------//

	#include("include/excelwriter.inc.php");
	
	#$excel = new ExcelWriter($export_base);

	#if($excel == false) echo $excel->error;
	
	//Colunas
	#$excel->writeLine($keys);
	#foreach ($rows as $line) { $excel->writeLine($line); }
	#$excel->close();

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

	$html .= '<Worksheet ss:Name="'.$titulo.'" ss:Description="'.$titulo.'"><ss:Table>';
	
	$html .= '<ss:Row>';
	foreach ($keys as $value) { $html .= '<ss:Cell><Data ss:Type="String">'.@$value.'</Data></ss:Cell>' . "\n"; }
	$html .= '</ss:Row>';


	foreach ($rows as $line) {
		$html .= '<ss:Row>'. "\n"; 
		
		foreach ($line as $key => $value) {

			switch (true) {
				case ($value instanceof DateTime):
					$styleid = ' ss:StyleID="DateTime"' ;
					$type = 'DateTime' ;
					$value = @$value->format('Y-m-d\TH:i:s.u');
				break;

				case is_float($value) && ($key != 'PERCENTUAL'):
					$styleid = ' ss:StyleID="Currency"' ;
					$type = 'Number' ;
				break;

				case is_float($value) && ($key == 'PERCENTUAL'):
					$styleid = ' ss:StyleID="Percent"' ;
					$type = 'Number' ;
					$value = ($value > 0) ? $value / 100 : 0;
				break;

				case is_numeric($value) && ($key != 'CPF_CNPJ') && ($key != 'NUMERO_CARTAO')  && ($key != 'TID'):
					$styleid = '' ;
					$type = 'Number' ;
				break;

				case is_string($value):
				default:
					$styleid = '' ;
					$type = 'String' ;					
				break;
			}

			if($key == 'CPF_CNPJ') $value = formatCPFCNPJ(trim($value));
			if($key == 'TELEFONE CLIENTE') $value = str_replace('+', '', $value);
			if($key == 'STATUS CLEARSALE' || $key == 'RISCO CLEARSALE' || $key == 'ORIGEM DA VENDA') $value = utf8_decode($value);
			
			$html .= '<ss:Cell'.$styleid.'><Data ss:Type="'.$type.'">'.utf8_encode(trim(@$value)).'</Data></ss:Cell>' . "\n";

		}

		$html .= '</ss:Row>'. "\n";
	}

	$html .= '</ss:Table></Worksheet>'; 
	$html .= '</Workbook>';


	//-----------------------------------------------------------------//
	
	#echo $html;		
	#exit();

	//Configuracoes header para forcar o download 
	// header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	// header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
	// header("Cache-Control: no-cache, must-revalidate");
	// header("Pragma: no-cache");
	// header("Content-Type: text/html; charset=utf-8");
	// header("Content-type: application/excel");
	// header("Content-Disposition: attachment; filename=\"{$nome_export}\"");
	// header("Content-Description: PHP Generated Data");

	echo $html;	
	
	exit();

	?>
	<script type="text/javascript">
		location.href='<? echo $export_site; ?>';
	</script>
	<?

}


//-----------------------------------------------------------------//

//Fechar conexoes
include("conn/close.php");
//include("conn/close-sankhya.php");

?>