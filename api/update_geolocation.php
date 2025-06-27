<?php 
$sql="UPDATE `md101_experts_geoloc` g
INNER JOIN adresses_avec_coordonnees c on c.id_expert=g.id_expert
SET g.`latitude`=c.latitude,g.`longitude`=c.longitude";
  ?>
