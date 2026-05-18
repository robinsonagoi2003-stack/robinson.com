<?php
require_once __DIR__ . '/functions.php';
session_destroy();
session_start();
flash('You have been logged out.', 'info');
redirect('login.php');
