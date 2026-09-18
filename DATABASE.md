# Cơ sở dữ liệu của demo

Hệ quản trị: MySQL 8 hoặc MariaDB 10.5+. Database mẫu: `masan_web`, mã hóa utf8mb4.

| Bảng | Trường chính | Chức năng |
|---|---|---|
| admins | id, username, password_hash | Tài khoản đăng nhập quản trị, mật khẩu băm bằng PHP |
| offerings | id, kind, title, summary, description, created_at | Sản phẩm và dịch vụ; kind là software hoặc service |
| contacts | id, name, email, company, message, status, created_at | Liên hệ từ form; status là new hoặc done |
| rate_limits | bucket, attempts, window_start | Giới hạn yêu cầu đăng nhập và gửi liên hệ theo IP |

Mỗi bảng có khóa chính. username có ràng buộc UNIQUE. Bản demo đơn giản chưa có quan hệ khóa ngoại giữa bốn bảng. Thời gian hiển thị UTC.

## Hai cách khởi tạo
1. **Khuyến nghị:** tạo database/user, cấu hình app/config.php rồi chạy scripts/setup.php. Script tạo bảng, 8 mục nội dung mẫu và tài khoản admin.
2. **Nhập SQL bằng phpMyAdmin:** tạo và chọn database masan_web, Import database/masan_demo.sql. Sau đó cấu hình PHP và chạy scripts/setup.php để tạo admin.

Chọn một cách. Không cần nhập SQL trước nếu dùng cách 1. Không có mật khẩu quản trị mặc định. File SQL không chứa bản ghi admin; mật khẩu quản trị được người cài đặt tự đặt và băm bằng password_hash.

masan_demo.sql chứa cùng schema với schema.sql và cùng 8 nội dung với seed.json. File import không xóa bảng, không ghi đè các ID có sẵn, nhưng chỉ nên dùng cho database cài mới. Giữ backup nếu thao tác trên database đang dùng.

## Luồng dữ liệu
- Khách xem danh mục: index.php → PDO SELECT offerings → HTML.
- Khách gửi yêu cầu: POST index.php → kiểm tra CSRF/giới hạn/đầu vào → INSERT contacts → thông báo đã lưu.
- Quản trị đăng nhập: POST admin.php → SELECT admins → password_verify → session.
- Quản trị cập nhật nội dung: kiểm tra session + CSRF → INSERT/UPDATE/DELETE offerings.
- Quản trị liên hệ: SELECT contacts, UPDATE status.

## Kiểm tra khi trình bày
Thêm một dịch vụ trong admin và kiểm tra xuất hiện ở trang chủ. Gửi một yêu cầu từ trang liên hệ rồi xem ở admin. Sửa trạng thái, tải lại trang để chứng minh dữ liệu được lưu thật trong MySQL. Không có bước giả lập lưu vào localStorage.
