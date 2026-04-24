<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mini game winner</title>
</head>
<body>
    <h2>Chuc mung ban da dan dau mini game tuan nay</h2>
    <p>Xin chao {{ $user->name }},</p>
    <p>
        Ban la nguoi du doan chinh xac cao nhat trong tuan
        {{ $weeklyScore->week_start->format('Y-m-d') }} den {{ $weeklyScore->week_end->format('Y-m-d') }}
        voi tong so lan dung la {{ $weeklyScore->correct_count }}.
    </p>
    <p>
        Vui long dang nhap va cap nhat thong tin STK tai Dashboard de nhan thuong.
    </p>
    <p>Tran trong.</p>
</body>
</html>
