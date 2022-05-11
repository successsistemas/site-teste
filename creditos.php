<style>
.rodape{
	margin-left: auto; 
	margin-right: auto;
	background-color: #1C3451;
	padding-top: 15px;
	padding-bottom: 15px;
	line-height: 1.5em;
}
.rodape_tabela td{
	color: #FFF;
	font-size: 11px;
	height: 22px;
}
</style>
<div class="rodape">
<table width="980" border="0" cellspacing="0" cellpadding="0" align="center" class="rodape_tabela">
<tr>

    <td align="left" valign="middle" width="320">
        <div style="text-align: left; color: #FFF; margin-left: 30px;">
            <img src="imagens/rodape_logo.jpg" style="border: 0px;" />
            <? echo $row_parametros['rodape_site']; ?>
        </div>
    </td>
    
    <td align="right" valign="middle" width="1px" style="background-color: #284A75;"></td>

    <td align="left" valign="middle" width="560">
        <div style="padding-left: 30px;">       
            <?
            // site_parceiro_listar
            mysql_select_db($database_conexao, $conexao);
            $query_site_parceiro_listar = "
            SELECT 
                site_parceiro.* 
            FROM 
                site_parceiro   
            ORDER BY 
                site_parceiro.ordem IS NULL ASC, site_parceiro.ordem ASC, site_parceiro.titulo ASC
            ";
            $site_parceiro_listar = mysql_query($query_site_parceiro_listar, $conexao) or die(mysql_error());
            $row_site_parceiro_listar = mysql_fetch_assoc($site_parceiro_listar);
            $totalRows_site_parceiro_listar = mysql_num_rows($site_parceiro_listar);
            // fim - site_parceiro_listar
            ?>
            <table border="0" cellspacing="0" cellpadding="0">

                <?php do { ?>
                    <tr>
                        <td width="130"><? echo $row_site_parceiro_listar['titulo']; ?></td>
                        <td width="450"><? echo $row_site_parceiro_listar['texto']; ?></td>
                    </tr>
                <?php } while ($row_site_parceiro_listar = mysql_fetch_assoc($site_parceiro_listar)); ?>

            </table>
            <? mysql_free_result($site_parceiro_listar); ?>
		</div>
    </td>
    
    <td align="right" valign="middle" width="1px" style="background-color: #284A75;"></td>
    
    <td align="center" valign="middle">
        <div style="text-align: left; color: #FFF; text-align:center;">
        <img src="imagens/icone-facebook.png" width="32" height="32" border="0" />
        </div>
    </td>
        
    </tr>
</table>
</div>