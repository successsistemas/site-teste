// function validaCPF()
function validaCPF(value){
	
	var cpf_cnpj_campo = $("input[name=cpf_cnpj]");
	value = jQuery.trim(value);
	
	value = value.replace('.','');
	value = value.replace('.','');
	cpf = value.replace('-','');
	
	while(cpf.length < 11) cpf = "0"+ cpf;
	var expReg = /^0+$|^1+$|^2+$|^3+$|^4+$|^5+$|^6+$|^7+$|^8+$|^9+$/;
	var a = [];
	var b =0;
	var c = 11;
	for (i=0; i<11; i++){
		a[i] = cpf.charAt(i);
		if (i < 9) b += (a[i] * --c);
	}
		if ((x = b % 11) < 2) { a[9] = 0; } else { a[9] = 11-x; }          
	b = 0;
	c = 11;
	for (y=0; y<10; y++) b += (a[y] * c--);
	if ((x = b % 11) < 2) { a[10] = 0; } else { a[10] = 11-x; }
	
	var retorno = true;
	if ((cpf.charAt(9) != a[9]) || (cpf.charAt(10) != a[10]) || cpf.match(expReg)){ retorno = false; }
	
	return retorno;
		
}
// fim - function validaCPF()


// function validaCNPJ()
function validaCNPJ(cnpj){
	
	var cpf_cnpj_campo = $("input[name=cpf_cnpj]");
	cnpj = jQuery.trim(cnpj);
	
	cnpj = cnpj.replace('.','');
	cnpj = cnpj.replace('.','');
	cnpj = cnpj.replace('-','');
	cnpj = cnpj.replace('/','');

	var a = [];
	var b = new Number;
	var c = [6,5,4,3,2,9,8,7,6,5,4,3,2];
	for(i=0; i<12; i++){
	  a[i] = cnpj.charAt(i);
	  b += a[i] * c[i+1];
	}
	if((x = b % 11) < 2){
	  a[12] = 0
	}else{
	  a[12] = 11-x
	}
	b = 0;
	for(y=0; y<13; y++){
	  b += (a[y] * c[y]);
	}
	if((x = b % 11) < 2){
	  a[13] = 0;
	}else{
	  a[13] = 11-x;
	}
	
	var retorno = true;
	if((cnpj.charAt(12) != a[12]) || cnpj == '00000000000000' || (cnpj.charAt(13) != a[13])){ retorno = false; }

	return retorno;
	
}
// fim - function validaCNPJ()