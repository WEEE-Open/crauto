<?php
/** @var $users array[] */
/** @var $error string|null */
$this->layout('base', ['title' => 'People']);
?>
<h1>People</h1>

<?php if(isset($error)): ?>
	<div class="alert alert-danger" role="alert">
		Error: <?= $this->e($error) ?>
	</div>
<?php endif ?>

<table class="table">
	<caption>List of people</caption>
	<thead class="thead-dark">
	<tr>
		<th scope="col">Username</th>
		<th scope="col">Full name</th>
		<th scope="col">Groups</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach($users as $user): ?>
		<tr <?= isset($user['nsaccountlock']) && $user['nsaccountlock'] === 'true' ? 'class="locked"' : '' ?>>
			<td><a href="/people.php?uid=<?= urlencode($user['uid']) ?>"><?= $this->e($user['uid']) ?></a><?= isset($user['nsaccountlock']) && $user['nsaccountlock'] === 'true' ? ' (locked)' : '' ?></td>
			<td><?= $this->e($user['cn']) ?></td>
			<td><?= implode(', ', $user['memberof']) ?></td>
		</tr>
	<?php endforeach ?>
	</tbody>
</table>
