<?php
/** @var $users array[] */
/** @var $error string|null */

use WEEEOpen\Crauto\Template;

$this->layout('base', ['title' => 'People']);
$testdates = [];
$sirsToSign = [];
$keys = [];
$today = new DateTimeImmutable();

$extractInfo = function($user) use ($today, &$testdates, &$sirsToSign, &$keys) {
	$signedSir = boolval($user['signedsir'] ?? false);
	list($testDaysDiff, $testScheduled) = safetyTest($user, $testdates, $today);
//	if($testDone && !$signedSir) {
	if(!$signedSir && $testScheduled) {
		$sirsToSign[] = $user;
	}
	$haskey = boolval($user['haskey'] ?? false);
	if($haskey) {
		$keys[] = $user;
	}
	return [$testDaysDiff, $testScheduled, $signedSir];
};

require_once 'safety_test.php';
?>
<h2>People</h2>

<?php if(isset($error)): ?>
	<div class="alert alert-danger" role="alert">
		Error: <?= $this->e($error) ?>
	</div>
<?php endif ?>

<!-- Non blocked user table -->
<table class="table" data-toggle="table">
	<thead class="thead-dark">
	<tr>
        <!--<th scope="col" class="text-center" data-sortable="false">Photo</th>-->
        <th scope="col" class="text-center" data-sortable="true">Username</th>
        <th scope="col" class="text-center" data-sortable="true">Full name</th>
        <th scope="col" class="text-center" data-sortable="true">Groups</th>
        <th scope="col" class="text-center" data-sortable="true">Test done</th>
        <th scope="col" class="text-center" data-sortable="true">Telegram</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach($users as $user): ?>
		<?php
		list($testDaysDiff, $testScheduled, $signedSir) = $extractInfo($user);

		if(!isset($user['nsaccountlock']) || $user['nsaccountlock'] === null): ?>
		<tr >
			<!--<td class="photo"><img alt="profile picture" src=""></td>-->
			<td class="text-center" ><a href="/people.php?uid=<?= urlencode($user['uid']) ?>"><?= $this->e($user['uid']) ?></a></td>
			<td class="text-center"><?= $this->e($user['cn']) ?></td>
            <td class="text-center"><?= !empty($user['memberof']) ? implode(', ', $user['memberof']) : '' ?></td>
            <td class="text-center"><?= safetyTestIcon($testDaysDiff, $testScheduled, $signedSir); ?></td>
            <td class="text-center">
	            <?= Template::telegramColumn($user['telegramnickname'], $user['telegramid']); ?>
            </td>
		</tr>
		<?php endif ?>
	<?php endforeach ?>
	</tbody>
</table>

<p class="text-muted"><a href="people.php?for=website">View code</a> for the website "Chi siamo" page</p>

<?php
$columns = (count($testdates) > 0 ? 1 : 0)
+ (count($sirsToSign) > 0 ? 1 : 0)
+ (count($keys) > 0 ? 1 : 0);
switch($columns) {
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
if($columns > 0):
?>
<div class="row">
	<?php if(count($testdates) > 0): ?>
		<div class="<?= $class ?>">
			<h2>Upcoming tests on safety</h2>
			<?php foreach($testdates as $date => $usersTestOnSafety): ?>
				<h5><?= $date ?></h5>
				<ul class="list-unstyled">
					<?php
					$user = ksort($usersTestOnSafety,  SORT_NATURAL | SORT_FLAG_CASE);
					foreach($usersTestOnSafety as $user): ?>
						<li><a href="/people.php?uid=<?= $this->e($user['uid']) ?>"><?= $this->e($user['cn']) ?></a>, <?= $this->e($user['schacpersonaluniquecode'])?> (<a href="/sir.php?uid=<?= $this->e($user['uid']) ?>">get SIR</a>)</li>
					<?php endforeach; ?>
				</ul>
			<?php endforeach; ?>
		</div>
	<?php endif ?>

	<?php if(count($sirsToSign) > 0): ?>
		<div class="<?= $class ?>">
			<h2>SIRs to print</h2>
				<ul class="list-unstyled">
					<?php foreach($sirsToSign as $user): ?>
						<li><a href="/sir.php?uid=<?= $this->e($user['uid']) ?>"><?= $this->e($user['cn']) ?></a>, <?= $this->e($user['schacpersonaluniquecode'])?></li>
					<?php endforeach; ?>
				</ul>
		</div>
	<?php endif ?>

	<?php if(count($keys) > 0): ?>
		<div class="<?= $class ?>">
			<h2>Who has keys to the lab</h2>
			<ul class="list-unstyled">
				<?php foreach($keys as $user): ?>
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


<h2>Locked accounts</h2>

<!-- Blocked user table -->
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
	foreach($users as $user): ?>
		<?php
		// Do not call $extractInfo, these users were already parsed
		$signedSir = boolval($user['signedsir'] ?? false);
		$nullref = NULL;
		list($testDaysDiff, $testScheduled) = safetyTest($user, $nullref, $today);

		$creationDate = DateTime::createFromFormat('YmdHis\Z', $user['createtimestamp']);
		$creationDate = $creationDate->format('Y-m-d');

		if(isset($user['nsaccountlock']) && $user['nsaccountlock'] === 'true'): ?>
			<tr class="locked">
				<!--<td class="photo"><img alt="profile picture" src=""></td>-->
				<td class="text-center"><a href="/people.php?uid=<?= urlencode($user['uid']) ?>"><?= $this->e($user['uid']) ?></a></td>
				<td class="text-center"><?= $this->e($user['cn']) ?></td>
				<td class="text-center"><?= $creationDate ?></td>
				<td class="text-center"><?= !empty($user['memberof']) ? implode(', ', $user['memberof']) : '' ?></td>
				<td class="text-center"><?= safetyTestIcon($testDaysDiff, $testScheduled, $signedSir); ?></td>
				<td class="text-center">
					<?= Template::telegramColumn($user['telegramnickname'], $user['telegramid']); ?>
				</td>
			</tr>
		<?php endif ?>
	<?php endforeach ?>
	</tbody>
</table>