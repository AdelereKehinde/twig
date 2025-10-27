<?php
session_start();
echo "Testing redirect...<br>";
header('Location: /dashboard');
exit;