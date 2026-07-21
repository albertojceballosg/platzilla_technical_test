<?php

	/* El siguiente script realiza la revision y correccion de las claves primarias de un servidor */
	
	$conex = mysql_connect('167.114.39.220:3306','root','Echscps02',true); //Aca van los datos de conexión de un user que tenga privilegios de acceso a la estructura del servidor

	if ($conex) {
	
		$query = "SELECT COUNT( * ) AS  `Filas` ,  `TABLE_SCHEMA` 
			FROM  information_schema.`TABLES` 
			WHERE TABLE_SCHEMA NOT IN ('information_schema', 'mysql', 'performance_schema')
			GROUP BY  `TABLE_SCHEMA` 
			ORDER BY  `TABLE_SCHEMA` ";
			
		$result = mysql_query($query,$conex);
		
		while ($row = mysql_fetch_array($result)) {


			$query = "SELECT t.table_schema,t.table_name,ENGINE FROM information_schema.tables t 
						INNER JOIN information_schema .COLUMNS c  
						ON t.table_schema=c.table_schema AND t.table_name=c.table_name AND t.table_schema NOT IN ('information_schema', 'mysql', 'performance_schema')
						WHERE t.table_schema = '".$row['TABLE_SCHEMA']."' 
						GROUP BY  t.table_schema,t.table_name   
						HAVING SUM(IF((column_key IN ('PRI','UNI')),1,0)) = 0;";
					
			$resultaux = mysql_query($query,$conex);
		
			if ($resultaux) {
				while ($rowaux = mysql_fetch_array($resultaux)) { //Para cada tabla se verifican las columnas que posee
					$query = "SELECT column_name FROM information_schema.`COLUMNS` WHERE table_name = '".$rowaux['table_name']."' AND TABLE_SCHEMA = '".$rowaux['table_schema']."'";
				
					$result2 = mysql_query($query,$conex);
				
					if (mysql_num_rows($result2) == 1 && strstr($rowaux['table_name'],'_seq')) { //Tabla de un solo campo, entonces se coloca este campo como clave primaria
						$row2 = mysql_fetch_array($result2);
						$query = "ALTER TABLE ".$rowaux['table_schema'].".".$row['table_name']." ADD PRIMARY KEY (`".$row2['column_name']."`) ;";
					} else {
						$query = "ALTER TABLE ".$rowaux['table_schema'].".".$rowaux['table_name']." ADD fieldpk INT UNSIGNED NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (fieldpk);";
					}
					echo $query."<br/>\n";
				}
			
				//if (!mysql_query($query,$conex))
				//	echo mysql_error($conex);
			}
		}
	} else {
		echo "No se pudo conectar al cluster";
	}
?>