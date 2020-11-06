<?php
/** @var $users array[] */
/** @var $error string|null */
$this->layout('base', ['title' => 'People']);
$testdates = [];
$today = new DateTimeImmutable();
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
        $testDone = false;
		if($user['safetytestdate'] !== null) {
			if((int) $user['safetytestdate']->diff($today)->format('%R%a') >= 0) {
				$sortkey = $user['sn'] . ' ' . $user['cn'] . ' ' . $user['uid'];
				$testdates[$user['safetytestdate']->format('Y-m-d')][$sortkey] = $user;
				$testDone = true;
			}
		}
		$image = new \WEEEOpen\Crauto\Image($user['uid'], null);

		if(isset($user['nsaccountlock']) && $user['nsaccountlock'] !== 'true' ): ?>
		<tr >
			<!--<td class="photo"><img alt="profile picture" src=""></td>-->
			<td class="text-center" ><a href="/people.php?uid=<?= urlencode($user['uid']) ?>"><?= $this->e($user['uid']) ?></a></td>
			<td class="text-center"><?= $this->e($user['cn']) ?></td>
            <td class="text-center"><?= !empty($user['memberof']) ? implode(', ', $user['memberof']) : 'No groups' ?></td>
            <td class="text-center"><?= $testDone ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>' ?></td>
            <td class="text-center">
                <?php
                    //Telegram username ( if it exists )
                    if(isset($user['telegramnickname']) && $user['telegramnickname'] !== null )
                        echo '<a href="https://t.me/' . $user['telegramnickname'] . '">'. $user['telegramnickname'];
                    elseif(isset($user['telegramid']) && $user['telegramid'] !== null)
                        echo 'ID Only';
                    else
                        echo 'N/D';
                ?>
            </td>
		</tr>
		<?php endif ?>
	<?php endforeach ?>
	</tbody>
</table>

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
        $testDone = false;
        if($user['safetytestdate'] !== null) {
            if((int) $user['safetytestdate']->diff($today)->format('%R%a') >= 0) {
                $sortkey = $user['sn'] . ' ' . $user['cn'] . ' ' . $user['uid'];
                $testdates[$user['safetytestdate']->format('Y-m-d')][$sortkey] = $user;
                $testDone = true;
            }
        }
        $image = new \WEEEOpen\Crauto\Image($user['uid'], null);
        ?>
        <?php if(isset($user['nsaccountlock']) && $user['nsaccountlock'] === 'true'): ?>
            <tr class="locked">
	            <!--<td class="photo"><img alt="profile picture" src=""></td>-->
                <td class="text-center"><a href="/people.php?uid=<?= urlencode($user['uid']) ?>"><?= $this->e($user['uid']) ?></a></td>
                <td class="text-center"><?= $this->e($user['cn']) ?></td>
                <td class="text-center"><?= !empty($user['memberof']) ? implode(', ', $user['memberof']) : 'No groups' ?></td>
                <td class="text-center"><?= $testDone ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>' ?></td>
                <td class="text-center">
                    <?php
                    //Telegram username ( if it exists )
                    if(isset($user['telegramnickname']) && $user['telegramnickname'] !== null )
                        echo '<a href="https://t.me/' . $user['telegramnickname'] . '">'. $user['telegramnickname'];
                    elseif(isset($user['telegramid']) && $user['telegramid'] !== null)
                        echo 'ID Only';
                    else
                        echo 'N/D';
                    ?>
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
					<li><a href="/people.php?uid=<?= $this->e($user['uid']) ?>"><?= $this->e($user['cn']) ?></a>, <?= $this->e($user['schacpersonaluniquecode']) ?> (<a href="/sir.php?uid=<?= $this->e($user['uid']) ?>">get SIR</a>)</li>
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
