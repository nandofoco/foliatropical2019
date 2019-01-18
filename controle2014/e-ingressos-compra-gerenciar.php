<?

header('Content-Type: text/html; charset=utf-8');

//Incluir funções básicas
include("include/includes.php");

//-----------------------------------------------------------------------------//

$cod = (int) $_GET['c'];
$acao = format($_GET['a']);
$evento = (int) $_SESSION['usuario-carnaval'];

if(!empty($evento) && !empty($cod) && !empty($acao)) {

	// Buscando os ingressos que estao no mesmo grupo
	$sql_ingressos_valores = sqlsrv_query($conexao, "SELECT * FROM compras WHERE CO_COD='$cod'", $conexao_params, $conexao_options);
	if(sqlsrv_num_rows($sql_ingressos_valores) > 0) {

		$ingressos_valores = sqlsrv_fetch_array($sql_ingressos_valores);
		
		$ingressos_valores_tipo = is_null($ingressos_valores['CO_TIPO']) ? " CO_TIPO IS NULL " : " CO_TIPO='".$ingressos_valores['CO_TIPO']."'";
		$ingressos_valores_setor = is_null($ingressos_valores['CO_SETOR']) ? " AND CO_SETOR IS NULL " : " AND CO_SETOR='".$ingressos_valores['CO_SETOR']."'";
		$ingressos_valores_dia = is_null($ingressos_valores['CO_DIA']) ? " AND CO_DIA IS NULL " : " AND CO_DIA='".$ingressos_valores['CO_DIA']."'";
		$ingressos_valores_fila = is_null($ingressos_valores['CO_FILA']) ? " AND CO_FILA IS NULL " : " AND CO_FILA='".$ingressos_valores['CO_FILA']."'";
		$ingressos_valores_nivel = is_null($ingressos_valores['CO_NIVEL']) ? " AND CO_NIVEL IS NULL " : " AND CO_NIVEL='".$ingressos_valores['CO_NIVEL']."'";
		$ingressos_valores_valor = is_null($ingressos_valores['CO_VALOR']) ? " AND CO_VALOR IS NULL " : " AND CO_VALOR='".$ingressos_valores['CO_VALOR']."'";

		$sql_ingressos = sqlsrv_query($conexao, "SELECT CO_COD FROM compras WHERE $ingressos_valores_tipo $ingressos_valores_setor $ingressos_valores_dia $ingressos_valores_fila $ingressos_valores_nivel $ingressos_valores_valor", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_ingressos) > 0) {
			$ar_ingressos = array();
			while ($ingressos = sqlsrv_fetch_array($sql_ingressos)) array_push($ar_ingressos, $ingressos['CO_COD']);

			$ar_ingressos = implode(",", $ar_ingressos);

			//-----------------------------------------------------------------------------//

			switch ($acao) {
				case 'bloquear':
				case 'desbloquear':
					$block = ($acao == 'bloquear') ? 1 : 0;
					$block_texto = ($block) ? 'bloqueados' : 'desbloqueados';
					$sql_update = sqlsrv_query($conexao, "UPDATE compras SET CO_BLOCK=$block WHERE CO_COD IN($ar_ingressos)", $conexao_params, $conexao_options);
					$mensagem = 'Ingressos '.$block_texto;
				break;

				case 'excluir':
					$sql_update = sqlsrv_query($conexao, "UPDATE compras SET D_E_L_E_T_=1 WHERE CO_COD IN($ar_ingressos)", $conexao_params, $conexao_options);
					$mensagem = 'Ingressos excluídos';
				break;				
			}

			
			?>
			<script type="text/javascript">
				alert('<? echo $mensagem; ?>');
				location.href='<? echo SITE; ?>ingressos/compra/';
			</script>
			<?

			//Fechar conexoes
			include("conn/close.php");

			exit();
			
		}
	} // Ingressos
}

?>
<script type="text/javascript">
	alert('Ocorreu um erro, tente novamente.');
	history.go(-1);
</script>