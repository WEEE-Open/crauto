<?php
/** @var $users array[] */
/** @var $error string|null */

use WEEEOpen\Crauto\Template;

$this->layout('base', ['title' => 'Groups']);
$testdates = [];
$groups = []; //[ 'Cloud' => [ cloud users ], "Soviet" => [ soviet users ], ... ]
$today = new DateTimeImmutable();
require_once 'safety_test.php';
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
	        $signedSir = boolval($user['signedsir'] ?? false);
	        list($testDone, $testToDo) = safetyTest($user, $testdates, $today);
            ?>
            <tr <?= isset($user['nsaccountlock']) && $user['nsaccountlock'] === 'true' ? 'class="locked"' : '' ?> >
                <!--<td class="photo"><img alt="profile picture" src=""></td>-->
                <td class="text-center"><a href="/people.php?uid=<?= urlencode($user['uid']) ?>"><?= $this->e($user['uid']) ?></a></td>
                <td class="text-center"><?= $this->e($user['cn']) ?></td>
                <td class="text-center"><?php
                    $key = array_search($name, $user['memberof']);
                    $otherGroups = $user['memberof'];
                    unset($otherGroups[$key]);
                    if(!empty($otherGroups)) {
	                    echo implode(', ', $otherGroups);
                    }
                    ?>
                </td>
                <td class="text-center"><?= safetyTestIcon($testDone, $testToDo, $signedSir); ?></td>
                <td class="text-center">
                    <?= Template::telegramColumn($user['telegramnickname'], $user['telegramid']); ?>
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
