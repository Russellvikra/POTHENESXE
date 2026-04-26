<?php
session_start();
// Action: Destroy current session and return user to login page.
session_destroy();
header('Location: login.php', true, 302);
exit;
