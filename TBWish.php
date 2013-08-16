<?php

	shell_exec( "php WishPage.php --page ".($_GET["page"]));
	header('Location: http://tieteentermipankki.fi/wiki/'.($_GET["page"]));
	die();	


