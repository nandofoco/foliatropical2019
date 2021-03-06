--Relatorio de Compras

DECLARE @compras TABLE(COD INT, GRUPO INT, TIPO INT, SETOR INT, DIA INT, FILA VARCHAR(255), NIVEL VARCHAR(255), NUMERO VARCHAR(255), VALOR DECIMAL(10,2), QTDE INT, ESTOQUE INT, BLOCK TINYINT);

INSERT INTO @compras (COD, GRUPO, TIPO, SETOR, DIA, FILA, NIVEL, NUMERO, VALOR, QTDE, ESTOQUE, BLOCK)
SELECT MIN(CO_COD), CO_GRUPO, CO_TIPO, CO_SETOR, CO_DIA, CO_FILA, CO_NIVEL, CO_NUMERO, CO_VALOR, COUNT(CO_COD) AS QTDE, ISNULL(SUM(CO_ESTOQUE),0) AS ESTOQUE, MAX(CO_BLOCK) FROM [foliatropical2014].[dbo].[compras]
WHERE CO_EVENTO=%evento% AND D_E_L_E_T_=0 
GROUP BY CO_GRUPO, CO_TIPO, CO_SETOR, CO_DIA, CO_FILA, CO_NIVEL, CO_NUMERO, CO_VALOR

SELECT

NUMERACAO AS 'NUMERAÇÃO',
TIPO,
DATA,
SETOR,
FILA,
NIVEL,
NUMERO AS 'NÚMERO',
QUANTIDADE,
VALOR AS 'VALOR UNITÁRIO',
(VALOR * QUANTIDADE) AS 'VALOR TOTAL'

FROM (

	SELECT 
	c.GRUPO AS NUMERACAO,
	c.FILA AS FILA,
	c.NIVEL AS NIVEL,
	c.NUMERO AS NUMERO,
	c.VALOR AS VALOR,
	CASE WHEN c.ESTOQUE > 0 THEN c.ESTOQUE ELSE c.QTDE END AS QUANTIDADE,
	--c.QTDE AS QUANTIDADE,
	c.ESTOQUE AS ESTOQUE,
	d.ED_NOME,
	CASE WHEN t.TI_NOME='Lounge' THEN 'Folia Tropical' ELSE t.TI_NOME END AS TIPO,
	CONVERT(VARCHAR, d.ED_DATA, 103) AS DATA,
	s.ES_NOME AS SETOR,
	d.ED_DATA

	FROM @compras c 
	LEFT JOIN [foliatropical2014].[dbo].[tipos] t ON t.TI_COD=c.TIPO
	LEFT JOIN [foliatropical2014].[dbo].[eventos_dias] d ON d.ED_COD=c.DIA
	LEFT JOIN [foliatropical2014].[dbo].[eventos_setores] s ON s.ES_COD=c.SETOR
	WHERE d.D_E_L_E_T_=0 AND 
	t.D_E_L_E_T_=0 AND 
	s.D_E_L_E_T_=0
	--ORDER BY s.ES_NOME ASC, t.TI_NOME ASC, d.ED_DATA ASC

) S ORDER BY SETOR ASC, TIPO ASC, ED_DATA ASC