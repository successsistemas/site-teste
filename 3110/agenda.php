<? session_start(); ?>
<?php require_once('restrito.php'); ?>
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

// usuário logado via SESSION - ok
$colname_usuario = "-1";
if (isset($_SESSION['MM_Username'])) {
    $colname_usuario = $_SESSION['MM_Username'];
}
mysql_select_db($database_conexao, $conexao);
$query_usuario = sprintf("SELECT * FROM usuarios WHERE usuario = %s", GetSQLValueString($colname_usuario, "text"));
$usuario = mysql_query($query_usuario, $conexao) or die(mysql_error());
$row_usuario = mysql_fetch_assoc($usuario);
$totalRows_usuario = mysql_num_rows($usuario);
// fim - usuário logado via SESSION

if ($praca_status == 0) {
    header("Location: painel/index.php");
    exit;
}

// filtro praca - ok
mysql_select_db($database_conexao, $conexao);
$query_filtro_praca = "SELECT praca FROM geral_tipo_praca ORDER BY praca ASC";
$filtro_praca = mysql_query($query_filtro_praca, $conexao) or die(mysql_error());
$row_filtro_praca = mysql_fetch_assoc($filtro_praca);
$totalRows_filtro_praca = mysql_num_rows($filtro_praca);
// fim - filtro praca

// filtro usuario_responsavel - ok
mysql_select_db($database_conexao, $conexao);
$query_filtro_id_usuario_responsavel = "SELECT IdUsuario, nome FROM usuarios ORDER BY nome ASC";
$filtro_id_usuario_responsavel = mysql_query($query_filtro_id_usuario_responsavel, $conexao) or die(mysql_error());
$row_filtro_id_usuario_responsavel = mysql_fetch_assoc($filtro_id_usuario_responsavel);
$totalRows_filtro_id_usuario_responsavel = mysql_num_rows($filtro_id_usuario_responsavel);
// fim - filtro usuario_responsavel

mysql_select_db($database_conexao, $conexao);

$where = "1=1";

//region - se padrão ---------------------------------------------------------------------------------------------------------------------------------------------------------
if (isset($_GET['padrao']) && ($_GET['padrao'] == "sim")) {
}
//endregion - fim - se padrão ---------------------------------------------------------------------------------------------------------------------------------------------------


$where_usuario_logado = $where; // para o filtro por id (elimina todos os outros filtros)


//region - agenda - filtros --------------------------------------------------------------------------------------------------------------------------------------------
// se existe filtro de empresa
if ((isset($_GET["empresa"])) && ($_GET['empresa'] != "")) {
    $colname_agenda_empresa = GetSQLValueString($_GET["empresa"], "string");
    $where .= " 
    and 
    (
        case 
        when suporte.id IS NOT NULL then suporte.empresa LIKE '%$colname_agenda_empresa%' 
        when prospeccao.id IS NOT NULL then prospeccao.nome_razao_social LIKE '%$colname_agenda_empresa%' 
        when venda.id IS NOT NULL then venda.empresa LIKE '%$colname_agenda_empresa%' 
        else NULL end 
    ) 
    ";
    $where_campos[] = "empresa";
}
// fim - se existe filtro de empresa

// se existe filtro de praca
if ((isset($_GET["praca"])) && ($_GET['praca'] != "")) {
    $colname_agenda_praca = GetSQLValueString($_GET["praca"], "string");
    $where .= " 
    and 
    (
        case 
        when suporte.id IS NOT NULL then suporte.praca = '$colname_agenda_praca' 
        when prospeccao.id IS NOT NULL then prospeccao.praca = '$colname_agenda_praca' 
        when venda.id IS NOT NULL then venda.praca = '$colname_agenda_praca' 
        else NULL end 
    ) 
    ";
    $where_campos[] = "praca";
}
// fim - se existe filtro de praca

// se existe filtro de usuario_responsavel
if ((isset($_GET["id_usuario_responsavel"])) && ($_GET['id_usuario_responsavel'] != "")) {
    $colname_agenda_usuario_responsavel = $_GET['id_usuario_responsavel'];
    $where .= " and usuarios.IdUsuario = '$colname_agenda_usuario_responsavel' ";
    $where_campos[] = "usuario_responsavel";
}
// fim - se existe filtro de usuario_responsavel

// se existe filtro de id
if ((isset($_GET["id"])) && ($_GET['id'] != "")) {
    $colname_agenda_id = GetSQLValueString($_GET["id"], "int");
    $where = $where_usuario_logado . " and agenda.id = '$colname_agenda_id' ";
    $where_campos[] = "id";
}
// fim - se existe filtro de id

// se existe filtro de status
$contador_status = 0;
$contador_status_atual = 0;
if ((isset($_GET["status"])) && ($_GET['status'] != "")) {

    // contar quantidade de situacões atual
    foreach ($_GET["status"] as $status) {
        $contador_status = $contador_status + 1;
    }
    // fim - contar quantidade de situacões atual

    $query_status = " and ( ";
    foreach ($_GET["status"] as $status) {
        $contador_status_atual = $contador_status_atual + 1; // verifica o contador atual
        $contador_total = $contador_status - $contador_status_atual; // calcula diferença de situações total - situação atual
        if ($contador_total <> 0) {
            $or = " or ";
        } else {
            $or = "";
        } // se não é a última, então insere OR
        $query_status .= sprintf(" agenda.status = '$status' $or");
    }
    $where .= sprintf($query_status) . " ) ";
    $where_campos[] = "status";
}
// fim - se existe filtro de status

// se existe filtro de situacao
$contador_situacao = 0;
$contador_situacao_atual = 0;
if ((isset($_GET["situacao"])) && ($_GET['situacao'] != "")) {

    // contar quantidade de situacões atual
    foreach ($_GET["situacao"] as $situacao) {
        $contador_situacao = $contador_situacao + 1;
    }
    // fim - contar quantidade de situacões atual

    $query_situacao = " and ( ";
    foreach ($_GET["situacao"] as $situacao) {
        $contador_situacao_atual = $contador_situacao_atual + 1; // verifica o contador atual
        $contador_total = $contador_situacao - $contador_situacao_atual; // calcula diferença de situações total - situação atual
        if ($contador_total <> 0) {
            $or = " or ";
        } else {
            $or = "";
        } // se não é a última, então insere OR
        $query_situacao .= sprintf(" suporte.situacao = '$situacao' $or");
    }
    $where .= sprintf($query_situacao) . " ) ";
    $where_campos[] = "situacao";
}
// fim - se existe filtro de situacao


// se existe filtro de data ( somente data final )
if (((isset($_GET["data_fim"])) && ($_GET["data_fim"] != "")) && ($_GET["data_inicio"] == "")) {

    // converter data em portugues para ingles
    if (isset($_GET["data_fim"])) {
        $data_fim_data = substr($_GET["data_fim"], 0, 10);
        $data_fim_hora = " 23:59:59";
        $data_fim = implode("-", array_reverse(explode("-", $data_fim_data))) . $data_fim_hora;
        $where_campos[] = "data_fim";
    }
    // converter data em portugues para ingles - fim

    $colname_agenda_data_fim = GetSQLValueString($data_fim, "string");
    $where .= " and agenda.data <= '" . $colname_agenda_data_fim . "' ";
}
// fim - se existe filtro de data ( somente data final )

// se existe filtro de data ( somente data inicial )
if (((isset($_GET["data_inicio"])) && ($_GET["data_inicio"] != "")) && ($_GET["data_fim"] == "")) {

    // converter data em portugues para ingles
    if (isset($_GET["data_inicio"])) {
        $data_inicio_data = substr($_GET["data_inicio"], 0, 10);
        $data_inicio_hora = " 00:00:00";
        $data_inicio = implode("-", array_reverse(explode("-", $data_inicio_data))) . $data_inicio_hora;
        $where_campos[] = "data_inicio";
    }
    // converter data em portugues para ingles - fim

    $colname_agenda_data_inicio = GetSQLValueString($data_inicio, "string");
    $where .= " and agenda.data >= '" . $colname_agenda_data_inicio . "' ";
}
// fim - se existe filtro de data ( somente data inicial )

// se existe filtro de data ( entre data inicial e data final )
if (((isset($_GET["data_inicio"])) && ($_GET["data_inicio"] != "")) && ((isset($_GET["data_fim"])) && ($_GET["data_fim"] != ""))) {

    // converter data em portugues para ingles
    if (isset($_GET["data_inicio"])) {
        $data_inicio_data = substr($_GET["data_inicio"], 0, 10);
        $data_inicio_hora = " 00:00:00";
        $data_inicio = implode("-", array_reverse(explode("-", $data_inicio_data))) . $data_inicio_hora;
        $where_campos[] = "data_inicio";
    }
    // converter data em portugues para ingles - fim

    // converter data em portugues para ingles
    if (isset($_GET["data_fim"])) {
        $data_fim_data = substr($_GET["data_fim"], 0, 10);
        $data_fim_hora = " 23:59:59";
        $data_fim = implode("-", array_reverse(explode("-", $data_fim_data))) . $data_fim_hora;
        $where_campos[] = "data_fim";
    }
    // converter data em portugues para ingles - fim

    $colname_agenda_data_inicio = GetSQLValueString($data_inicio, "string");
    $colname_agenda_data_fim = GetSQLValueString($data_fim, "string");

    $where .= " and agenda.data between '$colname_agenda_data_inicio' and '$colname_agenda_data_fim' ";
}
// fim - se existe filtro de data ( entre data inicial e data final )
//endregion - fim - agenda - filtros --------------------------------------------------------------------------------------------------------------------------------------

//region - agenda
$query_agenda1 = "SET SESSION SQL_BIG_SELECTS=1";
$agenda1 = mysql_query($query_agenda1, $conexao) or die(mysql_error());

$query_agenda = "
SELECT 
    agenda.id_agenda, agenda.data, agenda.data_inicio, agenda.id_usuario_responsavel, agenda.descricao, 

    suporte.id AS suporte_id,
    suporte.empresa AS suporte_empresa, 
    suporte.data_suporte AS suporte_data_suporte, 

    prospeccao.id AS prospeccao_id,
    prospeccao.nome_razao_social AS prospeccao_nome_razao_social, 
    prospeccao.data_prospeccao AS prospeccao_data_prospeccao, 

    venda.id AS venda_id,  
    venda.empresa AS venda_empresa, 
    venda.data_venda AS venda_data_venda, 

    usuarios.nome AS usuarios_nome,

    (
        case 
        when suporte.id IS NOT NULL then suporte.praca  
        when prospeccao.id IS NOT NULL then prospeccao.praca 
        when venda.id IS NOT NULL then venda.praca  
        else NULL end 
    ) As praca, 

    (
        case 
        when suporte.id IS NOT NULL then suporte.empresa 
        when prospeccao.id IS NOT NULL then prospeccao.nome_razao_social  
        when venda.id IS NOT NULL then venda.empresa 
        else NULL end 
    ) as empresa, 

    (
        case 
        when suporte.id IS NOT NULL then suporte.prioridade 
        when prospeccao.id IS NOT NULL then (
            case 
            when prospeccao.nivel_interesse = 'n' then 'Nenhum' 
            when prospeccao.nivel_interesse = 'b' then 'Baixo' 
            when prospeccao.nivel_interesse = 'm' then 'Médio' 
            when prospeccao.nivel_interesse = 'a' then 'Alto' 
            else NULL end
            ) 
        when venda.id IS NOT NULL then NULL 
        else NULL end 
    ) as prioridade_label, 

    (
        case 
        when suporte.id IS NOT NULL then suporte.situacao 
        when prospeccao.id IS NOT NULL then prospeccao.situacao  
        when venda.id IS NOT NULL then venda.situacao  
        else NULL end 
    ) as situacao_label 

FROM 
    agenda 

LEFT JOIN 
    suporte ON (suporte.id = agenda.id_suporte)
LEFT JOIN 
    prospeccao ON (prospeccao.id = agenda.id_prospeccao)
LEFT JOIN 
    venda ON (venda.id = agenda.id_venda_treinamento or venda.id = agenda.id_venda_implantacao)
LEFT JOIN 
    usuarios ON agenda.id_usuario_responsavel = usuarios.IdUsuario 

WHERE 
    $where and 
    (agenda.status = 'a' or agenda.status = 'g')

ORDER BY 
    agenda.data_inicio ASC, agenda.data ASC
";

$agenda = mysql_query($query_agenda, $conexao) or die(mysql_error());
$row_agenda = mysql_fetch_assoc($agenda);
$totalRows_agenda = mysql_num_rows($agenda);
//endregion - fim - agenda
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="css/suporte.css" rel="stylesheet" type="text/css">
<style>
    body {
        overflow-y: scroll;
        /* se não é IE, então mostra a scroll vertical */
    }

    label.error {
        color: red;
        display: none;
        /* erro de validação */
    }

    #empresa_dados {
        padding: 5px;
        font-weight: normal;
        font-family: Lucida Grande, Lucida Sans, Arial, sans-serif;
        font-size: 12px;
    }

    .cliente_css {
        border: 1px solid #CCC;
        margin: 0px;
        padding: 5px;
    }

    .cliente_buscar_css {
        border: 1px solid #CCC;
        margin-top: 0px;
        margin-left: 0px;
        margin-right: 0px;
        margin-bottom: 5px;
        padding: 5px;
    }

    .cor_black {
        padding: 1px;
    }

    .cor_orange {
        color: #FF9900;
            !important;
        padding: 1px;
            !important;
    }

    .cor_red {
        color: #FF0000;
            !important;
        padding: 1px;
            !important;
    }

    .cor_blue {
        color: blue;
            !important;
        padding: 1px;
            !important;
    }

    .cor_green {
        color: green;
            !important;
        padding: 1px;
            !important;
    }

    .ui-jqgrid .ui-jqgrid-btable {
        table-layout: auto;
    }
</style>

<script type="text/javascript" src="js/jquery.js"></script>

<script type="text/javascript" src="js/jquery.metadata.js"></script>
<script type="text/javascript" src="js/jquery.validate.js"></script>

<script type="text/javascript" src="js/jquery.maskedinput-1.2.2.min.js"></script>
<script type="text/javascript" src="js/jquery.rsv.js"></script>

<link rel="stylesheet" href="css/thickbox.css" type="text/css" media="screen" />
<script type="text/javascript" src="js/thickbox.js"></script>

<link rel="stylesheet" type="text/css" media="screen" href="css/jquery-ui.css" />
<link rel="stylesheet" type="text/css" media="screen" href="css/ui.jqgrid.css" />
<script src="js/grid.locale-pt-br.js" type="text/javascript"></script>
<script src="js/jquery.jqGrid.src.js" type="text/javascript"></script>

<script type="text/javascript">
$.jgrid.no_legacy_api = true;
$.metadata.setType("attr", "validate");

$(document).ready(function() {
    //$(document).ready(function() {

    // mascara
    $('#data_inicio').mask('99-99-9999', {
        placeholder: " "
    });
    $('#data_fim').mask('99-99-9999', {
        placeholder: " "
    });
    // mascara - fim

    // ocultar/exibir filtros
    $('#corpo_agenda_filtro').toggle();
    $('#cabecalho_agenda_filtro').click(function() {
        $('#corpo_agenda_filtro').toggle();
    });
    // fim - ocultar/exibir fitlros

    // ocultar/exibir agendas
    //$('#corpo_agendas').toggle();
    $('#cabecalho_agenda').click(function() {
        $('#corpo_agenda').toggle();
    });
    // fim - ocultar/exibir agendas

    // marcar todos
    $('#checkall_situacao').click(function() {
        $(this).parents('fieldset:eq(0)').find(':checkbox').attr('checked', this.checked);
    });

    $('#checkall_status').click(function() {
        $(this).parents('fieldset:eq(0)').find(':checkbox').attr('checked', this.checked);
    });
    // fim - marcar todos

});

// limpar formulário do filtro
function clear_form_elements(ele) {

    $(ele).find(':input').each(function() {
        switch (this.type) {
            case 'password':
            case 'select-multiple':
            case 'select-one':
            case 'text':
            case 'textarea':
                $(this).val('');
                break;
            case 'checkbox':
            case 'radio':
                this.checked = false;
        }
    });

}
// fim - limpar formulário do filtro
</script>
<title>Agenda</title>
</head>

<body>
<? // echo $where; ?>

<!-- barra superior -->
<div class="div_solicitacao_linhas">
    <table cellspacing="0" cellpadding="0" width="100%">
        <tr>
            <td style="text-align:left">
                Agenda
                <font color="#3399CC"> | </font>
                <a href="solicitacao.php?padrao=sim&<? echo $situacao_padrao; ?>" style="color: #D1E3F1">Controle de solicitação</a>
                <font color="#3399CC"> | </font>
                <a href="suporte.php?padrao=sim&<? echo $suporte_padrao; ?>" style="color: #D1E3F1">Controle de suporte</a>
                <font color="#3399CC"> | </font>
                <a href="prospeccao.php?padrao=sim&<? echo $prospeccao_padrao; ?>" style="color: #D1E3F1">Controle de prospecção</a>
                <font color="#3399CC"> | </font>
                <a href="agenda.php?padrao=sim&<? echo $agenda_padrao; ?>" style="color: #D1E3F1">Controle de agendas</a>
            </td>

            <td style="text-align: right">
                &lt;&lt; <a href="index.php">Voltar</a> |
                Usuário logado:
                <? echo $row_usuario['nome']; ?> |
                <a href="painel/padrao_sair.php">Sair</a>
            </td>
        </tr>
    </table>
</div>
<!-- fim - barra superior -->

<div class="div_solicitacao_linhas2">
    Clique sobre a opção desejada para visualizar mais informações.
</div>

<!-- filtros -->
<div class="div_solicitacao_linhas" id="cabecalho_agenda_filtro" style="cursor: pointer">
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

<form name="buscar" action="agenda.php" method="GET">
    <div id="corpo_agenda_filtro">

        <!-- filtros da agenda -->
        <div style="border: 1px solid #c5dbec; margin-bottom: 5px;">

            <div class="div_filtros2">
                <table cellspacing="0" cellpadding="0" width="100%">
                    <tr>
                        <td style="text-align:left" width="560px">
                            <span class="label_solicitacao">Cliente:</span>
                            <input name="empresa" type="text" id="empresa" value="<? if ( isset($_GET['empresa']) ) { echo $_GET['empresa']; } ?>" style="width: 500px" />
                        </td>

                        <td style="text-align: right">
                            <span class="label_solicitacao">Praça: </span>
                            <select name="praca">
                                <option value="" <?php if (!(strcmp("", isset($_GET['praca'])))) {
                                                        echo "selected=\"selected\"";
                                                    } ?>>
                                    Escolha ...
                                </option>
                                <?php do {  ?>
                                    <option value="<?php echo $row_filtro_praca['praca'] ?>" <?php if ((isset($_GET['praca'])) and (!(strcmp($row_filtro_praca['praca'], $_GET['praca'])))) {
                                                                                                echo "selected=\"selected\"";
                                                                                            } ?>>
                                        <?php echo $row_filtro_praca['praca'] ?>
                                    </option>
                                <?php
                                } while ($row_filtro_praca = mysql_fetch_assoc($filtro_praca));
                                $rows = mysql_num_rows($filtro_praca);
                                if ($rows > 0) {
                                    mysql_data_seek($filtro_praca, 0);
                                    $row_filtro_praca = mysql_fetch_assoc($filtro_praca);
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="div_filtros">
                <table cellspacing="0" cellpadding="0" width="100%">
                    <tr>
                        <td style="text-align:left" width="560px">
                            <span class="label_solicitacao">Solicitante: </span>
                            <input name="solicitante" type="text" id="solicitante" value="<? if ( isset($_GET['solicitante']) ) { echo $_GET['solicitante']; } ?>" style="width: 470px" />
                        </td>

                        <td style="text-align:right">
                            <span class="label_solicitacao">Responsável: </span>
                            <select name="id_usuario_responsavel" style="width: 380px">
                                <option value="" <?php if (!(strcmp("", isset($_GET['filtro_id_usuario_responsavel'])))) {
                                                        echo "selected=\"selected\"";
                                                    } ?>>Escolha ...</option>
                                <?php do {  ?>
                                    <option value="<?php echo $row_filtro_id_usuario_responsavel['IdUsuario']; ?>" <?php if ((isset($_GET['id_usuario_responsavel'])) and (!(strcmp($row_filtro_id_usuario_responsavel['IdUsuario'], $_GET['id_usuario_responsavel'])))) {
                                                                                                                        echo "selected=\"selected\"";
                                                                                                                    } ?>>
                                        <?php echo utf8_encode($row_filtro_id_usuario_responsavel['nome']); ?>
                                    </option>
                                <?php
                                } while ($row_filtro_id_usuario_responsavel = mysql_fetch_assoc($filtro_id_usuario_responsavel));
                                $rows = mysql_num_rows($filtro_id_usuario_responsavel);
                                if ($rows > 0) {
                                    mysql_data_seek($filtro_id_usuario_responsavel, 0);
                                    $row_filtro_id_usuario_responsavel = mysql_fetch_assoc($filtro_id_usuario_responsavel);
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="div_filtros2">
                <table cellspacing="0" cellpadding="0" width="100%">
                    <tr>
                        <td style="text-align:left">
                            <span class="label_solicitacao">Data criação (inicial): </span>
                            <input name="data_inicio" id="data_inicio" type="text" value="<? 
                            if ( isset($_GET['data_inicio']) ){ echo $_GET['data_inicio']; }
                            ?>" />
                        </td>

                        <td style="text-align:right" class="div_filtros_agendas_corpo_td">
                            <span class="label_solicitacao">Data criação (final): </span>
                            <input name="data_fim" id="data_fim" type="text" value="<? 
                            if ( isset($_GET['data_fim']) ){ echo $_GET['data_fim']; }
                            ?>" />
                        </td>
                    </tr>
                </table>
            </div>

            <div class="div_filtros">
                <table cellspacing="0" cellpadding="0" width="100%">
                    <tr>
                        <td style="text-align:left">
                            <fieldset style="border: 0px;">
                                <span class="label_solicitacao">Situação:</span>

                                <input name="situacao[]" type="checkbox" value="criada" />criada
                                <input name="situacao[]" type="checkbox" value="analisada" />analisada
                                <input name="situacao[]" type="checkbox" value="em execução" />em execução
                                <input name="situacao[]" type="checkbox" value="em validação" />em validação
                                <input name="situacao[]" type="checkbox" value="solicitado suporte" />solicitado suporte
                                <input name="situacao[]" type="checkbox" value="solicitado visita" />solicitado visita
                                <input name="situacao[]" type="checkbox" value="encaminhado para solicitação" />encaminhado para solicitação
                                <input name="situacao[]" type="checkbox" value="solucionada" />solucionada
                                <input name="situacao[]" type="checkbox" value="cancelada" />cancelada

                                <input name="situacao[]" type="checkbox" value="em negociação" />em negociação
                                <input name="situacao[]" type="checkbox" value="solicitado agendamento" />solicitado agendamento
                                <input name="situacao[]" type="checkbox" value="venda realizada" />venda realizada
                                <input name="situacao[]" type="checkbox" value="venda perdida" />venda perdida

                                <input name="situacao[]" type="checkbox" value="documentação pendente" />documentação pendente

                                <input type="checkbox" id="checkall_situacao" name="checkall_situacao" />Marcar todos
                            </fieldset>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="div_filtros2">
                <table cellspacing="0" cellpadding="0" width="100%">
                    <tr>
                        <td style="text-align:left" valign="top">
                            <fieldset>
                                <span class="label_solicitacao">Status:</span>
                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td width="250" valign="top">


                                            <input name="status[]" type="checkbox" class="checkbox" value="pendente operador" <? // verificar se foi selecionada if(isset($_GET['status'])){ foreach($_GET["status"] as $status){ if($status=="pendente operador" ){ echo "checked=\" checked\""; } } } // verificar se foi selecionada ?>
                                            />pendente operador

                                            <br>

                                            <input name="status[]" type="checkbox" class="checkbox" value="pendente usuario responsavel" <? // verificar se foi selecionada if(isset($_GET['status'])){ foreach($_GET["status"] as $status){ if($status=="pendente usuario responsavel" ){ echo "checked=\" checked\""; } } } // verificar se foi selecionada ?>
                                            />pendente usuario responsavel

                                            <br>

                                            <input name="status[]" type="checkbox" class="checkbox" value="pendente controlador de agenda" <? // verificar se foi selecionada if(isset($_GET['status'])){ foreach($_GET["status"] as $status){ if($status=="pendente controlador de agenda" ){ echo "checked=\" checked\""; } } } // verificar se foi selecionada ?>
                                            />pendente controlador de agenda

                                        </td>

                                        <td valign="top">

                                            <input name="status[]" type="checkbox" class="checkbox" value="encaminhada para operador" <? // verificar se foi selecionada if(isset($_GET['status'])){ foreach($_GET["status"] as $status){ if($status=="encaminhada para operador" ){ echo "checked=\" checked\""; } } } // verificar se foi selecionada ?>
                                            />encaminhada para operador

                                            <br>

                                            <input name="status[]" type="checkbox" class="checkbox" value="encaminhada para usuario responsavel" <? // verificar se foi selecionada if(isset($_GET['status'])){ foreach($_GET["status"] as $status){ if($status=="encaminhada para usuario responsavel" ){ echo "checked=\" checked\""; } } } // verificar se foi selecionada ?>
                                            />encaminhada para usuario responsavel

                                            <br>

                                            <input type="checkbox" class="checkbox" id="checkall_status" name="checkall_status" />Marcar todos

                                        </td>
                                    </tr>
                                </table>
                            </fieldset>
                        </td>

                        <td style="text-align: right" valign="top">&nbsp;</td>

                    </tr>
                </table>
            </div>

        </div>
        <!-- fim - filtros da agenda -->

        <div class="div_filtros">
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td style="text-align:left">
                        <input name="Filtrar" type="submit" value="Filtrar" class="botao_geral2" style="width: 100px" />
                        <input onclick="clear_form_elements(this.form)" type="button" value="Limpar filtro" class="botao_geral2" style="width: 100px" />
                    </td>
                </tr>
            </table>
        </div>


    </div>
</form>
<!-- fim - filtros -->


<!-- agenda -->
<? if($totalRows_agenda > 0){ ?>

<div class="div_solicitacao_linhas" id="cabecalho_agenda" style="cursor: pointer">
    <table cellspacing="0" cellpadding="0" width="100%">
        <tr>
            <td style="text-align:left">
                Agenda (
                <? echo $totalRows_agenda; ?>)
            </td>

            <td style="text-align: right">
                <div style="float: right; background-image:url(imagens/icone_filtro.png); width: 12px; height: 12px;"></div>
            </td>
        </tr>
    </table>
</div>

<div id="corpo_agenda" style="cursor: pointer">
    <table id="agenda"></table>
    <div id="agenda_navegacao"></div>
    <script type="text/javascript">
        var data_atual_segundos = Math.round(new Date(<?php echo time() * 1000 ?>).getTime() / 1000);
        var dados = [
            <?php do { ?>

                {
                    id: "<?php
                            if ($row_agenda['suporte_id'] != NULL) {
                                echo $row_agenda['suporte_id'];
                            } else if ($row_agenda['prospeccao_id'] != NULL) {
                                echo $row_agenda['prospeccao_id'];
                            } else if ($row_agenda['venda_id'] != NULL) {
                                echo $row_agenda['venda_id'];
                            } else {
                                echo $row_agenda['id_agenda'];
                            }
                            ?>",
                    data_modulo: "<?php
                                    if ($row_agenda['suporte_id'] != NULL) {
                                        echo $row_agenda['suporte_data_suporte'];
                                    } else if ($row_agenda['prospeccao_id'] != NULL) {
                                        echo $row_agenda['prospeccao_data_prospeccao'];
                                    } else if ($row_agenda['venda_data_venda'] != NULL) {
                                        echo $row_agenda['venda_data_venda'];
                                    }
                                    ?>",
                    data_inicio: "<?php echo $row_agenda['data_inicio']; ?>",
                    hora_inicio: "<?php echo $row_agenda['data_inicio']; ?>",
                    data: "<?php echo $row_agenda['data']; ?>",
                    usuario_responsavel: "<?php echo $row_agenda['usuarios_nome']; ?>",
                    empresa: "<?php
                                if ($row_agenda['suporte_id'] != NULL) {
                                    echo utf8_encode($row_agenda['suporte_empresa']);
                                } else if ($row_agenda['prospeccao_id'] != NULL) {
                                    echo $row_agenda['prospeccao_nome_razao_social'];
                                } else if ($row_agenda['venda_id'] != NULL) {
                                    echo $row_agenda['venda_empresa'];
                                }
                                ?>",
                    descricao: "<?php echo preg_replace('/\s+/', ' ', trim($row_agenda['descricao'])); ?>",
                    modulo: "<?php
                                if ($row_agenda['suporte_id'] != NULL) {
                                    echo 'Suporte';
                                } else if ($row_agenda['prospeccao_id'] != NULL) {
                                    echo 'Prospecção';
                                } else if ($row_agenda['venda_id'] != NULL) {
                                    echo 'Venda';
                                }
                                ?>",
                    prioridade: "<? echo $row_agenda['prioridade_label']; ?>",
                    situacao: "<? echo $row_agenda['situacao_label']; ?>",
                    praca: "<? echo $row_agenda['praca']; ?>",
                    visualizar: "<?php
                                    if ($row_agenda['suporte_id'] != NULL) {
                                        echo "<a href='suporte_editar.php?id_suporte=" . $row_agenda['suporte_id'] . "&padrao=sim' target='_blank'><img src='imagens/visualizar.png' border='0' /></a>";
                                    } else if ($row_agenda['prospeccao_id'] != NULL) {
                                        echo "<a href='prospeccao_editar.php?id_prospeccao=" . $row_agenda['prospeccao_id'] . "&padrao=sim' target='_blank'><img src='imagens/visualizar.png' border='0' /></a>";
                                    } else if ($row_agenda['venda_id'] != NULL) {
                                        echo "<a href='venda_editar.php?id_venda=" . $row_agenda['venda_id'] . "&padrao=sim' target='_blank'><img src='imagens/visualizar.png' border='0' /></a>";
                                    }
                                    ?>"
                },

            <?php } while ($row_agenda = mysql_fetch_assoc($agenda)); ?>
        ];
        jQuery('#agenda').jqGrid({
            data: dados,
            datatype: 'local',
            colNames: ['Núm', 'Emissão', 'Previsão', 'Início', 'Fim', 'Responsável', 'empresa', 'Descrição', 'Módulo', 'Prioridade', 'Situação', 'Praça', ''],
            colModel: [{
                    name: 'id',
                    index: 'id',
                    width: 60,
                    sorttype: 'integer'
                }, // 40
                {
                    name: 'data_modulo',
                    index: 'data_modulo',
                    width: 70,
                    sorttype: 'date',
                    formatter: 'date',
                    formatoptions: {
                        srcformat: "ISO8601Long",
                        newformat: "d-m-Y"
                    },
                    align: 'center'
                },
                {
                    name: 'data_inicio',
                    index: 'data_inicio',
                    width: 70,
                    sorttype: 'date',
                    formatter: 'date',
                    formatoptions: {
                        srcformat: "ISO8601Long",
                        newformat: "d-m-Y"
                    },
                    align: 'center'
                },
                {
                    name: 'hora_inicio',
                    index: 'hora_inicio',
                    width: 55,
                    sorttype: 'date',
                    formatter: 'date',
                    formatoptions: {
                        srcformat: "ISO8601Long",
                        newformat: "H:i"
                    },
                    align: 'center'
                },
                {
                    name: 'data',
                    index: 'data',
                    width: 55,
                    sorttype: 'date',
                    formatter: 'date',
                    formatoptions: {
                        srcformat: "ISO8601Long",
                        newformat: "H:i"
                    },
                    align: 'center'
                },
                {
                    name: 'usuario_responsavel',
                    index: 'usuario_responsavel',
                    width: 100,
                    align: 'center'
                }, // 80
                {
                    name: 'empresa',
                    index: 'empresa',
                    width: 200,
                    align: 'left'
                },
                {
                    name: 'descricao',
                    width: 220,
                    align: 'left'
                },
                {
                    name: 'modulo',
                    width: 70,
                    align: 'left'
                },
                {
                    name: 'prioridade',
                    width: 70,
                    align: 'left'
                },
                {
                    name: 'situacao',
                    width: 70,
                    align: 'left'
                },
                {
                    name: 'praca',
                    width: 70,
                    align: 'left'
                },
                {
                    name: 'visualizar',
                    width: 20,
                    align: 'center'
                }
            ],
            rowNum: 20,
            rowList: [2, 5, 10, 20, 30, 40, 50, 100, 999999],
            loadComplete: function() {
                $("option[value=999999]").text('Todos');
            },
            pager: '#agenda_navegacao',
            toppager: true, // aparecer a barra de navegação também no topo
            multiSort: true,
            //sortname: 'data_inicio, data',
            //sortorder: 'asc',
            viewrecords: true,
            height: "100%",
            autowidth: true,
            gridview: false,
            // cores/atrasos
            afterInsertRow: function(rowid, rowdata, rowelem) {

                // data
                var data = rowdata['data'];
                var quebraDI = data.split("-");
                var anoDI = quebraDI[0];
                var mesDI = quebraDI[1] - 1;
                var diaDI = quebraDI[2].substr(0, 2);
                var time_inicial = quebraDI[2].substr(3, 8);
                var quebraTimeDI = time_inicial.split(":");
                var horaDI = quebraTimeDI[0];
                var minutoDI = quebraTimeDI[1];
                var segundoDI = quebraTimeDI[2];
                var data = new Date(anoDI, mesDI, diaDI, horaDI, minutoDI, segundoDI, 00);
                // fim - data

                var data_segundos = Math.round(data.getTime() / 1000);
                var diferenca = (data_atual_segundos - data_segundos);

                if (diferenca >= 86400 && diferenca < 172800) {
                    $(this).jqGrid('setRowData', rowid, false, {
                        color: 'orange'
                    });
                } else if (diferenca >= 172800) {
                    $(this).jqGrid('setRowData', rowid, false, {
                        color: 'red'
                    });
                } else {
                    $(this).jqGrid('setRowData', rowid, false, {
                        color: 'black'
                    });
                }

            }
            // fim - cores/atrasos
        });
    </script>
</div>

<? } else { ?>

<div class="div_solicitacao_linhas4">
    Nenhuma agenda encontrada na filtragem atual.
</div>

<? } ?>
<!-- fim - agenda -->


<!-- barra inferior -->
<div class="div_solicitacao_linhas4" style="margin-top: 5px;">
    <table cellspacing="0" cellpadding="0" width="100%">
        <tr>
            <td style="text-align:left">

                <!-- Gerar relatório -->
                <? if($totalRows_agenda > "0") { // caso seja encontrada alguma agenda com os filtros atuais ?>
                <a href="#TB_inline?height=<? echo $agenda_editar_tabela_height; ?>&width=<? echo $agenda_editar_tabela_width; ?>&inlineId=gerar_relatorio&modal=true" class="thickbox" id="botao_geral2">Gerar relatório</a>
                <? } ?>
                <!-- fim - Gerar relatório -->

                <!-- Gerar novo agendamento -->
                <a href="agenda_gerar.php" id="botao_geral2">Gerar novo agendamento</a>
                <!-- fim - Gerar novo agendamento -->

            </td>
        </tr>
    </table>
</div>
<!-- fim - barra inferior -->


<!-- relatórios (oculto) -->
<script>
    //função de submit
    function enviar() {
        document.getElementById('form').submit();
    }
</script>
<div id="gerar_relatorio" style="display: none;">
    <form action="agenda_relatorio.php" method="post" target="_blank" id="form" name="form">
        <!-- cabeçalho -->
        <div class="div_solicitacao_linhas">
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td style="text-align:left">
                        Gerar relatório
                    </td>

                    <td style="text-align: right">
                        &lt;&lt; <a href="#" onClick="self.parent.tb_remove();" style="color: #FFF;">Voltar</a>
                    </td>
                </tr>
            </table>
        </div>
        <!-- fim - cabeçalho -->

        <div class="div_solicitacao_linhas4">
            <input name="relatorio_tipo" type="radio" value="agenda" checked="checked" /> Agendas
        </div>

        <div class="div_solicitacao_linhas4">
            Marque os campos que irão aparecer no relatório:
            <br><br>
            <!-- campos (checklist) -->
            <fieldset style="border: 0px;">
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td width="33%" valign="top">
                            <input value="id" type="checkbox" name="relatorio_campos[]" checked />
                            Núm. do agenda
                            <br>


                            <input value="id_prospeccao" type="checkbox" name="relatorio_campos[]" />
                            Nº da prospecção
                            <br>

                            <input value="data" type="checkbox" name="relatorio_campos[]" checked />
                            Data do agenda
                            <br>

                            <input value="empresa" type="checkbox" name="relatorio_campos[]" checked />
                            Empresa
                            <br>

                            <input value="contrato" type="checkbox" name="relatorio_campos[]" />
                            Núm. do contrato
                            <br>

                            <input value="praca" type="checkbox" name="relatorio_campos[]" />
                            Praça
                            <br>

                            <input value="data_inicio" type="checkbox" name="relatorio_campos[]" />
                            Data início
                            <br>

                            <input value="data_fim" type="checkbox" name="relatorio_campos[]" />
                            Data fim
                        </td>
                        <td width="33%" valign="top">
                            <input value="usuario_responsavel" type="checkbox" name="relatorio_campos[]" />
                            Usuário responsável
                            <br>

                            <input value="status" type="checkbox" name="relatorio_campos[]" />
                            Status
                            <br>

                            <input value="situacao" type="checkbox" name="relatorio_campos[]" />
                            Situação
                            <br>

                            <input value="observacao" type="checkbox" name="relatorio_campos[]" />
                            Observação
                        </td>

                    </tr>
                </table>
            </fieldset>
            <!-- fim - campos (checklist) -->
        </div>

        <!-- rodapé -->
        <div>Obs: este relatório é baseado nos filtros utilizados na tela anterior de listagem das agendas.</div>
        <div style="margin-top: 5px;">
            <input type="hidden" name="where" id="where" value="<?  echo @$where; ?>">
            <?
            $campos = "";
            $count = count(@$where_campos);
            if($count > 0){
                for ($i = 0; $i < $count; $i++) {
                    $campos .= $where_campos[$i].";";
                }
            }
            ?>
            <input type="hidden" name="campos" id="campos" value="<?  echo $campos; ?>">
            <a href="#" onclick="enviar();" id="botao_geral2">Visualizar</a>
        </div>
        <!-- fim - rodapé -->
    </form>
</div>
<!-- fim - relatórios (oculto) -->


</body>

</html>

<?php
mysql_free_result($usuario);
mysql_free_result($filtro_praca);
mysql_free_result($filtro_id_usuario_responsavel);
mysql_free_result($agenda);
?>