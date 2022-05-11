<?php require('restrito.php'); ?>
<?php require_once('Connections/conexao.php'); ?>
<?php
if (!function_exists("GetSQLValueString")) {
    function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "")
    {
        $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;

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

// suporte
$colname_suporte = "-1";
if (isset($_GET['id_suporte'])) {
    $colname_suporte = $_GET['id_suporte'];
}
mysql_select_db($database_conexao, $conexao);
$query_suporte = sprintf(
"
SELECT 
    suporte.*,  
    (SELECT nome FROM usuarios WHERE usuarios.IdUsuario = suporte.id_usuario_responsavel) as usuario_responsavel, 
    (SELECT nome FROM usuarios WHERE usuarios.IdUsuario = suporte.id_usuario_envolvido) as usuario_envolvido, 
    geral_tipo_praca.suporte_inloco_sim_prazo_anexo AS geral_tipo_praca_suporte_inloco_sim_prazo_anexo 
FROM 
    suporte 
LEFT JOIN 
    geral_tipo_praca ON geral_tipo_praca.praca = suporte.praca 
WHERE
    suporte.id = %s
",
GetSQLValueString($colname_suporte, "int"));
$suporte = mysql_query($query_suporte, $conexao) or die(mysql_error());
$row_suporte = mysql_fetch_assoc($suporte);
$totalRows_suporte = mysql_num_rows($suporte);
// fim - suporte

// arquivos anexados
$colname_arquivos_anexados = "-1";
if (isset($_GET['id_suporte'])) {
    $colname_arquivos_anexados = $_GET['id_suporte'];
}
mysql_select_db($database_conexao, $conexao);
$query_arquivos_anexados = sprintf("
SELECT 
    suporte_arquivos.*, 
    suporte_descricoes.data 
FROM 
    suporte_arquivos 
LEFT JOIN 
    suporte_descricoes ON suporte_descricoes.id_arquivo = suporte_arquivos.id_arquivo 
WHERE 
    suporte_arquivos.id_suporte = %s 
ORDER BY 
    suporte_arquivos.id_arquivo DESC
", 
GetSQLValueString($colname_arquivos_anexados, "int"));
$arquivos_anexados = mysql_query($query_arquivos_anexados, $conexao) or die(mysql_error());
$row_arquivos_anexados = mysql_fetch_assoc($arquivos_anexados);
$totalRows_arquivos_anexados = mysql_num_rows($arquivos_anexados);
// fim - arquivos anexados

$prazo_anexo_segundos = $row_suporte['geral_tipo_praca_suporte_inloco_sim_prazo_anexo'] * 86400;
$prazo_anexo_limite_segundos = strtotime($row_suporte['data_inicio']) + $prazo_anexo_segundos;
$data_atual_segundos = strtotime(date("Y-m-d 00:00:00"));

// insere arquivo e descricao ---------------------------------------------------------
if (
    (
        $row_usuario['controle_suporte'] == "Y" or
        $row_usuario['suporte_operador_parceiro'] == "Y" or
        $row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or
        $row_suporte['id_usuario_envolvido'] == $row_usuario['IdUsuario'] or
        $row_suporte['praca'] == $row_usuario['praca']
    ) and (
        (
            $row_suporte['inloco'] == "s" and 
            $row_suporte['situacao'] != "cancelada" and 
            (
                ($data_atual_segundos <= $prazo_anexo_limite_segundos) or 
                ($data_atual_segundos > $prazo_anexo_limite_segundos and $row_suporte['prazo_anexo_liberar'] == "s")
            )
        ) or
        ($row_suporte['inloco'] == "n" and $row_suporte['situacao'] != "cancelada" and $row_suporte['situacao'] != "solucionada")
    )
) {

    // Load the common classes
    require_once('includes/common/KT_common.php');

    // Load the tNG classes
    require_once('includes/tng/tNG.inc.php');

    // Make a transaction dispatcher instance
    $tNGs = new tNG_dispatcher("");

    // Make unified connection variable
    $conn_conexao = new KT_connection($conexao, $database_conexao);

    // Start trigger
    $masterValidation = new tNG_FormValidation();
    $masterValidation->addField("nome_arquivo", true, "", "", "", "", "Selecione um arquivo antes de prosseguir.");
    $tNGs->prepareValidation($masterValidation);
    // End trigger

    // Start trigger
    $detailValidation = new tNG_FormValidation();
    $tNGs->prepareValidation($detailValidation);
    // End trigger

    //start Trigger_LinkTransactions trigger
    //remove this line if you want to edit the code by hand 
    function Trigger_LinkTransactions(&$tNG)
    {
        global $ins_suporte_descricoes;
        $site_linkObj = new tNG_LinkedTrans($tNG, $ins_suporte_descricoes);
        $site_linkObj->setLink("id_arquivo");
        return $site_linkObj->Execute();
    }
    //end Trigger_LinkTransactions trigger

    //start Trigger_FileUpload trigger
    //remove this line if you want to edit the code by hand 
    function Trigger_FileUpload(&$tNG)
    {
        $uploadObj = new tNG_FileUpload($tNG);
        $uploadObj->setFormFieldName("nome_arquivo");
        $uploadObj->setDbFieldName("nome_arquivo");
        $uploadObj->setFolder("arquivos/");
        $uploadObj->setMaxSize(5120);
        $uploadObj->setAllowedExtensions("rar, zip, txt, jpeg, jpg, pdf");
        $uploadObj->setRename("auto");
        return $uploadObj->Execute();
    }
    //end Trigger_FileUpload trigger

    // Make an insert transaction instance
    $ins_suporte_arquivos = new tNG_insert($conn_conexao);
    $tNGs->addTransaction($ins_suporte_arquivos);
    // Register triggers
    $ins_suporte_arquivos->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Insert1");
    $ins_suporte_arquivos->registerTrigger("BEFORE", "Trigger_Default_FormValidation", 10, $masterValidation);
    $ins_suporte_arquivos->registerTrigger("END", "Trigger_Default_Redirect", 99, "suporte_editar_upload.php?id_suporte={suporte.id}&acao={GET.acao}&editar_tabela={GET.editar_tabela}&voltar={GET.voltar}");
    $ins_suporte_arquivos->registerTrigger("AFTER", "Trigger_LinkTransactions", 98);
    $ins_suporte_arquivos->registerTrigger("ERROR", "Trigger_LinkTransactions", 98);
    $ins_suporte_arquivos->registerTrigger("AFTER", "Trigger_FileUpload", 97);
    // Add columns
    $ins_suporte_arquivos->setTable("suporte_arquivos");
    $ins_suporte_arquivos->addColumn("nome_arquivo", "FILE_TYPE", "FILES", "nome_arquivo");
    $ins_suporte_arquivos->addColumn("id_suporte", "NUMERIC_TYPE", "POST", "id_suporte", "{suporte.id}");
    $ins_suporte_arquivos->addColumn("comprovante_pagamento", "STRING_TYPE", "POST", "comprovante_pagamento");
    $ins_suporte_arquivos->setPrimaryKey("id_arquivo", "NUMERIC_TYPE");

    // Make an insert transaction instance
    $ins_suporte_descricoes = new tNG_insert($conn_conexao);
    $tNGs->addTransaction($ins_suporte_descricoes);
    // Register triggers
    $ins_suporte_descricoes->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "");
    $ins_suporte_descricoes->registerTrigger("BEFORE", "Trigger_Default_FormValidation", 10, $detailValidation);
    // Add columns
    $ins_suporte_descricoes->setTable("suporte_descricoes");
    $ins_suporte_descricoes->addColumn("descricao", "STRING_TYPE", "POST", "descricao");
    $ins_suporte_descricoes->addColumn("data", "DATE_TYPE", "POST", "data");
    $ins_suporte_descricoes->addColumn("tipo_postagem", "STRING_TYPE", "POST", "tipo_postagem");
    $ins_suporte_descricoes->addColumn("id_usuario_responsavel", "NUMERIC_TYPE", "POST", "id_usuario_responsavel");
    $ins_suporte_descricoes->addColumn("id_suporte", "NUMERIC_TYPE", "POST", "id_suporte", "{suporte.id}");
    $ins_suporte_descricoes->addColumn("id_arquivo", "NUMERIC_TYPE", "VALUE", "");
    $ins_suporte_descricoes->setPrimaryKey("id", "NUMERIC_TYPE");

    // Execute all the registered transactions
    $tNGs->executeTransactions();

    // Get the transaction recordset
    $rssuporte_arquivos = $tNGs->getRecordset("suporte_arquivos");
    $row_rssuporte_arquivos = mysql_fetch_assoc($rssuporte_arquivos);
    $totalRows_rssuporte_arquivos = mysql_num_rows($rssuporte_arquivos);

    // Get the transaction recordset
    $rssuporte_descricoes = $tNGs->getRecordset("suporte_descricoes");
    $row_rssuporte_descricoes = mysql_fetch_assoc($rssuporte_descricoes);
    $totalRows_rssuporte_descricoes = mysql_num_rows($rssuporte_descricoes);
}
// fim - insere arquivo e descricao ---------------------------------------------------
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><? echo $_GET['acao']; ?> (<?php echo $row_suporte['id']; ?>)</title>

    <link rel="stylesheet" href="css/suporte.css" type="text/css" media="screen" />
    <style type="text/css">
        .block {
            display: block;
        }

        form.cmxform label.error {
            display: none;
        }
    </style>

    <script type="text/javascript" src="js/jquery.js"></script>

    <?
    if (
        (
            $row_usuario['controle_suporte'] == "Y" or
            $row_usuario['suporte_operador_parceiro'] == "Y" or
            $row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or
            $row_suporte['id_usuario_envolvido'] == $row_usuario['IdUsuario'] or
            $row_suporte['praca'] == $row_usuario['praca']
        ) and (
            (
                $row_suporte['inloco'] == "s" and 
                $row_suporte['situacao'] != "cancelada" and 
                (
                    ($data_atual_segundos <= $prazo_anexo_limite_segundos) or 
                    ($data_atual_segundos > $prazo_anexo_limite_segundos and $row_suporte['prazo_anexo_liberar'] == "s")
                )
            ) or
            ($row_suporte['inloco'] == "n" and $row_suporte['situacao'] != "cancelada" and $row_suporte['situacao'] != "solucionada")
        )
    ) {
    ?>
        <link href="includes/skins/mxkollection3.css" rel="stylesheet" type="text/css" media="all" />
        <script src="includes/common/js/base.js" type="text/javascript"></script>
        <script src="includes/common/js/utility.js" type="text/javascript"></script>
        <script src="includes/skins/style.js" type="text/javascript"></script>
        <script type="text/javascript">
            function confirmaSubmit() {
                var agree = confirm("Deseja realmente excluir este arquivo ?");
                if (agree)
                    return true;
                else
                    return false;
            }
        </script>
        <?php echo $tNGs->displayValidationRules(); ?>
    <? } ?>
</head>

<body style="text-align: center">

    <? if (@$_GET['editar_tabela'] != "s") { ?>
        <div class="div_solicitacao_linhas">
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td style="text-align:left">
                        Suporte número: <?php echo $row_suporte['id'];
                                        echo $_GET['voltar']; ?>
                    </td>

                    <? if (@$_GET['voltar'] == 's') { ?>
                        <td style="text-align: right">
                            &lt;&lt; <a href="suporte_editar.php?id_suporte=<?php echo $_GET['id_suporte']; ?>" target="_top">Voltar</a>
                        </td>
                    <? } ?>
                </tr>
            </table>
        </div>
    <? } ?>

    <? if (@$_GET['editar_tabela'] != "s") { ?>
        <div class="div_solicitacao_linhas2">
            <table cellspacing="0" cellpadding="0">
                <tr>
                    <td style="text-align: left">
                        <strong><? echo $_GET['acao']; ?></strong>
                    </td>
                </tr>
            </table>
        </div>
    <? } ?>

    <? if (@$_GET['editar_tabela'] != "s") { ?>
        <div class="div_solicitacao_linhas3">
            <table cellspacing="0" cellpadding="0">
                <tr>
                    <td style="text-align: left">
                        Título: <?php echo $row_suporte['titulo']; ?>
                    </td>
                </tr>
            </table>
        </div>
    <? } ?>

    <!-- formulário -->
    <?
    if (
        (
            $row_usuario['controle_suporte'] == "Y" or
            $row_usuario['suporte_operador_parceiro'] == "Y" or
            $row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or
            $row_suporte['id_usuario_envolvido'] == $row_usuario['IdUsuario'] or
            $row_suporte['praca'] == $row_usuario['praca']
        ) and (
            (
                $row_suporte['inloco'] == "s" and 
                $row_suporte['situacao'] != "cancelada" and 
                (
                    ($data_atual_segundos <= $prazo_anexo_limite_segundos) or 
                    ($data_atual_segundos > $prazo_anexo_limite_segundos and $row_suporte['prazo_anexo_liberar'] == "s")
                )
            ) or
            (
                $row_suporte['inloco'] == "n" and 
                $row_suporte['situacao'] != "cancelada" and 
                $row_suporte['situacao'] != "solucionada"
            )
        )
    ) {
    ?>
        <div class="div_solicitacao_linhas2">
            <table cellspacing="0" cellpadding="0">
                <tr>
                    <td style="text-align: left">
                        <form id="form" name="form" method="POST" action="<?php echo KT_escapeAttribute(KT_getFullUri()); ?>" enctype="multipart/form-data" class="cmxform">

                            <div style="padding-bottom: 10px;">
                                <div class="label_solicitacao2">Anexar novo arquivo:</div>
                                <div>Extensões permitidas: rar, zip, txt, jpeg, jpg, pdf</div>
                                <div>Data Limite: <strong><? echo date('d-m-Y', ($prazo_anexo_limite_segundos)); ?></strong></div>
                                <input type="file" name="nome_arquivo" id="nome_arquivo" style="width: 400px; padding: 5px;" />
                            </div>

                            <input type="hidden" id="comprovante_pagamento" name="comprovante_pagamento" value="<? echo @$_GET['editar_tabela']; ?>" />
                            <input type="hidden" name="id_suporte" value="<?php echo $row_suporte['id']; ?>" />
                            <input type="hidden" name="id_usuario_responsavel" id="id_usuario_responsavel" value="<?php echo $row_usuario['IdUsuario']; ?>" />
                            <input type="hidden" name="data" id="data" value="<?php echo date('d-m-Y H:i:s'); ?>" />
                            <input type="hidden" name="tipo_postagem" id="tipo_postagem" value="Anexo de arquivo" />
                            <input type="hidden" name="descricao" id="descricao" value="Novo arquivo anexado" />


                            <!-- Botões -->
                            <input type="submit" name="KT_Insert1" id="KT_Insert1" value="Anexar arquivo" class="botao_geral2" style="width: 150px" />
                            <!-- fim - Botões -->
                        </form>
                        <?php echo $tNGs->displayFieldError("suporte_arquivos", "nome_arquivo"); ?>
                        <?php echo $tNGs->getErrorMsg(); ?>
                    </td>
                </tr>
            </table>
        </div>
    <? } ?>
    <!-- fim - formulário -->


    <!-- arquivos já anexados -->
    <div class="div_solicitacao_linhas4">
        <? if ($totalRows_arquivos_anexados > 0) { ?>
            <table cellspacing="0" cellpadding="0">
                <tr>
                    <td style="text-align: left">
                        <strong>Arquivos já anexados:</strong>
                        <br>
                        <?php do { ?>
                            <div style=" margin-top: 10px; margin-bottom: 10px;">
                                <a href="arquivos/<?php echo $row_arquivos_anexados['nome_arquivo']; ?>" target="_blank">
                                    - <?php if ($row_arquivos_anexados['comprovante_pagamento'] == "s") { ?>
                                        <strong>[Comprovante de Pagto.]</strong>
                                    <? } ?>
                                    <?php echo $row_arquivos_anexados['nome_arquivo']; ?> 
                                    <?php if($row_arquivos_anexados['data'] <> NULL){ ?> - <? echo date('d-m-Y H:i:s', strtotime($row_arquivos_anexados['data'])); ?><? } ?>
                                </a>

                                <?
                                if (
                                    (
                                        $row_usuario['controle_suporte'] == "Y" or
                                        $row_usuario['suporte_operador_parceiro'] == "Y" or
                                        $row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or
                                        $row_suporte['id_usuario_envolvido'] == $row_usuario['IdUsuario'] or
                                        $row_suporte['praca'] == $row_usuario['praca']
                                    ) and (
                                        (
                                            $row_suporte['inloco'] == "s" and 
                                            $row_suporte['situacao'] != "cancelada" and 
                                            (
                                                ($data_atual_segundos <= $prazo_anexo_limite_segundos) or 
                                                ($data_atual_segundos > $prazo_anexo_limite_segundos and $row_suporte['prazo_anexo_liberar'] == "s")
                                            )
                                        ) or
                                        ($row_suporte['inloco'] == "n" and $row_suporte['situacao'] != "cancelada" and $row_suporte['situacao'] != "solucionada")
                                    )
                                ) {
                                ?>
                                    <a href="suporte_editar_upload_excluir.php?id_suporte=<?php echo $row_suporte['id']; ?>&id_arquivo=<?php echo $row_arquivos_anexados['id_arquivo']; ?>&acao=<? echo $_GET['acao']; ?>&editar_tabela=<? echo @$_GET['editar_tabela']; ?>" target="_self">
                                        <img src="imagens/remove.png" border="0" align="top" title="Excluir arquivo" onClick="return confirmaSubmit()">
                                    </a>
                                <? } ?>
                            </div>
                        <?php } while ($row_arquivos_anexados = mysql_fetch_assoc($arquivos_anexados)); ?>
                    </td>
                </tr>
            </table>
        <? } else { ?>
            Nenhum arquivo em anexo.
        <? } ?>
    </div>
    <!-- fim - arquivos já anexados -->

</body>

</html>
<?php
mysql_free_result($suporte);

mysql_free_result($usuario);

mysql_free_result($arquivos_anexados);
?>