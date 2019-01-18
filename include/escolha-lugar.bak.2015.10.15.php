<?

if (defined('PGINDEX')){
	$escolha_titulo = $lg['escolha_titulo'];
	$class = 'index';
	$tag_titulo = 'h1';
	$tag_noticia = 'h2';
} else {
	$class = 'interno';
	$escolha_titulo = $lg['escolha_conheca_tambem'];
	$tag_titulo = 'h3';
	$tag_noticia = 'h4';
}

?>
<section id="escolha-lugar" class="<? echo $class; ?>">
	<section class="wrapper">
		

		<<? echo $tag_titulo; ?>><? echo $escolha_titulo; ?></<? echo $tag_titulo; ?>>

		<? /*<a class="box folia-tropical" href="<? echo SITE; ?>folia-tropical-carnaval-2015/">
			<<? echo $tag_noticia; ?>><? echo $lg['escolha_folia_tropical']; ?></<? echo $tag_noticia; ?>>
			<ul>
				<li><div class="ver"></div></li>
			</ul>
			<img src="<? echo SITE; ?>img/img-escolha-folia-tropical.jpg" alt="<? echo $lg['escolha_folia_tropical']; ?>" />
			<span></span>
		</a>
		
		<a class="box camarote" href="<? echo SITE; ?>camarotes-carnaval-2015/">
			<<? echo $tag_noticia; ?>><? echo $lg['escolha_camarote']; ?></<? echo $tag_noticia; ?>>
			<ul>
				<li><div class="ver"></div></li>
			</ul>
			<img src="<? echo SITE; ?>img/img-escolha-camarote.jpg" alt="<? echo $lg['escolha_camarote']; ?>" />
			<span></span>
		</a>
		
		<a class="box camarote-corporativo last" href="<? echo SITE; ?>camarote-corporativo-carnaval-2015/">
			<<? echo $tag_noticia; ?>><? echo $lg['escolha_camarote_corporativo']; ?></<? echo $tag_noticia; ?>>
			<ul>
				<li><div class="ver"></div></li>
			</ul>
			<img src="<? echo SITE; ?>img/img-escolha-camarote-corporativo.jpg" alt="<? echo $lg['escolha_camarote_corporativo']; ?>" />
			<span></span>
		</a>
		
		<a class="box arquibancadas" href="<? echo SITE; ?>arquibancadas-carnaval-2015/">
			<<? echo $tag_noticia; ?>><? echo $lg['escolha_arquibancadas']; ?></<? echo $tag_noticia; ?>>
			<ul>
				<li><div class="ver"></div></li>
			</ul>
			<img src="<? echo SITE; ?>img/img-escolha-arquibancadas.jpg" alt="<? echo $lg['escolha_arquibancadas']; ?>" />
			<span></span>
		</a>
		
		<a class="box frisas" href="<? echo SITE; ?>frisas-carnaval-2015/">
			<<? echo $tag_noticia; ?>><? echo $lg['escolha_frisas']; ?></<? echo $tag_noticia; ?>>
			<ul>
				<li><div class="ver"></div></li>
			</ul>
			<img src="<? echo SITE; ?>img/img-escolha-frisas.jpg" alt="<? echo $lg['escolha_frisas']; ?>" />
			<span></span>
		</a>
		
		<a class="box cadeiras last" href="<? echo SITE; ?>cadeiras-numeradas-carnaval-2015/">
			<<? echo $tag_noticia; ?>><? echo $lg['escolha_cadeiras']; ?></<? echo $tag_noticia; ?>>
			<ul>
				<li><div class="ver"></div></li>
			</ul>
			<img src="<? echo SITE; ?>img/img-escolha-cadeiras.jpg" alt="<? echo $lg['escolha_cadeiras']; ?>" />
			<span></span>
		</a>*/ ?>


		<a class="box folia-tropical full" href="<? echo SITE; ?>folia-tropical-carnaval-2016/">
			<<? echo $tag_noticia; ?>><? echo $lg['escolha_folia_tropical']; ?></<? echo $tag_noticia; ?>>
			<ul>
				<li><div class="ver"></div></li>
			</ul>
			<img src="<? echo SITE; ?>img/img-escolha-folia-tropical.jpg" alt="<? echo $lg['escolha_folia_tropical']; ?>" />
			<span></span>
		</a>
		
		
		<a class="box frisas" href="<? echo SITE; ?>frisas-carnaval-2016/">
			<<? echo $tag_noticia; ?>><? echo $lg['escolha_frisas']; ?></<? echo $tag_noticia; ?>>
			<ul>
				<li><div class="ver"></div></li>
			</ul>
			<img src="<? echo SITE; ?>img/img-escolha-frisas.jpg" alt="<? echo $lg['escolha_frisas']; ?>" />
			<span></span>
		</a>

		<a class="box camarote" href="<? echo SITE; ?>camarotes-carnaval-2016/">
			<<? echo $tag_noticia; ?>><? echo $lg['escolha_camarote']; ?></<? echo $tag_noticia; ?>>
			<ul>
				<li><div class="ver"></div></li>
			</ul>
			<img src="<? echo SITE; ?>img/img-escolha-camarote.jpg" alt="<? echo $lg['escolha_camarote']; ?>" />
			<span></span>
		</a>
		
		<a class="box camarote-corporativo last" href="<? echo SITE; ?>camarote-corporativo-carnaval-2016/">
			<<? echo $tag_noticia; ?>><? echo $lg['escolha_camarote_corporativo']; ?></<? echo $tag_noticia; ?>>
			<ul>
				<li><div class="ver"></div></li>
			</ul>
			<img src="<? echo SITE; ?>img/img-escolha-camarote-corporativo.jpg" alt="<? echo $lg['escolha_camarote_corporativo']; ?>" />
			<span></span>
		</a>
		
		<div class="clear"></div>

	</section>
</section>