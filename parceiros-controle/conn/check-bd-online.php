<?

//Incluir arquivo de comunicação com o banco
include("conn.php");

//-----------------------------------------------------------------------------//

$autoriza = $_GET['a'];
if(!empty($autoriza)) {

    if ($conexao) {
    	echo json_encode(array("sucesso"=>true));
    	sqlsrv_close($conexao);
    } else {
    	echo json_encode(array("sucesso"=>false));
    }
    
}

?>