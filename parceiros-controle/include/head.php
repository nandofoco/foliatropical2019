<?

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

?><!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="expires" content="<? echo date(DATE_RFC822,strtotime("Sat, 26 Jul 1997 05:00:00 GMT")); ?>">
<title>Folia Tropical</title>

<link rel="shortcut icon" href="<? echo SITE; ?>favicon.ico" />

<link rel="stylesheet" type="text/css" href="<? echo SITE; ?>../plugins/select2/css/select2.css"/>
<link rel="stylesheet" type="text/css" href="<? echo SITE; ?>css/foliatropical.css"/>


<!-- <link rel="stylesheet" type="text/css" href="<? echo SITE; ?>fancybox/jquery.fancybox.css?v=2.1.5" media="screen" /> -->
<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans:400,600,700"/>

<script type="text/javascript" src="<? echo SITE; ?>js/jquery-1.10.0.min.js"></script>
<script type="text/javascript" src="<? echo SITE; ?>js/jquery-migrate-1.2.1.js"></script>
<script type="text/javascript" src="<? echo SITE; ?>js/jquery-ui-1.10.3.custom.min.js"></script>
<script type="text/javascript" src="<? echo SITE; ?>js/jquery.infieldlabel.js"></script>
<script type="text/javascript" src="<? echo SITE; ?>js/jquery.maskedinput.min.js"></script>
<script type="text/javascript" src="<? echo SITE; ?>js/jquery.maskMoney.js"></script>
<script type="text/javascript" src="<? echo SITE; ?>js/jquery.tablesorter.js"></script>
<script type="text/javascript" src="<? echo SITE; ?>js/jquery.tablesorter.pager.js"></script>
<!-- <script type="text/javascript" src="<? echo SITE; ?>fancybox/jquery.mousewheel-3.0.6.pack.js"></script>
<script type="text/javascript" src="<? echo SITE; ?>fancybox/jquery.fancybox.js?v=2.1.5"></script> -->
<!-- botstrap validator -->
<script type="text/javascript" src="<? echo SITE; ?>../plugins/bootstrap-validator/validator.js"></script>

<!-- select2 -->
<script type="text/javascript" src="<? echo SITE; ?>../plugins/select2/js/select2.min.js"></script>

<!-- charts.js -->
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.bundle.min.js"></script>

<!-- Sweet Alerts -->
<link rel="stylesheet" href="<? echo SITE; ?>plugins/sweetalert/sweetalert.css">
<script type="text/javascript"src="<? echo SITE; ?>plugins/sweetalert/sweetalert.js"></script>
    
<script type="text/javascript" src="<? echo SITE; ?>js/scroll.js"></script>
<script type="text/javascript" src="<? echo SITE; ?>js/global.js"></script>

<!--[if IE]>
<script src="<? echo SITE; ?>js/html5.js" type="text/javascript"></script>
<![endif]-->

</head>
<body>
<input type="hidden" name="base-site" value="<?php echo SITE ?>">