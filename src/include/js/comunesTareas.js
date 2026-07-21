/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
function eliminar(i){
   
	var tableName = document.getElementById('testingasoc');
	var prev = tableName.rows.length;
        var prev2=prev-1;
        
        document.getElementById('cantidadFilas').value = prev2;
//	document.getElementById('proTab').deleteRow(i);
	document.getElementById(i).style.display = 'none';
        
        
        document.getElementById('row'+i).style.display = 'none';
        document.getElementById('jscal_field_datefin'+i).style.display = 'none';
        document.getElementById('jscal_trigger_datefin'+i).style.display = 'none';
        document.getElementById('eliminar'+i).style.display = 'none';
        document.getElementById('jscal_trigger_dateinicio'+i).style.display = 'none';
        document.getElementById('jscal_field_dateinicio'+i).style.display = 'none';
	document.getElementById('turno'+i).style.display = 'none';
        document.getElementById(i).value="";
        
        document.getElementById('jscal_trigger_dateinicio'+i).value= '';
        document.getElementById('jscal_field_dateinicio'+i).value = '';
	iMax = tableName.rows.length;
	for(iCount=i;iCount>=1;iCount--)
	{
          
		if(document.getElementById(iCount) && document.getElementById(iCount).style.display != 'none')
		{
			iPrevRowIndex = iCount;
			break;
		}
	}
	iPrevCount = iPrevRowIndex;
	oCurRow = eval(document.getElementById(i));
        
	sTemp = oCurRow.innerHTML;
	
	if(i != 2 && iPrevCount != 1)
	{
		oPrevRow = eval(document.getElementById(iPrevCount));
			
		iPrevCount = eval(iPrevCount);
		oPrevRow.cells[0].innerHTML = '<img src="../../../themes/images/delete.gif"   border="0" onclick="eliminar('+iPrevCount+')" name="eliminar'+iPrevCount+'" id="eliminar'+iPrevCount+'" value="Eliminar">';
	}
	else if(iPrevCount == 1)
	{
		iSwapIndex = i;
		for(iCount=i;iCount<=iMax-2;iCount++)
		{
			if(document.getElementById(iCount) && document.getElementById(iCount).style.display != 'none')
			{
				iSwapIndex = iCount;
				break;
			}
		}	
		if(iSwapIndex == i)
		{
			oPrevRow = eval(document.getElementById(iPrevCount));
			iPrevCount = eval(iPrevCount);
		}
	}
// Product reordering addition ends
	document.getElementById("jscal_field_dateinicio"+i).value = "";
        document.getElementById("jscal_field_datefin"+i).value = "";
        
	document.getElementById("desarrollador"+i).value = "";
	document.getElementById("turno"+i).value = "";
	//document.getElementBy("productName"+i).value = "";
	document.getElementById('eliminar'+i).value = 1;
	
}
function eliminarExpress(i){
   
	var tableName = document.getElementById('testingasoc');
	var prev = tableName.rows.length;
	var prev2=prev-1;
	
	document.getElementById('cantidadFilas').value = prev2;
//	document.getElementById('proTab').deleteRow(i);
	document.getElementById('tipoot'+i).style.display = 'none';
	
	
	document.getElementById('row'+i).style.display = 'none';
	document.getElementById('eliminar'+i).style.display = 'none';
	document.getElementById('jscal_trigger_dateinicio'+i).style.display = 'none';
	
	document.getElementById('descri'+i).style.display = 'none';
	document.getElementById('jscal_field_dateinicio'+i).style.display = 'none';
	document.getElementById('turno'+i).style.display = 'none';
	document.getElementById(i).value="";
	
	document.getElementById('jscal_trigger_dateinicio'+i).value= '';
	document.getElementById('jscal_field_dateinicio'+i).value = '';
	iMax = tableName.rows.length;
	for(iCount=i;iCount>=1;iCount--)
	{
          
		if(document.getElementById(iCount) && document.getElementById(iCount).style.display != 'none')
		{
			iPrevRowIndex = iCount;
			break;
		}
	}
	iPrevCount = iPrevRowIndex;
	oCurRow = eval(document.getElementById(i));
        
	sTemp = oCurRow.innerHTML;
	
	if(i != 2 && iPrevCount != 1)
	{
		oPrevRow = eval(document.getElementById(iPrevCount));
			
		iPrevCount = eval(iPrevCount);
		oPrevRow.cells[0].innerHTML = '<img src="themes/images/delete.gif"   border="0" onclick="eliminar('+iPrevCount+')" name="eliminar'+iPrevCount+'" id="eliminar'+iPrevCount+'" value="Eliminar">';
	}
	else if(iPrevCount == 1)
	{
		iSwapIndex = i;
		for(iCount=i;iCount<=iMax-2;iCount++)
		{
			if(document.getElementById(iCount) && document.getElementById(iCount).style.display != 'none')
			{
				iSwapIndex = iCount;
				break;
			}
		}	
		if(iSwapIndex == i)
		{
			oPrevRow = eval(document.getElementById(iPrevCount));
			iPrevCount = eval(iPrevCount);
		}
	}
// Product reordering addition ends
	document.getElementById("jscal_field_dateinicio"+i).value = "";
       
    if (document.getElementById("descri"+i))
		document.getElementById("descri"+i).value = "";
	if (document.getElementById("desarrollador"+i))
		document.getElementById("desarrollador"+i).value = "";
	if (document.getElementById("turno"+i))
		document.getElementById("turno"+i).value = "";
	//document.getElementBy("productName"+i).value = "";
	if (document.getElementById("eliminar"+i))
		document.getElementById('eliminar'+i).value = 1;
	
}
function loadHito(innId,proyecto){	

						$("#"+innId).attr("disabled", "disabled");
						
							$.post("index.php?module=HelpDesk&action=hitoSelectAJAX&Ajax=true",{ "proyectoid": proyecto, "OL":innId }, function(data) {
							  $("#"+innId).html(data);
							  $("#"+innId).removeAttr("disabled");

							});
						
}

function agregarExpress(){
   var tableName = document.getElementById('testingasoc');
   document.getElementById('cantidadFilas').value=document.getElementById('cantidadFilas').value+1;
	var prev = tableName.rows.length;
    	var count = eval(prev)-1;//As the table has two headers, we should reduce the count
    	var row = tableName.insertRow(prev);
        var count2=count+1;
		row.id = "row"+count2;
		row.style.verticalAlign = "top";

		var colzero = row.insertCell(0);
        colzero.className='dvtCellInfo'; 
		var colone = row.insertCell(1);
        colone.className='dvtCellInfo'; 
        var coltwo = row.insertCell(2);
        coltwo.className='dvtCellInfo';
        var colthree= row.insertCell(3);
        colthree.className='dvtCellInfo';
		colthree.style.display ='none';
        var colfour= row.insertCell(4);
        colfour.className='dvtCellInfo';
		var colfive= row.insertCell(5);
        colfive.className='dvtCellInfo';
        var valore='';
        var texto='';
        var count=(document.getElementById('testingasoc').getElementsByTagName('tr').length)-1;
        var boton='<img src="../../../themes/images/delete.gif" border="0" name="eliminar'+count+'" id="eliminar'+count+'" onClick=eliminarExpress('+count+'); value="Eliminar">';
		var selector0='<select name="tipoot'+count+'" id="tipoot'+count+'" class="small">';
        for(var i=0;i<document.getElementById('tipoot1').length;i++){
            valore=document.getElementById("tipoot1").options[i].value;
            texto=document.getElementById("tipoot1").options[i].text;
            selector0+='<option value="'+valore+'">'+texto+'</option>';
        }
        selector0+='</select>';
        selector0=boton+selector0;
        colzero.innerHTML=selector0;
		
        var selector='<select name="desarrollador'+count+'" id="desarrollador'+count+'" class="small">';
        for(var i=0;i<document.getElementById('desarrollador1').length;i++){
            valore=document.getElementById("desarrollador1").options[i].value;
            texto=document.getElementById("desarrollador1").options[i].text;
            selector+='<option value="'+valore+'">'+texto+'</option>';
        }
        selector+='</select>';
        selector=boton+selector;
        colone.innerHTML=selector;
        coltwo.innerHTML='<textarea id="descri'+count+'" name="descrip'+count+'"></textarea>';
        colthree.innerHTML='<select id="turno'+count+'" name="turno'+count+'"><option value="man">Ma&ntilde;ana</option><option value="tar">Tarde<option></select>';
       
		colfour.innerHTML='<input name="dateinicio'+count+'" type="text" id="jscal_field_dateinicio'+count+'" style="border: 1px solid rgb(186, 186, 186);" tabindex=""   value="" size="11" maxlength="10" ><img id="jscal_trigger_dateinicio'+count+'" src="../../../themes/softed/images/btnL3Calendar.gif">';
      
		Calendar.setup ({

				inputField : 'jscal_field_dateinicio'+count, ifFormat : '%d-%m-%Y', showsTime : false, button : 'jscal_trigger_dateinicio'+count, singleClick : true, step : 1

			})
			
		colfive.innerHTML='<input name="datefin'+count+'" type="text" id="jscal_field_datefin'+count+'" style="border: 1px solid rgb(186, 186, 186);" tabindex=""   value="" size="11" maxlength="10" ><img id="jscal_trigger_datefin'+count+'" src="../../../themes/softed/images/btnL3Calendar.gif">';
      
		Calendar.setup ({

				inputField : 'jscal_field_datefin'+count, ifFormat : '%d-%m-%Y', showsTime : false, button : 'jscal_trigger_datefin'+count, singleClick : true, step : 1

			})
     
     
		     
       
}

function agregar(){
   var tableName = document.getElementById('testingasoc');
   document.getElementById('cantidadFilas').value=document.getElementById('cantidadFilas').value+1;
	var prev = tableName.rows.length;
    	var count = eval(prev)-1;//As the table has two headers, we should reduce the count
    	var row = tableName.insertRow(prev);
        var count2=count+1;
		row.id = "row"+count2;
		row.style.verticalAlign = "top";

	
	var colone = row.insertCell(0);
        colone.className='dvtCellInfo';
	 
        var coltwo = row.insertCell(1);
        coltwo.className='dvtCellInfo';
        var colthree= row.insertCell(2);
         colthree.className='dvtCellInfo';
         var colfour= row.insertCell(3);
         colfour.className='dvtCellInfo';
        var valore='';
        var texto='';
        var count=(document.getElementById('testingasoc').getElementsByTagName('tr').length)-1;
        var boton='<img src="../../../themes/images/delete.gif" border="0" name="eliminar'+count+'" id="eliminar'+count+'" onClick=eliminar('+count+'); value="Eliminar">';
        var selector='<select name="desarrollador'+count+'" id="'+count+'">';
        for(var i=0;i<document.getElementById('1').length;i++){
            valore=document.getElementById("1").options[i].value;
            texto=document.getElementById("1").options[i].text;
            selector+='<option value="'+valore+'">'+texto+'</option>';
        }
        selector+='</select>';
        selector=boton+selector;
        colone.innerHTML=selector;
        coltwo.innerHTML='<select id="turno'+count+'" name="turno'+count+'"><option value="man">Ma&ntilde;ana</option><option value="tar">Tarde<option></select>';
       
      colthree.innerHTML='<input name="dateinicio'+count+'" type="text" id="jscal_field_dateinicio'+count+'" style="border: 1px solid rgb(186, 186, 186);" tabindex=""   value="" size="11" maxlength="10" ><img id="jscal_trigger_dateinicio'+count+'" src="../../../themes/softed/images/btnL3Calendar.gif">';
      
      Calendar.setup ({

				inputField : 'jscal_field_dateinicio'+count, ifFormat : '%d-%m-%Y', showsTime : false, button : 'jscal_trigger_dateinicio'+count, singleClick : true, step : 1

			})
     colfour.innerHTML='<input name="dateinicio'+count+'" type="text" id="jscal_field_datefin'+count+'" style="border: 1px solid rgb(186, 186, 186);" tabindex=""   value="" size="11" maxlength="10" ><img id="jscal_trigger_datefin'+count+'" src="../../../themes/softed/images/btnL3Calendar.gif">';
      
      Calendar.setup ({

				inputField : 'jscal_field_datefin'+count, ifFormat : '%d-%m-%Y', showsTime : false, button : 'jscal_trigger_datefin'+count, singleClick : true, step : 1

			})                    

		     
       
}

