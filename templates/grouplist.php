<?php
/** @var $users array[] */
/** @var $error string|null */
$this->layout('base', ['title' => 'Groups']);
$testdates = [];
$today = new DateTimeImmutable();
$groups = []; //[ 'Cloud' => [ cloud users ], "Soviet" => [ soviet users ], etc ... ]
$keys = [];
?>
<h2>Groups</h2>

<?php if (isset($error)): ?>
    <div class="alert alert-danger" role="alert">
        Error: <?= $this->e($error) ?>
    </div>
<?php endif ?>

<?php
// Collecting users in groups
foreach($users as $user) {
	foreach($user['memberof'] as $group) {
		if(array_key_exists($group, $groups)) {
			array_push($groups[$group], $user);
		} else {
			$groups[$group] = [$user];
		}
	}
}
ksort($groups); ?>

<?php // Printing tables
foreach($groups as $name => $group): ?>
    <h4><?= $name ?></h4>
    <table class="table" data-toggle="table">
        <thead class="thead-dark">
        <tr>
            <!--<th scope="col" class="text-center" data-sortable="false">Photo</th>-->
            <th scope="col" class="text-center" data-sortable="true">Username</th>
            <th scope="col" class="text-center" data-sortable="true">Full name</th>
            <th scope="col" class="text-center" data-sortable="true">Other Groups</th>
            <th scope="col" class="text-center" data-sortable="true">Test done</th>
            <th scope="col" class="text-center" data-sortable="true">Telegram</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($group as $user):
            $testDone = false;
            if ($user['safetytestdate'] !== null) {
                if((int)$user['safetytestdate']->diff($today)->format('%R%a') >= 0) {
                    $testDone = true;
                }
            }
            ?>
            <tr <?= isset($user['nsaccountlock']) && $user['nsaccountlock'] === 'true' ? 'class="locked"' : '' ?> >
                <!--<td class="photo"><img alt="profile picture" src=""></td>-->
                <td class="text-center"><a href="/people.php?uid=<?= urlencode($user['uid']) ?>"><?= $this->e($user['uid']) ?></a></td>
                <td class="text-center"><?= $this->e($user['cn']) ?></td>
                <td class="text-center"><?php
                    $key = array_search($keys[$i], $user['memberof']);
                    $otherGroups = $user['memberof'];
                    unset($otherGroups[$key]);
                    if(!empty($otherGroups)) {
	                    echo implode(', ', $otherGroups);
                    }
                    ?>
                </td>
                <td class="text-center"><?= $testDone ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>' ?></td>
                <td class="text-center">
                    <?php
                    //Telegram username ( if it exists )
                    if(isset($user['telegramnickname']) && $user['telegramnickname'] !== null) {
	                    echo '<a href="https://t.me/' . $user['telegramnickname'] . '">@' . $user['telegramnickname'];
                    } elseif (isset($user['telegramid']) && $user['telegramid'] !== null) {
	                    echo 'ID Only';
                    } else {
	                    echo 'N/D';
                    }
                    ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <br/>
<?php endforeach; ?>

<script>
    document.getElementsByName('table').bootstrapTable('refreshOptions', {
        sortable: true
    })
</script>
