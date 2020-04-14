<?php
header("Content-Type: text/html; charset=utf-8");
require_once("api/ActiveCampaign.class.php");
$ac = new ActiveCampaign("https://cyptus.api-us1.com", "1d5cd52490ef3e80e2eaad53bdea81d7cdc1deb3d43ae4a1caf15b5ec0ebd56dac894bca");
// Проверка прав
if (!(int)$ac->credentials_test()) {
    echo "<p>Access denied: Invalid credentials (URL and/or API key).</p>";
    exit();
}
$ac->version(1);