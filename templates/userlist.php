<?php
/** @var $users array[] */
/** @var $error string|null */
$this->layout('base', ['title' => 'People']);
?>
<h1>People</h1>

<?php if($error !== null): ?>
	<div class="alert alert-danger" role="alert">
		Error: <?=$error?>
	</div>
<?php endif ?>

<table class="table table-striped">
	<caption>List of people</caption>
	<thead class="thead-dark">
	<tr>
		<th scope="col">Username</th>
		<th scope="col">Full name</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach($users as $user): ?>
		<tr>
			<td><a href="/people.php?uid=<?= urlencode($user['uid']) ?>"><?= $this->e($user['uid']) ?></a></td>
			<td><?= $this->e($user['cn']) ?></td>
		</tr>
	<?php endforeach ?>
	</tbody>
</table>
