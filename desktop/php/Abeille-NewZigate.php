</br>
<legend><i class="fas fa-cogs"></i> {{Changement de Zigate}}</legend>
<div class="form-group" style="background-color: rgba(var(--defaultBkg-color), var(--opacity)) !important; padding-left: 10px">

	<!-- <br/>
	<label style="margin-right : 20px">Changement de zigate</label> -->
	<?php
	// TODO: Full URL to point on eq replacement chapter
	// echo '<a class="btn btn-primary btn-xs" target="_blank" href="'.urlUserMan.'"><i class="fas fa-book"></i>{{Documentation}}</a>';
	?>
	<!-- <form action="/plugins/Abeille/desktop/php/AbeilleFormAction.php" method="post"> -->
		<!-- Remplacement d une zigate:
		<br/>
		<select name="zigateZ" style="width : 40%">
		< ?php
			for ( $i=1; $i<= maxGateways; $i++ ) {
				echo '<option value="' . $i . '">Zigate' . $i . '</option>';
			}
		?>
		</select>
		</br><br/>
		<input type="submit" name="submitButton" value="ReplaceZigate">
		</br><br/> -->
	<!-- </form> -->

	{{Vous avez ajouté ou remplacé une zigate, ou il y a eu changement de port générant ainsi un changement de son adresse IEEE.}}
	<br/>
	{{La Zigate est ignorée par mesure de sécurité.}}
	<br/>
	<br/>
	{{Selectionnez la nouvelle Zigate et confirmez.}}
	<br/>
	<select id="idNewZigate" style="width : 40%">
	<?php
		for ($gtwId = 1; $gtwId <= maxGateways; $gtwId++) {
			if (config::byKey('ab::gtwEnabled'.$gtwId, 'Abeille', 'N') != 'Y')
				continue; // Disabled
			$eqLogic = Abeille::byLogicalId('Abeille'.$gtwId.'/0000', 'Abeille');
			if (!is_object($eqLogic))
				continue;
			echo '<option value="'.$gtwId.'">Zigate '.$gtwId.' ('.$eqLogic->getHumanName().')</option>';
		}
	?>
	</select>
	<br/>
	<br/>
	<a class="btn btn-warning" onclick="acceptNewZigate()">{{Confirmer}}</a>
	<br/>
	<br/>
</div>


