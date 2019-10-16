<?php
/** @var $users array[] */
/** @var $error string|null */
$this->layout('base', ['title' => 'People']);
$testdates = [];
$today = new DateTimeImmutable();
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
		<?php
		if($user['safetytestdate'] !== null) {
			if((int) $user['safetytestdate']->diff($today)->format('%a') <= 0) {
				$testdates[$user['safetytestdate']->format('Y-m-d')][] = $user;
			}
		}
		?>
		<tr <?= isset($user['nsaccountlock']) && $user['nsaccountlock'] === 'true' ? 'class="locked"' : '' ?>>
			<td><a href="/people.php?uid=<?= urlencode($user['uid']) ?>"><?= $this->e($user['uid']) ?></a><?= isset($user['nsaccountlock']) && $user['nsaccountlock'] === 'true' ? ' (locked)' : '' ?></td>
			<td><?= $this->e($user['cn']) ?></td>
			<td><?= implode(', ', $user['memberof']) ?></td>
		</tr>
	<?php endforeach ?>
	</tbody>
</table>

<?php if(count($testdates) > 0): ?>
	<div>
		<h2>Upcoming tests on safety</h2>
		<?php foreach($testdates as $date => $users): ?>
			<h3><?= $date ?></h3>
			<ul class="list-unstyled">
				<?php foreach($users as $user): ?>
					<li><?= $this->e($user['cn']) ?> (<a href="/sir.php?uid=<?= $this->e($user['uid']) ?>">get SIR</a>)</li>
				<?php endforeach; ?>
			</ul>
		<?php endforeach; ?>
	</div>
<?php endif ?>
