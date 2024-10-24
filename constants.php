<?php

define('ROOT_DIR', __DIR__);
define('UPLOAD_WEB_DIR', 'uploads/');
define('UPLOAD_DIR', __DIR__ . '/web/' . UPLOAD_WEB_DIR);
define('TMP_DIR', __DIR__ . '/var/tmp/');

if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}
if (!is_dir(TMP_DIR)) {
    mkdir(TMP_DIR, 0777, true);
}