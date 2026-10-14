<?php
// halaman/login/logout.php
$_SESSION = [];
session_destroy();
redirect('login');