<?php

/** @var $users array[] */
/** @var $error string|null */

use WEEEOpen\Crauto\Template;

require_once 'safety_test.php';

$this->layout('base', ['title' => 'People']);
$testdates = [];
$sirsToSign = [];
$keys = [];
$today = new DateTimeImmutable();

for ($i = 0; $i < count($users); ++$i) {
	$signedSir = boolval($users[$i]['signedsir'] ?? false);
	list($testDaysDiff, $testScheduled) = safetyTest($users[$i], $testdates, $today);
	if (!$signedSir && $testScheduled) {
		$sirsToSign[] = $users[$i];
	}
	$haskey = boolval($users[$i]['haskey'] ?? false);
	if ($haskey) {
		$keys[] = $users[$i];
	}
	$users[$i]["testDaysDiff"] = $testDaysDiff;
	$users[$i]["testScheduled"] = $testScheduled;
	$users[$i]["signedSir"] = $signedSir;
}

$activeUsers = array_filter($users, function($user) {
	return !isset($user['nsaccountlock']) || $user['nsaccountlock'] === null;
});

$lockedUsers = array_filter($users, function($user) {
	return isset($user['nsaccountlock']) && $user['nsaccountlock'] !== null;
});

?>
<h2>People (<?= count($activeUsers) ?>)</h2>

<?php if (isset($error)) : ?>
	<div class="alert alert-danger" role="alert">
		Error: <?= $this->e($error) ?>
	</div>
<?php endif ?>

<!-- Non Locked user table -->
<table class="table" data-toggle="table">
	<thead class="thead-dark">
	<tr>
		<!--<th scope="col" class="text-center" data-sortable="false">Photo</th>-->
		<th scope="col" class="text-center" data-sortable="true">Username</th>
		<th scope="col" class="text-center" data-sortable="true">Full name</th>
		<th scope="col" class="text-center" data-sortable="true">Role and groups</th>
		<th scope="col" class="text-center" data-sortable="true">Test done</th>
		<th scope="col" class="text-center" data-sortable="true">Telegram</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($activeUsers as $user) : ?>
		<tr>
			<!--<td class="photo"><img alt="profile picture" src=""></td>-->
			<td class="text-center" ><a href="/people.php?uid=<?= urlencode($user['uid']) ?>"><?= $this->e($user['uid']) ?></a></td>
			<td class="text-center"><?= $this->e($user['cn']) ?></td>
			<td class="text-center"><?= nl2br($user['websitedescription'] ?? '', false) ?><br><small><?= !empty($user['memberof']) ? implode(', ', $user['memberof']) : '' ?></small></td>
			<td class="text-center"><?= safetyTestIcon($user["testDaysDiff"], $user["testScheduled"], $user["signedSir"]); ?></td>
			<td class="text-center"><?= Template::telegramColumn($user['telegramnickname'], $user['telegramid']); ?></td>
		</tr>
	<?php endforeach ?>
	</tbody>
</table>

<p class="text-muted"><a href="people.php?for=website"><i class="fa fa-id-card mr-1"></i>View code</a> for the website "Chi siamo" page</p>

<?php
$columns = (count($testdates) > 0 ? 1 : 0)
+ (count($sirsToSign) > 0 ? 1 : 0)
+ (count($keys) > 0 ? 1 : 0);
switch ($columns) {
	case 3:
		$class = 'col-xl-4 col-lg-6';
		break;
	case 2:
		$class = 'col-lg-6';
		break;
	case 1:
	default:
		$class = '';
		break;
}
if ($columns > 0) :
	?>
<div class="row">
	<?php if (count($testdates) > 0) : ?>
		<div class="<?= $class ?>">
			<h2>Upcoming tests on safety (<?= count($testdates) ?>)</h2>
			<?php foreach ($testdates as $date => $usersTestOnSafety) : ?>
				<h5><?= $date ?></h5>
				<ul class="list-unstyled">
					<?php
					$user = ksort($usersTestOnSafety, SORT_NATURAL | SORT_FLAG_CASE);
					foreach ($usersTestOnSafety as $user) :
						$schacpersonaluniquecode = isset($user['schacpersonaluniquecode']) ? $this->e($user['schacpersonaluniquecode']) : null; ?>
						<li><?= Template::shortListEntry($this->e($user['uid']), $this->e($user['cn']), $schacpersonaluniquecode) ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endforeach; ?>
		</div>
	<?php endif ?>

	<?php if (count($sirsToSign) > 0) : ?>
		<div class="<?= $class ?>">
			<h2>SIRs to print (<?= count($sirsToSign) ?>)</h2>
				<ul class="list-unstyled">
					<?php foreach ($sirsToSign as $user) :
						$schacpersonaluniquecode = isset($user['schacpersonaluniquecode']) ? $this->e($user['schacpersonaluniquecode']) : null; ?>
						<li><?= Template::shortListEntry($this->e($user['uid']), $this->e($user['cn']), $schacpersonaluniquecode) ?></li>
					<?php endforeach; ?>
				</ul>
		</div>
	<?php endif ?>

	<?php if (count($keys) > 0) : ?>
		<div class="<?= $class ?>">
			<h2>Who has keys to the lab (<?= count($keys) ?>)</h2>
			<ul class="list-unstyled">
				<?php foreach ($keys as $user) : ?>
					<li><a href="/people.php?uid=<?= $this->e($user['uid']) ?>"><?= $this->e($user['cn']) ?></a>, <?= $this->e($user['schacpersonaluniquecode'])?></li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif ?>
</div>
<?php endif; ?>

<script>
	document.getElementsByName('table').bootstrapTable('refreshOptions', {
		sortable: true
	})
</script>


<h2>Locked accounts (<?= count($lockedUsers) ?>)</h2>

<!-- Locked user table -->
<table class="table" data-toggle="table">
	<thead class="thead-dark">
	<tr>
		<!--<th scope="col" class="text-center" data-sortable="false">Photo</th>-->
		<th scope="col" class="text-center" data-sortable="true">Username</th>
		<th scope="col" class="text-center" data-sortable="true">Full name</th>
		<th scope="col" class="text-center" data-sortable="true">Created on</th>
		<th scope="col" class="text-center" data-sortable="true">Groups</th>
		<th scope="col" class="text-center" data-sortable="true">Test done</th>
		<th scope="col" class="text-center" data-sortable="true">Telegram</th>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ($lockedUsers as $user) : ?>
		<?php
		// Do not call $extractInfo, these users were already parsed
		$signedSir = boolval($user['signedsir'] ?? false);
		$nullref = null;
		list($testDaysDiff, $testScheduled) = safetyTest($user, $nullref, $today);

		$creationDate = DateTime::createFromFormat('YmdHis\Z', $user['createtimestamp']);
		$creationDate = $creationDate->format('Y-m-d');
		?>

		<tr class="locked">
			<!--<td class="photo"><img alt="profile picture" src=""></td>-->
			<td class="text-center"><a href="/people.php?uid=<?= urlencode($user['uid']) ?>"><?= $this->e($user['uid']) ?></a></td>
			<td class="text-center"><?= $this->e($user['cn']) ?></td>
			<td class="text-center"><?= $creationDate ?></td>
			<td class="text-center"><?= !empty($user['memberof']) ? implode(', ', $user['memberof']) : '' ?></td>
			<td class="text-center"><?= safetyTestIcon($testDaysDiff, $testScheduled, $signedSir); ?></td>
			<td class="text-center"><?= Template::telegramColumn($user['telegramnickname'], $user['telegramid']); ?></td>
		</tr>
	<?php endforeach ?>
	</tbody>
</table>