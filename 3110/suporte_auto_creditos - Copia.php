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

require_once('parametros.php');

// parametros
mysql_select_db($database_conexao, $conexao);
$query_parametros = "SELECT * FROM parametros";
$parametros = mysql_query($query_parametros, $conexao) or die(mysql_error());
$row_parametros = mysql_fetch_assoc($parametros);
$totalRows_parametros = mysql_num_rows($parametros);
// fim - parametros

// manutencao dados
mysql_select_db($database_conexao, $conexao);
$query_manutencao_dados = "
SELECT 
da37.codigo17, da37.optacuv17, da37.cliente17, da37.tpocont17, da37.visita17, da37.status17, da37.datcont17, STR_TO_DATE(datatv17,'%Y%m%d') as datatv17, 
da01.codigo1, da01.nome1, da01.fantasia1, status1, flag1, 
geral_tipo_contrato.descricao as tpocont17_descricao,
geral_tipo_visita.descricao as visita17_descricao 

FROM da37 
INNER JOIN da01 ON da37.cliente17 = da01.codigo1 
INNER JOIN geral_tipo_contrato ON da37.tpocont17 = geral_tipo_contrato.IdTipoContrato
INNER JOIN geral_tipo_visita ON da37.visita17 = geral_tipo_visita.IdTipoVisita

WHERE da37.status17 <> 'C' and da37.status17 <> 'B' and da37.status17 <> 'S' and da01.sr_deleted <> 'T' and da37.sr_deleted <> 'T' 

ORDER BY da37.codigo17";
$manutencao_dados = mysql_query($query_manutencao_dados, $conexao) or die(mysql_error());
$row_manutencao_dados = mysql_fetch_assoc($manutencao_dados);
$totalRows_manutencao_dados = mysql_num_rows($manutencao_dados);
// fim - manutencao dados

$geral_credito_acumulo_qtde = $geral_credito_acumulo_qtde - 1; // 'menos um' pois pega também o mês corrente/atual
$geral_credito_acumulo_data = date('Y-m', strtotime('-' . $geral_credito_acumulo_qtde . 'month'));

?>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title></title>
</head>

<body style="font-family:Verdana, Geneva, sans-serif; font-size: 12px; margin: 0px;">

    <div style="border: 1px solid #000; margin: 5px; padding: 5px;">
        Total de contratos: <strong><? echo $totalRows_manutencao_dados; ?></strong>
        <br>
        Data atual: <strong><? echo date('d-m-Y H:i:s'); ?></strong>
        <br>
        Desabilita créditos anteriores a: <strong><? echo date('m/Y', strtotime($geral_credito_acumulo_data)); ?></strong>
    </div>

    <? if ($totalRows_manutencao_dados > 0) { ?>

        <?
        //region - update (atualiza créditos com mais de '6' meses para 'expirado')
        $updateSQL = sprintf("
        UPDATE 
            geral_credito 
        SET 
            status = '0' 
        WHERE 
            data_criacao < %s and 
            status = 1 and 
            data_utilizacao IS NULL
        ",
        GetSQLValueString($geral_credito_acumulo_data . '-01 00:00:00', "date"));

        mysql_select_db($database_conexao, $conexao);
        $Result = mysql_query($updateSQL, $conexao) or die(mysql_error());
        //endregion - fim - update (atualiza créditos com mais de '6' meses para 'expirado')

        //region - update (atualiza créditos com adiantamento do mês anterior)
        $updateSQL_adiantamento = sprintf("
        UPDATE 
            geral_credito 
        SET 
            adiantamento = 'n' 
        WHERE 
            data_criacao = %s and 
            adiantamento = 's'
        ",
        GetSQLValueString(date('Y-m') . '-01 00:00:00', "date"));

        mysql_select_db($database_conexao, $conexao);
        $Result_adiantamento = mysql_query($updateSQL_adiantamento, $conexao) or die(mysql_error());
        //endregion - fim - update (atualiza créditos com adiantamento do mês anterior)

        //region - delete (deleta geral_contrato_alterado a mais de 2 meses)
        $deleteSQL_geral_contrato_alterado = "
        DELETE FROM 
            geral_contrato_alterado 
        WHERE 
            data_alteracao < (ADDDATE(LAST_DAY(SUBDATE(CURDATE(), INTERVAL 2 MONTH)), 1))";
        mysql_select_db($database_conexao, $conexao);
        $Result_geral_contrato_alterado = mysql_query($deleteSQL_geral_contrato_alterado, $conexao) or die(mysql_error());
        //endregion - fim - delete (deleta geral_contrato_alterado a mais de 2 meses)
        ?>

        <?php do { ?>
            <div style="border: 1px solid #CCC; margin: 5px; padding: 5px;">

                <?
                $contrato_tipo_visita = $row_manutencao_dados['visita17'];

                $contrato_alterado = 0;
                $contrato_alterado_data_alteracao = NULL;
                $contrato_alterado_status = NULL;

                // 1: Nenhum
                // 2: Sem Limite
                // 3: Mensal
                // 4: Trimestral
                // 5: Sem visita
                ?>

                <!-- dados -->
                    Cliente: <?php echo $id = $row_manutencao_dados['codigo1']; ?> | <?php echo $id = $row_manutencao_dados['nome1']; ?>
                    <?php if (
                        $row_manutencao_dados['fantasia1'] != "                                                  " and
                        $row_manutencao_dados['fantasia1'] != ""
                    ) { ?>
                        (<?php echo $row_manutencao_dados['fantasia1']; ?>)
                    <? } ?>
                    <br>
                    Contrato: <strong><?php echo $row_manutencao_dados['codigo17']; ?></strong> |
                    Criação: <strong><? echo date('d-m-Y', strtotime($row_manutencao_dados['datcont17'])); ?></strong>
                    <? if ($row_manutencao_dados['datatv17'] > 0) { // se houve alteração de contrato 
                    ?>
                        | Alteração: <strong><? echo date('d-m-Y', strtotime($row_manutencao_dados['datatv17'])); ?></strong>
                    <? } // fim - se houve alteração de contrato 
                    ?>
                    <br>
                    Tipo do contrato: <strong><?php echo $row_manutencao_dados['tpocont17_descricao']; ?></strong>
                    <br>
                    Tipo de visita: <strong><?php echo $row_manutencao_dados['visita17_descricao']; ?></strong>
                    <br>
                    Optante por acumulo de manutenção: <strong><?php if ($row_manutencao_dados['optacuv17'] == "N") {
                                                                    echo "Não";
                                                                }
                                                                if ($row_manutencao_dados['optacuv17'] == "S") {
                                                                    echo "Sim";
                                                                } ?></strong>
                    <br>
                    Status da manutenção:
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
                        if ($row_manutencao_dados['status1'] == "0") {
                            echo "Desbloqueado";
                        } // manual
                        if ($row_manutencao_dados['status1'] == "1") {
                            echo "<font color='red'>Bloqueado</font>";
                        } // manual
                        ?> |
                    </strong>
                    Status automático:
                    <strong>
                        <?php
                        if ($row_manutencao_dados['flag1'] == "0") {
                            echo "Desbloqueado";
                        } // autom
                        if ($row_manutencao_dados['flag1'] == "1") {
                            echo "<font color='red'>Bloqueado</font>";
                        } // autom
                        ?>
                    </strong>
                    <br>
                <!-- fim - dados -->

                <!-- 'Alteração de contrato' nos últimos 2 meses -->
                <? if ($row_manutencao_dados['datatv17'] > 0 and $row_manutencao_dados['datatv17'] >= date("Y-m-01", strtotime("-1 month")) and ($contrato_tipo_visita == "3" or $contrato_tipo_visita == "4")) { ?>

                    <!-- geral_contrato_alterado (pesquisa se já existe a alteração na tabela 'geral_alteracao_contrato') -->
                    <?
                    mysql_select_db($database_conexao, $conexao);
                    $query_geral_contrato_alterado = sprintf(
                        "
                        SELECT 
                            count(IdContratoAlterado) as retorno  
                        FROM 
                           geral_contrato_alterado 
                        WHERE 
                            contrato = %s and 
                            data_alteracao = %s 
                        ORDER BY 
                            IdContratoAlterado DESC",
                        GetSQLValueString($row_manutencao_dados['codigo17'], "text"),
                        GetSQLValueString($row_manutencao_dados['datatv17'], "date")
                    );
                    $geral_contrato_alterado = mysql_query($query_geral_contrato_alterado, $conexao) or die(mysql_error());
                    $row_geral_contrato_alterado = mysql_fetch_assoc($geral_contrato_alterado);
                    $totalRows_geral_contrato_alterado = mysql_num_rows($geral_contrato_alterado);
                    $row_geral_contrato_alterado['retorno'];
                    ?>
                    <? if ($row_geral_contrato_alterado['retorno'] == 0) { ?>
                        <?
                        // delete (deleta geral_contrato_alterado repetido)
                        $deleteSQL_geral_contrato_alterado_repetido = sprintf(
                            "
									 DELETE FROM geral_contrato_alterado 
									 where contrato = %s",
                            GetSQLValueString($row_manutencao_dados['codigo17'], "text")
                        );
                        mysql_select_db($database_conexao, $conexao);
                        $Result_geral_contrato_alterado_repetido = mysql_query($deleteSQL_geral_contrato_alterado_repetido, $conexao) or die(mysql_error());
                        // fim - delete (deleta geral_contrato_alterado_repetido repetido)

                        // insert - geral_contrato_alterado (insere na tabela 'geral_alteracao_contrato')
                        $insertSQL_geral_contrato_alterado = sprintf("
                        INSERT INTO 
                            geral_contrato_alterado 
                        (contrato, data_alteracao, tipo_visita, data_criacao) 
                        VALUES 
                            (%s, %s, %s, %s)",
                        GetSQLValueString($row_manutencao_dados['codigo17'], "text"),
                        GetSQLValueString($row_manutencao_dados['datatv17'], "date"),
                        GetSQLValueString($row_manutencao_dados['visita17'], "text"),
                        GetSQLValueString(date('Y-m-d H:i:s'), "date"));

                        mysql_select_db($database_conexao, $conexao);
                        $Result_geral_contrato_alterado = mysql_query($insertSQL_geral_contrato_alterado, $conexao) or die(mysql_error());
                        // fim - insert - geral_contrato_alterado (insere na tabela 'geral_alteracao_contrato' caso ainda não exista o contrato com a alteração)
                        ?>
                    <? } ?>
                    <? mysql_free_result($geral_contrato_alterado); ?>
                    <!-- fim - geral_contrato_alterado (pesquisa se já existe a alteração na tabela 'geral_alteracao_contrato') -->

                    <br>
                    <span style="color: #FFCC00;">Alteração de contrato: </span><? echo date('d-m-Y', strtotime($row_manutencao_dados['datatv17'])); ?>
                    <br>

                <? } ?>
                <!-- fim - 'Alteração de contrato' nos últimos 2 meses -->

                <!-- consulta_contrato_alterado -->
                <?
                mysql_select_db($database_conexao, $conexao);
                $query_consulta_contrato_alterado = sprintf("
                SELECT 
                    *  
                FROM 
                    geral_contrato_alterado 
                WHERE 
                    contrato = %s and 
                    data_alteracao = %s 
                ORDER BY 
                    IdContratoAlterado DESC
                ",
                GetSQLValueString($row_manutencao_dados['codigo17'], "text"),
                GetSQLValueString($row_manutencao_dados['datatv17'], "date"));
                $consulta_contrato_alterado = mysql_query($query_consulta_contrato_alterado, $conexao) or die(mysql_error());
                $row_consulta_contrato_alterado = mysql_fetch_assoc($consulta_contrato_alterado);
                $totalRows_consulta_contrato_alterado = mysql_num_rows($consulta_contrato_alterado);
                ?>
                <!-- fim - consulta_contrato_alterado -->

                <?
                // 1 ou 5 ---------------------------------------------------------------------------------------------------------------
                if ($contrato_tipo_visita == "1" or $contrato_tipo_visita == "5") { // Nenhum / Sem visita
                ?>

                    <?
                    // delete (Alteração de contrato)
                    $deleteSQL_geral_contrato_alterado = sprintf(
                    "
                    DELETE FROM 
                        geral_contrato_alterado  
                    WHERE 
                        contrato = %s",
                    GetSQLValueString($row_manutencao_dados['codigo17'], "text"));
                    mysql_select_db($database_conexao, $conexao);
                    $Result_geral_contrato_alterado = mysql_query($deleteSQL_geral_contrato_alterado, $conexao) or die(mysql_error());
                    // fim - delete (Alteração de contrato)
                    ?>

                <?
                }
                // fim - 1 ou 5 ---------------------------------------------------------------------------------------------------------
                ?>

                <?
                // 2 ---------------------------------------------------------------------------------------------------------------
                if ($contrato_tipo_visita == "2") { // Sem Limite
                ?>

                    <?
                    // delete (Créditos do contrato)
                    $deleteSQL_geral_credito = sprintf("
                    DELETE FROM geral_credito 
                    where contrato = %s",
                        GetSQLValueString($row_manutencao_dados['codigo17'], "text")
                    );
                    mysql_select_db($database_conexao, $conexao);
                    $Result_geral_credito = mysql_query($deleteSQL_geral_credito, $conexao) or die(mysql_error());
                    // fim - delete (Créditos do contrato)
                    ?>

                    <?
                    // delete (Alteração de contrato)
                    $deleteSQL_geral_contrato_alterado = sprintf("
                    DELETE FROM geral_contrato_alterado  
                    where contrato = %s",
                        GetSQLValueString($row_manutencao_dados['codigo17'], "text")
                    );
                    mysql_select_db($database_conexao, $conexao);
                    $Result_geral_contrato_alterado = mysql_query($deleteSQL_geral_contrato_alterado, $conexao) or die(mysql_error());
                    // fim - delete (Alteração de contrato)
                    ?>

                <?
                }
                // fim - 2 ---------------------------------------------------------------------------------------------------------
                ?>

                <?
                // 3M-4T -------------------------------------------------------------------------------------------------------------
                if ($contrato_tipo_visita == "3" or $contrato_tipo_visita == "4") { // Mensal / Trimestral
                ?>

                    <?
                    $data_criacao = '0000-00-00 00:00:00';
                    $data_proximo = '0000-00-00 00:00:00';
                    ?>

                    <? if ($row_consulta_contrato_alterado['status'] == 1) { ?>

                        <? if (strtotime($row_consulta_contrato_alterado['data_alteracao']) >= (strtotime(date('Y-m-16 00:00:00', strtotime($row_manutencao_dados['datatv17']))))) { // 16 a 31 
                        ?>

                            <?
                            $data_criacao = '0000-00-00 00:00:00';
                            $data_proximo = date('Y-m-01 00:00:00', strtotime(date('Y-m-01', strtotime($row_consulta_contrato_alterado['data_alteracao'])) . ' + 1 month'));
                            ?>

                        <? } else { // 1 a 15 
                        ?>

                            <?
                            $data_criacao = '0000-00-00 00:00:00';
                            $data_proximo = date($row_consulta_contrato_alterado['data_alteracao']);
                            ?>

                        <? } ?>

                    <? } else { ?>

                        <!-- consulta de créditos -->
                        <?
                        // geral_credito_ultimo (busca o ultimo 'credito' gerado 'ativo') - MENSAL e TRIMESTRAL
                        mysql_select_db($database_conexao, $conexao);
                        $query_geral_credito_ultimo = sprintf("
                        SELECT 
                            IdCredito, data_criacao 
                        FROM 
                            geral_credito 
                        WHERE 
                            contrato = %s and 
                            status = 1
                        ORDER BY 
                            data_criacao DESC 
                        LIMIT 1
                        ",
                        GetSQLValueString($row_manutencao_dados['codigo17'], "text")
                        );

                        $geral_credito_ultimo = mysql_query($query_geral_credito_ultimo, $conexao) or die(mysql_error());
                        $row_geral_credito_ultimo = mysql_fetch_assoc($geral_credito_ultimo);
                        $totalRows_geral_credito_ultimo = mysql_num_rows($geral_credito_ultimo);
                        // fim - geral_credito_ultimo (busca o ultimo 'credito' gerado 'ativo') - MENSAL e TRIMESTRAL

                        // se existe credito anterior
                        if ($totalRows_geral_credito_ultimo > 0 and $row_geral_credito_ultimo['data_criacao'] != "") {

                            // Optante por acumulo de manutenção: Não 
                            if ($row_manutencao_dados['optacuv17'] == "N") {

                                // delete (deleta credito ativo diferente do último gerado)
                                $deleteSQL_credito_nao_acumulativo = sprintf(
                                    "
                                    DELETE FROM 
                                        geral_credito
                                    WHERE 
                                        contrato = %s and 
                                        status = 1 and 
                                        IdCredito <> %s and 
                                        data_criacao < %s
                                    ",
                                    GetSQLValueString($row_manutencao_dados['codigo17'], "text"),
                                    GetSQLValueString($row_geral_credito_ultimo['IdCredito'], "int"),
                                    GetSQLValueString($row_geral_credito_ultimo['data_criacao'], "date")
                                );

                                mysql_select_db($database_conexao, $conexao);
                                $Result_credito_nao_acumulativo = mysql_query($deleteSQL_credito_nao_acumulativo, $conexao) or die(mysql_error());
                                // fim - delete (deleta credito ativo diferente do último gerado)

                            }
                            // fim - Optante por acumulo de manutenção: Não 

                            $data_criacao = date('Y-m-01 00:00:00', strtotime($row_geral_credito_ultimo['data_criacao'])); // desta forma para corrigir o problema do dia 31 jogar para daqui 2 meses

                            // $data_proximo
                            if ($contrato_tipo_visita == "4") {
                                $data_proximo = date('Y-m-d H:i:s', strtotime($data_criacao . ' + 3 month')); // trimestral
                            } else {
                                $data_proximo = date('Y-m-d H:i:s', strtotime($data_criacao . ' + 1 month')); // mensal
                            }
                            // fim - $data_proximo

                        }
                        // fim - se existe credito anterior

                        // se existe NÃO credito anterior
                        else {

                            $data_criacao = '0000-00-00 00:00:00';
                            $data_proximo = date('Y-m-01 00:00:00');
                        }
                        //se existe NÃO credito anterior

                        mysql_free_result($geral_credito_ultimo);
                        ?>
                        <!-- fim - consulta de créditos -->

                    <? } ?>

                    <br>
                    <span style="color: #090;">Último crédito:&nbsp;&nbsp;&nbsp; <strong>
                            <? if ($data_criacao == '0000-00-00 00:00:00') {
                                echo "Não existe";
                            } else {
                                echo date('m/Y', strtotime($data_criacao));
                            } ?>
                        </strong></span>
                    <br>
                    <span style="color: #F60;">Próximo crédito: <strong><? echo date('m/Y', strtotime($data_proximo)); ?></strong></span>
                    <br><br>

                    <?
                    // se é o MES e o ANO para criar o crédito (compara com o mês/ano de hoje)
                    if (date('m', strtotime($data_proximo)) == date('m') and date('Y', strtotime($data_proximo)) == date('Y')) {
                    ?>

                        <span style="color: #00F">Crédito Inserido em -&gt; <? echo date('m/Y'); ?></span>
                        <?
                        // insert - geral_credito
                        $insertSQL = sprintf(
                            "INSERT INTO geral_credito (contrato, tipo_visita, data_criacao, status) VALUES (%s, %s, %s, %s)",
                            GetSQLValueString($row_manutencao_dados['codigo17'], "text"),
                            GetSQLValueString($row_manutencao_dados['visita17'], "text"),
                            GetSQLValueString(date('Y-m-d H:i:s'), "date"),
                            GetSQLValueString('1', "int")
                        );

                        mysql_select_db($database_conexao, $conexao);
                        $Result1 = mysql_query($insertSQL, $conexao) or die(mysql_error());
                        // fim - insert - geral_credito

                        if ($row_consulta_contrato_alterado['status'] == 1) {
                            // update - 'geral_contrato_alterado'
                            $updateSQL_geral_contrato_alterado = sprintf(
                            "
                            UPDATE 
                                geral_contrato_alterado 
                            SET 
                                status = '0' 
                            WHERE 
                                contrato = %s",
                            GetSQLValueString($row_manutencao_dados['codigo17'], "text"));
                            mysql_select_db($database_conexao, $conexao);
                            $Result_geral_contrato_alterado = mysql_query($updateSQL_geral_contrato_alterado, $conexao) or die(mysql_error());
                            // fim - update - 'geral_contrato_alterado'
                        }
                        ?>

                    <? }
                    // fim - se é o MES e o ANO para criar o crédito (compara com o mês/ano de hoje)

                    // se NÃO é o MES e o ANO para criar o crédito
                    else { ?>

                        <span style="color: #F00">Não insere crédito</span>

                    <? }
                    // fim - se NÃO é o MES e o ANO para criar o crédito 
                    ?>

                <?
                }
                // fim - 3M-4T -------------------------------------------------------------------------------------------------------
                ?>

                <? mysql_free_result($consulta_contrato_alterado); ?>

            </div>
        <?php } while ($row_manutencao_dados = mysql_fetch_assoc($manutencao_dados)); ?>

    <? } ?>

    <?
    //region - insert - auto *****************************************************************
    $insertSQL_auto = sprintf( "
    INSERT INTO auto 
        (titulo, data, ip) 
    VALUES 
        (%s, %s, %s)
    ",
    GetSQLValueString(basename($_SERVER['PHP_SELF']), "text"),
    GetSQLValueString(date('Y-m-d H:i:s'), "date"),
    GetSQLValueString(basename($_SERVER["REMOTE_ADDR"]), "text"));
    mysql_select_db($database_conexao, $conexao);
    $Result_auto = mysql_query($insertSQL_auto, $conexao) or die(mysql_error());
    //enregion - fim - insert - auto ***********************************************************
    ?>
</body>

</html>
<?php
mysql_free_result($manutencao_dados);
mysql_free_result($parametros);
?>