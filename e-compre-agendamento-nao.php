<?

header('Content-Type: text/html; charset=utf-8');

//Verificamos o dominio
include("include/includes.php");

//Conexão com o banco de dados do sqlserver
include("conn/conn-mssql.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");

//-----------------------------------------------------------------//

if(!checklogado()){
?>
<script type="text/javascript">
	location.href='<? echo SITE.$link_lang; ?>';
</script>
<?
	exit();
}

//-----------------------------------------------------------------------------//

$interno = (bool) $_GET['interno'];
$loja = (int) $_GET['c'];

$resposta = 'Ocorreu um erro, tente novamente!';

$usuario_cod = $_SESSION['usuario-cod'];

//-----------------------------------------------------------------------------//

if(!empty($loja)) {
	$sql_loja = sqlsrv_query($conexao, "SELECT TOP 1 LO_COD FROM loja WHERE LO_COD='$loja' AND LO_CLIENTE='$usuario_cod' AND D_E_L_E_T_=0", $conexao_params, $conexao_options);
	if(sqlsrv_num_rows($sql_loja) > 0) {
		

		$sql_itens = sqlsrv_query($conexao, "SELECT li.*, v.VE_DIA, v.VE_SETOR, es.ES_NOME, ed.ED_NOME FROM loja_itens li, vendas v, eventos_setores es, eventos_dias ed WHERE li.LI_COMPRA='$loja' AND li.LI_INGRESSO=v.VE_COD AND es.ES_COD=v.VE_SETOR AND ed.ED_COD=v.VE_DIA AND li.D_E_L_E_T_='0' ORDER BY LI_COD ASC", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_itens) > 0) {
			$i = 1;
			while ($item = sqlsrv_fetch_array($sql_itens)) {
				$item_cod = $item['LI_COD'];
				$item_nome = utf8_encode($item['LI_NOME']);
				$item_dia = utf8_encode($item['ED_NOME']);
				$item_setor = $item['ES_NOME'];

				if(is_numeric($item_setor) && ($item_setor%2 == 0)) { $tipo_roteiro = 1; } elseif(is_numeric($item_setor) && ($item_setor%2 != 0)) { $tipo_roteiro = 2; } elseif(!is_numeric($item_setor)) { $tipo_roteiro = 3; }
				
				// Horario
				$sql_roteiros = sqlsrv_query($conexao, "SELECT TOP 1 RO_COD FROM roteiros WHERE RO_COD IN (".implode(',', $roteiros_nao_agendar).") AND RO_BLOCK='0' AND RO_TIPO='$tipo_roteiro' AND D_E_L_E_T_='0' ORDER BY RO_NOME ASC", $conexao_params, $conexao_options);
				if(sqlsrv_num_rows($sql_roteiros) > 0) {
					$roteiros = sqlsrv_fetch_array($sql_roteiros);
					$roteiro = $roteiros['RO_COD'];
				
					$sql_horario = sqlsrv_query($conexao, "SELECT TOP 1 h.TH_COD FROM transportes t, transportes_horarios h WHERE t.TR_ROTEIRO='$roteiro' AND h.TH_TRANSPORTE=t.TR_COD AND h.TH_BLOCK=0 AND h.D_E_L_E_T_ =0 AND t.TR_BLOCK=0 AND t.D_E_L_E_T_=0", $conexao_params, $conexao_options);
					if(sqlsrv_num_rows($sql_horario) > 0) $horario = sqlsrv_fetch_array($sql_horario);
					$item_horario = $horario['TH_COD'];
					
					//Editar ou inserir
					$sql_exist = sqlsrv_query($conexao, "SELECT TA_COD FROM transportes_agendamento WHERE TA_ITEM=$item_cod", $conexao_params, $conexao_options);
					if(sqlsrv_num_rows($sql_exist) > 0) $sql_insert = sqlsrv_query($conexao, "UPDATE TOP(1) transportes_agendamento SET TA_HORARIO='$item_horario' WHERE TA_ITEM='$item_cod'", $conexao_params, $conexao_options);
					else $sql_insert = sqlsrv_query($conexao, "INSERT INTO transportes_agendamento (TA_HORARIO, TA_ITEM) VALUES ('$item_horario', '$item_cod')", $conexao_params, $conexao_options);
					
					$sql_update_item = sqlsrv_query($conexao, "UPDATE TOP(1) loja_itens SET LI_NOME='' WHERE LI_COD='$item_cod'", $conexao_params, $conexao_options);			
				}				

			}
		}

		$resposta = "Agendamento cadastrado com sucesso.";
		$sucesso = true;
		
	}

}

//fechar conexao com o banco
include("conn/close.php");
include("conn/close-mssql.php");
include("conn/close-sankhya.php");

?>
<script type="text/javascript">
	alert('<? echo $resposta; ?>');
	location.href='<? echo SITE.$link_lang; echo $interno ? 'minhas-compras/agendamentos/' : 'ingressos/agendamento/' ; echo $loja; ?>/';
</script>
