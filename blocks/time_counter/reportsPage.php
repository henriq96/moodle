<?php 

require_once('../../config.php');

echo "<!DOCTYPE html>";
echo "<html>
<head>
	<link rel='stylesheet' type='text/css' href='reportsPageStyle.css'>
	
</head>";
echo "<body>";

echo "<script src='/blocks/active_time_tracker/jslibraries/jquery-3.2.1.js'></script>";
echo "<script src='reportsPageScript.js'></script>";

echo "<div id='bars'>";

echo "<div id='topBar'><img src='mainlogo.png' id='logo'></div>";

echo "<div id='secondBar'><h1>Relatório de Tempo Dispendido</h1></div>";

echo "</div>";

echo "
			<div id='roleSelect' class='selectionObject'>
			Perfil:
				<select onchange='getCourses()' id='roles'>
					<option value='0'>-</option>
  					<option value='1'>Manager</option>
  					<option value='3'>Teacher</option>
  					<option value='5'>Student</option>
				</select>
			</div>

			<div id='courseSelect' class='selectionObject'>
			Curso:
				<select onchange='getModulesAndUsers()' id='courses'>
					<option value='0'>-</option>
				</select>

			</div>

			<br>
<br>

		
			<div id='resourcesSelect' class='selectionObject'>
			Recurso/Atividade:
				<select id='modules'>
					<option value='0'>-</option>
				</select>
			</div>

			<div id='usersSelect' class='selectionObject'>

			Utilizador:
				<select id='users'>
					<option value='0'>-</option>
				</select>

			<br><br>
			</div>

	

	<div id='buttons'>

		<button onclick='showReports()'>Mostrar Relatório</button>

		<button onclick='exportTo(1)'>Exportar para PDF</button>

		<button onclick='exportTo(2)'>Exportar para CSV (Excel)</button>

		<br>

	</div>

	
	<br>
	<br>

	

	<div id='queryResult'>
	</div>


";


echo "</body>";
echo "</html>";






?>