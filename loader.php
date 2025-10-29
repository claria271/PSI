<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Memuat...</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      height: 100vh;
      background: linear-gradient(135deg, #ff4b4b, #000000);
      display: flex;
      justify-content: center;
      align-items: center;
      flex-direction: column;
      color: #fff;
      overflow: hidden;
    }

    /* Logo animasi berputar */
    .logo-container {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.1);
      display: flex;
      justify-content: center;
      align-items: center;
      box-shadow: 0 0 20px rgba(255, 255, 255, 0.3);
      animation: pulse 2s infinite ease-in-out;
    }

    .logo {
      width: 70px;
      animation: spin 3s linear infinite;
    }

    /* Animasi teks fade-in */
    h2 {
      margin-top: 30px;
      font-size: 1.6rem;
      letter-spacing: 1px;
      opacity: 0;
      animation: fadeIn 2s ease-in forwards;
      animation-delay: 1.5s;
    }

    /* Progress bar animasi */
    .progress-bar {
      width: 200px;
      height: 6px;
      border-radius: 3px;
      background: rgba(255, 255, 255, 0.3);
      margin-top: 30px;
      overflow: hidden;
    }

    .progress {
      height: 100%;
      width: 0;
      background: #ffffff;
      border-radius: 3px;
      animation: load 3s ease-in-out forwards;
    }

    /* Keyframes animasi */
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    @keyframes fadeIn {
      0% { opacity: 0; transform: translateY(20px); }
      100% { opacity: 1; transform: translateY(0); }
    }

    @keyframes pulse {
      0%, 100% { transform: scale(1); box-shadow: 0 0 20px rgba(255, 255, 255, 0.3); }
      50% { transform: scale(1.1); box-shadow: 0 0 40px rgba(255, 255, 255, 0.6); }
    }

    @keyframes load {
      0% { width: 0; }
      100% { width: 100%; }
    }
  </style>
</head>
<body>

  <div class="logo-container">
    <img src="assets/image/logo.png" alt="Logo PSI" class="logo">
  </div>
  <h2>Memuat Website PSI...</h2>
  <div class="progress-bar">
    <div class="progress"></div>
  </div>

  <script>
    // Arahkan ke halaman utama setelah 3.5 detik
    setTimeout(() => {
      window.location.href = "index.php";
    }, 3500);
  </script>

</body>
</html>
