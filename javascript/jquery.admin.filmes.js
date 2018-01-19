$(function(){
	var registro_cargado = 0, open_table = "", desde = moment.utc(), imdb = [], detenido = false, maximo = 0, procesadas = 0, progressbar = $("#progressbar"), tablas = $('.datatable'), datatable = null, select = {
		peliculas: $("#listado-buscar")
	}, inputs = {
		avanzar: $("#id-avanzar-pelicula")
	}, div = {
		peliculas: $("#contenedor-actualizaciones")
	}, button = {
		buscar: $(".buscar-peliculas"),
		peliculas: $(".accion-peliculas"),
		actores: $(".accion-actores"),
		avanzar: $("#button-avanzar-pelicula"),
		anterior: $("#button-avanzar-anterior"),
		siguiente: $("#button-avanzar-siguiente"),
		ultimo: $("#button-avanzar-ultimo"),
		nuevo: $("#button-avanzar-nuevo"),
		localizaciones: $("#accion-localizaciones")
	}, progressLabel = $(".progress-label"), times = [], th = {
		media: $("#th-media"),
		hasta: $("#th-hasta"),
		current: $("#th-current")
	}, span = {
		encontradas: $("#span_encontradas"),
		total: $("#span_total")
	};

	var query = location.search.substring(1);
	var result = {};
	var ultimo_registro = 0;
	var siguiente = 0;

	String.prototype.ucfirst = function(){
		return this.charAt(0).toUpperCase() + this.slice(1);
	};

	function ActualizarCampoTablaFilme(input){
		ActualizarCampoFilme(input.attr("name"), input.val());
	}

	function ActualizarFlagTablaFilme(input){
		ActualizarCampoFilme(input.attr("name"), (input.is(":checked")?1:0));
	}

	function ActualizarCampoFilme(campo, valor){
		if(!registro_cargado){
			return false;
		}
		$.ajax({
			type: "POST",
			url: "../serverside/admin_actualizar_filme.php",
			data: {
				id_filme: registro_cargado,
				campo: campo,
				valor: valor
			},
			async: true,
			beforeSend: function(){
				$.block();
			},
			complete: function(){
				$.unblockUI();
			},
			contentType: "application/x-www-form-urlencoded",
			dataType: "html",
			success: function(datos){
				if(!empty(datos)){
					$.alert(datos);
				}
			}
		});
	}

	function ActualizarCampoTablaActor(input){
		ActualizarCampoActor(input.attr("name"), input.val());
	}

	function ActualizarCampoActor(campo, valor){
		if(!registro_cargado){
			return false;
		}
		$.ajax({
			type: "POST",
			url: "../serverside/operaciones_actores.php",
			data: {
				action: 'ModificarCampo',
				actor: registro_cargado,
				campo: campo,
				valor: valor
			},
			async: true,
			beforeSend: function(){
				$.block();
			},
			complete: function(){
				$.unblockUI();
			},
			contentType: "application/x-www-form-urlencoded",
			dataType: "json",
			success: function(json){
				if(json.error){
					$.alert(json.error);
				}
			}
		});
	}

	function Average(){
		var sum = times.reduce(function(a, b){
			return a + b;
		});
		return sum / times.length;
	}

	function DevolverTotalActoresActualizar(){
		return $.ajax({
			type: 'POST',
			url: "../serverside/operaciones_admin.php",
			data: {
				action: "DevolverTotalActoresActualizar"
			},
			async: true,
			contentType: "application/x-www-form-urlencoded",
			dataType: "json"
		});
	}

	function DevolverActoresActualizar(){
		$.ajax({
			type: 'POST',
			url: "../serverside/operaciones_admin.php",
			data: {
				action: "DevolverActoresActualizar"
			},
			async: true,
			contentType: "application/x-www-form-urlencoded",
			dataType: "json",
			success: function(json){
				if(json.error){
					progressLabel.text(json.error);
				}
				else if(json.length){
					imdb = json;
					span.encontradas.text(json.length);
					ActualizarActores();
				}
				else{
					progressLabel.text('error desconocido');
				}
			}
		});
	}

	function ActualizarActores(){
		if(!imdb || !imdb.length){
			DevolverActoresActualizar();
			return false;
		}
		if(detenido){
			if(button.actores.hasClass("deteniendo")){
				detenido = true;
				button.actores.text("Comenzar");
				button.actores.toggleClass("comenzar deteniendo");
			}
			return false;
		}
		$.ajax({
			type: 'POST',
			url: "../serverside/admin_actualizar_actor.php",
			data: {
				imdb: imdb.shift()
			},
			async: true,
			error: function(){
				location.reload();
			},
			contentType: "application/x-www-form-urlencoded",
			dataType: "json",
			success: function(json){
				if(json.error){
					progressLabel.text(json.error);
				}
				else{
					ActualizarActores();
					procesadas++;
					progressbar.progressbar("option", "value", procesadas);
					times.push(json.segundos);
					var v = "<tr><td>" + json.tiempo + "<br />" + moment.utc(moment.utc().diff(desde)).format("HH:mm:ss") + "</td><td>" + str_pad(procesadas, 4, 0, 'STR_PAD_LEFT') + "</td><td><a href='index.php?page=actores&id=" + json.id + "' target='_blank'>" + json.nombre + "</a></td><td>" + json.mensaje + "</td></tr>";
					$("#log").prepend(v);
					var segundos = Average();
					th.media.text(segundos);
					var quedarian = maximo - procesadas;
					var faltantes = Math.ceil(segundos * quedarian);
					var until = moment().add(faltantes, 'seconds');
					th.hasta.text(until.format("DD/MM HH:mm:ss"));
					th.current.text(procesadas);
					var pos = document.title.indexOf("|");
					var ok = document.title.substring(pos);
					document.title = progressLabel.text() + ' ' + until.from(moment()) + ' ' + ok;
				}
			}
		});
	}

	function ActualizarLocalizaciones(){
		if(procesadas >= maximo){
			return false;
		}
		var caption = tablas.find("caption");
		$.ajax({
			type: 'POST',
			url: "../serverside/operaciones_localizaciones.php",
			data: {
				action: 'ActualizarLocalizacionesPendientes'
			},
			async: true,
			error: function(){
				location.reload();
			},
			contentType: "application/x-www-form-urlencoded",
			dataType: "json",
			success: function(json){
				if(json.direcciones.length){
					if(json.error){
						var tiempo = moment().startOf('day').add(10, 'seconds');
						caption.text(json.error + ' ' + tiempo.format("mm:ss"));
						var contador = null;
						contador = setInterval(function(){
							tiempo.subtract(1, 'second');
							var estimado = parseInt(tiempo.format("s")) || 0;
							caption.text(json.error + ' ' + tiempo.format("mm:ss"));
							if(estimado === 0){
								clearInterval(contador);
							}
						}, 1000);
						setTimeout(function(){
							ActualizarLocalizaciones();
						}, 10000);
					}
					datatable.rows.add(json.direcciones).draw();
					procesadas += json.direcciones.length;
					procesadas += parseInt(json.fallidos) || 0;
					var pos = document.title.indexOf("|");
					var ok = document.title.substring(pos);
					document.title = procesadas + ' / ' + maximo + ' ' + desde.fromNow() + ' ' + ok;
				}
				else if(json.error){
					caption.text(json.error);
				}
			}
		});
	}

	function DevolverTotalFilmesActualizar(){
		return $.ajax({
			type: 'POST',
			url: "../serverside/operaciones_admin.php",
			data: {
				action: "DevolverTotalFilmesActualizar",
				listado: select.peliculas.val()
			},
			async: true,
			contentType: "application/x-www-form-urlencoded",
			dataType: "json"
		});
	}

	function DevolverTotalLocalizacionesActualizar(){
		return $.ajax({
			type: 'POST',
			url: "../serverside/operaciones_localizaciones.php",
			data: {
				action: "DevolverTotalLocalizacionesPendientes"
			},
			async: true,
			contentType: "application/x-www-form-urlencoded",
			dataType: "json"
		});
	}

	function DevolverFilmesActualizar(){
		$.ajax({
			type: 'POST',
			url: "../serverside/operaciones_admin.php",
			data: {
				action: "DevolverFilmesActualizar",
				listado: select.peliculas.val()
			},
			async: true,
			contentType: "application/x-www-form-urlencoded",
			dataType: "json",
			success: function(json){
				if(json.error){
					progressLabel.text(json.error);
				}
				else if(json.length){
					imdb = json;
					span.encontradas.text(json.length);
					ActualizarPeliculas();
				}
				else{
					progressLabel.text('error desconocido');
				}
			}
		});
	}

	function EliminarFilaRegistro(input){
		if(!input.length){
			return false;
		}
		var data = input.data();
		$.ajax({
			type: "POST",
			url: "../serverside/admin_eliminar_registro.php",
			data: {
				tabla: data.target,
				id: data.id
			},
			async: true,
			contentType: "application/x-www-form-urlencoded",
			dataType: "json",
			success: function(json){
				if(json.error){
					$.alert(json.error);
				}
				else{
					input.closest("tr").remove();
				}
			}
		});
	}

	function DevolverUltimoRegistro(pagina){
		return $.ajax({
			type: "POST",
			url: "../serverside/operaciones_admin.php",
			data: {
				action: 'DevolverUltimoRegistro',
				pagina: pagina
			},
			async: true,
			contentType: "application/x-www-form-urlencoded",
			dataType: "json"
		});
	}

	function ActualizarPeliculas(){
		if(!imdb || !imdb.length){
			DevolverFilmesActualizar();
			return false;
		}
		if(detenido){
			if(button.peliculas.hasClass("deteniendo")){
				detenido = true;
				button.peliculas.toggleClass("comenzar deteniendo fa-play fa-pause");
			}
			return false;
		}
		$.ajax({
			type: 'POST',
			url: "../serverside/admin_actualizar_movie.php",
			data: {
				imdb: imdb.shift()
			},
			async: true,
			error: function(){
				location.reload();
			},
			contentType: "application/x-www-form-urlencoded",
			dataType: "json",
			success: function(json){
				if(json.error){
					progressLabel.text(json.error);
				}
				else{
					ActualizarPeliculas();
					procesadas++;
					progressbar.progressbar("option", "value", procesadas);
					times.push(json.segundos);
					var v = "<tr><td>" + json.tiempo + "<br />" + moment.utc(moment.utc().diff(desde)).format("HH:mm:ss") + "</td><td>" + str_pad(procesadas, 4, 0, 'STR_PAD_LEFT') + "</td><td><a href='index.php?page=filmes&id=" + json.id + "' target='_blank'>" + json.titulo + "</a></td><td>" + json.mensaje + "</td></tr>";
					$("#log").prepend(v);
					var segundos = Average();
					th.media.text(segundos);
					var faltantes = Math.ceil(segundos * imdb.length);
					var until = moment().add(faltantes, 'seconds');
					th.hasta.text(until.format("DD/MM HH:mm:ss"));
					th.current.text(procesadas);
					var pos = document.title.indexOf("|");
					var ok = document.title.substring(pos);
					document.title = progressLabel.text() + ' ' + until.from(moment()) + ' ' + ok;
				}
			}
		});
	}

	$('.autosubmit').change(function(){
		$(this).closest("form").submit();
	});
	datatable = tablas.DataTable();
	$('#archivos-descargados').SumoSelect({
		search: true
	});
	$('.accordion').accordion({
		heightStyle: "content",
		collapsible: true
	});
	$(".datepicker").datepicker({
		changeMonth: true,
		changeYear: true,
		dateFormat: "yy-mm-dd"
	});

	if(button.peliculas.length){
		progressbar.hide();
		div.peliculas.hide();
	}

	button.buscar.click(function(){
		$.when(DevolverTotalFilmesActualizar()).then(function(json){
			if(json.error){
				$.alert(json.error);
			}
			else{
				maximo = parseInt(json.total) || 0;
				if(maximo){
					div.peliculas.show();
					button.peliculas.click(function(){
						if(button.peliculas.hasClass("detener")){
							detenido = true;
							button.peliculas.toggleClass("detener deteniendo fa-stop fa-pause");
						}
						else if(button.peliculas.hasClass("deteniendo")){

						}
						else{
							button.peliculas.toggleClass("detener comenzar fa-play fa-stop");
							detenido = false;
							ActualizarPeliculas();
						}
					});
					progressbar.show().progressbar({
						value: 0,
						max: maximo,
						change: function(){
							progressLabel.text(Math.round(progressbar.progressbar("value") * 100 / maximo * 100) / 100 + "%");
						},
						complete: function(){
							progressLabel.text("Completado!");
						}
					});
					span.total.text(maximo);
					DevolverFilmesActualizar();
				}
				else{
					$.alert("Sin datos!");
				}
			}
		});
	});

	if(button.actores.length){
		button.actores.hide();
		progressbar.hide();

		$.when(DevolverTotalActoresActualizar()).then(function(json){
			if(json.error){
				progressLabel.text(json.error);
			}
			else{
				maximo = parseInt(json.total) || 0;
				if(maximo){
					button.actores.show().click(function(){
						if(button.actores.hasClass("detener")){
							detenido = true;
							button.actores.text("Deteniendo...");
							button.actores.toggleClass("detener deteniendo");
						}
						else if(button.actores.hasClass("deteniendo")){

						}
						else{
							button.actores.toggleClass("detener comenzar");
							button.actores.text("Detener");
							detenido = false;
							ActualizarActores();
						}
					});
					progressbar.show().progressbar({
						value: 0,
						max: maximo,
						change: function(){
							progressLabel.text(Math.round(progressbar.progressbar("value") * 100 / maximo * 100) / 100 + "%");
						},
						complete: function(){
							progressLabel.text("Completado!");
						}
					});
					span.total.text(maximo);
					DevolverActoresActualizar();
				}
				else{
					progressLabel.text("Sin datos!");
				}
			}
		});
	}

	if(button.localizaciones.length){
		$.when(DevolverTotalLocalizacionesActualizar()).then(function(json){
			if(json.error){
				$.alert(json.error);
			}
			else{
				maximo = parseInt(json.total) || 0;
				if(maximo){
					ActualizarLocalizaciones();
				}
				else{
					$.alert("Sin datos!");
				}
			}
		});
	}

	query.split("&").forEach(function(part){
		var item = part.split("=");
		result[item[0]] = decodeURIComponent(item[1]);
	});

	if(result.id && (registro_cargado = parseInt(result.id))){
		var anterior = registro_cargado - 1;
		if(anterior > 0){
			button.anterior.click(function(){
				location.href = 'index.php?page=' + result.page + '&id=' + anterior;
			});
		}
		else{
			button.anterior.prop("disabled", true);
		}
		siguiente = registro_cargado + 1;
		button.siguiente.click(function(){
			location.href = 'index.php?page=' + result.page + '&id=' + siguiente;
		});
	}
	else{
		button.anterior.prop("disabled", true);
		button.siguiente.prop("disabled", true);
	}

	button.ultimo.click(function(){
		var pagina = registro_cargado?result.page:'filmes';
		if(!ultimo_registro){
			$.when(DevolverUltimoRegistro(pagina)).then(function(json){
				if(!json.error){
					location.href = 'index.php?page=' + pagina + '&id=' + json.ultimo;
				}
			});
			return false;
		}
		location.href = 'index.php?page=' + pagina + '&id=' + siguiente;
	});
	button.nuevo.click(function(){
		var pagina = registro_cargado?result.page:'filmes';
		if(!ultimo_registro){
			$.when(DevolverUltimoRegistro(pagina)).then(function(json){
				if(!json.error){
					var nuevo = parseInt(json.ultimo) + 1;
					location.href = 'index.php?page=' + pagina + '&id=' + nuevo;
				}
			});
			return false;
		}
		nuevo = ultimo_registro + 1;
		location.href = 'index.php?page=' + pagina + '&id=' + nuevo;
	});
	button.avanzar.click(function(){
		if(parseInt(inputs.avanzar.val())){
			var pagina = registro_cargado?result.page:'filmes';
			location.href = 'index.php?page=' + pagina + '&id=' + inputs.avanzar.val();
		}
	});
	button.localizaciones.click(function(){
		$.ajax({
			url: "../serverside/operaciones_localizaciones.php?action=ActualizarLocalizacionesProcesadas",
			async: true,
			beforeSend: function(){
				$.block();
			},
			complete: function(){
				$.unblockUI();
			},
			error: function(objeto, quepaso){
				$.alert(quepaso);
			},
			contentType: "application/x-www-form-urlencoded",
			dataType: "json",
			success: function(json){
				if(json.error){
					$.alert(json.error);
				}
				else{
					$.alert('Modificadas ' + json.modificados + ' localizaciones y agregadas ' + json.nuevos + ' nuevas');
				}
			}
		});
	});

	if($('#rating-pelicula').length){
		$('#rating-pelicula').rateYo({
			rating: $('#rating-pelicula').data('value'),
			onInit: function(rating, rateYoInstance){
				console.log("RateYo initialized! with " + rating);
			},
			onSet: function(rating, rateYoInstance){
				var data = $(this).closest("div.contenedor-global").data();
				if(!data.pelicula || !parseInt(data.pelicula)){
					return false;
				}
				$.ajax({
					type: "POST",
					url: "../serverside/operaciones_peliculas.php?action=ActualizarVotoPelicula",
					data: {
						pelicula: data.pelicula,
						voto: rating
					},
					async: true,
					beforeSend: function(){
						$.block();
					},
					complete: function(){
						$.unblockUI();
					},
					error: function(objeto, quepaso){
						$.alert(quepaso);
					},
					contentType: "application/x-www-form-urlencoded",
					dataType: "json",
					success: function(json){
						if(json.error){
							$.alert(json.error);
						}
						else{
							$("#voto-usuario").text(rating);
							$("#rating-total").text(json.rating);
						}
					}
				});
			},
			onChange: function(rating, rateYoInstance){
				$(this).next().text(rating);
			}
		});
	}
	$('.eliminar-datos-actores').click(function(){
		EliminarFilaRegistro($(this));
	});
	$('.borrar-datos-actores').click(function(){
		EliminarFilaRegistro($(this));
	});
	$('.actualizar-flag-filme').click(function(){
		if(!registro_cargado){
			return false;
		}
		ActualizarFlagTablaFilme($(this));
	});
	$('#updateimdb').click(function(){
		$('#pagina-imdb').val($('#enlace-imdb').attr('href'));
		$('#formimdbnew').submit();
	});
	$('#change_permalink').click(function(){
		$('#permalink').val(str_replace(" ", "-", $('#predeterminado').val().replace(/[^a-zA-Z 0-9.]+/g, '').toLowerCase()));
	});
	$('#agregar-foto-actor').click(function(){
		$('#userfile').click();
	});
	$('.ocultos').hide();
	$('.mostrar').click(function(){
		var id = this.target;
		if($('#' + id).is(':visible')){
			$('#' + id).hide();
		}
		else{
			$('#' + id).show();
		}
	});
	$('#actualizar-duracion-capitulos').click(function(){
		$.ajax({
			type: "GET",
			url: "actualizar_duracion_capitulos.php",
			data: {
				filme: registro_cargado,
				duracion: (parseInt($("#duracion").val()) || 0)
			},
			async: true,
			contentType: "application/x-www-form-urlencoded",
			dataType: "html",
			success: function(datos){
				$('#calendario').html(datos);
			}
		});
	});
	$('#elimina_online_lk').click(function(){
		$.ajax({
			type: "POST",
			url: "../serverside/admin_eliminar_online.php",
			data: "filme=" + registro_cargado,
			async: true,
			contentType: "application/x-www-form-urlencoded",
			dataType: "html",
			success: function(datos){
				$("#online_lk").val("");
				$("#broken_link_check").attr("checked", false);
			}
		});
	});
	$('.autoblur').focusout(function(){
		ActualizarCampoTablaFilme($(this));
	});
	$('.auto-actualizar-campo-actor').focusout(function(){
		ActualizarCampoTablaActor($(this));
	});
	$('.actualizar-actor').click(function(){
		ActualizarCampoTablaActor($(this).closest("div.input-group").find(":input").eq(0));
	});
	$('.actualizar').click(function(){
		ActualizarCampoTablaFilme($(this).closest("div.input-group").find(":input").eq(0));
	});
	$('#color').change(function(){
		ActualizarCampoTablaFilme($(this));
	});
	$('#moneda').change(function(){
		ActualizarCampoTablaFilme($(this));
	});
	$('.auto-actualizar-actor').change(function(){
		ActualizarCampoTablaActor($(this));
	});
	$('.add-movie-image').click(function(){
		var data = $(this).data();
		var form = $("#add-movie-image-form");
		form.children("input[type=hidden]").each(function(){
			var input = $(this);
			var named = input.attr('name');
			if(data[named]){
				input.val(data[named]);
			}
			else{
				input.val(0);
			}
		});
		form.children("input[type=file]").click();
	});
	$('.delete-movie-image').click(function(){
		var image = $(this);
		var src = image.attr('src');
		$.ajax({
			type: "POST",
			url: "../serverside/operaciones_admin.php?action=EliminarImagen",
			data: {
				src: src
			},
			async: true,
			contentType: "application/x-www-form-urlencoded",
			dataType: "json",
			success: function(json){
				if(json.error){
					$.alert(json.error);
				}
				else{
					image.parent().remove();
				}
			}
		});
	});
	$('.eliminar').click(function(){
		EliminarFilaRegistro($(this));
	});
	$("a.total").click(function(event){
		event.preventDefault();
		var parent = $(this).parent();
		parent.find(".enlaces").each(function(index){
			var url = $(this).attr("href");
			var wnd = $(this).attr("target");
			window.open(url, wnd);
		});
		parent.remove();
		$("#totales").html($(".contenedores").length);
		if(empty($(".contenedores").length)){
			location.reload();
		}
		return false;
	});
	$('.contenido').click(function(){
		var c = $(this);
		var u = c.data('page') + '.php';
		var table = c.data('table');
		if(!empty(table)){
			open_table = table;
			u += '?table=' + open_table + '&campo=id_filme';
		}
		else{
			u += '?filme=' + registro_cargado;
		}
		$.ajax({
			type: "GET",
			url: u,
			async: true,
			contentType: "application/x-www-form-urlencoded",
			dataType: "html",
			success: function(datos){
				$('#contenedor').html(datos);
			}
		});
	});
	$('.actualizar-nacionalidad-actor').click(function(){
		var i = $(this);
		var tr = i.closest("tr");
		var data = tr.data();
		$.ajax({
			type: "POST",
			url: "../serverside/operaciones_actores.php?action=ActualizarNacionalidadActor",
			data: {
				actor: data.actor,
				pais: data.codigo
			},
			async: true,
			contentType: "application/x-www-form-urlencoded",
			dataType: "json",
			success: function(json){
				if(json){
					if(json.error){
						console.log(json.error);
					}
					else if(parseInt(json.modificados)){
						tr.remove();
					}
					else{
						console.log(json);
					}
				}
			}
		});
	});
	$("#insertar").html($('.pendientes').length);
	$(".botonIr").click(function(){
		var form = $(this).data("form");
		$("#form" + form).submit().parent().remove();
	});
	$(".removeURL").click(function(){
		var form = $(this).data("form");
		$("." + form).remove();
		$("#insertar").html($('.pendientes').length);
	});
	$(".ratelist").click(function(){
		var url = $(this).html();
		var ide = this.id;
		$("#iformulariodetalle").attr("action", "http://www.imdb.com/title/" + url + "/").submit();
		$.ajax({
			type: 'POST',
			url: 'actualizar_ratelist_interna.php',
			data: 'id=' + ide,
			async: true,
			contentType: "application/x-www-form-urlencoded",
			dataType: "html",
			success: function(data){
				if(!empty(data)){
					$.alert(data);
				}
				else{
					$("#" + ide).parent().parent().remove();
					var total = $("#inserted").html() * 1;
					total++;
					$("#inserted").html(total);
					var total = $("#remain").html() * 1;
					total--;
					$("#remain").html(total);
					var remain = $(".ratelist").length;
					if(empty(remain)){
						location.reload();
					}
				}
			}
		});
	});
	$(".washlist").click(function(){
		var url = $(this).html();
		var ide = this.id;
		$("#const").val(url);
		$("#list_id").val("FhnLsYR5o2k");
		$("#code_tag").val("7983");
		$("#iformulario").submit();
		$.ajax({
			type: 'POST',
			url: 'actualizar_washlist_interna.php',
			data: 'id=' + ide,
			async: true,
			contentType: "application/x-www-form-urlencoded",
			dataType: "html",
			success: function(data){
				if(!empty(data)){
					$.alert(data);
				}
				else{
					$("#" + ide).remove();
					var total = $("#inserted").html() * 1;
					total++;
					$("#inserted").html(total);
					var total = $("#remain").html() * 1;
					total--;
					$("#remain").html(total);
					var remain = $(".washlist").length;
					if(empty(remain)){
						location.reload();
					}
				}
			}
		});
	});
	$(".swashlist").click(function(){
		var url = $(this).html();
		var ide = this.id;
		$("#const").val(url);
		$("#list_id").val("FhnLsYR5o2k");
		$("#code_tag").val("7983");
		$("#iformulario").submit();
		$.ajax({
			type: 'POST',
			url: 'actualizar_washlist_interna.php',
			data: 'id=' + ide,
			async: true,
			contentType: "application/x-www-form-urlencoded",
			dataType: "html",
			success: function(data){
				if(!empty(data)){
					$.alert(data);
				}
				else{
					$("#" + ide).remove();
					var total = $("#sinserted").html() * 1;
					total++;
					$("#sinserted").html(total);
					var total = $("#sremain").html() * 1;
					total--;
					$("#sremain").html(total);
					var remain = $(".swashlist").length;
					if(empty(remain)){
						location.reload();
					}
				}
			}
		});
	});
	$(".ewashlist").click(function(){
		var url = $(this).html();
		var ide = this.id;
		$("#const").val(url);
		$("#list_id").val("FhnLsYR5o2k");
		$("#code_tag").val("7983");
		$("#iformulario").submit();
		$.ajax({
			type: 'POST',
			url: 'actualizar_washlist_interna.php',
			data: 'id=' + ide,
			async: true,
			contentType: "application/x-www-form-urlencoded",
			dataType: "html",
			success: function(data){
				if(!empty(data)){
					$.alert(data);
				}
				else{
					$("#" + ide).remove();
					var total = $("#einserted").html() * 1;
					total++;
					$("#einserted").html(total);
					var total = $("#eremain").html() * 1;
					total--;
					$("#eremain").html(total);
					var remain = $(".ewashlist").length;
					if(empty(remain)){
						location.reload();
					}
				}
			}
		});
	});
	$("#asignar-archivos-descargados").click(function(){
		var button = $(this);
		var data = button.closest("div.contenedor-global").data();
		if(!data.pelicula || !parseInt(data.pelicula)){
			return false;
		}
		var archivos = $('#archivos-descargados').val();
		if(!archivos.length){
			return false;
		}
		$.ajax({
			type: 'POST',
			url: '../serverside/operaciones_peliculas.php?action=InsertarArchivosVideo',
			data: {
				pelicula: data.pelicula,
				archivos: archivos
			},
			async: true,
			contentType: "application/x-www-form-urlencoded",
			dataType: "json",
			success: function(json){
				if(json.error){
					$.alert(json.error);
				}
				else{
					location.reload();
				}
			}
		});
	});
	$("#agregar-pelicula-vista").click(function(){
		var button = $(this);
		var data = button.closest("div.contenedor-global").data();
		if(!data.pelicula || !parseInt(data.pelicula)){
			return false;
		}
		$.ajax({
			type: 'POST',
			url: '../serverside/operaciones_peliculas.php?action=AgregarPeliculaUsuario',
			data: {
				pelicula: data.pelicula
			},
			async: true,
			contentType: "application/x-www-form-urlencoded",
			dataType: "json",
			success: function(json){
				if(json.error){
					$.alert(json.error);
				}
				else{
					button.hide();
					$("#agregar-pelicula-pendiente").hide();
				}
			}
		});
	});
	$("#agregar-pelicula-pendiente").click(function(){
		var button = $(this);
		var data = button.closest("div.contenedor-global").data();
		if(!data.pelicula || !parseInt(data.pelicula)){
			return false;
		}
		$.ajax({
			type: 'POST',
			url: '../serverside/operaciones_peliculas.php?action=AgregarPeliculaPendiente',
			data: {
				pelicula: data.pelicula
			},
			async: true,
			contentType: "application/x-www-form-urlencoded",
			dataType: "json",
			success: function(json){
				if(json.error){
					$.alert(json.error);
				}
				else{
					button.hide();
				}
			}
		});
	});
	$(".eliminar-pelicula-listado").click(function(){
		var button = $(this);
		var data = button.data();
		if(!data.pelicula || !parseInt(data.pelicula)){
			return false;
		}
		$.ajax({
			type: 'POST',
			url: '../serverside/operaciones_peliculas.php?action=EliminarListadoUsuario',
			data: {
				pelicula: data.pelicula
			},
			async: true,
			contentType: "application/x-www-form-urlencoded",
			dataType: "json",
			success: function(json){
				if(json.error){
					$.alert(json.error);
				}
				else{
					button.closest("div.datos-pelicula-listado").remove();
				}
			}
		});
	});
	$(document).on('click', '#enviar_ajax', function(){
		$.ajax({
			type: "POST",
			url: "insertar_nuevo_registro.php?table=" + open_table + "&ajax=true",
			data: $('#fr_new_record').serialize(),
			async: true,
			contentType: "application/x-www-form-urlencoded",
			dataType: "html",
			success: function(datos){
				if(!empty(datos)){
					$.alert(datos);
				}
			}
		});
	}).on('click', '#cancelar_ajax', function(){
		open_table = "";
		$('#contenedor').html('');
	}).on('click', '.SumoSelect', function(){
		_this = $(this);
		_this.closest('.ui-accordion-content').addClass('overflow-visible');
		if(_this.find('.optWrapper ').hasClass('open')){
			$(this).closest('.ui-accordion-content').addClass('overflow-visible');
		}
	});
	$('.listados-peliculas-usuario').find('.img-responsive').each(function(){
		var imagen = $(this);
		var altura_imagen = imagen.height();
		var altura_contenedor = imagen.closest("div.row").height();
		imagen.css('margin-top', Math.ceil((altura_contenedor - altura_imagen) / 2));
	});
	$('.rating-pelicula-pendiente').each(function(){
		var container = $(this);
		var rating = container.data('value');
		container.rateYo({
			rating: rating,
			starWidth: "20px",
			readOnly: true
		});
	});
	$('.panel-heading').click(function(){
		var body = $(this).next('div.panel-body');
		if(body.is(':visible')){
			body.hide();
		}
		else{
			body.show();
		}
	});
	$('#filtros-peliculas-pendientes').find('div.panel-body').hide();
	var alerts = $('.alert-update-films');
	if(alerts.length){
	    setTimeout(function(){ 
	        alerts.each(function(){
	           $(this).hide('clip', {}, 1000); 
	        });
	    }, 2500);
	}
});