<?php
/** @var $users array[] */
/** @var $excludedGroups string[] */
/** @var $error string|null */
$this->layout('base', ['title' => 'People, but for the website']);
$testdates = [];
$today = new DateTimeImmutable();
?>
<h2>People (website code version)</h2>

<?php if(isset($error)): ?>
	<div class="alert alert-danger" role="alert">
		Error: <?= $this->e($error) ?>
	</div>
<?php endif ?>

<?php
$usersToPrint = [];
foreach($users as $user) {
	$testDone = false;
	if(!isset($user['nsaccountlock']) || $user['nsaccountlock'] === null) {
		$usersToPrint[] = $user;
	}
}

usort($usersToPrint, function($a, $b) {
	if($a['sn'] !== $b['sn']) {
		return strcmp($a['sn'], $b['sn']);
	}
	return strcmp($a['cn'], $b['cn']);
});
?>

<p>Displaying all unlocked accounts except groups: <?= htmlspecialchars(implode(', ', $excludedGroups)); ?>.</p>

<pre>
&lt;style style="display:hidden;">.persona {box-sizing:border-box;width:25%;text-align:center;border-bottom:0.3rem solid #333;padding:0.4em;} .persona .name {font-size: 130%;font-weight:bold;} .persona .area {font-weight:bold;}&lt;/style>
&lt;div style="display:flex;flex-wrap:wrap;justify-content:space-around;align-items:stretch;flex-direction:row;">
<?php foreach($usersToPrint as $user): ?>
&lt;div class="persona">
&lt;p class="name"><?= $user['cn']; ?>&lt;/p>
<?php if(isset($user['websitedescription']) && $user['websitedescription'] !== ''): ?>&lt;p class="area"><?= nl2br(htmlspecialchars($user['websitedescription'], ENT_HTML5)); ?>&lt;/p><?php echo "\n"; endif; ?>
<?php if(isset($user['degreecourse']) && $user['degreecourse'] !== ''): ?>&lt;p>&lt;small>Studente di <?= htmlspecialchars($user['degreecourse'], ENT_HTML5); ?>&lt;/small>&lt;/p><?php echo "\n"; endif; ?>
&lt;/div>
<?php endforeach; ?>
&lt;/div>
</pre>

<p>Return to <a href="people.php">people list</a>.</p>