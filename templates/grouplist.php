<?php
/** @var $users array[] */
/** @var $error string|null */
$this->layout('base', ['title' => 'Groups']);
$testdates = [];
$today = new DateTimeImmutable();
$groups = [];
$noGroupsUsers = [];
?>
<h2>Groups</h2>

<?php if (isset($error)): ?>
    <div class="alert alert-danger" role="alert">
        Error: <?= $this->e($error) ?>
    </div>
<?php endif ?>

<!-- Collecting groups -->
<?php foreach ($users as $user):
    if (empty($user['memberof']))
        array_push($noGroupsUsers, $user);
    else
        foreach ($user['memberof'] as $group)
            if (!in_array($group, $groups)) array_push($groups, $group);
endforeach; ?>

<?php foreach ($groups as $group): ?>
    <!-- Groups user table -->
    <h4><?= $group ?></h4>
    <table class="table" data-toggle="table">
        <thead class="thead-dark">
        <tr>
            <th scope="col" class="text-center" data-sortable="false">Photo</th>
            <th scope="col" class="text-center" data-sortable="true">Username</th>
            <th scope="col" class="text-center" data-sortable="true">Full name</th>
            <th scope="col" class="text-center" data-sortable="true">Other Groups</th>
            <th scope="col" class="text-center" data-sortable="true">Test done</th>
            <th scope="col" class="text-center" data-sortable="true">Telegram</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user):
            if (in_array($group, $user['memberof'])):
                $testDone = false;
                if ($user['safetytestdate'] !== null) {
                    if ((int)$user['safetytestdate']->diff($today)->format('%R%a') >= 0) {
                        $sortkey = $user['sn'] . ' ' . $user['cn'] . ' ' . $user['uid'];
                        $testdates[$user['safetytestdate']->format('Y-m-d')][$sortkey] = $user;
                        $testDone = true;
                    }
                }
                $image = new \WEEEOpen\Crauto\Image($user['uid'], null);
                ?>
                <tr <?= isset($user['nsaccountlock']) && $user['nsaccountlock'] !== 'true' ? 'class="locked"' : '' ?> >
                    <td class="photo"><?php if ($image->exists()): ?><img alt="profile picture"
                                                                          src="<?= $this->e($image->getUrl()) ?>"><?php endif; ?>
                    </td>
                    <td class="text-center"><a
                                href="/people.php?uid=<?= urlencode($user['uid']) ?>"><?= $this->e($user['uid']) ?></a>
                    </td>
                    <td class="text-center"><?= $this->e($user['cn']) ?></td>
                    <td class="text-center"><?php
                        $key = array_search($group, $user['memberof']);
                        $otherGroups = $user['memberof'];
                        unset($otherGroups[$key]);
                        echo implode(', ', $otherGroups);
                        ?>
                    </td>
                    <td class="text-center"><?= $testDone ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>' ?></td>
                    <td class="text-center">
                        <?php
                        //Telegram username ( if it exists )
                        if (isset($user['telegramnickname']) && $user['telegramnickname'] !== null)
                            echo '<a href="https://t.me/' . $user['telegramnickname'] . '">' . $user['telegramnickname'];
                        elseif (isset($user['telegramid']) && $user['telegramid'] !== null)
                            echo 'ID Only';
                        else
                            echo 'N/D';
                        ?>
                    </td>
                </tr>
            <?php endif; endforeach; ?>
        </tbody>
    </table>
    <br/>
<?php endforeach; ?>

<!-- No Group user table TODO: Could be removed if this is an impossible case-->
<h4>Account with no groups</h4>
<table class="table" data-toggle="table">
    <thead class="thead-dark">
    <tr>
        <th scope="col" class="text-center" data-sortable="false">Photo</th>
        <th scope="col" class="text-center" data-sortable="true">Username</th>
        <th scope="col" class="text-center" data-sortable="true">Full name</th>
        <th scope="col" class="text-center" data-sortable="true">Test done</th>
        <th scope="col" class="text-center" data-sortable="true">Telegram</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($noGroupsUsers as $user):
        $testDone = false;
        if ($user['safetytestdate'] !== null) {
            if ((int)$user['safetytestdate']->diff($today)->format('%R%a') >= 0) {
                $sortkey = $user['sn'] . ' ' . $user['cn'] . ' ' . $user['uid'];
                $testdates[$user['safetytestdate']->format('Y-m-d')][$sortkey] = $user;
                $testDone = true;
            }
        }
        $image = new \WEEEOpen\Crauto\Image($user['uid'], null);
        ?>
        <tr <?= isset($user['nsaccountlock']) && $user['nsaccountlock'] !== 'true' ? 'class="locked"' : '' ?> >
            <td class="photo"><?php if ($image->exists()): ?><img alt="profile picture"
                                                                  src="<?= $this->e($image->getUrl()) ?>"><?php endif; ?>
            </td>
            <td class="text-center"><a
                        href="/people.php?uid=<?= urlencode($user['uid']) ?>"><?= $this->e($user['uid']) ?></a>
            </td>
            <td class="text-center"><?= $this->e($user['cn']) ?></td>
            <td class="text-center"><?= $testDone ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>' ?></td>
            <td class="text-center">
                <?php
                //Telegram username ( if it exists )
                if (isset($user['telegramnickname']) && $user['telegramnickname'] !== null)
                    echo '<a href="https://t.me/' . $user['telegramnickname'] . '">' . $user['telegramnickname'];
                elseif (isset($user['telegramid']) && $user['telegramid'] !== null)
                    echo 'ID Only';
                else
                    echo 'N/D';
                ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<script>
    document.getElementsByName('table').bootstrapTable('refreshOptions', {
        sortable: true
    })
</script>
