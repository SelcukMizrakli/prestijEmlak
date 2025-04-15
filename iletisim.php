<?php
session_start();
?>
<!DOCTYPE html>
<html lang="tr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>İletişim - Prestij Emlak</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="style.css" rel="stylesheet">
  <style>
    .contact-icons {
      font-size: 2rem;
      margin: 10px;
      color: #004080;
      transition: color 0.3s ease;
    }

    .contact-icons:hover {
      color: #ff6600;
    }

    .contact-section {
      text-align: center;
      margin-top: 50px;
    }

    .contact-section h1 {
      margin-bottom: 20px;
    }

    .contact-section p {
      font-size: 1.2rem;
      margin-bottom: 30px;
    }
  </style>
</head>

<body>
  <?php include("header.php"); ?>

  <div class="container mt-5">
    <div class="contact-section">
      <h1>Bizimle İletişime Geçin</h1>
      <p>Prestij Emlak olarak size yardımcı olmaktan mutluluk duyarız. Aşağıdaki bağlantılar üzerinden bize ulaşabilirsiniz:</p>
      <div>
        <!-- Instagram -->
        <a href="https://www.instagram.com/selcukmzrkl" target="_blank" class="contact-icons">
          <i class="bi bi-instagram"></i>
        </a>
        <!-- WhatsApp -->
        <a href="https://wa.me/+905313173971" target="_blank" class="contact-icons">
          <i class="bi bi-whatsapp"></i>
        </a>
        <!-- Mail -->
        <a href="mailto:selcukmizrakli20@gmail.com" target="_blank" class="contact-icons">
          <i class="bi bi-envelope"></i>
        </a>
      </div>
    </div>
  </div>
  <footer>
    <p>&copy; 2025 Prestij Emlak. Tüm hakları saklıdır.</p>
    <p><a href="#" style="color: #ff6600; text-decoration: none;">İletişim</a> | <a href="#" style="color: #ff6600; text-decoration: none;">Gizlilik Politikası</a></p>
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.js"></script>
</body>

</html>