<?php
session_start();
?>
<!DOCTYPE html>
<html lang="tr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>İlanlar - Prestij Emlak</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="style.css" rel="stylesheet">
</head>

<body>
  <?php include("header.php"); ?>

  <!-- Arama Bölümü -->
  <section class="search-section">
    <div class="search-container">
      <h1>Tüm İlanlar</h1>
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
  <div class="container mt-5">
    <!-- İlanlar Bölümü -->
    <div class="listings mt-5">
      <div class="listing-grid">
        <!-- Statik İlan 1 -->
        <a href="#" style="text-decoration: none; color: inherit;">
          <div class="listing-card">
            <img src="https://via.placeholder.com/300x200" alt="İlan Resmi">
            <div class="card-content">
              <h3>Satılık Daire</h3>
              <p>3+1, 120 m², İstanbul</p>
              <p><strong>Fiyat:</strong> 1,500,000 TL</p>
            </div>
          </div>
        </a>
        <!-- Statik İlan 2 -->
        <a href="#" style="text-decoration: none; color: inherit;">
          <div class="listing-card">
            <img src="https://via.placeholder.com/300x200" alt="İlan Resmi">
            <div class="card-content">
              <h3>Kiralık Daire</h3>
              <p>2+1, 90 m², Ankara</p>
              <p><strong>Fiyat:</strong> 5,000 TL</p>
            </div>
          </div>
        </a>
        <!-- Statik İlan 3 -->
        <a href="#" style="text-decoration: none; color: inherit;">
          <div class="listing-card">
            <img src="https://via.placeholder.com/300x200" alt="İlan Resmi">
            <div class="card-content">
              <h3>Satılık Villa</h3>
              <p>5+2, 300 m², İzmir</p>
              <p><strong>Fiyat:</strong> 7,500,000 TL</p>
            </div>
          </div>
        </a>
        <!-- Daha fazla statik ilan eklenebilir -->
      </div>
    </div>
  </div>
  <footer>
    <p>&copy; 2025 Prestij Emlak. Tüm hakları saklıdır.</p>
    <p><a href="#" style="color: #ff6600; text-decoration: none;">İletişim</a> | <a href="#" style="color: #ff6600; text-decoration: none;">Gizlilik Politikası</a></p>
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>