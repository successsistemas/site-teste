<?
// função para converter data em portugues para ingles
function formataDataING($data){
	if ( isset($data) ) {
		$data_data = substr($data,0,10);
		$data_hora = substr($data,10,9);
		return implode("-",array_reverse(explode("-",$data_data))).$data_hora;
	}
}

// função para converter data em ingles para portugues
function formataDataPTG($data){
	if ( isset($data) ) {
		return date('d-m-Y H:i:s', strtotime($data));
	}
}
?>