<?php /* @var $isAdmin bool */ ?>
<?php /* @var $authenticated bool */ ?>
<nav class="navbar navbar-expand-sm navbar-dark bg-dark">
	<div class="container">
	    <a class="navbar-brand" href="/">
		    <img src="weee.png" height="26" class="d-inline-block align-middle" alt="WEEE Open">
	    </a>
	    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
	        <span class="navbar-toggler-icon"></span>
	    </button>
	    <div class="collapse navbar-collapse" id="navbarNav">
	        <ul class="navbar-nav">
		        <?php if($authenticated ?? true): ?>
		            <li class="nav-item">
		                <a class="nav-link" href="/personal.php">Personal</a>
		            </li>
		            <li class="nav-item">
		                <a class="nav-link" href="/authentication.php">Authentication</a>
		            </li>
		            <li class="nav-item">
		                <a class="nav-link" href="/sessions.php">Sessions</a>
		            </li>
			        <?php if($isAdmin): ?>
		            <li class="nav-item">
		                <a class="nav-link" href="/accounts.php">Accounts</a>
		            </li>
			        <?php endif ?>
		            <li class="nav-item">
		                <a class="nav-link" href="/logout.php">Logout</a>
		            </li>
		        <?php else: ?>
			        <li class="nav-item">
				        <a class="nav-link" href="/login.php">Login</a>
			        </li>
		        <?php endif ?>
	        </ul>
	    </div>
	</div>
</nav>
