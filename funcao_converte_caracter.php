<?
function funcao_converte_caracter($palavra){
	
	$caracter_entrada = array("\'","\"");
	$caracter_saida = array("&#039;","&quot;");
	
	return str_replace($caracter_entrada, $caracter_saida, $palavra);

}
?>