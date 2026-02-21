<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hisobni o'chirish - Ustoz izla</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f8f9fa; color: #333; line-height: 1.7; }
        .header { background: linear-gradient(135deg, #1B1E4B, #2a2e6e); color: #fff; padding: 40px 20px; text-align: center; }
        .header h1 { font-size: 28px; margin-bottom: 8px; }
        .header p { opacity: 0.8; font-size: 14px; }
        .container { max-width: 600px; margin: 0 auto; padding: 32px 20px; }
        .card { background: #fff; border-radius: 12px; padding: 32px; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        h2 { color: #1B1E4B; font-size: 20px; margin-bottom: 16px; }
        p { font-size: 15px; color: #555; margin-bottom: 12px; }
        .warning { background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 16px; margin-bottom: 20px; }
        .warning p { color: #856404; margin: 0; font-size: 14px; }
        .form-group { margin-bottom: 16px; }
        label { display: block; font-weight: 600; margin-bottom: 6px; color: #1B1E4B; font-size: 14px; }
        input[type="email"], input[type="password"] { width: 100%; padding: 12px 16px; border: 1px solid #ddd; border-radius: 8px; font-size: 15px; transition: border 0.2s; }
        input:focus { outline: none; border-color: #1B1E4B; }
        .btn-delete { width: 100%; padding: 14px; background: #DC3545; color: #fff; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: background 0.2s; }
        .btn-delete:hover { background: #c82333; }
        .btn-delete:disabled { background: #ccc; cursor: not-allowed; }
        .data-list { background: #f8f9fa; border-radius: 8px; padding: 16px; margin: 16px 0; }
        .data-list li { font-size: 14px; color: #666; margin-bottom: 4px; }
        .success { background: #d4edda; border: 1px solid #28a745; border-radius: 8px; padding: 20px; text-align: center; display: none; }
        .success p { color: #155724; font-weight: 600; }
        .error { background: #f8d7da; border: 1px solid #dc3545; border-radius: 8px; padding: 12px; margin-bottom: 16px; display: none; }
        .error p { color: #721c24; margin: 0; font-size: 14px; }
        .footer { text-align: center; padding: 32px 20px; color: #999; font-size: 13px; }
        a { color: #1B1E4B; text-decoration: underline; }
        .loading { display: none; text-align: center; padding: 8px; }
        .loading::after { content: ''; display: inline-block; width: 20px; height: 20px; border: 3px solid #ddd; border-top-color: #DC3545; border-radius: 50%; animation: spin 0.8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div class="header">
        <h1>Hisobni o'chirish</h1>
        <p>Ustoz izla ilovasi</p>
    </div>

    <div class="container">
        <div class="card" id="delete-form-card">
            <h2>Hisobingizni o'chirish</h2>

            <div class="warning">
                <p><strong>Diqqat!</strong> Hisobingizni o'chirsangiz, quyidagi barcha ma'lumotlar butunlay o'chiriladi va bu amalni qaytarib bo'lmaydi.</p>
            </div>

            <p>O'chiriladigan ma'lumotlar:</p>
            <ul class="data-list">
                <li>Shaxsiy ma'lumotlaringiz (ism, email, telefon)</li>
                <li>Profil rasmi</li>
                <li>Joylashtirilgan barcha e'lonlar</li>
                <li>Yuklangan videolar</li>
                <li>Yozilgan sharhlar</li>
                <li>Saralangan e'lonlar</li>
                <li>Reyting va baholar</li>
                <li>Chat xabarlari</li>
                <li>Ustoz profili (agar mavjud bo'lsa)</li>
            </ul>

            <div class="error" id="error-box">
                <p id="error-text"></p>
            </div>

            <form id="delete-form" onsubmit="deleteAccount(event)">
                <div class="form-group">
                    <label for="email">Email manzilingiz</label>
                    <input type="email" id="email" name="email" placeholder="email@example.com" required>
                </div>

                <div class="form-group">
                    <label for="password">Parolingiz</label>
                    <input type="password" id="password" name="password" placeholder="Parolingizni kiriting" required>
                </div>

                <div class="form-group" style="margin-top: 20px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" id="confirm-check" required>
                        <span style="font-weight: normal; color: #555;">Hisobim va barcha ma'lumotlarim o'chirilishiga roziman</span>
                    </label>
                </div>

                <div class="loading" id="loading"></div>

                <button type="submit" class="btn-delete" id="delete-btn">Hisobimni o'chirish</button>
            </form>
        </div>

        <div class="success" id="success-box">
            <p style="font-size: 40px; margin-bottom: 12px;">&#10003;</p>
            <p>Hisobingiz muvaffaqiyatli o'chirildi.</p>
            <p style="font-weight: normal; color: #555; font-size: 14px; margin-top: 8px;">Barcha shaxsiy ma'lumotlaringiz tizimdan olib tashlandi.</p>
        </div>

        <div class="card" style="text-align: center;">
            <p>Hisobni o'chirish bo'yicha savollar uchun:</p>
            <p><strong>Email:</strong> support@ustozizla.uz</p>
            <p><strong>Telegram:</strong> @ustozizla</p>
        </div>
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} Ustoz izla. Barcha huquqlar himoyalangan.</p>
        <p style="margin-top: 8px;">
            <a href="{{ url('/') }}">Bosh sahifa</a> &middot;
            <a href="{{ url('/privacy-policy') }}">Maxfiylik siyosati</a>
        </p>
    </div>

    <script>
        async function deleteAccount(e) {
            e.preventDefault();

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const errorBox = document.getElementById('error-box');
            const errorText = document.getElementById('error-text');
            const loading = document.getElementById('loading');
            const deleteBtn = document.getElementById('delete-btn');

            errorBox.style.display = 'none';
            loading.style.display = 'block';
            deleteBtn.disabled = true;

            try {
                // 1. Login to get token
                const loginRes = await fetch('{{ url("/api/v1/auth/login") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ email, password })
                });

                const loginData = await loginRes.json();

                if (!loginRes.ok || !loginData.success) {
                    throw new Error(loginData.message || 'Email yoki parol noto\'g\'ri');
                }

                const token = loginData.data.access_token;

                // 2. Delete account
                const deleteRes = await fetch('{{ url("/api/v1/auth/delete-account") }}', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + token
                    },
                    body: JSON.stringify({ password })
                });

                const deleteData = await deleteRes.json();

                if (!deleteRes.ok || !deleteData.success) {
                    throw new Error(deleteData.message || 'Hisobni o\'chirishda xatolik');
                }

                // Success
                document.getElementById('delete-form-card').style.display = 'none';
                document.getElementById('success-box').style.display = 'block';

            } catch (err) {
                errorText.textContent = err.message;
                errorBox.style.display = 'block';
            } finally {
                loading.style.display = 'none';
                deleteBtn.disabled = false;
            }
        }
    </script>
</body>
</html>
