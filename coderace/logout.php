<?php
session_start(); // Kailangan ito para ma-access ang session na ititigil
session_unset(); // Inaalis ang lahat ng session variables
session_destroy(); // Sinisira ang buong session data

// NAPAKAHALAGA: Dito itatapon ang user pagkatapos mag-logout
header("Location: login.php"); 
exit(); // Pinapatigil ang script para siguradong mag-re-redirect
?>  