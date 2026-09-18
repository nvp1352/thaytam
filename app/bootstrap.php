<?php
declare(strict_types=1);
require_once __DIR__.'/images.php';
ini_set('display_errors', '0');
set_exception_handler(function(Throwable $ex): void { error_log((string)$ex); http_response_code(500); echo 'Hệ thống chưa xử lý được yêu cầu. Vui lòng kiểm tra cấu hình hoặc thử lại sau.'; });
ini_set('session.use_strict_mode', '1');
session_set_cookie_params(['httponly'=>true,'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off','samesite'=>'Lax']);
session_start();
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');
header("Content-Security-Policy: default-src 'self'; style-src 'self'; script-src 'self'; img-src 'self' data:; form-action 'self'; base-uri 'none'; frame-ancestors 'none'");
function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function db(): PDO {
    static $pdo;
    if ($pdo) return $pdo;
    $file=__DIR__.'/config.php';
    if (!is_file($file)) { http_response_code(503); exit('Chưa cấu hình database. Xem README.md để cài đặt website.'); }
    $c=require $file;
    try {
        $pdo=new PDO("mysql:host={$c['host']};port={$c['port']};dbname={$c['database']};charset=utf8mb4",$c['username'],$c['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
    } catch (PDOException $ex) { error_log($ex->getMessage()); http_response_code(503); exit('Chưa kết nối được cơ sở dữ liệu. Vui lòng kiểm tra cấu hình.'); }
    $pdo->exec("SET time_zone = '+00:00'");
    return $pdo;
}
function query(string $sql,array $args=[]): PDOStatement { $s=db()->prepare($sql); $s->execute($args); return $s; }
function token(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function csrf(): void { if(!hash_equals(token(),(string)($_POST['csrf']??''))) {http_response_code(403); exit('Phiên làm việc đã thay đổi. Hãy tải lại trang.');} }
function field(): string { return '<input type="hidden" name="csrf" value="'.e(token()).'">'; }
function go(string $path): never {header('Location: '.$path, true, 303); exit;}
function flash(string $s): void {$_SESSION['flash']=$s;}
function notice(): void {if(isset($_SESSION['flash'])) {echo '<div class="notice" role="status">'.e($_SESSION['flash']).'</div>';unset($_SESSION['flash']);}}
function textvalue(string $k,int $max): string { $v=trim((string)($_POST[$k]??'')); if($v==='' || preg_match_all('/./us',$v)>$max) throw new InvalidArgumentException('Vui lòng nhập đầy đủ thông tin, không vượt quá độ dài cho phép.'); return $v; }
function head(string $title): void { echo '<!doctype html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="description" content="Website mô phỏng MASAN: phần mềm, dịch vụ và giải pháp chuyển đổi số."><title>'.e($title).' | MASAN</title><link rel="icon" href="assets/favicon.svg"><link rel="stylesheet" href="assets/style.css"></head><body><div class="demo">ĐỒ ÁN MÔ PHỎNG · Không phải website chính thức của Masan hay FPT</div><header class="header"><a class="brand" href="index.php">MASAN<span>TECHNOLOGY</span></a><nav aria-label="Điều hướng chính"><a href="index.php#about">Giới thiệu</a><a href="index.php#solutions">Giải pháp</a><a href="index.php#services">Dịch vụ</a><a class="nav-cta" href="index.php#contact">Liên hệ tư vấn ↗</a></nav></header>'; }
function foot(): void {echo '<footer><a class="brand" href="index.php">MASAN<span>TECHNOLOGY</span></a><p>Website phục vụ học tập. Tên sản phẩm và nội dung là dữ liệu minh họa.</p><div class="footer-links"><a href="references.php">Nguồn tham khảo</a><a href="admin.php">Quản trị</a></div></footer></body></html>';}
