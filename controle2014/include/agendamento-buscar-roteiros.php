<?
session_start();

include("../conn/conn.php");

header('Content-Type: text/html; charset=utf-8'); 
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1 
header("Expires: Fri, 1 Jan 2010 08:00:00 GMT"); // Date in the past 

$sucesso = false;

$roteiro = $_POST['roteiro'];
$transporte = $_POST['transporte'];

if(!empty($roteiro)) {

	//busca locais do roteiro
	$sql_transporte = sqlsrv_query($conexao, "SELECT * FROM transportes WHERE TR_ROTEIRO='$roteiro' AND TR_BLOCK='0' AND D_E_L_E_T_='0' ORDER BY TR_NOME ASC", $conexao_params, $conexao_options);
	$n_transporte = sqlsrv_num_rows($sql_transporte);

	if($n_transporte > 0) {
		$lista="";
		while ($transportes = sqlsrv_fetch_array($sql_transporte)) {
			$transportes_cod = $transportes['TR_COD'];
			$transportes_nome = utf8_encode($transportes['TR_NOME']);
			$lista .= '<li><label class="item"><input type="radio" name="item-transporte" value="'.$transportes_cod.'" alt="'.$transportes_nome.'" />'.$transportes_nome.'</label></li>';
		}
	}
		$sucesso = true;

} elseif(!empty($transporte)) {

	//busca horarios do transporte
	$sql_horarios = sqlsrv_query($conexao, "SELECT *, SUBSTRING(CONVERT(CHAR, TH_HORA, 8), 1, 5) AS HORA FROM transportes_horarios WHERE TH_TRANSPORTE='$transporte' AND TH_BLOCK='0' AND D_E_L_E_T_='0' ORDER BY TH_HORA ASC", $conexao_params, $conexao_options);
	$n_horarios = sqlsrv_num_rows($sql_horarios);

	if($n_horarios > 0) {
		$lista="";
		while ($horarios = sqlsrv_fetch_array($sql_horarios)) {
			$horarios_cod = $horarios['TH_COD'];
			$horarios_hora = $horarios['HORA'];
									
			$lista .= '<li><label class="item"><input type="radio" name="item-horario" value="'.$horarios_cod.'" alt="'.$horarios_hora.'" />'.$horarios_hora.'</label></li>';
		}
	}
		$sucesso = true;
}
echo json_encode(array("sucesso"=>$sucesso, "lista"=>$lista));

//Fechar conexoes
include("../conn/close.php");

?>