<?php
$db = new PDO('sqlite:database/database.sqlite');
$stmt = $db->query('SELECT slug FROM pages');
$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($res as $row) {
    echo $row['slug'] . "\n";
}
