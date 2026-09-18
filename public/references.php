<?php
require __DIR__.'/../app/bootstrap.php';
$sources=json_decode(file_get_contents(__DIR__.'/../app/sources.json'),true,512,JSON_THROW_ON_ERROR);
head('Nguồn tham khảo');
?>
<main class="section references"><p class="eyebrow">TÀI LIỆU CỦA ĐỒ ÁN</p><h1>Nguồn tham khảo</h1><p class="lead">Các website thật được dùng để tham khảo nội dung và cách tổ chức thông tin của bản demo.</p><p>Ngày truy cập: 18/09/2026. MASAN Technology trong bản demo là doanh nghiệp giả định theo đề bài, không phải website chính thức của Masan hoặc FPT. Các nguồn dưới đây không xác nhận quan hệ hợp tác hay đại lý cho bản demo.</p>
<div class="reference-list">
<?php foreach($sources as $i=>$source): ?>
<article class="admin-item"><span class="eyebrow">NGUỒN <?= (int)$i+1 ?></span><h2><a href="<?=e($source['url'])?>" target="_blank" rel="noopener noreferrer"><?=e($source['title'])?> ↗</a></h2><p><?=e($source['use'])?></p><p class="muted"><?=e($source['applied'])?></p><a class="source-url" href="<?=e($source['url'])?>" target="_blank" rel="noopener noreferrer"><?=e($source['url'])?></a></article>
<?php endforeach; ?>
</div><h2>Phạm vi bản demo</h2><p>Mã PHP, giao diện và database được viết riêng cho đồ án; không phải mã nguồn của các doanh nghiệp tham khảo. Website có danh mục, trang chi tiết, form lưu liên hệ và quản trị nội dung. Các giải pháp ký hợp đồng, CRM và quản trị nhân sự chỉ được giới thiệu, chưa triển khai thành ứng dụng nghiệp vụ riêng.</p><h2>Hình ảnh</h2><p>Ảnh văn phòng, sản phẩm, dịch vụ, trung tâm dữ liệu và hỗ trợ khách hàng được tạo bằng AI để minh họa. Đây không phải ảnh nhân viên hay cơ sở thực tế của Masan/FPT, và không lấy từ những website trên.</p><a class="button" href="index.php">Về trang chủ</a></main>
<?php foot(); ?>
