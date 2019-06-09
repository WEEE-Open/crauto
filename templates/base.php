<?php
/** @var string $title */
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="bootstrap.min.css">
    <title><?= $this->e($title) ?> - Crauto</title>
	<style>
		.navbar {
			border-bottom: 0.5rem solid #00983a;
			margin-bottom: 0.5rem;
		}

		a {
			color: #00983a;
		}

		a:visited {
			color: #00692b;
		}

		a:hover {
			color: #00cc4e;
		}

		a:active {
			color: #33ff81;
		}
	</style>
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
