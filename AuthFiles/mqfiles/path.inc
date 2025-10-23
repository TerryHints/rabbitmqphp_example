<?php
$path = '/opt/it490'; //changed to our created path 
    if (file_exists('local.ini'))
    {
        $parse = parse_ini_file('local.ini',true);
        if (isset($parse['paths']))
        {
            $path = $parse['paths']['libraries'];
        }
        else
        {
           trigger_error("local.ini missing paths field\n",E_USER_NOTICE); 
        }
    }
    set_include_path(get_include_path().PATH_SEPARATOR.$path);
?>
