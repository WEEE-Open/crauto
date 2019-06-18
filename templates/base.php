<?php
/** @var string $title */
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= $this->e($title) ?> - Crauto</title>
	<style>
		.navbar {
			border-bottom: 0.5rem solid #00983a;
			margin-bottom: 0.5rem;
		}

		a:not(.btn), a:link:not(.btn) {
			color: #00983a;
		}

		a:visited:not(.btn), a:link:visited:not(.btn) {
			color: #00692b;
		}

		a:hover:not(.btn), a:link:hover:not(.btn) {
			color: #00cc4e;
		}

		a:active:not(.btn), a:link:active:not(.btn) {
			color: #33ff81;
		}

		tr.locked {
			background: #d6d8db;
			color: #666;
		}

		tr.locked a {
			color: #333;
		}
	</style>
	<link rel="stylesheet" href="bootstrap.min.css">
</head>
<body>
<?= $this->fetch('navbar') ?>
<div class="container">
<?= $this->section('content') ?>
</div>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</body>
</html>
