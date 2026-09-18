<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require __DIR__.'/../app/bootstrap.php';
if (query('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=?', ['admins'])->fetchColumn()) {
    if ((int)query('SELECT COUNT(*) FROM admins')->fetchColumn()>0) exit("Đã cài đặt. Không ghi đè tài khoản hoặc dữ liệu.\n");
}
$user=getenv('ADMIN_USER') ?: 'admin';
$password=getenv('ADMIN_PASSWORD') ?: '';
if(strlen($password)<12) exit("Đặt biến ADMIN_PASSWORD tối thiểu 12 ký tự trước khi chạy.\n");
$sql=file_get_contents(__DIR__.'/../database/schema.sql');
foreach(explode(';',$sql) as $statement) if(trim($statement)!=='') db()->exec($statement);
db()->beginTransaction();
try {
    query('INSERT INTO admins(username,password_hash) VALUES (?,?)',[$user,password_hash($password,PASSWORD_DEFAULT)]);
    if ((int)query('SELECT COUNT(*) FROM offerings')->fetchColumn()===0) {
        foreach(json_decode(file_get_contents(__DIR__.'/../database/seed.json'),true) as $row) query('INSERT INTO offerings(kind,title,summary,description) VALUES (?,?,?,?)',$row);
    }
    db()->commit(); echo "Cài đặt thành công. Đăng nhập tại admin.php bằng tài khoản đã đặt.\n";
} catch(Throwable $e) {db()->rollBack();throw $e;}
