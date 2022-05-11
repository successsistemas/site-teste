<?
function tempo_gasto($id_solicitacaoHG, $situacaoHG, $acaoHG, $id_usuarioHG, $responsabilidadeHG, $tempo_gastoHG){
    require('Connections/conexao.php');

    $parteHG = explode(":", $tempo_gastoHG); // divide o tempo gasto em partes
    $diaHG = $parteHG[0]; // pega o dia
    $horaHG = $parteHG[1]; // pega a hora
    $minutoHG = $parteHG[2]; // pega o minuto

	$total_em_minutosHG = $minutoHG + ($horaHG*60) + (($diaHG*24)*60);

    $insertSQL_HORA_GASTA  = sprintf("INSERT INTO solicitacao_tempo_gasto (id_solicitacao, data, tempo_gasto, dia, hora, minuto, id_usuario, responsabilidade, situacao, acao, total_em_minutos) 
                                VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($id_solicitacaoHG, "int"),
                       GetSQLValueString(date("Y-m-d H:i:s"), "date"),
                       GetSQLValueString($tempo_gastoHG, "text"),
                       GetSQLValueString($diaHG, "int"),
                       GetSQLValueString($horaHG, "int"),
                       GetSQLValueString($minutoHG, "int"),
                       GetSQLValueString($id_usuarioHG, "int"),
                       GetSQLValueString($responsabilidadeHG, "text"),
                       GetSQLValueString($situacaoHG, "text"),
                       GetSQLValueString($acaoHG, "text"),
                       GetSQLValueString($total_em_minutosHG, "int"));
    
    mysql_select_db($database_conexao, $conexao);
    $Result_HORA_GASTA = mysql_query($insertSQL_HORA_GASTA, $conexao) or die(mysql_error());
}
?>