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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    /* Genel Stiller */
    body {
      font-family: 'Arial', sans-serif;
      background-color: #f9f9f9;
      color: #333;
    }

    header {
      background-color: #004080;
      color: #fff;
      padding: 15px 20px;
    }

    header .container {
      display: flex;
      justify-content: space-between;
      align-items: center;
      height: 60px; /* Fixed height for consistent alignment */
    }

    header .logo {
      font-size: 1.5em;
      font-weight: bold;
      line-height: 60px; /* Match container height */
      padding: 0 20px;
    }

    nav ul {
      list-style: none;
      display: flex;
      gap: 15px;
      margin: 0;
      padding: 0;
      height: 60px; /* Match container height */
      align-items: center;
    }

    nav ul li a {
      display: inline-block;
      line-height: 60px; /* Match container height */
      padding: 0 10px;
      color: #fff;
      text-decoration: none;
      font-weight: bold;
      transition: color 0.3s ease;
    }

    nav ul li a:hover {
      color: #ff6600;
    }

    .dropdown-toggle {
      line-height: 60px !important; /* Match container height */
      padding: 0 10px !important;
    }

    .search-section {
      background: url('https://via.placeholder.com/1200x400') no-repeat center/cover;
      padding: 80px 20px;
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
      background: rgba(0, 0, 0, 0.5);
    }

    .search-section .search-container {
      position: relative;
      z-index: 1;
      max-width: 800px;
      margin: auto;
    }

    .search-section h1 {
      font-size: 2.8em;
      margin-bottom: 20px;
    }

    .search-form {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 10px;
    }

    .search-form input,
    .search-form select {
      padding: 10px;
      border: none;
      border-radius: 4px;
      min-width: 150px;
    }

    .search-form button {
      padding: 10px 20px;
      border: none;
      border-radius: 4px;
      background-color: #ff6600;
      color: #fff;
      font-weight: bold;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .search-form button:hover {
      background-color: #e65c00;
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
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .listing-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
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

    footer a {
      color: #ff6600;
      text-decoration: none;
      transition: color 0.3s ease;
    }

    footer a:hover {
      color: #e65c00;
    }

    @media (max-width: 768px) {
      header .container {
        flex-direction: column;
        text-align: center;
      }

      nav ul {
        flex-direction: column;
        gap: 10px;
      }

      .search-section h1 {
        font-size: 2em;
      }
    }
  </style>
</head>

<body>
  <?php include("header.php"); ?>

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

  // En çok görüntülenen 5 ilanı çekiyoruz
  $sql = "SELECT 
            i.istatistikGoruntulenmeSayisi,
            il.ilanID,
            id.ilanDAciklama,
            id.ilanDFiyat,
            (SELECT r.resimUrl FROM t_resimler r WHERE r.resimIlanID = il.ilanID LIMIT 1) AS resimYolu
          FROM t_istatistik i
          JOIN t_ilanlar il ON i.istatistikIlanID = il.ilanID
          JOIN t_ilandetay id ON il.ilanID = id.ilanDilanID
          ORDER BY i.istatistikGoruntulenmeSayisi DESC 
          LIMIT 5";
  
  $result = $baglan->query($sql);

  if ($result->num_rows > 0) {
    echo '<div class="listings">';
    echo '<div class="listing-grid">';
    while ($row = $result->fetch_assoc()) {
      echo '<a href="ilanDetay.php?id=' . htmlspecialchars($row['ilanID']) . '" style="text-decoration: none; color: inherit;">';
      echo '<div class="listing-card">';
      echo '<img src="' . htmlspecialchars($row['resimYolu']) . '"';
      echo '<div class="card-content">';
      echo '<p>' . htmlspecialchars($row['ilanDAciklama']) . '</p>';
      echo '<p><strong>Fiyat:</strong> ' . htmlspecialchars($row['ilanDFiyat']) . ' TL</p>';
      echo '</div>';
      echo '</div>';
      echo '</a>';
    }
    echo '</div>';
    echo '</div>';
  } else {
    echo '<div class="listings"><p>Öne çıkan ilan bulunamadı.</p></div>';
  }
  $baglan->close();
  ?>


  <!-- Footer -->
  <footer>
    <p>&copy; 2025 Prestij Emlak. Tüm hakları saklıdır.</p>
    <p><a href="#" style="color: #ff6600; text-decoration: none;">İletişim</a> | <a href="#" style="color: #ff6600; text-decoration: none;">Gizlilik Politikası</a></p>
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>