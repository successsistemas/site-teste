<?
function devolucao($id_solicitacao, $situacao, $id_usuario, $devolucao_para, $devolucao_motivo, $aceitar_recusar, $data){
    require('Connections/conexao.php');

    //region - delete - solicitacao_devolucao
	mysql_select_db($database_conexao, $conexao);	
	$delete_SQL_devolucao = sprintf("
    DELETE FROM 
        solicitacao_devolucao 
    WHERE 
        id_solicitacao=%s and 
        aceitar_recusar IS NULL 
    ", 
	GetSQLValueString($id_solicitacao, "int"));
    $Result_devolucao = mysql_query($delete_SQL_devolucao, $conexao) or die(mysql_error());
    //endregion -  fim - delete - solicitacao_devolucao
    
    //region - delete - solicitacao_devolucao
    $insertSQL_devolucao  = sprintf("
    INSERT INTO solicitacao_devolucao (
        id_solicitacao, 
        situacao, 
        data, 
        id_usuario, 
        motivo, 
        devolucao_para, 
        aceitar_recusar
    ) 
    VALUES (
        %s, %s, %s, %s, %s, %s, %s 
    )",
    GetSQLValueString($id_solicitacao, "int"),
    GetSQLValueString($situacao, "text"),
    GetSQLValueString($data, "date"),
    GetSQLValueString($id_usuario, "int"),
    GetSQLValueString($devolucao_motivo, "text"),
    GetSQLValueString($devolucao_para, "text"),
    GetSQLValueString($aceitar_recusar, "text"));
    
    mysql_select_db($database_conexao, $conexao);
    $Result_devolucao = mysql_query($insertSQL_devolucao, $conexao) or die(mysql_error());
    //endregion - fim - delete - solicitacao_devolucao

}
?>