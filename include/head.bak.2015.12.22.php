<?

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

if(!isset($meta_title)) $meta_title = "Folia Tropical";
if(!isset($meta_description)) $meta_description = "";

?><!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><? echo $meta_title; ?></title>

<meta name="description" content="<? echo $meta_description; ?>" />
<!-- <meta name="google-site-verification" content="5RHb4Lv8Xw4yE1fwbkVWbxjDigZ9ccmP06vvdesTu14" /> -->
<meta name="google-site-verification" content="v8I95HIBf0F1pESvOT12wWEBBQ5BQ4a5GmvxB2AHbGw" />

<meta http-equiv="expires" content="<? echo date(DATE_RFC822,strtotime("Sat, 26 Jul 1997 05:00:00 GMT")); ?>">
<meta http-equiv="cache-control" content="public" />
<meta http-equiv="Pragma" content="public">

<link rel="publisher" href="https://plus.google.com/u/0/116734980024521633080/"/>
<link rel="shortcut icon" href="<? echo SITE; ?>favicon.ico" />
<link rel="stylesheet" type="text/css" href="<? echo SITE; ?>css/foliatropical.min.css"/>
<? if(defined('PGHOTSITE')) { ?><link rel="stylesheet" type="text/css" href="<? echo SITE; ?>css/index.css"/><? } ?>
<? if(isset($meta_canonical) && !empty($meta_canonical)) { ?><link rel="canonical" href="<? echo $meta_canonical; ?>"/><? } ?>

<?

//if($session_language == 'US') {
    
    $server_name = $_SERVER['SERVER_NAME'];
    $server_uri = $_SERVER ['REQUEST_URI'];
    $server_site = str_replace('http://', '', SITE);
    $server_site = str_replace('www.', '', $server_site);
    $server_site = str_replace($server_name, '', $server_site);
    
    $server_uri = str_replace($server_site, '', $server_uri);
    $server_uri = str_replace('br/', '', $server_uri);
    $server_uri = str_replace('us/', '', $server_uri);
    
    $total_lang = count($lang) - 1;
    $ilang = 1;

    foreach ($lang as $lang_key => $v) {        
    ?>              
    <link rel="alternate" href="<? echo SITE.strtolower($lang_key); ?>/<? echo $server_uri; ?>" hreflang="<? echo $v['link_alternate']; ?>" />
    <?        
    }

//}

?>

<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans:400,600,700"/>
<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans+Condensed:300,700"/>
<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Londrina+Solid"/>

<? /*<link rel="stylesheet" type="text/css" href="<? echo SITE; ?>fancybox/jquery.fancybox.css?v=2.1.5" media="screen" />
*<script type="text/javascript" src="<? echo SITE; ?>js/jquery-1.10.0.min.js"></script>/*
<script type="text/javascript" src="<? echo SITE; ?>js/jquery-migrate-1.2.1.js"></script>
<script type="text/javascript" src="<? echo SITE; ?>js/jquery-ui-1.10.3.custom.min.js"></script>
<script type="text/javascript" src="<? echo SITE; ?>js/jquery.infieldlabel.js"></script>
<script type="text/javascript" src="<? echo SITE; ?>js/jquery.maskedinput.min.js"></script>
<script type="text/javascript" src="<? echo SITE; ?>js/jquery.maskMoney.js"></script>
<script type="text/javascript" src="<? echo SITE; ?>js/jquery.ba-outside-events.min.js"></script>
<script type="text/javascript" src="<? echo SITE; ?>js/jquery.countdown.min.js"></script>
<script type="text/javascript" src="<? echo SITE; ?>js/ddpanorama.min.js"> </script>
<script type="text/javascript" src="<? echo SITE; ?>js/jquery.autosize.js"></script>
<script type="text/javascript" src="<? echo SITE; ?>fancybox/jquery.mousewheel-3.0.6.pack.js"></script>
<script type="text/javascript" src="<? echo SITE; ?>fancybox/jquery.fancybox.js?v=2.1.5"></script>
<script type="text/javascript" src="<? echo SITE; ?>js/jquery.tablesorter.js"></script>
<script type="text/javascript" src="<? echo SITE; ?>js/jquery.tablesorter.pager.js"></script>

<script type="text/javascript" src="<? echo SITE; ?>js/scroll.js"></script>
<script type="text/javascript" src="<? echo SITE; ?>js/global.js"></script>*/ ?>

<script type="text/javascript" src="<? echo SITE; ?>js/all.min.js"></script>

<script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
<script type="text/javascript" src="//assets.pinterest.com/js/pinit.js"></script>

<!--[if IE]>
<script src="<? echo SITE; ?>js/html5.js" type="text/javascript"></script>
<![endif]-->

<!-- Analytics old
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-46488019-1', 'foliatropical.com.br');
  ga('send', 'pageview');

</script>-->

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-717432-69', 'auto');
  ga('send', 'pageview');

</script>

<script type="text/javascript">
var fb_param = {};
fb_param.pixel_id = '6011441902557';
fb_param.value = '0.00';
fb_param.currency = 'BRL';
(function(){
  var fpw = document.createElement('script');
  fpw.async = true;
  fpw.src = '//connect.facebook.net/en_US/fp.js';
  var ref = document.getElementsByTagName('script')[0];
  ref.parentNode.insertBefore(fpw, ref);
})();
</script>
<noscript><img height="1" width="1" alt="" style="display:none" src="https://www.facebook.com/offsite_event.php?id=6011441902557&amp;value=0&amp;currency=BRL" /></noscript>

</head>
<body <? if (defined('PGRESPOSTA')){ echo 'class="resposta"'; } ?> itemscope itemtype="http://schema.org/WebPage">
  
<script>(function(d, s, id) { var js, fjs = d.getElementsByTagName(s)[0]; if (d.getElementById(id)) return; js = d.createElement(s); js.id = id; js.src = "//connect.facebook.net/pt_BR/all.js#xfbml=1"; fjs.parentNode.insertBefore(js, fjs); }(document, 'script', 'facebook-jssdk'));</script>
<script type="text/javascript">
  window.___gcfg = {lang: 'pt-BR'};

  (function() {
    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
    po.src = 'https://apis.google.com/js/platform.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
  })();
</script>
<? if (!defined('PGMODAL')){ ?><section id="outter"><? } ?>