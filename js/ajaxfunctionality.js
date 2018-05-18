var xmlHttp = createXmlHttpRequestObject(),
xmlResponse = "",
ajaxUrl = "",
inputId="";
$(document).ready(function(){
});


function createXmlHttpRequestObject(){
	var xmlHttp;
	
	if(window.ActiveXObject){
		try{
			xmlHttp = new ActiveObject("Microsoft.XMLHTTP");
		}
		catch(e){
			xmlHttp = false;
		}	
		
	}
	else{
		try{
			xmlHttp = new XMLHttpRequest();
		}
		catch(e){
			xmlHttp = false;
		}	
	}
	
	if(!xmlHttp)alert('Not Created');
	else return xmlHttp;

}

function process(ajaxResult){
	if(xmlHttp.readyState == 4 || xmlHttp.readyState == 0){
		ajaxUrl = ajaxResult.attr("data-url");
		inputId = ajaxResult.attr("data-ref");
		input = encodeURIComponent(document.getElementById(inputId).value);
		xmlHttp.open("GET",ajaxUrl+input,true);
		xmlHttp.onreadystatechange = handleServerResponse;
		xmlHttp.send(null);
	}
	//else alert('DB Can Not Be Accessed');

}

function handleServerResponse(){
	if(xmlHttp.readyState==4){
		if(xmlHttp.status==200){
			xmlResponse = xmlHttp.responseXML;
			xmlDocumentElement = xmlResponse.documentElement;
			var message = xmlDocumentElement.innerHTML;
			xmlResponse = message;
			$('.ajaxResult[data-ref="'+inputId+'"]').html(xmlResponse);
			return message;
		}
	}
}
