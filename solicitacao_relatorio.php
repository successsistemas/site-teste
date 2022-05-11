<?php session_start(""); ?>
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


// solicitações
$where = "-1";
if (isset($_POST['where'])) {
	$where = stripslashes($_POST["where"]); // stripslashes: serve para tirar a "barra invertida" que o servidor insere no POST como segurança
}

//

$join = NULL;
$solicitacao_situacao_dt_conclusao_view_posicao = strpos($where, 'solicitacao_situacao_dt_conclusao_view');
if($solicitacao_situacao_dt_conclusao_view_posicao > 0){

    $join .= " 
    INNER JOIN 
        solicitacao_situacao_dt_conclusao_view ON solicitacao_situacao_dt_conclusao_view.id_solicitacao = solicitacao.id 
     ";

}

mysql_select_db($database_conexao, $conexao);
$query_solicitacao = "
SELECT 
	solicitacao.*, 
	(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_usuario_responsavel) as usuario_responsavel, 
	(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_operador) as nome_operador, 
	(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_executante) as nome_executante, 
	(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_analista_orcamento) as nome_analista_orcamento, 
	(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_testador) as nome_testador 
FROM 
	solicitacao 
$join 
$where 
GROUP BY
    solicitacao.id 
ORDER BY 
    solicitacao.previsao_geral ASC
";
$solicitacao = mysql_query($query_solicitacao, $conexao) or die(mysql_error());
$row_solicitacao = mysql_fetch_assoc($solicitacao);
$totalRows_solicitacao = mysql_num_rows($solicitacao);
// fim - solicitações

?>
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title></title>
	<style>
		td {
			font-family: "Courier New", Courier, monospace;
			font-size: 12px;
			text-align: left;
		}

		.linha1 {
			background-color: #EEEEEE;
		}

		.linha2 {
			background-color: #FFFFFF;
		}

		.div_td {
			padding-bottom: 2px;
			padding-top: 2px;
			padding-left: 15px;
			padding-right: 5px;
		}
	</style>
</head>

<body>

	<div>
	</div>

	<table width="100%" border="0" cellpadding="0" cellspacing="0">

		<!-- cabeçalho -->
		<tr>
			<? $contador_relatorio_campos = 0; ?>
			<? foreach ($_POST["relatorio_campos"] as $relatorio_campos) { // lista os campos selecionados para o relatório 
			?>
				<? $contador_relatorio_campos = $contador_relatorio_campos + 1; // contador de campos selecionados  
				?>
				<td>
					<div class="div_td">
						<?
						if ($relatorio_campos == "id") {
							echo "Número";
						}
						if ($relatorio_campos == "titulo") {
							echo "Título";
						}
						if ($relatorio_campos == "dt_solicitacao") {
							echo "Data da criação";
						}
						if ($relatorio_campos == "protocolo_suporte") {
							echo "Núm controle suporte";
						}
						if ($relatorio_campos == "empresa") {
							echo "Empresa";
						}
						if ($relatorio_campos == "contrato") {
							echo "Núm contrato";
						}
						if ($relatorio_campos == "praca") {
							echo "Praça";
						}
						if ($relatorio_campos == "tipo") {
							echo "Tipo";
						}
						if ($relatorio_campos == "status") {
							echo "Status";
						}
						if ($relatorio_campos == "prioridade") {
							echo "Prioridade";
						}
						if ($relatorio_campos == "situacao") {
							echo "Situação";
						}
						if ($relatorio_campos == "versao") {
							echo "Versão";
						}
						if ($relatorio_campos == "geral_tipo_distribuicao") {
							echo "Distribuição";
						}
						if ($relatorio_campos == "programa") {
							echo "Programa";
						}
						if ($relatorio_campos == "subprograma") {
							echo "Subprograma";
						}
						if ($relatorio_campos == "campo") {
							echo "Campo";
						}
						if ($relatorio_campos == "data_executavel") {
							echo "Data executável";
						}
						if ($relatorio_campos == "hora_executavel") {
							echo "Hora executável";
						}
						if ($relatorio_campos == "tipo_bd") {
							echo "Banco de dados";
						}
						if ($relatorio_campos == "geral_tipo_ecf") {
							echo "ECF";
						}
						if ($relatorio_campos == "usuario_responsavel") {
							echo "Solicitante";
						}
						if ($relatorio_campos == "nome_operador") {
							echo "Operador";
						}
						if ($relatorio_campos == "nome_analista_orcamento") {
							echo "Analista de Orc.";
						}
						if ($relatorio_campos == "nome_executante") {
							echo "Executante";
						}
						if ($relatorio_campos == "nome_testador") {
							echo "Testador";
						}
						?>
					</div>
				</td>
			<? } // fim - lista os campos selecionados para o relatório  
			?>
		</tr>

		<tr>
			<td colspan="<? echo $contador_relatorio_campos; ?>">
				<div style=" padding:2px; margin-top: 5px; margin-bottom: 5px;; background-color:#CCCCCC; ">
				</div>
			</td>
		</tr>
		<!-- fim - cabeçalho -->

		<!-- registros -->
		<? $contador = 0; ?>
		<?php do { ?>
			<? $contador = $contador + 1; ?>
			<tr class="<? if (($contador % 2) == 0) {
							echo "linha1";
						} else {
							echo "linha2";
						} ?>">

				<? $contador_relatorio_campos = 0;
				foreach ($_POST["relatorio_campos"] as $relatorio_campos) { // lista os campos selecionados para o relatório 
					$contador_relatorio_campos = $contador_relatorio_campos + 1; // contador de campos selecionados 
				?>

					<td>
						<div class="div_td">
							<?
							$campo = $row_solicitacao[$relatorio_campos];

							if ($relatorio_campos == "dt_solicitacao") {
								$campo = date('d-m-Y', strtotime($campo));
							}
							if ($relatorio_campos == "data_executavel") {
								$campo = date('d-m-Y', strtotime($campo));
							}

							echo $campo;
							?>
						</div>
					</td>

				<? } // fim - lista os campos selecionados para o relatório  
				?>
			</tr>
		<?php } while ($row_solicitacao = mysql_fetch_assoc($solicitacao)); ?>
		<!-- fim - registros -->

	</table>
</body>

</html>
<?
mysql_free_result($usuario);

mysql_free_result($solicitacao);

mysql_close($conexao);
?>