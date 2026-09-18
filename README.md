# MASAN — Website PHP + MySQL

Website mô phỏng phục vụ đồ án, bằng PHP thuần 8.1+ và MySQL 8 / MariaDB 10.5+. Không cần Composer, Node.js hoặc framework.

## Chức năng
- Trang giới thiệu, danh mục phần mềm và dịch vụ lấy từ MySQL, trang chi tiết.
- Form liên hệ lưu vào database (không gửi email).
- Quản trị: đăng nhập, thêm/sửa/xóa sản phẩm và dịch vụ, xem liên hệ, đánh dấu đã xử lý, phân trang.
- Giao diện responsive tiếng Việt, dùng tài nguyên nội bộ.
- PDO prepared statements, escape HTML, CSRF token, mật khẩu băm, session, giới hạn lần đăng nhập và gửi form theo IP.

## Lưu ý nội dung
Tên MASAN được giữ theo đề bài. Văn bản gốc dẫn tới thông tin/sản phẩm mang tên FPT, vì vậy đây không phải thông tin doanh nghiệp Masan đã được xác minh. Website ghi rõ là mô phỏng, không dùng số liệu 1988, 70.000 nhân sự, 29 quốc gia hoặc khẳng định quan hệ đại lý là sự thật. Các tên MASAN eContract, Work+, HRM+, Info+, OncallCX là dữ liệu minh họa của đề bài. Trước khi xuất bản thương mại, thay nội dung bằng thông tin đã xác minh và được phép sử dụng.

## Cách chạy nhanh trên Windows / XAMPP
1. Bật MySQL trong XAMPP. Cần PHP 8.1 trở lên với extension pdo_mysql.
2. Giải nén thư mục `masan-php` vào `C:\xampp\htdocs\masan-php`.
3. Mở phpMyAdmin, tab SQL và chạy (đổi mật khẩu trong ví dụ):

```sql
CREATE DATABASE masan_web CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'masan_app'@'127.0.0.1' IDENTIFIED BY 'THAY_MAT_KHAU_DB';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE ON masan_web.* TO 'masan_app'@'127.0.0.1';
```

Nếu DB xác định máy khách là `localhost` thay vì `127.0.0.1`, tạo user cho host `localhost` và điều chỉnh host trong cấu hình cho phù hợp.

4. Sao chép `app/config.example.php` thành `app/config.php`; nhập mật khẩu DB đã đặt.
5. Mở PowerShell trong thư mục dự án, đặt mật khẩu quản trị riêng (tối thiểu 12 ký tự) và chạy:

```powershell
$env:ADMIN_USER = "admin"
$env:ADMIN_PASSWORD = "THAY_BANG_MAT_KHAU_RIENG_12_KY_TU"
C:\xampp\php\php.exe scripts\setup.php
Remove-Item Env:ADMIN_PASSWORD
C:\xampp\php\php.exe -S 127.0.0.1:8080 -t public
```

6. Mở http://127.0.0.1:8080 và http://127.0.0.1:8080/admin.php.

Chạy bằng `-t public` giúp chỉ công khai thư mục `public`. Không mở toàn bộ thư mục dự án ra Internet. PHP built-in server chỉ dành cho thử nghiệm cục bộ. Tài khoản quản trị không có mật khẩu mặc định; dùng mật khẩu bạn đặt ở bước 5.

## Rocky Linux 9 / Apache
Lệnh quản trị dưới đây chạy bằng tài khoản có sudo. Nếu PHP đang thấp hơn 8.1, chọn stream PHP 8.1 hoặc mới hơn trong kho của máy trước khi cài.

```bash
sudo dnf install httpd php php-cli php-fpm php-mysqlnd mariadb-server
php -v
sudo systemctl enable --now mariadb httpd php-fpm
sudo mariadb
```

Chạy các câu SQL tạo database/user ở hướng dẫn trên. Đặt dự án tại `/var/www/masan-php`, cấu hình `app/config.php` và chạy cài đặt:

```bash
cd /var/www/masan-php
export ADMIN_USER=admin
read -rsp 'Mat khau quan tri (>=12 ky tu): ' ADMIN_PASSWORD
export ADMIN_PASSWORD
php scripts/setup.php
unset ADMIN_PASSWORD
```

Tạo `/etc/httpd/conf.d/masan.conf`:

```apache
<VirtualHost *:80>
    ServerName masan.test
    DocumentRoot /var/www/masan-php/public
    <Directory /var/www/masan-php/public>
        Options -Indexes
        AllowOverride None
        Require all granted
        DirectoryIndex index.php
    </Directory>
</VirtualHost>
```

```bash
sudo chown -R root:apache /var/www/masan-php
sudo find /var/www/masan-php -type d -exec chmod 750 {} \;
sudo find /var/www/masan-php -type f -exec chmod 640 {} \;
sudo restorecon -Rv /var/www/masan-php
sudo setsebool -P httpd_can_network_connect_db 1
sudo httpd -t
sudo systemctl reload httpd
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --reload
```

Trên máy khách, thêm `IP_MAY_ROCKY masan.test` vào file hosts, rồi mở http://masan.test. Nếu firewalld không hoạt động thì bỏ hai lệnh firewall. Khi dùng Internet, cấu hình HTTPS cho Apache và tên miền thật; không dùng HTTP để đăng nhập quản trị trên Internet.

Sau cài đặt có thể thu hồi quyền CREATE của tài khoản database:

```sql
REVOKE CREATE ON masan_web.* FROM 'masan_app'@'127.0.0.1';
```

## Cấu trúc
- `public/index.php`: website và tiếp nhận liên hệ.
- `public/admin.php`: quản trị nội dung, liên hệ và đăng nhập.
- `public/assets/`: CSS, JavaScript và favicon.
- `app/bootstrap.php`: PDO, session, CSRF và hàm giao diện.
- `app/rate.php`: giới hạn yêu cầu theo IP trong database.
- `app/config.example.php`: cấu hình mẫu; tự tạo `config.php`.
- `database/schema.sql`: cấu trúc bảng.
- `database/seed.json`: dữ liệu mẫu.
- `scripts/setup.php`: tạo bảng, dữ liệu và quản trị viên bằng dòng lệnh.

Các bảng: admins, offerings, contacts, rate_limits. Không cần nhập schema thủ công nếu đã chạy setup. Chạy lại setup không ghi đè tài khoản có sẵn.

## Kiểm tra sau cài đặt
1. Mở trang chủ, chọn một giải pháp và kiểm tra trang chi tiết.
2. Gửi form liên hệ, xác nhận thông báo đã lưu.
3. Đăng nhập admin, mở “Yêu cầu liên hệ”, kiểm tra dữ liệu và cập nhật trạng thái.
4. Thêm, sửa và xóa một nội dung thử; xác nhận trang chủ thay đổi.
5. Đăng xuất, xác nhận không thể xem dữ liệu quản trị.

Ứng dụng đặt múi giờ kết nối database về UTC; trang quản trị hiển thị thời gian UTC. Với tải thực tế cao cần thêm cơ chế chống spam, backup và quản lý log phù hợp.

## Tình trạng kiểm tra trong môi trường tạo tệp
Môi trường tạo tệp không có PHP/MySQL nên chưa chạy kiểm thử tích hợp trực tiếp. Mã nguồn và cấu trúc gói đã được rà soát; cần thực hiện các bước kiểm tra trên máy XAMPP hoặc Rocky của bạn.

## Bản giao diện có hình ảnh
- Banner văn phòng công nghệ ở đầu trang và ảnh trung tâm dữ liệu trước phần dịch vụ.
- Trang chi tiết sử dụng ảnh minh họa tương ứng; ảnh nằm trong `public/assets/`, không phụ thuộc nguồn ảnh bên ngoài.
- Ảnh được tạo bằng AI cho demo, không phải hình cơ sở hoặc nhân viên thật của MASAN/FPT.
- Các chức năng PHP/MySQL và cấu trúc database giữ nguyên. Nếu đã cài bản trước, chỉ cập nhật mã nguồn và assets; giữ `app/config.php` của bạn, không cần chạy setup lại.

Kiểm tra bản ảnh: xác nhận các file ảnh PNG hợp lệ và đường dẫn tài nguyên tồn tại. Chưa chạy kiểm tra hiển thị bằng trình duyệt hoặc tích hợp PHP/MySQL trong môi trường tạo tệp.

## Bản bổ sung nguồn tham khảo và SQL
- `public/references.php`: trang nguồn tham khảo, liên kết ở chân trang và trang chi tiết.
- `NGUON_THAM_KHAO.md`: danh sách website thật và phần được tham khảo, dùng trong báo cáo.
- `DATABASE.md`: bảng dữ liệu, luồng xử lý và kịch bản trình bày.
- `database/masan_demo.sql`: cấu trúc 4 bảng và 8 nội dung mẫu, nhập vào database đã tạo bằng phpMyAdmin. Sau khi nhập, chạy setup.php để tạo admin. Có thể bỏ bước import nếu dùng setup.php từ đầu.
- `app/sources.json`: dữ liệu của trang nguồn tham khảo.

Đây là bộ mã nguồn website có backend, không phải ảnh chụp hoặc bản HTML tĩnh. Tuy nhiên môi trường tạo tệp không có PHP/MySQL nên chưa xác nhận chạy tích hợp thành công. Hãy làm các bước kiểm tra sau cài đặt ở trên trước khi thuyết trình.

## Bản đầy đủ hình ảnh
- Tổng cộng 8 ảnh: văn phòng công nghệ, trung tâm dữ liệu, phần mềm doanh nghiệp, ký hợp đồng điện tử, làm việc nhóm, chăm sóc khách hàng, tư vấn chuyển đổi số và thiết kế UX/UI.
- 4 sản phẩm và 4 dịch vụ mẫu đều có ảnh minh họa; trang chi tiết dùng cùng ảnh của mục đã chọn.
- Phần giới thiệu và liên hệ có ảnh, quản trị có ảnh thu nhỏ để nhận diện nội dung.
- Tất cả ảnh nằm trong public/assets; các ảnh dưới màn hình đầu tải chậm bằng loading="lazy".
- app/images.php ánh xạ tên nội dung mẫu sang ảnh. Khi đổi tên/thêm nội dung mới, ảnh mặc định vẫn hiển thị; muốn dùng ảnh khác, chỉnh ánh xạ trong file này. Chưa có chức năng upload ảnh qua quản trị.
- Database giữ nguyên. Với bản đã cài, cập nhật mã PHP/CSS và toàn bộ assets, giữ app/config.php và dữ liệu; không cần nhập SQL lại.
