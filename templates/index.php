<?php
/** @var $uid string */
/** @var $id string */
/** @var $name string */
$this->layout('base', ['title' => 'Welcome']) ?>

<h1>Crauto</h1>
<small>Creatore e Rimuovitore Autogestito di Utenti che Tutto Offre</small>
<p>Hi <?= $name ?>, your username is <?= $uid ?> and your ID is <?= $id ?></p>
<h2>Enabled services</h2>
<p>What can I access with this account?</p>
<ul>
	<li>Crauto - account management system, where you are now</li>
	<?php
	foreach(CRAUTO_HOME_PAGE_SERVICES as $service) {
		switch(count($service)) {
			case 1:
			default:
				echo "<li>$service[0]</li>";
				break;
			case 2:
				echo "<li><a href=\"$service[1]\" target=\"_blank\">$service[0]</a></li>";
				break;
			case 3:
				echo "<li><a href=\"$service[1]\" target=\"_blank\">$service[0]</a> - $service[2]</li>";
				break;
		}
	}
	?>
</ul>
