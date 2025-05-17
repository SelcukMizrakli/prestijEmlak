<?php
session_start();
include("ayar.php"); // Veritabanı bağlantısı $baglan değişkeni üzerinden yapılmalı

// Eğer kullanıcı zaten giriş yapmışsa anasayfaya yönlendir.
if (isset($_SESSION['giris']) || (isset($_SESSION['uyeAd']) && isset($_SESSION['uyeMail']))) {
  header("Location: index.php");
  exit();
}

// POST isteği geldiyse hangi formun gönderildiğine bakıyoruz.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // Giriş işlemi: gEmail ve gSifre alanları mevcutsa
  if (isset($_POST['gEmail']) && isset($_POST['gSifre'])) {
    $kullaniciMail = trim($_POST["gEmail"]);
    $sifre = trim($_POST["gSifre"]);

    // t_uyeler tablosunda girilen e-posta ile kullanıcıyı arıyoruz.
    $stmt = $baglan->prepare("SELECT * FROM t_uyeler WHERE uyeMail = ?");
    $stmt->bind_param("s", $kullaniciMail);
    $stmt->execute();
    $sorgu = $stmt->get_result();

    if ($sorgu->num_rows > 0) {
      $kullanici = $sorgu->fetch_assoc();
      // Girilen şifreyi kontrol ediyoruz (şifreler hash'lenmiş olmalı)
      if (password_verify($sifre, $kullanici['uyeSifre'])) {
        // Giriş başarılı: Session değişkenlerini ayarla
        $_SESSION["giris"] = true;
        $_SESSION["uyeID"] = $kullanici["uyeID"];
        $_SESSION["uyeAd"] = $kullanici["uyeAd"];
        $_SESSION["uyeMail"] = $kullanici["uyeMail"];
        $_SESSION["uyeYetkiID"] = $kullanici["uyeYetkiID"]; // uyeYetki yerine uyeYetkiID kullan

        // İsteğe bağlı: Çerez oluşturma
        setcookie("kullanici", "msb", time() + 3600, "/");

        echo "<script>window.location.href='index.php';</script>";
        exit();
      } else {
        echo "<script>
                        alert('HATALI KULLANICI BİLGİSİ!');
                        window.location.href='girisYap.php';
                      </script>";
        exit();
      }
    } else {
      echo "<script>
                    alert('HATALI KULLANICI BİLGİSİ!');
                    window.location.href='girisYap.php';
                  </script>";
      exit();
    }
  }
  // Kayıt işlemi: kEmail, kSifre ve kSifreTekrar alanları varsa
  else if (isset($_POST['kEmail']) && isset($_POST['kSifre']) && isset($_POST['kSifreTekrar'])) {
    $ad = trim($_POST['kAd']);
    $soyad = trim($_POST['kSoyad']);
    $telNo = trim($_POST['kTelNo']);
    $email = trim($_POST['kEmail']);
    $password = $_POST['kSifre'];
    $password_repeat = $_POST['kSifreTekrar'];

    // Girilen e-posta ile daha önce kayıtlı hesap var mı kontrol et
    $emailCheck = $baglan->prepare("SELECT uyeMail FROM t_uyeler WHERE uyeMail = ?");
    $emailCheck->bind_param("s", $email);
    $emailCheck->execute();
    $result = $emailCheck->get_result();

    if ($result->num_rows > 0) {
      echo "<script>
                    alert('Bu e-posta adresi zaten kullanılıyor!');
                    window.location.href='girisYap.php';
                  </script>";
      exit();
    }
    $emailCheck->close();

    // Şifrelerin eşleştiğini kontrol et
    if ($password === $password_repeat) {
      // Şifreyi hash'le
      $hashed_password = password_hash($password, PASSWORD_DEFAULT);
      // Kullanıcıyı veritabanına ekle
      $stmt = $baglan->prepare("INSERT INTO t_uyeler (uyeAd, uyeSoyad, uyeTelNo, uyeMail, uyeSifre, uyeAdresID, uyeYetkiID, uyeAktiflikDurumu) VALUES (?, ?, ?, ?, ?,?,?,?)");
      $null = null;
      $defaultYetki = 2;
      $defaultAktiflik = 1; // Varsayılan aktiflik durumu
      $stmt->bind_param("sssssiii", $ad, $soyad, $telNo, $email, $hashed_password, $null, $defaultYetki, $defaultAktiflik);

      if ($stmt->execute()) {
        // Kayıt başarılı, session bilgilerini oluştur
        $_SESSION["giris"] = sha1(md5("var"));
        $_SESSION["uyeID"] = $stmt->insert_id;
        $_SESSION["uyeAd"] = $ad;
        $_SESSION["uyeMail"] = $email;
        // Varsayılan yetki değeri (örneğin 0) atanabilir
        $_SESSION["uyeYetki"] = 2;

        echo "<script>
                        alert('Kayıt başarılı!');
                        window.location.href='index.php';
                      </script>";
        exit();
      } else {
        echo "<script>
                        alert('Kayıt sırasında bir hata oluştu.');
                      </script>";
        exit();
      }
    } else {
      echo "<script>
                    alert('Şifreler eşleşmiyor!');
                  </script>";
      exit();
    }
  }
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Prestij Emlak - Giriş / Kayıt</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="style.css" rel="stylesheet">
  <style>
    /* Temel stil ayarları */
    body {
      font-family: Arial, sans-serif;
      background: #f9f9f9;
      margin: 0;
      padding: 0;
    }

    .header {
      background-color: #004080;
      color: #fff;
      padding: 20px;
      text-align: center;
    }

    .btn {
      padding: 10px 20px;
      background-color: #ff6600;
      color: #fff;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      margin: 5px;
    }

    .btn:hover {
      opacity: 0.9;
    }

    /* Modal stil ayarları */
    .modal {
      display: none;
      /* Başlangıçta gizli */
      position: fixed;
      z-index: 1;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
      background-color: #fff;
      margin: 10% auto;
      padding: 20px;
      border: 1px solid #888;
      width: 90%;
      max-width: 500px;
      border-radius: 4px;
      position: relative;
    }

    .close {
      color: #aaa;
      position: absolute;
      right: 15px;
      top: 10px;
      font-size: 28px;
      font-weight: bold;
      cursor: pointer;
    }

    .close:hover {
      color: #000;
    }

    form {
      display: flex;
      flex-direction: column;
    }

    form input {
      padding: 10px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 4px;
    }
  </style>
</head>

<body>
  <?php include("header.php"); ?>
  <div class="header">
    <h1>Prestij Emlak</h1>
    <!-- Giriş ve Kayıt butonları -->
    <button id="loginBtn" class="btn">Giriş Yap</button>
    <button id="registerBtn" class="btn">Kayıt Ol</button>
  </div>

  <!-- Giriş Modal'ı -->
  <div id="loginModal" class="modal">
    <div class="modal-content">
      <span class="close" id="loginClose">&times;</span>
      <h2>Giriş Yap</h2>
      <form method="POST">
        <input type="email" name="gEmail" placeholder="Mail" required>
        <input type="password" name="gSifre" placeholder="Şifre" required>
        <button type="submit" class="btn">Giriş Yap</button>
      </form>
    </div>
  </div>

  <!-- Kayıt Modal'ı -->
  <div id="registerModal" class="modal">
    <div class="modal-content">
      <span class="close" id="registerClose">&times;</span>
      <h2>Kayıt Ol</h2>
      <form method="POST">
        <input type="text" name="kAd" placeholder="Ad" required>
        <input type="text" name="kSoyad" placeholder="Soyad" required>
        <input type="text" name="kTelNo" placeholder="Telefon" required>
        <input type="email" name="kEmail" placeholder="Mail" required>
        <input type="password" name="kSifre" placeholder="Şifre" required>
        <input type="password" name="kSifreTekrar" placeholder="Şifre Tekrar" required>
        <button type="submit" class="btn">Kayıt Ol</button>
      </form>
    </div>
  </div>

  <footer style="margin-top: 30.15%;">
    <p>&copy; 2025 Prestij Emlak. Tüm hakları saklıdır.</p>
    <p><a href="#" style="color: #ff6600; text-decoration: none;">İletişim</a> | <a href="#" style="color: #ff6600; text-decoration: none;">Gizlilik Politikası</a></p>
  </footer>
  <script>
    // Modal öğelerini tanımlıyoruz
    var loginModal = document.getElementById("loginModal");
    var registerModal = document.getElementById("registerModal");
    // Butonları alıyoruz
    var loginBtn = document.getElementById("loginBtn");
    var registerBtn = document.getElementById("registerBtn");
    // Kapatma ikonlarını alıyoruz
    var loginClose = document.getElementById("loginClose");
    var registerClose = document.getElementById("registerClose");

    // Giriş butonuna tıklandığında login modal'ını aç
    loginBtn.onclick = function() {
      loginModal.style.display = "block";
    }
    // Kayıt butonuna tıklandığında register modal'ını aç
    registerBtn.onclick = function() {
      registerModal.style.display = "block";
    }
    // Kapatma ikonlarına tıklayınca modal'ları kapat
    loginClose.onclick = function() {
      loginModal.style.display = "none";
    }
    registerClose.onclick = function() {
      registerModal.style.display = "none";
    }
    // Modal dışında tıklanırsa da kapat
    window.onclick = function(event) {
      if (event.target == loginModal) {
        loginModal.style.display = "none";
      }
      if (event.target == registerModal) {
        registerModal.style.display = "none";
      }
    }
  </script>
</body>

</html>