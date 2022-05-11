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

require_once('parametros.php');
require_once('funcao_dia_util.php');

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

// -------------------------------------------------------------------------------------------------------------------------------------------------------------------
// solicitações ------------------------------------------------------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------------------------------------------------------------------
mysql_select_db($database_conexao, $conexao);

$join = NULL;
$where = "WHERE 1=1";

// se padrão ---------------------------------------------------------------------------------------------------------------------------------------------------------
if (isset($_GET['padrao']) && ($_GET['padrao'] == "sim")) {

    // se usuário logado -------------------------------------------------------------------------------------------------------------------------------------------------
    if (isset($_SESSION['MM_Username'])   &&   ($_SESSION['MM_Username'] != "")) {

        // operador
        if ($row_usuario['controle_solicitacao'] == "Y") {
            $where .= "";
        }
        // fim - operador

        // não é operador
        else {

            // executante e testador
            if (($row_usuario['solicitacao_executante'] == "Y") && ($row_usuario['solicitacao_testador'] == "Y")) {

                $where .= " and ( ";
                $where .= " id_usuario_responsavel = '" . $row_usuario['IdUsuario'] . "' ";
                $where .= " or ";
                $where .= " id_executante = '" . $row_usuario['IdUsuario'] . "' ";
                $where .= " or ";
                $where .= " id_testador = '" . $row_usuario['IdUsuario'] . "' ";

                $where .= " or ";
                $where .= " id_analista_orcamento = '" . $row_usuario['IdUsuario'] . "' ";

                // praca
                if (($row_usuario['controle_praca'] == "Y")) {
                    $where .= " or ";
                    $where .= " praca = '" . $row_usuario['praca'] . "' ";
                }
                // fim - praca

                $where .= " ) ";

            }
            // fim - executante e testador

            // só executante
            else if (($row_usuario['solicitacao_executante'] == "Y")) {
                $where .= " and ( ";
                $where .= " id_usuario_responsavel = '" . $row_usuario['IdUsuario'] . "' ";
                $where .= " or ";
                $where .= " id_executante = '" . $row_usuario['IdUsuario'] . "' ";

                $where .= " or ";
                $where .= " id_analista_orcamento = '" . $row_usuario['IdUsuario'] . "' ";

                // praca
                if (($row_usuario['controle_praca'] == "Y")) {
                    $where .= " or ";
                    $where .= " praca = '" . $row_usuario['praca'] . "' ";
                }
                // fim - praca

                $where .= " ) ";
            }
            // fim - só executante

            // só testador
            else if (($row_usuario['solicitacao_testador'] == "Y")) {
                $where .= " and ( ";
                $where .= " id_usuario_responsavel = '" . $row_usuario['IdUsuario'] . "' ";
                $where .= " or ";
                $where .= " id_testador = '" . $row_usuario['IdUsuario'] . "' ";

                // praca
                if (($row_usuario['controle_praca'] == "Y")) {
                    $where .= " or ";
                    $where .= " praca = '" . $row_usuario['praca'] . "' ";
                }
                // fim - praca

                $where .= " ) ";
            }
            // fim - só testador

            // se usuário comum
            else if (($row_usuario['controle_praca'] == "N") or ($row_usuario['controle_praca'] == "")) {
                $where .= " and ";
                $where .= " id_usuario_responsavel = '" . $row_usuario['IdUsuario'] . "' ";
            }
            // fim - se usuário comum

            // praca - comum
            else if (($row_usuario['controle_praca'] == "Y")) {
                $where .= " and";
                $where .= " praca = '" . $row_usuario['praca'] . "' ";
            }
            // fim - praca - comum

        }
        // fim - não é operador

        $where_usuario_logado = $where; // registra o WHERE até aqui, onde o faz os filtros por usuário ( serve para o filtro de "campos chaves" )

    }
    // fim - se usuário logado -------------------------------------------------------------------------------------------------------------------------------------------

    $where .= " and previsao_geral_inicio <= '" . date("Y-m-d") . " 23:59:59' ";
    $_GET['dt_previsao_inicio'] = date("d-m-Y");

    // usuário comum
    $where .= " and ( 
					situacao = 'criada' or
					(id_usuario_responsavel = '" . $row_usuario['IdUsuario'] . "' and 
							(
							situacao = 'em validação' or
                            (situacao = 'testada' and status = 'encaminhada para solicitante') or 
							status = 'pendente solicitante' or
							status_questionamento = 'solicitante'
							)
					) ";
    // fim - usuário comum

    // se operador
    if ($row_usuario['controle_solicitacao'] == "Y") {
        $where .= " or ( id_operador = '" . $row_usuario['IdUsuario'] . "' and
							(
							status = 'pendente operador' or
							status = 'encaminhada para operador' or
							status_questionamento = 'operador' or
							
							situacao = 'em análise' or 
							situacao = 'analisada' or
							situacao = 'aprovada' or
                            situacao = 'executada' or 
                            situacao = 'testada' or
							
							(situacao = 'em orçamento' and status <> 'pendente executante') or
							(situacao = 'em execução' and status <> 'pendente executante') or 
							(situacao = 'em testes' and status <> 'pendente testador') or 
							(situacao = 'em validação' and status <> 'pendente solicitante')						
							)
					) ";
    }
    // fim - se operador

    // se analita de orçamento / executante
    if ($row_usuario['solicitacao_executante'] == "Y") {

        $where .= " or ( id_analista_orcamento = '" . $row_usuario['IdUsuario'] . "' and 
							(
							situacao='analisada' or 
							situacao = 'em orçamento' or

							status= 'encaminhada para analista' or
							
							status_questionamento = 'analista de orçamento' or
							
							status = 'devolvida para operador' or 
							status = 'devolvida para executante' or 
							status = 'devolvida para testador'
							)
					)
					or ( id_encaminhamento = '" . $row_usuario['IdUsuario'] . "' )
					or ( id_executante = '" . $row_usuario['IdUsuario'] . "' and 
							(
							(situacao='aprovada' and status='encaminhada para executante') or
							situacao='em execução' or
							situacao='executada' or
							
							(status = 'pendente operador' and status_recusa = '1') or
 							status = 'pendente executante' or
							status = 'encaminhada para executante' or
							
							status_questionamento = 'executante' or
							
							status = 'devolvida para operador' or 
							status = 'devolvida para executante' or 
							status = 'devolvida para testador'							
							)
					) ";
    }
    // fim - se analita de orçamento / executante

    // se testador
    if ($row_usuario['solicitacao_testador'] == "Y") {
        $where .= " or ( id_testador = '" . $row_usuario['IdUsuario'] . "' and

				(
				situacao = 'em testes' or 
				
				status = 'pendente testador' or
				status = 'encaminhada para testador' or
				
				status_questionamento = 'testador' or 
				
				status = 'devolvida para testador'
				)

		) ";
    }
    // fim - se testador

    $where .= " ) ";

}
// fim - se padrão ---------------------------------------------------------------------------------------------------------------------------------------------------


// se não é padrão ---------------------------------------------------------------------------------------------------------------------------------------------------
// se existe filtro de dt_previsao ( somente data inicial )
else if (((isset($_GET["dt_previsao_inicio"])) && ($_GET["dt_previsao_inicio"] != "")) && ($_GET["dt_previsao_inicio"] == "")) {

    // converter data em portugues para ingles
    if (isset($_GET["dt_previsao_inicio"])) {
        $dt_previsao_inicio_data = substr($_GET["dt_previsao_inicio"], 0, 10);
        $dt_previsao_inicio_hora = " 23:59:59";
        $dt_previsao_inicio = implode("-", array_reverse(explode("-", $dt_previsao_inicio_data))) . $dt_previsao_inicio_hora;
    }
    // converter data em portugues para ingles - inicio

    $colname_solicitacao_dt_previsao_inicio = GetSQLValueString($dt_previsao_inicio, "string");
    $where .= " and previsao_geral <= '" . $colname_solicitacao_dt_previsao_inicio . "' ";
}
// inicio - se existe filtro de dt_previsao ( somente data inicial )
// fim - se não é padrão - -------------------------------------------------------------------------------------------------------------------------------------------


//region - filtros -----------------------------------------------------------------------------------------------------------------------------------------------------------

// se existe filtro de dt_previsao_fim ( somente data final )
if (((isset($_GET["dt_previsao_fim"])) && ($_GET["dt_previsao_fim"] != "")) && (@$_GET["dt_previsao_inicio"] == "")) {

    // converter data em portugues para ingles
    if (isset($_GET["dt_previsao_fim"])) {
        $dt_previsao_fim_data = substr($_GET["dt_previsao_fim"], 0, 10);
        $dt_previsao_fim_hora = " 00:00:00";
        $dt_previsao_fim = implode("-", array_reverse(explode("-", $dt_previsao_fim_data))) . $dt_previsao_fim_hora;
    }
    // converter data em portugues para ingles - fim

    $colname_solicitacao_dt_previsao_fim = GetSQLValueString($dt_previsao_fim, "string");
    $where .= " and previsao_geral_inicio >= '" . $colname_solicitacao_dt_previsao_fim . "' ";
}
// fim - se existe filtro de dt_previsao_fim ( somente data final )


// se existe filtro de dt_previsao_inicio ( somente data inicial )
if (((isset($_GET["dt_previsao_inicio"])) && ($_GET["dt_previsao_inicio"] != "")) && (@$_GET["dt_previsao_fim"] == "")) {

    // converter data em portugues para ingles
    if (isset($_GET["dt_previsao_inicio"])) {
        $dt_previsao_inicio_data = substr($_GET["dt_previsao_inicio"], 0, 10);
        $dt_previsao_inicio_hora = " 23:59:59";
        $dt_previsao_inicio = implode("-", array_reverse(explode("-", $dt_previsao_inicio_data))) . $dt_previsao_inicio_hora;
    }
    // converter data em portugues para ingles - fim

    $colname_solicitacao_dt_previsao_inicio = GetSQLValueString($dt_previsao_inicio, "string");
    $where .= " and previsao_geral_inicio <= '" . $colname_solicitacao_dt_previsao_inicio . "' ";
}
// fim - se existe filtro de dt_previsao_inicio ( somente data inicial )


// se existe filtro de dt_previsao_inicio/dt_previsao_fim ( entre data inicial e data final )
if (((isset($_GET["dt_previsao_inicio"])) && ($_GET["dt_previsao_inicio"] != "")) && ((isset($_GET["dt_previsao_fim"])) && ($_GET["dt_previsao_fim"] != ""))) {

    // converter data em portugues para ingles
    if (isset($_GET["dt_previsao_inicio"])) {
        $dt_previsao_inicio_data = substr($_GET["dt_previsao_inicio"], 0, 10);
        $dt_previsao_inicio_hora = " 00:00:00";
        $dt_previsao_inicio = implode("-", array_reverse(explode("-", $dt_previsao_inicio_data))) . $dt_previsao_inicio_hora;
    }
    // converter data em portugues para ingles - fim

    // converter data em portugues para ingles
    if (isset($_GET["dt_previsao_fim"])) {
        $dt_previsao_fim_data = substr($_GET["dt_previsao_fim"], 0, 10);
        $dt_previsao_fim_hora = " 23:59:59";
        $dt_previsao_fim = implode("-", array_reverse(explode("-", $dt_previsao_fim_data))) . $dt_previsao_fim_hora;
    }
    // converter data em portugues para ingles - fim

    $colname_solicitacao_dt_previsao_inicio = GetSQLValueString($dt_previsao_inicio, "string");
    $colname_solicitacao_dt_previsao_fim = GetSQLValueString($dt_previsao_fim, "string");

    $where .= " and previsao_geral_inicio between '$colname_solicitacao_dt_previsao_inicio' and '$colname_solicitacao_dt_previsao_fim' ";
}
// fim - se existe filtro de dt_previsao_inicio/dt_previsao_fim ( entre data inicial e data final )


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
        $query_situacao .= sprintf(" situacao = '$situacao' $or");
    }
    $where .= sprintf($query_situacao) . " ) ";
}
// fim - se existe filtro de situacao


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
        $query_status .= sprintf(" status = '$status' $or");
    }
    $where .= sprintf($query_status) . " ) ";
}
// fim - se existe filtro de status


// se existe filtro de status_questionamento
$contador_status_questionamento = 0;
$contador_status_questionamento_atual = 0;
if ((isset($_GET["status_questionamento"])) && ($_GET['status_questionamento'] != "")) {

    // contar quantidade de situacões atual
    foreach ($_GET["status_questionamento"] as $status_questionamento) {
        $contador_status_questionamento = $contador_status_questionamento + 1;
    }
    // fim - contar quantidade de situacões atual

    //$query_status_questionamento=" and ( status_questionamento IS NULL or ";

    $query_status_questionamento = " and ( ";
    foreach ($_GET["status_questionamento"] as $status_questionamento) {
        $contador_status_questionamento_atual = $contador_status_questionamento_atual + 1; // verifica o contador atual
        $contador_total = $contador_status_questionamento - $contador_status_questionamento_atual; // calcula diferença de situações total - situação atual
        if ($contador_total <> 0) {
            $or = " or ";
        } else {
            $or = "";
        } // se não é a última, então insere OR

        if ($status_questionamento == "nenhum") { // caso seja marcada a opção de nenhum questionamento, então cria um where com o IS NULL
            $query_status_questionamento .= sprintf(" status_questionamento IS NULL $or");
        } else {
            $query_status_questionamento .= sprintf(" status_questionamento = '$status_questionamento' $or");
        }
    }
    $where .= sprintf($query_status_questionamento) . " ) ";
}
// fim - se existe filtro de status_questionamento


// se existe filtro de título
if ((isset($_GET["titulo"])) && ($_GET['titulo'] != "")) {
    $colname_solicitacao_titulo = $_GET['titulo'];
    $where .= " and solicitacao.titulo LIKE '%$colname_solicitacao_titulo%' ";
}
// fim - se existe filtro de título


// se existe filtro de solicitacao_tipo_solicitacao
if ((isset($_GET["solicitacao_tipo_solicitacao"])) && ($_GET['solicitacao_tipo_solicitacao'] != "")) {
    $colname_solicitacao_tipo = GetSQLValueString($_GET["solicitacao_tipo_solicitacao"], "string");
    $where .= " and tipo = '$colname_solicitacao_tipo' ";
}
// fim - se existe filtro de solicitacao_tipo_solicitacao


// se existe filtro de prioridade
if ((isset($_GET["prioridade"])) && ($_GET['prioridade'] != "")) {
    $colname_solicitacao_tipo = GetSQLValueString($_GET["prioridade"], "string");
    $where .= " and prioridade = '$colname_solicitacao_tipo' ";
}
// fim - se existe filtro de prioridade

// se existe filtro de solicitacao_desmembrada
if ((isset($_GET["solicitacao_desmembrada"])) && ($_GET['solicitacao_desmembrada'] != "")) {

    $colname_solicitacao_tipo = GetSQLValueString($_GET["solicitacao_desmembrada"], "string");

    if($colname_solicitacao_tipo == "s"){
        $where .= " and solicitacao_desmembrada > 0 ";
    } else if($colname_solicitacao_tipo == "n"){
        $where .= " and solicitacao_desmembrada IS NULL ";
    }
    
}
// fim - se existe filtro de solicitacao_desmembrada

// se existe filtro de empresa
if ((isset($_GET["empresa"])) && ($_GET['empresa'] != "")) {
    $colname_solicitacao_empresa = GetSQLValueString($_GET["empresa"], "string");
    $where .= " and codigo_empresa = '$colname_solicitacao_empresa' ";
}
// fim - se existe filtro de empresa


// se existe filtro de id_programa
if ((isset($_GET["id_programa"])) && ($_GET['id_programa'] != "")) {
    $colname_solicitacao_id_programa = GetSQLValueString($_GET["id_programa"], "int");
    $where .= " and id_programa = $colname_solicitacao_id_programa ";
}
// fim - se existe filtro de id_programa


// se existe filtro de id_subprograma
if ((isset($_GET["id_subprograma"])) && ($_GET['id_subprograma'] != "")) {
    $colname_solicitacao_id_subprograma = GetSQLValueString($_GET["id_subprograma"], "int");
    $where .= " and id_subprograma = $colname_solicitacao_id_subprograma ";
}
// fim - se existe filtro de id_subprograma


// se existe filtro de versao
$contador_versao = 0;
$contador_versao_atual = 0;
if ((isset($_GET["versao"])) && ($_GET['versao'] != "")) {

 //   FIND_IN_SET('1', category_ids)

    // contar quantidade de situacões atual
    foreach ($_GET["versao"] as $versao) {
        $contador_versao = $contador_versao + 1;
    }
    // fim - contar quantidade de situacões atual

    $query_versao = " and ( ";
    foreach ($_GET["versao"] as $versao) {
        $contador_versao_atual = $contador_versao_atual + 1; // verifica o contador atual
        $contador_total = $contador_versao - $contador_versao_atual; // calcula diferença de situações total - situação atual
        if ($contador_total <> 0) {
            $or = " or ";
        } else {
            $or = "";
        } // se não é a última, então insere OR
        $query_versao .= sprintf(" find_in_set(".$versao.", versao) $or ");
    }
    $where .= sprintf($query_versao) . " ) ";
    
}
// fim - se existe filtro de versao

// se existe filtro de praca
if ((isset($_GET["praca"])) && ($_GET['praca'] != "")) {
    $colname_solicitacao_praca = GetSQLValueString($_GET["praca"], "string");
    $where .= " and praca = '$colname_solicitacao_praca' ";
}
// fim - se existe filtro de praca


// se existe filtro de solicitante (usuario_responsavel)
if ((isset($_GET["solicitante"])) && ($_GET['solicitante'] != "")) {
    $colname_solicitacao_solicitante = GetSQLValueString($_GET["solicitante"], "int");
    $where .= " and id_usuario_responsavel = $colname_solicitacao_solicitante ";
}
// fim - se existe filtro de solicitante (usuario_responsavel)

// se existe filtro de operador
if ((isset($_GET["operador"])) && ($_GET['operador'] != "")) {
    $colname_solicitacao_operador = GetSQLValueString($_GET["operador"], "int");
    $where .= " and id_operador = $colname_solicitacao_operador ";
}
// fim - se existe filtro de operador


// se existe filtro de analista_orcamento
if ((isset($_GET["analista_orcamento"])) && ($_GET['analista_orcamento'] != "")) {
    $colname_solicitacao_analista_orcamento = GetSQLValueString($_GET["analista_orcamento"], "int");
    $where .= " and id_analista_orcamento = $colname_solicitacao_analista_orcamento ";
}
// fim - se existe filtro de analista_orcamento


// se existe filtro de executante
if ((isset($_GET["executante"])) && ($_GET['executante'] != "")) {
    $colname_solicitacao_executante = GetSQLValueString($_GET["executante"], "int");
    $where .= " and id_executante = $colname_solicitacao_executante ";
}
// fim - se existe filtro de executante


// se existe filtro de testador
if ((isset($_GET["testador"])) && ($_GET['testador'] != "")) {
    $colname_solicitacao_testador = GetSQLValueString($_GET["testador"], "int");
    $where .= " and id_testador = $colname_solicitacao_testador ";
}
// fim - se existe filtro de testador


// se existe filtro de dt_solicitacao ( somente data final )
if (((isset($_GET["dt_solicitacao_fim"])) && ($_GET["dt_solicitacao_fim"] != "")) && ($_GET["dt_solicitacao_inicio"] == "")) {

    // converter data em portugues para ingles
    if (isset($_GET["dt_solicitacao_fim"])) {
        $dt_solicitacao_fim_data = substr($_GET["dt_solicitacao_fim"], 0, 10);
        $dt_solicitacao_fim_hora = " 23:59:59";
        $dt_solicitacao_fim = implode("-", array_reverse(explode("-", $dt_solicitacao_fim_data))) . $dt_solicitacao_fim_hora;
    }
    // converter data em portugues para ingles - fim

    $colname_solicitacao_dt_solicitacao_fim = GetSQLValueString($dt_solicitacao_fim, "string");
    $where .= " and dt_solicitacao <= '" . $colname_solicitacao_dt_solicitacao_fim . "' ";
}
// fim - se existe filtro de dt_solicitacao ( somente data final )


// se existe filtro de dt_solicitacao ( somente data inicial )
if (((isset($_GET["dt_solicitacao_inicio"])) && ($_GET["dt_solicitacao_inicio"] != "")) && ($_GET["dt_solicitacao_fim"] == "")) {

    // converter data em portugues para ingles
    if (isset($_GET["dt_solicitacao_inicio"])) {
        $dt_solicitacao_inicio_data = substr($_GET["dt_solicitacao_inicio"], 0, 10);
        $dt_solicitacao_inicio_hora = " 00:00:00";
        $dt_solicitacao_inicio = implode("-", array_reverse(explode("-", $dt_solicitacao_inicio_data))) . $dt_solicitacao_inicio_hora;
    }
    // converter data em portugues para ingles - fim

    $colname_solicitacao_dt_solicitacao_inicio = GetSQLValueString($dt_solicitacao_inicio, "string");
    $where .= " and dt_solicitacao >= '" . $colname_solicitacao_dt_solicitacao_inicio . "' ";
}
// fim - se existe filtro de dt_solicitacao ( somente data inicial )


// se existe filtro de dt_solicitacao ( entre data inicial e data final )
if (((isset($_GET["dt_solicitacao_inicio"])) && ($_GET["dt_solicitacao_inicio"] != "")) && ((isset($_GET["dt_solicitacao_fim"])) && ($_GET["dt_solicitacao_fim"] != ""))) {

    // converter data em portugues para ingles
    if (isset($_GET["dt_solicitacao_inicio"])) {
        $dt_solicitacao_inicio_data = substr($_GET["dt_solicitacao_inicio"], 0, 10);
        $dt_solicitacao_inicio_hora = " 00:00:00";
        $dt_solicitacao_inicio = implode("-", array_reverse(explode("-", $dt_solicitacao_inicio_data))) . $dt_solicitacao_inicio_hora;
    }
    // converter data em portugues para ingles - fim

    // converter data em portugues para ingles
    if (isset($_GET["dt_solicitacao_fim"])) {
        $dt_solicitacao_fim_data = substr($_GET["dt_solicitacao_fim"], 0, 10);
        $dt_solicitacao_fim_hora = " 23:59:59";
        $dt_solicitacao_fim = implode("-", array_reverse(explode("-", $dt_solicitacao_fim_data))) . $dt_solicitacao_fim_hora;
    }
    // converter data em portugues para ingles - fim

    $colname_solicitacao_dt_solicitacao_inicio = GetSQLValueString($dt_solicitacao_inicio, "string");
    $colname_solicitacao_dt_solicitacao_fim = GetSQLValueString($dt_solicitacao_fim, "string");

    $where .= " and dt_solicitacao between '$colname_solicitacao_dt_solicitacao_inicio' and '$colname_solicitacao_dt_solicitacao_fim' ";
}
// fim - se existe filtro de dt_solicitacao ( entre data inicial e data final )


// se existe filtro de dt_conclusao ( somente data final )
if (((isset($_GET["dt_conclusao_fim"])) && ($_GET["dt_conclusao_fim"] != "")) && ($_GET["dt_conclusao_inicio"] == "")) {

    // converter data em portugues para ingles
    if (isset($_GET["dt_conclusao_fim"])) {
        $dt_conclusao_fim_data = substr($_GET["dt_conclusao_fim"], 0, 10);
        $dt_conclusao_fim_hora = " 23:59:59";
        $dt_conclusao_fim = implode("-", array_reverse(explode("-", $dt_conclusao_fim_data))) . $dt_conclusao_fim_hora;
    }
    // converter data em portugues para ingles - fim

    $colname_conclusao_dt_conclusao_fim = GetSQLValueString($dt_conclusao_fim, "string");
    $where .= " and solicitacao_situacao_dt_conclusao_view.dt_conclusao <= '" . $colname_conclusao_dt_conclusao_fim . "' ";

    $join .= " 
    INNER JOIN 
        solicitacao_situacao_dt_conclusao_view ON solicitacao_situacao_dt_conclusao_view.id_solicitacao = solicitacao.id 
     ";

}
// fim - se existe filtro de dt_conclusao ( somente data final )


// se existe filtro de dt_conclusao ( somente data inicial )
if (((isset($_GET["dt_conclusao_inicio"])) && ($_GET["dt_conclusao_inicio"] != "")) && ($_GET["dt_conclusao_fim"] == "")) {

    // converter data em portugues para ingles
    if (isset($_GET["dt_conclusao_inicio"])) {
        $dt_conclusao_inicio_data = substr($_GET["dt_conclusao_inicio"], 0, 10);
        $dt_conclusao_inicio_hora = " 00:00:00";
        $dt_conclusao_inicio = implode("-", array_reverse(explode("-", $dt_conclusao_inicio_data))) . $dt_conclusao_inicio_hora;
    }
    // converter data em portugues para ingles - fim

    $colname_conclusao_dt_conclusao_inicio = GetSQLValueString($dt_conclusao_inicio, "string");
    $where .= " and solicitacao_situacao_dt_conclusao_view.dt_conclusao >= '" . $colname_conclusao_dt_conclusao_inicio . "' ";

    $join .= " 
    INNER JOIN 
        solicitacao_situacao_dt_conclusao_view ON solicitacao_situacao_dt_conclusao_view.id_solicitacao = solicitacao.id 
     ";
    
}
// fim - se existe filtro de dt_conclusao ( somente data inicial )


// se existe filtro de dt_conclusao ( entre data inicial e data final )
if (((isset($_GET["dt_conclusao_inicio"])) && ($_GET["dt_conclusao_inicio"] != "")) && ((isset($_GET["dt_conclusao_fim"])) && ($_GET["dt_conclusao_fim"] != ""))) {

    // converter data em portugues para ingles
    if (isset($_GET["dt_conclusao_inicio"])) {
        $dt_conclusao_inicio_data = substr($_GET["dt_conclusao_inicio"], 0, 10);
        $dt_conclusao_inicio_hora = " 00:00:00";
        $dt_conclusao_inicio = implode("-", array_reverse(explode("-", $dt_conclusao_inicio_data))) . $dt_conclusao_inicio_hora;
    }
    // converter data em portugues para ingles - fim

    // converter data em portugues para ingles
    if (isset($_GET["dt_conclusao_fim"])) {
        $dt_conclusao_fim_data = substr($_GET["dt_conclusao_fim"], 0, 10);
        $dt_conclusao_fim_hora = " 23:59:59";
        $dt_conclusao_fim = implode("-", array_reverse(explode("-", $dt_conclusao_fim_data))) . $dt_conclusao_fim_hora;
    }
    // converter data em portugues para ingles - fim

    $colname_conclusao_dt_conclusao_inicio = GetSQLValueString($dt_conclusao_inicio, "string");
    $colname_conclusao_dt_conclusao_fim = GetSQLValueString($dt_conclusao_fim, "string");
    $where .= " and solicitacao_situacao_dt_conclusao_view.dt_conclusao between '$colname_conclusao_dt_conclusao_inicio' and '$colname_conclusao_dt_conclusao_fim' ";

    $join .= " 
    INNER JOIN 
        solicitacao_situacao_dt_conclusao_view ON solicitacao_situacao_dt_conclusao_view.id_solicitacao = solicitacao.id 
     ";

}
// fim - se existe filtro de dt_conclusao ( entre data inicial e data final )


// se existe filtro de dt_conclusao_testes ( somente data final )
if (((isset($_GET["dt_conclusao_testes_fim"])) && ($_GET["dt_conclusao_testes_fim"] != "")) && ($_GET["dt_conclusao_testes_inicio"] == "")) {

    // converter data em portugues para ingles
    if (isset($_GET["dt_conclusao_testes_fim"])) {
        $dt_conclusao_testes_fim_data = substr($_GET["dt_conclusao_testes_fim"], 0, 10);
        $dt_conclusao_testes_fim_hora = " 23:59:59";
        $dt_conclusao_testes_fim = implode("-", array_reverse(explode("-", $dt_conclusao_testes_fim_data))) . $dt_conclusao_testes_fim_hora;
    }
    // converter data em portugues para ingles - fim

    $colname_conclusao_testes_dt_conclusao_testes_fim = GetSQLValueString($dt_conclusao_testes_fim, "string");
    $where .= " and solicitacao_situacao_dt_conclusao_testes_view.dt_conclusao_testes <= '" . $colname_conclusao_testes_dt_conclusao_testes_fim . "' ";

    $join .= " 
    INNER JOIN 
        solicitacao_situacao_dt_conclusao_testes_view ON solicitacao_situacao_dt_conclusao_testes_view.id_solicitacao = solicitacao.id 
     ";

}
// fim - se existe filtro de dt_conclusao_testes ( somente data final )


// se existe filtro de dt_conclusao_testes ( somente data inicial )
if (((isset($_GET["dt_conclusao_testes_inicio"])) && ($_GET["dt_conclusao_testes_inicio"] != "")) && ($_GET["dt_conclusao_testes_fim"] == "")) {

    // converter data em portugues para ingles
    if (isset($_GET["dt_conclusao_testes_inicio"])) {
        $dt_conclusao_testes_inicio_data = substr($_GET["dt_conclusao_testes_inicio"], 0, 10);
        $dt_conclusao_testes_inicio_hora = " 00:00:00";
        $dt_conclusao_testes_inicio = implode("-", array_reverse(explode("-", $dt_conclusao_testes_inicio_data))) . $dt_conclusao_testes_inicio_hora;
    }
    // converter data em portugues para ingles - fim

    $colname_conclusao_testes_dt_conclusao_testes_inicio = GetSQLValueString($dt_conclusao_testes_inicio, "string");
    $where .= " and solicitacao_situacao_dt_conclusao_testes_view.dt_conclusao_testes >= '" . $colname_conclusao_testes_dt_conclusao_testes_inicio . "' ";

    $join .= " 
    INNER JOIN 
        solicitacao_situacao_dt_conclusao_testes_view ON solicitacao_situacao_dt_conclusao_testes_view.id_solicitacao = solicitacao.id 
     ";

}
// fim - se existe filtro de dt_conclusao_testes ( somente data inicial )


// se existe filtro de dt_conclusao_testes ( entre data inicial e data final )
if (((isset($_GET["dt_conclusao_testes_inicio"])) && ($_GET["dt_conclusao_testes_inicio"] != "")) && ((isset($_GET["dt_conclusao_testes_fim"])) && ($_GET["dt_conclusao_testes_fim"] != ""))) {

    // converter data em portugues para ingles
    if (isset($_GET["dt_conclusao_testes_inicio"])) {
        $dt_conclusao_testes_inicio_data = substr($_GET["dt_conclusao_testes_inicio"], 0, 10);
        $dt_conclusao_testes_inicio_hora = " 00:00:00";
        $dt_conclusao_testes_inicio = implode("-", array_reverse(explode("-", $dt_conclusao_testes_inicio_data))) . $dt_conclusao_testes_inicio_hora;
    }
    // converter data em portugues para ingles - fim

    // converter data em portugues para ingles
    if (isset($_GET["dt_conclusao_testes_fim"])) {
        $dt_conclusao_testes_fim_data = substr($_GET["dt_conclusao_testes_fim"], 0, 10);
        $dt_conclusao_testes_fim_hora = " 23:59:59";
        $dt_conclusao_testes_fim = implode("-", array_reverse(explode("-", $dt_conclusao_testes_fim_data))) . $dt_conclusao_testes_fim_hora;
    }
    // converter data em portugues para ingles - fim

    $colname_conclusao_testes_dt_conclusao_testes_inicio = GetSQLValueString($dt_conclusao_testes_inicio, "string");
    $colname_conclusao_testes_dt_conclusao_testes_fim = GetSQLValueString($dt_conclusao_testes_fim, "string");

    $where .= " and solicitacao_situacao_dt_conclusao_testes_view.dt_conclusao_testes between '$colname_conclusao_testes_dt_conclusao_testes_inicio' and '$colname_conclusao_testes_dt_conclusao_testes_fim' ";

    $join .= " 
    INNER JOIN 
        solicitacao_situacao_dt_conclusao_testes_view ON solicitacao_situacao_dt_conclusao_testes_view.id_solicitacao = solicitacao.id 
     ";

}
// fim - se existe filtro de dt_conclusao_testes ( entre data inicial e data final )


// se existe filtro de dt_aprovacao_reprovacao ( somente data final )
if (((isset($_GET["dt_aprovacao_reprovacao_fim"])) && ($_GET["dt_aprovacao_reprovacao_fim"] != "")) && ($_GET["dt_aprovacao_reprovacao_inicio"] == "")) {

    // converter data em portugues para ingles
    if (isset($_GET["dt_aprovacao_reprovacao_fim"])) {
        $dt_aprovacao_reprovacao_fim_data = substr($_GET["dt_aprovacao_reprovacao_fim"], 0, 10);
        $dt_aprovacao_reprovacao_fim_hora = " 23:59:59";
        $dt_aprovacao_reprovacao_fim = implode("-", array_reverse(explode("-", $dt_aprovacao_reprovacao_fim_data))) . $dt_aprovacao_reprovacao_fim_hora;
    }
    // converter data em portugues para ingles - fim

    $colname_aprovacao_reprovacao_dt_aprovacao_reprovacao_fim = GetSQLValueString($dt_aprovacao_reprovacao_fim, "string");
    $where .= " and dt_aprovacao_reprovacao <= '" . $colname_aprovacao_reprovacao_dt_aprovacao_reprovacao_fim . "' ";
}
// fim - se existe filtro de dt_aprovacao_reprovacao ( somente data final )


// se existe filtro de dt_aprovacao_reprovacao ( somente data inicial )
if (((isset($_GET["dt_aprovacao_reprovacao_inicio"])) && ($_GET["dt_aprovacao_reprovacao_inicio"] != "")) && ($_GET["dt_aprovacao_reprovacao_fim"] == "")) {

    // converter data em portugues para ingles
    if (isset($_GET["dt_aprovacao_reprovacao_inicio"])) {
        $dt_aprovacao_reprovacao_inicio_data = substr($_GET["dt_aprovacao_reprovacao_inicio"], 0, 10);
        $dt_aprovacao_reprovacao_inicio_hora = " 00:00:00";
        $dt_aprovacao_reprovacao_inicio = implode("-", array_reverse(explode("-", $dt_aprovacao_reprovacao_inicio_data))) . $dt_aprovacao_reprovacao_inicio_hora;
    }
    // converter data em portugues para ingles - fim

    $colname_aprovacao_reprovacao_dt_aprovacao_reprovacao_inicio = GetSQLValueString($dt_aprovacao_reprovacao_inicio, "string");
    $where .= " and dt_aprovacao_reprovacao >= '" . $colname_aprovacao_reprovacao_dt_aprovacao_reprovacao_inicio . "' ";
}
// fim - se existe filtro de dt_aprovacao_reprovacao ( somente data inicial )


// se existe filtro de dt_aprovacao_reprovacao ( entre data inicial e data final )
if (((isset($_GET["dt_aprovacao_reprovacao_inicio"])) && ($_GET["dt_aprovacao_reprovacao_inicio"] != "")) && ((isset($_GET["dt_aprovacao_reprovacao_fim"])) && ($_GET["dt_aprovacao_reprovacao_fim"] != ""))) {

    // converter data em portugues para ingles
    if (isset($_GET["dt_aprovacao_reprovacao_inicio"])) {
        $dt_aprovacao_reprovacao_inicio_data = substr($_GET["dt_aprovacao_reprovacao_inicio"], 0, 10);
        $dt_aprovacao_reprovacao_inicio_hora = " 00:00:00";
        $dt_aprovacao_reprovacao_inicio = implode("-", array_reverse(explode("-", $dt_aprovacao_reprovacao_inicio_data))) . $dt_aprovacao_reprovacao_inicio_hora;
    }
    // converter data em portugues para ingles - fim

    // converter data em portugues para ingles
    if (isset($_GET["dt_aprovacao_reprovacao_fim"])) {
        $dt_aprovacao_reprovacao_fim_data = substr($_GET["dt_aprovacao_reprovacao_fim"], 0, 10);
        $dt_aprovacao_reprovacao_fim_hora = " 23:59:59";
        $dt_aprovacao_reprovacao_fim = implode("-", array_reverse(explode("-", $dt_aprovacao_reprovacao_fim_data))) . $dt_aprovacao_reprovacao_fim_hora;
    }
    // converter data em portugues para ingles - fim

    $colname_aprovacao_reprovacao_dt_aprovacao_reprovacao_inicio = GetSQLValueString($dt_aprovacao_reprovacao_inicio, "string");
    $colname_aprovacao_reprovacao_dt_aprovacao_reprovacao_fim = GetSQLValueString($dt_aprovacao_reprovacao_fim, "string");

    $where .= " and dt_aprovacao_reprovacao between '$colname_aprovacao_reprovacao_dt_aprovacao_reprovacao_inicio' and '$colname_aprovacao_reprovacao_dt_aprovacao_reprovacao_fim' ";
}
// fim - se existe filtro de dt_aprovacao_reprovacao ( entre data inicial e data final )


// se existe filtro de tipo
$contador_tipo = 0;
$contador_tipo_atual = 0;
if ((isset($_GET["tipo"])) && ($_GET['tipo'] != "")) {

    // contar quantidade de situacões atual
    foreach ($_GET["tipo"] as $tipo) {
        $contador_tipo = $contador_tipo + 1;
    }
    // fim - contar quantidade de situacões atual

    $query_tipo = " and ( ";
    foreach ($_GET["tipo"] as $tipo) {
        $contador_tipo_atual = $contador_tipo_atual + 1; // verifica o contador atual
        $contador_total = $contador_tipo - $contador_tipo_atual; // calcula diferença de situações total - situação atual
        if ($contador_total <> 0) {
            $or = " or ";
        } else {
            $or = "";
        } // se não é a última, então insere OR
        $query_tipo .= sprintf(" tipo = '$tipo' $or");
    }
    $where .= sprintf($query_tipo) . " ) ";
}
// fim - se existe filtro de tipo


// se existe filtro de protocolo_suporte
if ((isset($_GET["protocolo_suporte"])) && ($_GET['protocolo_suporte'] != "")) {
    $colname_solicitacao_protocolo_suporte = GetSQLValueString($_GET["protocolo_suporte"], "int");
    $where .= $where_usuario_logado . " and solicitacao.protocolo_suporte = $colname_solicitacao_protocolo_suporte ";
}
// fim - se existe filtro de protocolo_suporte


// se existe filtro de id da solicitação
if ((isset($_GET["id"])) && ($_GET['id'] != "")) {
    $colname_solicitacao_id = GetSQLValueString($_GET["id"], "int");
    $where .= $where_usuario_logado . " and solicitacao.id = $colname_solicitacao_id ";
}
// fim - se existe filtro de id da solicitação

//endregion -  fim - filtros -----------------------------------------------------------------------------------------------------------------------------------------------------

$query_solicitacao = "
SELECT 
    solicitacao.*, 

    DATEDIFF (
        
        NOW(), 

        (
        case 
            when 
                (solicitacao.status = 'pendente solicitante' and solicitacao.previsao_geral IS NOT NULL and solicitacao.previsao_geral <> '0000-00-00 00:00:00') 
            then 
                ADDDATE(solicitacao.previsao_geral, INTERVAL solicitacao_tipo_solicitacao.prazo_validacao_solicitacao_dias DAY)
        else solicitacao.previsao_geral end 
        ) 

    ) AS previsao_geral_dias_atraso, 


    (
    case 
		when 
			(solicitacao.status = 'pendente solicitante' and solicitacao.previsao_geral IS NOT NULL and solicitacao.previsao_geral <> '0000-00-00 00:00:00') 
		then 
			ADDDATE(solicitacao.previsao_geral, INTERVAL solicitacao_tipo_solicitacao.prazo_validacao_solicitacao_dias DAY)
    else solicitacao.previsao_geral end 
	) AS previsao_geral, 


    (SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_usuario_responsavel) as usuario_responsavel, 
    (SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_operador) as nome_operador, 
    (SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_executante) as nome_executante, 
    (SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_analista_orcamento) as nome_analista_orcamento, 
    (SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_testador) as nome_testador, 

    (
        SELECT 
            solicitacao_tipo_solicitacao.solicitacao_auto_email_dias 
        FROM 
            solicitacao_tipo_solicitacao 
        WHERE 
            solicitacao_tipo_solicitacao.titulo = solicitacao.tipo
    ) AS solicitacao_auto_email_dias, 

    (
        SELECT 
            suporte_reclamacao_contador 
        FROM 
            suporte_reclamacao_contador_view 
        WHERE 
            suporte_reclamacao_contador_view.contrato = solicitacao.contrato and 
            suporte_reclamacao_contador_view.status_flag = 'a' 
    )  AS suporte_reclamacao_em_andamento, 

    (
        SELECT 
            suporte.data_suporte 
        FROM 
            suporte 
        WHERE 
            suporte.id = solicitacao.protocolo_suporte
    ) AS data_suporte
    
FROM 
    solicitacao 
$join 
LEFT JOIN 
	solicitacao_tipo_solicitacao ON solicitacao_tipo_solicitacao.titulo = solicitacao.tipo 
$where 
GROUP BY
    solicitacao.id 
ORDER BY 
    solicitacao.previsao_geral ASC
";
$solicitacao = mysql_query($query_solicitacao, $conexao) or die(mysql_error());
$row_solicitacao = mysql_fetch_assoc($solicitacao);
$totalRows_solicitacao = mysql_num_rows($solicitacao);
// -------------------------------------------------------------------------------------------------------------------------------------------------------------------
// fim - solicitações ------------------------------------------------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------------------------------------------------------------------

//region - recordset para filtros
// listagem das empresas em filtros
mysql_select_db($database_conexao, $conexao);

$query_filtro_empresas = "";

// seleciona os EXECUTORES que o usuário atual tem acesso E montagem da query -------------------------------------------------
$query_usuarios_geral_tipo_praca_executor = sprintf(" 
	SELECT usuarios.praca, geral_tipo_praca_executor.praca, usuarios.IdUsuario, geral_tipo_praca_executor.IdExecutor
	FROM usuarios 
	INNER JOIN geral_tipo_praca_executor ON  usuarios.praca = geral_tipo_praca_executor.praca 
	WHERE usuarios.IdUsuario = " . $row_usuario['IdUsuario'] . "
	");
$usuarios_geral_tipo_praca_executor = mysql_query($query_usuarios_geral_tipo_praca_executor, $conexao) or die(mysql_error());
$sql_filtro_empresas_vendedor17 = "";

// lista os EXECUTORES - em cada EXECUTOR com acesso, é adicionado seu codigo a uma string
while ($row_usuarios_geral_tipo_praca_executor = mysql_fetch_assoc($usuarios_geral_tipo_praca_executor)) {
    $sql_filtro_empresas_vendedor17 .= "vendedor17 = '" . $row_usuarios_geral_tipo_praca_executor['IdExecutor'] . "' or ";
}
// fim - lista ...

$sql_filtro_empresas_vendedor17 = substr($sql_filtro_empresas_vendedor17, 0, -4);
// fim - seleciona os EXECUTORES que o usuário atual tem acesso E montagem da query -------------------------------------------

// filtra os contratos(manutenções) que o usuário atual tem, filtrando pelos seus EXECUTORES permitidos - pega as strings montadas acima...

// se existe filtro de IdUsuario e NÃO possui CONTROLE DE SOLICITACAO ou está VAZIO
if (($row_usuario['controle_solicitacao'] == "Y") or
    ($row_usuario['solicitacao_executante'] == "Y") or
    ($row_usuario['solicitacao_testador'] == "Y")
) {

    $query_filtro_empresas = "
				SELECT da37.cliente17, da01.codigo1, da37.codigo17, da37.vendedor17, da37.vendedor17, da01.codigo1, da01.nome1
				FROM da37 
				INNER JOIN da01 ON da37.cliente17 = da01.codigo1 
				WHERE da01.sr_deleted <> 'T' and da37.sr_deleted <> 'T' 
				ORDER BY da01.nome1
	";
} else {
    // fim - se existe filtro de IdUsuario

    // se não existe filtro
    $query_filtro_empresas = "
				SELECT da37.cliente17, da01.codigo1, da37.codigo17, da37.vendedor17, da37.vendedor17, da01.codigo1, da01.nome1
				FROM da37 
				INNER JOIN da01 ON da37.cliente17 = da01.codigo1 
				WHERE ($sql_filtro_empresas_vendedor17) and da01.sr_deleted <> 'T' and da37.sr_deleted <> 'T' 
				ORDER BY da01.nome1
	";
}
// fim - se não existe filtro

$filtro_empresas = mysql_query($query_filtro_empresas, $conexao) or die(mysql_error());
$row_filtro_empresas = mysql_fetch_assoc($filtro_empresas);
$totalRows_filtro_empresas = mysql_num_rows($filtro_empresas);
// fim - filtra
// fim - listagem das empresas em filtros

mysql_select_db($database_conexao, $conexao);
$query_filtro_geral_tipo_versao = "SELECT * FROM geral_tipo_versao ORDER BY IdTipoVersao ASC";
$filtro_geral_tipo_versao = mysql_query($query_filtro_geral_tipo_versao, $conexao) or die(mysql_error());
$row_filtro_geral_tipo_versao = mysql_fetch_assoc($filtro_geral_tipo_versao);
$totalRows_filtro_geral_tipo_versao = mysql_num_rows($filtro_geral_tipo_versao);

mysql_select_db($database_conexao, $conexao);
$query_filtro_geral_tipo_programa = "SELECT * FROM geral_tipo_programa ORDER BY programa ASC";
$filtro_geral_tipo_programa = mysql_query($query_filtro_geral_tipo_programa, $conexao) or die(mysql_error());
$row_filtro_geral_tipo_programa = mysql_fetch_assoc($filtro_geral_tipo_programa);
$totalRows_filtro_geral_tipo_programa = mysql_num_rows($filtro_geral_tipo_programa);

mysql_select_db($database_conexao, $conexao);
$query_filtro_solicitacao_tipo_solicitacao = "SELECT * FROM solicitacao_tipo_solicitacao ORDER BY titulo ASC";
$filtro_solicitacao_tipo_solicitacao = mysql_query($query_filtro_solicitacao_tipo_solicitacao, $conexao) or die(mysql_error());
$row_filtro_solicitacao_tipo_solicitacao = mysql_fetch_assoc($filtro_solicitacao_tipo_solicitacao);
$totalRows_filtro_solicitacao_tipo_solicitacao = mysql_num_rows($filtro_solicitacao_tipo_solicitacao);

mysql_select_db($database_conexao, $conexao);
$query_filtro_prioridade = "SELECT * FROM solicitacao_tipo_prioridade ORDER BY titulo ASC";
$filtro_prioridade = mysql_query($query_filtro_prioridade, $conexao) or die(mysql_error());
$row_filtro_prioridade = mysql_fetch_assoc($filtro_prioridade);
$totalRows_filtro_prioridade = mysql_num_rows($filtro_prioridade);

mysql_select_db($database_conexao, $conexao);
$query_filtro_praca = "SELECT * FROM geral_tipo_praca ORDER BY praca ASC";
$filtro_praca = mysql_query($query_filtro_praca, $conexao) or die(mysql_error());
$row_filtro_praca = mysql_fetch_assoc($filtro_praca);
$totalRows_filtro_praca = mysql_num_rows($filtro_praca);

mysql_select_db($database_conexao, $conexao);
$query_filtro_solicitante = "SELECT IdUsuario, nome, usuario FROM usuarios ORDER BY nome ASC";
$filtro_solicitante = mysql_query($query_filtro_solicitante, $conexao) or die(mysql_error());
$row_filtro_solicitante = mysql_fetch_assoc($filtro_solicitante);
$totalRows_filtro_solicitante = mysql_num_rows($filtro_solicitante);

mysql_select_db($database_conexao, $conexao);
$query_filtro_operador = "SELECT IdUsuario, nome, usuario FROM usuarios WHERE controle_solicitacao = 'Y' ORDER BY nome ASC";
$filtro_operador = mysql_query($query_filtro_operador, $conexao) or die(mysql_error());
$row_filtro_operador = mysql_fetch_assoc($filtro_operador);
$totalRows_filtro_operador = mysql_num_rows($filtro_operador);

mysql_select_db($database_conexao, $conexao);
$query_filtro_analista_orcamento = "SELECT IdUsuario, nome, usuario FROM usuarios WHERE solicitacao_executante = 'Y' ORDER BY nome ASC";
$filtro_analista_orcamento = mysql_query($query_filtro_analista_orcamento, $conexao) or die(mysql_error());
$row_filtro_analista_orcamento = mysql_fetch_assoc($filtro_analista_orcamento);
$totalRows_filtro_analista_orcamento = mysql_num_rows($filtro_analista_orcamento);

mysql_select_db($database_conexao, $conexao);
$query_filtro_executante = "SELECT IdUsuario, nome, usuario FROM usuarios WHERE solicitacao_executante = 'Y' ORDER BY nome ASC";
$filtro_executante = mysql_query($query_filtro_executante, $conexao) or die(mysql_error());
$row_filtro_executante = mysql_fetch_assoc($filtro_executante);
$totalRows_filtro_executante = mysql_num_rows($filtro_executante);

mysql_select_db($database_conexao, $conexao);
$query_filtro_testador = "SELECT IdUsuario, nome, usuario FROM usuarios WHERE solicitacao_testador = 'Y' ORDER BY nome ASC";
$filtro_testador = mysql_query($query_filtro_testador, $conexao) or die(mysql_error());
$row_filtro_testador = mysql_fetch_assoc($filtro_testador);
$totalRows_filtro_testador = mysql_num_rows($filtro_testador);
//endregion - fim - recordset para filtros
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link href="css/suporte.css" rel="stylesheet" type="text/css">
    <!--[if !IE]> -->
    <style>
        body {
            overflow-y: scroll;
            /* se não é IE, então mostra a scroll vertical */
        }
    </style>
    <!-- <![endif]-->
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

        #caixa_questionamentos {
            width: 500px;
            padding: 5px;
            position: absolute;
            z-index: 100;
            float: left;

            border: 2px solid #06C;
            background-color: #FFF;
        }
    </style>

    <script type="text/javascript" src="js/jquery.js"></script>

    <script type="text/javascript" src="js/jquery.maskedinput-1.2.2.min.js"></script>
    <script type="text/javascript" src="js/jquery.alphanumeric.pack.js"></script>

    <link rel="stylesheet" href="css/thickbox.css" type="text/css" media="screen" />
    <script type="text/javascript" src="js/thickbox.js"></script>

    <link rel="stylesheet" type="text/css" media="screen" href="css/jquery-ui.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="css/ui.jqgrid.css" />
    <script src="js/grid.locale-pt-br.js" type="text/javascript"></script>
    <script src="js/jquery.jqGrid.src.js" type="text/javascript"></script>

    <script type="text/javascript">
        $.jgrid.no_legacy_api = true;

        // 2 cliques na solcicitação
        function solicitacao_editar($id) {
            $(document).ready(function() {
                top.location.href = "solicitacao_editar.php?id_solicitacao=" + id + "&padrao=sim";
            });
        };
        // fim - 2 cliques na solcicitação

        // limpar formulário de filtro
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
        // fim - limpar formulário de filtro

        $(document).ready(function() {

            // ocultar/exibir filtros
            $('#corpo_filtros').toggle();
            $('#cabecalho_filtros').click(function() {
                $('#corpo_filtros').toggle();
            });
            // fim - ocultar/exibir fitlros

            // ocultar/exibir solicitacao
            //$('#corpo_solicitacao').toggle();
            $('#cabecalho_solicitacao').click(function() {
                $('#corpo_solicitacao').toggle();
            });
            // fim - ocultar/exibir solicitacao

            // mascara - inicio
            $('#dt_solicitacao_inicio').mask('99-99-9999', {
                placeholder: " "
            });
            $('#dt_solicitacao_fim').mask('99-99-9999', {
                placeholder: " "
            });

            $('#dt_conclusao_inicio').mask('99-99-9999', {
                placeholder: " "
            });
            $('#dt_conclusao_fim').mask('99-99-9999', {
                placeholder: " "
            });

            $('#dt_conclusao_testes_inicio').mask('99-99-9999', {
                placeholder: " "
            });
            $('#dt_conclusao_testes_fim').mask('99-99-9999', {
                placeholder: " "
            });

            $('#dt_aprovacao_reprovacao_inicio').mask('99-99-9999', {
                placeholder: " "
            });
            $('#dt_aprovacao_reprovacao_fim').mask('99-99-9999', {
                placeholder: " "
            });

            $('#dt_previsao_inicio').mask('99-99-9999', {
                placeholder: " "
            });
            $('#dt_previsao_fim').mask('99-99-9999', {
                placeholder: " "
            });

            $('#numero_protocolo').numeric();
            // mascara - fim

            // geral_tipo_programa
            $("select[name=id_programa]").change(function() {
                $("select[name=id_subprograma]").html('<option value="0">Carregando...</option>');

                $.post("solicitacao_geral_tipo_subprograma.php", {
                        id_programa: $(this).val()
                    },
                    function(valor) {
                        $("select[name=id_subprograma]").html(valor);
                    }
                )

            })
            // geral_tipo_programa - fim

            // marcar todos
            $('#checkall_status').click(function() {
                $(this).parents('fieldset:eq(0)').find(':checkbox').attr('checked', this.checked);
            });

            $('#checkall_status_questionamento').click(function() {
                $(this).parents('fieldset:eq(0)').find(':checkbox').attr('checked', this.checked);
            });

            $('#checkall_situacao').click(function() {
                $(this).parents('fieldset:eq(0)').find(':checkbox').attr('checked', this.checked);
            });

            $('#checkall_tipo').click(function() {
                $(this).parents('fieldset:eq(0)').find(':checkbox').attr('checked', this.checked);
            });
            // fim - marcar todos

            // questionamento ao passar o mouse em '?'
            $('#caixa_questionamentos').hide();
            $('.jqgrow').find('.ponto_interrogacao').mouseover(function(e) {

                id_atual = $(this).attr('id');
                $('#caixa_questionamentos').html("<img src='imagens/loadingAnimation.gif'>");
                $.post("solicitacao_questionamentos.php", {
                        id: id_atual
                    },
                    function(valor) {
                        $("#caixa_questionamentos").html(valor).slideToggle("fast");
                    }
                )

            });

            $('.jqgrow').find('span').mouseout(function(e) {
                $("#caixa_questionamentos").hide();
            });
            // fim - questionamento ao passar o mouse em '?'

        });
    </script>
    <title>Solicitação</title>
</head>

<body>
    <? 
    // echo $where; 
    // echo $query_solicitacao;
    ?>
    <div class="div_solicitacao_linhas">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td style="text-align:left">
                    Controle de solicitação
                    <font color="#3399CC"> | </font>
                    <a href="suporte.php?padrao=sim&<? echo $suporte_padrao; ?>" style="color: #D1E3F1">Controle de suporte</a></font>
                    <font color="#3399CC"> | </font>
                    <a href="prospeccao.php?padrao=sim&<? echo $prospeccao_padrao; ?>" style="color: #D1E3F1">Controle de prospecção</a>
                    <font color="#3399CC"> | </font>
                    <a href="venda.php?padrao=sim&<? echo $venda_padrao; ?>" style="color: #D1E3F1">Controle de vendas</a>
                </td>

                <td style="text-align: right">
                    &lt;&lt; <a href="index.php">Voltar</a> |
                    Usuário logado: <? echo $row_usuario['nome']; ?> |
                    <a href="painel/padrao_sair.php">Sair</a>
                </td>
            </tr>
        </table>
    </div>

    <div class="div_solicitacao_linhas2">
        Clique sobre a opção desejada para visualizar mais informações.
    </div>

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
        <form name="buscar" action="solicitacao.php" method="GET">
            <div class="div_filtros">
                <table cellspacing="0" cellpadding="0" width="100%">
                    <tr>
                        <td style="text-align:left">
                            <span class="label_solicitacao">Título:</span>
                            <input name="titulo" type="text" id="titulo" value="<? if (isset($_GET['titulo'])) {
                                                                                    echo $_GET['titulo'];
                                                                                } ?>" size="110" />
                        </td>

                        <td style="text-align: right">
                            <span class="label_solicitacao"><strong>Número da solicitação:</strong> </span>
                            <input name="id" type="text" id="id" value="<? if (isset($_GET['id'])) {
                                                                            echo $_GET['id'];
                                                                        } ?>" size="20" />
                        </td>

                    </tr>
                </table>
            </div>

            <div class="div_filtros2">
                <table cellspacing="0" cellpadding="0" width="100%">
                    <tr>
                        <td style="text-align:left">
                            <fieldset style="border: 0px;">
                                <span class="label_solicitacao">Situação:</span>
                                <input name="situacao[]" type="checkbox" value="criada" <?
                                                                                        // verificar se foi selecionada
                                                                                        if (isset($_GET['situacao'])) {
                                                                                            foreach ($_GET["situacao"] as $situacao) {
                                                                                                if ($situacao == "criada") {
                                                                                                    echo "checked=\"checked\"";
                                                                                                }
                                                                                            }
                                                                                        }
                                                                                        // verificar se foi selecionada
                                                                                        ?> />criada


                                <input name="situacao[]" type="checkbox" value="recebida" <?
                                                                                            // verificar se foi selecionada
                                                                                            if (isset($_GET['situacao'])) {
                                                                                                foreach ($_GET["situacao"] as $situacao) {
                                                                                                    if ($situacao == "recebida") {
                                                                                                        echo "checked=\"checked\"";
                                                                                                    }
                                                                                                }
                                                                                            }
                                                                                            // verificar se foi selecionada
                                                                                            ?> />recebida

                                <input name="situacao[]" type="checkbox" value="em análise" <?
                                                                                            // verificar se foi selecionada
                                                                                            if (isset($_GET['situacao'])) {
                                                                                                foreach ($_GET["situacao"] as $situacao) {
                                                                                                    if ($situacao == "em análise") {
                                                                                                        echo "checked=\"checked\"";
                                                                                                    }
                                                                                                }
                                                                                            }
                                                                                            // verificar se foi selecionada
                                                                                            ?> />em análise

                                <input name="situacao[]" type="checkbox" value="analisada" <?
                                                                                            // verificar se foi selecionada
                                                                                            if (isset($_GET['situacao'])) {
                                                                                                foreach ($_GET["situacao"] as $situacao) {
                                                                                                    if ($situacao == "analisada") {
                                                                                                        echo "checked=\"checked\"";
                                                                                                    }
                                                                                                }
                                                                                            }
                                                                                            // verificar se foi selecionada
                                                                                            ?> />analisada

                                <input name="situacao[]" type="checkbox" value="em orçamento" <?
                                                                                                // verificar se foi selecionada
                                                                                                if (isset($_GET['situacao'])) {
                                                                                                    foreach ($_GET["situacao"] as $situacao) {
                                                                                                        if ($situacao == "em orçamento") {
                                                                                                            echo "checked=\"checked\"";
                                                                                                        }
                                                                                                    }
                                                                                                }
                                                                                                // verificar se foi selecionada
                                                                                                ?> />em orçamento

                                <input name="situacao[]" type="checkbox" value="aprovada" <?
                                                                                            // verificar se foi selecionada
                                                                                            if (isset($_GET['situacao'])) {
                                                                                                foreach ($_GET["situacao"] as $situacao) {
                                                                                                    if ($situacao == "aprovada") {
                                                                                                        echo "checked=\"checked\"";
                                                                                                    }
                                                                                                }
                                                                                            }
                                                                                            // verificar se foi selecionada
                                                                                            ?> />aprovada

                                <input name="situacao[]" type="checkbox" value="em execução" <?
                                                                                                // verificar se foi selecionada
                                                                                                if (isset($_GET['situacao'])) {
                                                                                                    foreach ($_GET["situacao"] as $situacao) {
                                                                                                        if ($situacao == "em execução") {
                                                                                                            echo "checked=\"checked\"";
                                                                                                        }
                                                                                                    }
                                                                                                }
                                                                                                // verificar se foi selecionada
                                                                                                ?> />em execução

                                <input name="situacao[]" type="checkbox" value="executada" <?
                                                                                            // verificar se foi selecionada
                                                                                            if (isset($_GET['situacao'])) {
                                                                                                foreach ($_GET["situacao"] as $situacao) {
                                                                                                    if ($situacao == "executada") {
                                                                                                        echo "checked=\"checked\"";
                                                                                                    }
                                                                                                }
                                                                                            }
                                                                                            // verificar se foi selecionada
                                                                                            ?> />executada

                                <input name="situacao[]" type="checkbox" value="em testes" <?
                                                                                            // verificar se foi selecionada
                                                                                            if (isset($_GET['situacao'])) {
                                                                                                foreach ($_GET["situacao"] as $situacao) {
                                                                                                    if ($situacao == "em testes") {
                                                                                                        echo "checked=\"checked\"";
                                                                                                    }
                                                                                                }
                                                                                            }
                                                                                            // verificar se foi selecionada
                                                                                            ?> />em testes

                                <input name="situacao[]" type="checkbox" value="testada" <?
                                                                                            // verificar se foi selecionada
                                                                                            if (isset($_GET['situacao'])) {
                                                                                                foreach ($_GET["situacao"] as $situacao) {
                                                                                                    if ($situacao == "testada") {
                                                                                                        echo "checked=\"checked\"";
                                                                                                    }
                                                                                                }
                                                                                            }
                                                                                            // verificar se foi selecionada
                                                                                            ?> />testada

                                <input name="situacao[]" type="checkbox" value="em validação" <?
                                                                                                // verificar se foi selecionada
                                                                                                if (isset($_GET['situacao'])) {
                                                                                                    foreach ($_GET["situacao"] as $situacao) {
                                                                                                        if ($situacao == "em validação") {
                                                                                                            echo "checked=\"checked\"";
                                                                                                        }
                                                                                                    }
                                                                                                }
                                                                                                // verificar se foi selecionada
                                                                                                ?> />em validação

                                <input name="situacao[]" type="checkbox" value="solucionada" <?
                                                                                                // verificar se foi selecionada
                                                                                                if (isset($_GET['situacao'])) {
                                                                                                    foreach ($_GET["situacao"] as $situacao) {
                                                                                                        if ($situacao == "solucionada") {
                                                                                                            echo "checked=\"checked\"";
                                                                                                        }
                                                                                                    }
                                                                                                }
                                                                                                // verificar se foi selecionada
                                                                                                ?> />solucionada

                                <input name="situacao[]" type="checkbox" value="reprovada" <?
                                                                                            // verificar se foi selecionada
                                                                                            if (isset($_GET['situacao'])) {
                                                                                                foreach ($_GET["situacao"] as $situacao) {
                                                                                                    if ($situacao == "reprovada") {
                                                                                                        echo "checked=\"checked\"";
                                                                                                    }
                                                                                                }
                                                                                            }
                                                                                            // verificar se foi selecionada
                                                                                            ?> />reprovada

                                <input type="checkbox" id="checkall_situacao" name="checkall_situacao" />Marcar todos
                            </fieldset>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="div_filtros">
                <table cellspacing="0" cellpadding="0" width="100%">
                    <tr>
                        <td style="text-align:left">
                            <span class="label_solicitacao">Empresa:</span>
                            <select name="empresa">
                                <option value="">Escolha ...</option>
                                <?php do { ?>
                                    <option value="<?php echo $row_filtro_empresas['codigo1'] ?>" <?php if ((isset($_GET['empresa'])) and (!(strcmp($row_filtro_empresas['codigo1'], $_GET['empresa'])))) {
                                                                                                        echo "selected=\"selected\"";
                                                                                                    } ?>>
                                        <?php echo $row_filtro_empresas['nome1'] ?>
                                    </option>
                                <?php
                                } while ($row_filtro_empresas = mysql_fetch_assoc($filtro_empresas));
                                $rows = mysql_num_rows($filtro_empresas);
                                if ($rows > 0) {
                                    mysql_data_seek($filtro_empresas, 0);
                                    $row_filtro_empresas = mysql_fetch_assoc($filtro_empresas);
                                }
                                ?>
                            </select>
                        </td>

                        <? //if($row_usuario['controle_solicitacao']=="Y"){ 
                        ?>
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
                        <? //} 
                        ?>

                        <td style="text-align:right" width="300px">
                            <span class="label_solicitacao">Núm. Controle Suporte: </span>
                            <input name="protocolo_suporte" type="text" value="<? if (isset($_GET['protocolo_suporte'])) {
                                                                                    echo $_GET['protocolo_suporte'];
                                                                                } ?>" />
                        </td>
                    </tr>
                </table>
            </div>

            <div class="div_filtros2">
                <table cellspacing="0" cellpadding="0" width="100%">
                    <tr>
                        <td style="text-align:left">
                            <span class="label_solicitacao">Programa: </span>
                            <select name="id_programa">
                                <option value="">
                                    Escolha ...
                                </option>
                                <?php do { ?>
                                    <option value="<?php echo $row_filtro_geral_tipo_programa['id_programa'] ?>" <?php if ((isset($_GET['id_programa'])) and (!(strcmp($row_filtro_geral_tipo_programa['id_programa'], $_GET['id_programa'])))) {
                                                                                                                    echo "selected=\"selected\"";
                                                                                                                } ?>>
                                        <?php echo $row_filtro_geral_tipo_programa['programa'] ?>
                                    </option>
                                <?php
                                } while ($row_filtro_geral_tipo_programa = mysql_fetch_assoc($filtro_geral_tipo_programa));
                                $rows = mysql_num_rows($filtro_geral_tipo_programa);
                                if ($rows > 0) {
                                    mysql_data_seek($filtro_geral_tipo_programa, 0);
                                    $row_filtro_geral_tipo_programa = mysql_fetch_assoc($filtro_geral_tipo_programa);
                                }
                                ?>
                            </select>
                        </td>

                        <td style="text-align:right" class="div_filtros_solicitacao_corpo_td">
                            <span class="label_solicitacao">Subprograma: </span>
                            <? if ((isset($_GET['id_programa'])) && ($_GET['id_programa'] != "")) { // caso possua algum id_programa definido ... 
                            ?>

                                <?
                                $colname_filtro_geral_tipo_subprograma = "-1";
                                if (isset($_GET['id_programa'])) {
                                    $colname_filtro_geral_tipo_subprograma = $_GET['id_programa'];
                                }
                                mysql_select_db($database_conexao, $conexao);
                                $query_filtro_geral_tipo_subprograma = sprintf(
                                    "SELECT * FROM geral_tipo_subprograma WHERE id_programa = %s ORDER BY subprograma ASC",
                                    GetSQLValueString($colname_filtro_geral_tipo_subprograma, "int")
                                );
                                $filtro_geral_tipo_subprograma = mysql_query($query_filtro_geral_tipo_subprograma, $conexao) or die(mysql_error());
                                $row_filtro_geral_tipo_subprograma = mysql_fetch_assoc($filtro_geral_tipo_subprograma);
                                $totalRows_filtro_geral_tipo_subprograma = mysql_num_rows($filtro_geral_tipo_subprograma);
                                ?>
                                <select name="id_subprograma">
                                    <option value="">
                                        Escolha o subprograma ...
                                    </option>
                                    <?php do { ?>
                                        <option value="<?php echo $row_filtro_geral_tipo_subprograma['id_subprograma'] ?>" <?php if ((isset($_GET['id_subprograma'])) and (!(strcmp($row_filtro_geral_tipo_subprograma['id_subprograma'], $_GET['id_subprograma'])))) {
                                                                                                                                echo "selected=\"selected\"";
                                                                                                                            } ?>>
                                            <?php echo $row_filtro_geral_tipo_subprograma['subprograma'] ?>
                                        </option>
                                    <?php
                                    } while ($row_filtro_geral_tipo_subprograma = mysql_fetch_assoc($filtro_geral_tipo_subprograma));
                                    $rows = mysql_num_rows($filtro_geral_tipo_subprograma);
                                    if ($rows > 0) {
                                        mysql_data_seek($filtro_geral_tipo_subprograma, 0);
                                        $row_filtro_geral_tipo_subprograma = mysql_fetch_assoc($filtro_geral_tipo_subprograma);
                                    }
                                    ?>
                                </select>

                                <? mysql_free_result($filtro_geral_tipo_subprograma); ?>

                            <? } else { // caso não possua nenhum id_programa definido ... 
                            ?>

                                <select name="id_subprograma">
                                    <option value="">Escolha um programa primeiro ... </option>
                                </select>

                            <? } ?>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="div_filtros">
                <table cellspacing="0" cellpadding="0" width="100%">
                    <tr>
                        <td width="270" class="div_filtros_solicitacao_corpo_td" style="text-align:left;">
                            <span class="label_solicitacao">Versão: </span>

                            <fieldset>
                            <?php do { ?>
                                
                                <input  name="versao[]" id="versao" type="checkbox" class="checkbox" 
                                value="<? echo $row_filtro_geral_tipo_versao['IdTipoVersao']; ?>" 
                                <?
                                // verificar se foi selecionada
                                if (isset($_GET['versao'])) {
                                    foreach ($_GET["versao"] as $versao) {
                                        if ($versao == $row_filtro_geral_tipo_versao['IdTipoVersao']) {
                                            echo "checked=\"checked\"";
                                        }
                                    }
                                }
                                // verificar se foi selecionada
                                ?>
                                /> 
                                <? echo $row_filtro_geral_tipo_versao['titulo']; ?>

                            <?php } while ($row_filtro_geral_tipo_versao = mysql_fetch_assoc($filtro_geral_tipo_versao)); ?>
                            </fieldset>
                                
                        </td>

                    </tr>
                </table>
            </div>

            <div class="div_filtros">
                <table cellspacing="0" cellpadding="0" width="100%">
                    <tr>
                        <td style="text-align:left;">
                            <span class="label_solicitacao">Tipo de solicitação:</span>
                            <select name="solicitacao_tipo_solicitacao">
                                <option value="">
                                    Escolha ...
                                </option>
                                <?php do { ?>
                                    <option value="<?php echo $row_filtro_solicitacao_tipo_solicitacao['titulo'] ?>" <?php if ((isset($_GET['solicitacao_tipo_solicitacao'])) and (!(strcmp($row_filtro_solicitacao_tipo_solicitacao['titulo'], $_GET['solicitacao_tipo_solicitacao'])))) {
                                                                                                                        echo "selected=\"selected\"";
                                                                                                                    } ?>>
                                        <?php echo $row_filtro_solicitacao_tipo_solicitacao['titulo'] ?>
                                    </option>
                                <?php
                                } while ($row_filtro_solicitacao_tipo_solicitacao = mysql_fetch_assoc($filtro_solicitacao_tipo_solicitacao));
                                $rows = mysql_num_rows($filtro_solicitacao_tipo_solicitacao);
                                if ($rows > 0) {
                                    mysql_data_seek($filtro_solicitacao_tipo_solicitacao, 0);
                                    $row_filtro_solicitacao_tipo_solicitacao = mysql_fetch_assoc($filtro_solicitacao_tipo_solicitacao);
                                }
                                ?>
                            </select>
                        </td>

                        <td style="text-align: right;">
                            <span class="label_solicitacao">Prioridade:</span>
                            <select name="prioridade">
                                <option value="">
                                    Escolha ...
                                </option>
                                <?php do { ?>
                                    <option value="<?php echo $row_filtro_prioridade['titulo'] ?>" <?php if ((isset($_GET['prioridade'])) and (!(strcmp($row_filtro_prioridade['titulo'], $_GET['prioridade'])))) {
                                                                                                        echo "selected=\"selected\"";
                                                                                                    } ?>>
                                        <?php echo $row_filtro_prioridade['titulo'] ?>
                                    </option>
                                <?php
                                } while ($row_filtro_prioridade = mysql_fetch_assoc($filtro_prioridade));
                                $rows = mysql_num_rows($filtro_prioridade);
                                if ($rows > 0) {
                                    mysql_data_seek($filtro_prioridade, 0);
                                    $row_filtro_prioridade = mysql_fetch_assoc($filtro_prioridade);
                                }
                                ?>
                            </select>
                        </td>

                        <td style="text-align: right;" width="200px">
                            <span class="label_solicitacao">Desmembrada:</span>
                            <select name="solicitacao_desmembrada">
                                <option value="">...</option>
                                <option value="n" <?php if ((isset($_GET['solicitacao_desmembrada'])) and (!(strcmp("n", $_GET['solicitacao_desmembrada'])))) {echo "selected=\"selected\"";} ?>>Não</option>
                                <option value="s" <?php if ((isset($_GET['solicitacao_desmembrada'])) and (!(strcmp("s", $_GET['solicitacao_desmembrada'])))) {echo "selected=\"selected\"";} ?>>Sim</option>
                            </select>
                        </td>

                    </tr>
                </table>
            </div>

            <div class="div_filtros2">
                <table cellspacing="0" cellpadding="0" width="100%">
                    <tr>
                        <td style="text-align:left">
                            <span class="label_solicitacao">Data da previsão (início): </span>
                            <input name="dt_previsao_inicio" id="dt_previsao_inicio" type="text" value="<?
                                                                                                        if (isset($_GET['dt_previsao_inicio'])) {
                                                                                                            echo $_GET['dt_previsao_inicio'];
                                                                                                        }
                                                                                                        ?>" />
                        </td>

                        <td style="text-align:right">
                            <span class="label_solicitacao">Data da previsão (fim): </span>
                            <input name="dt_previsao_fim" id="dt_previsao_fim" type="text" value="<?
                                                                                                    if (isset($_GET['dt_previsao_fim'])) {
                                                                                                        echo $_GET['dt_previsao_fim'];
                                                                                                    }
                                                                                                    ?>" />
                        </td>
                    </tr>
                </table>
            </div>

            <div class="div_filtros">
                <table cellspacing="0" cellpadding="0" width="100%">
                    <tr>
                        <td style="text-align:left">
                            <span class="label_solicitacao">Data da solicitação (início): </span>
                            <input name="dt_solicitacao_inicio" id="dt_solicitacao_inicio" type="text" value="<?
                                                                                                                if (isset($_GET['dt_solicitacao_inicio'])) {
                                                                                                                    echo $_GET['dt_solicitacao_inicio'];
                                                                                                                }
                                                                                                                ?>" />
                        </td>

                        <td style="text-align:right" class="div_filtros_solicitacao_corpo_td">
                            <span class="label_solicitacao">Data da solicitação (fim): </span>
                            <input name="dt_solicitacao_fim" id="dt_solicitacao_fim" type="text" value="<?
                                                                                                        if (isset($_GET['dt_solicitacao_fim'])) {
                                                                                                            echo $_GET['dt_solicitacao_fim'];
                                                                                                        }
                                                                                                        ?>" />
                        </td>
                    </tr>
                </table>
            </div>

            <div class="div_filtros2">
                <table cellspacing="0" cellpadding="0" width="100%">
                    <tr>
                        <td style="text-align:left">
                            <span class="label_solicitacao">Data da Conclusão da Execução (início): </span>
                            <input name="dt_conclusao_inicio" id="dt_conclusao_inicio" type="text" value="<?
                                                                                                            if (isset($_GET['dt_conclusao_inicio'])) {
                                                                                                                echo $_GET['dt_conclusao_inicio'];
                                                                                                            }
                                                                                                            ?>" />
                        </td>

                        <td style="text-align:right" class="div_filtros_solicitacao_corpo_td">
                            <span class="label_solicitacao">Data da Conclusão da Execução (fim): </span>
                            <input name="dt_conclusao_fim" id="dt_conclusao_fim" type="text" value="<?
                                                                                                    if (isset($_GET['dt_conclusao_fim'])) {
                                                                                                        echo $_GET['dt_conclusao_fim'];
                                                                                                    }
                                                                                                    ?>" />
                        </td>
                    </tr>
                </table>
            </div>

            <div class="div_filtros">
                <table cellspacing="0" cellpadding="0" width="100%">
                    <tr>
                        <td style="text-align:left">
                            <span class="label_solicitacao">Data da Conclusão dos Testes (início): </span>
                            <input name="dt_conclusao_testes_inicio" id="dt_conclusao_testes_inicio" type="text" value="<?
                                                                                                                        if (isset($_GET['dt_conclusao_testes_inicio'])) {
                                                                                                                            echo $_GET['dt_conclusao_testes_inicio'];
                                                                                                                        }
                                                                                                                        ?>" />
                        </td>

                        <td style="text-align:right" class="div_filtros_solicitacao_corpo_td">
                            <span class="label_solicitacao">Data da Conclusão dos Testes (fim): </span>
                            <input name="dt_conclusao_testes_fim" id="dt_conclusao_testes_fim" type="text" value="<?
                                                                                                                    if (isset($_GET['dt_conclusao_testes_fim'])) {
                                                                                                                        echo $_GET['dt_conclusao_testes_fim'];
                                                                                                                    }
                                                                                                                    ?>" />
                        </td>
                    </tr>
                </table>
            </div>


            <div class="div_filtros2">
                <table cellspacing="0" cellpadding="0" width="100%">
                    <tr>
                        <td style="text-align:left">
                            <span class="label_solicitacao">Data da Aprovação/Reprovação (início): </span>
                            <input name="dt_aprovacao_reprovacao_inicio" id="dt_aprovacao_reprovacao_inicio" type="text" value="<?
                                                                                                                                if (isset($_GET['dt_aprovacao_reprovacao_inicio'])) {
                                                                                                                                    echo $_GET['dt_aprovacao_reprovacao_inicio'];
                                                                                                                                }
                                                                                                                                ?>" />
                        </td>

                        <td style="text-align:right" class="div_filtros_solicitacao_corpo_td">
                            <span class="label_solicitacao">Data da Aprovação/Reprovação (fim): </span>
                            <input name="dt_aprovacao_reprovacao_fim" id="dt_aprovacao_reprovacao_fim" type="text" value="<?
                                                                                                                            if (isset($_GET['dt_aprovacao_reprovacao_fim'])) {
                                                                                                                                echo $_GET['dt_aprovacao_reprovacao_fim'];
                                                                                                                            }
                                                                                                                            ?>" />
                        </td>
                    </tr>
                </table>
            </div>

            <div class="div_filtros">
                <table cellspacing="0" cellpadding="0" width="100%">
                    <tr>

                        <td style="text-align: left">
                            <span class="label_solicitacao">Solicitante: </span>
                            <select name="solicitante">
                                <option value="" <?php if (!(strcmp("", isset($_GET['solicitante'])))) {
                                                        echo "selected=\"selected\"";
                                                    } ?>>
                                    Escolha ...
                                </option>
                                <?php do {  ?>
                                    <option value="<?php echo $row_filtro_solicitante['IdUsuario'] ?>" <?php if ((isset($_GET['solicitante'])) and (!(strcmp($row_filtro_solicitante['IdUsuario'], $_GET['solicitante'])))) {
                                                                                                            echo "selected=\"selected\"";
                                                                                                        } ?>>
                                        <?php echo $row_filtro_solicitante['nome'] ?>
                                    </option>
                                <?php
                                } while ($row_filtro_solicitante = mysql_fetch_assoc($filtro_solicitante));
                                $rows = mysql_num_rows($filtro_solicitante);
                                if ($rows > 0) {
                                    mysql_data_seek($filtro_solicitante, 0);
                                    $row_filtro_solicitante = mysql_fetch_assoc($filtro_solicitante);
                                }
                                ?>
                            </select>
                        </td>

                        <td style="text-align: right">
                            <span class="label_solicitacao">Operador: </span>
                            <select name="operador">
                                <option value="" <?php if (!(strcmp("", isset($_GET['operador'])))) {
                                                        echo "selected=\"selected\"";
                                                    } ?>>
                                    Escolha ...
                                </option>
                                <?php do {  ?>
                                    <option value="<?php echo $row_filtro_operador['IdUsuario'] ?>" <?php if ((isset($_GET['operador'])) and (!(strcmp($row_filtro_operador['IdUsuario'], $_GET['operador'])))) {
                                                                                                        echo "selected=\"selected\"";
                                                                                                    } ?>>
                                        <?php echo $row_filtro_operador['nome'] ?>
                                    </option>
                                <?php
                                } while ($row_filtro_operador = mysql_fetch_assoc($filtro_operador));
                                $rows = mysql_num_rows($filtro_operador);
                                if ($rows > 0) {
                                    mysql_data_seek($filtro_operador, 0);
                                    $row_filtro_operador = mysql_fetch_assoc($filtro_operador);
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

                        <td style="text-align: left">
                            <span class="label_solicitacao">Analista de orçamento: </span>
                            <select name="analista_orcamento">
                                <option value="" <?php if (!(strcmp("", isset($_GET['analista_orcamento'])))) {
                                                        echo "selected=\"selected\"";
                                                    } ?>>
                                    Escolha ...
                                </option>
                                <?php do {  ?>
                                    <option value="<?php echo $row_filtro_analista_orcamento['IdUsuario'] ?>" <?php if ((isset($_GET['analista_orcamento'])) and (!(strcmp($row_filtro_analista_orcamento['IdUsuario'], $_GET['analista_orcamento'])))) {
                                                                                                                    echo "selected=\"selected\"";
                                                                                                                } ?>>
                                        <?php echo $row_filtro_analista_orcamento['nome'] ?>
                                    </option>
                                <?php
                                } while ($row_filtro_analista_orcamento = mysql_fetch_assoc($filtro_analista_orcamento));
                                $rows = mysql_num_rows($filtro_analista_orcamento);
                                if ($rows > 0) {
                                    mysql_data_seek($filtro_analista_orcamento, 0);
                                    $row_filtro_analista_orcamento = mysql_fetch_assoc($filtro_analista_orcamento);
                                }
                                ?>
                            </select>
                        </td>

                        <td style="text-align: left">
                            <span class="label_solicitacao">Executante: </span>
                            <select name="executante">
                                <option value="" <?php if (!(strcmp("", isset($_GET['executante'])))) {
                                                        echo "selected=\"selected\"";
                                                    } ?>>
                                    Escolha ...
                                </option>
                                <?php do {  ?>
                                    <option value="<?php echo $row_filtro_executante['IdUsuario'] ?>" <?php if ((isset($_GET['executante'])) and (!(strcmp($row_filtro_executante['IdUsuario'], $_GET['executante'])))) {
                                                                                                            echo "selected=\"selected\"";
                                                                                                        } ?>>
                                        <?php echo $row_filtro_executante['nome'] ?>
                                    </option>
                                <?php
                                } while ($row_filtro_executante = mysql_fetch_assoc($filtro_executante));
                                $rows = mysql_num_rows($filtro_executante);
                                if ($rows > 0) {
                                    mysql_data_seek($filtro_executante, 0);
                                    $row_filtro_executante = mysql_fetch_assoc($filtro_executante);
                                }
                                ?>
                            </select>
                        </td>

                        <td style="text-align: right">
                            <span class="label_solicitacao">Testador: </span>
                            <select name="testador">
                                <option value="" <?php if (!(strcmp("", isset($_GET['testador'])))) {
                                                        echo "selected=\"selected\"";
                                                    } ?>>
                                    Escolha ...
                                </option>
                                <?php do {  ?>
                                    <option value="<?php echo $row_filtro_testador['IdUsuario'] ?>" <?php if ((isset($_GET['testador'])) and (!(strcmp($row_filtro_testador['IdUsuario'], $_GET['testador'])))) {
                                                                                                        echo "selected=\"selected\"";
                                                                                                    } ?>>
                                        <?php echo $row_filtro_testador['nome'] ?>
                                    </option>
                                <?php
                                } while ($row_filtro_testador = mysql_fetch_assoc($filtro_testador));
                                $rows = mysql_num_rows($filtro_testador);
                                if ($rows > 0) {
                                    mysql_data_seek($filtro_testador, 0);
                                    $row_filtro_testador = mysql_fetch_assoc($filtro_testador);
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
                            <fieldset style="border: 0px;">
                                <span class="label_solicitacao">Tipo:</span>

                                <input name="tipo[]" type="checkbox" value="Correção" <?
                                                                                        // verificar se foi selecionada
                                                                                        if (isset($_GET['tipo'])) {
                                                                                            foreach ($_GET["tipo"] as $tipo) {
                                                                                                if ($tipo == "Correção") {
                                                                                                    echo "checked=\"checked\"";
                                                                                                }
                                                                                            }
                                                                                        }
                                                                                        // verificar se foi selecionada
                                                                                        ?> />Correção

                                <input name="tipo[]" type="checkbox" value="Implementação" <?
                                                                                            // verificar se foi selecionada
                                                                                            if (isset($_GET['tipo'])) {
                                                                                                foreach ($_GET["tipo"] as $tipo) {
                                                                                                    if ($tipo == "Implementação") {
                                                                                                        echo "checked=\"checked\"";
                                                                                                    }
                                                                                                }
                                                                                            }
                                                                                            // verificar se foi selecionada
                                                                                            ?> />Implementação

                                <input name="tipo[]" type="checkbox" value="Dúvida" <?
                                                                                    // verificar se foi selecionada
                                                                                    if (isset($_GET['tipo'])) {
                                                                                        foreach ($_GET["tipo"] as $tipo) {
                                                                                            if ($tipo == "Dúvida") {
                                                                                                echo "checked=\"checked\"";
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                    // verificar se foi selecionada
                                                                                    ?> />Dúvida

                                <input name="tipo[]" type="checkbox" value="Fiscal" <?
                                                                                    // verificar se foi selecionada
                                                                                    if (isset($_GET['tipo'])) {
                                                                                        foreach ($_GET["tipo"] as $tipo) {
                                                                                            if ($tipo == "Fiscal") {
                                                                                                echo "checked=\"checked\"";
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                    // verificar se foi selecionada
                                                                                    ?> />Fiscal

                                <input name="tipo[]" type="checkbox" value="Sugestão" <?
                                                                                        // verificar se foi selecionada
                                                                                        if (isset($_GET['tipo'])) {
                                                                                            foreach ($_GET["tipo"] as $tipo) {
                                                                                                if ($tipo == "Sugestão") {
                                                                                                    echo "checked=\"checked\"";
                                                                                                }
                                                                                            }
                                                                                        }
                                                                                        // verificar se foi selecionada
                                                                                        ?> />Sugestão

                                <input type="checkbox" id="checkall_tipo" name="checkall_tipo" />Marcar todos
                            </fieldset>
                        </td>

                    </tr>
                </table>
            </div>

            <!-- status -->
            <div class="div_filtros">
                <table cellspacing="0" cellpadding="0" width="100%">
                    <tr>

                        <td style="text-align:left">
                            <fieldset style="border: 0px;">
                                <span class="label_solicitacao">Status:</span>
                                <input name="status[]" type="checkbox" value="pendente solicitante" <?
                                                                                                    // verificar se foi selecionada
                                                                                                    if (isset($_GET['status'])) {
                                                                                                        foreach ($_GET["status"] as $status) {
                                                                                                            if ($status == "pendente solicitante") {
                                                                                                                echo "checked=\"checked\"";
                                                                                                            }
                                                                                                        }
                                                                                                    }
                                                                                                    // verificar se foi selecionada
                                                                                                    ?> />PenSol

                                <input name="status[]" type="checkbox" value="pendente operador" <?
                                                                                                    // verificar se foi selecionada
                                                                                                    if (isset($_GET['status'])) {
                                                                                                        foreach ($_GET["status"] as $status) {
                                                                                                            if ($status == "pendente operador") {
                                                                                                                echo "checked=\"checked\"";
                                                                                                            }
                                                                                                        }
                                                                                                    }
                                                                                                    // verificar se foi selecionada
                                                                                                    ?> />PenOpe

                                <input name="status[]" type="checkbox" value="pendente executante" <?
                                                                                                    // verificar se foi selecionada
                                                                                                    if (isset($_GET['status'])) {
                                                                                                        foreach ($_GET["status"] as $status) {
                                                                                                            if ($status == "pendente executante") {
                                                                                                                echo "checked=\"checked\"";
                                                                                                            }
                                                                                                        }
                                                                                                    }
                                                                                                    // verificar se foi selecionada
                                                                                                    ?> />PenExe

                                <input name="status[]" type="checkbox" value="pendente testador" <?
                                                                                                    // verificar se foi selecionada
                                                                                                    if (isset($_GET['status'])) {
                                                                                                        foreach ($_GET["status"] as $status) {
                                                                                                            if ($status == "pendente testador") {
                                                                                                                echo "checked=\"checked\"";
                                                                                                            }
                                                                                                        }
                                                                                                    }
                                                                                                    // verificar se foi selecionada
                                                                                                    ?> />PenTes

                                <input name="status[]" type="checkbox" value="encaminhada para solicitante" <?
                                                                                                            // verificar se foi selecionada
                                                                                                            if (isset($_GET['status'])) {
                                                                                                                foreach ($_GET["status"] as $status) {
                                                                                                                    if ($status == "encaminhada para solicitante") {
                                                                                                                        echo "checked=\"checked\"";
                                                                                                                    }
                                                                                                                }
                                                                                                            }
                                                                                                            // verificar se foi selecionada
                                                                                                            ?> />EncSol

                                <input name="status[]" type="checkbox" value="encaminhada para operador" <?
                                                                                                            // verificar se foi selecionada
                                                                                                            if (isset($_GET['status'])) {
                                                                                                                foreach ($_GET["status"] as $status) {
                                                                                                                    if ($status == "encaminhada para operador") {
                                                                                                                        echo "checked=\"checked\"";
                                                                                                                    }
                                                                                                                }
                                                                                                            }
                                                                                                            // verificar se foi selecionada
                                                                                                            ?> />EncOpe

                                <input name="status[]" type="checkbox" value="encaminhada para executante" <?
                                                                                                            // verificar se foi selecionada
                                                                                                            if (isset($_GET['status'])) {
                                                                                                                foreach ($_GET["status"] as $status) {
                                                                                                                    if ($status == "encaminhada para executante") {
                                                                                                                        echo "checked=\"checked\"";
                                                                                                                    }
                                                                                                                }
                                                                                                            }
                                                                                                            // verificar se foi selecionada
                                                                                                            ?> />EncExe

                                <input name="status[]" type="checkbox" value="encaminhada para testador" <?
                                                                                                            // verificar se foi selecionada
                                                                                                            if (isset($_GET['status'])) {
                                                                                                                foreach ($_GET["status"] as $status) {
                                                                                                                    if ($status == "encaminhada para testador") {
                                                                                                                        echo "checked=\"checked\"";
                                                                                                                    }
                                                                                                                }
                                                                                                            }
                                                                                                            // verificar se foi selecionada
                                                                                                            ?> />EncTes

                                <input name="status[]" type="checkbox" value="encaminhada para analista" <?
                                                                                                            // verificar se foi selecionada
                                                                                                            if (isset($_GET['status'])) {
                                                                                                                foreach ($_GET["status"] as $status) {
                                                                                                                    if ($status == "encaminhada para analista") {
                                                                                                                        echo "checked=\"checked\"";
                                                                                                                    }
                                                                                                                }
                                                                                                            }
                                                                                                            // verificar se foi selecionada
                                                                                                            ?> />EncAna

                                <input name="status[]" type="checkbox" value="devolvida para solicitante" <?
                                                                                                        // verificar se foi selecionada
                                                                                                        if (isset($_GET['status'])) {
                                                                                                            foreach ($_GET["status"] as $status) {
                                                                                                                if ($status == "devolvida para solicitante") {
                                                                                                                    echo "checked=\"checked\"";
                                                                                                                }
                                                                                                            }
                                                                                                        }
                                                                                                        // verificar se foi selecionada
                                                                                                        ?> />DevSol

                                <input name="status[]" type="checkbox" value="devolvida para operador" <?
                                                                                                        // verificar se foi selecionada
                                                                                                        if (isset($_GET['status'])) {
                                                                                                            foreach ($_GET["status"] as $status) {
                                                                                                                if ($status == "devolvida para operador") {
                                                                                                                    echo "checked=\"checked\"";
                                                                                                                }
                                                                                                            }
                                                                                                        }
                                                                                                        // verificar se foi selecionada
                                                                                                        ?> />DevOpe

                                <input name="status[]" type="checkbox" value="devolvida para executante" <?
                                                                                                            // verificar se foi selecionada
                                                                                                            if (isset($_GET['status'])) {
                                                                                                                foreach ($_GET["status"] as $status) {
                                                                                                                    if ($status == "devolvida para executante") {
                                                                                                                        echo "checked=\"checked\"";
                                                                                                                    }
                                                                                                                }
                                                                                                            }
                                                                                                            // verificar se foi selecionada
                                                                                                            ?> />DevExe

                                <input name="status[]" type="checkbox" value="devolvida para testador" <?
                                                                                                        // verificar se foi selecionada
                                                                                                        if (isset($_GET['status'])) {
                                                                                                            foreach ($_GET["status"] as $status) {
                                                                                                                if ($status == "devolvida para testador") {
                                                                                                                    echo "checked=\"checked\"";
                                                                                                                }
                                                                                                            }
                                                                                                        }
                                                                                                        // verificar se foi selecionada
                                                                                                        ?> />DevTes

                                <input type="checkbox" id="checkall_status" name="checkall_status" />Marcar todos
                            </fieldset>
                        </td>

                    </tr>
                </table>
            </div>
            <!-- fim - status -->

            <!-- status_questionamento -->
            <div class="div_filtros2">
                <table cellspacing="0" cellpadding="0" width="100%">
                    <tr>

                        <td style="text-align:left">
                            <fieldset style="border: 0px;">
                                <span class="label_solicitacao">Questionamento para:</span>

                                <input name="status_questionamento[]" type="checkbox" value="nenhum" <?
                                                                                                        // verificar se foi selecionada
                                                                                                        if (isset($_GET['status_questionamento'])) {
                                                                                                            foreach ($_GET["status_questionamento"] as $status_questionamento) {
                                                                                                                if ($status_questionamento == "nenhum") {
                                                                                                                    echo "checked=\"checked\"";
                                                                                                                }
                                                                                                            }
                                                                                                        }
                                                                                                        // verificar se foi selecionada
                                                                                                        ?> />nenhum

                                <input name="status_questionamento[]" type="checkbox" value="solicitante" <?
                                                                                                            // verificar se foi selecionada
                                                                                                            if (isset($_GET['status_questionamento'])) {
                                                                                                                foreach ($_GET["status_questionamento"] as $status_questionamento) {
                                                                                                                    if ($status_questionamento == "solicitante") {
                                                                                                                        echo "checked=\"checked\"";
                                                                                                                    }
                                                                                                                }
                                                                                                            }
                                                                                                            // verificar se foi selecionada
                                                                                                            ?> />solicitante

                                <input name="status_questionamento[]" type="checkbox" value="operador" <?
                                                                                                        // verificar se foi selecionada
                                                                                                        if (isset($_GET['status_questionamento'])) {
                                                                                                            foreach ($_GET["status_questionamento"] as $status_questionamento) {
                                                                                                                if ($status_questionamento == "operador") {
                                                                                                                    echo "checked=\"checked\"";
                                                                                                                }
                                                                                                            }
                                                                                                        }
                                                                                                        // verificar se foi selecionada
                                                                                                        ?> />operador

                                <input name="status_questionamento[]" type="checkbox" value="analista de orçamento" <?
                                                                                                                    // verificar se foi selecionada
                                                                                                                    if (isset($_GET['status_questionamento'])) {
                                                                                                                        foreach ($_GET["status_questionamento"] as $status_questionamento) {
                                                                                                                            if ($status_questionamento == "analista de orçamento") {
                                                                                                                                echo "checked=\"checked\"";
                                                                                                                            }
                                                                                                                        }
                                                                                                                    }
                                                                                                                    // verificar se foi selecionada
                                                                                                                    ?> />analista de orçamento

                                <input name="status_questionamento[]" type="checkbox" value="executante" <?
                                                                                                            // verificar se foi selecionada
                                                                                                            if (isset($_GET['status_questionamento'])) {
                                                                                                                foreach ($_GET["status_questionamento"] as $status_questionamento) {
                                                                                                                    if ($status_questionamento == "executante") {
                                                                                                                        echo "checked=\"checked\"";
                                                                                                                    }
                                                                                                                }
                                                                                                            }
                                                                                                            // verificar se foi selecionada
                                                                                                            ?> />executante

                                <input name="status_questionamento[]" type="checkbox" value="testador" <?
                                                                                                        // verificar se foi selecionada
                                                                                                        if (isset($_GET['status_questionamento'])) {
                                                                                                            foreach ($_GET["status_questionamento"] as $status_questionamento) {
                                                                                                                if ($status_questionamento == "testador") {
                                                                                                                    echo "checked=\"checked\"";
                                                                                                                }
                                                                                                            }
                                                                                                        }
                                                                                                        // verificar se foi selecionada
                                                                                                        ?> />testador

                                <input type="checkbox" id="checkall_status_questionamento" name="checkall_status_questionamento" />Marcar todos
                            </fieldset>
                        </td>

                    </tr>
                </table>
            </div>
            <!-- fim - status_questionamento -->

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
        </form>
    </div>
    <!-- fim - Filtros -->

    <!-- solicitações -->
    <? if ($totalRows_solicitacao > 0) { ?>

        <div class="div_solicitacao_linhas" id="cabecalho_solicitacao" style="cursor: pointer">
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td style="text-align:left">
                        Solicitações (<? echo $totalRows_solicitacao; ?>)
                    </td>

                    <td style="text-align: right">
                        <div style="float: right; background-image:url(imagens/icone_filtro.png); width: 12px; height: 12px;"></div>
                    </td>
                </tr>
            </table>
        </div>

        <div id="caixa_questionamentos"></div>

        <div id="corpo_solicitacao" style="cursor: pointer">
            <table id="solicitacao"></table>
            <div id="navegacao"></div>
            <script type="text/javascript">
                var dados = [

                    <?php do { ?>

                        <?
                        // status ------------------------------------------------------------------------------------------------------------------------------------
                        $cor_css = "cor_black";
                        if ($row_solicitacao['previsao_geral'] != "") {

                            $previsao_geral = $row_solicitacao['previsao_geral'];
                            $previsao_geral = strtotime($previsao_geral); // converte em segundos

                            $data_atual = strtotime(date("Y-m-d H:i:s")); // converte em segundos
                            $diferenca = $data_atual - $previsao_geral;

                            # teste
                            if (
                                $data_atual >= $previsao_geral and 
                                $diferenca >= 86400 and $diferenca < 172800 // entre 24 e 48 hrs
                            ) { 
                                $cor_css = "cor_orange";
                            } else if (
                                $data_atual >= $previsao_geral and 
                                $diferenca >= 172800 // mais de 48hrs
                            ) { // mais de 48hrs
                                $cor_css = "cor_red";
                            }
                            # fim - teste

                        }
                        // fim - status ------------------------------------------------------------------------------------------------------------------------------
                        ?>

                        <? $previsao_data_exibir = NULL; ?>
                        
                        <? if($row_solicitacao['situacao']=="em análise" and $row_solicitacao['previsao_analise']!="0000-00-00 00:00:00") { ?>
                            <? $previsao_data_exibir = $row_solicitacao['previsao_analise']; ?>
                        <? } else ?>
                        
                        <? if($row_solicitacao['situacao']=="em orçamento" and $row_solicitacao['previsao_analise_orcamento']!="0000-00-00 00:00:00" and $row_solicitacao['orcamento']=="") { ?>
                            <? $previsao_data_exibir = $row_solicitacao['previsao_analise_orcamento']; ?>
                        <? } else ?>

                        <? if($row_solicitacao['situacao']=="em orçamento" and $row_solicitacao['previsao_retorno_orcamento']!="0000-00-00 00:00:00") { ?>
                            <? $previsao_data_exibir = $row_solicitacao['previsao_retorno_orcamento']; ?>
                        <? } else ?>
                        
                        <? if($row_solicitacao['situacao']=="em execução" and $row_solicitacao['previsao_solucao']!="0000-00-00 00:00:00") { ?>
                            <? $previsao_data_exibir = $row_solicitacao['previsao_solucao']; ?>
                        <? } else ?>
                            
                        <? if($row_solicitacao['situacao']=="em testes" and $row_solicitacao['previsao_testes']!="0000-00-00 00:00:00") { ?>
                            <? $previsao_data_exibir = $row_solicitacao['previsao_testes']; ?>
                        <? } else ?>
                        
                        <? if($row_solicitacao['situacao']=="em validação" and $row_solicitacao['previsao_validacao']!="0000-00-00 00:00:00") { ?>
                            <? $previsao_data_exibir = $row_solicitacao['previsao_validacao']; ?>
                        <? } ?>

                        {
                            id: "<?php echo $row_solicitacao['id']; ?>",
                            titulo: "<? echo GetSQLValueString($row_solicitacao['titulo'], "string"); //ogetsqlValueString resolve o problema de quando alguem digitar aspas duplas ?>",
                            empresa: "<?php echo utf8_encode($row_solicitacao['empresa']); ?>",
                            suporte_reclamacao_em_andamento: "<?php 
                                if($row_solicitacao['suporte_reclamacao_em_andamento'] > 0){
                                    echo "<div class='cor_red'>Sim</div>";
                                }  else {
                                    echo "<div class='cor_black'>Não</div>";
                                }
                            ?>",
                            solicitante: "<?php echo $row_solicitacao['usuario_responsavel']; ?>", 
                            operador: "<?php echo $row_solicitacao['nome_operador']; ?>",
                            executante: "<?php if ($row_solicitacao['situacao'] == "em orçamento" or $row_solicitacao['status'] == "encaminhada para analista") {
                                                echo $row_solicitacao['nome_analista_orcamento'] . "*";
                                            } else {
                                                echo $row_solicitacao['nome_executante'];
                                            } ?>",
                            testador: "<?php echo $row_solicitacao['nome_testador']; ?>",
                            dt_solicitacao: "<? echo $row_solicitacao['dt_solicitacao']; ?>",
                            data_suporte: "<? echo $row_solicitacao['data_suporte']; ?>",
                            previsao_data_exibir: "<? echo $previsao_data_exibir; ?>",
                            dias_atraso:"<?
                            if ( $row_solicitacao['situacao']!="reprovada" and $row_solicitacao['situacao']!="solucionada" ){

                                if($row_solicitacao['previsao_geral'] <> NULL){
                                    echo $row_solicitacao['previsao_geral_dias_atraso'];
                                }

                            }
                            ?>",
                            dt_validacao: "<? echo $row_solicitacao['dt_validacao']; ?>",
                            tipo: "<?php echo $row_solicitacao['tipo']; ?>",
                            status: "<? echo "<div class='$cor_css'>"; ?><?php

                                    if ($row_solicitacao['status'] == "") {
                                        echo "&nbsp;";
                                    }

                                    if ($row_solicitacao['status_questionamento'] != "") {
                                        echo "<strong><font color=red><span class='ponto_interrogacao' id='" . $row_solicitacao['id'] . "'>?</span></font></strong> ";
                                    }

                                    if ($row_solicitacao['status'] == "pendente solicitante") {
                                        echo "PenSol";
                                    }
                                    if ($row_solicitacao['status'] == "pendente operador") {
                                        echo "PenOpe";
                                    }
                                    if ($row_solicitacao['status'] == "pendente executante") {
                                        echo "PenExe";
                                    }
                                    if ($row_solicitacao['status'] == "pendente testador") {
                                        echo "PenTes";
                                    }

                                    if ($row_solicitacao['status'] == "encaminhada para solicitante") {
                                        echo "EncSol";
                                    }
                                    if ($row_solicitacao['status'] == "encaminhada para operador") {
                                        echo "EncOpe";
                                    }
                                    if ($row_solicitacao['status'] == "encaminhada para executante") {
                                        echo "EncExe";
                                    }
                                    if ($row_solicitacao['status'] == "encaminhada para testador") {
                                        echo "EncTes";
                                    }
                                    if ($row_solicitacao['status'] == "encaminhada para analista") {
                                        echo "EncAna";
                                    }

                                    if ($row_solicitacao['status'] == "devolvida para solicitante") {
                                        echo "DevSol";
                                    }
                                    if ($row_solicitacao['status'] == "devolvida para operador") {
                                        echo "DevOpe";
                                    }
                                    if ($row_solicitacao['status'] == "devolvida para executante") {
                                        echo "DevExe";
                                    }
                                    if ($row_solicitacao['status'] == "devolvida para testador") {
                                        echo "DevTes";
                                    }

                                    ?><? echo "</div>"; ?>",
                            prioridade: "<?php echo $row_solicitacao['prioridade']; ?>",
                            situacao: "<?php
                                if ($row_solicitacao['situacao'] == "em análise") {
                                    echo "análise";
                                } else if ($row_solicitacao['situacao'] == "em orçamento") {
                                    echo "orçamento";
                                } else if ($row_solicitacao['situacao'] == "em execução") {
                                    echo "execução";
                                } else if ($row_solicitacao['situacao'] == "em testes") {
                                    echo "testes";
                                } else if ($row_solicitacao['situacao'] == "em validação") {
                                    echo "validação";
                                } else {
                                    echo $row_solicitacao['situacao'];
                                }
                            ?>",
                            visualizar: "<? echo "<a href='solicitacao_editar.php?id_solicitacao=" . $row_solicitacao['id'] . "&padrao=sim'><img src='imagens/visualizar.png' border='0' /></a>"; ?>"
                        },

                    <?php } while ($row_solicitacao = mysql_fetch_assoc($solicitacao)); ?>

                ];

                jQuery('#solicitacao').jqGrid({
                    data: dados,
                    datatype: 'local',
                    colNames: ['Núm', 'Título', 'Empresa', 'Reclamação', 'Solicitante', 'Operador', 'Executante', 'Testador', 'Criação', 'Data Suporte', 'Previsão', 'Dias Atraso', 'Validação', 'Tipo', 'Status', 'Prior.', 'Situação', ''],
                    colModel: [{
                            name: 'id',
                            index: 'id',
                            width: 25,
                            sorttype: 'integer'
                        },
                        {
                            name: 'titulo',
                            index: 'titulo'
                        },
                        {
                            name: 'empresa',
                            index: 'empresa',
                            width: 100,
                            align: 'left'
                        },
                        {
                            name: 'suporte_reclamacao_em_andamento',
                            index: 'suporte_reclamacao_em_andamento',
                            width: 50,
                            align: 'center'
                        },
                        {
                            name: 'solicitante',
                            index: 'solicitante',
                            width: 60,
                            align: 'left'
                        },
                        {
                            name: 'operador',
                            index: 'operador',
                            width: 60,
                            align: 'left'
                        },
                        {
                            name: 'executante',
                            index: 'executante',
                            width: 60,
                            align: 'left'
                        },
                        {
                            name: 'testador',
                            index: 'testador',
                            width: 60,
                            align: 'left'
                        },
                        {
                            name: 'dt_solicitacao',
                            index: 'dt_solicitacao',
                            width: 50,
                            formatter: 'date',
                            formatoptions: {
                                srcformat: "ISO8601Long",
                                newformat: "d-m-Y"
                            },
                            align: 'center'
                        },
                        {
                            name: 'data_suporte',
                            index: 'data_suporte',
                            width: 50,
                            formatter: 'date',
                            formatoptions: {
                                srcformat: "ISO8601Long",
                                newformat: "d-m-Y"
                            },
                            align: 'center'
                        },
                        {
                            name: 'previsao_data_exibir',
                            index: 'previsao_data_exibir',
                            width: 50,
                            formatter: 'date',
                            formatoptions: {
                                srcformat: "ISO8601Long",
                                newformat: "d-m-Y"
                            },
                            align: 'center'
                        },
                        {
                            name: 'dias_atraso',
                            index: 'dias_atraso',
                            width: 55,
                            align: 'center',
                            sorttype: 'integer'
                        },
                        {
                            name: 'dt_validacao',
                            index: 'dt_validacao',
                            width: 50,
                            formatter: 'date',
                            formatoptions: {
                                srcformat: "ISO8601Long",
                                newformat: "d-m-Y"
                            },
                            align: 'center'
                        },
                        {
                            name: 'tipo',
                            index: 'tipo',
                            width: 60,
                            align: 'center'
                        },
                        {
                            name: 'status',
                            index: 'status',
                            width: 40,
                            align: 'center'
                        },
                        {
                            name: 'prioridade',
                            index: 'prioridade',
                            width: 30,
                            align: 'center'
                        },
                        {
                            name: 'situacao',
                            index: 'situacao',
                            width: 45,
                            align: 'center'
                        },
                        {
                            name: 'visualizar',
                            index: 'visualizar',
                            width: 20,
                            align: 'center'
                        }
                    ],
                    rowNum: 20,
                    rowList: [2, 5, 10, 20, 30, 40, 50, 100, 999999],
                    loadComplete: function() {
                        $("option[value=999999]").text('Todos');
                    },
                    pager: '#navegacao',
                    //sortname: 'id',
                    viewrecords: true,
                    //sortorder: 'desc',
                    toppager: true, // aparecer a barra de navegação também no topo
                    //caption:"Solicitações de suporte",
                    autowidth: true,
                    height: "100%",

                    ondblClickRow: function(id) {
                        top.location.href = "solicitacao_editar.php?id_solicitacao=" + id + "&padrao=sim";
                    }
                });
            </script>
        </div>

    <? } else {  ?>
        <div class="div_solicitacao_linhas4">
            Nenhuma solicitação encontrada na filtragem atual.
        </div>
    <? } ?>
    <!-- fim - solicitações -->


    <!-- barra inferior -->
    <? if ($totalRows_solicitacao > "0") { // caso seja encontrada alguma solicitação com os filtros atuais 
    ?>
        <div class="div_solicitacao_linhas4" style="margin-top: 5px;">
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td style="text-align:left">
                        <a href="#TB_inline?height=<? echo $solicitacao_editar_tabela_height; ?>&width=<? echo $solicitacao_editar_tabela_width; ?>&inlineId=gerar_relatorio&modal=true" class="thickbox" id="botao_geral2">Gerar relatório</a>
                    </td>
                </tr>
            </table>
        </div>
    <? } ?>
    <!-- fim - barra inferior -->


    <!-- relatórios (oculto) -->
    <script>
        //função de submit
        function enviar() {
            document.getElementById('form').submit();
        }
    </script>
    <div id="gerar_relatorio" style="display: none;">
        <form action="solicitacao_relatorio.php" method="post" target="_blank" id="form" name="form">

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
                Marque os campos que irão aparecer no relatório:
                <br><br>
                <!-- campos (checklist) -->
                <fieldset style="border: 0px;">
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="33%" valign="top">
                                <input value="id" type="checkbox" name="relatorio_campos[]" checked />
                                Núm. da solicitação
                                <br>

                                <input value="titulo" type="checkbox" name="relatorio_campos[]" checked />
                                Título
                                <br>

                                <input value="dt_solicitacao" type="checkbox" name="relatorio_campos[]" checked />
                                Data da criação
                                <br>

                                <input value="protocolo_suporte" type="checkbox" name="relatorio_campos[]" />
                                Núm. controle suporte
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

                                <input value="tipo" type="checkbox" name="relatorio_campos[]" checked />
                                Tipo
                                <br>

                                <input value="status" type="checkbox" name="relatorio_campos[]" checked />
                                Status
                                <br>

                                <input value="prioridade" type="checkbox" name="relatorio_campos[]" checked />
                                Prioridade
                                <br>

                                <input value="situacao" type="checkbox" name="relatorio_campos[]" checked />
                                Situação
                            </td>
                            <td width="33%" valign="top">
                                <input value="versao" type="checkbox" name="relatorio_campos[]" />
                                Versão
                                <br>

                                <input value="geral_tipo_distribuicao" type="checkbox" name="relatorio_campos[]" />
                                Distribuição
                                <br>

                                <input value="programa" type="checkbox" name="relatorio_campos[]" />
                                Programa
                                <br>

                                <input value="subprograma" type="checkbox" name="relatorio_campos[]" />
                                Subprograma
                                <br>

                                <input value="campo" type="checkbox" name="relatorio_campos[]" />
                                Campo
                                <br>

                                <input value="data_executavel" type="checkbox" name="relatorio_campos[]" />
                                Data executável
                                <br>

                                <input value="hora_executavel" type="checkbox" name="relatorio_campos[]" />
                                Hora executável
                                <br>

                                <input value="tipo_bd" type="checkbox" name="relatorio_campos[]" />
                                Banco de dados
                                <br>

                                <input value="geral_tipo_ecf" type="checkbox" name="relatorio_campos[]" />
                                ECF
                            </td>
                            <td valign="top">
                                <input value="usuario_responsavel" type="checkbox" name="relatorio_campos[]" checked />
                                Solicitante/Usuário responsável
                                <br>

                                <input value="operador" type="checkbox" name="relatorio_campos[]" />
                                Operador
                                <br>

                                <input value="analista_orcamento" type="checkbox" name="relatorio_campos[]" />
                                Analista de orçamento
                                <br>

                                <input value="executante" type="checkbox" name="relatorio_campos[]" />
                                Executante
                                <br>

                                <input value="testador" type="checkbox" name="relatorio_campos[]" />
                                Testador
                            </td>
                        </tr>
                    </table>
                </fieldset>
                <!-- fim - campos (checklist) -->
            </div>

            <!-- rodapé -->
            <div>Obs: este relatório é baseado nos filtros utilizados na tela anterior de listagem das solicitações.</div>
            <div style="margin-top: 5px;">
                <input type="hidden" name="where" id="where" value="<? echo $where; ?>">
                <a href="#" onclick="enviar();" id="botao_geral2">Visualizar</a>
            </div>
            <!-- fim - rodapé -->

        </form>
    </div>
    <!-- fim - relatórios (oculto) -->

</body>

</html>
<?
mysql_free_result($usuario);
mysql_free_result($filtro_praca);
mysql_free_result($filtro_solicitante);
mysql_free_result($filtro_analista_orcamento);
mysql_free_result($filtro_operador);
mysql_free_result($filtro_executante);
mysql_free_result($filtro_testador);
mysql_free_result($solicitacao);
?>