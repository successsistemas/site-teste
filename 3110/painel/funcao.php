<?
// função para realizar upload de arquivo
function funcao_upload($caminho_arquivo, $arquivos){
	
	// 1 ou mais arquivos
	$contador_arquivo = 0;
	$retorno = NULL;
	
	foreach ($arquivos['name'] as $arqName) {

		// $arqName: O nome original do arquivo no computador do usuário
		$arqType = $arquivos['type'][$contador_arquivo]; // O tipo mime do arquivo. Um exemplo pode ser "image/gif"
		$arqSize = $arquivos['size'][$contador_arquivo]; // O tamanho, em bytes, do arquivo
		$arqTemp = $arquivos['tmp_name'][$contador_arquivo]; // O nome temporário do arquivo, como foi guardado no servidor
		$arqError = $arquivos['error'][$contador_arquivo]; // O código de erro associado a este upload de arquivo
		
		if($arqError == 0){
			
			$nome = preg_replace("/[^0-9a-z\.,;\-_\(\)\[\]\s]/i", "_", pathinfo($arqName, PATHINFO_FILENAME));
			$extensao = explode('.', $arqName); // Pega a extensão do arquivo enviado
			$extensao = end($extensao); // Pega a extensão do arquivo enviado
			$extensao = strtolower($extensao); // Pega a extensão do arquivo enviado
			
			// nome do arquivo (insere um incremento no final do nome)
			$incremento = ''; //start with no suffix
			while(file_exists($caminho_arquivo.$nome . $incremento . '.' . $extensao)) {
				$incremento++;
			}
			$nome = $nome . $incremento . '.' . $extensao;
			// fim - nome do arquivo (insere um incremento no final do nome)
					
			$upload = move_uploaded_file($arqTemp, $caminho_arquivo . $nome);
			
			$retorno[$contador_arquivo] = array('upload_retorno' => 1, "upload_nome" => $nome);

		} else {
			
			$retorno[$contador_arquivo] = array('upload_retorno' => 0, "upload_nome" => $arqName);
			
		}
	
		$contador_arquivo++;
		
	}
	// fim - 1 ou mais arquivos
	
	return $retorno;
	
}
// fim - função para realizar upload de arquivo

// function data_por_extenso
function data_por_extenso($data) {

	$dia = substr ($data, 8, 2);
	$mes = substr ($data, 5, 2);
	$ano = substr ( $data, 0, 4 );
	
	switch ($mes) {
		case "01": $mes = "Janeiro"; break;
		case "02": $mes = "Fevereiro"; break;
		case "03": $mes = "Março"; break;
		case "04": $mes = "Abril"; break;
		case "05": $mes = "Maio"; break;
		case "06": $mes = "Junho"; break;
		case "07": $mes = "Julho"; break;
		case "08": $mes = "Agosto"; break;
		case "09": $mes = "Setembro"; break;
		case "10": $mes = "Outubro"; break;
		case "11": $mes = "Novembro"; break;
		case "12": $mes = "Dezembro"; break;
	 }
	
	echo $dia." de ".$mes." de ".$ano;
}
// fim - function data_por_extenso

?>