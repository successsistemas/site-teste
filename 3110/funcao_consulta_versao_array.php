<?
// funcao_consulta_versao_array
function funcao_consulta_versao_array($array){

	$retorno = NULL;
	require('Connections/conexao.php');	

	if($array <> NULL){
		
		$array = explode(',', $array);
		if(count($array) > 0){
			
			foreach($array as $key => $value){
				
				mysql_select_db($database_conexao, $conexao);
				$query_consulta = "SELECT titulo FROM geral_tipo_versao WHERE IdTipoVersao = $value";
				$consulta = mysql_query($query_consulta, $conexao) or die(mysql_error());
				$row_consulta = mysql_fetch_assoc($consulta);
				$totalRows_consulta = mysql_num_rows($consulta);
				if($totalRows_consulta > 0){
					$array[$key] = $row_consulta['titulo'];	
				}
				mysql_free_result($consulta);
				
			}
			
		}
		
		$retorno = implode(', ', $array);
		$array = array();
	
	}
	
	return $retorno;

}
// fim - funcao_consulta_versao_array
?>