<?
if ($_SERVER["HTTP_X_CRON_AUTH"] != "3d5a01b606de5e79d6bcdaac8db316a0") {
    die("Acesso nao Autorizado");
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

require_once('solicitacao_funcao_update.php');
require_once('emails.php');
require_once('funcao_dia_util.php');

//region - parametros
mysql_select_db($database_conexao, $conexao);
$query_parametros = "SELECT * FROM parametros";
$parametros = mysql_query($query_parametros, $conexao) or die(mysql_error());
$row_parametros = mysql_fetch_assoc($parametros);
$totalRows_parametros = mysql_num_rows($parametros);
//endregion - fim - parametros

//region - solicitacao
mysql_select_db($database_conexao, $conexao);
$query_solicitacao = sprintf("
SELECT 
    solicitacao.*, 
    (SELECT solicitacao_tipo_solicitacao.solicitacao_auto_email_dias FROM solicitacao_tipo_solicitacao WHERE solicitacao_tipo_solicitacao.titulo = solicitacao.tipo) AS solicitacao_auto_email_dias 
FROM 
    solicitacao 
WHERE 
    situacao <> 'solucionada' and 
    situacao <> 'reprovada' 
ORDER BY 
    previsao_geral ASC
");
$solicitacao = mysql_query($query_solicitacao, $conexao) or die(mysql_error());
$row_solicitacao = mysql_fetch_assoc($solicitacao);
$totalRows_solicitacao = mysql_num_rows($solicitacao);
//endregion - fim - solicitacao
?>
<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title></title>
<style>
    .cor_black {
        padding: 1px;
    }

    .cor_orange {
        color: #FF9900;
            !important;
        font-weight: bold;
        padding: 1px;
            !important;
    }

    .cor_red {
        color: #FF0000;
            !important;
        font-weight: bold;
        padding: 1px;
            !important;
    }
</style>
</head>

<body style="font-family:Verdana, Geneva, sans-serif; font-size: 12px; margin: 0px;">

    <div style="border: 1px solid #000; padding: 5px; margin: 5px">
        Solicitações: <? echo $totalRows_solicitacao; ?>
    </div>

    <?php do { ?>

        <? $envia_email = 0; ?>

        <div style="border: 1px solid #CCC; margin: 5px; padding: 5px;">

            <!-- dados -->

            <?
            $email_para = "";
            
            // $email_para
            switch ($row_solicitacao['status']) {
                case "pendente solicitante":
                    $email_para = $row_solicitacao['usuario_responsavel'];
                    break;
                case "pendente operador":
                    $email_para = $row_solicitacao['nome_operador'];
                    break;
                case "pendente executante":
                    if ($row_solicitacao['situacao'] == 'em orçamento') {
                        $email_para = $row_solicitacao['nome_analista_orcamento'];
                    } else {
                        $email_para = $row_solicitacao['nome_executante'];
                    }
                    break;
                case "pendente testador":
                    $email_para = $row_solicitacao['nome_testador'];
                    break;

                case "encaminhada para solicitante":
                    $email_para = $row_solicitacao['usuario_responsavel'];
                    break;
                case "encaminhada para operador":
                    $email_para = $row_solicitacao['nome_operador'];
                    break;
                case "encaminhada para executante":
                    $email_para = $row_solicitacao['nome_executante'];
                    break;
                case "encaminhada para testador":
                    $email_para = $row_solicitacao['nome_testador'];
                    break;
                case "encaminhada para analista":
                    $email_para = $row_solicitacao['nome_analista_orcamento'];
                    break;

                case "devolvida para solicitante":
                    $email_para = $row_solicitacao['usuario_responsavel'];
                    break;
                case "devolvida para operador":
                    $email_para = $row_solicitacao['nome_operador'];
                    break;
                case "devolvida para executante":
                    $email_para = $row_solicitacao['nome_executante'];
                    break;
                case "devolvida para testador":
                    $email_para = $row_solicitacao['nome_testador'];
                    break;
            }
            // fim - $email_para

            // previsao_geral ou previsao_proposta
            if ($row_solicitacao['previsao_proposta'] != "") {
                $previsao_geral_proposta = $row_solicitacao['previsao_proposta'];
            } else {
                $previsao_geral_proposta = $row_solicitacao['previsao_geral'];
            }
            // fim - previsao_geral ou previsao_proposta

            $data_atual_segundos = strtotime("now");
            $previsao_geral_segundos = strtotime($previsao_geral_proposta);

            $previsao_limite = proximoDiaUtil(date('Y-m-d H:i:s', $previsao_geral_segundos)); 
            $previsao_limite_segundos = strtotime($previsao_limite);

            $previsao_geral_passados_dias = (($data_atual_segundos - $previsao_limite_segundos)) / 86400;
            $previsao_geral_passados_dias_arredondado = floor($previsao_geral_passados_dias);
            
            $previsao_geral_passados_segundos = (($data_atual_segundos - $previsao_limite_segundos));
            ?>

            Número da solicitação: <strong><?php echo $id = $row_solicitacao['id']; ?></strong>
            <br>
            Data da solicitação: <strong><? echo date('d-m-Y  H:i', strtotime($row_solicitacao['dt_solicitacao'])); ?></strong>
            <br>
            Situação: <strong><? echo $row_solicitacao['situacao']; ?></strong>
            <br>
            Tipo: <strong><? echo $row_solicitacao['tipo']; ?></strong>
            <br>
            Prazo p/ atraso: <strong><? echo $row_solicitacao['solicitacao_auto_email_dias']; ?> dias</strong>
            <br>
            Previsão geral/proposta: <strong><? echo date('d-m-Y  H:i:s', $previsao_geral_segundos); ?></strong>
            <br>
            Previsão limite: <strong><? echo date('d-m-Y  H:i:s', $previsao_limite_segundos); ?></strong>
            <br>
            Dias passados: <strong> <? echo $previsao_geral_passados_dias; ?> / <? echo $previsao_geral_passados_dias_arredondado; ?> (Segundos: <? echo $previsao_geral_passados_segundos; ?>)</strong>
            <br>
            
            <!-- fim - dados -->

            <!-- fora do prazo -->
            <? $solicitacao_auto_email_segundos = $row_solicitacao['solicitacao_auto_email_dias'] * 86400; ?>
            <? if ($previsao_geral_passados_segundos >= $solicitacao_auto_email_segundos) { ?>

                <? $descricao_nova = "<div style='color: #FF9900; font-weight: bold;'>Esta solicitação completou mais de ".$previsao_geral_passados_dias_arredondado." dias sem nenhuma ação. Por favor verifique.</div>"; ?>

                <?
                $dados_solicitacao = array(
                    "situacao" => $row_solicitacao['situacao'],
					"auto_email_status" => 1,
					"auto_email_data" => date("Y-m-d H:i:s")
                );

                $dados_solicitacao_descricao = array(
                    "id_solicitacao" => $id,
                    "id_usuario_responsavel" => "",
                    "descricao" => $descricao_nova, 
                    "data" => date("Y-m-d H:i:s"),
                    "tipo_postagem" => "Aviso de solicitação pendente",
                    "solicitacao_auto_email_dias" => $previsao_geral_passados_dias_arredondado
                );
                ?>

                <? if($row_solicitacao['auto_email_status'] <> 1){ ?>

                    <div style="color: purple;">
                        Envia um e-mail para: <? echo $email_para; ?>.
                        <br>
                        <? echo $descricao_nova; ?>
                    </div>

                    <? $funcao_solicitacao_update_retorno = funcao_solicitacao_update($id, $dados_solicitacao, $dados_solicitacao_descricao); ?>
                    
                    <? 
                    mysql_select_db($database_conexao, $conexao);
                    $updateSQL_solicitacao_update = sprintf("
                    UPDATE 
                        solicitacao 
                    SET 
                        auto_email_solicitacao_descricao=%s 
                    WHERE 
                        id=%s
                    ",
                    GetSQLValueString($funcao_solicitacao_update_retorno['solicitacao_descricoes']['id'], "text"),

                    GetSQLValueString($row_solicitacao['id'], "int"));
                    mysql_select_db($database_conexao, $conexao);
                    $Result_solicitacao_update= mysql_query($updateSQL_solicitacao_update, $conexao) or die(mysql_error());
                    ?>

                    <? // email_solicitacao_sem_movimento($id, $email_para, $dados_solicitacao_descricao['tipo_postagem'], $dados_solicitacao_descricao['descricao']); ?>

                <? } else { ?>

                    <? 
                    mysql_select_db($database_conexao, $conexao);
                    $updateSQL_solicitacao_descricoes_update = sprintf("
                    UPDATE 
                        solicitacao_descricoes 
                    SET 
                        data=%s, 
                        data_edicao=%s, 
                        descricao=%s, 
                        solicitacao_auto_email_dias=%s 

                    WHERE 
                        id_solicitacao=%s and 
                        id=%s
                    ",
                    GetSQLValueString(date('Y-m-d H:i:s'), "date"),
                    GetSQLValueString(date('Y-m-d H:i:s'), "date"),
                    GetSQLValueString($descricao_nova, "text"),
                    GetSQLValueString($previsao_geral_passados_dias_arredondado, "text"),

                    GetSQLValueString($row_solicitacao['id'], "int"),
                    GetSQLValueString($row_solicitacao['auto_email_solicitacao_descricao'], "int"));
                    mysql_select_db($database_conexao, $conexao);
                    $Result_solicitacao_descricoes_update= mysql_query($updateSQL_solicitacao_descricoes_update, $conexao) or die(mysql_error());
                    ?>

                     <div style="color: #09F;">
                        E-mail já enviado para: <? echo $email_para; ?>.
                        <br>
                        <? echo $descricao_nova; ?>
                        <br>
                        Mensagem já enviada.
                    </div>

                <? } ?>

            <? // ?>
            <!-- fim - fora do prazo -->

            <!-- dentro do prazo -->
            <? } else { ?>

                <?
                // atualiza solicitação
                $dados_solicitacao = array(
                    "situacao" => $row_solicitacao['situacao'],
					"auto_email_status" => "",
                    "auto_email_data" => "", 
                    "auto_email_solicitacao_descricao" => ""
                );

                $dados_solicitacao_descricao = NULL;
                // fim - atualiza solicitação
                ?>

                <div style="color: green;">
                    Esta solicitação está dentro do prazo.
                </div>

                <? funcao_solicitacao_update($id, $dados_solicitacao, $dados_solicitacao_descricao); ?>

            <? } ?>
            <!-- fim - dentro do prazo -->

        </div>

    <?php } while ($row_solicitacao = mysql_fetch_assoc($solicitacao)); ?>

    <?
    //region - insert - auto *****************************************************************
    $insertSQL_auto = sprintf(
        "
        INSERT INTO auto (titulo, data, ip) 
        VALUES (%s, %s, %s)",
        GetSQLValueString(basename($_SERVER['PHP_SELF']), "text"),
        GetSQLValueString(date('Y-m-d H:i:s'), "date"),
        GetSQLValueString(basename($_SERVER["REMOTE_ADDR"]), "text")
    );
    mysql_select_db($database_conexao, $conexao);
    $Result_auto = mysql_query($insertSQL_auto, $conexao) or die(mysql_error());
    //endregion - fim - insert - auto ***********************************************************
    ?>

</body>

</html>
<?php
mysql_free_result($solicitacao);
mysql_free_result($parametros);
?>