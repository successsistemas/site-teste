<?
$id = $_POST["id"];
if( $id == "Selecione algo"){
	/* Se o valor tiver vazio retona vazio */
	echo "Nada encontrado";
}else{
	/* Se tiver o valor então faz checagem no
	   banco ou outra como abaixo */
	switch($id){
		case "a";
			echo "Abacate<br />";
			break;
		case "b";
			echo "Banana<br/>Biribiri<br /.;";
			break;
		case "c";
			echo "Caqui<br />Caju<br />carambola<br />";
			break;
		case "teste";
			echo "opa viu como funciona até chegar na &uacute;ltima letra 'e'
				  ele não apresentou o resultado.";
			break;
	}
}
?>