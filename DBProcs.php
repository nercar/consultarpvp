<?php
	/**
	* Permite obtener los datos de la base de datos y retornarlos
	* en modo json o array
	*/
	try {
		date_default_timezone_set('America/Caracas');
		// Se capturan las opciones por Post
		$opcion = (isset($_POST["opcion"])) ? $_POST["opcion"] : "";
		// id para los filtros en las consultas
		$idpara = (isset($_POST["idpara"])) ? $_POST["idpara"] : '';
		$fecha  = date("DD-MM-YYYY");
		$hora   = date("hh:i");
		// Se establece la conexion con la BBDD
		$params = parse_ini_file('dist/config.ini');
		if ($params === false) {
			throw new \Exception("Error reading database configuration file");
		}
		// connect to the sql server database
		if($params['instance']!='') {
			$conStr = sprintf("sqlsrv:Server=%s\%s;",
			$params['host_sql'],
			$params['instance']);
		} else {	
			$conStr = sprintf("sqlsrv:Server=%s,%d;",		
			$params['host_sql'],		
			$params['port_sql']);
		}
		$connec = new \PDO($conStr, $params['user_sql'], $params['password_sql']);
		$moneda = $params['moneda'];
		$simpvp1 = 'Ref.$';
		$simpvp2 = 'Bs.';
		$simpvp3 = 'COP';
		$vercop  = $params['vercop'];
		switch ($opcion) {
			case 'hora_srv':
				echo json_encode('1¬' . $hora);
				break;
			case 'consultarpvp':
				$sql = "SELECT EA.codigo, EC.barra, EA.descripcion AS nombre, ED.DESCRIPCION AS dpto,
							EA.precio1 AS base, EA.impuesto, EA.moneda,
							(SELECT FACTOR FROM BDES.dbo.ESFormasPago_FactorC WHERE CODIGO = 60) AS tasa,
							(SELECT FACTOR FROM BDES.dbo.ESFormasPago_FactorC WHERE CODIGO = 61) AS cops,
							(CASE WHEN CAST(EA.fechainicio AS TIME) != '00:00:00' OR CAST(EA.fechafinal AS TIME) != '00:00:00' THEN
								(CASE WHEN GETDATE() BETWEEN EA.fechainicio AND EA.fechafinal
									THEN EA.preciooferta ELSE 0 END)
							ELSE
								(CASE WHEN CAST(GETDATE() AS DATE) 
									BETWEEN CAST(EA.fechainicio AS DATE) AND CAST(EA.fechafinal AS DATE)
									THEN EA.preciooferta ELSE 0 END)
							END) AS oferta
						FROM BDES.dbo.ESARTICULOS EA
							INNER JOIN BDES.dbo.ESCodigos EC ON EC.escodigo = EA.codigo
							INNER JOIN BDES.dbo.ESDpto ED ON ED.CODIGO = EA.departamento
						WHERE EC.barra = '$idpara'";
				// Se ejecuta la consulta en la BBDD
				$sql = $connec->query($sql);
				$datos = [];
				if($sql) {
					$row = $sql->fetch();
					if($row) {
						if($vercop==1) {
							$stypvp1 = 'font-size: 650%; font-weight: bold; letter-spacing: -5px;  line-height: 0.8em;';
							$stypvp2 = 'font-size: 450%; font-weight: bold; letter-spacing: -10px; line-height: 0.8em;';
							$stypvp3 = 'font-size: 550%; font-weight: bold; letter-spacing: -12px;  line-height: 0.8em;';
						} else {
							$stypvp1 = 'font-size: 700%; font-weight: bold; letter-spacing: -5px;  line-height: 0.8em;';
							$stypvp2 = 'font-size: 550%; font-weight: bold; letter-spacing: -10px; line-height: 0.8em;';
							$stypvp3 = 'font-size: 100%; font-weight: bold; letter-spacing: -12px;  line-height: 0.8em;';
						}
						$stysimb = 'font-size: 35%; letter-spacing: -2px;';
						$style   = "color: black; width: fit-content; text-align: center; ".
									"text-shadow: ". 
										"4px  -0   0.5px yellow, -4px -0   0.5px yellow,".
										"4px  -1px 0.5px yellow, -4px -1px 0.5px yellow,".
										"4px  -2px 0.5px yellow, -4px -2px 0.5px yellow,".
										"4px  -3px 0.5px yellow, -4px -3px 0.5px yellow,".
										"4px  -4px 0.5px yellow, -4px -4px 0.5px yellow,".
										"-4px  0   0.5px yellow,  0    0   0.5px yellow,".
										"-4px  1px 0.5px yellow,  0    1px 0.5px yellow,".
										"-4px  2px 0.5px yellow,  0    2px 0.5px yellow,".
										"-4px  3px 0.5px yellow,  0    3px 0.5px yellow,".
										"-4px  4px 0.5px yellow,  0    4px 0.5px yellow,".
										"0    -0   0.5px yellow,  4px  0   0.5px yellow,".
										"0    -1px 0.5px yellow,  4px  1px 0.5px yellow,".
										"0    -2px 0.5px yellow,  4px  2px 0.5px yellow,".
										"0    -3px 0.5px yellow,  4px  3px 0.5px yellow,".
										"0    -4px 0.5px yellow,  4px  4px 0.5px yellow;";
						if($row['moneda']==0) {
							$precio2 = round($row['base']   * ( 1 + ( $row['impuesto'] / 100 ) ), 2);
							$oferta2 = round($row['oferta'] * ( 1 + ( $row['impuesto'] / 100 ) ), 2);
							$precio1 = $precio2 / $row['tasa'];
							$oferta1 = $oferta2 / $row['tasa'];
							$precio3 = $precio2 * $row['cops'];
							$oferta3 = $oferta2 * $row['cops'];
							$pvpbsdi = round($precio2 * 1000000, 2);
							$offbsdi = round($oferta2 * 1000000, 2);
						} else {
							$precio1 = round($row['base']   * ( 1 + ( $row['impuesto'] / 100 ) ), 2);
							$oferta1 = round($row['oferta'] * ( 1 + ( $row['impuesto'] / 100 ) ), 2);
							$precio2 = $precio1 * $row['tasa'];
							$oferta2 = $oferta1 * $row['tasa'];
							$precio3 = $precio1 * $row['tasa'] * $row['cops'];
							$oferta3 = $oferta1 * $row['tasa'] * $row['cops'];
							$pvpbsdi = round($precio2 * 1000000, 2);
							$offbsdi = round($oferta2 * 1000000, 2);
						}
						$oferta  = $oferta1;
						$precio1 = '<span style="'.$stypvp1.'"><span style="font-size: 25%; letter-spacing: -2px;">'.$simpvp1 . '</span>' . number_format($precio1, 2).'</span>';
						$oferta1 = '<span style="'.$stypvp1.'"><span style="'.$style.'"><span style="font-size: 25%; letter-spacing: -2px;">'.$simpvp1 . '.</span>' . number_format($oferta1, 2) . '</span></span>';
						$precio2 = '<span style="'.$stypvp2.'"><span style="'.$stysimb.'">'.$simpvp2.'&nbsp;</span>' . number_format(intval($precio2), 0).'.<small>'.str_pad(number_format(($precio2-intval($precio2))*100, 0), 2, '0', STR_PAD_LEFT).'</small></span>';
						$precio2.= '<br><span style="'.$stypvp2.'"><span style="'.$stysimb.'">Bs.S&nbsp;</span>' . number_format(intval($pvpbsdi), 0).'.<small>'.str_pad(number_format(($pvpbsdi-intval($pvpbsdi))*100, 0), 2, '0', STR_PAD_LEFT).'</small></span>';
						$oferta2 = '<span style="'.$stypvp2.'"><span style="'.$style.'"><span style="'.$stysimb.'">'.$simpvp2.'&nbsp;</span>' . number_format(intval($oferta2), 0).'.<small>'.str_pad(number_format(($oferta2-intval($oferta2))*100, 0), 2, '0', STR_PAD_LEFT).'</small></span></span>';
						$oferta2.= '<br><span style="'.$stypvp2.'"><span style="'.$style.'"><span style="'.$stysimb.'">Bs.S&nbsp;</span>' . number_format(intval($offbsdi), 0).'.<small>'.str_pad(number_format(($offbsdi-intval($offbsdi))*100, 0), 2, '0', STR_PAD_LEFT).'</small></span></span>';
						if($vercop==1) {
							$precio3 = '<span style="'.$stypvp3.'"><span style="'.$stysimb.'">'.$simpvp3.'&nbsp;</span>' . number_format(intval($precio3), 0).'.<small>'.number_format(($precio3-intval($precio3))*100, 0).'</small></span>';
							$oferta3 = '<span style="'.$stypvp3.'"><span style="'.$style.'"><span style="'.$stysimb.'">'.$simpvp3.'&nbsp;</span>' . number_format(intval($oferta3), 0).'.<small>'.str_pad(number_format(($oferta3-intval($oferta3))*100, 0), 2, '0', STR_PAD_LEFT).'</small></span></span>';
						} else {
							$precio3 = '';
							$oferta3 = '';
						}
						$datos[] = [
							'codigo'  => $row['codigo'],
							'barra'   => $row['barra'],
							'nombre'  => $row['nombre'],
							'dpto'    => $row['dpto'],
							'precio1' => $precio1,
							'oferta1' => $oferta1,
							'precio2' => $precio2,
							'oferta2' => $oferta2,
							'precio3' => $precio3,
							'oferta3' => $oferta3,
							'oferta'  => $oferta,
						];
					}
				}
				// Se retornan los datos obtenidos
				echo json_encode($datos);
				break;
			default:
				# code...
				break;
		}
		// Se cierra la conexion
		$connec = null;
	} catch (Exception $e) {
		echo "Error : " . $e->getMessage() . "<br/>";
		die();
	}
?>
