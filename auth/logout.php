<?php
require_once __DIR__ . '/../includes/functions.php';

$_SESSION = [];
session_destroy();
session_start();
set_flash("You have been logged out.", "success");
redirect('/index.php');
