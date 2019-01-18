<? 	
	$qtd_arqui_t = $ar_grafico['qtde_arquibancadas'];
	$qtd_frisa_f = $ar_grafico['qtde_frisas'];	
	$qtd_camar_c = $ar_grafico['qtde_camarotes'];	
	$qtd_folia_ft = $ar_grafico['qtde_folia'];
	$qtd_super_s = $ar_grafico['qtde_super'];
	$qtd_lounges_l = $ar_grafico['qtde_lounges'];	

	//-----------------------------------------------------------------------------
	// 09/02 - Arquibancadas
	//-----------------------------------------------------------------------------
	
	$qtd_arqui_dia = $ar_grafico['qtde_arquibancadas_33'];

	$relatorio_exel[0]['dia'] = '09/02';

	$porcentagem_a = ($qtd_arqui_dia > 0) ? (($qtd_arqui_dia*100) / $qtd_arqui_t) : 0;	
	$relatorio_exel[0]['porcentagem'] = round($porcentagem_a);

	$relatorio_exel[0]['tipo'] = 'Arquibancadas';
	$relatorio_exel[0]['qtd'] = $qtd_arqui_dia;
	$relatorio_exel[0]['valor'] = number_format($ar_grafico['valor_arquibancadas_33'], 2, ',', '.');


	//-----------------------------------------------------------------------------
	// 09/02 - Frisas
	//-----------------------------------------------------------------------------	
	$qtd_frisas_dia = $ar_grafico['qtde_frisas_33'];

	$relatorio_exel[1]['dia'] = '09/02';

	$porcentagem_f = ($qtd_frisas_dia > 0) ? (($qtd_frisas_dia*100) / $qtd_frisa_f) : 0;
	$relatorio_exel[1]['porcentagem'] = round($porcentagem_f);

	$relatorio_exel[1]['tipo'] = 'Frisas';
	$relatorio_exel[1]['qtd'] = $qtd_frisas_dia;
	$relatorio_exel[1]['valor'] = number_format($ar_grafico['valor_frisas_33'], 2, ',', '.');


	//-----------------------------------------------------------------------------
	// 09/02 - Camarotes
	//-----------------------------------------------------------------------------
	$qtd_camar_dia = $ar_grafico['qtde_camarotes_33'];

	$relatorio_exel[2]['dia'] = '09/02';

	$porcentagem_c = ($qtd_camar_dia > 0) ? (($qtd_camar_dia*100) / $qtd_camar_c) : 0;
	$relatorio_exel[2]['porcentagem'] = round($porcentagem_c);

	$relatorio_exel[2]['tipo'] = 'Camarotes';
	$relatorio_exel[2]['qtd'] = $qtd_camar_dia;
	$relatorio_exel[2]['valor'] = number_format($ar_grafico['valor_camarotes_33'], 2, ',', '.');


	//-----------------------------------------------------------------------------
	// 09/02 - Folia Tropical
	//-----------------------------------------------------------------------------	
	$qtd_folia_dia = $ar_grafico['qtde_folia_33'];

	$relatorio_exel[3]['dia'] = '09/02';

	$porcentagem_ft = ($qtd_folia_dia > 0) ? (($qtd_folia_dia*100) / $qtd_folia_ft) : 0;
	$relatorio_exel[3]['porcentagem'] = round($porcentagem_ft);

	$relatorio_exel[3]['tipo'] = 'Folia Tropical';
	$relatorio_exel[3]['qtd'] = $qtd_folia_dia;
	$relatorio_exel[3]['valor'] = number_format($ar_grafico['valor_folia_33'], 2, ',', '.');


	//-----------------------------------------------------------------------------
	// 09/02 - Super Folia
	//-----------------------------------------------------------------------------	
	$qtd_super_dia = $ar_grafico['qtde_super_33'];

	$relatorio_exel[4]['dia'] = '09/02';

	$porcentagem_s = ($qtd_super_dia > 0) ? (($qtd_super_dia*100) / $qtd_super_s) : 0;
	$relatorio_exel[4]['porcentagem'] =  round($porcentagem_s);

	$relatorio_exel[4]['tipo'] = 'Super Folia';
	$relatorio_exel[4]['qtd'] = $qtd_super_dia;
	$relatorio_exel[4]['valor'] = number_format($ar_grafico['valor_super_33'], 2, ',', '.');


	//-----------------------------------------------------------------------------
	// 09/02 - Folia + Super Folia
	//-----------------------------------------------------------------------------	
	$qtd_lounges_dia = $ar_grafico['qtde_lounges_33'];
	$relatorio_exel[5]['dia'] = '09/02';

	$porcentagem_l = ($qtd_lounges_dia > 0) ? (($qtd_lounges_dia*100) / $qtd_lounges_l) : 0;
	$relatorio_exel[5]['porcentagem'] = round($porcentagem_l);

	$relatorio_exel[5]['tipo'] = 'Folia + Super Folia';
	$relatorio_exel[5]['qtd'] = $qtd_lounges_dia;
	$relatorio_exel[5]['valor'] = number_format($ar_grafico['valor_lounges_33'], 2, ',', '.');


	//-----------------------------------------------------------------------------
	// 10/02 - Arquibancadas
	//-----------------------------------------------------------------------------
	$qtd_arqui_dia = $ar_grafico['qtde_arquibancadas_34'];

	$relatorio_exel[6]['dia'] = '10/02';

	$porcentagem_a = ($qtd_arqui_dia > 0) ? (($qtd_arqui_dia*100) / $qtd_arqui_t) : 0;	
	$relatorio_exel[6]['porcentagem'] = round($porcentagem_a);

	$relatorio_exel[6]['tipo'] = 'Arquibancadas';
	$relatorio_exel[6]['qtd'] = $ar_grafico['qtde_arquibancadas_34'];
	$relatorio_exel[6]['valor'] = number_format($ar_grafico['valor_arquibancadas_34'], 2, ',', '.');


	//-----------------------------------------------------------------------------
	// 10/02 - Frisas
	//-----------------------------------------------------------------------------
	$qtd_frisas_dia = $ar_grafico['qtde_frisas_34'];

	$relatorio_exel[7]['dia'] = '10/02';

	$porcentagem_f = ($qtd_frisas_dia > 0) ? (($qtd_frisas_dia*100) / $qtd_frisa_f) : 0;
	$relatorio_exel[7]['porcentagem'] = round($porcentagem_f);

	$relatorio_exel[7]['tipo'] = 'Frisas';
	$relatorio_exel[7]['qtd'] = $ar_grafico['qtde_frisas_34'];
	$relatorio_exel[7]['valor'] = number_format($ar_grafico['valor_frisas_34'], 2, ',', '.');


	//-----------------------------------------------------------------------------
	// 10/02 - Camarotes
	//-----------------------------------------------------------------------------
	$qtd_camar_dia = $ar_grafico['qtde_camarotes_34'];

	$relatorio_exel[8]['dia'] = '10/02';

	$porcentagem_c = ($qtd_camar_dia > 0) ? (($qtd_camar_dia*100) / $qtd_camar_c) : 0;
	$relatorio_exel[8]['porcentagem'] = round($porcentagem_c);

	$relatorio_exel[8]['tipo'] = 'Camarotes';
	$relatorio_exel[8]['qtd'] = $ar_grafico['qtde_camarotes_34'];
	$relatorio_exel[8]['valor'] = number_format($ar_grafico['valor_camarotes_34'], 2, ',', '.');


	//-----------------------------------------------------------------------------
	// 10/02 - Folia Tropical
	//-----------------------------------------------------------------------------	
	$qtd_folia_dia = $ar_grafico['qtde_folia_34'];

	$relatorio_exel[9]['dia'] = '10/02';

	$porcentagem_ft = ($qtd_folia_dia > 0) ? (($qtd_folia_dia*100) / $qtd_folia_ft) : 0;
	$relatorio_exel[9]['porcentagem'] = round($porcentagem_ft);

	$relatorio_exel[9]['tipo'] = 'Folia Tropical';
	$relatorio_exel[9]['qtd'] = $ar_grafico['qtde_folia_34'];
	$relatorio_exel[9]['valor'] = number_format($ar_grafico['valor_folia_34'], 2, ',', '.');


	//-----------------------------------------------------------------------------
	// 10/02 - Super Folia
	//-----------------------------------------------------------------------------
	$qtd_super_dia = $ar_grafico['qtde_super_34'];

	$relatorio_exel[10]['dia'] = '10/02';

	$porcentagem_s = ($qtd_super_dia > 0) ? (($qtd_super_dia*100) / $qtd_super_s) : 0;
	$relatorio_exel[10]['porcentagem'] =  round($porcentagem_s);

	$relatorio_exel[10]['tipo'] = 'Super Folia';
	$relatorio_exel[10]['qtd'] = $ar_grafico['qtde_super_34'];
	$relatorio_exel[10]['valor'] = number_format($ar_grafico['valor_super_34'], 2, ',', '.');


	//-----------------------------------------------------------------------------
	// 10/02 - Folia + Super Folia
	//-----------------------------------------------------------------------------	
	$qtd_lounges_dia = $ar_grafico['qtde_lounges_34'];

	$relatorio_exel[11]['dia'] = '10/02';

	$porcentagem_l = ($qtd_lounges_dia > 0) ? (($qtd_lounges_dia*100) / $qtd_lounges_l) : 0;
	$relatorio_exel[11]['porcentagem'] = round($porcentagem_l);

	$relatorio_exel[11]['tipo'] = 'Folia + Super Folia';
	$relatorio_exel[11]['qtd'] = $ar_grafico['qtde_lounges_34'];
	$relatorio_exel[11]['valor'] = number_format($ar_grafico['valor_lounges_34'], 2, ',', '.');

	
	//-----------------------------------------------------------------------------
	// 11/02 - Arquibancadas
	//-----------------------------------------------------------------------------	
	$qtd_arqui_dia = $ar_grafico['qtde_arquibancadas_35'];

	$relatorio_exel[12]['dia'] = '11/02';	

	$porcentagem_a = ($qtd_arqui_dia > 0) ? (($qtd_arqui_dia*100) / $qtd_arqui_t) : 0;	
	$relatorio_exel[12]['porcentagem'] = round($porcentagem_a);

	$relatorio_exel[12]['tipo'] = 'Arquibancadas';
	$relatorio_exel[12]['qtd'] = $ar_grafico['qtde_arquibancadas_35'];
	$relatorio_exel[12]['valor'] = number_format($ar_grafico['valor_arquibancadas_35'], 2, ',', '.');


	//-----------------------------------------------------------------------------
	// 11/02 - Frisas
	//-----------------------------------------------------------------------------
	$qtd_frisas_dia = $ar_grafico['qtde_frisas_35'];

	$relatorio_exel[13]['dia'] = '11/02';

	$porcentagem_f = ($qtd_frisas_dia > 0) ? (($qtd_frisas_dia*100) / $qtd_frisa_f) : 0;
	$relatorio_exel[13]['porcentagem'] = round($porcentagem_f);

	$relatorio_exel[13]['tipo'] = 'Frisas';
	$relatorio_exel[13]['qtd'] = $ar_grafico['qtde_frisas_35'];
	$relatorio_exel[13]['valor'] = number_format($ar_grafico['valor_frisas_35'], 2, ',', '.');


	//-----------------------------------------------------------------------------
	// 11/02 - Camarotes
	//-----------------------------------------------------------------------------
	$qtd_camar_dia = $ar_grafico['qtde_camarotes_33'];

	$relatorio_exel[14]['dia'] = '11/02';

	$porcentagem_c = ($qtd_camar_dia > 0) ? (($qtd_camar_dia*100) / $qtd_camar_c) : 0;
	$relatorio_exel[14]['porcentagem'] = round($porcentagem_c);

	$relatorio_exel[14]['tipo'] = 'Camarotes';
	$relatorio_exel[14]['qtd'] = $ar_grafico['qtde_camarotes_35'];
	$relatorio_exel[14]['valor'] = number_format($ar_grafico['valor_camarotes_35'], 2, ',', '.');


	//-----------------------------------------------------------------------------
	// 11/02 - Folia Tropical
	//-----------------------------------------------------------------------------
	$qtd_folia_dia = $ar_grafico['qtde_folia_35'];

	$relatorio_exel[15]['dia'] = '11/02';

	$porcentagem_ft = ($qtd_folia_dia > 0) ? (($qtd_folia_dia*100) / $qtd_folia_ft) : 0;
	$relatorio_exel[15]['porcentagem'] = round($porcentagem_ft);

	$relatorio_exel[15]['tipo'] = 'Folia Tropical';
	$relatorio_exel[15]['qtd'] = $ar_grafico['qtde_folia_35'];
	$relatorio_exel[15]['valor'] = number_format($ar_grafico['valor_folia_35'], 2, ',', '.');


	//-----------------------------------------------------------------------------
	// 11/02 - Super Folia
	//-----------------------------------------------------------------------------
	$qtd_super_dia = $ar_grafico['qtde_super_35'];

	$relatorio_exel[16]['dia'] = '11/02';

	$porcentagem_s = ($qtd_super_dia > 0) ? (($qtd_super_dia*100) / $qtd_super_s) : 0;
	$relatorio_exel[16]['porcentagem'] =  round($porcentagem_s);

	$relatorio_exel[16]['tipo'] = 'Super Folia';
	$relatorio_exel[16]['qtd'] = $ar_grafico['qtde_super_35'];
	$relatorio_exel[16]['valor'] = number_format($ar_grafico['valor_super_35'], 2, ',', '.');


	//-----------------------------------------------------------------------------
	// 11/02 - Folia + SuperFolia
	//-----------------------------------------------------------------------------
	$qtd_lounges_dia = $ar_grafico['qtde_lounges_35'];

	$relatorio_exel[17]['dia'] = '11/02';

	$porcentagem_l = ($qtd_lounges_dia > 0) ? (($qtd_lounges_dia*100) / $qtd_lounges_l) : 0;
	$relatorio_exel[17]['porcentagem'] = round($porcentagem_l);

	$relatorio_exel[17]['tipo'] = 'Folia + Super Folia';
	$relatorio_exel[17]['qtd'] = $ar_grafico['qtde_lounges_35'];
	$relatorio_exel[17]['valor'] = number_format($ar_grafico['valor_lounges_35'], 2, ',', '.');

	
	//-----------------------------------------------------------------------------
	// 12/02 - Arquibancadas
	//-----------------------------------------------------------------------------
	$qtd_arqui_dia = $ar_grafico['qtde_arquibancadas_36'];

	$relatorio_exel[18]['dia'] = '12/02';

	$porcentagem_a = ($qtd_arqui_dia > 0) ? (($qtd_arqui_dia*100) / $qtd_arqui_t) : 0;	
	$relatorio_exel[18]['porcentagem'] = round($porcentagem_a);

	$relatorio_exel[18]['tipo'] = 'Arquibancadas';
	$relatorio_exel[18]['qtd'] = $ar_grafico['qtde_arquibancadas_36'];
	$relatorio_exel[18]['valor'] = number_format($ar_grafico['valor_arquibancadas_36'], 2, ',', '.');


	//-----------------------------------------------------------------------------
	// 12/02 - Frisas
	//-----------------------------------------------------------------------------
	$qtd_frisas_dia = $ar_grafico['qtde_frisas_36'];

	$relatorio_exel[19]['dia'] = '12/02';

	$porcentagem_f = ($qtd_frisas_dia > 0) ? (($qtd_frisas_dia * 100) / $qtd_frisa_f) : 0;
	$relatorio_exel[19]['porcentagem'] = round($porcentagem_f);

	$relatorio_exel[19]['tipo'] = 'Frisas';
	$relatorio_exel[19]['qtd'] = $ar_grafico['qtde_frisas_36'];
	$relatorio_exel[19]['valor'] = number_format($ar_grafico['valor_frisas_36'], 2, ',', '.');


	//-----------------------------------------------------------------------------
	// 12/02 - Camarotes
	//-----------------------------------------------------------------------------
	$qtd_camar_dia = $ar_grafico['qtde_camarotes_36'];

	$relatorio_exel[20]['dia'] = '12/02';

	$porcentagem_c = ($qtd_camar_dia > 0) ? (($qtd_camar_dia * 100) / $qtd_camar_c) : 0;
	$relatorio_exel[20]['porcentagem'] = round($porcentagem_c);

	$relatorio_exel[20]['tipo'] = 'Camarotes';
	$relatorio_exel[20]['qtd'] = $ar_grafico['qtde_camarotes_36'];
	$relatorio_exel[20]['valor'] = number_format($ar_grafico['valor_camarotes_36'], 2, ',', '.');


	//-----------------------------------------------------------------------------
	// 12/02 - Folia Tropical
	//-----------------------------------------------------------------------------
	$qtd_folia_dia = $ar_grafico['qtde_folia_36'];

	$relatorio_exel[21]['dia'] = '12/02';

	$porcentagem_ft = ($qtd_folia_dia > 0) ? (($qtd_folia_dia*100) / $qtd_folia_ft) : 0;
	$relatorio_exel[21]['porcentagem'] = round($porcentagem_ft);

	$relatorio_exel[21]['tipo'] = 'Folia Tropical';
	$relatorio_exel[21]['qtd'] = $ar_grafico['qtde_folia_36'];
	$relatorio_exel[21]['valor'] = number_format($ar_grafico['valor_folia_36'], 2, ',', '.');


	//-----------------------------------------------------------------------------
	// 12/02 - Super Folia
	//-----------------------------------------------------------------------------
	$qtd_super_dia = $ar_grafico['qtde_super_36'];

	$relatorio_exel[22]['dia'] = '12/02';

	$porcentagem_s = ($qtd_super_dia > 0) ? (($qtd_super_dia*100) / $qtd_super_s) : 0;
	$relatorio_exel[22]['porcentagem'] =  round($porcentagem_s);

	$relatorio_exel[22]['tipo'] = 'Super Folia';
	$relatorio_exel[22]['qtd'] = $ar_grafico['qtde_super_36'];
	$relatorio_exel[22]['valor'] = number_format($ar_grafico['valor_super_36'], 2, ',', '.');


	//-----------------------------------------------------------------------------
	// 12/02 - Folia + Super Folia
	//-----------------------------------------------------------------------------
	$qtd_lounges_dia = $ar_grafico['qtde_lounges_36'];

	$relatorio_exel[23]['dia'] = '12/02';

	$porcentagem_l = ($qtd_lounges_dia > 0) ? (($qtd_lounges_dia*100) / $qtd_lounges_l) : 0;
	$relatorio_exel[23]['porcentagem'] = round($porcentagem_l);

	$relatorio_exel[23]['tipo'] = 'Folia + Super Folia';
	$relatorio_exel[23]['qtd'] = $ar_grafico['qtde_lounges_36'];
	$relatorio_exel[23]['valor'] = number_format($ar_grafico['valor_lounges_36'], 2, ',', '.');

	
	//-----------------------------------------------------------------------------
	// 17/02 - Arquibancadas
	//-----------------------------------------------------------------------------
	$qtd_arqui_dia = $ar_grafico['qtde_arquibancadas_37'];

	$relatorio_exel[24]['dia'] = '17/02';

	$porcentagem_f = ($qtd_arqui_dia > 0) ? (($qtd_arqui_dia*100) / $qtd_camar_c) : 0;
	$relatorio_exel[24]['porcentagem'] = round($porcentagem_f);

	$relatorio_exel[24]['tipo'] = 'Arquibancadas';
	$relatorio_exel[24]['qtd'] = $ar_grafico['qtde_arquibancadas_37'];
	$relatorio_exel[24]['valor'] = number_format($ar_grafico['valor_arquibancadas_37'], 2, ',', '.');


	//-----------------------------------------------------------------------------
	// 17/02 - Frisas
	//-----------------------------------------------------------------------------
	$qtd_frisas_dia = $ar_grafico['qtde_frisas_37'];

	$relatorio_exel[25]['dia'] = '17/02';

	$porcentagem_f = ($qtd_frisas_dia > 0) ? (($qtd_frisas_dia * 100) / $qtd_frisa_f) : 0;
	$relatorio_exel[25]['porcentagem'] = round($porcentagem_f);

	$relatorio_exel[25]['tipo'] = 'Frisas';
	$relatorio_exel[25]['qtd'] = $ar_grafico['qtde_frisas_37'];
	$relatorio_exel[25]['valor'] = number_format($ar_grafico['valor_frisas_37'], 2, ',', '.');


	//-----------------------------------------------------------------------------
	// 17/02 - Camarotes
	//-----------------------------------------------------------------------------
	$qtd_camar_dia = $ar_grafico['qtde_camarotes_37'];

	$relatorio_exel[26]['dia'] = '17/02';

	$porcentagem_ft = ($qtd_camar_dia > 0) ? (($qtd_camar_dia * 100) / $qtd_camar_c) : 0;
	$relatorio_exel[26]['porcentagem'] = round($porcentagem_ft);

	$relatorio_exel[26]['tipo'] = 'Camarotes';
	$relatorio_exel[26]['qtd'] = $ar_grafico['qtde_camarotes_37'];
	$relatorio_exel[26]['valor'] = number_format($ar_grafico['valor_camarotes_37'], 2, ',', '.');


	//-----------------------------------------------------------------------------
	// 17/02 - Folia Tropical
	//-----------------------------------------------------------------------------
	$qtd_folia_dia = $ar_grafico['qtde_folia_37'];

	$relatorio_exel[27]['dia'] = '17/02';

	$porcentagem_s = ($qtd_folia_dia > 0) ? (($qtd_folia_dia * 100) / $qtd_folia_ft) : 0;
	$relatorio_exel[27]['porcentagem'] =  round($porcentagem_s);

	$relatorio_exel[27]['tipo'] = 'Folia Tropical';
	$relatorio_exel[27]['qtd'] = $ar_grafico['qtde_folia_37'];
	$relatorio_exel[27]['valor'] = number_format($ar_grafico['valor_folia_37'], 2, ',', '.');


	//-----------------------------------------------------------------------------
	// 17/02 - Super Folia
	//-----------------------------------------------------------------------------
	$qtd_super_dia = $ar_grafico['qtde_super_37'];

	$relatorio_exel[28]['dia'] = '17/02';

	$porcentagem_s = ($qtd_super_dia > 0) ? (($qtd_super_dia * 100) / $qtd_super_s) : 0;
	$relatorio_exel[28]['porcentagem'] =  round($porcentagem_s);

	$relatorio_exel[28]['tipo'] = 'Super Folia';
	$relatorio_exel[28]['qtd'] = $ar_grafico['qtde_super_37'];
	$relatorio_exel[28]['valor'] = number_format($ar_grafico['valor_super_37'], 2, ',', '.');


	//-----------------------------------------------------------------------------
	// 17/02 - Folia + Super Folia
	//-----------------------------------------------------------------------------
	$qtd_lounges_dia = $ar_grafico['qtde_lounges_37'];

	$relatorio_exel[29]['dia'] = '17/02';

	$porcentagem_l = ($qtd_lounges_dia > 0) ? (($qtd_lounges_dia * 100) / $qtd_lounges_l) : 0;
	$relatorio_exel[29]['porcentagem'] = round($porcentagem_l);

	$relatorio_exel[29]['tipo'] = 'Folia + Super Folia';
	$relatorio_exel[29]['qtd'] = $ar_grafico['qtde_lounges_37'];
	$relatorio_exel[29]['valor'] = number_format($ar_grafico['valor_lounges_37'], 2, ',', '.');

