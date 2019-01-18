<?

header('Content-Type: text/html; charset=utf-8');

//Verificamos o dominio
include("include/includes.php");

//Conexão com o banco de dados do sqlserver
include("conn/conn-mssql.php");

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

$cod = (int) $_POST['cod'];
$cupom = format($_POST['cupom']);
$matricula = format($_POST['matricula']);
$v2 = (isset($_POST['v2'])) ? 'v2/' : '' ;
$paypal = (isset($_POST['paypal'])) ? 'paypal/' : '' ;

$cliente = $_SESSION['usuario-cod'];


//-----------------------------------------------------------------------------//

if(!empty($cod) && !empty($cupom)) {

	// RTA Petros
	if(strtoupper($cupom) == 'PETROS') {

		if(!empty($matricula)) {			
			$_SESSION['compra-cupom-petros']['usuario'] = $cliente;
			$_SESSION['compra-cupom-petros']['matricula'] = $matricula;
		}

		?>
		<script type="text/javascript">
			location.href='<? echo SITE.$link_lang; ?>ingressos/pagamento/<? echo $v2.$paypal.$cod; ?>/';
		</script>
		<?

		//fechar conexao com o banco
		include("conn/close.php");
		include("conn/close-mssql.php");

		exit();


	} else { //-----------------------------------------------------------------------------//

		//Verificar a existencia de cupom de desconto para essa compra
		$sql_exist_cupom = sqlsrv_query($conexao, "SELECT TOP 1 * FROM cupom WHERE CP_COMPRA='$cod' AND CP_BLOCK='0' AND D_E_L_E_T_='0' AND CP_UTILIZADO='1' ", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_exist_cupom) > 0) {
			?>
			<script type="text/javascript">
				alert('Um cupom já foi utilizado para esta compra.');
				location.href='<? echo SITE.$link_lang; ?>ingressos/pagamento/<? echo $cod; ?>/';
			</script>
			<?
			exit();

		} else {

			$sql_cupom = sqlsrv_query($conexao, "SELECT TOP 1 * FROM cupom WHERE CP_CUPOM='$cupom' AND CP_BLOCK='0' AND D_E_L_E_T_='0' AND CP_UTILIZADO='0' AND CP_DATA_VALIDADE >= GETDATE() ", $conexao_params, $conexao_options);
			$n_cupom = sqlsrv_num_rows($sql_cupom);


			if($n_cupom > 0) {
								
				$cupom = sqlsrv_fetch_array($sql_cupom);
				$cupom_cod = $cupom['CP_COD'];

				$_SESSION['compra-cupom']['usuario'] = $cliente;
				$_SESSION['compra-cupom']['cod'] = $cupom_cod;

				?>
				<script type="text/javascript">
					location.href='<? echo SITE.$link_lang; ?>ingressos/pagamento/<? echo $cod; ?>/';
				</script>
				<?

				//fechar conexao com o banco
				include("conn/close.php");
				include("conn/close-mssql.php");

				exit();

			}

		}	
	} //Petros

}

$cupom = (int) $_GET['c'];
$cod = format($_GET['i']);
$v2 = (isset($_GET['v2'])) ? 'v2/' : '' ;
$paypal = (isset($_GET['paypal'])) ? 'paypal/' : '' ;

if(!empty($cod) && !empty($cupom)) {

	if($_SESSION['compra-cupom']['cod'] = $cupom) unset($_SESSION['compra-cupom']);
	if($_SESSION['compra-cupom-petros']) unset($_SESSION['compra-cupom-petros']);
	?>
	<script type="text/javascript">
		location.href='<? echo SITE.$link_lang; ?>ingressos/pagamento/<? echo $v2.$paypal.$cod; ?>/';
	</script>
	<?

	//fechar conexao com o banco
	include("conn/close.php");
	include("conn/close-mssql.php");

	exit();	

}

?>
<script type="text/javascript">
	alert('Cupom indisponível');
	history.go(-1);
</script>