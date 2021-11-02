<?php

function safetyTest(array $user, ?array &$testdates, DateTimeImmutable $today) {
	if($user['safetytestdate'] !== null) {
		$daysDiff = (int) $today->diff($user['safetytestdate'])->format('%R%a');
		if($daysDiff > 0) {
			$sortkey = $user['sn'] . ' ' . $user['cn'] . ' ' . $user['uid'];
			if(!is_null($testdates)) {
				$testdates[$user['safetytestdate']->format('Y-m-d')][$sortkey] = $user;
			}
		}
		return [$daysDiff, true];
	}
	return [0, false];
}

function safetyTestIcon(int $testDaysDiff, bool $testScheduled, bool $signedSir): string {
	if($signedSir) {
		$sir = '';
	} else {
		$sir = '<i class="fas fa-print text-danger" title="SIR not yet signed!"></i>';
	}

	if($testScheduled) {
		if($testDaysDiff < 0) {
			// Test done in the past: checkmark and optionally printer
			return '<i class="fas fa-check"></i>' . $sir;
		} else if($testDaysDiff === 0) {
			// Test today: hourglass and optionally printer
			return '<i class="fas fa-hourglass"></i>' . $sir;
		} else {
			// Test in the future: hourglass only
			return '<i class="fas fa-hourglass"></i>';
		}
	} else {
		// Test not scheduled: X
		return '<i class="fas fa-times text-danger"></i>';
	}
}
