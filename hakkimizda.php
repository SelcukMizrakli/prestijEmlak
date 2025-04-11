<?php
session_start();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Hakkımızda - Prestij Emlak</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="style.css" rel="stylesheet">
</head>
<body>
  <?php include("header.php"); ?>

  <div class="container mt-5">
    <h1 class="text-center mb-4">Hakkımızda</h1>
    <div class="row">
      <div class="col-md-6">
        <img src="https://via.placeholder.com/600x400" alt="Prestij Emlak" class="img-fluid rounded">
      </div>
      <div class="col-md-6">
        <p>
          Prestij Emlak olarak, üyelerimize en iyi hizmeti sunmayı ve hayallerindeki evi bulmalarına yardımcı olmayı misyon edindik. 
          Yılların tecrübesiyle, güvenilir ve profesyonel bir emlak platformu olarak sektördeki yerimizi sağlamlaştırdık.
        </p>
        <p>
          Üyelerimizin memnuniyeti bizim için her zaman önceliklidir. Bu nedenle, geniş ilan portföyümüz ve kullanıcı dostu platformumuzla 
          her ihtiyaca uygun çözümler sunuyoruz. İster satılık ister kiralık bir ev arıyor olun, Prestij Emlak sizin yanınızda.
        </p>
        <p>
          Ekibimiz, dürüstlük ve şeffaflık ilkeleriyle çalışarak, üyelerimize en iyi deneyimi sunmayı taahhüt eder. 
          Hayalinizdeki evi bulmak için doğru adrestesiniz!
        </p>
      </div>
    </div>
    <div class="text-center mt-5">
      <h3>Bizimle İletişime Geçin</h3>
      <p>
        Daha fazla bilgi almak veya sorularınızı sormak için <a href="iletisim.php" class="text-primary">iletişim</a> sayfamızı ziyaret edebilirsiniz.
      </p>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>