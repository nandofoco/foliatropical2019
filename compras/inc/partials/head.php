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
<meta name="google-site-verification" content="v8I95HIBf0F1pESvOT12wWEBBQ5BQ4a5GmvxB2AHbGw" />

<meta http-equiv="expires" content="<? echo date(DATE_RFC822,strtotime("Sat, 26 Jul 1997 05:00:00 GMT")); ?>">
<meta http-equiv="cache-control" content="public" />
<meta http-equiv="Pragma" content="public">

<meta property="og:image" content="<? echo SITE; ?>img/logo-fb.jpg">

<link rel="publisher" href="//plus.google.com/u/0/116734980024521633080/"/>
<link rel="shortcut icon" href="<? echo SITE; ?>favicon.ico" />

<link rel="stylesheet" type="text/css" href="<? echo SITE; ?>css/style.css"/>
<? if(isset($meta_canonical) && !empty($meta_canonical)) { ?><link rel="canonical" href="<? echo $meta_canonical; ?>"/><? } ?>

<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans:400,600,700"/>
<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans+Condensed:300,700"/>
<!-- <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Londrina+Solid"/> -->


<?
  
    $server_name = $_SERVER['SERVER_NAME'];
    $server_uri = $_SERVER ['REQUEST_URI'];
    $server_site = str_replace('http://', '', SITE);
    $server_site = str_replace('https://', '', $server_site);
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

?>

<script type="text/javascript" src="<? echo SITE; ?>js/dist/bundle.js?v=<? echo microtime(); ?>"></script>

<!--[if IE]>
<script src="<? echo SITE; ?>js/html5.js" type="text/javascript"></script>
<![endif]-->

<script type="text/javascript">
  var site = '<? echo SITE; ?>';
</script>
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-110698545-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-110698545-1');
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
<noscript><img height="1" width="1" alt="" style="display:none" src="//www.facebook.com/offsite_event.php?id=6011441902557&amp;value=0&amp;currency=BRL" /></noscript>

<!-- Facebook Pixel Code -->
<script>
  !function(f,b,e,v,n,t,s)
  {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
  n.callMethod.apply(n,arguments):n.queue.push(arguments)};
  if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
  n.queue=[];t=b.createElement(e);t.async=!0;
  t.src=v;s=b.getElementsByTagName(e)[0];
  s.parentNode.insertBefore(t,s)}(window, document,'script',
  'https://connect.facebook.net/en_US/fbevents.js');
  fbq('init', '338579539852154');
  fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
  src="https://www.facebook.com/tr?id=338579539852154&ev=PageView&noscript=1"
/></noscript>
<!-- End Facebook Pixel Code -->
        
</head>
<body itemscope itemtype="//schema.org/WebPage">
  
<script>(function(d, s, id) { var js, fjs = d.getElementsByTagName(s)[0]; if (d.getElementById(id)) return; js = d.createElement(s); js.id = id; js.src = "//connect.facebook.net/pt_BR/all.js#xfbml=1"; fjs.parentNode.insertBefore(js, fjs); }(document, 'script', 'facebook-jssdk'));</script>
<script type="text/javascript">
  window.___gcfg = {lang: 'pt-BR'};

  (function() {
    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
    po.src = '//apis.google.com/js/platform.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
  })();
</script>