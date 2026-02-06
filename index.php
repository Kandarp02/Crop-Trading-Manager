<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Welcome | Chamunda Mata Traders</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@400;600;700&display=swap" rel="stylesheet">

  <!-- AOS -->
  <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css" />

  <style>
   :root {
  --primary: #388e3c;         /* Replaces blue with green */
  --accent: #81c784;          /* Light green accent */
  --purple: #7048e8;          /* Optional: used on hover for contrast */
  --bg-light: #f8f9fa;
  --dark: #212529;
  --green-dark: #2e7d32;
  --green-light: #a5d6a7;
}


    * {
      font-family: 'Urbanist', sans-serif;
      scroll-behavior: smooth;
    }

    body {
      background-color: var(--bg-light);
      margin: 0;
    }

    .navbar {
      background-color: white;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .navbar-brand {
      font-weight: bold;
      font-size: 1.8rem;
      color: var(--primary) !important;
    }

    .nav-link {
      font-weight: 500;
      color: var(--dark) !important;
      margin-left: 1rem;
      transition: 0.3s ease;
    }

    .nav-link:hover {
      color: var(--green-dark) !important;
      transform: scale(1.1);
    }

    .hero {
      background: linear-gradient(135deg, var(--accent), var(--primary));
      color: white;
      padding: 120px 0;
      text-align: center;
    }

    .hero h1 {
      font-size: 3rem;
      font-weight: 700;
    }

    .hero p {
      font-size: 1.2rem;
      margin-top: 10px;
    }

    .hero .btn {
      background-color: white;
      color: var(--primary);
      font-weight: 600;
      margin-top: 20px;
      border-radius: 30px;
      padding: 10px 25px;
      transition: all 0.3s ease;
    }

    .hero .btn:hover {
      background-color: var(--purple);
      color: white;
      transform: scale(1.05);
    }

    .section {
      background-color: white;
      padding: 60px 0;
    }

    .section-title {
      font-size: 2rem;
      font-weight: 700;
      color: var(--primary);
      text-align: center;
      margin-bottom: 2rem;
    }

    .about {
  background-color: #f1f8e9;
  padding: 80px 20px;
  font-family: 'Segoe UI', sans-serif;
}

.about .section-title {
  text-align: center;
  font-size: 2.5rem;
  color: #2e7d32;
  margin-bottom: 40px;
  font-weight: 700;
  position: relative;
}

.about .section-title::after {
  content: '';
  width: 80px;
  height: 4px;
  background-color: #66bb6a;
  display: block;
  margin: 12px auto 0;
  border-radius: 5px;
}

.about .container {
  max-width: 1000px;
  margin: 0 auto;
  background: #ffffff;
  padding: 40px 30px;
  border-radius: 15px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.about p {
  font-size: 1.15rem;
  color: #333;
  line-height: 2;
  text-align: center;
}

.about p span {
  font-weight: bold;
  color: #2e7d32;
}

.about p br {
  line-height: 2.2;
  margin-bottom: 10px;
}

@media (max-width: 768px) {
  .about .container {
    padding: 25px 20px;
  }

  .about p {
    font-size: 1.05rem;
  }

  .about .section-title {
    font-size: 2rem;
  }
}
.contact {
  padding: 80px 20px;
  background: #f1f8e9; /* Light green background */
  font-family: 'Segoe UI', sans-serif;
}

.contact-card {
  background: #ffffff;
 
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  padding: 30px 25px;
  border-radius: 10px;
  max-width: 900px;
  margin: 0 auto;
  position: relative;
}

.section-title {
  text-align: center;
  font-size: 2.5rem;
  color: #388e3c;
  margin-bottom: 30px;
  font-weight: bold;
  position: relative;
}

.section-title::after {
  content: '';
  width: 80px;
  height: 4px;
  background-color: #81c784;
  display: block;
  margin: 10px auto 0;
  border-radius: 5px;
}

.contact-info {
  font-size: 1.15rem;
  line-height: 1.8;
  color: #2e7d32;
  text-align: center;
  margin-bottom: 30px;
}

.contact-info strong {
  color: #1b5e20;
}

.map-box {
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
}

.map-box iframe {
  width: 100%;
  height: 400px;
  border: none;
  border-radius: 10px;
}

@media (max-width: 768px) {
  .section-title {
    font-size: 2rem;
  }

  .contact-info {
    font-size: 1rem;
  }

  .map-box iframe {
    height: 300px;
  }
}



    /* .map-container {
      padding: 0 15px;
      margin-top: 30px;
    }

    iframe {
      width: 100%;
      height: 350px;
      border: none;
      border-radius: 10px;
    } */

     footer {
     
      bottom: 0;
      left: 0;
      width: 100%;
      background-color: #1f5d23;
      color: white;
      text-align: center;
      font-size: 13px;
      padding: 10px 12px;
      z-index: 999;
    }

    footer a {
      color: white;
      text-decoration: none;
    }

    @media (max-width: 576px) {
      .hero h1 {
        font-size: 2rem;
      }

      .hero p {
        font-size: 1rem;
      }

      .hero .btn {
        padding: 8px 20px;
      }
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg sticky-top" data-aos="fade-down">
    <div class="container">
      <a class="navbar-brand" href="#">Chamunda Mata Traders</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="nav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item" data-aos="fade-down" data-aos-delay="100">
            <a class="nav-link active" href="#"><i class="fas fa-home me-1"></i>Home</a>
          </li>
          <li class="nav-item" data-aos="fade-down" data-aos-delay="200">
            <a class="nav-link" href="#about"><i class="fas fa-info-circle me-1"></i>About Us</a>
          </li>
          <li class="nav-item" data-aos="fade-down" data-aos-delay="300">
            <a class="nav-link" href="tel:+919876543210" title="Call now"><i class="fas fa-phone-alt me-1"></i>Contact</a>
          </li>
          <li class="nav-item" data-aos="fade-down" data-aos-delay="400">
            <a class="nav-link" href="#contact"><i class="fas fa-map-marker-alt me-1"></i>Address</a>
          </li>
          <li class="nav-item" data-aos="fade-down" data-aos-delay="500">
            <a class="nav-link" href="login.php"><i class="fas fa-user-shield me-1"></i>Admin Login</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Hero -->
  <section class="hero">
    <div class="container">
      <h1 data-aos="fade-down">Welcome to Chamunda Mata Traders</h1>
      <p data-aos="fade-up" data-aos-delay="200">Delivering Quality & Service since 2022</p>
      <a href="tel:+919876543210" class="btn" data-aos="zoom-in" data-aos-delay="400">
        <i class="fas fa-phone-alt me-2"></i>Call +91 98765 43210
      </a>
    </div>
  </section>

  <!-- About Us -->
  <section class="section about" id="about">
    <div class="container" data-aos="fade-up">
      <h2 class="section-title" data-aos="zoom-in">About Us</h2>
      <p class="text-center w-75 mx-auto" data-aos="fade-right">
  Welcome to <span>Chamunda Mata Traders 🙏</span><br><br>
  We are located at <b>Pimpri Khurd, Taluka Dharangaon</b>, <br>on the Dharangaon Jalgaon Road.<br><br>

  🕘 <b>Shop Timings:</b><br>
  <span>Open every day from 9:00 AM to 5:00 PM</span><br><br>

  🌾 <b>What We Buy & Sell:</b><br><br>

  <span>Makka (मका), Jwari (ज्वारी), Bajari (बाजरी), Gahu (गहू),</span><br>
  <span>Chana (हरभरा), Mung (मुग), Mat (मटकी), Soyabean (सोयाबीन),</span><br>
  <span>Toor (तूर), Kapus (कापूस)</span><br><br>

  We give fair prices and transparent service. Many farmers from nearby villages trust us.<br><br>

  👨‍🌾 <b>Handled by:</b><br>
  Ganesh Sudam Badgujar<br>
  Mayur Ganesh Badgujar<br><br>

  We are here to support our farmers and customers with respect and honesty.<br><br>

  🙏 <b>Thank you for trusting Chamunda Mata Traders</b> 🙏
</p>
</div>
</section>

<section class="section contact" id="contact">
  <div class="container" data-aos="fade-up">
    <div class="contact-card" data-aos="zoom-in">
      <h2 class="section-title">📍 Address & Location</h2>

      <p class="contact-info">
        <strong>Chamunda Mata Traders</strong><br>
        Pimpri Khurd, Taluka Dharangaon,<br>
        District Jalgaon, Maharashtra, India<br><br>

        ⏰ <strong>Timings:</strong> 9:00 AM – 5:00 PM (Everyday)<br>
        📞 <strong>Contact:</strong> +91 7709294093<br>
        📧 <strong>Email:</strong> badgujarmayur607@gmail.com
      </p>

      <div class="map-box" data-aos="fade-up">
        <iframe
 src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d7448.636717208375!2d75.354821!3d21.019944!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3bd917f73da8ee99%3A0xcf9b1971ae0f76df!2sChamunda%20mata%20treders!5e0!3m2!1sen!2sin!4v1751831079115!5m2!1sen!2sin" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
          width="100%" height="400"
          style="border:0;" allowfullscreen="" loading="lazy"
          referrerpolicy="no-referrer-when-downgrade">
        </iframe>
      </div>
    </div>
  </div>
</section>



   <!-- Footer -->
  <footer>
    <p>&copy; 2025 Chamunda Mata Traders.<br> Built by Kandarp Patil | 📧 <a href="mailto:kandarppatil2@gmail.com">kandarppatil2@gmail.com</a></p>
  </footer>


  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <script>
    AOS.init({
      duration: 1200,
      once: true
    });
  </script>
</body>
</html>
