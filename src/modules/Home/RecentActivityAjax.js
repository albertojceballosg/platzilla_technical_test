/*[ TT11390 ] News bar - Jesus A. - Función que consulta via ajax las acvividades recientes de los usuarios */
function writesRecentActivities(){
	var  url = 'module=Home&action=HomeAjax&file=RecentActivityAjax&ajax=true';

	new Ajax.Request(
					'index.php',
					{asynchronous : false,
						cache: false,
						queue: {position: 'end', scope: 'command'},
						method: 'post',
						postBody:url,
						onComplete: function(response) {
							if ( response.responseText != '' ) {
									var recentActivity_Arr;
									//Lo parseamos para convertirlo en objeto
									recentActivity_Arr = JSON.parse(response.responseText);
							}
						}
					}
				);
}