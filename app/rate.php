<?php
function allowed(string $scope, int $max, int $window): bool {
    $key=hash('sha256',$scope.'|'.($_SERVER['REMOTE_ADDR']??'local')); $now=time();
    query('INSERT IGNORE INTO rate_limits(bucket,attempts,window_start) VALUES (?,0,?)',[$key,$now]);
    query('UPDATE rate_limits SET attempts=0, window_start=? WHERE bucket=? AND window_start < ?',[$now,$key,$now-$window]);
    query('UPDATE rate_limits SET attempts=attempts+1 WHERE bucket=?',[$key]);
    $n=query('SELECT attempts FROM rate_limits WHERE bucket=?',[$key])->fetchColumn();
    return (int)$n <= $max;
}
