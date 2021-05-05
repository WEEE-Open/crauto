<?php
/** @var $users array[] */
/** @var $error string|null */

use WEEEOpen\Crauto\Template;

$this->layout('base', ['title' => 'People']);
$testdates = [];
$today = new DateTimeImmutable();
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
		list($testDone, $testToDo) = safetyTest($user, $testdates, $today);

		if(!isset($user['nsaccountlock']) || $user['nsaccountlock'] === null): ?>
		<tr >
			<!--<td class="photo"><img alt="profile picture" src=""></td>-->
			<td class="text-center" ><a href="/people.php?uid=<?= urlencode($user['uid']) ?>"><?= $this->e($user['uid']) ?></a></td>
			<td class="text-center"><?= $this->e($user['cn']) ?></td>
            <td class="text-center"><?= !empty($user['memberof']) ? implode(', ', $user['memberof']) : '' ?></td>
            <td class="text-center"><?= safetyTestIcon($testDone, $testToDo); ?></td>
            <td class="text-center">
	            <?= Template::telegramColumn($user['telegramnickname'], $user['telegramid']); ?>
            </td>
		</tr>
		<?php endif ?>
	<?php endforeach ?>
	</tbody>
</table>

<p class="text-muted"><a href="people.php?for=website">View code</a> for the website "Chi siamo" page</p>

<br/>
<h2>Locked accounts</h2>

<!-- Blocked user table -->
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
	    list($testDone, $testToDo) = safetyTest($user, $testdates, $today);

        if(isset($user['nsaccountlock']) && $user['nsaccountlock'] === 'true'): ?>
            <tr class="locked">
	            <!--<td class="photo"><img alt="profile picture" src=""></td>-->
                <td class="text-center"><a href="/people.php?uid=<?= urlencode($user['uid']) ?>"><?= $this->e($user['uid']) ?></a></td>
                <td class="text-center"><?= $this->e($user['cn']) ?></td>
                <td class="text-center"><?= !empty($user['memberof']) ? implode(', ', $user['memberof']) : '' ?></td>
	            <td class="text-center"><?= safetyTestIcon($testDone, $testToDo); ?></td>
                <td class="text-center">
	                <?= Template::telegramColumn($user['telegramnickname'], $user['telegramid']); ?>
                </td>
            </tr>
        <?php endif ?>
    <?php endforeach ?>
    </tbody>
</table>
<br/>

<?php if(count($testdates) > 0): ?>
	<div>
		<h2>Upcoming tests on safety</h2>
		<?php foreach($testdates as $date => $users): ?>
			<h3><?= $date ?></h3>
			<ul class="list-unstyled">
				<?php
				$user = ksort($users,  SORT_NATURAL | SORT_FLAG_CASE);
				foreach($users as $user): ?>
					<li><a href="/people.php?uid=<?= $this->e($user['uid']) ?>"><?= $this->e($user['cn']) ?></a>, <?= $this->e($user['schacpersonaluniquecode'])?> (<a href="/sir.php?uid=<?= $this->e($user['uid']) ?>">get SIR</a>)</li>
				<?php endforeach; ?>
			</ul>
		<?php endforeach; ?>
	</div>
<?php endif ?>


<script>
    document.getElementsByName('table').bootstrapTable('refreshOptions', {
        sortable: true
    })
</script>
