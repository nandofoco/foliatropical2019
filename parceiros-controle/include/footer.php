	<footer id="rodape"></footer>
	<input type="hidden" id="base-site" value="<? echo SITE; ?>" />
	<?php 
	switch ($_SESSION['ALERT'][0]) {
	case 'sucesso':
	    echo '<script>'.'swal("", "'.$_SESSION['ALERT'][1].'", "success")'.'</script>';
	    break;
	case 'erro':
	    echo '<script>'.'swal("", "'.$_SESSION['ALERT'][1].'", "error")'.'</script>';
	    break;
	case 'aviso':
	    echo '<script>'.'swal("", "'.$_SESSION['ALERT'][1].'", "warning")'.'</script>';
	    break;
	}
	//limpando todos os aviso
	unset($_SESSION['ALERT']);
	?>
</body>
</html>