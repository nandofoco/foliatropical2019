<?


header('Content-Type: text/html; charset=utf-8');

//Incluir funções básicas
// include("include/includes.php");

//Verificamos o dominio
//include("include/checkwww.php");

//Banco de dados
include("conn/conn.php");

//Incluir funções básicas
// include("include/funcoes.php");

//Incluir função para url amigável
include("include/toascii.php");

//conexao Sankhya
include("conn/conn-sankhya.php");

//-----------------------------------------------------------------//

$sql_update = sqlsrv_query($conexao_sankhya, "UPDATE parceiros.dbo.TGFPAR SET CUPOM ='' WHERE VENDEDOR='S' ", $conexao_params, $conexao_options);
$sql_parceiro = sqlsrv_query($conexao_sankhya, "SELECT * FROM parceiros.dbo.TGFPAR WHERE VENDEDOR='S'", $conexao_params, $conexao_options);

	if(sqlsrv_num_rows($sql_parceiro) > 0) {

		while($parceiro = sqlsrv_fetch_array($sql_parceiro)) {

			$parceiro_cod = $parceiro['CODPARC'];
			$parceiro_nome = trim(utf8_encode($parceiro['NOMEPARC']));
			// $parceiro_cupom = trim(utf8_encode($parceiro['CUPOM']));
			// $parceiro_cupom = empty($parceiro_cupom) ? substr(str_replace('-', '', toAscii($parceiro_nome)), 0, 10) : str_replace('FOLIA', '', $parceiro_cupom);
			$parceiro_cupom = $parceiro_cupoml = 'FOLIA'.strtoupper(substr(str_replace('-', '', toAscii($parceiro_nome)), 0, 10));


			//Valor Unico
			$sql_nome_unico = sqlsrv_query($conexao_sankhya, "SELECT CUPOM FROM parceiros.dbo.TGFPAR WHERE CUPOM='$parceiro_cupom' AND CODPARC<>'$parceiro_cod'", $conexao_params, $conexao_options);
			
			if(sqlsrv_num_rows($sql_nome_unico) > 0) {
				$add = 1;
				$existe_cupom = true;
			
				while($existe_cupom == true){
					$sql_cupom = sqlsrv_query($conexao_sankhya, "SELECT CUPOM FROM parceiros.dbo.TGFPAR WHERE CUPOM='$parceiro_cupom' AND CODPARC<>'$parceiro_cod'", $conexao_params, $conexao_options);
					if(sqlsrv_num_rows($sql_cupom) > 0) {
						$parceiro_cupom = $parceiro_cupoml.$add;
						$add++;
					} else {
						$existe_cupom = false;	
					}

				}
			}

			$sql_update = sqlsrv_query($conexao_sankhya, "UPDATE parceiros.dbo.TGFPAR SET CUPOM ='$parceiro_cupom' WHERE VENDEDOR='S' AND CODPARC='$parceiro_cod'", $conexao_params, $conexao_options);
			echo $parceiro_cupom;
			echo '<br />';

			
		}
				
	}

//-----------------------------------------------------------------//

//Fechar conexoes
include("conn/close.php");
include("conn/close-sankhya.php");

?>