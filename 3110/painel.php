<?
if (@$_SERVER["HTTP_X_CRON_AUTH"] != "3d5a01b606de5e79d6bcdaac8db316a0") {
    require_once('restrito.php');
}
?>
<?php require_once('Connections/conexao.php'); ?>
<?php
if (!function_exists("GetSQLValueString")) {
    function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "")
    {
        if (PHP_VERSION < 6) {
            $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
        }

        $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

        switch ($theType) {
            case "text":
                $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
                break;
            case "long":
            case "int":
                $theValue = ($theValue != "") ? intval($theValue) : "NULL";
                break;
            case "double":
                $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
                break;
            case "date":
                $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
                break;
            case "defined":
                $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
                break;
        }
        return $theValue;
    }
}

$currentPage = $_SERVER["PHP_SELF"];

require_once('parametros.php');
require_once('funcao_dia_util.php');
require_once('funcao_limitarTexto.php');

//region - usuario
$colname_usuario = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_usuario = $_SESSION['MM_Username'];
}
mysql_select_db($database_conexao, $conexao);
$query_usuario = sprintf("SELECT IdUsuario, nome, praca FROM usuarios WHERE usuario = %s", GetSQLValueString($colname_usuario, "text"));
$usuario = mysql_query($query_usuario, $conexao) or die(mysql_error());
$row_usuario = mysql_fetch_assoc($usuario);
$totalRows_usuario = mysql_num_rows($usuario);
//endregion - end - usuario

$where_geral = " 1=1 ";
$where_usuario_listar = " 1=1 ";
$where_filtro_usuario_responsavel = " 1=1 ";
$where_geral_tipo_praca_listar = " 1=1 ";

if(@$_SERVER["HTTP_X_CRON_AUTH"] != "3d5a01b606de5e79d6bcdaac8db316a0" and $row_usuario['praca'] <> 'MATRIZ'){

    $where_geral = " usuarios.praca = '".$row_usuario['praca']."' ";
    $where_usuario_listar = " usuarios.praca = '".$row_usuario['praca']."' ";

    $where_filtro_usuario_responsavel = " usuarios.praca = '".$row_usuario['praca']."' ";

    $where_geral_tipo_praca_listar = " geral_tipo_praca.praca = '".$colname_praca."' ";

}

//region - filtros -----------------------------------------------------------------------------------------------------------------------------------------------------------

// se existe filtro de praca
if( (isset($_GET["praca"])) && ($_GET['praca'] !="") ) {
	$colname_praca = GetSQLValueString($_GET["praca"], "string");
    $where_geral .= " and usuarios.praca = '$colname_praca' ";
    $where_usuario_listar .= " and usuarios.praca = '$colname_praca' ";
    $where_geral_tipo_praca_listar .= " and geral_tipo_praca.praca = '".$colname_praca."' ";
} 
// end - se existe filtro de praca

// se existe filtro de usuario_responsavel
if( (isset($_GET["usuario_responsavel"])) && ($_GET['usuario_responsavel'] !="") ) {
	$colname_usuario_responsavel = $_GET['usuario_responsavel'];
    $where_geral .= " and usuarios.IdUsuario = '".$colname_usuario_responsavel."' ";
    $where_usuario_listar .= " and usuarios.IdUsuario = '".$colname_usuario_responsavel."' ";
} 
// end - se existe filtro de usuario_responsavel

//endregion end - filtros -----------------------------------------------------------------------------------------------------------------------------------------------------

//region - geral_tipo_praca_listar
mysql_select_db($database_conexao, $conexao);
$query_geral_tipo_praca_listar = "
SELECT 
    geral_tipo_praca.IdPraca, 
    geral_tipo_praca.praca 

FROM 
    geral_tipo_praca 
WHERE 
    ".$where_geral_tipo_praca_listar." 
ORDER BY 
    geral_tipo_praca.praca ASC 
";
$geral_tipo_praca_listar = mysql_query($query_geral_tipo_praca_listar, $conexao) or die(mysql_error());
$row_geral_tipo_praca_listar_assoc = mysql_fetch_assoc($geral_tipo_praca_listar);
$totalRows_geral_tipo_praca_listar = mysql_num_rows($geral_tipo_praca_listar);
$row_geral_tipo_praca_listar_array = NULL;
if($totalRows_geral_tipo_praca_listar > 0){

    do {

        $row_geral_tipo_praca_listar_array[$row_geral_tipo_praca_listar_assoc['praca']] = array(
            'IdPraca' => $row_geral_tipo_praca_listar_assoc['IdPraca'],
            'praca' => $row_geral_tipo_praca_listar_assoc['praca'], 

            'usuario_contador' => 0, 

            'campos' => NULL
        );

    } while ($row_geral_tipo_praca_listar_assoc = mysql_fetch_assoc($geral_tipo_praca_listar));

}
mysql_free_result($geral_tipo_praca_listar);
//endregion - end - geral_tipo_praca_listar

//region - usuario_listar
mysql_select_db($database_conexao, $conexao);
$query_usuario_listar = "
SELECT 
    usuarios.IdUsuario, 
    usuarios.praca, 
    usuarios.nome 

FROM 
    usuarios 
WHERE 
    usuarios.status = 1 and 
    ".$where_usuario_listar." 
ORDER BY 
    usuarios.praca ASC, usuarios.nome ASC 
";
$usuario_listar = mysql_query($query_usuario_listar, $conexao) or die(mysql_error());
$row_usuario_listar_assoc = mysql_fetch_assoc($usuario_listar);
$totalRows_usuario_listar = mysql_num_rows($usuario_listar);
$row_usuario_listar_array = NULL;
if($totalRows_usuario_listar > 0){

    do {

        $row_usuario_listar_array[$row_usuario_listar_assoc['IdUsuario']] = array(
            'IdUsuario' => $row_usuario_listar_assoc['IdUsuario'],
            'praca' => $row_usuario_listar_assoc['praca'],
            'nome' => $row_usuario_listar_assoc['nome']
        );

        $row_geral_tipo_praca_listar_array[$row_usuario_listar_assoc['praca']]['usuario_contador'] ++;

    } while ($row_usuario_listar_assoc = mysql_fetch_assoc($usuario_listar));

}
mysql_free_result($usuario_listar);
//endregion - end - usuario_listar

//region - painel_listar
mysql_select_db($database_conexao, $conexao);
$query_painel_listar = "
SELECT 
    painel.*, 
    (SELECT COUNT(painel_campo.IdPainelCampo) FROM painel_campo WHERE painel_campo.IdPainel = painel.IdPainel) AS painel_campo_contador 
FROM 
    painel 
ORDER BY 
    painel.IdPainel ASC 
";
$painel_listar = mysql_query($query_painel_listar, $conexao) or die(mysql_error());
$row_painel_listar_assoc = mysql_fetch_assoc($painel_listar);
$totalRows_painel_listar = mysql_num_rows($painel_listar);
$row_painel_listar_array = NULL;
if($totalRows_painel_listar > 0){

    do {

        $row_painel_listar_array[$row_painel_listar_assoc['IdPainel']] = array(
            'painel_campo_contador' => $row_painel_listar_assoc['painel_campo_contador'],
            'IdPainel' => $row_painel_listar_assoc['IdPainel'],
            'titulo' => $row_painel_listar_assoc['titulo'],
            'modulo' => $row_painel_listar_assoc['modulo'],
            'modulo_id' => $row_painel_listar_assoc['modulo_id'],
            'query_join' => $row_painel_listar_assoc['query_join'],
            'query_where' => $row_painel_listar_assoc['query_where'],
            'query_group' => $row_painel_listar_assoc['query_group'],
            'query_having' => $row_painel_listar_assoc['query_having'],
            'query_order' => $row_painel_listar_assoc['query_order'],
            'query_geral' => $row_painel_listar_assoc['query_geral'],
        );

    } while ($row_painel_listar_assoc = mysql_fetch_assoc($painel_listar));

}
mysql_free_result($painel_listar);
//endregion - end - painel_listar

$row_painel_campo_geral_listar_array = NULL;

foreach($row_painel_listar_array AS $row_painel_listar_key => $row_painel_listar){

    if($row_painel_listar['painel_campo_contador'] > 0){

        //region - painel_campo_listar
        mysql_select_db($database_conexao, $conexao);
        $query_painel_campo_listar = "
        SELECT 
            painel_campo.* 
        FROM 
            painel_campo
        WHERE 
            painel_campo.IdPainel = '".$row_painel_listar ['IdPainel']."'
        ORDER BY 
            painel_campo.IdPainelCampo ASC 
        ";
        $painel_campo_listar = mysql_query($query_painel_campo_listar, $conexao) or die(mysql_error());
        $row_painel_campo_listar_assoc = mysql_fetch_assoc($painel_campo_listar);
        $totalRows_painel_campo_listar = mysql_num_rows($painel_campo_listar);
        $row_painel_campo_listar_array = NULL;

        if($totalRows_painel_campo_listar > 0){

            do {

                // para o modulo_listar
                $row_painel_campo_listar_array[$row_painel_campo_listar_assoc['IdPainelCampo']] = array(
                    'IdPainelCampo' => $row_painel_campo_listar_assoc['IdPainelCampo'],
                    'titulo' => $row_painel_campo_listar_assoc['titulo'], 
                    'campo' => $row_painel_campo_listar_assoc['campo'],
                    'campo_where' => $row_painel_campo_listar_assoc['campo_where'], 
                    'campo_filtro_data' => $row_painel_campo_listar_assoc['campo_filtro_data'],
                    'campo_filtro_data_padrao' => $row_painel_campo_listar_assoc['campo_filtro_data_padrao'], 
                );
                // end - para o modulo_listar

                // para a montagem da tabela
                $row_painel_campo_geral_listar_array[$row_painel_campo_listar_assoc['IdPainelCampo']] = array(
                    'IdPainelCampo' => $row_painel_campo_listar_assoc['IdPainelCampo'],
                    'IdPainel' => $row_painel_campo_listar_assoc['IdPainel'],
                    'titulo' => $row_painel_campo_listar_assoc['titulo'], 
                    'campo' => $row_painel_campo_listar_assoc['campo']
                );
                // end - para a montagem da tabela
                
                //region - zerar todos os campos
                if(count($row_usuario_listar_array) > 0){
                    foreach($row_usuario_listar_array AS $row_usuario_listar_key => $row_usuario_listar){

                        $row_usuario_listar_array[$row_usuario_listar['IdUsuario']][$row_painel_campo_listar_assoc['campo']] = 0;
                
                    }
                }
                //endregion - end - zerar todos os campos
                
                //region - zerar todos os campos
                if(count($row_geral_tipo_praca_listar_array) > 0){
                    foreach($row_geral_tipo_praca_listar_array AS $row_geral_tipo_praca_listar_key => $row_geral_tipo_praca_listar){

                        $row_geral_tipo_praca_listar_array[$row_geral_tipo_praca_listar['praca']]['totalizador'][$row_painel_campo_listar_assoc['campo']] = 0;
                
                    }
                }
                //endregion - end - zerar todos os campos

            } while ($row_painel_campo_listar_assoc = mysql_fetch_assoc($painel_campo_listar));

        }
        mysql_free_result($painel_campo_listar);
        //endregion - end - painel_campo_listar

        //region - modulo_listar
        mysql_select_db($database_conexao, $conexao);
        $query_modulo_listar = "
        SELECT ";

        foreach($row_painel_campo_listar_array AS $row_painel_campo_listar_key => $row_painel_campo_listar){

            $campo_filtro_data = NULL;
            if(
                $row_painel_campo_listar['campo_filtro_data_padrao'] <> NULL and 
                ((isset($_GET["data_inicio"]) and $_GET["data_inicio"] == NULL) or !isset($_GET["data_inicio"])) and 
                ((isset($_GET["data_fim"]) and $_GET["data_fim"] == NULL) or !isset($_GET["data_fim"]))

            ){
                $campo_filtro_data = " and ".$row_painel_campo_listar['campo_filtro_data_padrao']." ";
            } else {

                // se existe filtro de data_inicio ( somente data final )
                if( ((isset($_GET["data_fim"])) && ($_GET["data_fim"] != "")) && ($_GET["data_inicio"] == "") ) {

                        // converter data em portugues para ingles
                        if ( isset($_GET["data_fim"]) ) {
                            $data_fim_data = substr($_GET["data_fim"],0,10);
                            $data_fim_hora = " 23:59:59";
                            $data_fim = implode("-",array_reverse(explode("-",$data_fim_data))).$data_fim_hora;
                        }
                        // converter data em portugues para ingles - fim
                    
                        $colname_data_fim = GetSQLValueString($data_fim, "string");
                        $campo_filtro_data = " and ".$row_painel_campo_listar['campo_filtro_data']." <= '".$colname_data_fim."' ";
                }
                // end - se existe filtro de data_inicio ( somente data final )

                // se existe filtro de data_inicio ( somente data inicial )
                if( ((isset($_GET["data_inicio"])) && ($_GET["data_inicio"] != "")) && ($_GET["data_fim"] == "") ) {

                        // converter data em portugues para ingles
                        if ( isset($_GET["data_inicio"]) ) {
                            $data_inicio_data = substr($_GET["data_inicio"],0,10);
                            $data_inicio_hora = " 00:00:00";
                            $data_inicio = implode("-",array_reverse(explode("-",$data_inicio_data))).$data_inicio_hora;
                        }
                        // converter data em portugues para ingles - fim
                    
                        $colname_data_inicio = GetSQLValueString($data_inicio, "string");
                        $campo_filtro_data = " and ".$row_painel_campo_listar['campo_filtro_data']." >= '".$colname_data_inicio."' ";
                }
                // end - se existe filtro de data_inicio ( somente data inicial )

                // se existe filtro de data_inicio ( entre data inicial e data final )
                if( ((isset($_GET["data_inicio"])) && ($_GET["data_inicio"] != "")) && ((isset($_GET["data_fim"])) && ($_GET["data_fim"] != "")) ) {

                        // converter data em portugues para ingles
                        if ( isset($_GET["data_inicio"]) ) {
                            $data_inicio_data = substr($_GET["data_inicio"],0,10);
                            $data_inicio_hora = " 00:00:00";
                            $data_inicio = implode("-",array_reverse(explode("-",$data_inicio_data))).$data_inicio_hora;
                        }
                        // converter data em portugues para ingles - fim

                        // converter data em portugues para ingles
                        if ( isset($_GET["data_fim"]) ) {
                            $data_fim_data = substr($_GET["data_fim"],0,10);
                            $data_fim_hora = " 23:59:59";
                            $data_fim = implode("-",array_reverse(explode("-",$data_fim_data))).$data_fim_hora;
                        }
                        // converter data em portugues para ingles - fim
                    
                        $colname_data_inicio = GetSQLValueString($data_inicio, "string");
                        $colname_data_fim = GetSQLValueString($data_fim, "string");

                        $campo_filtro_data = " and ".$row_painel_campo_listar['campo_filtro_data']." between '$colname_data_inicio' and '$colname_data_fim' ";
                }
                // end - se existe filtro de data_inicio ( entre data inicial e data final )

            }


            $query_modulo_listar .= " 
            (
                COUNT(
                    if(
                        (".$row_painel_campo_listar['campo_where'].$campo_filtro_data."), ".$row_painel_listar ['modulo'].".".$row_painel_listar ['modulo_id'].", NULL
                    )
                ) 
            ) AS ".$row_painel_campo_listar['campo'].", 
            ";

        }

        $query_modulo_listar .= " usuarios.praca, ";

        $query_modulo_listar .= " usuarios.IdUsuario ";

        $query_modulo_listar .= " FROM ".$row_painel_listar ['modulo']." ";

        $query_modulo_listar .= "  ".$row_painel_listar ['query_join']." ";

        $query_modulo_listar .= "   WHERE ".$row_painel_listar ['query_where']." and ".$where_geral." ";

        $query_modulo_listar .= " ".$row_painel_listar ['query_group']." ";

        $query_modulo_listar .= " ".$row_painel_listar ['query_having']." ";

        $query_modulo_listar .= "   ORDER BY ".$row_painel_listar ['query_order']." ";

        $modulo_listar = mysql_query($query_modulo_listar, $conexao) or die(mysql_error());
        $row_modulo_listar = mysql_fetch_assoc($modulo_listar);
        $totalRows_modulo_listar = mysql_num_rows($modulo_listar);
        if($totalRows_modulo_listar > 0){

            do {

                foreach($row_painel_campo_listar_array AS $row_painel_campo_listar_key => $row_painel_campo_listar){

                    $row_usuario_listar_array[$row_modulo_listar['IdUsuario']][$row_painel_campo_listar['campo']] = $row_modulo_listar[$row_painel_campo_listar['campo']];

                    $row_painel_campo_geral_listar_array[$row_painel_campo_listar['IdPainelCampo']]['totalizador'] = 
                    $row_painel_campo_geral_listar_array[$row_painel_campo_listar['IdPainelCampo']]['totalizador'] + $row_modulo_listar[$row_painel_campo_listar['campo']]; // totalizador geral

                    $row_geral_tipo_praca_listar_array[$row_modulo_listar['praca']]['totalizador'][$row_painel_campo_listar['campo']] = 
                    $row_geral_tipo_praca_listar_array[$row_modulo_listar['praca']]['totalizador'][$row_painel_campo_listar['campo']] + $row_modulo_listar[$row_painel_campo_listar['campo']]; // totalizacao praca


                }

            } while ($row_modulo_listar = mysql_fetch_assoc($modulo_listar));

        }
        mysql_free_result($modulo_listar);
        //endregion - end - modulo_listar

    }
}

//region - filtro_praca
mysql_select_db($database_conexao, $conexao);
$query_filtro_praca = "
SELECT 
    praca 
FROM 
    geral_tipo_praca 
ORDER BY 
    praca ASC
";
$filtro_praca = mysql_query($query_filtro_praca, $conexao) or die(mysql_error());
$row_filtro_praca = mysql_fetch_assoc($filtro_praca);
$totalRows_filtro_praca = mysql_num_rows($filtro_praca);
//endregion - end - filtro_praca

//region - filtro_usuario_responsavel
mysql_select_db($database_conexao, $conexao);
$query_filtro_usuario_responsavel = "
SELECT 
    IdUsuario, nome, praca  
FROM 
    usuarios 
WHERE 
    status = 1 and 
    $where_filtro_usuario_responsavel
ORDER BY 
    nome ASC
";
$filtro_usuario_responsavel = mysql_query($query_filtro_usuario_responsavel, $conexao) or die(mysql_error());
$row_filtro_usuario_responsavel = mysql_fetch_assoc($filtro_usuario_responsavel);
$totalRows_filtro_usuario_responsavel = mysql_num_rows($filtro_usuario_responsavel);	
//endregion - end - filtro_usuario_responsavel
?>
<? if (@$_SERVER["HTTP_X_CRON_AUTH"] != "3d5a01b606de5e79d6bcdaac8db316a0") { // cronjob ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="css/suporte.css" rel="stylesheet" type="text/css">
<style>
    table.table_usuario_listar {
        font-family: Lucida Grande, Lucida Sans, Arial, sans-serif; 
        font-size: 11px;
        overflow: hidden;
    }
    table.table_usuario_listar thead th {
        padding: 7px;
        background-color: #E6F2FD;
        color: #2E6E9E;
        border: 1px solid #A6C9E2;

        position: relative;
    }
    table.table_usuario_listar tbody td {
        padding: 5px;
        border-bottom: 1px solid #A6C9E2;
        border-left: 1px solid #A6C9E2;
        border-right: 1px solid #A6C9E2;

        position: relative;
    }
    table.table_usuario_listar tfoot td {
        padding: 5px;
        border-bottom: 1px solid #A6C9E2;
        border-left: 1px solid #A6C9E2;
        border-right: 1px solid #A6C9E2;
        font-weight: bold;
    }
    table.table_usuario_listar tfoot td.tfoot_td_totalizador {
        padding: 5px;
        border-bottom: 1px solid #A6C9E2;
        border-left: 1px solid #A6C9E2;
        border-right: 1px solid #A6C9E2;
        background-color: #E6F2FD;
    }
    table.table_usuario_listar tr td.tbody_td_totalizador_praca { 
        font-weight: bold;
        background-color: #F1F1F1;
    }
    table.table_usuario_listar tr.tbody_tr_campo:hover { 
        background-color: #d0e5f5;;
        color: #1d5987;
    }

    table.table_usuario_listar tbody .tbody_tr_campo.active {
        background: #d0e5f5;
    }
</style>

<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="funcoes.js"></script>

<script type="text/javascript" src="js/jquery.metadata.js" ></script>
<script type="text/javascript" src="js/jquery.validate.js"></script>

<script type="text/javascript" src="js/jquery.maskedinput-1.2.2.min.js"></script> 
<script type="text/javascript" src="js/jquery.rsv.js"></script> 

<link rel="stylesheet" href="css/thickbox.css" type="text/css" media="screen" />
<script type="text/javascript" src="js/thickbox.js"></script>

<script type="text/javascript">
$(document).ready(function() {
	
	// mascara
	$('#data_inicio').mask('99-99-9999',{placeholder:" "});
	$('#data_fim').mask('99-99-9999',{placeholder:" "});
	// mascara - fim

	// filtro_geral_data_criacao/filtro_geral_data_criacao_fim - verifica se é uma data válida
    $('#data_inicio, #data_fim').blur(function(){

		var campo = $(this);
		
        // erro
		var erro = funcao_verifica_data_valida2(campo) // chamada da função (retorna 0/1)		
		if(erro==1){
			
			alert("Data inválida");
			campo.val('');
			setTimeout(function() {campo.focus();}, 100);
			return false;
			
		}
		// fim - erro
		
    });
	// fim - filtro_geral_data_criacao/filtro_geral_data_criacao_fim - verifica se é uma data válida
	
	// ocultar/exibir filtros
	$('#corpo_filtros').toggle();
	$('#cabecalho_filtros').click(function() {
		$('#corpo_filtros').toggle();
	});
	// end - ocultar/exibir fitlros

    $(".table_usuario_listar > tbody > .tbody_tr_campo").click(function(){

        $(this).toggleClass("active");

    });
					
});
</script>
<title>Painel</title>
</head>

<body>

    <!-- Título -->
    <div class="div_solicitacao_linhas">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td style="text-align:left">
                    Painel
                </td>

                <td style="text-align: right">
                &lt;&lt; <a href="index.php">Voltar</a> | 
                Usuário logado: <? echo $row_usuario['nome']; ?> |
                <a href="painel/padrao_sair.php">Sair</a>
                </td>
            </tr>
        </table>
    </div>
    <!-- end - Título -->

    <!-- filtros -->
    <div class="div_solicitacao_linhas" id="cabecalho_filtros" style="cursor: pointer">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td style="text-align:left">
                Filtros
                </td>
                
                <td style="text-align: right">
                <div style="float: right; background-image:url(imagens/icone_filtro.png); width: 12px; height: 12px;"></div>
                </td>
            </tr>
        </table>
    </div>

    <div style="border: 1px solid #c5dbec; margin-bottom: 5px;" id="corpo_filtros">
        <form name="buscar" action="painel.php" method="GET">

            <div class="div_filtros2">

                <table border="0" cellspacing="0" cellpadding="0">
                    <tr>

                        <? if($row_usuario['praca'] == 'MATRIZ'){ ?>
                        <td style="text-align: left; padding-right: 10px;">

                            <span class="label_solicitacao">Praça: </span>
                            <br>
                            <select name="praca">
                                <option value=""
                                <?php if (!(strcmp("", isset($_GET['praca'])))) {echo "selected=\"selected\"";} ?>
                                >
                                Escolha ...
                                </option>
                                <?php do {  ?>
                                <option value="<?php echo $row_filtro_praca['praca']?>"
                                <?php if ( (isset($_GET['praca'])) and (!(strcmp($row_filtro_praca['praca'], $_GET['praca']))) ) {echo "selected=\"selected\"";} ?>
                                >
                                <?php echo $row_filtro_praca['praca']?>
                                </option>
                                <?php
                                } while ($row_filtro_praca = mysql_fetch_assoc($filtro_praca));
                                $rows = mysql_num_rows($filtro_praca);
                                if($rows > 0) {
                                mysql_data_seek($filtro_praca, 0);
                                $row_filtro_praca = mysql_fetch_assoc($filtro_praca);
                                }
                                ?>
                            </select>

                        </td>
                        <? } ?>
                
                        <td style="text-align: left; padding-right: 10px;">
                        
                            <span class="label_solicitacao">Responsável: </span>
                            <br>
                            <select name="usuario_responsavel">
                                <option value=""
                                <?php if (!(strcmp("", isset($_GET['filtro_usuario_responsavel'])))) {echo "selected=\"selected\"";} ?>>Escolha ...</option>
                                <?php do {  ?>
                                    <option value="<?php echo $row_filtro_usuario_responsavel['IdUsuario']; ?>"
                                    <?php if ( (isset($_GET['usuario_responsavel'])) and (!(strcmp($row_filtro_usuario_responsavel['IdUsuario'], $_GET['usuario_responsavel']))) ) {echo "selected=\"selected\"";} ?>
                                    >
                                    <?php echo utf8_encode($row_filtro_usuario_responsavel['nome']); ?> • <?php echo utf8_encode($row_filtro_usuario_responsavel['praca']); ?>
                                    </option>
                                <?php
                                } while ($row_filtro_usuario_responsavel = mysql_fetch_assoc($filtro_usuario_responsavel));
                                $rows = mysql_num_rows($filtro_usuario_responsavel);
                                if($rows > 0) {
                                mysql_data_seek($filtro_usuario_responsavel, 0);
                                $row_filtro_usuario_responsavel = mysql_fetch_assoc($filtro_usuario_responsavel);
                                }
                                ?>
                            </select>

                        </td>

                        <td style="text-align: left; padding-right: 10px;">

                            <span class="label_solicitacao">Data (inicial): </span>
                            <br>
                            <input name="data_inicio" id="data_inicio" type="text" value="<? 
                            if ( isset($_GET['data_inicio']) ){ echo $_GET['data_inicio']; }
                            ?>" />

                        </td>
                        
                        <td style="text-align: left; padding-right: 10px;">

                            <span class="label_solicitacao">Data (final): </span>
                            <br>
                            <input name="data_fim" id="data_fim" type="text" value="<? 
                            if ( isset($_GET['data_fim']) ){ echo $_GET['data_fim']; }
                            ?>" />
                            
                        </tr>          

                    </tr>
                </table>

            </div>

            <div class="div_filtros">
                <table cellspacing="0" cellpadding="0" width="100%">
                    <tr>
                        <td style="text-align:left">
                        <input name="Filtrar" type="submit" value="Filtrar" class="botao_geral2" style="width: 100px" />
                        </td>
                    </tr>
                </table>
            </div> 
                    
        </form>
    </div>
    <!-- end - filtros -->

    <div class="div_solicitacao_linhas" id="cabecalho_filtros" style="cursor: pointer">
        Colaboradores (<? echo $totalRows_usuario_listar; ?>)
    </div>

    <!-- usuario_listar -->
    <table width="100%" class="table_usuario_listar" border="0" cellpadding="0" cellspacing="0">
    <? if($totalRows_usuario_listar > 0){ ?>

        <? $usuario_listar_contador = 0; ?>
        <? $usuario_listar_praca_contador = NULL; ?>
        <?php foreach($row_usuario_listar_array AS $row_usuario_listar_key => $row_usuario_listar){ ?>

            <tbody>

                <? $usuario_listar_contador = $usuario_listar_contador+1; ?>
                <? $usuario_listar_praca_contador[$row_usuario_listar['praca']] ++; ?>

                <? 
                if(
                    $usuario_listar_praca_contador[$row_usuario_listar['praca']] == 1
                ){ 
                ?>
                <thead>
                    <tr>
                        <th style="text-align: left;"><? echo $row_usuario_listar['praca']; ?></th>

                        <? foreach($row_painel_listar_array AS $row_painel_listar_key => $row_painel_listar){ ?>
                            <th width="20%" colspan="<? echo $row_painel_listar['painel_campo_contador']; ?>"><? echo utf8_encode($row_painel_listar['titulo']); ?></th>
                        <? } ?>               			
                    </tr>

                    <tr>
                        <th style="text-align: left;">Colaborador</th>

                        <? foreach($row_painel_campo_geral_listar_array AS $row_painel_campo_geral_listar_key => $row_painel_campo_geral_listar){ ?>

                            <th width="3%" class="thead_th_campo" style="text-align: center;"><? echo utf8_encode($row_painel_campo_geral_listar['titulo']); ?></th>

                        <? } ?>

                    </tr>
                </thead>
                <? } ?>

                <tr class="tbody_tr_campo">
                    
                    <td title="<? echo $row_usuario_listar['nome']; ?> (<? echo $row_usuario_listar['IdUsuario']; ?>)"><? 

                    $arr = explode(' ', $row_usuario_listar['nome']);
                    echo $arr[0];
                    if (array_key_exists(1, $arr)) {
                        echo " ".strtoupper(substr($arr[1], 0, 1)); 
                    }
                    
                    ?></td>
                    
                    <? foreach($row_painel_campo_geral_listar_array AS $row_painel_campo_geral_listar_key => $row_painel_campo_geral_listar){ ?>

                        <td class="tbody_td_campo" style="text-align: center;">
                            <a href="painel_vinculo.php?IdUsuario=<? echo $row_usuario_listar['IdUsuario']; ?>&IdPainel=<? echo $row_painel_campo_geral_listar['IdPainel']; ?>&IdPainelCampo=<? echo $row_painel_campo_geral_listar['IdPainelCampo']; ?><? if( ((isset($_GET["praca"])) && ($_GET["praca"] != "")) ) { ?>&praca=<? echo $_GET["praca"]; ?><? } ?><? if( ((isset($_GET["usuario_responsavel"])) && ($_GET["usuario_responsavel"] != "")) ) { ?>&usuario_responsavel=<? echo $_GET["usuario_responsavel"]; ?><? } ?><? if( ((isset($_GET["data_inicio"])) && ($_GET["data_inicio"] != "")) ) { ?>&data_inicio=<? echo $_GET["data_inicio"]; ?><? } ?><? if( ((isset($_GET["data_fim"])) && ($_GET["data_fim"] != "")) ) { ?>&data_fim=<? echo $_GET["data_fim"]; ?><? } ?>&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=500&width=800&modal=true" class="thickbox" style="color: black;">
                            <? echo $row_usuario_listar[$row_painel_campo_geral_listar['campo']]; ?>
                            </a>
                        </td>

                    <? } ?>

                </tr>

                <!-- totalizador - praca -->
                <? 
                if(
                    $usuario_listar_praca_contador[$row_usuario_listar['praca']] == $row_geral_tipo_praca_listar_array[$row_usuario_listar['praca']]['usuario_contador']
                ){ 
                ?>
                <tr class="tbody_tr_totalizador_praca"> 
                    <td class="tbody_td_totalizador_usuario"></td>
                    <? foreach($row_painel_campo_geral_listar_array AS $row_painel_campo_geral_listar_key => $row_painel_campo_geral_listar){ ?>
                        <td class="tbody_td_totalizador_praca" style="text-align: center;">
                            <? echo $row_geral_tipo_praca_listar_array[$row_usuario_listar['praca']]['totalizador'][$row_painel_campo_geral_listar['campo']]; ?>
                        </td>
                    <? } ?>
                </tr>
                <? } ?>
                <!-- end - totalizador - praca -->

            </tbody>

        <?php } ?>

        <? if($totalRows_geral_tipo_praca_listar > 1){ ?>
        <tfoot>
            <tr>
                <td style="text-align: left; font-weight: bold;">TOTAL</td>

                <? foreach($row_painel_campo_geral_listar_array AS $row_painel_campo_geral_listar_key => $row_painel_campo_geral_listar){ ?>
                    <td class="tfoot_td_totalizador" style="text-align: center;"><? echo $row_painel_campo_geral_listar['totalizador']; ?></td>
                <? } ?>               			
            </tr>
        
        </tfoot>
        <? } ?>

    <? } else { ?>

        <tbody>
            <tr>
                <td>Nenhum colaborador disponível.</td>
            </tr>
        </tbody>

    <? } ?>
    </table>
    <!-- fim - usuario_listar -->

</body>
</html>

<? } ?>

<? if (@$_SERVER["HTTP_X_CRON_AUTH"] == "3d5a01b606de5e79d6bcdaac8db316a0") { // cronjob ?>

    <? if(count($row_usuario_listar_array) > 0){ ?>
    <?php foreach($row_usuario_listar_array AS $row_usuario_listar_key => $row_usuario_listar){ ?>    
        <? foreach($row_painel_campo_geral_listar_array AS $row_painel_campo_geral_listar_key => $row_painel_campo_geral_listar){ ?>

            <?
            // insert - painel_historico
            $insertSQL_painel_historico = sprintf("
            INSERT INTO painel_historico (
                IdPainel,
                IdPainelCampo,
                IdUsuario,
                praca, 
                data,
                campo
            ) VALUES (
                %s, 
                %s, 
                %s, 
                %s,
                %s,  
                %s
            )",
            GetSQLValueString($row_painel_campo_geral_listar['IdPainel'], "int"), 
            GetSQLValueString($row_painel_campo_geral_listar['IdPainelCampo'], "int"), 
            GetSQLValueString($row_usuario_listar['IdUsuario'], "int"),
            GetSQLValueString($row_usuario_listar['praca'], "text"),  
            GetSQLValueString(date('Y-m-d H:i:s'), "date"), // date('Y-m-d H:i:s')
            GetSQLValueString($row_usuario_listar[$row_painel_campo_geral_listar['campo']], "int"));

            mysql_select_db($database_conexao, $conexao);
            $Result_painel_historico = mysql_query($insertSQL_painel_historico, $conexao) or die(mysql_error());
            // fim - insert - painel_historico
            ?>

        <? } ?>
    <? } ?>
    <? } ?>

    <?
    // insert - auto *****************************************************************
    echo $insertSQL_auto = sprintf("
    INSERT INTO auto (titulo, data, ip) 
    VALUES (%s, %s, %s)", 
    GetSQLValueString(basename($_SERVER['PHP_SELF']), "text"), 
    GetSQLValueString(date('Y-m-d H:i:s'), "date"),
    GetSQLValueString(basename($_SERVER["REMOTE_ADDR"]), "text"));
    mysql_select_db($database_conexao, $conexao);
    $Result_auto = mysql_query($insertSQL_auto, $conexao) or die(mysql_error());
    // fim - insert - auto ***********************************************************
    ?>

<? } ?>

<?php
mysql_free_result($filtro_praca);
mysql_free_result($filtro_usuario_responsavel);
?>