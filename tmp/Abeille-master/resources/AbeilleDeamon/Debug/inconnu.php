<!--Liste les informations definis comme inconnues por le demon Abeille.-->
<!--exemple objets inconnus, commandes inconnus,......-->
<!--Pour essayer de compléter les modèles, EventConfig-->

<!DOCTYPE html>
<html>
<body>

<h1>Inconnu...</h1>




<p>Ci dessous la liste des informations inconnues et de ce fait non gérées par Abaeille.</p>
<p>L'idée est de collecter ces informations pour améliorer les modèles ou le code.</p>
<p>-------------------------------------</p>
<?php
$cmd = "grep inconnu /var/www/html/log/Abeille";

exec($cmd, $output);

if ( count($output) == 0 ) {
  echo "Liste vide";
  }
else {
  foreach ( $output as $line ) {
    echo $line."<br>\n";
  }
}
?>
<p>-------------------------------------</p>
</body>
</html>
