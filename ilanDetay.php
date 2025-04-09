<?php
session_start();
include("ayar.php");

$loggedIn = isset($_SESSION['giris']) && isset($_SESSION['uyeAd']) && isset($_SESSION['uyeMail']);
if ($loggedIn) {
    $kullaniciAdi   = $_SESSION['uyeAd'];
    $kullaniciEmail = $_SESSION['uyeMail'];
}

// İlan ID'sini al
if (isset($_GET['id'])) {
    $ilanID = intval($_GET['id']);
} else {
    // Eğer ID yoksa, ana sayfaya yönlendir
    header("Location: index.php");
    exit;
}

// İlan bilgilerini al
$sorgu = $baglan->prepare("SELECT 
                            id.ilanDAciklama,
                            id.ilanDFiyat,
                            id.ilanDmetreKareBrut,
                            id.ilanDmetreKareNet,
                            id.ilanDOdaSayisi,
                            id.ilanDBinaYasi,
                            id.ilanDSiteIcerisindeMi,
                            id.ilanDMulkTuru,
                            id.ilanDKonumBilgisi,
                            id.ilanDIsıtmaTipi,
                            id.ilanDBulunduguKatSayisi,
                            id.ilanDBinaKatSayisi,
                            GROUP_CONCAT(r.resimUrl) as resimler
                          FROM t_ilandetay id
                          JOIN t_ilanlar il ON id.ilanDilanID = il.ilanID
                          LEFT JOIN t_resimler r ON il.ilanID = r.resimIlanID
                          WHERE il.ilanID = ?
                          GROUP BY il.ilanID");
$sorgu->bind_param("i", $ilanID);
$sorgu->execute();
$result = $sorgu->get_result();
$ilan = $result->fetch_object();

if (!$ilan) {
    // Eğer ilan bulunamazsa, ana sayfaya yönlendir
    header("Location: index.php");
    exit;
}

// Resimleri ayır
$resimler = isset($ilan->resimler) && $ilan->resimler !== null ? explode(',', $ilan->resimler) : [];
?>

<!doctype html>
<html lang="tr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Prestij Emlak - İlan Detay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
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

        .image-container {
            position: relative;
            text-align: center;
            max-width: 720px;
            margin: auto;
        }

        .large-image {
            width: 100%;
            height: 400px;
            object-fit: contain;
            transition: transform 0.2s;
        }

        .nav-button {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            z-index: 10;
        }

        .nav-button.left {
            left: 10px;
        }

        .nav-button.right {
            right: 10px;
        }

        .thumbnail-container {
            display: flex;
            justify-content: center;
            margin-top: 10px;
        }

        .thumbnail {
            width: 100px;
            height: 80px;
            margin: 0 5px;
            cursor: pointer;
            opacity: 0.6;
        }

        .thumbnail:hover {
            opacity: 1;
        }

        .details-container {
            max-width: 720px;
            margin: auto;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }

        .details-container h2 {
            margin-bottom: 20px;
        }

        .details-container p {
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
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
    <div class="container mt-5">
        <div class="image-container">
            <button class="nav-button left" onclick="changeImage(-1)">&#10094;</button>
            <img src="<?php echo htmlspecialchars($resimler[0] ?? ''); ?>" alt="İlan Resmi" class="large-image" id="currentImage">
            <button class="nav-button right" onclick="changeImage(1)">&#10095;</button>
        </div>
        <div class="thumbnail-container">
            <?php foreach ($resimler as $index => $resim): ?>
                <img src="<?php echo htmlspecialchars($resim); ?>" alt="Küçük Resim" class="thumbnail" onclick="document.getElementById('currentImage').src='<?php echo htmlspecialchars($resim); ?>';">
            <?php endforeach; ?>
        </div>
        <div class="details-container mt-4">
            <h2>İlan Detayları</h2>
            <p><strong>Fiyat:</strong> <?php echo number_format($ilan->ilanDFiyat, 2); ?> TL</p>
            <p><strong>Metrekare (Brüt):</strong> <?php echo $ilan->ilanDmetreKareBrut; ?> m²</p>
            <p><strong>Metrekare (Net):</strong> <?php echo $ilan->ilanDmetreKareNet; ?> m²</p>
            <p><strong>Oda Sayısı:</strong> <?php echo $ilan->ilanDOdaSayisi; ?></p>
            <p><strong>Bina Yaşı:</strong> <?php echo $ilan->ilanDBinaYasi; ?></p>
            <p><strong>Site İçerisinde Mi:</strong> <?php echo $ilan->ilanDSiteIcerisindeMi ? 'Evet' : 'Hayır'; ?></p>
            <p><strong>Mülk Türü:</strong> <?php echo $ilan->ilanDMulkTuru; ?></p>
            <p><strong>Konum:</strong> <?php echo $ilan->ilanDKonumBilgisi; ?></p>
            <p><strong>Isıtma Tipi:</strong> <?php echo $ilan->ilanDIsıtmaTipi; ?></p>
            <p><strong>Bulunduğu Kat:</strong> <?php echo $ilan->ilanDBulunduguKatSayisi; ?></p>
            <p><strong>Bina Kat Sayısı:</strong> <?php echo $ilan->ilanDBinaKatSayisi; ?></p>
        </div>
    </div>
    <!-- Footer -->
    <footer>
        <p>&copy; 2025 Prestij Emlak. Tüm hakları saklıdır.</p>
        <p><a href="#" style="color: #ff6600; text-decoration: none;">İletişim</a> | <a href="#" style="color: #ff6600; text-decoration: none;">Gizlilik Politikası</a></p>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script>
        let currentIndex = 0;
        const images = <?php echo json_encode($resimler); ?>;

        function changeImage(direction) {
            currentIndex = (currentIndex + direction + images.length) % images.length;
            document.getElementById('currentImage').src = images[currentIndex];
        }
    </script>
</body>

</html>