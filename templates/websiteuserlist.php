<?php
/** @var $users array[] */
/** @var $error string|null */
$this->layout('base', ['title' => 'People, but for the weeebsite']);
$testdates = [];
$today = new DateTimeImmutable();
?>
<h2>People (website version)</h2>

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

<p>Paste this into the <a href="https://github.com/WEEE-Open/WEEEbsite/">WEEEbsite</a> <strong>after manual review</strong>.</p>

<pre>
<?php foreach($usersToPrint as $user): ?>
&#9;&lt;div class="persona">
&#9;&#9;&lt;h2><?= $user['cn']; ?>&lt;/h2>
&#9;&#9;&lt;p>&lt;small>Studente di <?= $user['degreecourse']; ?>&lt;/small>&lt;/p>
&#9;&lt;/div>
<?php endforeach; ?>
</pre>

<p>Return to the <a href="people.php">people list</a>.</p>