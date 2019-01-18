SELECT
	data,
 	SUM(CASE WHEN data='12/09/2014' THEN valor_dia ELSE 0 END) AS valor_dia1,
 	SUM(CASE WHEN data='12/09/2014' THEN qtde_dia ELSE 0 END) AS qtde_dia1
	FROM (
		SELECT SUM((li.LI_VALOR_TABELA - li.LI_DESCONTO + li.LI_OVER_INTERNO) - (li.LI_VALOR_TABELA - li.LI_DESCONTO + li.LI_OVER_INTERNO) * lo.LO_COMISSAO / 100) AS valor_dia, 
		COUNT(lo.LO_COD) AS qtde_dia,
		CONVERT(VARCHAR, lo.LO_DATA_COMPRA, 103) as data
		FROM foliatropical.dbo.loja lo, foliatropical.dbo.loja_itens li, foliatropical.dbo.vendas ve 
		WHERE lo.LO_COD=li.LI_COMPRA AND li.D_E_L_E_T_='0' AND lo.LO_BLOCK='0' AND lo.D_E_L_E_T_='0' 
		AND li.LI_INGRESSO=ve.VE_COD AND ve.VE_EVENTO='2' AND ve.VE_BLOCK='0' AND ve.D_E_L_E_T_='0' 
		AND lo.LO_EVENTO='2' GROUP BY lo.LO_DATA_COMPRA
	) S
GROUP BY data

SELECT 
	SUM(CASE WHEN data='02/11/2016' THEN valor_dia ELSE 0 END) AS valor_dia1,
	SUM(CASE WHEN data='02/11/2016' THEN qtde_dia ELSE 0 END) AS qtde_dia1,
    data 
    FROM ( 
    	SELECT SUM((li.LI_VALOR_TABELA - li.LI_DESCONTO + li.LI_OVER_INTERNO) - (li.LI_VALOR_TABELA - li.LI_DESCONTO + li.LI_OVER_INTERNO) * lo.LO_COMISSAO / 100) AS valor_dia, 
    	COUNT(lo.LO_COD) AS qtde_dia, CONVERT(VARCHAR, lo.LO_DATA_COMPRA, 103) as data 
    	FROM loja lo, loja_itens li, vendas ve 
    	WHERE lo.LO_COD=li.LI_COMPRA AND li.D_E_L_E_T_='0' AND lo.LO_BLOCK='0' AND lo.D_E_L_E_T_='0' 
    	AND li.LI_INGRESSO=ve.VE_COD AND ve.VE_EVENTO='2' AND ve.VE_BLOCK='0' AND ve.D_E_L_E_T_='0' AND lo.LO_EVENTO='2' ) S GROUP BY data