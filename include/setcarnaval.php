<?
	
function setcarnaval(){

	global $conexao, $conexao_params, $conexao_options;
	
    #$sql_carnaval_atual = sqlsrv_query($conexao, "SELECT TOP 1 EV_COD FROM eventos WHERE EV_ANO >=  DATEPART(year, GETDATE()) AND EV_BLOCK=0 AND D_E_L_E_T_=0 ORDER BY EV_ANO DESC", $conexao_params, $conexao_options);
    $sql_carnaval_atual = sqlsrv_query($conexao, "SELECT TOP 1 EV_COD FROM eventos WHERE EV_BLOCK=0 AND D_E_L_E_T_=0 ORDER BY EV_ANO DESC", $conexao_params, $conexao_options);
    if(sqlsrv_num_rows($sql_carnaval_atual) > 0) {
        $carnaval_atual = sqlsrv_fetch_array($sql_carnaval_atual);
        $carnaval_atual = $carnaval_atual['EV_COD'];
    }

    return isset($carnaval_atual) ? $carnaval_atual : null;
}

?>