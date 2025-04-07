<?php
session_start();
include("ayar.php");

if (isset($_GET['id'])) {
    $ilanID = intval($_GET['id']);
} else {
    header("Location: index.php");
    exit;
}

// Corrected query with proper table joins
$sorgu = $baglan->prepare("SELECT 
                            id.ilanDAciklama,
                            id.ilanDFiyat,
                            id.ilanDmetreKareBrut,
                            id.ilanDmetreKareNet,
                            id.ilanDOdaSayisi,
                            id.ilanDBinaYasi,
                            id.ilanDSiteIcerisindeMi,
                            id.ilanDMulkTipiID,
                            id.ilanDMulkTuru,
                            id.ilanDKonumBilgisi,
                            id.ilanDIsıtmaTipi,
                            id.ilanDBulunduguKatSayisi,
                            id.ilanDBinaKatSayisi,
                            id.ilanDIlanTurID,
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
    header("Location: index.php");
    exit;
}

$resimler = explode(',', $ilan->resimler);
?>

<!doctype html>
<html lang="tr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Prestij Emlak - İlan Detay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .image-container {
            position: relative;
            text-align: center;
            max-width: 720px;
            margin: auto;
        }

        .image-container img {
            width: 100%;
            height: auto;
            margin-bottom: 15px;
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
    <div class="container mt-5">
        <div class="image-container mt-4">
            <h3>İlan Resimleri</h3>
            <?php foreach ($resimler as $resim): ?>
                <img src="<?php echo htmlspecialchars($resim); ?>" alt="İlan Resmi">
            <?php endforeach; ?>
        </div>
        <div class="details-container">
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
</body>

</html>