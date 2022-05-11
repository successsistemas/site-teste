function valida_data(a){
	var data 		= a;
	var dia 		= data.substr(0,2);
	var barra1		= data.substr(2,1);
	var mes 		= data.substr(3,2);
	var barra2		= data.substr(5,1);
	var ano 		= data.substr(6,4);
	
	if(data.length!=10||barra1!="-"||barra2!="-"||isNaN(dia)||isNaN(mes)||isNaN(ano)||dia>31||mes>12){ return data_existe = 1; }
	else if((mes==4||mes==6||mes==9||mes==11) && dia==31){ return data_existe = 1; }
	else if(mes==2  &&  (dia>29||(dia==29 && ano%4!=0))){ return data_existe = 1; }
	else if(ano < 1900){ return data_existe = 1; }
	else { return data_existe = 0 }
}

function compara_data(a,b){
	var data1 		= a;
	var dia1 		= data1.substr(0,2);
	var mes1 		= data1.substr(3,2);
	var ano1 		= data1.substr(6,4);
	var data1 = ano1+'-'+mes1+'-'+dia1;
	
	var data2 		= b;
	var dia2 		= data2.substr(0,2);
	var mes2 		= data2.substr(3,2);
	var ano2 		= data2.substr(6,4);
	var data2 = ano2+'-'+mes2+'-'+dia2;
	
	if(data1 > data2){ return data_comparacao = 1; }
	else if(data2 > data1){ return data_comparacao = 2; }
	else { return data_comparacao = 0; }
}

function valida_hora(a){
	var horario 	= a;
	var hora 		= horario.substr(0,2);
	var ponto1		= horario.substr(2,1);
	var minuto 		= horario.substr(3,2);
	if(horario.length!=5||ponto1!=":"||isNaN(hora)||isNaN(minuto)||hora>23||minuto>59){ return hora_existe = 1; }
	else { return hora_existe = 0 }
}