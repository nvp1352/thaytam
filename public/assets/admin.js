document.querySelectorAll('.delete-form').forEach(form => form.addEventListener('submit', event => { if (!confirm('Xóa nội dung này? Thao tác không thể hoàn tác.')) event.preventDefault(); }));
