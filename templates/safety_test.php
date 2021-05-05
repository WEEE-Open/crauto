<?php

function safetyTest(array $user, ?array &$testdates, DateTimeImmutable $today): array {
	if($user['safetytestdate'] !== null) {
		if((int) $user['safetytestdate']->diff($today)->format('%R%a') < 0) {
			$sortkey = $user['sn'] . ' ' . $user['cn'] . ' ' . $user['uid'];
			if(!is_null($testdates)) {
				$testdates[$user['safetytestdate']->format('Y-m-d')][$sortkey] = $user;
			}
			return [false, true];
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
		return '<i class="fas fa-check"></i>' . $sir;
	} else if ($testToDo) {
		return '<i class="fas fa-hourglass"></i>';
	} else {
		return '<i class="fas fa-times text-danger"></i>';
	}
}
