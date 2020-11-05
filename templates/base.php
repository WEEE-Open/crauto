<?php
/** @var string $title */
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= $this->e($title) ?> - Crauto</title>
    <link rel="stylesheet" href="crauto.css">
    <link rel="stylesheet" href="https://unpkg.com/bootstrap-table@1.18.0/dist/bootstrap-table.min.css">
	<link rel="stylesheet" href="bootstrap.min.css">
</head>
<body>
<?= $this->fetch('navbar') ?>
<div class="container">
<?= $this->section('content') ?>
</div>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
<script src="https://kit.fontawesome.com/c0b7a90268.js" crossorigin="anonymous"></script>
<script src="https://unpkg.com/bootstrap-table@1.18.0/dist/bootstrap-table.min.js"></script>
</body>
</html>
