<?php
session_start();
// Eğer kullanıcı giriş yapmışsa, session'dan bilgileri çekiyoruz.
$loggedIn = isset($_SESSION['giris']) && isset($_SESSION['uyeAd']) && isset($_SESSION['uyeMail']);
if ($loggedIn) {
  $kullaniciAdi   = $_SESSION['uyeAd'];
  $kullaniciEmail = $_SESSION['uyeMail'];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Prestij Emlak</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <style>
    /* Temel reset */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: Arial, sans-serif;
      line-height: 1.6;
      background-color: #f9f9f9;
      color: #333;
    }

    header {
      background-color: #004080;
      color: #fff;
      padding: 15px 20px;
    }

    header .container {
      max-width: 1200px;
      margin: auto;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    header .logo {
      font-size: 1.8em;
      font-weight: bold;
    }

    nav ul {
      list-style: none;
      display: flex;
    }

    nav ul li {
      margin-left: 20px;
    }

    nav ul li a {
      color: #fff;
      text-decoration: none;
      font-weight: bold;
    }

    .search-section {
      background: url('https://via.placeholder.com/1200x400') no-repeat center/cover;
      padding: 60px 20px;
      text-align: center;
      color: #fff;
      position: relative;
    }

    .search-section::after {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.4);
    }

    .search-section .search-container {
      position: relative;
      z-index: 1;
      max-width: 800px;
      margin: auto;
    }

    .search-section h1 {
      font-size: 2.5em;
      margin-bottom: 20px;
    }

    .search-form {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
    }

    .search-form input,
    .search-form select {
      padding: 10px;
      margin: 5px;
      border: none;
      border-radius: 4px;
      min-width: 150px;
    }

    .search-form button {
      padding: 10px 20px;
      margin: 5px;
      border: none;
      border-radius: 4px;
      background-color: #ff6600;
      color: #fff;
      font-weight: bold;
      cursor: pointer;
    }

    .listings {
      max-width: 1200px;
      margin: 40px auto;
      padding: 0 20px;
    }

    .listing-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 20px;
    }

    .listing-card {
      background-color: #fff;
      border-radius: 4px;
      overflow: hidden;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .listing-card img {
      width: 100%;
      height: 180px;
      object-fit: cover;
    }

    .listing-card .card-content {
      padding: 15px;
    }

    .listing-card .card-content h3 {
      margin-bottom: 10px;
      font-size: 1.2em;
      color: #004080;
    }

    .listing-card .card-content p {
      font-size: 0.9em;
      color: #555;
    }

    footer {
      background-color: #333;
      color: #ccc;
      padding: 20px;
      text-align: center;
    }

    @media (max-width: 768px) {
      header .container {
        flex-direction: column;
      }

      nav ul {
        flex-direction: column;
        margin-top: 10px;
      }

      nav ul li {
        margin: 5px 0;
      }
    }
  </style>
</head>

<body>
  <!-- Header -->
  <header>
    <div class="container">
      <div class="logo">Prestij Emlak</div>
      <nav>
        <ul>
          <li><a href="#">Ana Sayfa</a></li>
          <li><a href="#">İlanlar</a></li>
          <li><a href="#">Hakkımızda</a></li>
          <li><a href="#">İletişim</a></li>
          <?php if ($loggedIn) { ?>
            <!-- Kullanıcı oturumu aktifse, kullanıcı bilgilerini gösteren dropdown -->
            <li>
              <div class="dropdown">
                <button class="btn dropdown-toggle" type="button" style="color: white; background-color: #004080; border: none;" data-bs-toggle="dropdown" aria-expanded="false">
                  <!-- Örnek ikon -->
                  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                    <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z" />
                  </svg>
                  <?php echo htmlspecialchars($kullaniciAdi); ?> (<?php echo htmlspecialchars($kullaniciEmail); ?>)
                </button>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="profil.php">Profil</a></li>
                  <li><a class="dropdown-item" href="favoriler.php">Favoriler</a></li>
                  <li><a class="dropdown-item" href="sepet.php">Sepet</a></li>
                  <li><a class="dropdown-item text-danger" href="cikisYap.php">Çıkış Yap</a></li>
                </ul>
              </div>
            </li>
          <?php } else { ?>
            <!-- Kullanıcı oturumu kapalıysa giriş ve kayıt linkleri -->
            <li><a href="girisYap.php">Giriş Yap</a></li>
            <li><a href="girisYap.php">Kayıt Ol</a></li>
          <?php } ?>
      </nav>
    </div>
  </header>

  <!-- Arama Bölümü -->
  <section class="search-section">
    <div class="search-container">
      <h1>Hayalinizdeki Evi Bulun</h1>
      <form class="search-form">
        <select name="il">
          <option value="">İl Seçiniz</option>
          <option value="istanbul">İstanbul</option>
          <option value="ankara">Ankara</option>
          <option value="izmir">İzmir</option>
        </select>
        <select name="ilan-turu">
          <option value="">İlan Türü</option>
          <option value="satilik">Satılık</option>
          <option value="kiralik">Kiralık</option>
        </select>
        <input type="text" name="fiyat" placeholder="Fiyat Aralığı">
        <input type="text" name="oda" placeholder="Oda Sayısı">
        <button type="submit" class="btn btn-secondary">Ara</button>
        <button type="reset" class="btn btn-secondary">Temizle</button>
        <button type="button" class="btn btn-primary" onclick="window.location.href='ilanEkle.php'">İlan Ekle</button>
      </form>
    </div>
  </section>

  <?php
  // Veritabanı bağlantı ayarları
  include("ayar.php");
  if ($baglan->connect_error) {
    die("Bağlantı hatası: " . $baglan->connect_error);
  }

  // "t_istaatistik" tablosundan en çok görüntülenen 5 ilanı çekiyoruz.
  $sql = "SELECT * FROM t_istatistik ORDER BY istatistikGoruntulenmeSayisi DESC LIMIT 5";
  $result = $baglan->query($sql);

  if ($result->num_rows > 0) {
    echo '<div class="featured-listings">';
    while ($row = $result->fetch_assoc()) {
      echo '<div class="listing">';
      // Örneğin ilan başlığını ve görüntülenme sayısını gösteriyoruz.
      echo '<p>Görüntülenme Sayısı: ' . $row['istatistikGoruntulenmeSayisi'] . '</p>';
      echo '</div>';
    }
    echo '</div>';
  } else {
    echo "Öne çıkan ilan bulunamadı.";
  }
  $baglan->close();
  ?>


  <!-- Footer -->
  <footer>
    <p>&copy; 2025 Prestij Emlak. Tüm hakları saklıdır.</p>
    <p><a href="#" style="color: #ff6600; text-decoration: none;">İletişim</a> | <a href="#" style="color: #ff6600; text-decoration: none;">Gizlilik Politikası</a></p>
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>

</html>