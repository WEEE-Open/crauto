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
		<th scope="col">Photo</th>
		<th scope="col">Username</th>
		<th scope="col">Full name</th>
		<th scope="col">Groups</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach($users as $user): ?>
		<?php
		if($user['safetytestdate'] !== null) {
			if((int) $user['safetytestdate']->diff($today)->format('%R%a') <= 0) {
				$sortkey = $user['sn'] . ' ' . $user['cn'] . ' ' . $user['uid'];
				$testdates[$user['safetytestdate']->format('Y-m-d')][$sortkey] = $user;
			}
		}
		$photo = new \WEEEOpen\Crauto\Image($user['uid'], null);
		?>
		<tr <?= isset($user['nsaccountlock']) && $user['nsaccountlock'] === 'true' ? 'class="locked"' : '' ?>>
			<td class="photo"><?php if($photo->exists()): ?><img alt="profile picture" src="<?= $this->e($image->getUrl()) ?>"><?php endif; ?></td>
			<td class="align-middle"><a href="/people.php?uid=<?= urlencode($user['uid']) ?>"><?= $this->e($user['uid']) ?></a><?= isset($user['nsaccountlock']) && $user['nsaccountlock'] === 'true' ? ' (locked)' : '' ?></td>
			<td class="align-middle"><?= $this->e($user['cn']) ?></td>
			<td class="align-middle"><?= implode(', ', $user['memberof']) ?></td>
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
				<?php
				$user = ksort($users,  SORT_NATURAL | SORT_FLAG_CASE);
				foreach($users as $user): ?>
					<li><a href="/people.php?uid=<?= $this->e($user['uid']) ?>"><?= $this->e($user['cn']) ?></a>, <?= $this->e($user['schacpersonaluniquecode']) ?> (<a href="/sir.php?uid=<?= $this->e($user['uid']) ?>">get SIR</a>)</li>
				<?php endforeach; ?>
			</ul>
		<?php endforeach; ?>
	</div>
<?php endif ?>
