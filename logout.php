<?php
require_once __DIR__ . '/includes/functions.php';

unset($_SESSION['user']);
session_regenerate_id(true);
flash('info', 'Kamu sudah logout.');
redirect('index.php');
