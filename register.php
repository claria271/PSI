<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Daftar</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #fff;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }

    /* Gambar header */
    .header-img {
      width: 50%; /* lebar container diperkecil */
    }
    .header-img img {
      width: 100%;
      height: auto;
      display: block;
      border-radius: 10px 10px 0 0;
      opacity: 0.5;
    }

    /* Container form */
    .container {
      background: #fff;
      border-radius: 0 0 10px 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      width: 50%; /* sama dengan gambar */
      padding: 12px 0;
      box-sizing: border-box;
      display: flex;
      justify-content: center;
    }

    h2 {
      text-align: center;
      margin-bottom: 10px;
      font-size: 18px;
    }

    form {
      width: 70%; /* form tetap ramping 30% dari container */
      margin: 0 auto;
    }

    label {
      display: block;
      margin-top: 4px;
      margin-bottom: 2px;
      font-weight: normal; 
      font-size: 13px;
      color: #333;
    }

    input, button {
      width: 100%;
      padding: 8px;
      margin-bottom: 5px;
      border-radius: 5px;
      box-sizing: border-box;
    }

    input {
      border: 1px solid #ccc;
      font-size: 14px;
    }

    button {
      border: none;
      background: #4a90e2;
      color: #fff;
      font-size: 15px;
      cursor: pointer;
      margin-top: 8px;
    }

    button:hover {
      background: #357abd;
    }
  </style>
</head>
<body>
  <!-- Gambar di luar container -->
  <div class="header-img">
    <img src="assets/image/psi2.jpg" alt="psi">
  </div>

  <!-- Form container -->
  <div class="container">
    <div style="width:100%">
      <h2>Daftar</h2>
      <form method="POST">
        <label for="nama_lengkap">Nama Lengkap</label>
        <input type="text" id="nama_lengkap" name="nama_lengkap" placeholder="Nama Lengkap" required>

        <label for="alamat_lengkap">Alamat Lengkap</label>
        <input type="text" id="alamat_lengkap" name="alamat_lengkap" placeholder="Alamat Lengkap" required>

        <label for="nomor_telepon">Nomor Telepon</label>
        <input type="text" id="nomor_telepon" name="nomor_telepon" placeholder="Nomor Telepon" required>

        <label for="alamat_email">Alamat Email</label>
        <input type="email" id="alamat_email" name="alamat_email" placeholder="Alamat Email" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Password" required>

        <button type="submit">Daftar</button>
      </form>
    </div>
  </div>
</body>
</html>
