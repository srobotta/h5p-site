<?php

define('ROOT', dirname(__FILE__));
define('WEB_ROOT', ROOT . DIRECTORY_SEPARATOR . 'html');
define('DB_FILE', ROOT . DIRECTORY_SEPARATOR . 'db.json');
define('DATA_DIR', WEB_ROOT . DIRECTORY_SEPARATOR . 'data');
define('WEB_DATA_DIR', '/data');

/**
 * Generate a random ID.
 * 
 * @return string The generated ID.
 */
function genId(): string {
    $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $length = 10;
    $max = mb_strlen($keyspace, '8bit') - 1;
    for ($i = 0; $i < $length; ++$i) {
        $pieces []= $keyspace[random_int(0, $max)];
    }
    return implode('', $pieces);
}

/**
 * Recursively remove a directory.
 * 
 * @param string $dir The directory to remove.
 */
function rrmdir(string $dir) { 
    if (is_dir($dir)) { 
        $objects = scandir($dir);
        foreach ($objects as $object) { 
            if ($object != "." && $object != "..") {
                $obj = $dir. DIRECTORY_SEPARATOR . $object;
                if (is_dir($obj) && !is_link($obj)) {
                    rrmdir($obj);
                } else {
                    @unlink($obj); 
                }
            } 
        }
        rmdir($dir); 
    } 
}

/**
 * Get all entries from the database.
 */
function getAllEntries(): array {
    $json = @file_get_contents(DB_FILE);
    $entries = json_decode($json ?: '', true);
    if (!is_array($entries)) {
        return [];
    }
    return $entries;
}

/**
 * Get a single entry from the database by ID.
 * 
 * @param string $id The ID of the entry to get.
 */
function getEntry($id): ?array {
    $entries = getAllEntries(DATA_DIR);
    if (isset($entries[$id])) {
        return $entries[$id];
    }
    return null;
}

/**
 * Delete an entry from the database.
 * 
 * @param string $id The ID of the entry to delete.
 */
function deleteEntry(string $id) {
    $entries = getAllEntries(DATA_DIR);
    if (isset($entries[$id])) {
        rrmdir(DATA_DIR . DIRECTORY_SEPARATOR . $id);
        unset($entries[$id]);
        file_put_contents(DB_FILE, json_encode($entries));
    }
}

/**
 * Handle the upload of a new H5P file.
 * Create a new directory for the file, extract the contents of the zip file,
 * and save the metadata to the database. If an error occurs, redirect to the
 * home page with an error code. If successful, redirect to the new entry.
 */
function handleUpload() {
    $file = $_FILES['file'] ?? null;
    if (!$file) {
        header('Location: /');
        return;
    }
    $id = genId();
    $newDir = DATA_DIR . DIRECTORY_SEPARATOR . $id;
    mkdir($newDir, 0777, true);
    if (!is_dir($newDir)) {
        header('Location: /?error=1');
        return;
    }
    if (!move_uploaded_file($file['tmp_name'], $newDir . DIRECTORY_SEPARATOR . $file['name'])) {
        rrmdir($newDir);
        header('Location: /?error=2');
        return;
    }
    $zip = new ZipArchive();
    $res = $zip->open($newDir . DIRECTORY_SEPARATOR . $file['name']);
    if ($res === TRUE) {
        $zip->extractTo($newDir);
        $zip->close();
    } else {
        rrmdir($newDir);
        header('Location: /?error=3');
        return;
    }
    $content = file_get_contents($newDir . DIRECTORY_SEPARATOR . 'h5p.json');
    $h5p = json_decode($content, true);
    if (!is_array($h5p)) {
        rrmdir($newDir);
        header('Location: /?error=4');
        return;
    }
    $entry = [
        'title' => $h5p['title'] ?? 'No title',
        'created' => time(),
    ];
    $entries = getAllEntries(DATA_DIR);
    $entries[$id] = $entry;
    if (file_put_contents(DB_FILE, json_encode($entries))) {
        header('Location: /?id=' . $id);
    } else {
        header('Location: /?error=5');
    }
}

