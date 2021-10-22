<?php

function safetyTest(array $user, ?array &$testdates, DateTimeImmutable $today): array {
	if($user['safetytestdate'] !== null) {
		$daysDiff = (int) $user['safetytestdate']->diff($today)->format('%R%a');
		if($daysDiff < 0) {
			$sortkey = $user['sn'] . ' ' . $user['cn'] . ' ' . $user['uid'];
			if(!is_null($testdates)) {
				$testdates[$user['safetytestdate']->format('Y-m-d')][$sortkey] = $user;
			}
			return [false, true];
		} else if($daysDiff === 0) {
			return [true, true];
		} else {
			return [true, false];
		}
	}
	return [false, false];
}

function safetyTestIcon(bool $testDone, bool $testToDo, bool $signedSir): string {
	if($signedSir) {
		$sir = '';
	} else {
		$sir = '<i class="fas fa-print text-danger" title="SIR not yet signed!"></i>';
	}

	if($testDone) {
		// Test done in the past: checkmark and optionally printer
		return '<i class="fas fa-check"></i>' . $sir;
	} else if($testToDo && $testDone) {
		// Test today: hourglass and printer OR checkmark
		if($signedSir) {
			return '<i class="fas fa-check"></i>';
		} else {
			return '<i class="fas fa-hourglass"></i>' . $sir;
		}
	} elseif($testToDo) {
		// Test in the future: hourglass only
		return '<i class="fas fa-hourglass"></i>';
	} else {
		// No test scheduled
		return '<i class="fas fa-times text-danger"></i>';
	}
}
