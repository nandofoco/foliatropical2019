<?
/*
if ($_SERVER['SERVER_NAME'] == "server") define ("SITE", "http://server/foliatropical/");
else define ("SITE", "http://www.foliatropical.com.br/");

?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Folia Tropical</title>

<meta name="google-site-verification" content="5RHb4Lv8Xw4yE1fwbkVWbxjDigZ9ccmP06vvdesTu14" />

<link rel="publisher" href="https://plus.google.com/u/0/116734980024521633080/"/>
<link rel="shortcut icon" href="<? echo SITE; ?>favicon.ico" />
<link rel="stylesheet" type="text/css" href="<? echo SITE; ?>css/foliatropical.css"/>
<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Open+Sans:400,600,700"/>
<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Open+Sans+Condensed:300,700"/>

<!--[if IE]>
<script src="<? echo SITE; ?>js/html5.js" type="text/javascript"></script>
<![endif]-->

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-46488019-1', 'foliatropical.com.br');
  ga('send', 'pageview');

</script>

<style type="text/css">

	@charset "utf-8";
	html, body, div, span, applet, object, iframe, table, caption,
	tbody, tfoot, thead, tr, th, td, del, dfn, em, font, img, ins,
	kbd, q, s, samp, small, strike, strong, sub, sup, tt, var,
	h1, h2, h3, h4, h5, h6, p, blockquote, pre, a, abbr,
	acronym, address, big, cite, code, dl, dt, dd, ol, ul, li,
	fieldset, form, label, legend {
	    vertical-align: baseline;
	    font-family: inherit;
	    font-weight: inherit;
	    font-style: inherit;
	    font-size: 100%;
	    outline: 0;
	    padding: 0;
	    margin: 0;
	    border: 0;
	}
	:focus { outline: 0; }
	body {
	    background: white;
	    line-height: 1;
	    color: black;
	}
	ol, ul { list-style: none; }
	table {
	    border-collapse: separate;
	    border-spacing: 0;
	}
	caption, th, td {
	    font-weight: normal;
	    text-align: left;
	}
	blockquote:before, blockquote:after, q:before, q:after { content: ""; }
	blockquote, q { quotes: "" ""; }
	strong { font-weight: bold; }
	em { font-style: italic; }
	header, section, footer, article, nav, aside { display: block; }
	h1, h2, h3, h4, h5, h6 { font-weight: 400; }

	body {      
	    font: normal 12px/1em 'Open Sans Condensed', sans-serif;
	    position: relative;
	    text-align: center;
	    margin: 0;
	    padding: 0;
	    background: #fff url(<? echo SITE; ?>img/bg-body.png) repeat center top;
	}

	body.resposta {
	    background: #ff8400 url(<? echo SITE; ?>img/bg-header-index.png) repeat center top;
	}
	    body.resposta section#resposta {
	        padding: 80px 0;
	        width: 550px;
	        margin: 0 auto;
	    }
	    body.resposta section#resposta a#logo {
	        display: block;
	        margin: 0 auto;
	        width: 480px;
	        height: 365px;
	        background: url(<? echo SITE; ?>img/logo.png) no-repeat center center;
	    }
	        body.resposta section#resposta a#logo span { display: none; }
	    
	    body.resposta section#resposta h2 {
	        font-size: 36px;
	        font-weight: 700;
	        line-height: 1.2em;
	        color: #f8f8f8;
	        letter-spacing: -2px;
	        text-transform: uppercase;
	        text-align: center;
	        z-index: 3;

	        text-shadow: 1px 1px 5px rgba(0,0,0,.1);
	    }
	        body.resposta section#resposta h2 strong {
	            display: block;
	            font-size: 50px;
	            line-height: 1.2em;
	            letter-spacing: -2px;
	            color: #fffbc3;
	        }

	.clear  {
	    clear: both;
	    display: block;
	    font: 1px/0px serif;
	    content: ".";
	    height: 0;
	    visibility: hidden;
	}

</style>
</head>
<body class="resposta">  
    <section id="resposta">
        <a href="<? echo SITE; ?>" id="logo"><span>Folia Tropical</span></a>
        <h2><strong>Site em atualização</strong>Entre em contato através do telefone (21) 3202-6000.</h2>
    </section>
</body>
</html>
<?

exit();*/

//Verificamos o dominio
include("include/checkwww.php");

//Banco de dados
include("conn/conn.php");

//Incluir função de encriptacao
include("include/focoencrypt.php");
define('FOCOENCRYPT', 'true');

// Checar usuario logado
include("include/checklogado.php");

// Checar usuario logado
include("include/language.php");

//Incluir funções básicas
include("include/funcoes.php");

//Incluir função para url amigável
include("include/toascii.php");

?>