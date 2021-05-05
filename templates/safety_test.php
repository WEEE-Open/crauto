<?php

function safetyTest(array $user, array &$testdates, DateTimeImmutable $today): array {
	if($user['safetytestdate'] !== null) {
		if((int) $user['safetytestdate']->diff($today)->format('%R%a') < 0) {
			$sortkey = $user['sn'] . ' ' . $user['cn'] . ' ' . $user['uid'];
			$testdates[$user['safetytestdate']->format('Y-m-d')][$sortkey] = $user;
			return [false, true];
		} else {
			return [true, false];
		}
	}
	return [false, false];
}

function safetyTestIcon(bool $testDone, bool $testToDo): string {
	if($testDone) {
		return '<i class="fas fa-check"></i>';
	} else if ($testToDo) {
		return '<i class="fas fa-hourglass"></i>';
	} else {
		return '<i class="fas fa-times text-danger"></i>';
	}
}
