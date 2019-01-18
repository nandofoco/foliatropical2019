<?

//Incluir funções básicas
include("include/includes.php");

//-----------------------------------------------------------------------------//

$nome = format($_POST['nome']);

if(!empty($nome)) {

	$abrev_ar = toAscii(utf8_encode($nome));
	$abrev_ar = explode("-", $abrev_ar);
	$abrev = $abrevl = $abrev_ar[0].$abrev_ar[1].$abrev_ar[2];

	//Abreviacao
	$sql_abrev = sqlsrv_query($conexao, "SELECT VA_NOME_EXIBICAO FROM vendas_adicionais WHERE VA_NOME_EXIBICAO='$abrev' AND D_E_L_E_T_=0", $conexao_params, $conexao_options);
	
	if(sqlsrv_num_rows($sql_abrev) > 0) {
		$add = 1;
		$existe_abrev = true;
	
		while($existe_abrev == true){
			$sql_nome = sqlsrv_query($conexao, "SELECT VA_NOME_EXIBICAO FROM vendas_adicionais WHERE VA_NOME_EXIBICAO='$abrev' AND D_E_L_E_T_=0", $conexao_params, $conexao_options);
			if(sqlsrv_num_rows($sql_nome) > 0) {
				$abrev = $abrevl.$add;
				$add++;
			} else {
				$existe_abrev = false;	
			}

		}
	}

	//-----------------------------------------------------------------------------//

	// Inserir venda
	$sql_ins_venda = sqlsrv_query($conexao, "INSERT INTO vendas_adicionais (VA_LABEL, VA_NOME_EXIBICAO, VA_NOME_INSERCAO) VALUES ('$nome', '$abrev', '$abrev')", $conexao_params, $conexao_options);
	$venda = getLastId();
		
	?>
	<script type="text/javascript">
		alert('Adicional cadastro com sucesso.');
		location.href='<? echo SITE; ?>ingressos/adicionais/';
	</script>
	<?

	//Fechar conexoes
	include("conn/close.php");
	
	exit();
}

?>
<script type="text/javascript">
	alert('Ocorreu um erro no cadastro, tente novamente.');
	history.go(-1);
</script>