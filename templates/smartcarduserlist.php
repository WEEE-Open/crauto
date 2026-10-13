<?php
/** @var $users array[] */
/** @var $excludedGroups string[] */
/** @var $error string|null */
$this->layout('base', ['title' => 'People, but for the smartcard renewal']);
$testdates = [];
$today = new DateTimeImmutable();
?>
<h2>People (but list that you can easily copy paste into an email)</h2>

<?php if (isset($error)) : ?>
	<div class="alert alert-danger" role="alert">
		Error: <?= $this->e($error) ?>
	</div>
<?php endif ?>

<?php
$usersToPrint = [];
foreach ($users as $user) {
	$testDone = false;
	if (!isset($user['nsaccountlock']) || $user['nsaccountlock'] === null) {
		$usersToPrint[] = $user;
	}
}

usort($usersToPrint, function ($a, $b) {
	if ($a['sn'] !== $b['sn']) {
		return strcmp($a['sn'], $b['sn']);
	}
	return strcmp($a['cn'], $b['cn']);
});
?>

<p>Displaying all unlocked accounts except groups: <?= htmlspecialchars(implode(', ', $excludedGroups)); ?>.</p>

<pre>
<?php foreach ($usersToPrint as $user) :
	$name = $user['cn'];
	$firstQuotes = strpos($name, '"');
	if ($firstQuotes !== false) {
		$secondQuotes = strpos($name, '"', $firstQuotes + 1);
		if ($secondQuotes !== false) {
			$name = trim(trim(substr($name, 0, $firstQuotes)) . ' ' . trim(substr($name, $secondQuotes + 1)));
		}
	}
	echo $user["schacpersonaluniquecode"] . " " . $name . "\n";
	?>
<?php endforeach; ?>
</pre>

<p>Return to <a href="people.php">people list</a>.</p>