<?php
	$params = parse_ini_file('dist/config.ini');
	if ($params === false) {
		$titulo = '';
	}
	$titulo = $params['title'];
	$version = $params['version'];
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<title><?php echo $titulo; ?></title>
		<!-- Tell the browser to be responsive to screen width -->
		<meta name="viewport" content="width=device-width">
		<meta name="mobile-web-app-capable" content="yes">
		
		<!-- Icon Favicon -->
		<link rel="shortcut icon" href="dist/img/favicon.png">
		<link rel="icon" sizes="192x192" href="dist/img/favicon.png">
		
		<!-- Theme style -->
		<link rel="stylesheet" href="dist/css/adminlte.css">

		<style>
			body {
				background: url(dist/img/fondo.jpeg);
				background-repeat: no-repeat;
				background-attachment: fixed;
				background-position: top;
				background-size: 100vw 100vh;
			}

			.thickOutlined {
				color: white;
				width: fit-content;
				text-align: center;
				text-shadow: -2px -2px 0 #000,
							0px -2px 0 #000,
							2px -2px 0 #000,
							-2px  2px 0 #000,
							0px  2px 0 #000,
							2px  2px 0 #000;
			}
			
			#nombre { font-size: 250%; line-height: 0.8em; font-weight: bold; }
			#alerta { font-size: 350%; line-height: 0.8em; }
		</style>
	</head>
	<body onload="$('#barra').focus()" onclick="$('#barra').focus()">
		<center><span class="badge badge-info m-0"> <?php echo $version; ?> </span></center>
		<div id="ppal" class="p-0 pt-4 m-0" onclick="$('#barra').focus()">
			<input type="hidden" name="hora_act" id="hora_act" value="">
			<div class="d-flex justify-content-center align-content-center" onclick="$('#barra').focus()">
				<div style="width: 40vw;"></div>
				<div style="height: 88vh; width: 60vw;">
					<center><input type="text" id="barra" class="text-center bg-transparent"  onblur="$('#barra').focus()"></center>
					<br>
					<br>
					<br>
					<div class="w-100 text-right">
						<div id="nombre" class="bg-transparent ml-5 mr-5 text-center"></div>
					</div>
					<div class="text-right" style="position: absolute; top: 35vh; right: 3vw;">
						<div id="precio1" class="bg-transparent mr-5"></div>
						<div id="precio2" class="bg-transparent mr-5"></div>
						<div id="precio3" class="bg-transparent mr-5"></div>
						<center><span id="alerta" class="thickOutlined text-danger bg-danger m-5 p-3 d-none rounded border border-dark elevation-4">!!Código No Existe!!</span></center>
					</div>
				</div>
			</div>
		</div>
	</body>

	<!-- jQuery -->
	<script src="dist/js/jquery.min.js"></script>
	<!-- jQuery UI 1.12.1 -->
	<script src="dist/js/jquery-ui.min.js"></script>
	<!-- Bootstrap 4 -->
	<script src="dist/js/bootstrap.bundle.min.js"></script>
	<!-- AdminLTE App -->
	<script src="dist/js/adminlte.min.js"></script>
	<!-- JS propias app -->
	<script src="dist/js/app.js"></script>

	<script>
		var tiempo = '';

		$("body").on("keydown", "input", function(e) {
			// si presiono el enter
			if (e.keyCode == 13) {
				if($('#barra').val()!='') {
					$('#alerta').addClass('d-none');
					$('#precio1').removeClass('d-none');
					$('#precio2').removeClass('d-none');
					$('#precio3').removeClass('d-none');
					clearTimeout(tiempo);
					$.ajax({
						data: {
							opcion: "consultarpvp",
							idpara: $('#barra').val()
						},
						type: "POST",
						dataType: "json",
						url: "DBProcs.php",
						success: function (data) {
							if(data.length > 0) {
								$('#barra').val('');
								$('#nombre').html(data[0]['nombre']);
								if(data[0]['oferta']!=0) {
									$('#precio1').html(data[0]['oferta1']);
									$('#precio2').html(data[0]['oferta2']);
									$('#precio3').html(data[0]['oferta3']);
								} else {
									$('#precio1').html(data[0]['precio1']);
									$('#precio2').html(data[0]['precio2']);
									$('#precio3').html(data[0]['precio3']);
								}
								tiempo = setTimeout("inicializar()", 15000);
							} else {
								$('#precio1').addClass('d-none');
								$('#precio2').addClass('d-none');
								$('#precio3').addClass('d-none');
								$('#alerta').removeClass('d-none');
								inicializar();
								setTimeout(function() {
									$('#alerta').addClass('d-none');
									$('#precio1').removeClass('d-none');
									$('#precio2').removeClass('d-none');
									$('#precio3').removeClass('d-none');
								}, 15000);
							}
						}
					});
				}
				return false;
			}
		});

		function inicializar() {
			$('#barra').val('');
			$('#nombre').html('&nbsp;');
			$('#precio1').html('&nbsp;');
			$('#precio2').html('&nbsp;');
			$('#precio3').html('&nbsp;');
		}
	</script>	
</html>