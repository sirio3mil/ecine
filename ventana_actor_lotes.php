<?php 
include_once 'acceso_restringido.php';
?>
<div id="form_actores_lotes">
<form method="post" name="extend" action="index.php?page=insertar_lotes" >
	<fieldset>
    <legend align="left">Importar Actores</legend>
	<label for="id_filme">ID filme:</label>
    <input type="text" name="id_filme" size="10" maxlength="256" value="<?php echo $_SESSION['id_page']?>">
    <input type="submit" value="Ir" class="botonIr"><br>
    <label for="actores">Nombres:</label><br>
    <textarea cols="50" rows="3" name="actores"></textarea>
    </fieldset>
</form>
</div>