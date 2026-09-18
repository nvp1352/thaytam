<?php
require __DIR__.'/../app/bootstrap.php';
require __DIR__.'/../app/rate.php';
header('Cache-Control: no-store');
$error='';
if(isset($_SESSION['admin']) && time()-($_SESSION['last_active']??0)>1800) unset($_SESSION['admin']);
if(isset($_SESSION['admin'])) $_SESSION['last_active']=time();
if($_SERVER['REQUEST_METHOD']==='POST') {
 csrf(); $action=(string)($_POST['action']??'');
 try {
  if($action==='login') {
   if(!allowed('login',10,900)) throw new InvalidArgumentException('Quá nhiều lần thử. Hãy thử lại sau 15 phút.');
   $admin=query('SELECT * FROM admins WHERE username=?',[(string)($_POST['username']??'')])->fetch();
   if(!$admin || !password_verify((string)($_POST['password']??''),$admin['password_hash'])) throw new InvalidArgumentException('Tên đăng nhập hoặc mật khẩu không đúng.');
   session_regenerate_id(true); $_SESSION['admin']=$admin['id']; $_SESSION['last_active']=time(); $_SESSION['csrf']=bin2hex(random_bytes(32)); go('admin.php');
  }
  if(!isset($_SESSION['admin'])) {http_response_code(401);exit('Cần đăng nhập.');}
  if($action==='logout') {$_SESSION=[];session_destroy();go('admin.php');}
  if($action==='save') {
   $kind=(string)($_POST['kind']??''); if(!in_array($kind,['software','service'],true)) throw new InvalidArgumentException('Loại nội dung không hợp lệ.');
   $values=[$kind,textvalue('title',150),textvalue('summary',500),textvalue('description',20000)];
   $id=(int)($_POST['id']??0);
   if($id>0) { $values[]=$id; query('UPDATE offerings SET kind=?,title=?,summary=?,description=? WHERE id=?',$values); }
   else query('INSERT INTO offerings(kind,title,summary,description) VALUES (?,?,?,?)',$values);
   flash('Đã lưu nội dung.');go('admin.php');
  }
  if($action==='delete') { query('DELETE FROM offerings WHERE id=?',[(int)($_POST['id']??0)]);flash('Đã xóa nội dung.');go('admin.php'); }
  if($action==='status') { $status=(string)($_POST['status']??'');if(!in_array($status,['new','done'],true))throw new InvalidArgumentException('Trạng thái không hợp lệ.');query('UPDATE contacts SET status=? WHERE id=?',[$status,(int)($_POST['id']??0)]);flash('Đã cập nhật yêu cầu.');go('admin.php?tab=contacts'); }
 } catch(InvalidArgumentException $ex) {$error=$ex->getMessage();}
}
head('Quản trị');
if(!isset($_SESSION['admin'])): ?>
<main class="login section"><p class="eyebrow">MASAN / QUẢN TRỊ NỘI DUNG</p><h1>Chào mừng trở lại.</h1><p>Đăng nhập bằng tài khoản tạo khi cài đặt.</p><?php if($error)echo '<p class="error" role="alert">'.e($error).'</p>';?><form method="post"><?=field()?><input type="hidden" name="action" value="login"><label>Tên đăng nhập<input name="username" required autocomplete="username"></label><label>Mật khẩu<input name="password" type="password" required autocomplete="current-password"></label><button class="button">Đăng nhập ↗</button></form></main>
<?php else:
$tab=($_GET['tab']??'')==='contacts'?'contacts':'content';
$edit=isset($_GET['edit']) ? query('SELECT * FROM offerings WHERE id=?',[(int)$_GET['edit']])->fetch():false;
if($error && ($_POST['action']??'')==='save') $edit=$_POST;
$count=query('SELECT COUNT(*) FROM contacts WHERE status=?',['new'])->fetchColumn();
?>
<main class="admin section"><div class="section-top"><div><p class="eyebrow">KHÔNG GIAN QUẢN TRỊ</p><h1>Quản lý website</h1></div><form method="post"><?=field()?><button class="secondary" name="action" value="logout">Đăng xuất</button></form></div><div class="tabs"><a class="<?=$tab==='content'?'active':''?>" href="admin.php">Sản phẩm & dịch vụ</a><a class="<?=$tab==='contacts'?'active':''?>" href="admin.php?tab=contacts">Yêu cầu liên hệ <b><?=(int)$count?></b></a></div><?php notice();if($error)echo '<p class="error" role="alert">'.e($error).'</p>';?>
<?php if($tab==='content'):?><div class="admin-layout"><section><h2>Nội dung hiện có</h2><?php $rows=query('SELECT * FROM offerings ORDER BY id')->fetchAll();if(!$rows)echo '<p>Chưa có nội dung. Thêm mục đầu tiên bằng biểu mẫu.</p>';foreach($rows as $row):?><article class="admin-item"><img class="admin-thumbnail" src="assets/<?=e(offering_image($row))?>" alt="<?=e($row['title'])?>" width="1536" height="1024" loading="lazy"><span class="tag"><?=$row['kind']==='software'?'PHẦN MỀM':'DỊCH VỤ'?></span><h3><?=e($row['title'])?></h3><p><?=e($row['summary'])?></p><div class="actions"><a href="admin.php?edit=<?=(int)$row['id']?>#editor">Sửa</a><a href="index.php?id=<?=(int)$row['id']?>">Xem</a><form class="delete-form" method="post"><?=field()?><input type="hidden" name="id" value="<?=(int)$row['id']?>"><button class="danger" name="action" value="delete">Xóa</button></form></div></article><?php endforeach;?></section><section id="editor" class="editor"><h2><?=$edit?'Sửa nội dung':'Thêm nội dung'?></h2><form method="post"><?=field()?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?=(int)($edit['id']??0)?>"><label>Loại nội dung<select name="kind"><option value="software">Phần mềm</option><option value="service" <?=($edit['kind']??'')==='service'?'selected':''?>>Dịch vụ</option></select></label><label>Tên<input name="title" required maxlength="150" value="<?=e($edit['title']??'')?>"></label><label>Mô tả ngắn<textarea name="summary" required maxlength="500" rows="3"><?=e($edit['summary']??'')?></textarea></label><label>Nội dung chi tiết<textarea name="description" required maxlength="20000" rows="7"><?=e($edit['description']??'')?></textarea></label><button class="button">Lưu nội dung</button> <a href="admin.php">Hủy</a></form></section></div>
<?php else:
$page=max(1,(int)($_GET['page']??1));$offset=($page-1)*20;$total=(int)query('SELECT COUNT(*) FROM contacts')->fetchColumn();$contacts=query('SELECT * FROM contacts ORDER BY id DESC LIMIT 20 OFFSET '.$offset)->fetchAll();if(!$contacts)echo '<div class="empty">Chưa có yêu cầu liên hệ tại trang này.</div>';foreach($contacts as $c):?><article class="contact-item"><div class="section-top"><div><h3><?=e($c['name'])?></h3><p><?=e($c['email'])?> · <?=e($c['company'])?></p></div><span class="tag"><?=$c['status']==='new'?'MỚI':'ĐÃ XỬ LÝ'?></span></div><p class="message"><?=nl2br(e($c['message']))?></p><div class="section-top"><small><?=e($c['created_at'])?> UTC</small><form method="post"><?=field()?><input type="hidden" name="action" value="status"><input type="hidden" name="id" value="<?=(int)$c['id']?>"><input type="hidden" name="status" value="<?=$c['status']==='new'?'done':'new'?>"><button class="secondary"><?=$c['status']==='new'?'Đánh dấu đã xử lý':'Chuyển về mới'?></button></form></div></article><?php endforeach;?><div class="actions"><?php if($page>1):?><a href="admin.php?tab=contacts&page=<?=$page-1?>">← Trang trước</a><?php endif;if($offset+20<$total):?><a href="admin.php?tab=contacts&page=<?=$page+1?>">Trang sau →</a><?php endif;?></div><?php endif;?></main><script src="assets/admin.js"></script>
<?php endif;foot(); ?>
