<?php
$db = new PDO('sqlite:../data/unlink.db');

$sqlDir = __DIR__;
echo "SQL Dir: $sqlDir <br/>";
$sqlFiles = glob($sqlDir . '/*.sql');
echo "SQL Files: " . print_r($sqlFiles, true) . "<br/>";

foreach ($sqlFiles as $file) {
    echo "Processing: $file <br/>";
    $sql = file_get_contents($file);

    try {
        $db->exec($sql);
        echo "Executed: $file <br/>";
    } catch (PDOException $e) {
        echo "Error executing $file: " . $e->getMessage() . "<br/>";
    }
}
