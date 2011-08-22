<?php

define('MOD_NCBSF_PATH',rtrim(dirname(__FILE__), DIRECTORY_SEPARATOR));
$folders = explode(DIRECTORY_SEPARATOR,MOD_NCBSF_PATH);
define('MOD_NCBSF_DIR',rtrim(array_pop($folders),DIRECTORY_SEPARATOR));
unset($folders);
