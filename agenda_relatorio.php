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


// agenda
$where = "-1";
if (isset($_POST['where'])) {
	$where = stripslashes($_POST["where"]); // stripslashes: serve para tirar a "barra invertida" que o servidor insere no POST como segurança
}

mysql_select_db($database_conexao, $conexao);
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
	$where 

ORDER BY 
    agenda.data_inicio ASC, agenda.data ASC
";
$agenda = mysql_query($query_agenda, $conexao) or die(mysql_error());
$row_agenda = mysql_fetch_assoc($agenda);
$totalRows_agenda = mysql_num_rows($agenda);
// fim - agenda
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
							echo "Núm.";
						}
						if ($relatorio_campos == "data_modulo") {
							echo "Emissão";
						}
						if ($relatorio_campos == "data_inicio") {
							echo "Previsão";
						}
						if ($relatorio_campos == "hora_inicio") {
							echo "Início";
						}
						if ($relatorio_campos == "data") {
							echo "Fim";
						}
						if ($relatorio_campos == "usuarios_nome") {
							echo "Responsável";
						}
						if ($relatorio_campos == "empresa") {
							echo "Empresa";
						}
						if ($relatorio_campos == "descricao") {
							echo "Descrição";
						}
						if ($relatorio_campos == "modulo") {
							echo "Módulo";
						}
						if ($relatorio_campos == "prioridade_label") {
							echo "Prioridade";
						}
						if ($relatorio_campos == "situacao_label") {
							echo "Situação";
						}
						if ($relatorio_campos == "praca") {
							echo "Praça";
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
							$campo = $row_agenda[$relatorio_campos];

							if ($relatorio_campos == "id") {
								if ($row_agenda['suporte_id'] != NULL) {
									$campo = $row_agenda['suporte_id'];
								} else if ($row_agenda['prospeccao_id'] != NULL) {
									$campo = $row_agenda['prospeccao_id'];
								} else if ($row_agenda['venda_id'] != NULL) {
									$campo = $row_agenda['venda_id'];
								} else {
									$campo = $row_agenda['id_agenda'];
								}
							}

							if ($relatorio_campos == "data_modulo") {
								if ($row_agenda['suporte_id'] != NULL) {
									$campo = $row_agenda['suporte_data_suporte'];
								} else if ($row_agenda['prospeccao_id'] != NULL) {
									$campo = $row_agenda['prospeccao_data_prospeccao'];
								} else if ($row_agenda['venda_data_venda'] != NULL) {
									$campo = $row_agenda['venda_data_venda'];
								}
							}

							if ($relatorio_campos == "data_modulo") {
								$campo = date('d-m-Y', strtotime($campo));
							}

							if ($relatorio_campos == "data_executavel") {
								$campo = date('d-m-Y', strtotime($campo));
							}

							if ($relatorio_campos == "data_inicio") {
								$campo = date('d-m-Y', strtotime($campo));
							}

							if ($relatorio_campos == "hora_inicio") {
								$campo = date('H:i', strtotime($campo));
							}

							if ($relatorio_campos == "data") {
								$campo = date('H:i', strtotime($campo));
							}

							if ($relatorio_campos == "modulo") {
								if ($row_agenda['suporte_id'] != NULL) {
									$campo = 'Suporte';
								} else if ($row_agenda['prospeccao_id'] != NULL) {
									$campo = 'Prospecção';
								} else if ($row_agenda['venda_id'] != NULL) {
									$campo = 'Venda';
								}	
							}


							echo $campo;
							?>
						</div>
					</td>

				<? } // fim - lista os campos selecionados para o relatório  
				?>
			</tr>
		<?php } while ($row_agenda = mysql_fetch_assoc($agenda)); ?>
		<!-- fim - registros -->

	</table>
</body>

</html>
<?
mysql_free_result($usuario);

mysql_free_result($agenda);

mysql_close($conexao);
?>