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
require_once('venda_funcao_update.php');

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

// venda_editar (recordset) - seleciona o venda atual
$colname_venda_editar = "-1";
if (isset($_GET['id_venda'])) {
    $colname_venda_editar = $_GET['id_venda'];
}
mysql_select_db($database_conexao, $conexao);
$query_venda_editar = sprintf("
SELECT id, id_usuario_responsavel, status, praca, id_prospeccao, espelho, 
(SELECT nome FROM usuarios WHERE venda.id_usuario_responsavel = usuarios.IdUsuario) AS usuario_responsavel 
FROM venda 
WHERE id = %s", GetSQLValueString($colname_venda_editar, "int"));
$venda_editar = mysql_query($query_venda_editar, $conexao) or die(mysql_error());
$row_venda_editar = mysql_fetch_assoc($venda_editar);
$totalRows_venda_editar = mysql_num_rows($venda_editar);
// fim - venda_ditar (recordset) - seleciona o venda atual

// caso não tenho venda, volta para listagem ********************************
if ($totalRows_venda_editar < 1) {
    $site_link_redireciona = "venda.php?padrao=sim&" . $venda_padrao;
    echo sprintf("<meta http-equiv=\"refresh\" content=\"0; url=%s\">", $site_link_redireciona);
    exit;
}
// fim - caso não tenho venda, volta para listagem **************************

// caso não impresso o espelho, volta para o espelho ********************************
if ($row_venda_editar['espelho'] == 0) {
    $site_link_redireciona = "prospeccao_editar_espelho.php?id_prospeccao=" . $row_venda_editar['id_prospeccao'] . "&id_venda=" . $row_venda_editar['id'];
    echo sprintf("<meta http-equiv=\"refresh\" content=\"0; url=%s\">", $site_link_redireciona);
    exit;
}
// fim - caso não impresso o espelho, volta para o espelho **************************


// insert - LEU --------------------------------------------------
// se é usuario_responsavel
if ($row_venda_editar['id_usuario_responsavel'] == $row_usuario['IdUsuario']) {
    $updateSQL_leu = sprintf(
        "UPDATE venda SET usuario_responsavel_leu=%s WHERE id=%s",
        GetSQLValueString(date("Y-m-d H:i:s"), "date"),
        GetSQLValueString($row_venda_editar['id'], "int")
    );

    mysql_select_db($database_conexao, $conexao);
    $Result_leu = mysql_query($updateSQL_leu, $conexao) or die(mysql_error());
}
// fim - se é usuario_responsavel
// fim - insert - LEU  -------------------------------------------

mysql_free_result($venda_editar);

// ----------------------------------------------------------------------------------------------------------------------------------------------------------------

// venda
$colname_venda = "-1";
if (isset($_GET['id_venda'])) {
    $colname_venda = $_GET['id_venda'];
}
mysql_select_db($database_conexao, $conexao);
$query_venda = sprintf("
SELECT venda.*, 
(SELECT nome FROM usuarios WHERE venda.id_usuario_responsavel = usuarios.IdUsuario) AS usuario_responsavel
FROM venda 
WHERE venda.id = %s", GetSQLValueString($colname_venda, "int"));
$venda = mysql_query($query_venda, $conexao) or die(mysql_error());
$row_venda = mysql_fetch_assoc($venda);
$totalRows_venda = mysql_num_rows($venda);
// fim - venda

// $colname_contrato
$colname_contrato = "-1";
if (isset($row_venda["contrato"])) {
    $colname_contrato = $row_venda["contrato"];
}
// fim - $colname_contrato

// manutencao_dados
mysql_select_db($database_conexao, $conexao);
$query_manutencao_dados = sprintf(
    "
SELECT 
geral_tipo_praca_executor.praca, 
da37.codigo17, da37.cliente17, da37.status17, da37.obs17, da37.tpocont17, da37.visita17, da37.optacuv17, da37.versao17, da37.espmod17, versao17, espmod17, da37.datvis17, 
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

// empresa_dados ---------------------------
if ($totalRows_manutencao_dados > 0 and $row_venda['codigo_empresa'] != "") { // contrato existe na tabela 'DA37s9'

    mysql_select_db($database_conexao, $conexao);
    $query_empresa_dados = sprintf(
        "
SELECT nome1, cgc1, insc1, endereco1, bairro1, cidade1, uf1, telefone1, comercio1, cep1, ultcompra1, atraso1, status1, flag1
FROM da01 
WHERE codigo1 = %s and da01.sr_deleted <> 'T'",
        GetSQLValueString($row_manutencao_dados['cliente17'], "text")
    );
    $empresa_dados = mysql_query($query_empresa_dados, $conexao) or die(mysql_error());
    $row_empresa_dados = mysql_fetch_assoc($empresa_dados);
    $totalRows_empresa_dados = mysql_num_rows($empresa_dados);
} else { // contrato NÃO existe na tabela 'DA37s9'

    mysql_select_db($database_conexao, $conexao);
    $query_empresa_dados = sprintf(
        "
SELECT nome_razao_social AS nome1, cpf_cnpj AS cgc1, rg_inscricao AS insc1, concat(endereco,' - ',endereco_numero) AS endereco1, bairro AS bairro1, cidade AS cidade1, 
uf AS uf1, telefone telefone1, celular AS comercio1, cep AS cep1, '' AS ultcompra1, '' AS atraso1, '' AS status1, '' AS flag1
FROM prospeccao 
WHERE id = %s",
        GetSQLValueString($row_venda['id_prospeccao'], "text")
    );
    $empresa_dados = mysql_query($query_empresa_dados, $conexao) or die(mysql_error());
    $row_empresa_dados = mysql_fetch_assoc($empresa_dados);
    $totalRows_empresa_dados = mysql_num_rows($empresa_dados);
}
// fim - empresa_dados ---------------------

// venda_descricoes
$colname_descricao = "-1";
if (isset($_GET['id_venda'])) {
    $colname_descricao = $_GET['id_venda'];
}
mysql_select_db($database_conexao, $conexao);
$query_descricao = sprintf("
SELECT venda_descricoes.*, 
(SELECT nome FROM usuarios WHERE venda_descricoes.id_usuario_responsavel = usuarios.IdUsuario) AS usuario_responsavel 
FROM venda_descricoes 
WHERE venda_descricoes.id_venda = %s 
ORDER BY venda_descricoes.id DESC", GetSQLValueString($colname_descricao, "text"));
$descricao = mysql_query($query_descricao, $conexao) or die(mysql_error());
$row_descricao = mysql_fetch_assoc($descricao);
$totalRows_descricao = mysql_num_rows($descricao);
// fim - venda_descricoes

// arquivos_anexos
mysql_select_db($database_conexao, $conexao);
$query_arquivos_anexos = sprintf("SELECT id_arquivo FROM venda_arquivos WHERE id_venda = %s", GetSQLValueString($_GET['id_venda'], "int"));
$arquivos_anexos = mysql_query($query_arquivos_anexos, $conexao) or die(mysql_error());
$row_arquivos_anexos = mysql_fetch_assoc($arquivos_anexos);
$totalRows_arquivos_anexos = mysql_num_rows($arquivos_anexos);
// fim - arquivos_anexos

// prospeccao
mysql_select_db($database_conexao, $conexao);
$query_prospeccao = sprintf("SELECT id, tipo_cliente FROM prospeccao WHERE id = %s", GetSQLValueString($row_venda['id_prospeccao'], "int"));
$prospeccao = mysql_query($query_prospeccao, $conexao) or die(mysql_error());
$row_prospeccao = mysql_fetch_assoc($prospeccao);
$totalRows_prospeccao = mysql_num_rows($prospeccao);
// fim - prospeccao

// venda_modulos
mysql_select_db($database_conexao, $conexao);
$query_venda_modulos = sprintf("SELECT geral_tipo_modulo.descricao AS modulo FROM venda_modulos LEFT JOIN geral_tipo_modulo ON venda_modulos.id_modulo = geral_tipo_modulo.IdTipoModulo WHERE venda_modulos.id_venda = %s ORDER BY id ASC", GetSQLValueString($_GET['id_venda'], "int"));
$venda_modulos = mysql_query($query_venda_modulos, $conexao) or die(mysql_error());
$row_venda_modulos = mysql_fetch_assoc($venda_modulos);
$totalRows_venda_modulos = mysql_num_rows($venda_modulos);
// fim - venda_modulos

// venda_contato (contador)
mysql_select_db($database_conexao, $conexao);
$query_venda_contato = sprintf("
SELECT count(id) as retorno
FROM venda_contato 
WHERE id_venda = %s 
ORDER BY id ASC", GetSQLValueString($row_venda['id'], "int"));
$venda_contato = mysql_query($query_venda_contato, $conexao) or die(mysql_error());
$row_venda_contato = mysql_fetch_assoc($venda_contato);
$totalRows_venda_contato = mysql_num_rows($venda_contato);
// fim - venda_contato (contador)

// reclamacao_venda
mysql_select_db($database_conexao, $conexao);
$query_reclamacao_venda = sprintf("
SELECT id, data_suporte, situacao, titulo 
FROM suporte 
WHERE reclamacao_venda = %s 
ORDER BY id ASC", GetSQLValueString($row_venda['id'], "text"));
$reclamacao_venda = mysql_query($query_reclamacao_venda, $conexao) or die(mysql_error());
$row_reclamacao_venda = mysql_fetch_assoc($reclamacao_venda);
$totalRows_reclamacao_venda = mysql_num_rows($reclamacao_venda);
// fim - reclamacao_venda

// reclamacao_consulta
mysql_select_db($database_conexao, $conexao);
$query_reclamacao_consulta = sprintf(
    "
SELECT id, empresa, situacao, status_flag     
FROM suporte 
WHERE contrato = %s and tipo_suporte = 'r' and 
((status_flag = 'a') or (status_flag = 'f' and DATE_ADD(data_fim,INTERVAL " . $row_parametros['suporte_reclamacao_mensagem_inicial_dias'] . " DAY) >= now()))
",
    GetSQLValueString($row_venda['contrato'], "text")
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
        $reclamacao_consulta_mensagem = 'ATENÇÃO:\nRECLAMAÇÃO REGISTRADA RECENTEMENTE\nCliente: ' . utf8_encode($row_venda['empresa']) . '\n' . $reclamacao_consulta_mensagem_fechada;
        $reclamacao_consulta_mensagem_corpo = 'ATENÇÃO: CLIENTE COM RECLAMAÇÃO REGISTRADA RECENTEMENTE';
    } else if ($reclamacao_consulta_status == 1) {
        $reclamacao_consulta_mensagem = 'ATENÇÃO:\nRECLAMAÇÃO EM ANDAMENTO\nCliente: ' . utf8_encode($row_venda['empresa']) . '\n' . $reclamacao_consulta_mensagem_aberta;
        $reclamacao_consulta_mensagem_corpo = 'ATENÇÃO: CLIENTE COM RECLAMAÇÃO EM ANDAMENTO';
    }
}
// fim - reclamacao_consulta

// agenda_treinamento
$colname_agenda_treinamento = "-1";
if (isset($_GET['id_venda'])) {
    $colname_agenda_treinamento = $_GET['id_venda'];
}
mysql_select_db($database_conexao, $conexao);
$query_agenda_treinamento = sprintf("
SELECT * 
FROM agenda 
WHERE id_venda_treinamento = %s 
ORDER BY data ASC", GetSQLValueString($colname_agenda_treinamento, "text"));
$agenda_treinamento = mysql_query($query_agenda_treinamento, $conexao) or die(mysql_error());
$row_agenda_treinamento = mysql_fetch_assoc($agenda_treinamento);
$totalRows_agenda_treinamento = mysql_num_rows($agenda_treinamento);
// fim - agenda_treinamento

// agenda_treinamento_agendado
mysql_select_db($database_conexao, $conexao);
$query_agenda_treinamento_agendado = sprintf("
SELECT id_agenda 
FROM agenda 
WHERE id_venda_treinamento = %s and status = 'a'
ORDER BY data ASC", GetSQLValueString(@$_GET['id_venda'], "text"));
$agenda_treinamento_agendado = mysql_query($query_agenda_treinamento_agendado, $conexao) or die(mysql_error());
$row_agenda_treinamento_agendado = mysql_fetch_assoc($agenda_treinamento_agendado);
$totalRows_agenda_treinamento_agendado = mysql_num_rows($agenda_treinamento_agendado);
// fim - agenda_treinamento_agendado

// agenda_treinamento_contador (calculo) ------------------------------------------------------------------
$contador_treinamento_segundo = $row_venda['treinamento_tempo'] * 60;
$contador_treinamento_segundo_finalizado = 0;
$contador_treinamento_segundo_agendado = 0;
$contador_treinamento_segundo_cancelado = 0;
$contador_treinamento_segundo_restante = $contador_treinamento_segundo;

// agenda_treinamento_contador
$colname_agenda_treinamento_contador = "-1";
if (isset($_GET['id_venda'])) {
    $colname_agenda_treinamento_contador = $_GET['id_venda'];
}
mysql_select_db($database_conexao, $conexao);
$query_agenda_treinamento_contador = sprintf("
SELECT data_inicio, data, status  
FROM agenda 
WHERE id_venda_treinamento = %s 
ORDER BY data ASC", GetSQLValueString($colname_agenda_treinamento_contador, "text"));
$agenda_treinamento_contador = mysql_query($query_agenda_treinamento_contador, $conexao) or die(mysql_error());
$row_agenda_treinamento_contador = mysql_fetch_assoc($agenda_treinamento_contador);
$totalRows_agenda_treinamento_contador = mysql_num_rows($agenda_treinamento_contador);
// fim - agenda_treinamento_contador

if ($totalRows_agenda_treinamento_contador > 0) {
    do {

        $data_ini = strtotime($row_agenda_treinamento_contador['data_inicio']);
        $data_final = strtotime($row_agenda_treinamento_contador['data']);

        if ($row_agenda_treinamento_contador['status'] == "f") {
            $contador_treinamento_segundo_finalizado = (strtotime($row_agenda_treinamento_contador['data']) - strtotime($row_agenda_treinamento_contador['data_inicio'])) + $contador_treinamento_segundo_finalizado;
        }

        if ($row_agenda_treinamento_contador['status'] == "a") {
            $contador_treinamento_segundo_agendado = (strtotime($row_agenda_treinamento_contador['data']) - strtotime($row_agenda_treinamento_contador['data_inicio'])) + $contador_treinamento_segundo_agendado;
        }

        if ($row_agenda_treinamento_contador['status'] == "c") {
            $contador_treinamento_segundo_cancelado = (strtotime($row_agenda_treinamento_contador['data']) - strtotime($row_agenda_treinamento_contador['data_inicio'])) + $contador_treinamento_segundo_cancelado;
        }

        $contador_treinamento_segundo_restante = $contador_treinamento_segundo - ($contador_treinamento_segundo_finalizado + $contador_treinamento_segundo_agendado);
    } while ($row_agenda_treinamento_contador = mysql_fetch_assoc($agenda_treinamento_contador));
}
mysql_free_result($agenda_treinamento_contador);
// fim - agenda_treinamento_contador (calculo) -----------------------------------------------------------

// agenda_implantacao
$colname_agenda_implantacao = "-1";
if (isset($_GET['id_venda'])) {
    $colname_agenda_implantacao = $_GET['id_venda'];
}
mysql_select_db($database_conexao, $conexao);
$query_agenda_implantacao = sprintf("
SELECT * 
FROM agenda 
WHERE id_venda_implantacao = %s 
ORDER BY data ASC", GetSQLValueString($colname_agenda_implantacao, "text"));
$agenda_implantacao = mysql_query($query_agenda_implantacao, $conexao) or die(mysql_error());
$row_agenda_implantacao = mysql_fetch_assoc($agenda_implantacao);
$totalRows_agenda_implantacao = mysql_num_rows($agenda_implantacao);
// fim - agenda_implantacao

// agenda_implantacao_agendado
mysql_select_db($database_conexao, $conexao);
$query_agenda_implantacao_agendado = sprintf("
SELECT id_agenda 
FROM agenda 
WHERE id_venda_implantacao = %s and status = 'a'
ORDER BY data ASC", GetSQLValueString(@$_GET['id_venda'], "text"));
$agenda_implantacao_agendado = mysql_query($query_agenda_implantacao_agendado, $conexao) or die(mysql_error());
$row_agenda_implantacao_agendado = mysql_fetch_assoc($agenda_implantacao_agendado);
$totalRows_agenda_implantacao_agendado = mysql_num_rows($agenda_implantacao_agendado);
// fim - agenda_implantacao_agendado

// agenda_implantacao_contador (calculo) ------------------------------------------------------------------
$contador_implantacao_segundo = $row_venda['implantacao_tempo'] * 60;
$contador_implantacao_segundo_finalizado = 0;
$contador_implantacao_segundo_agendado = 0;
$contador_implantacao_segundo_cancelado = 0;
$contador_implantacao_segundo_restante = $contador_implantacao_segundo;

// agenda_implantacao_contador
$colname_agenda_implantacao_contador = "-1";
if (isset($_GET['id_venda'])) {
    $colname_agenda_implantacao_contador = $_GET['id_venda'];
}
mysql_select_db($database_conexao, $conexao);
$query_agenda_implantacao_contador = sprintf("
SELECT data_inicio, data, status  
FROM agenda 
WHERE id_venda_implantacao = %s 
ORDER BY data ASC", GetSQLValueString($colname_agenda_implantacao_contador, "text"));
$agenda_implantacao_contador = mysql_query($query_agenda_implantacao_contador, $conexao) or die(mysql_error());
$row_agenda_implantacao_contador = mysql_fetch_assoc($agenda_implantacao_contador);
$totalRows_agenda_implantacao_contador = mysql_num_rows($agenda_implantacao_contador);
// fim - agenda_implantacao_contador

if ($totalRows_agenda_implantacao_contador > 0) {
    do {

        $data_ini = strtotime($row_agenda_implantacao_contador['data_inicio']);
        $data_final = strtotime($row_agenda_implantacao_contador['data']);

        if ($row_agenda_implantacao_contador['status'] == "f") {
            $contador_implantacao_segundo_finalizado = (strtotime($row_agenda_implantacao_contador['data']) - strtotime($row_agenda_implantacao_contador['data_inicio'])) + $contador_implantacao_segundo_finalizado;
        }

        if ($row_agenda_implantacao_contador['status'] == "a") {
            $contador_implantacao_segundo_agendado = (strtotime($row_agenda_implantacao_contador['data']) - strtotime($row_agenda_implantacao_contador['data_inicio'])) + $contador_implantacao_segundo_agendado;
        }

        if ($row_agenda_implantacao_contador['status'] == "c") {
            $contador_implantacao_segundo_cancelado = (strtotime($row_agenda_implantacao_contador['data']) - strtotime($row_agenda_implantacao_contador['data_inicio'])) + $contador_implantacao_segundo_cancelado;
        }

        $contador_implantacao_segundo_restante = $contador_implantacao_segundo - ($contador_implantacao_segundo_finalizado + $contador_implantacao_segundo_agendado);
    } while ($row_agenda_implantacao_contador = mysql_fetch_assoc($agenda_implantacao_contador));
}
mysql_free_result($agenda_implantacao_contador);
// fim - agenda_implantacao_contador (calculo) -----------------------------------------------------------

$avaliacao_implantacao = date('d-m-Y  H:i:s', strtotime($row_venda['data_inicio']) + ($row_parametros['implantacao_prazo'] * 86400));

$venda_validade_dias = $row_parametros['venda_validade_dias'] + $row_venda['dilacao_prazo'];
$validade = date('d-m-Y 23:59:59', strtotime("+$venda_validade_dias days", strtotime($row_venda['data_venda'])));
$validade_passado_dias = (strtotime(date('Y-m-d H:i:s')) - strtotime($validade)) / 86400;
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

            <? if ($row_venda['situacao'] != "criada" and $row_venda['situacao'] != "solucionada" and $row_venda['situacao'] != "cancelada" and $validade_passado_dias < 0) { ?>
                $("a[name='botao_concluir_implantacao_treinamento']").click(function() {
                    return confirm("Este recurso deverá ser utilizado somente após concluir o processo de implantação e treinamento, pois trata-se de um resumo dos serviços executados.");
                });
            <? } ?>

        });
    </script>
    <title>Venda n° <? echo $row_venda['id']; ?></title>
</head>

<body>

    <div class="div_solicitacao_linhas">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td style="text-align:left">
                    Venda n° <? echo $row_venda['id']; ?>
                </td>

                <td style="text-align: right">
                    &lt;&lt; <a href="venda.php?padrao=sim&<? echo $venda_padrao; ?>">Voltar</a> |
                    Usuário logado: <? echo $row_usuario['nome']; ?> (<? echo $row_usuario['nivel_venda']; ?>) |
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
                    <?php echo utf8_encode($row_empresa_dados['nome1']); ?> |

                    <span class="label_solicitacao">Praça: </span>
                    <?php echo $row_venda['praca']; ?>
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
                    <span class="label_solicitacao">Endereço: </span>
                    <? echo utf8_encode($row_empresa_dados['endereco1']); ?> - <?php echo utf8_encode($row_empresa_dados['bairro1']); ?>
                    <br>
                    CEP: <?php echo $row_empresa_dados['cep1']; ?> | <?php echo utf8_encode($row_empresa_dados['cidade1']); ?> - <?php echo $row_empresa_dados['uf1']; ?>
                </td>

                <td style="text-align:right">
                    <span class="label_solicitacao">Ordem de serviço: </span>
                    <?php echo $row_venda['ordem_servico']; ?>

                    <!-- Alterar ordem de serviço -->
                    <?php if ($row_venda['status_flag'] == "a") { ?>
                        <?php if ($row_usuario['praca'] == 'MATRIZ') { ?>
                            <a href="venda_editar_tabela.php?id_venda=<? echo $row_venda['id']; ?>&interacao=<? echo $row_venda['interacao']; ?>&situacao=editar&acao=Alterar ordem de serviço&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $venda_editar_tabela_height; ?>&width=<? echo $venda_editar_tabela_width; ?>&modal=true" class="thickbox">
                                <img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar ordem de serviço">
                            </a>
                        <? } ?>
                    <? } ?>
                    <!-- fim - Alterar ordem de serviço -->
                </td>

            </tr>
        </table>
    </div>

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
                    <span class="label_solicitacao">Contrato: </span>
                    <? if ($totalRows_manutencao_dados > 0 and $row_venda['codigo_empresa'] != "") { // contrato existe na tabela 'DA37s9' 
                    ?>

                        <strong><?php echo $colname_contrato; ?></strong>

                    <? } else { // contrato NÃO existe na tabela 'DA37s9' 
                    ?>

                        <strong><span style="color: #F00;"><?php echo $colname_contrato; ?>*</span></strong>

                    <? } ?> |

                    <span class="label_solicitacao">Data do contrato: </span>
                    <? echo date('d-m-Y', strtotime($row_venda['data_contrato'])); ?>
                    <!-- Alterar data do contrato -->
                    <?php if ($row_usuario['administrador_site'] == 'Y' and $row_venda['status_flag'] == "a") { ?>
                        <a href="venda_editar_tabela.php?id_venda=<? echo $row_venda['id']; ?>&interacao=<? echo $row_venda['interacao']; ?>&situacao=editar&acao=Alterar data do contrato&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $venda_editar_tabela_height; ?>&width=<? echo $venda_editar_tabela_width; ?>&modal=true" class="thickbox">
                            <img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar data do contrato">
                        </a>
                    <? } ?>
                    <!-- fim - Alterar data do contrato -->
                    |
                    <span class="label_solicitacao">Tipo do contrato: </span><strong><?php echo $row_manutencao_dados['tpocont17_descricao']; ?></strong>
                    <? if ($row_manutencao_dados['datvis17'] > 0) { ?>
                        | <span class="label_solicitacao">Últ. alteração contratual: </span>
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

                <td style="text-align:right">
                    Última compra: <strong><? if ($row_empresa_dados['ultcompra1'] != "") {
                                                echo date('d-m-Y', strtotime($row_empresa_dados['ultcompra1']));
                                            } ?></strong> |
                    Total dias em atraso: <strong><?php echo $row_empresa_dados['atraso1']; ?></strong>
                </td>
            </tr>
        </table>
    </div>

    <div class="div_solicitacao_linhas3">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td style="text-align:left" width="400">
                    Tipo de visita: <strong><?php echo $row_manutencao_dados['visita17_descricao']; ?></strong> |
                    Optante por acumulo de manutenção: <strong><?php if ($row_manutencao_dados['optacuv17'] == "N") {
                                                                    echo "Não";
                                                                }
                                                                if ($row_manutencao_dados['optacuv17'] == "S") {
                                                                    echo "Sim";
                                                                } ?></strong>
                </td>

                <td style="text-align:right">

                    <span class="label_solicitacao">Data/hora criação/início: </span>
                    <? echo date('d-m-Y  H:i:s', strtotime($row_venda['data_venda'])); ?>
                    <br>

                    <?php if ($row_venda['data_fim'] != "") { ?>
                        <span class="label_solicitacao">Data/hora fim: </span><? echo date('d-m-Y  H:i:s', strtotime($row_venda['data_fim'])); ?>
                        <br>
                    <? } ?>

                    <span class="label_solicitacao">Validade: </span>
                    <? echo $validade; ?> |
                    <span class="label_solicitacao">Dilação: </span>
                    <?php echo $row_venda['dilacao_prazo']; ?> dias
                    <br>

                    <span class="label_solicitacao">Data/hora avaliação da implantação: </span>
                    <? echo $avaliacao_implantacao; ?>
                    <br>

                </td>
            </tr>
        </table>
    </div>

    <div class="div_solicitacao_linhas2">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td style="text-align:left; vertical-align: top">
                    <span class="label_solicitacao">Prospecção: </span><?php echo $row_venda['id_prospeccao']; ?> -
                    <a href="prospeccao_editar.php?id_prospeccao=<?php echo $row_venda['id_prospeccao']; ?>&padrao=sim" target="_blank"><strong>Acessar</strong></a>
                </td>

                <td style="text-align: right">
                    <span class="label_solicitacao">Situação: </span><?php echo $row_venda['situacao']; ?> |
                    <span class="label_solicitacao">Status: </span><?php echo $row_venda['status']; ?>

                    <? if($row_venda['atendimento'] <> ''){ ?>
                        <br>
                        <span class="label_solicitacao">Atendimento: </span><? echo @$atendimento_array[$row_venda['atendimento']]['titulo']; ?>
                    <? } ?>
                </td>
            </tr>
        </table>
    </div>

    <div class="div_solicitacao_linhas3">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td style="text-align:left; vertical-align: top">
                    <span class="label_solicitacao">Módulos: </span>
                    <?
                    $contador_venda_modulos = 0;
                    $modulos = NULL;
                    ?>
                    <? do { ?>

                        <? $contador_venda_modulos = $contador_venda_modulos + 1; ?>
                        <? $modulos .= $row_venda_modulos['modulo'];
                        if ($contador_venda_modulos < $totalRows_venda_modulos) {
                            $modulos .= ", ";
                        } ?>

                    <?php } while ($row_venda_modulos = mysql_fetch_assoc($venda_modulos)); ?>
                    <? echo $modulos; ?>

                    <!-- Alterar módulos -->
                    <?php if ($row_usuario['nivel_venda'] == 1 and $row_venda['status_flag'] == "a") { ?>
                        <a href="venda_editar_tabela.php?id_venda=<? echo $row_venda['id']; ?>&interacao=<? echo $row_venda['interacao']; ?>&situacao=editar&acao=Alterar módulos&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $venda_editar_tabela_height; ?>&width=<? echo $venda_editar_tabela_width; ?>&modal=true" class="thickbox">
                            <img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar módulos">
                        </a>
                    <? } ?>
                    <!-- fim - Alterar módulos -->
                </td>

                <td style="text-align: right" width="350">
                    <span class="label_solicitacao">Valor da venda do software: </span>R$ <? echo number_format($row_venda['valor_venda'], 2, ',', '.'); ?>

                    <?php if ($row_usuario['administrador_site'] == 'Y' and $row_venda['status_flag'] == "a") { ?>
                        <a href="venda_editar_tabela.php?id_venda=<? echo $row_venda['id']; ?>&interacao=<? echo $row_venda['interacao']; ?>&situacao=editar&acao=Alterar valor da venda do software&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $venda_editar_tabela_height; ?>&width=<? echo $venda_editar_tabela_width; ?>&modal=true" class="thickbox">
                            <img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar valor da venda do software"></a>
                    <? } ?>

                    <br>

                    <span class="label_solicitacao">Valor da venda do treinamento: </span>R$ <? echo number_format($row_venda['valor_treinamento'], 2, ',', '.'); ?>

                    <?php if ($row_usuario['administrador_site'] == 'Y' and $row_venda['status_flag'] == "a") { ?>
                        <a href="venda_editar_tabela.php?id_venda=<? echo $row_venda['id']; ?>&interacao=<? echo $row_venda['interacao']; ?>&situacao=editar&acao=Alterar valor da venda do treinamento&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $venda_editar_tabela_height; ?>&width=<? echo $venda_editar_tabela_width; ?>&modal=true" class="thickbox">
                            <img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar valor da venda do treinamento"></a>
                    <? } ?>
                </td>
            </tr>
        </table>
    </div>

    <!-- Botões ====================================================================================================================================================== -->
    <? if (
        $row_venda['situacao'] != "criada" and 
        $row_venda['situacao'] != "solucionada" and 
        $row_venda['situacao'] != "cancelada" and 
        
        ($row_usuario['controle_venda'] == "Y" or $validade_passado_dias < 0)
    ) { ?>

        <div class="div_solicitacao_linhas4" id="botoes">
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td style="text-align:left">

                        <!-- Encaminhar -->
                        <?
                        if (
                            // nivel_venda: 1 =======================================================================================================
                            (
                                ($row_usuario['nivel_venda'] == 1 and $row_usuario['praca'] == $row_venda['praca']) and

                                $row_venda['status_flag'] == "a" and ($row_venda['situacao'] == "analisada" or
                                    $row_venda['situacao'] == "em execução"))
                            // fim - nivel_venda: 1 =================================================================================================
                            or
                            // nivel_venda: 2 =======================================================================================================
                            (
                                ($row_usuario['nivel_venda'] == 2 and $row_usuario['praca'] == $row_venda['praca']) and

                                $row_venda['status_flag'] == "a" and ($row_venda['situacao'] == "analisada" or
                                    $row_venda['situacao'] == "em execução"))
                            // fim - nivel_venda: 2 =================================================================================================
                            or
                            // usuario_responsavel ============================================================================================================================
                            ($row_venda['id_usuario_responsavel'] == $row_usuario['IdUsuario'] and

                                $row_venda['status_flag'] == "a" and ($row_venda['situacao'] == "em execução"))
                            or ($row_venda['encaminhamento_id'] == $row_usuario['IdUsuario'] and

                                $row_venda['status_flag'] == "a" and ($row_venda['situacao'] == "analisada"))
                            // fim - usuario_responsavel =======================================================================================================================
                        ) { ?>

                            <a href="venda_editar_tabela.php?id_venda=<? echo $row_venda['id']; ?>&interacao=<? echo $row_venda['interacao']; ?>&situacao=<? echo $row_venda['situacao']; ?>&acao=Encaminhar&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $venda_editar_tabela_height; ?>&width=<? echo $venda_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2">Encaminhar</a>

                            <a href="painel.php" target="_blank" id="botao_geral2">Painel</a>

                        <? } ?>
                        <!-- fim - Encaminhar -->


                        <!-- Encerrar -->
                        <?
                        if (
                            // nivel_venda: 1 =======================================================================================================
                            (
                                ($row_usuario['nivel_venda'] == 1 and $row_usuario['praca'] == $row_venda['praca']) and

                                $row_venda['status_flag'] == "a" and ($row_venda['situacao'] == "analisada" or
                                    $row_venda['situacao'] == "em execução" or
                                    $row_venda['situacao'] == "solicitado agendamento"))
                            // fim - nivel_venda: 1 =================================================================================================
                            or
                            // nivel_venda: 2 =======================================================================================================
                            (
                                ($row_usuario['nivel_venda'] == 2 and $row_usuario['praca'] == $row_venda['praca']) and

                                $row_venda['status_flag'] == "a" and ($row_venda['situacao'] == "analisada" or
                                    $row_venda['situacao'] == "em execução" or
                                    $row_venda['situacao'] == "solicitado agendamento"))
                            // fim - nivel_venda: 2 =================================================================================================
                            or
                            // usuario_responsavel ============================================================================================================================
                            ($row_venda['id_usuario_responsavel'] == $row_usuario['IdUsuario'] and

                                $row_venda['status_flag'] == "a" and ($row_venda['situacao'] == "em execução" or
                                    $row_venda['situacao'] == "solicitado agendamento"))
                            or ($row_venda['encaminhamento_id'] == $row_usuario['IdUsuario'] and

                                $row_venda['status_flag'] == "a" and ($row_venda['situacao'] == "analisada" or
                                    $row_venda['situacao'] == "solicitado agendamento"))
                            // fim - usuario_responsavel =======================================================================================================================
                        ) { ?>

                            <? if (
                                $row_venda['status_flag'] == 'a' and
                                $row_venda['ordem_servico'] > 0 and
                                $row_venda['conclusao_implantacao_treinamento'] == 1 and
                                $contador_treinamento_segundo_agendado == 0 and
                                $contador_implantacao_segundo_agendado == 0
                            ) { ?>

                                <a href="venda_editar_tabela.php?id_venda=<? echo $row_venda['id']; ?>&interacao=<? echo $row_venda['interacao']; ?>&situacao=editar&acao=Encerrar&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $venda_editar_tabela_height; ?>&width=<? echo $venda_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 70px;">Encerrar</a>

                            <? } else { ?>

                                <div id="botao_geral_desativado">Encerrar</div>

                            <? } ?>

                        <? } ?>
                        <!-- fim - Encerrar -->


                        <!-- Cancelar -->
                        <?
                        if (
                            // controle_venda =======================================================================================================
                            (
                                ($row_usuario['controle_venda'] == "Y") and

                                $row_venda['status_flag'] == "a" and ($row_venda['situacao'] == "documentação pendente" or
                                    $row_venda['situacao'] == "analisada" or
                                    $row_venda['situacao'] == "em execução" or
                                    $row_venda['situacao'] == "solicitado agendamento"))
                            // fim - controle_venda =================================================================================================
                        ) { ?>

                            <a href="venda_editar_tabela.php?id_venda=<? echo $row_venda['id']; ?>&interacao=<? echo $row_venda['interacao']; ?>&situacao=editar&acao=Cancelar&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $venda_editar_tabela_height; ?>&width=<? echo $venda_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 70px;">Cancelar</a>

                        <? } ?>
                        <!-- fim - Cancelar -->


                        <!-- Dilação de prazo -->
                        <?
                        if (
                            // nivel_venda: 1 =======================================================================================================
                            (
                                ($row_usuario['nivel_venda'] == 1 and $row_usuario['praca'] == $row_venda['praca']) and

                                    $row_venda['status_flag'] == "a" and ($row_venda['situacao'] == "analisada" or
                                    $row_venda['situacao'] == "em execução" or
                                    $row_venda['situacao'] == "solicitado agendamento")
                                )
                            // fim - nivel_venda: 1 =================================================================================================
                            or
                            // nivel_venda: 2 =======================================================================================================
                            (
                                ($row_usuario['nivel_venda'] == 2 and $row_usuario['praca'] == $row_venda['praca']) and

                                $row_venda['status_flag'] == "a" and ($row_venda['situacao'] == "analisada" or
                                    $row_venda['situacao'] == "em execução" or
                                    $row_venda['situacao'] == "solicitado agendamento"))
                            // fim - nivel_venda: 2 =================================================================================================
                            or
                            // controle_venda =======================================================================================================
                            (
                                ($row_usuario['controle_venda'] == "Y") and
                                $row_venda['status_flag'] == "a" and ($row_venda['situacao'] == "analisada" or $row_venda['situacao'] == "em execução" or $row_venda['situacao'] == "solicitado agendamento"))
                            // fim - controle_venda =================================================================================================
                        ) { ?>

                            <? if (
                                // controlador_venda
                                ($row_usuario['controle_venda'] == "Y") //  and $validade_passado_dias >= 0
                                // fim - controlador_venda
                                or
                                // usuario_responsavel / nivel_venda: 1 / nivel_venda: 2
                                (
                                    (
                                    ($row_usuario['nivel_venda'] == 1 and $row_usuario['praca'] == $row_venda['praca']) or ($row_usuario['nivel_venda'] == 2 and $row_usuario['praca'] == $row_venda['praca'])) and
                                    $row_venda['dilacao_prazo_proposto'] == 0 and
                                    $row_venda['dilacao_prazo_quantidade'] < $row_parametros['venda_dilacao_prazo_quantidade'] and // se está dentro da 'quantidade' 
                                    $row_parametros['venda_dilacao_prazo_quantidade'] > 0 and // se 'quantidade' de alterações nos parametros é maior que ZERO
                                    $validade_passado_dias < $row_parametros['venda_dilacao_prazo_solicitar_dilacao'] // se está dentro do prazo de XX para solicitar a dilação
                                )
                                // fim - usuario_responsavel / nivel_venda: 1 / nivel_venda: 2
                            ) { ?>

                                <a href="venda_editar_tabela.php?id_venda=<? echo $row_venda['id']; ?>&interacao=<? echo $row_venda['interacao']; ?>&situacao=editar&acao=Dilação de prazo&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $venda_editar_tabela_height; ?>&width=<? echo $venda_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 130px;">Dilação de prazo (<? echo $row_venda['dilacao_prazo_quantidade']; ?>)</a>

                            <? } else { // se estourou a quantidade, desabilita o botão 
                            ?>

                                <div id="botao_geral_desativado">Dilação de prazo (<? echo $row_venda['dilacao_prazo_quantidade']; ?>)</div>

                            <? } ?>

                        <? } ?>
                        <!-- fim - Dilação de prazo -->


                        <!-- Agendamento de treinamento -->
                        <?
                        if (
                            // nivel_venda: 1 =======================================================================================================
                            (
                                ($row_usuario['nivel_venda'] == 1 and $row_usuario['praca'] == $row_venda['praca']) and

                                $row_venda['status_flag'] == "a" and ($row_venda['situacao'] == "analisada" or
                                    $row_venda['situacao'] == "em execução" or
                                    $row_venda['situacao'] == "solicitado agendamento"))
                            // fim - nivel_venda: 1 =================================================================================================
                            or
                            // nivel_venda: 2 =======================================================================================================
                            (
                                ($row_usuario['nivel_venda'] == 2 and $row_usuario['praca'] == $row_venda['praca']) and

                                $row_venda['status_flag'] == "a" and ($row_venda['situacao'] == "analisada" or
                                    $row_venda['situacao'] == "em execução" or
                                    $row_venda['situacao'] == "solicitado agendamento"))
                            // fim - nivel_venda: 2 =================================================================================================
                            or
                            // nivel_venda: 3 =======================================================================================================
                            (
                                ($row_usuario['nivel_venda'] == 3 and $row_usuario['praca'] == $row_venda['praca']) and

                                $row_venda['status_flag'] == "a" and ($row_venda['situacao'] == "analisada" or
                                    $row_venda['situacao'] == "em execução" or
                                    $row_venda['situacao'] == "solicitado agendamento"))
                            // fim - nivel_venda: 3 =================================================================================================
                            or
                            // usuario_responsavel ============================================================================================================================
                            ($row_venda['id_usuario_responsavel'] == $row_usuario['IdUsuario'] and

                                $row_venda['status_flag'] == "a" and ($row_venda['situacao'] == "em execução" or
                                    $row_venda['situacao'] == "solicitado agendamento"))
                            or ($row_venda['encaminhamento_id'] == $row_usuario['IdUsuario'] and

                                $row_venda['status_flag'] == "a" and ($row_venda['situacao'] == "analisada" or
                                    $row_venda['situacao'] == "solicitado agendamento"))
                            // fim - usuario_responsavel =======================================================================================================================
                        ) { ?>

                            <? if ($contador_treinamento_segundo_restante == 0) { ?>

                                <div id="botao_geral_desativado">Agendamento de treinamento</div>

                            <? } else { ?>

                                <a href="venda_editar_tabela.php?id_venda=<? echo $row_venda['id']; ?>&interacao=<? echo $row_venda['interacao']; ?>&situacao=editar&acao=Agendamento de treinamento&resposta=&agenda_tipo=treinamento&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $venda_editar_tabela_height; ?>&width=<? echo $venda_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 180px;">Agendamento de treinamento</a>


                            <? } ?>

                        <? } ?>
                        <!-- fim - Agendamento de treinamento -->


                        <!-- Agendamento de implantação -->
                        <?
                        if (
                            // nivel_venda: 1 =======================================================================================================
                            (
                                ($row_usuario['nivel_venda'] == 1 and $row_usuario['praca'] == $row_venda['praca']) and

                                $row_venda['status_flag'] == "a" and ($row_venda['situacao'] == "analisada" or
                                    $row_venda['situacao'] == "em execução" or
                                    $row_venda['situacao'] == "solicitado agendamento"))
                            // fim - nivel_venda: 1 =================================================================================================
                            or
                            // nivel_venda: 2 =======================================================================================================
                            (
                                ($row_usuario['nivel_venda'] == 2 and $row_usuario['praca'] == $row_venda['praca']) and

                                $row_venda['status_flag'] == "a" and ($row_venda['situacao'] == "analisada" or
                                    $row_venda['situacao'] == "em execução" or
                                    $row_venda['situacao'] == "solicitado agendamento"))
                            // fim - nivel_venda: 2 =================================================================================================
                            or
                            // nivel_venda: 3 =======================================================================================================
                            (
                                ($row_usuario['nivel_venda'] == 3 and $row_usuario['praca'] == $row_venda['praca']) and

                                $row_venda['status_flag'] == "a" and ($row_venda['situacao'] == "analisada" or
                                    $row_venda['situacao'] == "em execução" or
                                    $row_venda['situacao'] == "solicitado agendamento"))
                            // fim - nivel_venda: 3 =================================================================================================
                            or
                            // usuario_responsavel ============================================================================================================================
                            ($row_venda['id_usuario_responsavel'] == $row_usuario['IdUsuario'] and

                                $row_venda['status_flag'] == "a" and ($row_venda['situacao'] == "em execução" or
                                    $row_venda['situacao'] == "solicitado agendamento"))
                            or ($row_venda['encaminhamento_id'] == $row_usuario['IdUsuario'] and

                                $row_venda['status_flag'] == "a" and ($row_venda['situacao'] == "analisada" or
                                    $row_venda['situacao'] == "solicitado agendamento"))
                            // fim - usuario_responsavel =======================================================================================================================
                        ) { ?>

                            <? if ($contador_implantacao_segundo_restante == 0) { ?>

                                <div id="botao_geral_desativado">Agendamento de implantação</div>

                            <? } else { ?>

                                <a href="venda_editar_tabela.php?id_venda=<? echo $row_venda['id']; ?>&interacao=<? echo $row_venda['interacao']; ?>&situacao=editar&acao=Agendamento de implantação&resposta=&agenda_tipo=implantacao&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $venda_editar_tabela_height; ?>&width=<? echo $venda_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 180px;">Agendamento de implantação</a>


                            <? } ?>

                        <? } ?>
                        <!-- fim - Agendamento de implantação -->


                        <!-- Formulário -->
                        <?
                        if (
                            // nivel_venda: 1 =======================================================================================================
                            (
                                ($row_usuario['nivel_venda'] == 1 and $row_usuario['praca'] == $row_venda['praca']) and

                                $row_venda['status_flag'] == "a" and ($row_venda['situacao'] == "analisada" or
                                    $row_venda['situacao'] == "em execução" or
                                    $row_venda['situacao'] == "solicitado agendamento"))
                            // fim - nivel_venda: 1 =================================================================================================
                            or
                            // nivel_venda: 2 =======================================================================================================
                            (
                                ($row_usuario['nivel_venda'] == 2 and $row_usuario['praca'] == $row_venda['praca']) and

                                $row_venda['status_flag'] == "a" and ($row_venda['situacao'] == "analisada" or
                                    $row_venda['situacao'] == "em execução" or
                                    $row_venda['situacao'] == "solicitado agendamento"))
                            // fim - nivel_venda: 2 =================================================================================================
                            or
                            // usuario_responsavel ============================================================================================================================
                            ($row_venda['id_usuario_responsavel'] == $row_usuario['IdUsuario'] and

                                $row_venda['status_flag'] == "a" and ($row_venda['situacao'] == "em execução" or
                                    $row_venda['situacao'] == "solicitado agendamento"))
                            or ($row_venda['encaminhamento_id'] == $row_usuario['IdUsuario'] and

                                $row_venda['status_flag'] == "a" and ($row_venda['situacao'] == "analisada" or
                                    $row_venda['situacao'] == "solicitado agendamento"))
                            // fim - usuario_responsavel =======================================================================================================================
                        ) { ?>

                            <a href="venda_formulario_implantacao_treinamento.php?codigo_empresa=<?php echo $row_venda['codigo_empresa']; ?>&contrato=<?php echo $row_venda['contrato']; ?>&id_venda=<? echo $row_venda['id']; ?>" id="botao_geral2" target="_blank" style="width: 140px;">Form. de Impl. e Trein.</a>


                            <!-- resumo -->
                            <a href="venda_formulario_resumo_implantacao_treinamento.php?codigo_empresa=<?php echo $row_venda['codigo_empresa']; ?>&contrato=<?php echo $row_venda['contrato']; ?>&id_venda=<? echo $row_venda['id']; ?>" id="botao_geral2" name="botao_concluir_implantacao_treinamento" target="_blank" style="width: 220px;">Concluir Implantação e Treinamento</a>
                            <!-- fim - resumo -->


                            <!-- avaliacao -->
                            <? if (strtotime($avaliacao_implantacao) < strtotime(date('Y-m-d H:i:s'))) { ?>

                                <a href="venda_formulario_avaliacao_implantacao.php?codigo_empresa=<?php echo $row_venda['codigo_empresa']; ?>&contrato=<?php echo $row_venda['contrato']; ?>&id_venda=<? echo $row_venda['id']; ?>" id="botao_geral2" target="_blank" style="width: 170px;">Form. Avaliação de Impl.</a>

                            <? } else { ?>

                                <div id="botao_geral_desativado">Form. Avaliação de Impl.</div>

                            <? } ?>
                            <!-- fim - avaliacao -->

                        <? } ?>
                        <!-- fim - Formulário -->


                        <!-- Contatos ========================================================================================================================================= -->
                        <? if (
                            ($row_usuario['nivel_venda'] == 1 and $row_usuario['praca'] == $row_venda['praca']) or ($row_usuario['nivel_venda'] == 2 and $row_usuario['praca'] == $row_venda['praca']) or ($row_usuario['nivel_venda'] == 3 and $row_usuario['praca'] == $row_venda['praca']) or
                            $row_usuario['controle_venda'] == "Y" or
                            $row_venda['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or
                            $row_venda['praca'] == $row_usuario['praca']
                        ) { ?>

                            <a href="venda_editar_tabela.php?id_venda=<? echo $row_venda['id']; ?>&interacao=<? echo $row_venda['interacao']; ?>&situacao=editar&acao=Contato&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $venda_editar_tabela_height; ?>&width=<? echo $venda_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 90px;">Contato (<? echo $row_venda_contato['retorno']; ?>)</a>

                        <? } ?>
                        <!-- fim - Contatos =================================================================================================================================== -->


                        <!-- Validar venda ========================================================================================================================================= -->
                        <? if (
                            ($row_usuario['nivel_venda'] == 1 and $row_usuario['praca'] == "MATRIZ" and $row_usuario['controle_venda'] == "Y") and ($row_venda['validacao_venda_data'] == NULL and $row_venda['validacao_venda_IdUsuario'] == NULL)
                        ) { ?>

                            <a href="venda_editar_tabela.php?id_venda=<? echo $row_venda['id']; ?>&interacao=<? echo $row_venda['interacao']; ?>&situacao=editar&acao=Validar venda&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $venda_editar_tabela_height; ?>&width=<? echo $venda_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 90px;">Validar venda</a>

                        <? } ?>
                        <!-- fim - Validar venda =================================================================================================================================== -->


                        <!-- Questionar ========================================================================================================================================= -->
                        <? if (
                            ($row_usuario['nivel_venda'] == 1 and $row_usuario['praca'] == $row_venda['praca']) or ($row_usuario['nivel_venda'] == 2 and $row_usuario['praca'] == $row_venda['praca']) or ($row_usuario['nivel_venda'] == 3 and $row_usuario['praca'] == $row_venda['praca']) or
                            $row_usuario['controle_venda'] == "Y" or
                            $row_venda['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or
                            $row_venda['praca'] == $row_usuario['praca']
                        ) { ?>

                            <a href="venda_editar_tabela.php?id_venda=<? echo $row_venda['id']; ?>&interacao=<? echo $row_venda['interacao']; ?>&situacao=editar&acao=Questionar&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $venda_editar_tabela_height; ?>&width=<? echo $venda_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 90px;">Questionar</a>

                        <? } ?>
                        <!-- fim - Questionar =================================================================================================================================== -->

                        <!-- Registrar reclamação ========================================================================================================================================= -->
                        <? if ($totalRows_manutencao_dados > 0 and $row_venda['codigo_empresa'] != "") { // contrato existe na tabela 'DA37s9' 
                        ?>

                            <a href="suporte_gerar.php?tipo_suporte=r&inloco=n&cobranca=n&cliente=<? echo $row_venda['codigo_empresa']; ?>6&contrato=<? echo $row_venda['contrato']; ?>&reclamacao_venda=<? echo $row_venda['id']; ?>" id="botao_geral2">Registrar reclamação</a>

                        <? } ?>
                        <!-- fim - Registrar reclamação =================================================================================================================================== -->
                    </td>

                    <td align="right" style="color:#F00; font-weight:bold;" width="350">

                        <!-- Aceitar / Recusar ============================================================================================================================== -->
                        <?
                        if (
                            // analisada ----------------------------------------------------------------------------
                            $row_venda['situacao'] == "analisada" and (

                                ($row_venda['status'] == "encaminhada para usuario responsavel" and ($row_venda['id_usuario_responsavel'] == $row_usuario['IdUsuario']) and ($row_venda['status_recusa'] != "1")) or ($row_venda['status'] == "pendente usuario responsavel" and ($row_venda['encaminhamento_id'] == $row_usuario['IdUsuario']) and ($row_venda['status_recusa'] == "1")))
                            // fim - analisada ----------------------------------------------------------------------------
                            or (

                                ($row_venda['status'] == "devolvida para usuario responsavel" and $row_venda['id_usuario_responsavel'] == $row_usuario['IdUsuario']))

                        ) { ?>

                            <div style="float:right; margin-left: 5px;">
                                <a href="venda_editar_tabela.php?id_venda=<? echo $row_venda['id']; ?>&interacao=<? echo $row_venda['interacao']; ?>&situacao=<? echo $row_venda['situacao']; ?>&acao=Aceitar&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $venda_editar_tabela_height; ?>&width=<? echo $venda_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2">Aceitar</a>

                                <a href="venda_editar_tabela.php?id_venda=<? echo $row_venda['id']; ?>&interacao=<? echo $row_venda['interacao']; ?>&situacao=<? echo $row_venda['situacao']; ?>&acao=Recusar&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $venda_editar_tabela_height; ?>&width=<? echo $venda_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2">Recusar</a>
                            </div>

                        <? } ?>
                        <!-- fim - Aceitar / Recusar ======================================================================================================================== -->


                        <!-- Mensagens ==================================================================================================================================== -->
                        <? if (
                            $row_venda['situacao'] == "analisada" and

                            $row_venda['status'] == "encaminhada para usuario responsavel" and
                            $row_venda['status_recusa'] != "1"
                        ) { ?>

                            <div id="texto_botao_geral">Aguardando aceitação do usuário responsável</div>

                        <? } ?>


                        <? if (
                            $row_venda['situacao'] == "analisada" and

                            $row_venda['status'] == "pendente usuario responsavel" and
                            $row_venda['status_recusa'] == "1"
                        ) { ?>

                            <div id="texto_botao_geral">Aguardando aceitação de recusa</div>

                        <? } ?>

                        <?
                        if (
                            $row_venda['status'] == "devolvida para usuario responsavel"
                        ) { ?>

                            <div id="texto_botao_geral">Aguardando aceitação de devolução</div>

                        <? } ?>
                        <!-- fim - Mensagens ============================================================================================================================== -->

                    </td>
                </tr>
            </table>
        </div>

    <? } ?>

    <? if (
        ($row_venda['situacao'] == "solucionada" or $row_venda['situacao'] == "cancelada") or 
        ($row_venda['situacao'] != "solucionada" and $row_venda['situacao'] != "cancelada" and $row_venda['situacao'] != "criada" and $validade_passado_dias >= 0)
    ) { ?>

        <div class="div_solicitacao_linhas4" id="botoes">
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td style="text-align:left">

                        <!-- Estornar -->
                        <?
                        if (
                            ($row_venda['situacao'] == "solucionada" or $row_venda['situacao'] == "cancelada") and 
                            $row_usuario['controle_venda'] == "Y"
                        ) {
                        ?>

                            <a href="venda_editar_tabela.php?id_venda=<? echo $row_venda['id']; ?>&interacao=<? echo $row_venda['interacao']; ?>&situacao=editar&acao=Estornar&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $venda_editar_tabela_height; ?>&width=<? echo $venda_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 70px;">Estornar</a>

                        <? } ?>
                        <!-- fim - Estornar -->

                        <!-- Questionar ========================================================================================================================================= -->
                        <? if (
                            ($row_usuario['nivel_venda'] == 1 and $row_usuario['praca'] == $row_venda['praca']) or ($row_usuario['nivel_venda'] == 2 and $row_usuario['praca'] == $row_venda['praca']) or ($row_usuario['nivel_venda'] == 3 and $row_usuario['praca'] == $row_venda['praca']) or
                            $row_usuario['controle_venda'] == "Y" or
                            $row_venda['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or
                            $row_venda['praca'] == $row_usuario['praca']
                        ) { ?>

                            <a href="venda_editar_tabela.php?id_venda=<? echo $row_venda['id']; ?>&interacao=<? echo $row_venda['interacao']; ?>&situacao=editar&acao=Questionar&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $venda_editar_tabela_height; ?>&width=<? echo $venda_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 90px;">Questionar</a>

                        <? } ?>
                        <!-- fim - Questionar =================================================================================================================================== -->

                        <!-- Formulário -->
                        <? if (
                            $row_venda['situacao'] == "solucionada" and ($row_usuario['administrador_site'] == 'Y' or $row_usuario['controle_venda'] == "Y")
                        ) { ?>

                            <a href="venda_formulario_implantacao_treinamento.php?codigo_empresa=<?php echo $row_venda['codigo_empresa']; ?>&contrato=<?php echo $row_venda['contrato']; ?>&id_venda=<? echo $row_venda['id']; ?>" id="botao_geral2" target="_blank" style="width: 140px;">Form. de Impl. e Trein.</a>

                            <a href="venda_formulario_resumo_implantacao_treinamento.php?codigo_empresa=<?php echo $row_venda['codigo_empresa']; ?>&contrato=<?php echo $row_venda['contrato']; ?>&id_venda=<? echo $row_venda['id']; ?>" id="botao_geral2" target="_blank" style="width: 220px;">Conclusão Implantação e Treinamento</a>

                            <a href="venda_formulario_avaliacao_implantacao.php?codigo_empresa=<?php echo $row_venda['codigo_empresa']; ?>&contrato=<?php echo $row_venda['contrato']; ?>&id_venda=<? echo $row_venda['id']; ?>" id="botao_geral2" target="_blank" style="width: 170px;">Form. Avaliação de Impl.</a>

                        <? } ?>
                        <!-- fim - Formulário -->

                    </td>
                </tr>
            </table>
        </div>
        
    <? } ?>
    <!-- fim - Botões ================================================================================================================================================= -->

    <? 
    if(
        $row_venda['situacao'] != "criada" and 
        $row_venda['situacao'] != "solucionada" and 
        $row_venda['situacao'] != "cancelada" and 
        
        ($row_usuario['controle_venda'] == "Y" or $validade_passado_dias < 0) and 
        
        $row_venda['atendimento'] <> "SolCan" and  
        $row_venda['atendimento'] <> "FinAte" and 
        (
            ($row_usuario['nivel_venda'] == 1 and $row_usuario['praca'] == $row_venda['praca']) or 
            ($row_usuario['nivel_venda'] == 2 and $row_usuario['praca'] == $row_venda['praca']) or 
            ($row_usuario['nivel_venda'] == 3 and $row_usuario['praca'] == $row_venda['praca']) or 
            $row_venda['id_usuario_responsavel'] == $row_usuario['IdUsuario']
        )
    ) { 
    ?>
        <div class="div_solicitacao_linhas4" id="botoes">
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td style="text-align:left">

                        <!-- Iniciar atendimento ========================================================================================================================================= -->
                        <? if ($row_venda['atendimento'] == NULL) { ?>
                            <a href="venda_editar_tabela.php?id_venda=<? echo $row_venda['id']; ?>&interacao=<? echo $row_venda['interacao']; ?>&situacao=&acao=Iniciar atendimento&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $venda_editar_tabela_height; ?>&width=<? echo $venda_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 120px;">Iniciar Atendimento</a>
                        <? } ?>
                        <!-- fim - Iniciar atendimento =================================================================================================================================== -->

                        <!-- Reagendar atendimento ========================================================================================================================================= -->
                        <? 
                        if (
                            $row_venda['atendimento'] == NULL or 
                            $row_venda['atendimento'] == "IniAte" or 
                            $row_venda['atendimento'] == "SolRea" or 
                            $row_venda['atendimento'] == "PenAte"
                        ) { 
                        ?>
                            <a href="venda_editar_tabela.php?id_venda=<? echo $row_venda['id']; ?>&interacao=<? echo $row_venda['interacao']; ?>&situacao=&acao=Reagendar atendimento&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $venda_editar_tabela_height; ?>&width=<? echo $venda_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 140px;">Reagendar Atendimento</a>
                        <? } ?>
                        <!-- fim - Reagendar atendimento =================================================================================================================================== -->

                        <!-- Cancelar atendimento ========================================================================================================================================= -->
                        <? 
                        if (
                            $row_venda['atendimento'] == NULL or 
                            $row_venda['atendimento'] == "IniAte" or 
                            $row_venda['atendimento'] == "SolRea" or 
                            $row_venda['atendimento'] == "PenAte"
                        ) { 
                        ?>
                            <a href="venda_editar_tabela.php?id_venda=<? echo $row_venda['id']; ?>&interacao=<? echo $row_venda['interacao']; ?>&situacao=&acao=Cancelar atendimento&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $venda_editar_tabela_height; ?>&width=<? echo $venda_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 130px;">Cancelar Atendimento</a>
                        <? } ?>
                        <!-- fim - Cancelar atendimento =================================================================================================================================== -->

                        <!-- Finalizar atendimento ========================================================================================================================================= -->
                        <? 
                        if (
                            $row_venda['atendimento'] == "IniAte" or 
                            $row_venda['atendimento'] == "SolRea" or 
                            $row_venda['atendimento'] == "PenAte"
                        ) { 
                        ?>
                            <a href="venda_editar_tabela.php?id_venda=<? echo $row_venda['id']; ?>&interacao=<? echo $row_venda['interacao']; ?>&situacao=&acao=Finalizar atendimento&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $venda_editar_tabela_height; ?>&width=<? echo $venda_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 120px;">Finalizar Atendimento</a>
                        <? } ?>
                        <!-- fim - Finalizar atendimento =================================================================================================================================== -->

                    </td>
                </tr>
            </table>
        </div>
    <? } ?>

    <!-- Encerrar (mensagem) -->
    <? if ($row_venda['status_flag'] == 'a' and $row_venda['ordem_servico'] == 0 and $validade_passado_dias < 0) { ?>
        <div class="div_solicitacao_linhas4">
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td style="text-align: left">
                        <span style="font-weight: bold; color: #F00;">
                            O botão encerrar somente será liberado após preenchimento correto do campo 'ordem de serviço' e desbloqueio por parte da matriz.
                        </span>
                    </td>
                </tr>
            </table>
        </div>
    <? } ?>
    <!-- fim - Encerrar (mensagem) -->


    <!-- Dilação de prazo (mensagem) -->
    <? if ($row_venda['status_flag'] == 'a' and $row_venda['dilacao_prazo_proposto'] > 0 and $validade_passado_dias < 0) { ?>
        <div class="div_solicitacao_linhas4">
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td style="text-align: left">
                        <span style="font-weight: bold; color: #F00;">
                            Foi solicitado a dilação da validade da venda.
                        </span>
                    </td>
                </tr>
            </table>
        </div>
    <? } ?>
    <!-- fim - Dilação de prazo (mensagem) -->


    <!-- observacao -->
    <div class="div_solicitacao_linhas4">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td style="text-align: left">
                    <span class="label_solicitacao">Observação: </span>
                    <br>
                    <?php echo $row_venda['observacao']; ?>
                    <?php if ($row_venda['status_flag'] == "a") { ?>
                        <?php if ($row_venda['id_usuario_responsavel'] == $row_usuario['IdUsuario']) { ?>
                            <a href="venda_editar_tabela.php?id_venda=<? echo $row_venda['id']; ?>&interacao=<? echo $row_venda['interacao']; ?>&situacao=editar&acao=Alterar observação&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $venda_editar_tabela_height; ?>&width=<? echo $venda_editar_tabela_width; ?>&modal=true" class="thickbox">
                                <img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar observação"></a>
                        <? } ?>
                    <? } ?>
                </td>
            </tr>
        </table>
    </div>
    <!-- fim - observacao -->


    <!-- Implantação -->
    <div class="div_solicitacao_linhas4">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td style="text-align: left; font-size: 14px;">

                    <div style="font-weight: bold;">
                        Implantação:
                        <?php if ($row_usuario['administrador_site'] == 'Y' and $row_venda['status_flag'] == "a") { ?>
                            <a href="venda_editar_tabela.php?id_venda=<? echo $row_venda['id']; ?>&interacao=<? echo $row_venda['interacao']; ?>&situacao=editar&acao=Alterar qtde de tempo para implantação&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $venda_editar_tabela_height; ?>&width=<? echo $venda_editar_tabela_width; ?>&modal=true" class="thickbox">
                                <img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar qtde de tempo para implantação"></a>
                        <? } ?>
                    </div>
                    <div style="height: 2px; background-color: #CCC; margin-top: 5px; margin-bottom: 5px;"></div>

                    <!-- Disponibilizado -->
                    <span class="label_solicitacao">Disponibilizado: </span>
                    <?
                    $tHoras = $contador_implantacao_segundo / 3600;
                    $tMinutos = $contador_implantacao_segundo % 3600 / 60;

                    echo sprintf('%02dh %02dm', $tHoras, $tMinutos);
                    ?> |
                    <!-- fim - Disponibilizado -->


                    <!-- Agendado -->
                    <span class="label_solicitacao">Agendado: </span>
                    <?
                    $tHoras = $contador_implantacao_segundo_agendado / 3600;
                    $tMinutos = $contador_implantacao_segundo_agendado % 3600 / 60;

                    echo sprintf('%02dh %02dm', $tHoras, $tMinutos);
                    ?> |
                    <!-- fim - Agendado -->


                    <!-- Finalizado -->
                    <span class="label_solicitacao">Finalizado: </span>
                    <?
                    $tHoras = $contador_implantacao_segundo_finalizado / 3600;
                    $tMinutos = $contador_implantacao_segundo_finalizado % 3600 / 60;

                    echo sprintf('%02dh %02dm', $tHoras, $tMinutos);
                    ?> |
                    <!-- fim - Finalizado -->


                    <!-- Cancelado -->
                    <span class="label_solicitacao">Cancelado: </span>
                    <?
                    $tHoras = $contador_implantacao_segundo_cancelado / 3600;
                    $tMinutos = $contador_implantacao_segundo_cancelado % 3600 / 60;

                    echo sprintf('%02dh %02dm', $tHoras, $tMinutos);
                    ?> |
                    <!-- fim - Cancelado -->


                    <!-- Restante -->
                    <span class="label_solicitacao">Restante: </span>
                    <?
                    $tHoras = $contador_implantacao_segundo_restante / 3600;
                    $tMinutos = $contador_implantacao_segundo_restante % 3600 / 60;

                    echo sprintf('%02dh %02dm', $tHoras, $tMinutos);
                    ?>
                    <!-- fim - Restante -->

                </td>
            </tr>
        </table>
    </div>
    <!-- fim - Implantação -->


    <!-- agenda_implantacao -->
    <? if ($totalRows_agenda_implantacao > 0) { ?>
        <div class="div_solicitacao_linhas4">
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td style="text-align: left">
                        <span class="label_solicitacao">Agenda implantação: </span>
                        <!-- tabela -->
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="padding: 5px;">
                            <tr bgcolor="#F1F1F1">
                                <td style="padding: 5px;" width="70"><strong>Número</strong></td>
                                <td style="padding: 5px;" width="180"><strong>Data</strong></td>
                                <td style="padding: 5px;" width="100"><strong>Status</strong></td>
                                <td style="padding: 5px;" width="300"><strong>Ações</strong></td>
                                <td style="padding: 5px;"><strong>Descrição</strong></td>
                            </tr>

                            <? $contador_agenda = 0; ?>

                            <?php do { ?>
                                <tr bgcolor="<? if (($contador_agenda % 2) == 1) {
                                                    echo "#F1F1F1";
                                                } else {
                                                    echo "#FFFFFF";
                                                } ?>">
                                    <td style="padding: 5px;"><?php echo $row_agenda_implantacao['id_agenda']; ?></td>
                                    <td style="padding: 5px;">
                                        <!-- tempo -->
                                        <span class="label_solicitacao">Tempo: </span>
                                        <?
                                        $data_ini = strtotime($row_agenda_implantacao['data_inicio']);
                                        $data_final = strtotime($row_agenda_implantacao['data']);

                                        $tHoras = ($data_final - $data_ini) / 3600;
                                        $tMinutos = ($data_final - $data_ini) % 3600 / 60;

                                        echo sprintf('%02dh %02dm', $tHoras, $tMinutos);
                                        ?>
                                        <!-- fim - tempo -->
                                        <br>
                                        Início: <? echo date('d-m-Y  H:i:s', strtotime($row_agenda_implantacao['data_inicio'])); ?>
                                        <br>
                                        Fim:&nbsp;&nbsp;&nbsp;&nbsp; <? echo date('d-m-Y  H:i:s', strtotime($row_agenda_implantacao['data'])); ?>
                                    </td>
                                    <td style="padding: 5px;">
                                        <?php if ($row_agenda_implantacao['status'] == "a") {
                                            echo "Agendado";
                                        } ?>
                                        <?php if ($row_agenda_implantacao['status'] == "f") {
                                            echo "Finalizado";
                                        } ?>
                                        <?php if ($row_agenda_implantacao['status'] == "c") {
                                            echo "Cancelado";
                                        } ?>
                                    </td>

                                    <td style="padding: 5px;">

                                        <?php if ($row_agenda_implantacao['status'] == "a") { ?>

                                            <!-- botoes -->
                                            <div id="botoes">

                                                <? if (($row_venda['status_flag'] == "a") and (
                                                        ($row_usuario['nivel_venda'] == 1 and $row_usuario['praca'] == $row_venda['praca']) or ($row_usuario['nivel_venda'] == 2 and $row_usuario['praca'] == $row_venda['praca']) or ($row_usuario['nivel_venda'] == 3 and $row_usuario['praca'] == $row_venda['praca']) or
                                                        $row_venda['id_usuario_responsavel'] == $row_usuario['IdUsuario'])
                                                ) { ?>

                                                    <a href="venda_editar_tabela.php?id_venda=<? echo $row_venda['id']; ?>&interacao=<? echo $row_venda['interacao']; ?>&situacao=editar&acao=Reagendar implantação&resposta=&id_agenda=<? echo $row_agenda_implantacao['id_agenda']; ?>&agenda_tipo=implantacao&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $venda_editar_tabela_height; ?>&width=<? echo $venda_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 70px;">Reagendar</a>

                                                <? } ?>

                                                <? if (($row_venda['status_flag'] == "a") and (
                                                        ($row_usuario['nivel_venda'] == 1 and $row_usuario['praca'] == $row_venda['praca']) or ($row_usuario['nivel_venda'] == 2 and $row_usuario['praca'] == $row_venda['praca']) or
                                                        $row_venda['id_usuario_responsavel'] == $row_usuario['IdUsuario'])
                                                ) { ?>

                                                    <a href="venda_editar_tabela.php?id_venda=<? echo $row_venda['id']; ?>&interacao=<? echo $row_venda['interacao']; ?>&situacao=editar&acao=Finalizar agendamento de implantação&resposta=&id_agenda=<? echo $row_agenda_implantacao['id_agenda']; ?>&agenda_tipo=implantacao&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $venda_editar_tabela_height; ?>&width=<? echo $venda_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 70px;">Finalizar</a>

                                                    <a href="venda_editar_tabela.php?id_venda=<? echo $row_venda['id']; ?>&interacao=<? echo $row_venda['interacao']; ?>&situacao=editar&acao=Cancelar agendamento de implantação&resposta=&id_agenda=<? echo $row_agenda_implantacao['id_agenda']; ?>&agenda_tipo=implantacao&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $venda_editar_tabela_height; ?>&width=<? echo $venda_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 70px;">Cancelar</a>

                                                <? } ?>

                                            </div>
                                            <!-- fim - botoes -->

                                        <? } ?>

                                    </td>

                                    <td style="padding: 5px;">
                                        <?php if ($row_agenda_implantacao['id_usuario_responsavel'] != "") { ?>
                                            Usuário responsável:
                                            <strong>
                                                <?php
                                                // busca usuario_responsavel selecionado
                                                mysql_select_db($database_conexao, $conexao);
                                                $query_usuario_responsavel_selecionado = sprintf(
                                                    "
                                                    SELECT nome 
                                                    FROM usuarios 
                                                    WHERE IdUsuario = %s",
                                                    GetSQLValueString($row_agenda_implantacao['id_usuario_responsavel'], "int")
                                                );
                                                $usuario_responsavel_selecionado = mysql_query($query_usuario_responsavel_selecionado, $conexao) or die(mysql_error());
                                                $row_usuario_responsavel_selecionado = mysql_fetch_assoc($usuario_responsavel_selecionado);
                                                $totalRows_usuario_responsavel_selecionado = mysql_num_rows($usuario_responsavel_selecionado);
                                                echo $row_usuario_responsavel_selecionado['nome'];
                                                mysql_free_result($usuario_responsavel_selecionado);
                                                // fim - busca usuario_responsavel selecionado
                                                ?>
                                            </strong>
                                            <br>
                                        <? } ?>

                                        <?php if ($row_agenda_implantacao['venda_solicitante'] != "") { ?>
                                            Solicitante: <strong><?php echo $row_agenda_implantacao['venda_solicitante']; ?></strong>
                                            <br>
                                        <? } ?>

                                        <?php if ($row_agenda_implantacao['venda_receptor'] != "") { ?>
                                            Receptor: <strong><?php echo $row_agenda_implantacao['venda_receptor']; ?></strong>
                                            <br>
                                        <? } ?>

                                        <?php if ($row_agenda_implantacao['venda_responsavel_cancelado'] != "") { ?>
                                            Resp. pelo cancelamento: <strong><?php echo $row_agenda_implantacao['venda_responsavel_cancelado']; ?></strong>
                                            <br>
                                        <? } ?>

                                        <?php echo $row_agenda_implantacao['descricao']; ?>
                                    </td>
                                </tr>
                                <? $contador_agenda = $contador_agenda + 1; ?>
                            <?php } while ($row_agenda_implantacao = mysql_fetch_assoc($agenda_implantacao)); ?>

                        </table>
                        <!-- fim - tabela -->
                    </td>
                </tr>
            </table>
        </div>
    <? } ?>
    <!-- fim - agenda_implantacao -->


    <!-- Treinamento -->
    <div class="div_solicitacao_linhas4">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td style="text-align: left; font-size: 14px;">

                    <div style="font-weight: bold;">
                    Treinamento:
                    <?php if ($row_usuario['administrador_site'] == 'Y' and $row_venda['status_flag'] == "a") { ?>
                        <a href="venda_editar_tabela.php?id_venda=<? echo $row_venda['id']; ?>&interacao=<? echo $row_venda['interacao']; ?>&situacao=editar&acao=Alterar qtde de tempo para treinamento&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $venda_editar_tabela_height; ?>&width=<? echo $venda_editar_tabela_width; ?>&modal=true" class="thickbox">
                            <img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar qtde de tempo para treinamento"></a>
                    <? } ?>
                    </div>
                    <div style="height: 2px; background-color: #CCC; margin-top: 5px; margin-bottom: 5px;"></div>

                    <!-- Adquirido -->
                    <span class="label_solicitacao">Adquirido: </span>
                    <?
                    $tHoras = $contador_treinamento_segundo / 3600;
                    $tMinutos = $contador_treinamento_segundo % 3600 / 60;

                    echo sprintf('%02dh %02dm', $tHoras, $tMinutos);
                    ?> |
                    <!-- fim - Adquirido -->


                    <!-- Agendado -->
                    <span class="label_solicitacao">Agendado: </span>
                    <?
                    $tHoras = $contador_treinamento_segundo_agendado / 3600;
                    $tMinutos = $contador_treinamento_segundo_agendado % 3600 / 60;

                    echo sprintf('%02dh %02dm', $tHoras, $tMinutos);
                    ?> |
                    <!-- fim - Agendado -->


                    <!-- Finalizado -->
                    <span class="label_solicitacao">Finalizado: </span>
                    <?
                    $tHoras = $contador_treinamento_segundo_finalizado / 3600;
                    $tMinutos = $contador_treinamento_segundo_finalizado % 3600 / 60;

                    echo sprintf('%02dh %02dm', $tHoras, $tMinutos);
                    ?> |
                    <!-- fim - Finalizado -->


                    <!-- Cancelado -->
                    <span class="label_solicitacao">Cancelado: </span>
                    <?
                    $tHoras = $contador_treinamento_segundo_cancelado / 3600;
                    $tMinutos = $contador_treinamento_segundo_cancelado % 3600 / 60;

                    echo sprintf('%02dh %02dm', $tHoras, $tMinutos);
                    ?> |
                    <!-- fim - Cancelado -->


                    <!-- Restante -->
                    <span class="label_solicitacao">Restante: </span>
                    <?
                    $tHoras = $contador_treinamento_segundo_restante / 3600;
                    $tMinutos = $contador_treinamento_segundo_restante % 3600 / 60;

                    echo sprintf('%02dh %02dm', $tHoras, $tMinutos);
                    ?>
                    <!-- fim - Restante -->

                </td>
            </tr>
        </table>
    </div>
    <!-- fim - Treinamento -->


    <!-- agenda_treinamento -->
    <? if ($totalRows_agenda_treinamento > 0) { ?>
        <div class="div_solicitacao_linhas4">
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td style="text-align: left">
                        <span class="label_solicitacao">Agenda treinamento: </span>
                        <!-- tabela -->
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="padding: 5px;">
                            <tr bgcolor="#F1F1F1">
                                <td style="padding: 5px;" width="70"><strong>Número</strong></td>
                                <td style="padding: 5px;" width="180"><strong>Data</strong></td>
                                <td style="padding: 5px;" width="100"><strong>Status</strong></td>
                                <td style="padding: 5px;" width="300"><strong>Ações</strong></td>
                                <td style="padding: 5px;"><strong>Descrição</strong></td>
                            </tr>

                            <? $contador_agenda = 0; ?>

                            <?php do { ?>
                                <tr bgcolor="<? if (($contador_agenda % 2) == 1) {
                                                    echo "#F1F1F1";
                                                } else {
                                                    echo "#FFFFFF";
                                                } ?>">
                                    <td style="padding: 5px;"><?php echo $row_agenda_treinamento['id_agenda']; ?></td>
                                    <td style="padding: 5px;">
                                        <!-- tempo -->
                                        <span class="label_solicitacao">Tempo: </span>
                                        <?
                                        $data_ini = strtotime($row_agenda_treinamento['data_inicio']);
                                        $data_final = strtotime($row_agenda_treinamento['data']);

                                        $tHoras = ($data_final - $data_ini) / 3600;
                                        $tMinutos = ($data_final - $data_ini) % 3600 / 60;

                                        echo sprintf('%02dh %02dm', $tHoras, $tMinutos);
                                        ?>
                                        <!-- fim - tempo -->
                                        <br>
                                        Início: <? echo date('d-m-Y  H:i:s', strtotime($row_agenda_treinamento['data_inicio'])); ?>
                                        <br>
                                        Fim:&nbsp;&nbsp;&nbsp;&nbsp; <? echo date('d-m-Y  H:i:s', strtotime($row_agenda_treinamento['data'])); ?>
                                    </td>
                                    <td style="padding: 5px;">
                                        <?php if ($row_agenda_treinamento['status'] == "a") {
                                            echo "Agendado";
                                        } ?>
                                        <?php if ($row_agenda_treinamento['status'] == "f") {
                                            echo "Finalizado";
                                        } ?>
                                        <?php if ($row_agenda_treinamento['status'] == "c") {
                                            echo "Cancelado";
                                        } ?>
                                    </td>

                                    <td style="padding: 5px;">

                                        <?php if ($row_agenda_treinamento['status'] == "a") { ?>

                                            <div id="botoes">

                                                <a href="venda_editar_tabela.php?id_venda=<? echo $row_venda['id']; ?>&interacao=<? echo $row_venda['interacao']; ?>&situacao=editar&acao=Reagendar treinamento&resposta=&id_agenda=<? echo $row_agenda_treinamento['id_agenda']; ?>&agenda_tipo=treinamento&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $venda_editar_tabela_height; ?>&width=<? echo $venda_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 70px;">Reagendar</a>

                                                <? if (($row_venda['status_flag'] == "a") and (
                                                        ($row_usuario['nivel_venda'] == 1 and $row_usuario['praca'] == $row_venda['praca']) or ($row_usuario['nivel_venda'] == 2 and $row_usuario['praca'] == $row_venda['praca']) or
                                                        $row_venda['id_usuario_responsavel'] == $row_usuario['IdUsuario'])
                                                ) { ?>

                                                    <a href="venda_editar_tabela.php?id_venda=<? echo $row_venda['id']; ?>&interacao=<? echo $row_venda['interacao']; ?>&situacao=editar&acao=Finalizar agendamento de treinamento&resposta=&id_agenda=<? echo $row_agenda_treinamento['id_agenda']; ?>&agenda_tipo=treinamento&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $venda_editar_tabela_height; ?>&width=<? echo $venda_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 70px;">Finalizar</a>

                                                    <a href="venda_editar_tabela.php?id_venda=<? echo $row_venda['id']; ?>&interacao=<? echo $row_venda['interacao']; ?>&situacao=editar&acao=Cancelar agendamento de treinamento&resposta=&id_agenda=<? echo $row_agenda_treinamento['id_agenda']; ?>&agenda_tipo=treinamento&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $venda_editar_tabela_height; ?>&width=<? echo $venda_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2" style="width: 70px;">Cancelar</a>

                                                <? } ?>

                                            </div>

                                        <? } ?>

                                    </td>

                                    <td style="padding: 5px;">
                                        <?php if ($row_agenda_treinamento['id_usuario_responsavel'] != "") { ?>
                                            Usuário responsável:
                                            <strong>
                                                <?php
                                                // busca usuario_responsavel selecionado
                                                mysql_select_db($database_conexao, $conexao);
                                                $query_usuario_responsavel_selecionado = sprintf(
                                                    "
                                                SELECT nome 
                                                FROM usuarios 
                                                WHERE IdUsuario = %s",
                                                    GetSQLValueString($row_agenda_treinamento['id_usuario_responsavel'], "int")
                                                );
                                                $usuario_responsavel_selecionado = mysql_query($query_usuario_responsavel_selecionado, $conexao) or die(mysql_error());
                                                $row_usuario_responsavel_selecionado = mysql_fetch_assoc($usuario_responsavel_selecionado);
                                                $totalRows_usuario_responsavel_selecionado = mysql_num_rows($usuario_responsavel_selecionado);
                                                echo $row_usuario_responsavel_selecionado['nome'];
                                                mysql_free_result($usuario_responsavel_selecionado);
                                                // fim - busca usuario_responsavel selecionado
                                                ?>
                                            </strong>
                                            <br>
                                        <? } ?>

                                        <?php if ($row_agenda_treinamento['venda_solicitante'] != "") { ?>
                                            Solicitante: <strong><?php echo $row_agenda_treinamento['venda_solicitante']; ?></strong>
                                            <br>
                                        <? } ?>

                                        <?php if ($row_agenda_treinamento['venda_receptor'] != "") { ?>
                                            Receptor: <strong><?php echo $row_agenda_treinamento['venda_receptor']; ?></strong>
                                            <br>
                                        <? } ?>

                                        <?php if ($row_agenda_treinamento['venda_responsavel_cancelado'] != "") { ?>
                                            Resp. pelo cancelamento: <strong><?php echo $row_agenda_treinamento['venda_responsavel_cancelado']; ?></strong>
                                            <br>
                                        <? } ?>

                                        <?php echo $row_agenda_treinamento['descricao']; ?>
                                    </td>
                                </tr>
                                <? $contador_agenda = $contador_agenda + 1; ?>
                            <?php } while ($row_agenda_treinamento = mysql_fetch_assoc($agenda_treinamento)); ?>

                        </table>
                        <!-- fim - tabela -->
                    </td>
                </tr>
            </table>
        </div>
    <? } ?>
    <!-- fim - agenda_treinamento -->

    <!-- reclamacao_venda -->
    <? if ($totalRows_reclamacao_venda > 0) { ?>
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

                            <? $contador_reclamacao_venda = 0; ?>

                            <?php do { ?>
                                <tr bgcolor="<? if (($contador_reclamacao_venda % 2) == 1) {
                                                    echo "#F1F1F1";
                                                } else {
                                                    echo "#FFFFFF";
                                                } ?>">
                                    <td style="padding: 5px;"><?php echo $row_reclamacao_venda['id']; ?></td>
                                    <td style="padding: 5px;"><? echo date('d-m-Y  H:i', strtotime($row_reclamacao_venda['data_suporte'])); ?></td>
                                    <td style="padding: 5px;"><?php echo $row_reclamacao_venda['situacao']; ?></td>
                                    <td style="padding: 5px;"><?php echo $row_reclamacao_venda['titulo']; ?></td>
                                    <td style="padding: 5px;"><a href="suporte_editar.php?id_suporte=<? echo $row_reclamacao_venda['id']; ?>&padrao=sim" target="_blank" id="botao_geral2" style="width: 70px;">Abrir</a></td>
                                </tr>
                                <? $contador_reclamacao_venda = $contador_reclamacao_venda + 1; ?>
                            <?php } while ($row_reclamacao_venda = mysql_fetch_assoc($reclamacao_venda)); ?>

                        </table>
                        <!-- fim - tabela -->
                    </td>
                </tr>
            </table>
        </div>
    <? } ?>
    <!-- fim - reclamacao_venda -->

    <div class="div_solicitacao_linhas3">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>

                <td width="200" valign="top">

                    <? if ($row_venda['situacao'] != "solucionada" and $row_venda['situacao'] != "cancelada") { ?>

                        <!-- usuario_responsavel leu em -->
                        <? if ($row_venda['id_usuario_responsavel'] != "") { ?>
                            <? echo $row_venda['usuario_responsavel']; ?>
                            <!-- alterar usuario_responsavel -->
                            <?php if (((($row_usuario['nivel_venda'] == 1 or $row_usuario['nivel_venda'] == 2) and $row_usuario['praca'] == $row_venda['praca'])) and $row_venda['status_flag'] == "a") { ?>
                                <a href="venda_editar_tabela.php?id_venda=<? echo $row_venda['id']; ?>&interacao=<? echo $row_venda['interacao']; ?>&situacao=editar&acao=Alterar usuário responsável&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $venda_editar_tabela_height; ?>&width=<? echo $venda_editar_tabela_width; ?>&modal=true" class="thickbox">
                                    <img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar usuário responsável">
                                </a>
                            <? } ?>
                            <!-- fim - alterar usuario_responsavel -->

                            <br>
                            <span class="label_solicitacao">Usuário Responsável leu em:</span>
                            <br>
                            <? if ($row_venda['usuario_responsavel_leu'] != "") {
                                echo date('d-m-Y - H:i:s', strtotime($row_venda['usuario_responsavel_leu']));
                            } else {
                                echo "não leu";
                            } ?>
                        <? } else { ?>
                            <br><br>
                            <span style="color:#F00;">Sem usuário responsável</span>
                            <!-- alterar usuario_responsavel -->
                            <?php if (((($row_usuario['nivel_venda'] == 1 or $row_usuario['nivel_venda'] == 2) and $row_usuario['praca'] == $row_venda['praca'])) and $row_venda['status_flag'] == "a") { ?>
                                <a href="venda_editar_tabela.php?id_venda=<? echo $row_venda['id']; ?>&interacao=<? echo $row_venda['interacao']; ?>&situacao=editar&acao=Alterar usuário responsável&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $venda_editar_tabela_height; ?>&width=<? echo $venda_editar_tabela_width; ?>&modal=true" class="thickbox">
                                    <img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar usuário responsável">
                                </a>
                            <? } ?>
                            <!-- fim - alterar usuario_responsavel -->
                            <br>
                            <span class="label_solicitacao">Usuário Responsável</span>
                        <? } ?>
                        <!-- fim - usuario_responsavel leu em -->


                        <!-- duração -->
                        <br><br>
                        <span class="label_solicitacao">Duração:</span>
                        <br>
                        <?
                        $data_ini = strtotime($row_venda['data_venda']);
                        $data_final = strtotime(date("Y-m-d H:i:s"));

                        $tHoras = ($data_final - $data_ini) / 3600;
                        $tMinutos = ($data_final - $data_ini) % 3600 / 60;

                        echo sprintf('%02dh %02dm', $tHoras, $tMinutos);
                        ?>
                        <!-- fim - duração -->

                    <? } ?>

                    <? if ($row_venda['situacao'] == "solucionada" or $row_venda['situacao'] == "cancelada") { ?>

                        <!-- usuario_responsavel leu em -->
                        <? echo $row_venda['usuario_responsavel']; ?>
                        <br>
                        <span class="label_solicitacao">Usuário Responsável</span>
                        <!-- fim - usuario_responsavel leu em -->

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
                    <a href="#" class="botao_geral" style="width: 150px;" onclick="print()">Imprimir</a>

                    <a href="prospeccao_editar_espelho.php?id_prospeccao=<?php echo $row_venda['id_prospeccao']; ?>&id_venda=<? echo $row_venda['id']; ?>" class="botao_geral" style="width: 150px;">Imprimir espelho</a>

                    <!-- anexos -->
                    <a href="venda_editar_upload.php?id_venda=<? echo $row_venda['id']; ?>&situacao=&acao=Arquivos em  anexo&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $venda_editar_tabela_height; ?>&width=<? echo $venda_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral" style="width: 150px;">Arquivos em anexo (<? echo $totalRows_arquivos_anexos; ?>)</a>
                    <!-- fim - anexos -->

                    <!-- Suportes vinculados -->
                    <a href="suporte_vinculo.php?codigo_empresa=<? echo $row_venda['codigo_empresa']; ?>&situacao=&acao=Suportes vinculados&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $venda_editar_tabela_height; ?>&width=<? echo $venda_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral" style="width: 150px;">Suportes</a>
                    <!-- fim - Suportes vinculados -->


                    <!-- Solicitações vinculadas -->
                    <a href="solicitacao_vinculo.php?codigo_empresa=<? echo $row_venda['codigo_empresa']; ?>&situacao=&acao=Solicitações vinculadas&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $venda_editar_tabela_height; ?>&width=<? echo $venda_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral" style="width: 150px;">Solicitações</a>
                    <!-- fim - Solicitações vinculadas -->
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
mysql_free_result($venda);
mysql_free_result($empresa_dados);
mysql_free_result($manutencao_dados);
mysql_free_result($descricao);
mysql_free_result($arquivos_anexos);
mysql_free_result($prospeccao);
mysql_free_result($venda_modulos);
mysql_free_result($venda_contato);
mysql_free_result($agenda_treinamento);
mysql_free_result($agenda_treinamento_agendado);
mysql_free_result($agenda_implantacao);
mysql_free_result($agenda_implantacao_agendado);
mysql_free_result($reclamacao_venda);
mysql_free_result($reclamacao_consulta);
?>