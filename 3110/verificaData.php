<?php

function verificaData() {

		if ( isset($_POST['data']) and $_POST['data'] !="" ) {
				// primeira data
				$dataI= date('Y-m-d H:i:s');
				$I= strtotime($dataI );
				// fim - primeira data
		
		
				// segunda data
		
						// converter data em portugues para ingles
							$dataII_data = substr($_POST['data'],0,10);
							$dataII_hora = substr($_POST['data'],10,9);
							$dataII = implode("-",array_reverse(explode("-",$dataII_data))).$dataII_hora;
						// converter data em portugues para ingles - fim
		
				$II= strtotime($dataII);
				// fim - segunda data
		
		
				if( $II >= $I ){
					return true;
				} else {
					return false;
				}
		
		}

}
echo verificaData();

?>