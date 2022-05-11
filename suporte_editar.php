<?php require_once('restrito.php'); ?>
<?php require_once('Connections/conexao.php'); ?>
<?php
if (!function_exists("GetSQLValueString")) {
    require_once('funcao_converte_caracter.php');

    function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "")
    {
        $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;

        $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

        switch ($theType) {
            case "text":
                //$theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
                $theValue = ($theValue != "") ? "'" . funcao_converte_caracter($theValue) . "'" : "NULL";
                break;
            case "long":
            case "int":
                $theValue = ($theValue != "") ? intval($theValue) : "NULL";
                break;
            case "double":
                $theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL";
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING'], ENT_QUOTES, 'UTF-8');
}

require_once('parametros.php');
require_once('suporte_funcao_update.php');
require_once('funcao_consulta_versao_array.php');

$atendimento_array['IniAte'] = array('titulo' => 'Iniciado atendimento');
$atendimento_array['SolRea'] = array('titulo' => 'Solicitado reagendamento');
$atendimento_array['SolCan'] = array('titulo' => 'Solicitado cancelamento');
$atendimento_array['FinAte'] = array('titulo' => 'Finalizar atendimento');
$atendimento_array['PenAte'] = array('titulo' => 'Pendente atendimento');

// usuário logado via SESSION
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

// suporte_editar (recordset) - seleciona o suporte atual
$colname_suporte_editar = "-1";
if (isset($_GET['id_suporte'])) {
    $colname_suporte_editar = $_GET['id_suporte'];
}
mysql_select_db($database_conexao, $conexao);
$query_suporte_editar = sprintf("
SELECT id, id_usuario_responsavel, id_usuario_envolvido, status, praca, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = suporte.id_usuario_responsavel) as usuario_responsavel, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = suporte.id_usuario_envolvido) as usuario_envolvido
FROM suporte 
WHERE id = %s", GetSQLValueString($colname_suporte_editar, "int"));
$suporte_editar = mysql_query($query_suporte_editar, $conexao) or die(mysql_error());
$row_suporte_editar = mysql_fetch_assoc($suporte_editar);
$totalRows_suporte_editar = mysql_num_rows($suporte_editar);
// fim - suporte_ditar (recordset) - seleciona o suporte atual

// caso não tenho suporte, volta para listagem ********************************
if ($totalRows_suporte_editar < 1) {
    $site_link_redireciona = "suporte.php?padrao=sim&" . $suporte_padrao;
    echo sprintf("<meta http-equiv=\"refresh\" content=\"0; url=%s\">", $site_link_redireciona);
    exit;
}
// fim - caso não tenho suporte, volta para listagem **************************

// insert - LEU --------------------------------------------------
// se é usuario_responsavel
if ($row_suporte_editar['id_usuario_responsavel'] == $row_usuario['IdUsuario']) {
    $updateSQL_leu = sprintf(
        "UPDATE suporte SET usuario_responsavel_leu=%s WHERE id=%s",
        GetSQLValueString(date("Y-m-d H:i:s"), "date"),
        GetSQLValueString($row_suporte_editar['id'], "int")
    );

    mysql_select_db($database_conexao, $conexao);
    $Result_leu = mysql_query($updateSQL_leu, $conexao) or die(mysql_error());
}
// fim - se é usuario_responsavel

// se é usuario_envolvido
if ($row_suporte_editar['id_usuario_envolvido'] == $row_usuario['IdUsuario']) {
    $updateSQL_leu = sprintf(
        "UPDATE suporte SET usuario_envolvido_leu=%s WHERE id=%s",
        GetSQLValueString(date("Y-m-d H:i:s"), "date"),
        GetSQLValueString($row_suporte_editar['id'], "int")
    );

    mysql_select_db($database_conexao, $conexao);
    $Result_leu = mysql_query($updateSQL_leu, $conexao) or die(mysql_error());
}
// fim - se é usuario_envolvido
// fim - insert - LEU  -------------------------------------------

mysql_free_result($suporte_editar);

// ----------------------------------------------------------------------------------------------------------------------------------------------------------------

// SELECT - suporte
$colname_suporte = "-1";
if (isset($_GET['id_suporte'])) {
    $colname_suporte = $_GET['id_suporte'];
}
mysql_select_db($database_conexao, $conexao);
$query_suporte = sprintf("
SELECT *,  
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = suporte.id_usuario_responsavel) as usuario_responsavel, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = suporte.id_usuario_envolvido) as usuario_envolvido
FROM suporte 
WHERE id = %s", GetSQLValueString($colname_suporte, "int"));
$suporte = mysql_query($query_suporte, $conexao) or die(mysql_error());
$row_suporte = mysql_fetch_assoc($suporte);
$totalRows_suporte = mysql_num_rows($suporte);
// fim - SELECT - suporte

// caso ainda tenha campos a informar (tela g/e), então cai para a página suporte_gerar.php
if ($row_suporte['tela'] == "g") {
    // redireciona
    echo sprintf("<meta http-equiv=\"refresh\" content=\"0; url=%s\">", "suporte_gerar.php?id_suporte=" . $row_suporte['id']);
    // fim - redireciona
    exit;
}
// fim - caso ainda tenha campos a informar (tela g/e), então cai para a página suporte_gerar.php

// verifica o tipo_suporte_inloco
$tipo_suporte_inloco = "";
if ($row_suporte['tipo_suporte'] == "c" and $row_suporte['inloco'] == "s") {
    $tipo_suporte_inloco = "cs"; // cliente inloco SIM
} else if ($row_suporte['tipo_suporte'] == "c" and $row_suporte['inloco'] == "n") {
    $tipo_suporte_inloco = "cn"; // cliente inloco NAO
} else if ($row_suporte['tipo_suporte'] == "p") {
    $tipo_suporte_inloco = "p"; // parceiro
} else if ($row_suporte['tipo_suporte'] == "r") {
    $tipo_suporte_inloco = "r"; // reclamacao
}
// verifica o tipo_suporte_inloco

// $colname_contrato
$colname_contrato = "-1";
if (isset($row_suporte["contrato"])) {
    $colname_contrato = $row_suporte["contrato"];
}
// fim - $colname_contrato

// manutencao_dados
mysql_select_db($database_conexao, $conexao);
$query_manutencao_dados = sprintf(
    "
SELECT 
geral_tipo_praca_executor.praca, 
da37.codigo17, da37.cliente17, da37.status17, da37.obs17, da37.tpocont17, da37.visita17, da37.optacuv17, da37.versao17, da37.espmod17, versao17, espmod17, da37.datvis17, da37.porsal17, 
geral_tipo_contrato.descricao as tpocont17_descricao, 
geral_tipo_visita.descricao as visita17_descricao

FROM da37 
INNER JOIN geral_tipo_praca_executor ON da37.vendedor17 = geral_tipo_praca_executor.IdExecutor
INNER JOIN geral_tipo_contrato ON da37.tpocont17 = geral_tipo_contrato.IdTipoContrato
INNER JOIN geral_tipo_visita ON da37.visita17 = geral_tipo_visita.IdTipoVisita 

WHERE da37.codigo17 = %s and da37.sr_deleted <> 'T'",
    GetSQLValueString($colname_contrato, "text")
);
$manutencao_dados = mysql_query($query_manutencao_dados, $conexao) or die(mysql_error());
$row_manutencao_dados = mysql_fetch_assoc($manutencao_dados);
$totalRows_manutencao_dados = mysql_num_rows($manutencao_dados);
// fim - manutencao_dados

// empresa_dados
mysql_select_db($database_conexao, $conexao);
$query_empresa_dados = sprintf("
SELECT 
    codigo1, nome1, endereco1, bairro1, cidade1, uf1, telefone1, comercio1, cep1, ultcompra1, atraso1, status1, flag1, tipo1, atraso1  
FROM 
    da01 
WHERE 
    codigo1 = %s and da01.sr_deleted <> 'T'
",
GetSQLValueString($row_manutencao_dados['cliente17'], "text")
);
$empresa_dados = mysql_query($query_empresa_dados, $conexao) or die(mysql_error());
$row_empresa_dados = mysql_fetch_assoc($empresa_dados);
$totalRows_empresa_dados = mysql_num_rows($empresa_dados);
// fim - empresa_dados

// pe13
mysql_select_db($database_conexao, $conexao);
$query_pe13 = "
SELECT 
	bloficca13
FROM 
	pe13 
WHERE 
	codigo13 = 'S9'
LIMIT 1 
";
$pe13 = mysql_query($query_pe13, $conexao) or die(mysql_error());
$row_pe13 = mysql_fetch_assoc($pe13);
$totalRows_pe13 = mysql_num_rows($pe13);
// fim - pe13

// modcon
mysql_select_db($database_conexao, $conexao);
$query_modcon = sprintf("
SELECT 
    modcon.* 
FROM 
    modcon 
WHERE 
    modcon.contrato = %s and 
    modcon.codcli = %s
", 
GetSQLValueString($row_manutencao_dados['codigo17'], "text"), 
GetSQLValueString($row_empresa_dados['codigo1'], "text"));
$modcon = mysql_query($query_modcon, $conexao) or die(mysql_error());
$row_modcon = mysql_fetch_assoc($modcon);
$totalRows_modcon = mysql_num_rows($modcon);
// fim - modcon

// função consulta créditos (contrato)
$creditos = NULL;
$creditos = funcao_suporte_credito($row_suporte['contrato']);
// fim - função consulta créditos (contrato)

// select - suporte_formulario_bonus_ultimo
mysql_select_db($database_conexao, $conexao);
$query_suporte_formulario_bonus_ultimo = sprintf(
    "
SELECT suporte_formulario.data 
FROM suporte_formulario 
WHERE suporte_formulario.contrato = %s and suporte_formulario.status_flag = 'a' and suporte_formulario.visita_bonus='s' 
ORDER BY suporte_formulario.IdFormulario DESC 
LIMIT 1 
",
    GetSQLValueString($colname_contrato, "text")
);
$suporte_formulario_bonus_ultimo = mysql_query($query_suporte_formulario_bonus_ultimo, $conexao) or die(mysql_error());
$row_suporte_formulario_bonus_ultimo = mysql_fetch_assoc($suporte_formulario_bonus_ultimo);
$totalRows_suporte_formulario_bonus_ultimo = mysql_num_rows($suporte_formulario_bonus_ultimo);
// fim - select - suporte_formulario_bonus_ultimo

// suporte_formulario_atual
mysql_select_db($database_conexao, $conexao);
$query_suporte_formulario_atual = sprintf("SELECT * FROM suporte_formulario WHERE id_suporte = %s", GetSQLValueString($row_suporte['id'], "int"));
$suporte_formulario_atual = mysql_query($query_suporte_formulario_atual, $conexao) or die(mysql_error());
$row_suporte_formulario_atual = mysql_fetch_assoc($suporte_formulario_atual);
$totalRows_suporte_formulario_atual = mysql_num_rows($suporte_formulario_atual);
// fim - suporte_formulario_atual

// suporte_formulario_listar
$suporte_formulario_listar_limite = 3;
if (isset($_GET['suporte_formulario_listar_limite']) and $_GET['suporte_formulario_listar_limite'] != '') {
    $suporte_formulario_listar_limite = $_GET['suporte_formulario_listar_limite'];
}

mysql_select_db($database_conexao, $conexao);
$query_suporte_formulario_listar = sprintf(
    "
SELECT * 
FROM suporte_formulario 
WHERE codigo_empresa = %s  AND contrato = %s  and status_flag <> 'c' ORDER BY IdFormulario DESC LIMIT $suporte_formulario_listar_limite",
    GetSQLValueString($row_manutencao_dados['cliente17'], "text"),
    GetSQLValueString($colname_contrato, "text")
);
$suporte_formulario_listar = mysql_query($query_suporte_formulario_listar, $conexao) or die(mysql_error());
$row_suporte_formulario_listar = mysql_fetch_assoc($suporte_formulario_listar);
$totalRows_suporte_formulario_listar = mysql_num_rows($suporte_formulario_listar);

$query_suporte_formulario_listar2 = sprintf(
    "SELECT IdFormulario FROM suporte_formulario WHERE codigo_empresa = %s  AND contrato = %s  and status_flag <> 'c' ORDER BY IdFormulario DESC",
    GetSQLValueString($row_manutencao_dados['cliente17'], "text"),
    GetSQLValueString($colname_contrato, "text")
);
$suporte_formulario_listar2 = mysql_query($query_suporte_formulario_listar2, $conexao) or die(mysql_error());
$row_suporte_formulario_listar2 = mysql_fetch_assoc($suporte_formulario_listar2);
$totalRows_suporte_formulario_listar2 = mysql_num_rows($suporte_formulario_listar2);
// fim - suporte_formulario_listar

// descricao
$colname_descricao = "-1";
if (isset($_GET['id_suporte'])) {
    $colname_descricao = $_GET['id_suporte'];
}
mysql_select_db($database_conexao, $conexao);
$query_descricao = sprintf("
SELECT *, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = suporte_descricoes.id_usuario_responsavel) as usuario_responsavel 
FROM suporte_descricoes 
WHERE id_suporte = %s 
ORDER BY id DESC", GetSQLValueString($colname_descricao, "text"));
$descricao = mysql_query($query_descricao, $conexao) or die(mysql_error());
$row_descricao = mysql_fetch_assoc($descricao);
$totalRows_descricao = mysql_num_rows($descricao);
// fim - descricao

// agenda
mysql_select_db($database_conexao, $conexao);
$query_agenda = sprintf("
SELECT * 
FROM agenda 
WHERE id_suporte = %s 
ORDER BY data ASC", GetSQLValueString(@$_GET['id_suporte'], "text"));
$agenda = mysql_query($query_agenda, $conexao) or die(mysql_error());
$row_agenda = mysql_fetch_assoc($agenda);
$totalRows_agenda = mysql_num_rows($agenda);
// fim - agenda

// agenda_agendado
mysql_select_db($database_conexao, $conexao);
$query_agenda_agendado = sprintf("
SELECT id_agenda 
FROM agenda 
WHERE id_suporte = %s and status = 'a'
ORDER BY data ASC", GetSQLValueString(@$_GET['id_suporte'], "text"));
$agenda_agendado = mysql_query($query_agenda_agendado, $conexao) or die(mysql_error());
$row_agenda_agendado = mysql_fetch_assoc($agenda_agendado);
$totalRows_agenda_agendado = mysql_num_rows($agenda_agendado);
// fim - agenda_agendado

// reclamacao_vinculo
mysql_select_db($database_conexao, $conexao);
$query_reclamacao_vinculo = sprintf("
SELECT id, data_suporte, situacao, titulo 
FROM suporte 
WHERE reclamacao_vinculo = %s 
ORDER BY id ASC", GetSQLValueString($row_suporte['id'], "text"));
$reclamacao_vinculo = mysql_query($query_reclamacao_vinculo, $conexao) or die(mysql_error());
$row_reclamacao_vinculo = mysql_fetch_assoc($reclamacao_vinculo);
$totalRows_reclamacao_vinculo = mysql_num_rows($reclamacao_vinculo);
// fim - reclamacao_vinculo

// suporte_contato (contador)
mysql_select_db($database_conexao, $conexao);
$query_suporte_contato = sprintf("
SELECT count(id) as retorno
FROM suporte_contato 
WHERE id_suporte = %s 
ORDER BY id ASC", GetSQLValueString($row_suporte['id'], "int"));
$suporte_contato = mysql_query($query_suporte_contato, $conexao) or die(mysql_error());
$row_suporte_contato = mysql_fetch_assoc($suporte_contato);
$totalRows_suporte_contato = mysql_num_rows($suporte_contato);
// fim - suporte_contato (contador)

// arquivos_anexos
mysql_select_db($database_conexao, $conexao);
$query_arquivos_anexos = sprintf("SELECT id_arquivo FROM suporte_arquivos WHERE id_suporte = %s", GetSQLValueString($_GET['id_suporte'], "int"));
$arquivos_anexos = mysql_query($query_arquivos_anexos, $conexao) or die(mysql_error());
$row_arquivos_anexos = mysql_fetch_assoc($arquivos_anexos);
$totalRows_arquivos_anexos = mysql_num_rows($arquivos_anexos);
// fim - arquivos_anexos

// tempo_gasto
$colname_tempo_gasto = "-1";
if (isset($_GET['id_suporte'])) {
    $colname_tempo_gasto = $_GET['id_suporte'];
}
mysql_select_db($database_conexao, $conexao);
$query_tempo_gasto = sprintf("
SELECT id_suporte_tempo_gasto 
FROM suporte_tempo_gasto 
WHERE id_suporte = %s 
ORDER BY id_suporte_tempo_gasto DESC", GetSQLValueString($colname_tempo_gasto, "int"));
$tempo_gasto = mysql_query($query_tempo_gasto, $conexao) or die(mysql_error());
$row_tempo_gasto = mysql_fetch_assoc($tempo_gasto);
$totalRows_tempo_gasto = mysql_num_rows($tempo_gasto);
// fim - tempo_gasto

// reclamacao_suporte
mysql_select_db($database_conexao, $conexao);
$query_reclamacao_suporte = sprintf("
SELECT id, data_suporte, situacao, titulo 
FROM suporte 
WHERE reclamacao_suporte = %s 
ORDER BY id ASC", GetSQLValueString($row_suporte['id'], "text"));
$reclamacao_suporte = mysql_query($query_reclamacao_suporte, $conexao) or die(mysql_error());
$row_reclamacao_suporte = mysql_fetch_assoc($reclamacao_suporte);
$totalRows_reclamacao_suporte = mysql_num_rows($reclamacao_suporte);
// fim - reclamacao_suporte

// reclamacao_consulta
mysql_select_db($database_conexao, $conexao);
$query_reclamacao_consulta = sprintf(
    "
SELECT 
    id, empresa, situacao, status_flag     
FROM 
    suporte 
WHERE 
    contrato = %s and tipo_suporte = 'r' and 
    (
        (status_flag = 'a') or 
        (status_flag = 'f' and DATE_ADD(data_fim,INTERVAL " . $row_parametros['suporte_reclamacao_mensagem_inicial_dias'] . " DAY) >= now())
    )
",
    GetSQLValueString($row_suporte['contrato'], "text")
);
$reclamacao_consulta = mysql_query($query_reclamacao_consulta, $conexao) or die(mysql_error());
$row_reclamacao_consulta = mysql_fetch_assoc($reclamacao_consulta);
$totalRows_reclamacao_consulta = mysql_num_rows($reclamacao_consulta);

if ($totalRows_reclamacao_consulta > 0) {

    $reclamacao_consulta_status = 0;
    $reclamacao_consulta_mensagem_aberta = NULL;
    $reclamacao_consulta_mensagem_fechada = NULL;
    do {

        if ($row_reclamacao_consulta['status_flag'] == "f") {
            $reclamacao_consulta_mensagem_fechada .= 'Reclamação: ' . $row_reclamacao_consulta['id'] . ' - Situação: ' . $row_reclamacao_consulta['situacao'] . '\n';
        } else {
            $reclamacao_consulta_status = 1;
            $reclamacao_consulta_mensagem_aberta .= 'Reclamação: ' . $row_reclamacao_consulta['id'] . ' - Situação: ' . $row_reclamacao_consulta['situacao'] . '\n';
        }
    } while ($row_reclamacao_consulta = mysql_fetch_assoc($reclamacao_consulta));

    $reclamacao_consulta_mensagem_corpo = NULL;
    if ($reclamacao_consulta_status == 0) {
        $reclamacao_consulta_mensagem = 'ATENÇÃO:\nRECLAMAÇÃO REGISTRADA RECENTEMENTE\nCliente: ' . utf8_encode($row_suporte['empresa']) . '\n' . $reclamacao_consulta_mensagem_fechada;
        $reclamacao_consulta_mensagem_corpo = 'ATENÇÃO: CLIENTE COM RECLAMAÇÃO REGISTRADA RECENTEMENTE';
    } else if ($reclamacao_consulta_status == 1) {
        $reclamacao_consulta_mensagem = 'ATENÇÃO:\nRECLAMAÇÃO EM ANDAMENTO\nCliente: ' . utf8_encode($row_suporte['empresa']) . '\n' . $reclamacao_consulta_mensagem_aberta;
        $reclamacao_consulta_mensagem_corpo = 'ATENÇÃO: CLIENTE COM RECLAMAÇÃO EM ANDAMENTO';
    }
}
// fim - reclamacao_consulta

// reclamacao_encerramento
mysql_select_db($database_conexao, $conexao);
$query_reclamacao_encerramento = sprintf(
    "
SELECT COUNT(id) as retorno 
FROM suporte 
WHERE contrato = %s and tipo_suporte = 'r' and 
(status_flag = 'f' and DATE_ADD(data_fim,INTERVAL " . $row_parametros['suporte_reclamacao_encerramento_dias'] . " DAY) >= now())
",
    GetSQLValueString($row_suporte['contrato'], "text")
);
$reclamacao_encerramento = mysql_query($query_reclamacao_encerramento, $conexao) or die(mysql_error());
$row_reclamacao_encerramento = mysql_fetch_assoc($reclamacao_encerramento);
$totalRows_reclamacao_encerramento = mysql_num_rows($reclamacao_encerramento);
// fim - reclamacao_encerramento

$prazo_anexo_segundos = $row_suporte['geral_tipo_praca_suporte_inloco_sim_prazo_anexo'] * 86400;
$prazo_anexo_limite_segundos = strtotime($row_suporte['data_inicio']) + $prazo_anexo_segundos;
$data_atual_segundos = strtotime(date("Y-m-d 00:00:00"));
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <link rel="stylesheet" href="css/suporte.css" type="text/css" />
    <link rel="stylesheet" href="css/suporte_imprimir.css" type="text/css" media="print" />

    <script type="text/javascript" src="js/jquery.js"></script>

    <link rel="stylesheet" href="css/thickbox.css" type="text/css" media="screen" />
    <script type="text/javascript" src="js/thickbox.js"></script>

    <script type="text/javascript" src="js/jquery.maskedinput-1.2.2.min.js"></script>
    <script type="text/javascript" src="js/jquery.rsv.js"></script>

    <script type="text/javascript">
        window.history.forward(1); // Desabilita a função de voltar do Browser

        $(document).ready(function() {

        });
    </script>
    <title>Suporte n° <? echo $row_suporte['id']; ?></title>
</head>

<body>

    <div class="<? if ($tipo_suporte_inloco == "r") { ?>div_solicitacao_linhas_laranja<? } else { ?>div_solicitacao_linhas<? } ?>">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td style="text-align:left">
                    <? if ($tipo_suporte_inloco == "cs") { ?>
                        Suporte ao cliente (in-loco: Sim <? if ($row_suporte['visita_bonus'] == "s") { ?> - Bônus<? } ?>) n° <? echo $row_suporte['id']; ?>
                        <? } else if ($tipo_suporte_inloco == "cn") { ?>
                            Suporte ao cliente (in-loco: Não) n° <? echo $row_suporte['id']; ?>
                        <? } else if ($tipo_suporte_inloco == "p") { ?>
                            Suporte ao parceiro n° <? echo $row_suporte['id']; ?>
                        <? } else if ($tipo_suporte_inloco == "r") { ?>
                            Reclamação n° <? echo $row_suporte['id']; ?>
                        <? } ?>
                </td>

                <td style="text-align: right">
                    &lt;&lt; <a href="suporte.php?padrao=sim&<? echo $suporte_padrao; ?>">Voltar</a> |
                    Usuário logado: <? echo $row_usuario['nome']; ?> |
                    <a href="painel/padrao_sair.php">Sair</a>
                </td>
            </tr>
        </table>
    </div>

    <div class="div_solicitacao_linhas2">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td style="text-align:left">
                    <span class="label_solicitacao">Empresa: </span>
                    <?php echo utf8_encode($row_suporte['empresa']); ?> |

                    <span class="label_solicitacao">Praça: </span>
                    <?php echo $row_suporte['praca']; ?>

                    <? if ($tipo_suporte_inloco == "cs" or $tipo_suporte_inloco == "cn" or $tipo_suporte_inloco == "p") { ?>
                        | <span class="label_solicitacao">Solicitante: </span>
                        <?php echo $row_suporte['solicitante']; ?>
                        <?php if ($row_suporte['status_flag'] == "a") { ?>
                            <?php if ($row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or ($row_usuario['controle_suporte'] == "Y" or  $row_usuario['suporte_operador_parceiro'] == "Y")) { ?>
                                <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=editar&acao=Alterar solicitante&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox">
                                    <img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar solicitante">
                                </a>
                            <? } ?>
                        <? } ?>
                    <? } ?>
                </td>

                <td style="text-align: right" width="250">
                    <span class="label_solicitacao">Fone: </span><?php echo $row_empresa_dados['telefone1']; ?>
                    <? if ($row_empresa_dados['comercio1'] != "") { ?>
                        | <?php echo $row_empresa_dados['comercio1']; ?>
                    <? } ?>
                </td>
            </tr>
        </table>
    </div>

    <? if ($totalRows_reclamacao_consulta > 0) { ?>
        <div class="div_solicitacao_linhas4" style="color: red;">
            <? echo $reclamacao_consulta_mensagem_corpo; ?>
        </div>
    <? } ?>

    <div class="div_solicitacao_linhas3">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>

                <td style="text-align:left">
                    <span class="label_solicitacao">Localização: </span>
                    <? echo utf8_encode($row_empresa_dados['endereco1']); ?> - <?php echo utf8_encode($row_empresa_dados['bairro1']); ?> -
                    CEP: <?php echo $row_empresa_dados['cep1']; ?> | <?php echo utf8_encode($row_empresa_dados['cidade1']); ?> - <?php echo $row_empresa_dados['uf1']; ?>
                </td>

                <td style="text-align: right" width="200">
                    <? if ($tipo_suporte_inloco == "cs" or $tipo_suporte_inloco == "cn" or $tipo_suporte_inloco == "p") { ?>
                        <span class="label_solicitacao">Prioridade: </span>
                        <?php echo $row_suporte['prioridade']; ?>
                        <?php if ($row_suporte['status_flag'] == "a") { ?>
                            <?php if ($row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or ($row_usuario['controle_suporte'] == "Y" or  $row_usuario['suporte_operador_parceiro'] == "Y")) { ?>
                                <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=editar&acao=Alterar prioridade&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox">
                                    <img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar prioridade"></a>
                            <? } ?>
                        <? } ?>
                    <? } ?>
                </td>

            </tr>
        </table>
    </div>

    <?php if ($row_suporte['prioridade_justificativa'] <> NULL) { ?>
        <div class="div_solicitacao_linhas3">
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td><span class="label_solicitacao">Justificativa da Prioridade Alta: </span><?php echo $row_suporte['prioridade_justificativa']; ?></td>
                </tr>
            </table>
        </div>
    <? } ?>

    <div class="div_solicitacao_linhas4">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td style="text-align:left">
                    <span class="label_solicitacao">Obs. sobre o cliente: </span>
                    <?php echo utf8_encode($row_manutencao_dados['obs17']); ?>
                </td>
            </tr>
        </table>
    </div>

    <div class="div_solicitacao_linhas3">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td style="text-align:left">
                    Contrato: <strong><?php echo $row_manutencao_dados['codigo17']; ?></strong> |
                    Tipo do contrato: <strong><?php echo $row_manutencao_dados['tpocont17_descricao']; ?></strong>
                    <? if ($row_manutencao_dados['datvis17'] > 0) { ?>
                        | Últ. alteração contratual:
                        <strong>
                            <?php
                            echo substr($row_manutencao_dados['datvis17'], 6, 2) . "-" . substr($row_manutencao_dados['datvis17'], 4, 2) . "-" . substr($row_manutencao_dados['datvis17'], 0, 4);
                            ?>
                        </strong>
                    <? } ?>
                </td>

                <td style="text-align:right">
                    Versão:
                    <strong>
                        <?php if ($row_manutencao_dados['versao17'] == "1") {
                            echo "DOS";
                        } ?>
                        <?php if ($row_manutencao_dados['versao17'] == "2") {
                            echo "Windows";
                        } ?>
                    </strong> |

                    Distribuição:
                    <strong>
                        <?php if ($row_manutencao_dados['espmod17'] == "B") {
                            echo "Standard";
                        } ?>
                        <?php if ($row_manutencao_dados['espmod17'] == "O") {
                            echo "Office";
                        } ?>
                    </strong>
                </td>
            </tr>
        </table>
    </div>

    <div class="div_solicitacao_linhas4">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td style="text-align:left">
                    <span class="label_solicitacao">Créditos:</span> <strong><?php echo $creditos; ?></strong>

                    <? if ($tipo_suporte_inloco == "cs") { ?>
                        | <span class="label_solicitacao">Adiantamento de Visita:</span>
                        <strong><?php if ($row_suporte['adiantamento_visita'] == "s") {
                                    echo "Sim";
                                } else {
                                    echo "Não";
                                } ?></strong>
                    <? } ?>

                    <? if ($row_suporte_formulario_atual['tipo_formulario'] == "Treinamento" or $row_suporte_formulario_atual['tipo_formulario'] == "Reclamacao") { ?>
                        <? if ($row_manutencao_dados['visita17'] == "3" or $row_manutencao_dados['visita17'] == "4") { ?>
                            | <span class="label_solicitacao">Abater nos créditos por formulário atual:</span>
                            <strong><?php if ($row_suporte_formulario_atual['creditar'] == "n") {
                                        echo "Não";
                                    }
                                    if ($row_suporte_formulario_atual['creditar'] == "s") {
                                        echo "Sim";
                                    } ?></strong>
                        <? } ?>
                    <? } ?>


                    <span class="label_solicitacao">Direito a visita bônus: </span>
                    <?
                    if (
                        $creditos == 0 and
                        intval($row_empresa_dados['atraso1']) <= intval($row_pe13['bloficca13']) and
                        (
                            (
                                $row_manutencao_dados['visita17'] == "3" and // mensal
                                ((strtotime(date('Y-m', strtotime($row_suporte_formulario_bonus_ultimo['data'])))) < (strtotime(date('Y-m')))) // ultimo bonus diferente do mês atual
                            ) or ($row_manutencao_dados['visita17'] == "4" and // trimestral
                                ((strtotime(date('Y-m', strtotime($row_suporte_formulario_bonus_ultimo['data'])))) < (strtotime('-2 months', strtotime(date('Y-m'))))) // ultimo bonus diferente do mês atual + últimos 2 meses
                            )
                        )
                    ) {
                    ?>
                        <strong>Sim</strong>
                    <? } else { ?>
                        <strong>Não</strong>
                    <? } ?>
                </td>

                <td style="text-align:right">
                    Status manutenção:
                    <strong>
                        <?php
                        if ($row_manutencao_dados['status17'] == "D") {
                            echo "Desbloqueado";
                        }
                        if ($row_manutencao_dados['status17'] == "B") {
                            echo "<font color='red'>Bloqueado</font>";
                        }
                        if ($row_manutencao_dados['status17'] == "C") {
                            echo "Cancelado";
                        }
                        if ($row_manutencao_dados['status17'] == "P") {
                            echo "Pendente";
                        }
                        if ($row_manutencao_dados['status17'] == "S") {
                            echo "Suspenso";
                        }
                        ?> |
                    </strong>
                    Status manual:
                    <strong>
                        <?php
                        if ($row_empresa_dados['status1'] == "0") {
                            echo "Desbloqueado";
                        } // manual
                        if ($row_empresa_dados['status1'] == "1") {
                            echo "<font color='red'>Bloqueado</font>";
                        } // manual
                        ?> |
                    </strong>
                    Status automático:
                    <strong>
                        <?php
                        if ($row_empresa_dados['flag1'] == "0") {
                            echo "Desbloqueado";
                        } // autom
                        if ($row_empresa_dados['flag1'] == "1") {
                            echo "<font color='red'>Bloqueado</font>";
                        } // autom
                        ?>
                    </strong>
                </td>
            </tr>
        </table>
    </div>

    <div class="div_solicitacao_linhas3">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td style="text-align:left" width="600">
                    Tipo de visita: <strong><?php echo $row_manutencao_dados['visita17_descricao']; ?></strong> |
                    Percentual Manutenção: <strong><?php echo $row_manutencao_dados['porsal17']; ?>%</strong> |
                    Optante por acumulo de manutenção: <strong><?php if ($row_manutencao_dados['optacuv17'] == "N") {
                                                                    echo "Não";
                                                                }
                                                                if ($row_manutencao_dados['optacuv17'] == "S") {
                                                                    echo "Sim";
                                                                } ?></strong>
                </td>

                <td style="text-align:right">
                    Última compra: <strong><? echo date('d-m-Y', strtotime($row_empresa_dados['ultcompra1'])); ?></strong> |
                    Total dias em atraso: <strong><?php echo $row_empresa_dados['atraso1']; ?></strong>
                </td>
            </tr>
        </table>
    </div>

    <div class="div_solicitacao_linhas4">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td style="text-align:left; vertical-align: top">
                    <span class="label_solicitacao">Módulos: </span>
                    (<? if($row_modcon['modest']!=NULL){echo "X";}else{echo " ";} ?>) Estoque 
                    (<? if($row_modcon['modfin']!=NULL){echo "X";}else{echo " ";} ?>) Financeiro 
                    (<? if($row_modcon['modser']!=NULL){echo "X";}else{echo " ";} ?>) Serviço 
                    (<? if($row_modcon['modoti']!=NULL){echo "X";}else{echo " ";} ?>) Ótica 
                    (<? if($row_modcon['modpdv']!=NULL){echo "X";}else{echo " ";} ?>) PDV
                    (<? if($row_modcon['modpve']!=NULL){echo "X";}else{echo " ";} ?>) PVE
                    (<? if($row_modcon['modben']!=NULL){echo "X";}else{echo " ";} ?>) Bens
                </td>

                <td style="text-align: right">
                    <span class="label_solicitacao">Qtde de Computadores:</span> <strong><? echo $row_modcon['qtdter']-0; ?></strong> | 
                    <span class="label_solicitacao">Qtde de Terminais:</span> <strong><? echo $row_modcon['qtdter']-1; ?></strong>
                </td>
            </tr>
        </table>
    </div>

    <div class="div_solicitacao_linhas4">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td style="text-align:left; vertical-align: top">
                    <span class="label_solicitacao">Ferramentas adicionais: </span>

                    (<? if($row_modcon['ferlot']!=NULL){echo "X";}else{echo " ";} ?>) Lote bancário
                    (<? if($row_modcon['fernfe']!=NULL){echo "X";}else{echo " ";} ?>) NFE
                    (<? if($row_modcon['ferefd']!=NULL){echo "X";}else{echo " ";} ?>) EFD
                    (<? if($row_modcon['ferrelcon']!=NULL){echo "X";}else{echo " ";} ?>) Relatórios Consultoria
                    (<? if($row_modcon['fermes']!=NULL){echo "X";}else{echo " ";} ?>) Controle Mesa

                    (<? if($row_modcon['ferbin']!=NULL){echo "X";}else{echo " ";} ?>) Bina
                    (<? if($row_modcon['ferfid']!=NULL){echo "X";}else{echo " ";} ?>) Cartão Fidelidade
                    
                    (<? if($row_modcon['fertdi']!=NULL){echo "X";}else{echo " ";} ?>) Tef Discado
                    (<? if($row_modcon['fertdd']!=NULL){echo "X";}else{echo " ";} ?>) Tef Dedicado
                    (<? if($row_modcon['fertpy']!=NULL){echo "X";}else{echo " ";} ?>) Tef Pay&Go
                </td>
            </tr>
        </table>
    </div>

    <div class="div_solicitacao_linhas3">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td style="text-align:left; vertical-align: top">
                    <span class="label_solicitacao">Título: </span>
                    <?php echo $row_suporte['titulo']; ?>
                    <?php if ($row_suporte['status_flag'] == "a" and $tipo_suporte_inloco == "cn" or $tipo_suporte_inloco == "p") { ?>
                        <?php if ($row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or ($row_usuario['controle_suporte'] == "Y" or  $row_usuario['suporte_operador_parceiro'] == "Y")) { ?>
                            <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=editar&acao=Alterar título&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox">
                                <img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar título"></a>
                        <? } ?>
                    <? } ?>
                </td>

                <td style="text-align: right">
                    <span class="label_solicitacao">Data/hora criação: </span>
                    <? echo date('d-m-Y  H:i:s', strtotime($row_suporte['data_suporte'])); ?>

                    <?php if ($row_suporte['data_suporte_fim'] != "0000-00-00 00:00:00" and $row_suporte['data_suporte_fim'] != "") { ?>
                        | <span class="label_solicitacao">Data/hora término: </span><? echo date('d-m-Y  H:i:s', strtotime($row_suporte['data_suporte_fim'])); ?>
                    <? } ?>

                    <br>
                    <span class="label_solicitacao">Agend. início: </span>
                    <? echo date('d-m-Y  H:i:s', strtotime($row_suporte['data_inicio'])); ?>

                    <?php if ($row_suporte['data_fim'] != "0000-00-00 00:00:00") { ?>
                        | <span class="label_solicitacao">Agend. fim: </span><? echo date('d-m-Y  H:i:s', strtotime($row_suporte['data_fim'])); ?>
                    <? } ?>

                    <!-- 'data_solicita_suporte' e 'data_solicita_suporte_aceita_recusa' -->
                    <?php if ($row_suporte['data_solicita_suporte'] != "" or $row_suporte['data_solicita_suporte_aceita_recusa'] != "") { ?>
                        <br>
                        <span class="label_solicitacao">Solicitado suporte em: </span>
                        <? echo date('d-m-Y  H:i:s', strtotime($row_suporte['data_solicita_suporte'])); ?>

                        <?php if ($row_suporte['data_solicita_suporte_aceita_recusa'] != "") { ?>
                            |
                            <span class="label_solicitacao">Aceito/Recusado suporte em: </span>
                            <? echo date('d-m-Y  H:i:s', strtotime($row_suporte['data_solicita_suporte_aceita_recusa'])); ?>

                            <br>
                            <span class="label_solicitacao">Atendimento realizado após: </span>
                            <?
                            // diferença entre 'data_solicita_suporte' e 'data_solicita_suporte_aceita_recusa'
                            $data_solicita_suporte_segundos = strtotime($row_suporte['data_solicita_suporte']);
                            $data_solicita_suporte_aceita_recusa_segundos = strtotime($row_suporte['data_solicita_suporte_aceita_recusa']);
                            $data_solicita_suporte_diferenca = $data_solicita_suporte_aceita_recusa_segundos - $data_solicita_suporte_segundos;

                            $nDias   = ($data_solicita_suporte_diferenca) / (3600 * 24);  // dias
                            $nHoras = (($data_solicita_suporte_diferenca) % (3600 * 24)) / 3600; // horas
                            $nMinutos = ((($data_solicita_suporte_diferenca) % (3600 * 24)) % 3600) / 60; // minutos

                            echo $data_solicita_suporte_resultado = sprintf('%02dd %02dh %02dm', $nDias, $nHoras, $nMinutos);
                            // fim - diferença entre 'data_solicita_suporte' e 'data_solicita_suporte_aceita_recusa'
                            ?>
                        <? } ?>
                        <br>
                    <? } ?>
                    <!-- fim - 'data_solicita_suporte' e 'data_solicita_suporte_aceita_recusa' -->
                </td>
            </tr>
        </table>
    </div>

    <?php if ($row_suporte['reclamacao'] <> NULL) { ?>
        <div class="div_solicitacao_linhas2">
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td style="text-align: left">
                        <span class="label_solicitacao">Reclamação: </span>
                        <br>
                        <?php echo $row_suporte['reclamacao']; ?>
                        <?php if ($row_suporte['status_flag'] == "a") { ?>
                            <?php if ($row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or ($row_usuario['controle_suporte'] == "Y" or  $row_usuario['suporte_operador_parceiro'] == "Y")) { ?>
                                <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=editar&acao=Alterar reclamação&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox">
                                    <img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar reclamação"></a>
                            <? } ?>
                        <? } ?>
                    </td>
                </tr>
            </table>
        </div>
    <? } ?>

    <?php if ($row_suporte['reclamacao_questionamento'] <> NULL) { ?>
        <div class="div_solicitacao_linhas3">
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td style="text-align: left">
                        <span class="label_solicitacao">Questionamento inicial da Success: </span>
                        <br>
                        <?php echo $row_suporte['reclamacao_questionamento']; ?>
                        <?php if ($row_suporte['status_flag'] == "a") { ?>
                            <?php if ($row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or ($row_usuario['controle_suporte'] == "Y" or  $row_usuario['suporte_operador_parceiro'] == "Y")) { ?>
                                <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=editar&acao=Alterar questionamento inicial&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox">
                                    <img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar questionamento inicial"></a>
                            <? } ?>
                        <? } ?>
                    </td>
                </tr>
            </table>
        </div>
    <? } ?>

    <div class="div_solicitacao_linhas2">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>

                <td style="text-align:left">
                    <? if ($tipo_suporte_inloco == "cs" or $tipo_suporte_inloco == "cn" or $tipo_suporte_inloco == "p") { ?>

                        <span class="label_solicitacao">Módulo: </span>
                        <?php echo $row_suporte['modulo']; ?>
                        <?php if ($row_suporte['status_flag'] == "a") { ?>
                            <?php if ($row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or ($row_usuario['controle_suporte'] == "Y" or  $row_usuario['suporte_operador_parceiro'] == "Y")) { ?>
                                <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=editar&acao=Alterar módulo&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox">
                                    <img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar módulo"></a>
                            <? } ?>
                        <? } ?> |

                        <span class="label_solicitacao">Tipo de atendimento: </span>
                        <?php echo $row_suporte['tipo_atendimento']; ?>
                        <?php if ($row_suporte['status_flag'] == "a") { ?>
                            <?php if ($row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or ($row_usuario['controle_suporte'] == "Y" or  $row_usuario['suporte_operador_parceiro'] == "Y")) { ?>
                                <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=editar&acao=Alterar tipo de atendimento&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox">
                                    <img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar tipo de atendimento"></a>
                            <? } ?>
                        <? } ?>

                        <?php if ($tipo_suporte_inloco == "p") { ?>

                            | <span class="label_solicitacao">Recomendação: </span>

                            <?php echo $row_suporte['recomendacao']; ?>
                            <?php if ($row_suporte['status_flag'] == "a") { ?>
                                <?php if ($row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or ($row_usuario['controle_suporte'] == "Y" or  $row_usuario['suporte_operador_parceiro'] == "Y")) { ?>
                                    <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=editar&acao=Alterar recomendação&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox">
                                        <img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar tipo de recomendação"></a>
                                <? } ?>
                            <? } ?>

                            <? if ($row_suporte['versao'] <> NULL) { ?>
                                <br>
                                <span class="label_solicitacao">Versões: </span><?php echo funcao_consulta_versao_array($row_suporte['versao']); ?>
                            <? } ?>

                        <? } ?>

                    <? } else if ($tipo_suporte_inloco == "r") { ?>

                        <span class="label_solicitacao">Percepção: </span>
                        <?php echo $row_suporte['reclamacao_percepcao']; ?>
                        <?php if ($row_suporte['status_flag'] == "a") { ?>
                            <?php if ($row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario']) { ?>
                                <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=editar&acao=Alterar percepção&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox">
                                    <img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar percepção"></a>
                            <? } ?>
                        <? } ?> |

                        <span class="label_solicitacao">Data acordada: </span>
                        <?php echo date('d-m-Y H:i', strtotime($row_suporte['reclamacao_data_acordada'])); ?>
                        <?php if ($row_suporte['status_flag'] == "a") { ?>
                            <?php if ($row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario']) { ?>
                                <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=editar&acao=Alterar data acordada&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox">
                                    <img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar data acordada"></a>
                            <? } ?>
                        <? } ?> |

                        <span class="label_solicitacao">Reclamante: </span>
                        <?php echo $row_suporte['reclamacao_responsavel']; ?>
                        <?php if ($row_suporte['status_flag'] == "a") { ?>
                            <?php if ($row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario']) { ?>
                                <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=editar&acao=Alterar reclamante&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox">
                                    <img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar reclamante"></a>
                            <? } ?>
                        <? } ?> |

                        <span class="label_solicitacao">Telefone de contato reclamante: </span>
                        <?php echo $row_suporte['reclamacao_telefone']; ?>
                        <?php if ($row_suporte['status_flag'] == "a") { ?>
                            <?php if ($row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario']) { ?>
                                <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=editar&acao=Alterar telefone reclamante&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox">
                                    <img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar telefone reclamante"></a>
                            <? } ?>
                        <? } ?>

                    <? } ?>
                </td>

                <td style="text-align: right">
                    <span class="label_solicitacao">Situação: </span><?php echo $row_suporte['situacao']; ?> 
                    | <span class="label_solicitacao">Status: </span><?php echo $row_suporte['status']; ?> 
                    <? if($row_suporte['status_questionamento'] <> ''){ ?>
                    | <span class="label_solicitacao">Questionamento para: </span><?php echo $row_suporte['status_questionamento']; ?>
                    <? } ?>

                    <? if($row_suporte['atendimento'] <> ''){ ?>
                        <br>
                        <span class="label_solicitacao">Atendimento: </span><? echo @$atendimento_array[$row_suporte['atendimento']]['titulo']; ?>
                    <? } ?>
                   
                </td>

            </tr>
        </table>
    </div>

    <?php if ($row_suporte['anomalia'] <> NULL) { ?>
        <div class="div_solicitacao_linhas3">
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td style="text-align: left">
                        <span class="label_solicitacao">Anomalia: </span>
                        <br>
                        <?php echo $row_suporte['anomalia']; ?>
                        <?php if ($row_suporte['status_flag'] == "a") { ?>
                            <?php if ($row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or ($row_usuario['controle_suporte'] == "Y" or  $row_usuario['suporte_operador_parceiro'] == "Y")) { ?>
                                <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=editar&acao=Alterar anomalia&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox">
                                    <img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar anomalia"></a>
                            <? } ?>
                        <? } ?>
                    </td>
                </tr>
            </table>
        </div>
    <? } ?>

    <? if ($tipo_suporte_inloco == "cn" or $tipo_suporte_inloco == "p") { ?>
        <div class="div_solicitacao_linhas2">
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td style="text-align: left">
                        <span class="label_solicitacao">Orientação: </span>
                        <br>
                        <?php echo $row_suporte['orientacao']; ?>
                        <?php if ($row_suporte['status_flag'] == "a") { ?>
                            <?php if ($row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or ($row_usuario['controle_suporte'] == "Y" or  $row_usuario['suporte_operador_parceiro'] == "Y")) { ?>
                                <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=editar&acao=Alterar orientação&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox">
                                    <img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar orientação"></a>
                            <? } ?>
                        <? } ?>
                    </td>
                </tr>
            </table>
        </div>
    <? } ?>

    <?php if ($row_suporte['parecer'] <> NULL) { ?>
        <div class="div_solicitacao_linhas3">
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td style="text-align: left; vertical-align: top;">
                        <span class="label_solicitacao">Parecer: </span>
                        <?php echo $row_suporte['parecer']; ?>
                    </td>
                </tr>
            </table>
        </div>
    <? } ?>

    <?php if ($row_suporte['cobranca'] == "s") { ?>
        <div class="div_solicitacao_linhas4">
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td style="text-align: left; vertical-align: top;">
                        <span style="color: #C00; font-weight: bold;">* Suporte para auxílio em cobrança</span>

                        <?php if ($row_suporte['cobranca_recebimento'] == "s") { ?>
                            | <span class="label_solicitacao">Confirma recebimento:</span> Sim
                        <? } ?>
                        <?php if ($row_suporte['cobranca_recebimento'] == "n") { ?>
                            | <span class="label_solicitacao">Confirma recebimento:</span> Não
                        <? } ?>
                        <?php if ($row_suporte['cobranca_documento_vinculado'] != "") { ?>
                            | <span class="label_solicitacao">Doc. Vinculado:</span> <? echo $row_suporte['cobranca_documento_vinculado']; ?>
                        <? } ?>
                    </td>

                    <td style="text-align: right; vertical-align: top;">
                        <span class="label_solicitacao">Justificativa: </span>
                        <?php echo $row_suporte['cobranca_recebimento_justificativa']; ?>
                    </td>
                </tr>
            </table>
        </div>
    <? } ?>

    <?php if ($row_suporte['avaliacao_atendimento'] != "") { ?>
        <div class="div_solicitacao_linhas3">
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td style="text-align: left; vertical-align: top;">
                        <span class="label_solicitacao">Avaliação de atendimento: </span>
                        <?php echo $row_suporte['avaliacao_atendimento']; ?>
                        <br>
                    </td>

                    <td style="text-align: right; vertical-align: top;">
                        <?php if ($row_suporte['avaliacao_atendimento_justificativa'] != "") { ?>
                            <span class="label_solicitacao">Justificativa: </span>
                            <?php echo $row_suporte['avaliacao_atendimento_justificativa']; ?>
                            <br>
                        <? } ?>
                    </td>
                </tr>
            </table>
        </div>
    <? } ?>

    <?php if ($row_suporte['solucionado'] == "n") { ?>
        <div class="div_solicitacao_linhas3">
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td style="text-align: left; vertical-align: top;">
                        <span class="label_solicitacao">Justificativa para o encerramento sem solução do problema: </span>
                        <?php echo $row_suporte['solucionado_nao']; ?>
                    </td>
                </tr>
            </table>
        </div>
    <? } ?>

    <!-- reclamacao_vinculo -->
    <?php if ($row_suporte['reclamacao_vinculo'] > 0) { ?>
        <div class="div_solicitacao_linhas3">
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td style="text-align: left; vertical-align: top;">
                        <span style="color: #C00; font-weight: bold;">Este suporte foi gerado a partir da reclamação nº <? echo $row_suporte['reclamacao_vinculo']; ?></span> -
                        <a href="suporte_editar.php?id_suporte=<? echo $row_suporte['reclamacao_vinculo']; ?>&padrao=sim">Acessar</a>
                    </td>

                    <td style="text-align: right; vertical-align: top;">
                        <span class="label_solicitacao">Envolvido(s) na reclamação:</span><br>
                        <? echo $row_suporte['envolvido_reclamacao']; ?>
                        <?php if ($row_suporte['status_flag'] == "a") { ?>
                            <?php if ($row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or ($row_usuario['controle_suporte'] == "Y" or  $row_usuario['suporte_operador_parceiro'] == "Y")) { ?>
                                <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=editar&acao=Alterar envolvido(s) na reclamação&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox">
                                    <img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar envolvido(s) na reclamação"></a>
                            <? } ?>
                        <? } ?>
                    </td>
                </tr>
            </table>
        </div>
    <? } ?>
    <!-- fim - reclamacao_vinculo -->

    <!-- reclamacao_solicitacao -->
    <?php if ($row_suporte['reclamacao_solicitacao'] > 0) { ?>
        <div class="div_solicitacao_linhas3">
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td style="text-align: left; vertical-align: top;">
                        <span style="color: #C00; font-weight: bold;">Esta reclamação foi gerada a partir da solicitação nº <? echo $row_suporte['reclamacao_solicitacao']; ?></span> -
                        <a href="solicitacao_editar.php?id_solicitacao=<? echo $row_suporte['reclamacao_solicitacao']; ?>&padrao=sim">Acessar</a>
                    </td>
                </tr>
            </table>
        </div>
    <? } ?>
    <!-- fim - reclamacao_solicitacao -->

    <!-- reclamacao_suporte -->
    <?php if ($row_suporte['reclamacao_suporte'] > 0) { ?>
        <div class="div_solicitacao_linhas3">
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td style="text-align: left; vertical-align: top;">
                        <span style="color: #C00; font-weight: bold;">Esta reclamação foi gerada a partir do suporte nº <? echo $row_suporte['reclamacao_suporte']; ?></span> -
                        <a href="suporte_editar.php?id_suporte=<? echo $row_suporte['reclamacao_suporte']; ?>&padrao=sim">Acessar</a>
                    </td>
                </tr>
            </table>
        </div>
    <? } ?>
    <!-- fim - reclamacao_suporte -->

    <!-- reclamacao_prospeccao -->
    <?php if ($row_suporte['reclamacao_prospeccao'] > 0) { ?>
        <div class="div_solicitacao_linhas3">
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td style="text-align: left; vertical-align: top;">
                        <span style="color: #C00; font-weight: bold;">Esta reclamação foi gerada a partir da prospecção nº <? echo $row_suporte['reclamacao_prospeccao']; ?></span> -
                        <a href="prospeccao_editar.php?id_prospeccao=<? echo $row_suporte['reclamacao_prospeccao']; ?>&padrao=sim">Acessar</a>
                    </td>
                </tr>
            </table>
        </div>
    <? } ?>
    <!-- fim - reclamacao_prospeccao -->

    <!-- reclamacao_venda -->
    <?php if ($row_suporte['reclamacao_venda'] > 0) { ?>
        <div class="div_solicitacao_linhas3">
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td style="text-align: left; vertical-align: top;">
                        <span style="color: #C00; font-weight: bold;">Esta reclamação foi gerada a partir da venda nº <? echo $row_suporte['reclamacao_venda']; ?></span> -
                        <a href="venda_editar.php?id_venda=<? echo $row_suporte['reclamacao_venda']; ?>&padrao=sim">Acessar</a>
                    </td>
                </tr>
            </table>
        </div>
    <? } ?>
    <!-- fim - reclamacao_venda -->



    <!-- Botões ====================================================================================================================================================== -->
    <? if ($row_suporte['situacao'] != "solucionada" and $row_suporte['situacao'] != "cancelada" and $row_suporte['situacao'] != "criada") { ?>
        <div class="div_solicitacao_linhas4" id="botoes">
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td style="text-align:left">

                        <!-- Concluir execução -->
                        <?
                        if (
                            // controle_suporte/operador-parceiro ====================================================================================================================
                            (($row_usuario['controle_suporte'] == "Y" or  $row_usuario['suporte_operador_parceiro'] == "Y") and

                                $row_suporte['status_flag'] == "a" and
                                $row_suporte['situacao'] == "em execução" and
                                $tipo_suporte_inloco == "r")
                            // fim - controle_suporte/operador-parceiro ==============================================================================================================
                            or
                            // controle_praca ================================================================================================================
                            (
                                (
                                    ($row_usuario['controle_praca'] == "Y" and $row_suporte['praca'] == $row_usuario['praca'])) and

                                $row_suporte['status_flag'] == "a" and
                                $row_suporte['situacao'] == "em execução" and
                                $tipo_suporte_inloco == "r")
                            // fim - controle_praca ==========================================================================================================
                        ) { ?>

                            <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=editar&acao=Concluir execução&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 120px;">Concluir execução</a>

                        <? } ?>
                        <!-- fim - Concluir execução -->


                        <!-- Concluir validação -->
                        <?
                        if (
                            // controle_suporte/operador-parceiro ====================================================================================================================
                            (($row_usuario['controle_suporte'] == "Y" or  $row_usuario['suporte_operador_parceiro'] == "Y") and

                                $tipo_suporte_inloco == "cs" and
                                $row_suporte['situacao'] == "em validação" and
                                $row_suporte['status'] == "pendente controlador de suporte")
                            // fim - controle_suporte/operador-parceiro ==============================================================================================================
                            or
                            // usuario_envolvido ============================================================================================================================
                            ($row_suporte['id_usuario_envolvido'] == $row_usuario['IdUsuario'] and

                                $tipo_suporte_inloco == "p" and
                                $row_suporte['situacao'] == "em validação" and
                                $row_suporte['status'] == "pendente usuario envolvido")
                            // fim - usuario_envolvido =======================================================================================================================

                        ) { ?>

                            <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=editar&acao=Concluir validação&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 130px;">Concluir validação</a>

                        <? } ?>
                        <!-- fim - Concluir validação -->


                        <!-- Devolver -->
                        <?
                        if (
                            // controle_suporte/operador-parceiro ====================================================================================================================
                            (($row_usuario['controle_suporte'] == "Y" or  $row_usuario['suporte_operador_parceiro'] == "Y") and

                                $tipo_suporte_inloco == "cs" and
                                $row_suporte['situacao'] == "em validação" and
                                $row_suporte['status'] == "pendente controlador de suporte")
                            // fim - controlador_suporte/operador-parceiro ============================================================================================================
                            or
                            // controle_praca ============================================================================================================================
                            (($row_usuario['controle_praca'] == "Y" and $row_suporte['praca'] == $row_usuario['praca']) and

                                $tipo_suporte_inloco == "r" and
                                $row_suporte['situacao'] == "em validação" and
                                $row_suporte['status'] == "pendente usuario responsavel")
                            // fim - controle_praca =======================================================================================================================
                            or
                            // usuario_envolvido ============================================================================================================================
                            ($row_suporte['id_usuario_envolvido'] == $row_usuario['IdUsuario'] and

                                $tipo_suporte_inloco == "p" and
                                $row_suporte['situacao'] == "em validação" and
                                $row_suporte['status'] == "pendente usuario envolvido")
                            // fim - usuario_envolvido =======================================================================================================================

                        ) { ?>

                            <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=editar&acao=Devolver&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 90px;">Devolver</a>

                        <? } ?>
                        <!-- fim - Devolver -->


                        <!-- Encaminhar -->
                        <?
                        if (
                            // controle_suporte/operador-parceiro ====================================================================================================================
                            (($row_usuario['controle_suporte'] == "Y" or  $row_usuario['suporte_operador_parceiro'] == "Y") and

                                $row_suporte['status_flag'] == "a" and

                                (
                                    ($row_usuario['controle_suporte'] == "Y") or
                                    ($row_usuario['suporte_operador_parceiro'] == "Y" and ($row_suporte['praca'] == $row_usuario['praca'] or $tipo_suporte_inloco == "cn" or $tipo_suporte_inloco == "p"))) and

                                ($row_suporte['situacao'] == "analisada" or
                                    $row_suporte['situacao'] == "em execução")

                                and $row_suporte['solicita_suporte'] == "n"
                                and $row_suporte['solicita_visita'] == "n"
                                and $row_suporte['solicita_solicitacao'] == "n")
                            // fim - controle_suporte/operador-parceiro ====================================================================================================================
                            or
                            // controle_praca ================================================================================================================
                            (
                                (
                                    ($row_usuario['controle_praca'] == "Y" and $row_suporte['praca'] == $row_usuario['praca'])) and

                                ($tipo_suporte_inloco == "cs" or
                                    $tipo_suporte_inloco == "cn" or
                                    $tipo_suporte_inloco == "r") and

                                $row_suporte['status_flag'] == "a" and

                                ($row_suporte['situacao'] == "analisada" or
                                    $row_suporte['situacao'] == "em execução") and

                                $row_suporte['solicita_suporte'] == "n" and
                                $row_suporte['solicita_visita'] == "n" and
                                $row_suporte['solicita_solicitacao'] == "n")
                            // fim - controle_praca ==========================================================================================================
                            or
                            // usuario_responsavel ============================================================================================================================
                            ($row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario'] and

                                $row_suporte['status_flag'] == "a" and

                                ($row_suporte['situacao'] == "em execução")

                                and $row_suporte['solicita_suporte'] == "n"
                                and $row_suporte['solicita_visita'] == "n"
                                and $row_suporte['solicita_solicitacao'] == "n")
                            or
                            ($row_suporte['encaminhamento_id'] == $row_usuario['IdUsuario'] and

                                $row_suporte['status_flag'] == "a" and

                                ($row_suporte['situacao'] == "analisada")

                                and $row_suporte['solicita_suporte'] == "n"
                                and $row_suporte['solicita_visita'] == "n"
                                and $row_suporte['solicita_solicitacao'] == "n")
                            // fim - usuario_responsavel =======================================================================================================================
                        ) { ?>

                            <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=<? echo $row_suporte['situacao']; ?>&acao=Encaminhar&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2">Encaminhar</a>

                            <a href="painel.php" target="_blank" id="botao_geral2">Painel</a>

                        <? } ?>
                        <!-- fim - Encaminhar -->


                        <!-- Alterar previsão -->
                        <?
                        if (
                            // controle_suporte/operador-parceiro ====================================================================================================================
                            (($row_usuario['controle_suporte'] == "Y" or  $row_usuario['suporte_operador_parceiro'] == "Y") and

                                $row_suporte['status_flag'] == "a" and

                                ($row_suporte['situacao'] == "analisada" or
                                    $row_suporte['situacao'] == "em execução" or
                                    $row_suporte['situacao'] == "solicitado suporte" or
                                    $row_suporte['situacao'] == "solicitado visita" or
                                    $row_suporte['situacao'] == "encaminhado para solicitação" or
                                    ($row_suporte['situacao'] == "em validação" and $tipo_suporte_inloco == "p")) and

                                ($tipo_suporte_inloco == "r"))
                            // fim - controle_suporte/operador-parceiro ==============================================================================================================
                            or
                            // usuario_responsavel ============================================================================================================================
                            ($row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario'] and

                                $row_suporte['status_flag'] == "a" and

                                ($row_suporte['situacao'] == "em execução" or
                                    $row_suporte['situacao'] == "solicitado suporte" or
                                    $row_suporte['situacao'] == "solicitado visita" or
                                    $row_suporte['situacao'] == "encaminhado para solicitação" or
                                    ($row_suporte['situacao'] == "em validação" and $tipo_suporte_inloco == "p")) and

                                ($tipo_suporte_inloco == "r"))
                            or
                            ($row_suporte['encaminhamento_id'] == $row_usuario['IdUsuario'] and

                                $row_suporte['status_flag'] == "a" and

                                ($row_suporte['situacao'] == "analisada") and

                                ($tipo_suporte_inloco == "r"))
                            // fim - usuario_responsavel =======================================================================================================================
                        ) { ?>

                            <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=editar&acao=Alterar previsão&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 110px;">Alterar previsão</a>

                        <? } ?>
                        <!-- fim - Alterar previsão -->


                        <!-- Encerrar -->
                        <?
                        if (
                            // controle_suporte/operador-parceiro ====================================================================================================================
                            (($row_usuario['controle_suporte'] == "Y" or  $row_usuario['suporte_operador_parceiro'] == "Y") and

                                $row_suporte['status_flag'] == "a" and

                                ($row_suporte['situacao'] == "analisada" or
                                    $row_suporte['situacao'] == "em execução" or
                                    $row_suporte['situacao'] == "solicitado suporte" or
                                    $row_suporte['situacao'] == "solicitado visita" or
                                    $row_suporte['situacao'] == "encaminhado para solicitação" or
                                    ($row_suporte['situacao'] == "em validação" and $tipo_suporte_inloco == "p") or
                                    ($row_suporte['situacao'] == "em validação" and $tipo_suporte_inloco == "r")) and

                                ($row_usuario['controle_suporte'] == "Y" or
                                    ($row_usuario['suporte_operador_parceiro'] == "Y" and
                                        ($row_suporte['praca'] == $row_usuario['praca'] or $tipo_suporte_inloco == "cn" or $tipo_suporte_inloco == "p"))))
                            // fim - controle_suporte/operador-parceiro ==============================================================================================================
                            or
                            // controle_praca ================================================================================================================
                            (($row_usuario['controle_praca'] == "Y" and $row_suporte['praca'] == $row_usuario['praca']) and

                                $row_suporte['status_flag'] == "a" and
                                (
                                    (
                                        ($tipo_suporte_inloco == "cs" or $tipo_suporte_inloco == "cn") and
                                        ($row_suporte['situacao'] == "analisada" or
                                            $row_suporte['situacao'] == "em execução" or
                                            $row_suporte['situacao'] == "solicitado suporte" or
                                            $row_suporte['situacao'] == "solicitado visita" or
                                            $row_suporte['situacao'] == "encaminhado para solicitação" or
                                            ($row_suporte['situacao'] == "em validação" and $tipo_suporte_inloco == "p")))))
                            // fim - controle_praca ==========================================================================================================
                            or
                            // usuario_responsavel ============================================================================================================================
                            ($row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario'] and

                                $row_suporte['status_flag'] == "a" and

                                ($row_suporte['situacao'] == "em execução" or
                                    $row_suporte['situacao'] == "solicitado suporte" or
                                    $row_suporte['situacao'] == "solicitado visita" or
                                    $row_suporte['situacao'] == "encaminhado para solicitação" or
                                    ($row_suporte['situacao'] == "em validação" and $tipo_suporte_inloco == "p")) and

                                (
                                    ($tipo_suporte_inloco == "cs" and $row_reclamacao_encerramento['retorno'] == 0) or
                                    ($tipo_suporte_inloco == "cn" and $row_reclamacao_encerramento['retorno'] == 0) or
                                    $tipo_suporte_inloco == "p"))
                            or
                            ($row_suporte['encaminhamento_id'] == $row_usuario['IdUsuario'] and

                                $row_suporte['status_flag'] == "a" and

                                ($row_suporte['situacao'] == "analisada") and

                                (
                                    ($tipo_suporte_inloco == "cs" and $row_reclamacao_encerramento['retorno'] == 0) or
                                    ($tipo_suporte_inloco == "cn" and $row_reclamacao_encerramento['retorno'] == 0) or
                                    $tipo_suporte_inloco == "p"))
                            // fim - usuario_responsavel =======================================================================================================================
                        ) { ?>

                            <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=editar&acao=Encerrar&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 70px;">Encerrar</a>

                        <? } ?>
                        <!-- fim - Encerrar -->


                        <!-- Cancelar -->
                        <?
                        if (
                            // controle_suporte/operador-parceiro ====================================================================================================================
                            (($row_usuario['controle_suporte'] == "Y" or  $row_usuario['suporte_operador_parceiro'] == "Y") and

                                ($row_suporte['status_flag'] == "a" or
                                    $row_suporte['status_flag'] == "b") and

                                (
                                    ($row_usuario['controle_suporte'] == "Y") or
                                    ($row_usuario['suporte_operador_parceiro'] == "Y" and ($row_suporte['praca'] == $row_usuario['praca'] or $tipo_suporte_inloco == "cn" or $tipo_suporte_inloco == "p"))) and

                                ($row_suporte['situacao'] == "analisada" or
                                    $row_suporte['situacao'] == "em execução" or
                                    $row_suporte['situacao'] == "solicitado suporte" or
                                    $row_suporte['situacao'] == "solicitado visita" or
                                    $row_suporte['situacao'] == "encaminhado para solicitação" or
                                    ($row_suporte['situacao'] == "em validação" and $tipo_suporte_inloco == "p")))
                            // fim - controle_suporte/operador-parceiro ====================================================================================================================
                            or
                            // controle_praca ================================================================================================================
                            (
                                (
                                    ($row_usuario['controle_praca'] == "Y" and $row_suporte['praca'] == $row_usuario['praca'])) and

                                ($row_suporte['status_flag'] == "a" or
                                    $row_suporte['status_flag'] == "b") and

                                ($tipo_suporte_inloco == "cs" or $tipo_suporte_inloco == "cn" or $tipo_suporte_inloco == "r") and

                                ($row_suporte['situacao'] == "analisada" or
                                    $row_suporte['situacao'] == "em execução" or
                                    $row_suporte['situacao'] == "solicitado suporte" or
                                    $row_suporte['situacao'] == "solicitado visita" or
                                    $row_suporte['situacao'] == "encaminhado para solicitação" or
                                    ($row_suporte['situacao'] == "em validação" and $tipo_suporte_inloco == "p")))
                            // fim - controle_praca ==========================================================================================================
                            or
                            // usuario_responsavel ============================================================================================================================
                            ($row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario'] and
                                $tipo_suporte_inloco == "r" and
                                $row_suporte['situacao'] == "em execução")
                            or
                            ($row_suporte['encaminhamento_id'] == $row_usuario['IdUsuario'] and
                                $tipo_suporte_inloco == "r" and
                                $row_suporte['situacao'] == "analisada")
                            // fim - usuario_responsavel =======================================================================================================================
                        ) { ?>

                            <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=editar&acao=Cancelar&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 70px;">Cancelar</a>

                        <? } ?>
                        <!-- fim - Cancelar -->


                        <!-- Encaminhar para solicitação (p) -->
                        <?
                        if (
                            // controle_suporte/operador-parceiro ====================================================================================================================
                            (($row_usuario['controle_suporte'] == "Y" or  $row_usuario['suporte_operador_parceiro'] == "Y") and

                                $tipo_suporte_inloco == "p" and
                                $row_suporte['solicita_solicitacao'] == "n" and

                                ($row_suporte['situacao'] == "em execução") and

                                $row_suporte['status_devolucao'] != "1")
                            // fim - controle_suporte/operador-parceiro ====================================================================================================================
                            or
                            // usuario_responsavel ============================================================================================================================
                            ($row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario'] and

                                $tipo_suporte_inloco == "p" and
                                $row_suporte['solicita_solicitacao'] == "n" and

                                ($row_suporte['situacao'] == "em execução") and

                                $row_suporte['status_devolucao'] != "1")
                            or
                            ($row_suporte['encaminhamento_id'] == $row_usuario['IdUsuario'] and

                                $tipo_suporte_inloco == "p" and
                                $row_suporte['solicita_solicitacao'] == "n" and

                                ($row_suporte['situacao'] == "analisada") and

                                $row_suporte['status_devolucao'] != "1")
                            // fim - usuario_responsavel =======================================================================================================================
                        ) { ?>

                            <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=editar&acao=Encaminhar para solicitação&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 180px;">Encaminhar para solicitação</a>

                            <a href="painel.php" target="_blank" id="botao_geral2">Painel</a>

                        <? } ?>
                        <!-- fim - Encaminhar para solicitação (p) -->


                        <!-- Gerar solicitação/Fechar suporte sem gerar solicitação (p) -->
                        <?
                        if (
                            // controle_suporte/operador-parceiro ====================================================================================================================
                            (($row_usuario['controle_suporte'] == "Y" or  $row_usuario['suporte_operador_parceiro'] == "Y") and

                                $tipo_suporte_inloco == "p" and
                                $row_suporte['solicita_solicitacao'] == "s" and
                                $row_suporte['id_solicitacao'] == ""

                                and $row_suporte['situacao'] == "encaminhado para solicitação")
                            // fim - controle_suporte/operador-parceiro ====================================================================================================================
                            or
                            // usuario_responsavel ============================================================================================================================
                            ($row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario'] and

                                $tipo_suporte_inloco == "p" and
                                $row_suporte['solicita_solicitacao'] == "s" and
                                $row_suporte['id_solicitacao'] == "" and

                                $row_suporte['situacao'] == "encaminhado para solicitação")
                            or
                            ($row_suporte['encaminhamento_id'] == $row_usuario['IdUsuario'] and

                                $tipo_suporte_inloco == "p" and
                                $row_suporte['solicita_solicitacao'] == "s" and
                                $row_suporte['id_solicitacao'] == "" and

                                $row_suporte['situacao'] == "encaminhado para solicitação")
                            // fim - usuario_responsavel =======================================================================================================================
                            or
                            // usuario_envolvido ============================================================================================================================
                            ($row_suporte['id_usuario_envolvido'] == $row_usuario['IdUsuario'] and

                                $tipo_suporte_inloco == "p" and
                                $row_suporte['solicita_solicitacao'] == "s" and
                                $row_suporte['id_solicitacao'] == "")
                            // fim - usuario_envolvido =======================================================================================================================

                        ) { ?>

                            <a href="solicitacao_gerar.php?numero_protocolo=<? echo $row_suporte['id']; ?>" id="botao_geral2" style="width: 130px;">Gerar solicitação</a>

                            <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=editar&acao=Fechar suporte sem gerar solicitação&resposta=e&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 250px;">Fechar suporte sem gerar solicitação</a>

                        <? } ?>
                        <!-- fim - Gerar solicitação/Fechar suporte sem gerar solicitação (p) -->


                        <!-- Solicitar suporte -->
                        <?
                        if (
                            // controle_suporte/operador-parceiro ====================================================================================================================
                            (($row_usuario['controle_suporte'] == "Y" or  $row_usuario['suporte_operador_parceiro'] == "Y") and

                                $tipo_suporte_inloco == "cn" and
                                $row_suporte['solicita_suporte'] == "n" and
                                $row_suporte['solicita_visita'] == "n" and

                                ($row_suporte['situacao'] == "em execução" or
                                    $row_suporte['situacao'] == "em validação"))
                            // fim - controle_suporte/operador-parceiro ====================================================================================================================
                            or
                            // controle_praca ================================================================================================================
                            (
                                (
                                    ($row_usuario['controle_praca'] == "Y" and $row_suporte['praca'] == $row_usuario['praca'])) and

                                $tipo_suporte_inloco == "cn" and
                                $row_suporte['solicita_suporte'] == "n" and
                                $row_suporte['solicita_visita'] == "n" and

                                ($row_suporte['situacao'] == "em execução" or
                                    $row_suporte['situacao'] == "em validação"))
                            // fim - controle_praca ==========================================================================================================
                            or
                            // usuario_responsavel ============================================================================================================================
                            ($row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario'] and

                                ($tipo_suporte_inloco == "cn" and
                                    $row_suporte['solicita_suporte'] == "n" and
                                    $row_suporte['solicita_visita'] == "n" and

                                    ($row_suporte['situacao'] == "em execução" or
                                        $row_suporte['situacao'] == "em validação")))
                            or
                            ($row_suporte['encaminhamento_id'] == $row_usuario['IdUsuario'] and

                                ($tipo_suporte_inloco == "cn" and
                                    $row_suporte['solicita_suporte'] == "n" and
                                    $row_suporte['solicita_visita'] == "n" and

                                    ($row_suporte['situacao'] == "analisada")))
                            // fim - usuario_responsavel =======================================================================================================================
                        ) { ?>

                            <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=editar&acao=Solicitar suporte&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 150px;">Solicitar suporte</a>

                        <? } ?>
                        <!-- fim - Solicitar suporte -->


                        <!-- Solicitar visita -->
                        <?
                        if (
                            // controle_suporte/operador-parceiro ====================================================================================================================
                            (($row_usuario['controle_suporte'] == "Y" or  $row_usuario['suporte_operador_parceiro'] == "Y") and

                                $tipo_suporte_inloco == "cn" and
                                $row_suporte['solicita_suporte'] == "n" and
                                $row_suporte['solicita_visita'] == "n")
                            // fim - controle_suporte/operador-parceiro ====================================================================================================================
                            or
                            // controle_praca ================================================================================================================
                            (
                                (
                                    ($row_usuario['controle_praca'] == "Y" and $row_suporte['praca'] == $row_usuario['praca'])) and

                                $tipo_suporte_inloco == "cn" and
                                $row_suporte['solicita_suporte'] == "n" and
                                $row_suporte['solicita_visita'] == "n")
                            // fim - controle_praca ==========================================================================================================
                            or
                            // usuario_responsavel ============================================================================================================================
                            ($row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario'] and

                                (
                                    ($tipo_suporte_inloco == "cn" and
                                        $row_suporte['solicita_suporte'] == "n" and
                                        $row_suporte['solicita_visita'] == "n" and

                                        ($row_suporte['situacao'] == "em execução" or
                                            $row_suporte['situacao'] == "em validação"))))
                            or
                            ($row_suporte['encaminhamento_id'] == $row_usuario['IdUsuario'] and

                                (
                                    ($tipo_suporte_inloco == "cn" and
                                        $row_suporte['solicita_suporte'] == "n" and
                                        $row_suporte['solicita_visita'] == "n" and

                                        ($row_suporte['situacao'] == "analisada"))))
                            // fim - usuario_responsavel =======================================================================================================================
                        ) { ?>

                            <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=editar&acao=Solicitar visita&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 150px;">Solicitar visita</a>

                        <? } ?>
                        <!-- fim - Solicitar visita -->


                        <!-- Gerar visita -->
                        <?
                        if (
                            // usuario_responsavel ============================================================================================================================
                            ($row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario'] and
                                $tipo_suporte_inloco == "r" and
                                $row_suporte['situacao'] == "em execução" and
                                $row_suporte['praca'] == $row_usuario['praca'] and
                                ($row_empresa_dados['status1'] == "0" and $row_empresa_dados['flag1'] == "0") and
                                ($row_manutencao_dados['status17'] <> "B" and $row_manutencao_dados['status17'] <> "C"))
                            or
                            ($row_suporte['encaminhamento_id'] == $row_usuario['IdUsuario'] and
                                $tipo_suporte_inloco == "r" and
                                $row_suporte['situacao'] == "analisada" and
                                $row_suporte['praca'] == $row_usuario['praca'] and
                                ($row_empresa_dados['status1'] == "0" and $row_empresa_dados['flag1'] == "0") and
                                ($row_manutencao_dados['status17'] <> "B" and $row_manutencao_dados['status17'] <> "C"))
                            // fim - usuario_responsavel =======================================================================================================================
                        ) { ?>

                            <a href="suporte_gerar.php?tipo_suporte=c&cobranca=n&cliente=<?php echo $row_suporte['codigo_empresa']; ?>&contrato=<?php echo $row_suporte['contrato']; ?>&inloco=s&reclamacao_vinculo=<? echo $row_suporte['id']; ?>" id="botao_geral2" style="width: 100px;">Gerar visita</a>

                        <? } ?>
                        <!-- fim - Gerar visita -->


                        <!-- Gerar suporte -->
                        <?
                        if (
                            // usuario_responsavel ============================================================================================================================
                            ($row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario'] and
                                $tipo_suporte_inloco == "r" and
                                $row_suporte['situacao'] == "em execução" and
                                ($row_empresa_dados['status1'] == "0" and $row_empresa_dados['flag1'] == "0") and
                                ($row_manutencao_dados['status17'] <> "B" and $row_manutencao_dados['status17'] <> "C"))
                            or
                            ($row_suporte['encaminhamento_id'] == $row_usuario['IdUsuario'] and
                                $tipo_suporte_inloco == "r" and
                                $row_suporte['situacao'] == "analisada" and
                                ($row_empresa_dados['status1'] == "0" and $row_empresa_dados['flag1'] == "0") and
                                ($row_manutencao_dados['status17'] <> "B" and $row_manutencao_dados['status17'] <> "C"))
                            // fim - usuario_responsavel =======================================================================================================================
                        ) { ?>

                            <a href="suporte_gerar.php?tipo_suporte=c&cobranca=n&cliente=<?php echo $row_suporte['codigo_empresa']; ?>&contrato=<?php echo $row_suporte['contrato']; ?>&inloco=n&reclamacao_vinculo=<? echo $row_suporte['id']; ?>" id="botao_geral2" style="width: 100px;">Gerar suporte</a>

                        <? } ?>
                        <!-- fim - Gerar suporte -->


                        <!-- Agendar visita/Cancelar solicitação de visita -->
                        <?
                        if (
                            // controle_praca ================================================================================================================
                            (
                                (
                                    ($row_usuario['controle_praca'] == "Y" and $row_suporte['praca'] == $row_usuario['praca'])) and

                                $tipo_suporte_inloco == "cn" and

                                $row_suporte['solicita_visita'] == "s")
                            // fim - controle_praca ==========================================================================================================
                            or
                            // usuario_responsavel ============================================================================================================================
                            ($row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario'] and

                                $tipo_suporte_inloco == "cn" and

                                $row_suporte['solicita_visita'] == "s")
                            or
                            ($row_suporte['encaminhamento_id'] == $row_usuario['IdUsuario'] and

                                $tipo_suporte_inloco == "cn" and

                                ($row_suporte['situacao'] == "analisada")

                                and $row_suporte['solicita_visita'] == "s")
                            // fim - usuario_responsavel =======================================================================================================================
                            or
                            // praca ============================================================================================================================
                            ($row_suporte['praca'] == $row_usuario['praca'] and

                                $tipo_suporte_inloco == "cn" and

                                $row_suporte['solicita_visita'] == "s")
                            // fim - praca =======================================================================================================================
                        ) { ?>

                            <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=editar&acao=Agendar visita&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 150px;">Agendar visita</a>

                            <? if ($row_suporte['solicita_visita'] == "s") { ?>

                                <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=editar&acao=Cancelar solicitação de visita&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 180px;">Cancelar solicitação de visita</a>

                            <? } ?>

                        <? } ?>
                        <!-- fim - Agendar visita/Cancelar solicitação de visita -->


                        <!-- Bloquear/Desbloquear - para formulario extra -->
                        <?
                        if (
                            // controle_suporte / operador-parceiro ====================================================================================================================
                            (($row_usuario['controle_suporte'] == "Y" or $row_usuario['suporte_operador_parceiro'] == "Y") and

                            $tipo_suporte_inloco == "cs" and

                            (
                                $row_suporte_formulario_atual['tipo_formulario'] == "Extra" or 
                                $row_suporte_formulario_atual['tipo_formulario'] == "Treinamento" or 
                                $row_suporte_formulario_atual['tipo_formulario'] == "Reclamacao")
                            ) and 

                            $row_suporte['situacao'] <> "em validação"
                            // fim - controle_suporte / operador-parceiro ====================================================================================================================
                        ) { ?>

                            <? if ($row_suporte['status_flag'] == "a") { ?>

                                <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=editar&acao=Bloquear&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 90px;">Bloquear</a>

                            <? } ?>


                            <? if ($row_suporte['status_flag'] == "b") { ?>

                                <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=editar&acao=Desbloquear&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 90px;">Desbloquear</a>

                            <? } ?>

                        <? } ?>
                        <!-- fim - Bloquear/Desbloquear - para formulario extra -->

                        <!-- Questionar ========================================================================================================================================= -->
                        <? if (
                            $row_usuario['controle_suporte'] == "Y" or
                            $row_usuario['suporte_operador_parceiro'] == "Y" or
                            $row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or
                            $row_suporte['id_usuario_envolvido'] == $row_usuario['IdUsuario'] or
                            $row_suporte['praca'] == $row_usuario['praca']
                        ) { ?>

                            <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=&acao=Questionar&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 90px;">Questionar</a>

                        <? } ?>
                        <!-- fim - Questionar =================================================================================================================================== -->

                        <!-- anexos -->
                        <a href="suporte_editar_upload.php?id_suporte=<? echo $row_suporte['id']; ?>&situacao=&acao=Arquivos em anexo&voltar=s&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral" style="width: 150px;">Arquivos em anexo (<? echo $totalRows_arquivos_anexos; ?>)</a>
                        <!-- fim - anexos -->

                        <!-- Liberar anexos ========================================================================================================================================= -->
                        <? if (
                            $row_usuario['administrador_site']=="Y" and 
                            ($tipo_suporte_inloco == "cs") and
                            $row_suporte['prazo_anexo_liberar'] == "n" and
                            $data_atual_segundos > $prazo_anexo_limite_segundos
                        ) { ?>

                            <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=editar&acao=Liberar anexos&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 100px;">Liberar anexos</a>

                        <? } ?>
                        <!-- fim - Liberar anexos =================================================================================================================================== -->

                        <!-- Bloquear anexos ========================================================================================================================================= -->
                        <? if (
                            $row_usuario['administrador_site']=="Y" and 
                            ($tipo_suporte_inloco == "cs") and
                            $row_suporte['prazo_anexo_liberar'] == "s" and
                            $data_atual_segundos > $prazo_anexo_limite_segundos 
                        ) { ?>

                            <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=editar&acao=Bloquear anexos&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 110px;">Bloquear anexos</a>

                        <? } ?>
                        <!-- fim - Bloquear anexos =================================================================================================================================== -->

                        <!-- Contato ========================================================================================================================================= -->
                        <?
                        if (
                            // controle_suporte/operador-parceiro ====================================================================================================================
                            (($row_usuario['controle_suporte'] == "Y" or  $row_usuario['suporte_operador_parceiro'] == "Y") and

                                $row_suporte['status_flag'] == "a")
                            // fim - controle_suporte/operador-parceiro ====================================================================================================================
                            or
                            // controle_praca ============================================================================================================================
                            (($row_usuario['controle_praca'] == "Y" and $row_suporte['praca'] == $row_usuario['praca']) and

                                $row_suporte['status_flag'] == "a")
                            // fim - controle_praca =======================================================================================================================
                            or
                            // usuario_responsavel ============================================================================================================================
                            ($row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario'] and

                                $row_suporte['status_flag'] == "a")
                            or
                            ($row_suporte['encaminhamento_id'] == $row_usuario['IdUsuario'] and

                                $row_suporte['status_flag'] == "a")
                            // fim - usuario_responsavel =======================================================================================================================
                        ) { ?>

                            <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=editar&acao=Contato&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 90px;">Contato (<? echo $row_suporte_contato['retorno']; ?>)</a>

                        <? } ?>
                        <!-- fim - Contato =================================================================================================================================== -->

                        <!-- Registrar reclamação ========================================================================================================================================= -->
                        <? if (
                            ($tipo_suporte_inloco == "cs" or $tipo_suporte_inloco == "cn" or $tipo_suporte_inloco == "p")
                        ) { ?>

                            <a href="suporte_gerar.php?tipo_suporte=r&inloco=n&cobranca=n&cliente=<? echo $row_suporte['codigo_empresa']; ?>6&contrato=<? echo $row_suporte['contrato']; ?>&reclamacao_suporte=<? echo $row_suporte['id']; ?>" id="botao_geral2">Registrar reclamação</a>

                        <? } ?>
                        <!-- fim - Registrar reclamação =================================================================================================================================== -->

                    </td>

                    <td align="right" style="color:#F00; font-weight:bold;">

                        <!-- Solicitar suporte - sim / nao -->
                        <?
                        if (
                            // controle_suporte/operador-parceiro ====================================================================================================================
                            (($row_usuario['controle_suporte'] == "Y" or $row_usuario['suporte_operador_parceiro'] == "Y") and

                                $tipo_suporte_inloco == "cn" and
                                $row_suporte['solicita_suporte'] == "s")
                            // fim - controle de suporte/operador-parceiro ===========================================================================================================
                        ) { ?>

                            <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=<? echo $row_suporte['situacao']; ?>&acao=Solicitar suporte&resposta=nao&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2_dir" style="width: 70px;">Não</a>

                            <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=<? echo $row_suporte['situacao']; ?>&acao=Solicitar suporte&resposta=sim&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2_dir" style="width: 70px;">Sim</a>

                        <? } ?>
                        <!-- fim - Solicitar suporte - sim / nao -->


                        <!-- Aceitar / Recusar ============================================================================================================================== -->
                        <?
                        if (
                            // analisada ----------------------------------------------------------------------------
                            $row_suporte['situacao'] == "analisada" and (

                                ($row_suporte['status'] == "encaminhada para usuario responsavel" and
                                    ($row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario']) and
                                    ($row_suporte['status_recusa'] != "1")) or ($row_suporte['status'] == "pendente usuario responsavel" and
                                    ($row_suporte['encaminhamento_id'] == $row_usuario['IdUsuario']) and
                                    ($row_suporte['status_recusa'] == "1")))
                            // fim - analisada ----------------------------------------------------------------------------
                            or (

                                ($row_suporte['status'] == "devolvida para usuario responsavel" and $row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario']))

                        ) { ?>

                            <div style="float:right; margin-left: 5px;">
                                <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=<? echo $row_suporte['situacao']; ?>&acao=Aceitar&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2">Aceitar</a>

                                <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=<? echo $row_suporte['situacao']; ?>&acao=Recusar&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2">Recusar</a>
                            </div>

                        <? } ?>
                        <!-- fim - Aceitar / Recusar ======================================================================================================================== -->


                        <!-- Mensagens ==================================================================================================================================== -->
                        <? if (
                            $row_suporte['situacao'] == "analisada" and

                            $row_suporte['status'] == "encaminhada para usuario responsavel" and
                            $row_suporte['status_recusa'] != "1"
                        ) { ?>

                            <div id="texto_botao_geral">Aguardando aceitação do usuário responsável</div>

                        <? } ?>


                        <? if (
                            $row_suporte['situacao'] == "analisada" and

                            $row_suporte['status'] == "pendente usuario responsavel" and
                            $row_suporte['status_recusa'] == "1"
                        ) { ?>

                            <div id="texto_botao_geral">Aguardando aceitação de recusa</div>

                        <? } ?>

                        <?
                        if (
                            $row_suporte['status'] == "devolvida para usuario responsavel"
                        ) { ?>

                            <div id="texto_botao_geral">Aguardando aceitação de devolução</div>

                        <? } ?>

                        <?
                        if (
                            $row_suporte['status'] == "pendente controlador de suporte" and
                            ($row_suporte['solicita_suporte'] == "s")
                        ) { ?>
                            <div id="texto_botao_geral">Aguardando aceitação de controlador de suporte
                                <?
                                if (
                                    // controle_suporte/operador-parceiro ====================================================================================================================
                                    (($row_usuario['controle_suporte'] == "Y" or  $row_usuario['suporte_operador_parceiro'] == "Y") and

                                        $row_suporte['id_usuario_responsavel'] != $row_usuario['IdUsuario'])
                                    // fim - controle de suporte/operador-parceiro ===========================================================================================================
                                ) { ?>
                                    - Aceitar suporte ?
                                <? } ?>
                            </div>

                        <? } ?>
                        <!-- fim - Mensagens ============================================================================================================================== -->


                    </td>
                </tr>
            </table>
        </div>
    <? } ?>

    <?
    if (
        $row_suporte['atendimento'] <> "SolCan" and  
        $row_suporte['atendimento'] <> "FinAte" and 
        (

            // controle_suporte/operador-parceiro ====================================================================================================================
            (($row_usuario['controle_suporte'] == "Y" or  $row_usuario['suporte_operador_parceiro'] == "Y") and

                $row_suporte['status_flag'] == "a" and

                ($row_suporte['situacao'] == "analisada" or
                    $row_suporte['situacao'] == "em execução" or
                    $row_suporte['situacao'] == "solicitado suporte" or
                    $row_suporte['situacao'] == "solicitado visita" or
                    $row_suporte['situacao'] == "encaminhado para solicitação" or
                    ($row_suporte['situacao'] == "em validação" and $tipo_suporte_inloco == "p") or
                    ($row_suporte['situacao'] == "em validação" and $tipo_suporte_inloco == "r")) and

                ($row_usuario['controle_suporte'] == "Y" or
                    ($row_usuario['suporte_operador_parceiro'] == "Y" and
                        ($row_suporte['praca'] == $row_usuario['praca'] or $tipo_suporte_inloco == "cn" or $tipo_suporte_inloco == "p"))))
            // fim - controle_suporte/operador-parceiro ==============================================================================================================
            or
            // controle_praca ================================================================================================================
            (($row_usuario['controle_praca'] == "Y" and $row_suporte['praca'] == $row_usuario['praca']) and

                $row_suporte['status_flag'] == "a" and
                (
                    (
                        ($tipo_suporte_inloco == "cs" or $tipo_suporte_inloco == "cn") and
                        ($row_suporte['situacao'] == "analisada" or
                            $row_suporte['situacao'] == "em execução" or
                            $row_suporte['situacao'] == "solicitado suporte" or
                            $row_suporte['situacao'] == "solicitado visita" or
                            $row_suporte['situacao'] == "encaminhado para solicitação" or
                            ($row_suporte['situacao'] == "em validação" and $tipo_suporte_inloco == "p")))))
            // fim - controle_praca ==========================================================================================================
            or
            // usuario_responsavel ============================================================================================================================
            ($row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario'] and

                $row_suporte['status_flag'] == "a" and

                ($row_suporte['situacao'] == "em execução" or
                    $row_suporte['situacao'] == "solicitado suporte" or
                    $row_suporte['situacao'] == "solicitado visita" or
                    $row_suporte['situacao'] == "encaminhado para solicitação" or
                    ($row_suporte['situacao'] == "em validação" and $tipo_suporte_inloco == "p")) and

                (
                    ($tipo_suporte_inloco == "cs" and $row_reclamacao_encerramento['retorno'] == 0) or
                    ($tipo_suporte_inloco == "cn" and $row_reclamacao_encerramento['retorno'] == 0) or
                    $tipo_suporte_inloco == "p"))
            or
            ($row_suporte['encaminhamento_id'] == $row_usuario['IdUsuario'] and

                $row_suporte['status_flag'] == "a" and

                ($row_suporte['situacao'] == "analisada") and

                (
                    ($tipo_suporte_inloco == "cs" and $row_reclamacao_encerramento['retorno'] == 0) or
                    ($tipo_suporte_inloco == "cn" and $row_reclamacao_encerramento['retorno'] == 0) or
                    $tipo_suporte_inloco == "p"))
            // fim - usuario_responsavel =======================================================================================================================

        )
    ) { ?>
        <div class="div_solicitacao_linhas4" id="botoes">
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td style="text-align:left">

                        <!-- Iniciar atendimento ========================================================================================================================================= -->
                        <? if ($row_suporte['atendimento'] == NULL) { ?>
                            <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=&acao=Iniciar atendimento&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 120px;">Iniciar Atendimento</a>
                        <? } ?>
                        <!-- fim - Iniciar atendimento =================================================================================================================================== -->

                        <!-- Reagendar atendimento ========================================================================================================================================= -->
                        <? 
                        if (
                            $row_suporte['atendimento'] == NULL or 
                            $row_suporte['atendimento'] == "IniAte" or 
                            $row_suporte['atendimento'] == "SolRea" or 
                            $row_suporte['atendimento'] == "PenAte"
                        ) { 
                        ?>
                            <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=&acao=Reagendar atendimento&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 140px;">Reagendar Atendimento</a>
                        <? } ?>
                        <!-- fim - Reagendar atendimento =================================================================================================================================== -->

                        <!-- Cancelar atendimento ========================================================================================================================================= -->
                        <? 
                        if (
                            $row_suporte['atendimento'] == NULL or 
                            $row_suporte['atendimento'] == "IniAte" or 
                            $row_suporte['atendimento'] == "SolRea" or 
                            $row_suporte['atendimento'] == "PenAte"
                        ) { 
                        ?>
                            <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=&acao=Cancelar atendimento&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 130px;">Cancelar Atendimento</a>
                        <? } ?>
                        <!-- fim - Cancelar atendimento =================================================================================================================================== -->

                        <!-- Finalizar atendimento ========================================================================================================================================= -->
                        <? 
                        if (
                            $row_suporte['atendimento'] == "IniAte" or 
                            $row_suporte['atendimento'] == "SolRea" or 
                            $row_suporte['atendimento'] == "PenAte"
                        ) { 
                        ?>
                            <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=&acao=Finalizar atendimento&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 120px;">Finalizar Atendimento</a>
                        <? } ?>
                        <!-- fim - Finalizar atendimento =================================================================================================================================== -->

                    </td>
                </tr>
            </table>
        </div>
    <? } ?>


    <? if ($row_suporte['situacao'] == "solucionada" or $row_suporte['situacao'] == "cancelada") { ?>
        <div class="div_solicitacao_linhas4" id="botoes">
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td style="text-align:left">

                        <!-- Estornar -->
                        <?
                        if (
                            ($tipo_suporte_inloco == "cs" and
                                (
                                    ($row_usuario['controle_suporte'] == "Y") or
                                    ($row_suporte['praca'] == $row_usuario['praca'] and $row_usuario['controle_praca'] == "Y"))) or ($tipo_suporte_inloco == "cn" and
                                $row_usuario['controle_suporte'] == "Y") or ($tipo_suporte_inloco == "p" and
                                $row_usuario['controle_suporte'] == "Y")
                        ) {
                        ?>

                            <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=editar&acao=Estornar&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 70px;">Estornar</a>

                        <? } ?>
                        <!-- fim - Estornar -->

                        <!-- Questionar ========================================================================================================================================= -->
                        <? if (
                            ($row_usuario['controle_suporte'] == "Y" or  $row_usuario['suporte_operador_parceiro'] == "Y") or
                            $row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or
                            $row_suporte['id_usuario_envolvido'] == $row_usuario['IdUsuario']
                        ) { ?>

                            <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=editar&acao=Questionar&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 90px;">Questionar</a>

                        <? } ?>
                        <!-- fim - Questionar =================================================================================================================================== -->

						<!-- anexos -->
                        <a href="suporte_editar_upload.php?id_suporte=<? echo $row_suporte['id']; ?>&situacao=&acao=Arquivos em anexo&voltar=s&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral" style="width: 150px;">Arquivos em anexo (<? echo $totalRows_arquivos_anexos; ?>)</a>
						<!-- fim - anexos -->

                        <!-- Liberar anexos ========================================================================================================================================= -->
                        <? if (
                            $row_suporte['situacao'] == "solucionada" and 

                            $row_usuario['administrador_site']=="Y" and 
                            ($tipo_suporte_inloco == "cs") and
                            $row_suporte['prazo_anexo_liberar'] == "n" and
                            $data_atual_segundos > $prazo_anexo_limite_segundos
                        ) { ?>

                            <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=editar&acao=Liberar anexos&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 100px;">Liberar anexos</a>

                        <? } ?>
                        <!-- fim - Liberar anexos =================================================================================================================================== -->

                        <!-- Bloquear anexos ========================================================================================================================================= -->
                        <? if (
                            $row_suporte['situacao'] == "solucionada" and 
                            
                            $row_usuario['administrador_site']=="Y" and 
                            ($tipo_suporte_inloco == "cs") and
                            $row_suporte['prazo_anexo_liberar'] == "s" and
                            $data_atual_segundos > $prazo_anexo_limite_segundos
                        ) { ?>

                            <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=editar&acao=Bloquear anexos&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 110px;">Bloquear anexos</a>

                        <? } ?>
                        <!-- fim - Bloquear anexos =================================================================================================================================== -->

                    </td>
                </tr>
            </table>
        </div>
    <? } ?>
    <!-- fim - botões -->


    <!-- solicitações -->
    <? if ($row_suporte['id_solicitacao'] != "") { ?>
        <div class="div_solicitacao_linhas4">
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td style="text-align:left">
                        <span class="label_solicitacao">Núm. Solicitação: </span><? echo $row_suporte['id_solicitacao']; ?> -
                        <a href="solicitacao_editar.php?id_solicitacao=<?php echo $row_suporte['id_solicitacao']; ?>&padrao=sim" target="_blank" style="text-decoration: none; color: #000">
                            Acessar
                        </a>
                    </td>
                </tr>
            </table>
        </div>
    <? } ?>
    <!-- fim - solicitações -->

    <!-- Bloqueado -->
    <? if ($row_suporte['status_flag'] == 'b') { ?>
        <div class="div_solicitacao_linhas4">
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td style="text-align: left">
                        <? if ($row_suporte_formulario_atual['tipo_formulario'] == "Extra") { ?>
                            <span style="font-weight: bold; color: #F00;">Suporte 'Extra' bloqueado. Aguardando ordem de serviço e desbloqueio por parte do controlador de suporte.</span>
                        <? } else if ($row_suporte_formulario_atual['tipo_formulario'] == "Treinamento") { ?>
                            <span style="font-weight: bold; color: #F00;">Suporte 'Treinamento' bloqueado. Aguardando ordem de serviço e desbloqueio por parte do controlador de suporte.</span>
                        <? } else { ?>
                            <span style="font-weight: bold; color: #F00;">Suporte bloqueado. Aguardando desbloqueio por parte do controlador de suporte.</span>
                        <? } ?>
                    </td>
                </tr>
            </table>
        </div>
    <? } ?>
    <!-- fim - Bloqueado -->


    <!-- reclamacao_vinculo -->
    <? if ($totalRows_reclamacao_vinculo > 0) { ?>
        <div class="div_solicitacao_linhas4">
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td style="text-align: left">Suportes vinculados: </span>
                        <!-- tabela -->
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="padding: 5px;">
                            <tr bgcolor="#F1F1F1">
                                <td style="padding: 5px;" width="70"><strong>Número</strong></td>
                                <td style="padding: 5px;" width="180"><strong>Data</strong></td>
                                <td style="padding: 5px;" width="100"><strong>Status</strong></td>
                                <td style="padding: 5px;" width="300"><strong>Título</strong></td>
                                <td style="padding: 5px;"><strong>Ações</strong></td>
                            </tr>

                            <? $contador_reclamacao_vinculo = 0; ?>

                            <?php do { ?>
                                <tr bgcolor="<? if (($contador_reclamacao_vinculo % 2) == 1) {
                                                    echo "#F1F1F1";
                                                } else {
                                                    echo "#FFFFFF";
                                                } ?>">
                                    <td style="padding: 5px;"><?php echo $row_reclamacao_vinculo['id']; ?></td>
                                    <td style="padding: 5px;"><? echo date('d-m-Y  H:i', strtotime($row_reclamacao_vinculo['data_suporte'])); ?></td>
                                    <td style="padding: 5px;"><?php echo $row_reclamacao_vinculo['situacao']; ?></td>
                                    <td style="padding: 5px;"><?php echo $row_reclamacao_vinculo['titulo']; ?></td>
                                    <td style="padding: 5px;"><a href="suporte_editar.php?id_suporte=<? echo $row_reclamacao_vinculo['id']; ?>&padrao=sim" target="_blank" id="botao_geral2" style="width: 70px;">Abrir</a></td>
                                </tr>
                                <? $contador_reclamacao_vinculo = $contador_reclamacao_vinculo + 1; ?>
                            <?php } while ($row_reclamacao_vinculo = mysql_fetch_assoc($reclamacao_vinculo)); ?>

                        </table>
                        <!-- fim - tabela -->
                    </td>
                </tr>
            </table>
        </div>
    <? } ?>
    <!-- fim - reclamacao_vinculo -->


    <? if ($tipo_suporte_inloco == "cs" or $tipo_suporte_inloco == "cn" or $tipo_suporte_inloco == "p") { ?>
        <!-- formulários de atendimento -->
        <? if ($totalRows_suporte_formulario_listar > 0) { ?>
            <div class="div_solicitacao_linhas4">
                <table cellspacing="0" cellpadding="0" width="100%">
                    <tr>
                        <td style="text-align: left">
                            <span class="label_solicitacao">Formulários de visitas gerados até o momento (<? echo $totalRows_suporte_formulario_listar2; ?>): </span>
                            <!-- tabela -->
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="padding: 5px;">

                                <thead>
                                    <tr bgcolor="#F1F1F1">
                                        <td style="padding: 5px;" width="70"><strong>Número</strong></td>
                                        <td style="padding: 5px;" width="140"><strong>Data de criação</strong></td>
                                        <td style="padding: 5px;" width="200"><strong>Tipo de formulário</strong></td>
                                        <td style="padding: 5px;" width="130"><strong>Suporte vinculado</strong></td>
                                        <td style="padding: 5px;" width="150"><strong>Situação</strong></td>
                                        <td style="padding: 5px;"><strong>Visualizar</strong></td>
                                    </tr>
                                </thead>

                                <tbody>

                                    <? $contador_suporte_formulario_listar = 0; ?>

                                    <? $contador_form_extra = 0; ?>
                                    <? $contador_form_treinamento = 0; ?>
                                    <? $contador_form_manutencao = 0; ?>
                                    <? $contador_form_cobranca = 0; ?>
                                    <? $contador_form_reclamacao = 0; ?>
                                    <?php do { ?>

                                        <? $contador_suporte_formulario_listar = $contador_suporte_formulario_listar + 1; ?>

                                        <? if ($row_suporte_formulario_listar['tipo_formulario'] == "Extra") { ?>
                                            <? $contador_form_extra = $contador_form_extra + 1; ?>
                                        <? } ?>

                                        <? if ($row_suporte_formulario_listar['tipo_formulario'] == "Treinamento") { ?>
                                            <? $contador_form_treinamento = $contador_form_treinamento + 1; ?>
                                        <? } ?>

                                        <? if ($row_suporte_formulario_listar['tipo_formulario'] == "Manutencao") { ?>
                                            <? $contador_form_manutencao = $contador_form_manutencao + 1; ?>
                                        <? } ?>

                                        <? if ($row_suporte_formulario_listar['tipo_formulario'] == "Cobranca") { ?>
                                            <? $contador_form_cobranca = $contador_form_cobranca + 1; ?>
                                        <? } ?>

                                        <? if ($row_suporte_formulario_listar['tipo_formulario'] == "Reclamacao") { ?>
                                            <? $contador_form_reclamacao = $contador_form_reclamacao + 1; ?>
                                        <? } ?>

                                        <tr bgcolor="<? if (($contador_suporte_formulario_listar % 2) == 1) {
                                                            echo "#FFFFFF";
                                                        } else {
                                                            echo "#F1F1F1";
                                                        } ?>">
                                            <td style="padding: 5px;"><?php echo $row_suporte_formulario_listar['IdFormulario']; ?></td>
                                            <td style="padding: 5px;"><? echo date('d-m-Y  H:i:s', strtotime($row_suporte_formulario_listar['data'])); ?></td>
                                            <td style="padding: 5px;">
                                                <?php
                                                if ($row_suporte_formulario_listar['tipo_formulario'] == "Manutencao") {
                                                    echo "Manutenção";
                                                } else if ($row_suporte_formulario_listar['tipo_formulario'] == "Cobranca") {
                                                    echo "Cobrança";
                                                } else {
                                                    echo $row_suporte_formulario_listar['tipo_formulario'];
                                                }
                                                ?>
                                                <?php
                                                if ($row_suporte_formulario_listar['visita_bonus'] == "s") {
                                                    echo " (bônus)";
                                                }
                                                ?>
                                            </td>
                                            <td style="padding: 5px;" width="130">
                                                <?php if ($row_suporte_formulario_listar['id_suporte'] == $row_suporte['id']) { ?>
                                                    <span style="font-weight: bold"><? echo $row_suporte_formulario_listar['id_suporte']; ?></span>
                                                <? } else { ?>
                                                    <? echo $row_suporte_formulario_listar['id_suporte']; ?>
                                                <? } ?>
                                            </td>
                                            <td style="padding: 5px;"><?php echo $row_suporte_formulario_listar['situacao']; ?></td>
                                            <td style="padding: 5px;">
                                                <? if ($row_suporte_formulario_listar['situacao'] != "encerrado") { ?>

                                                    <?
                                                    if (
                                                        $row_suporte_formulario_listar['tipo_formulario'] == "Extra" and
                                                        ($row_suporte['status_flag'] == "a" or $row_suporte['status_flag'] == "b")
                                                    ) {
                                                    ?>
                                                        <a href="suporte_formulario.php?codigo_empresa=<?php echo $row_suporte_formulario_listar['codigo_empresa']; ?>&contrato=<?php echo $row_suporte_formulario_listar['contrato']; ?>&IdFormulario=<?php echo $row_suporte_formulario_listar['IdFormulario']; ?>" target="_blank"><img src="imagens/visualizar.png" border="0" /></a>
                                                    <? } ?>

                                                    <?
                                                    if (
                                                        $row_suporte_formulario_listar['tipo_formulario'] == "Treinamento" and
                                                        ($row_suporte['status_flag'] == "a" or $row_suporte['status_flag'] == "b")
                                                    ) {
                                                    ?>
                                                        <a href="suporte_formulario.php?codigo_empresa=<?php echo $row_suporte_formulario_listar['codigo_empresa']; ?>&contrato=<?php echo $row_suporte_formulario_listar['contrato']; ?>&IdFormulario=<?php echo $row_suporte_formulario_listar['IdFormulario']; ?>" target="_blank"><img src="imagens/visualizar.png" border="0" /></a>
                                                    <? } ?>

                                                    <? if ($row_suporte_formulario_listar['tipo_formulario'] == "Manutencao" and $row_suporte['status_flag'] == "a") { ?>
                                                        <a href="suporte_formulario.php?codigo_empresa=<?php echo $row_suporte_formulario_listar['codigo_empresa']; ?>&contrato=<?php echo $row_suporte_formulario_listar['contrato']; ?>&IdFormulario=<?php echo $row_suporte_formulario_listar['IdFormulario']; ?>" target="_blank"><img src="imagens/visualizar.png" border="0" /></a>
                                                    <? } ?>

                                                    <? if ($row_suporte_formulario_listar['tipo_formulario'] == "Cobranca" and $row_suporte['status_flag'] == "a") { ?>
                                                        <a href="suporte_formulario.php?codigo_empresa=<?php echo $row_suporte_formulario_listar['codigo_empresa']; ?>&contrato=<?php echo $row_suporte_formulario_listar['contrato']; ?>&IdFormulario=<?php echo $row_suporte_formulario_listar['IdFormulario']; ?>" target="_blank"><img src="imagens/visualizar.png" border="0" /></a>
                                                    <? } ?>

                                                    <? if ($row_suporte_formulario_listar['tipo_formulario'] == "Reclamacao" and $row_suporte['status_flag'] == "a") { ?>
                                                        <a href="suporte_formulario.php?codigo_empresa=<?php echo $row_suporte_formulario_listar['codigo_empresa']; ?>&contrato=<?php echo $row_suporte_formulario_listar['contrato']; ?>&IdFormulario=<?php echo $row_suporte_formulario_listar['IdFormulario']; ?>" target="_blank"><img src="imagens/visualizar.png" border="0" /></a>
                                                    <? } ?>

                                                <? } ?>
                                            </td>
                                        </tr>

                                    <?php } while ($row_suporte_formulario_listar = mysql_fetch_assoc($suporte_formulario_listar)); ?>

                                    <!-- mostrar/ocultar formulario -->
                                    <? if ($totalRows_suporte_formulario_listar2 > 3) { ?>
                                        <tr style="background-color: #DDD;">
                                            <td colspan="6" style="padding: 5px;">
                                                <? if ($totalRows_suporte_formulario_listar == $totalRows_suporte_formulario_listar2) { ?>
                                                    <a href="suporte_editar.php?id_suporte=<? echo $row_suporte['id']; ?>&padrao=sim" style="color: #000; font-weight: bold; font-size:12px;">
                                                        <div>Mostrar somente mais recentes</div>
                                                    </a>
                                                <? } else { ?>
                                                    <a href="suporte_editar.php?id_suporte=<? echo $row_suporte['id']; ?>&suporte_formulario_listar_limite=<? echo $totalRows_suporte_formulario_listar2; ?>&padrao=sim" style="color: #000; font-weight: bold; font-size:12px; text-decoration: none;">
                                                        <div>Mostrar todos</div>
                                                    </a>
                                                <? } ?>
                                            </td>
                                        </tr>
                                    <? } ?>
                                    <!-- fim - mostrar/ocultar formulario -->

                                </tbody>

                            </table>
                            <!-- fim - tabela -->
                        </td>
                    </tr>
                </table>
            </div>
        <? } ?>
        <!-- formulários de atendimento -->
    <? } ?>


    <!-- observacao -->
    <div class="div_solicitacao_linhas4">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td style="text-align: left">
                    <span class="label_solicitacao">Observação: </span>
                    <br>
                    <?php echo $row_suporte['observacao']; ?>
                    <?php if ($row_suporte['status_flag'] == "a") { ?>
                        <?php if ($row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or ($row_usuario['controle_suporte'] == "Y" or  $row_usuario['suporte_operador_parceiro'] == "Y")) { ?>
                            <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=editar&acao=Alterar observação&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox">
                                <img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar observação"></a>
                        <? } ?>
                    <? } ?>
                </td>
            </tr>
        </table>
    </div>
    <!-- fim - observacao -->

    <!-- agenda -->
    <? if ($totalRows_agenda > 0) { ?>
        <div class="div_solicitacao_linhas4">
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td style="text-align: left">
                        <span class="label_solicitacao">Agenda: </span>
                        <!-- tabela -->
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="padding: 5px;">
                            <tr bgcolor="#F1F1F1">
                                <td style="padding: 5px;" width="70"><strong>Número</strong></td>
                                <td style="padding: 5px;" width="180"><strong>Data</strong></td>
                                <td style="padding: 5px;" width="100"><strong>Status</strong></td>
                                <td style="padding: 5px;" width="100"><strong>Ações</strong></td>
                                <td style="padding: 5px;"><strong>Descrição</strong></td>
                            </tr>

                            <? $contador_agenda = 0; ?>

                            <?php do { ?>
                                <tr bgcolor="<? if (($contador_agenda % 2) == 1) {
                                                    echo "#F1F1F1";
                                                } else {
                                                    echo "#FFFFFF";
                                                } ?>">
                                    <td style="padding: 5px;"><?php echo $row_agenda['id_agenda']; ?></td>
                                    <td style="padding: 5px;">
                                        Início: <? echo date('d-m-Y  H:i:s', strtotime($row_agenda['data_inicio'])); ?>
                                        <br>
                                        Fim:&nbsp;&nbsp;&nbsp;&nbsp; <? echo date('d-m-Y  H:i:s', strtotime($row_agenda['data'])); ?>
                                    </td>
                                    <td style="padding: 5px;">
                                        <?php if ($row_agenda['status'] == "a") {
                                            echo "Agendado";
                                        } ?>
                                        <?php if ($row_agenda['status'] == "f") {
                                            echo "Finalizado";
                                        } ?>
                                        <?php if ($row_agenda['status'] == "c") {
                                            echo "Cancelado";
                                        } ?>
                                    </td>

                                    <td style="padding: 5px;">

                                        <?php if ($row_agenda['status'] == "a") { ?>

                                            <!-- botoes -->
                                            <div id="botoes">

                                                <?
                                                if (
                                                    // controle_suporte/operador-parceiro ====================================================================================================================
                                                    (($row_usuario['controle_suporte'] == "Y" or  $row_usuario['suporte_operador_parceiro'] == "Y") and

                                                        $tipo_suporte_inloco == "cs" and

                                                        ($row_suporte['status_flag'] == "a" or
                                                            $row_suporte['status_flag'] == "b") and

                                                        ($row_suporte['situacao'] == "analisada" or
                                                            $row_suporte['situacao'] == "em execução"))
                                                    // fim - controle_suporte/operador-parceiro ====================================================================================================================
                                                    or
                                                    // controle_praca ================================================================================================================
                                                    (
                                                        (
                                                            ($row_usuario['controle_praca'] == "Y" and $row_suporte['praca'] == $row_usuario['praca'])) and

                                                        $tipo_suporte_inloco == "cs" and

                                                        ($row_suporte['status_flag'] == "a" or
                                                            $row_suporte['status_flag'] == "b") and

                                                        ($row_suporte['situacao'] == "analisada" or
                                                            $row_suporte['situacao'] == "em execução"))
                                                    // fim - controle_praca ==========================================================================================================
                                                    or
                                                    // usuario_responsavel ============================================================================================================================
                                                    ($row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario'] and

                                                        $tipo_suporte_inloco == "cs" and

                                                        ($row_suporte['status_flag'] == "a" or
                                                            $row_suporte['status_flag'] == "b") and

                                                        ($row_suporte['situacao'] == "em execução"))
                                                    or
                                                    ($row_suporte['encaminhamento_id'] == $row_usuario['IdUsuario'] and

                                                        $tipo_suporte_inloco == "cs" and

                                                        ($row_suporte['status_flag'] == "a" or
                                                            $row_suporte['status_flag'] == "b") and

                                                        ($row_suporte['situacao'] == "analisada"))
                                                    // fim - usuario_responsavel =======================================================================================================================
                                                    or
                                                    // praca ============================================================================================================================
                                                    ($row_suporte['praca'] == $row_usuario['praca'] and

                                                        $tipo_suporte_inloco == "cs" and

                                                        ($row_suporte['status_flag'] == "a" or
                                                            $row_suporte['status_flag'] == "b") and

                                                        ($row_suporte['situacao'] == "analisada" or
                                                            $row_suporte['situacao'] == "em execução"))
                                                    // fim - praca =======================================================================================================================

                                                ) { ?>

                                                    <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=editar&acao=Reagendar&resposta=&id_agenda=<? echo $row_agenda['id_agenda']; ?>&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 70px;">Reagendar</a>

                                                <? } ?>

                                            </div>
                                            <!-- fim - botoes -->

                                        <? } ?>

                                    </td>

                                    <td style="padding: 5px;">
                                        <?php echo $row_agenda['descricao']; ?>
                                    </td>
                                </tr>
                                <? $contador_agenda = $contador_agenda + 1; ?>
                            <?php } while ($row_agenda = mysql_fetch_assoc($agenda)); ?>

                        </table>
                        <!-- fim - tabela -->
                    </td>
                </tr>
            </table>
        </div>
    <? } ?>
    <!-- fim - agenda -->

    <?php if ($row_suporte['reclamacao_vinculo'] > 0) { ?>
        <div class="div_solicitacao_linhas3">
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td style="text-align: left; vertical-align: top;">
                        <span class="label_solicitacao">Reclamação vinculada:</span>
                        <a href="suporte_editar.php?id_suporte=<? echo $row_suporte['reclamacao_vinculo']; ?>&padrao=sim"><strong><? echo $row_suporte['reclamacao_vinculo']; ?></strong> - Acessar</a>
                    </td>
                </tr>
            </table>
        </div>
    <? } ?>

    <!-- reclamacao_suporte -->
    <? if ($totalRows_reclamacao_suporte > 0) { ?>
        <div class="div_solicitacao_linhas4">
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td style="text-align: left">Reclamações vinculadas: </span>
                        <!-- tabela -->
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="padding: 5px;">
                            <tr bgcolor="#F1F1F1">
                                <td style="padding: 5px;" width="70"><strong>Número</strong></td>
                                <td style="padding: 5px;" width="180"><strong>Data</strong></td>
                                <td style="padding: 5px;" width="100"><strong>Status</strong></td>
                                <td style="padding: 5px;" width="300"><strong>Título</strong></td>
                                <td style="padding: 5px;"><strong>Ações</strong></td>
                            </tr>

                            <? $contador_reclamacao_suporte = 0; ?>

                            <?php do { ?>
                                <tr bgcolor="<? if (($contador_reclamacao_suporte % 2) == 1) {
                                                    echo "#F1F1F1";
                                                } else {
                                                    echo "#FFFFFF";
                                                } ?>">
                                    <td style="padding: 5px;"><?php echo $row_reclamacao_suporte['id']; ?></td>
                                    <td style="padding: 5px;"><? echo date('d-m-Y  H:i', strtotime($row_reclamacao_suporte['data_suporte'])); ?></td>
                                    <td style="padding: 5px;"><?php echo $row_reclamacao_suporte['situacao']; ?></td>
                                    <td style="padding: 5px;"><?php echo $row_reclamacao_suporte['titulo']; ?></td>
                                    <td style="padding: 5px;"><a href="suporte_editar.php?id_suporte=<? echo $row_reclamacao_suporte['id']; ?>&padrao=sim" target="_blank" id="botao_geral2" style="width: 70px;">Abrir</a></td>
                                </tr>
                                <? $contador_reclamacao_suporte = $contador_reclamacao_suporte + 1; ?>
                            <?php } while ($row_reclamacao_suporte = mysql_fetch_assoc($reclamacao_suporte)); ?>

                        </table>
                        <!-- fim - tabela -->
                    </td>
                </tr>
            </table>
        </div>
    <? } ?>
    <!-- fim - reclamacao_suporte -->

    <div class="div_solicitacao_linhas3">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>

                <td width="200" valign="top">

                    <? if ($row_suporte['situacao'] != "solucionada" and $row_suporte['situacao'] != "cancelada") { ?>

                        <!-- usuario_responsavel leu em -->
                        <? if ($row_suporte['id_usuario_responsavel'] != "") { ?>
                            <? echo $row_suporte['usuario_responsavel']; ?>
                        <? } else { ?>
                            <span style="color:#F00;">Sem responsável</span>
                        <? } ?>

                        <!-- Alterar usuário responsável -->
                        <?php if (
                            ($row_usuario['controle_suporte']  == "Y") or
                            ($row_usuario['suporte_operador_parceiro'] == "Y") or 
                            ($row_usuario['controle_praca'] == "Y" and $row_suporte['praca'] == $row_usuario['praca'] and ($tipo_suporte_inloco == "cn" or $tipo_suporte_inloco == "r"))
                        ) { ?>
                            <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=editar&acao=Alterar usuário responsável&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox">
                                <img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar usuário responsável">
                            </a>
                        <? } ?>
                        <!-- fim - Alterar usuário responsável -->

                        <br>
                        <span class="label_solicitacao">Responsável leu em:</span>

                        <br>
                        <? if ($row_suporte['usuario_responsavel_leu'] != "") {
                            echo date('d-m-Y - H:i:s', strtotime($row_suporte['usuario_responsavel_leu']));
                        } else {
                            echo "não leu";
                        } ?>
                        <!-- fim - usuario_responsavel leu em -->


                        <? if ($tipo_suporte_inloco == "p") { ?>
                            <!-- usuario_envolvido leu em -->
                            <? if ($row_suporte['id_usuario_envolvido'] != "") { ?>
                                <br><br>
                                <? echo $row_suporte['usuario_envolvido']; ?>
                            <? } else { ?>
                                <br><br>
                                <span style="color:#F00;">Sem envolvido</span>
                            <? } ?>

                            <!-- Alterar usuário envolvido -->
                            <?php if (
                                ($row_usuario['controle_suporte']  == "Y") or
                                ($row_usuario['suporte_operador_parceiro'] == "Y") or 
                                ($row_usuario['controle_praca'] == "Y" and $row_suporte['praca'] == $row_usuario['praca'])
                            ) { ?>
                                <a href="suporte_editar_tabela.php?id_suporte=<? echo $row_suporte['id']; ?>&interacao=<? echo $row_suporte['interacao']; ?>&situacao=editar&acao=Alterar usuário envolvido&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox">
                                    <img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar usuário envolvido">
                                </a>
                            <? } ?>
                            <!-- fim - Alterar usuário envolvido -->

                            <br>
                            <span class="label_solicitacao">Envolvido leu em:</span>

                            <br>
                            <? if ($row_suporte['usuario_envolvido_leu'] != "") {
                                echo date('d-m-Y - H:i:s', strtotime($row_suporte['usuario_envolvido_leu']));
                            } else {
                                echo "não leu";
                            } ?>
                            <!-- fim - usuario_envolvido leu em -->
                        <? } ?>


                        <!-- duração -->
                        <br><br>
                        <span class="label_solicitacao">Duração:</span>
                        <br>
                        <?
                        $data_ini = strtotime($row_suporte['data_suporte']);
                        $data_final = strtotime(date("Y-m-d H:i:s"));

                        $nDias   = ($data_final - $data_ini) / (3600 * 24);  // dias
                        $nHoras = (($data_final - $data_ini) % (3600 * 24)) / 3600; // horas
                        $nMinutos = ((($data_final - $data_ini) % (3600 * 24)) % 3600) / 60; // minutos

                        echo sprintf('%02dd %02dh %02dm', $nDias, $nHoras, $nMinutos);
                        ?>
                        <!-- fim - duração -->

                    <? } ?>

                    <? if ($row_suporte['situacao'] == "solucionada" or $row_suporte['situacao'] == "cancelada") { ?>

                        <!-- usuario_responsavel leu em -->
                        <? echo $row_suporte['usuario_responsavel']; ?>
                        <br>
                        <span class="label_solicitacao">Usuário Responsável</span>
                        <!-- fim - usuario_responsavel leu em -->


                        <!-- usuario_envolvido leu em -->
                        <? if ($tipo_suporte_inloco != "cn") { ?>
                            <br><br>
                            <? echo $row_suporte['usuario_envolvido']; ?>
                            <br>
                            <span class="label_solicitacao">Usuário Envolvido</span>
                        <? } ?>
                        <!-- fim - usuario_envolvido leu em -->

                    <? } ?>

                </td>
                <td style="padding: 0px;" valign="top">

                    <div class="div_descricao" style="min-height: 150px;">

                        <!-- descricao -->
                        <? if ($totalRows_descricao > 0) { ?>
                            <?php do { ?>

                                <strong>
                                    <? if ($row_descricao['usuario_responsavel'] != "") {
                                        echo $row_descricao['usuario_responsavel'];
                                    } else {
                                        echo "Sistema";
                                    } ?> |
                                    <? echo date('d-m-Y | H:i:s', strtotime($row_descricao['data'])); ?> |
                                    <?php echo $row_descricao['tipo_postagem']; ?>
                                    <br>
                                </strong>

                                <?php if ($row_descricao['questionado'] != "") { ?>
                                    Para: <strong><?php echo $row_descricao['questionado']; ?></strong>
                                    <br>
                                <? } ?>

                                <?php echo $row_descricao['descricao']; ?>

                                <div style=" width: 100%; height: 1px; background-color: #CCCCCC; margin-top: 10px; margin-bottom: 10px; margin-left: 0px; margin-right: 0px;"></div>

                            <?php } while ($row_descricao = mysql_fetch_assoc($descricao)); ?>
                        <? } ?>
                        <!-- fim - descricao -->

                    </div>

                </td>

            </tr>
        </table>
    </div>

    <? if ($totalRows_arquivos_anexos > 0) { ?>
        <div class="div_solicitacao_linhas4">
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td style="text-align: left; color: #F00; font-weight: bold;">
                        Existe(m) arquivo(s) em anexo.
                    </td>
                </tr>
            </table>
        </div>
    <? } ?>

    <div class="div_solicitacao_linhas3" id="botoes">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td style="text-align: left;">
                    <!-- Tempo gasto -->
                    <? if ($totalRows_tempo_gasto > 0) { ?>
                        <a href="suporte_editar_tempo_gasto.php?id_suporte=<? echo $row_suporte['id']; ?>&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral">Tempo gasto</a>
                    <? } ?>
                    <!-- fim - Tempo gasto -->


                    <a href="#" class="botao_geral" style="width: 150px;" onclick="print()">Imprimir</a>

                    <a href="suporte_vinculo.php?codigo_empresa=<? echo $row_suporte['codigo_empresa']; ?>&situacao=&acao=Suportes vinculados&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral" style="width: 100px;">Suportes</a>

                    <a href="suporte_reclamacao_vinculo.php?codigo_empresa=<? echo $row_suporte['codigo_empresa']; ?>&situacao=&acao=Suportes vinculados&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral" style="width: 100px;">Reclamações</a>

                    <a href="solicitacao_vinculo.php?codigo_empresa=<? echo $row_suporte['codigo_empresa']; ?>&situacao=&acao=Solicitações vinculadas&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral" style="width: 100px;">Solicitações</a>


                    <!-- agenda -->
                    <?php if ($row_suporte['status_flag'] == "a") { ?>
                        <?php if ($row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario'] and $row_usuario['praca'] == $row_suporte['praca']) { ?>
                            <a href="agenda_popup.php?id_usuario_responsavel=<? echo $row_usuario['IdUsuario']; ?>&data_atual=<? echo date('d-m-Y'); ?>&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&placeValuesBeforeTB_=savedValues&TB_iframe=true" id="botao_geral2" class="thickbox">Ver agenda</a>
                        <? } ?>
                    <? } ?>
                    <!-- fim - agenda -->
                </td>
            </tr>
        </table>
    </div>


</body>

</html>

<!-- reclamacao_consulta -->
<? if (isset($_GET['padrao']) && ($_GET['padrao'] == "sim")) { ?>
    <? if ($totalRows_reclamacao_consulta > 0) { ?>

        <script>
            alert('<? echo $reclamacao_consulta_mensagem; ?>');
        </script>

    <? } ?>
<? } ?>
<!-- fim - reclamacao_consulta -->

<?php
mysql_free_result($usuario);
mysql_free_result($suporte);
mysql_free_result($empresa_dados);
mysql_free_result($pe13);
mysql_free_result($manutencao_dados);
mysql_free_result($modcon);
mysql_free_result($suporte_formulario_bonus_ultimo);
mysql_free_result($descricao);
mysql_free_result($agenda);
mysql_free_result($agenda_agendado);
mysql_free_result($reclamacao_vinculo);
mysql_free_result($suporte_contato);
mysql_free_result($suporte_formulario_atual);
mysql_free_result($suporte_formulario_listar);
mysql_free_result($suporte_formulario_listar2);
mysql_free_result($arquivos_anexos);
mysql_free_result($tempo_gasto);
mysql_free_result($reclamacao_suporte);
mysql_free_result($reclamacao_consulta);
mysql_free_result($reclamacao_encerramento);
?>