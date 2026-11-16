<?php
// Boot files run before Common.php defines env(). Exercise the web branch in
// an isolated PHP process, since CLI short-circuiting hid the original failure.
$source = file_get_contents(dirname(__DIR__, 2).'/app/Config/Boot/development.php');
$source = str_replace("PHP_SAPI === 'cli'", 'false', $source);
foreach (['unset'=>false, 'true'=>true, 'false'=>false] as $flag=>$expected) {
    $setup = 'unset($_ENV["GEREH_DEBUG_ENABLED"], $_SERVER["GEREH_DEBUG_ENABLED"]); putenv("GEREH_DEBUG_ENABLED");';
    if ($flag !== 'unset') $setup .= '$_ENV["GEREH_DEBUG_ENABLED"]='.var_export($flag,true).';';
    $script = tempnam(sys_get_temp_dir(),'gereh-boot-');
    file_put_contents($script, '<?php '.$setup.' ?>'.$source.' if (CI_DEBUG !== '.var_export($expected,true).') exit(2);');
    exec(escapeshellarg(PHP_BINARY).' -n '.escapeshellarg($script).' 2>&1', $output, $code);
    unlink($script);
    if ($code !== 0) throw new RuntimeException(implode("\n",$output));
    echo "PASS isolated web boot: $flag\n";
}
