var Ohtml = document.documentElement;
getSize();
function getSize(){
	var screenWidth = Ohtml.clientWidth;
	if(screenWidth <= 320){
		Ohtml.style.fontSize = '25px';  
	}else if(screenWidth >= 768){   
		Ohtml.style.fontSize = '60px';  
	}else{
		Ohtml.style.fontSize = screenWidth/(12.8) +'px';  
	}
}    