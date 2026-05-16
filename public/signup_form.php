<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>PWD Admin Sign Up</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

  <link rel="stylesheet" href="/assets/css/global/login_signup.css">

</head>

<body>

  <div class="main-wrapper">
    <!-- Left Section -->
    <div class="left-panel">
      <div class="left-content">
        <h1>Welcome to PWD<br>Online Application</h1>
        <p>Start your PWD application — create your account today.</p>
        <img src="/assets/pictures/PWD.png" alt="PWD Illustration">
      </div>
    </div>

<!-- Right Section -->
<div class="right-panel">
  <div class="login-card">
    <img src="/assets/pictures/Logo.jpg" class="logo" alt="PWD Logo">
    <p class="intro-text">Sign up to start your PWD registration journey.</p>
    <form action="/backend/auth/signup.php" method="POST">
      
      <div class="form-group">
        <input type="text" name="first_name" class="form-control" placeholder="First Name" required>
        <span class="form-icon"><i class="fas fa-user"></i></span>
      </div>
      
      <div class="form-group">
        <input type="text" name="last_name" class="form-control" placeholder="Last Name" required>
        <span class="form-icon"><i class="fas fa-user-tag"></i></span>
      </div>

      <div class="form-group">
        <input type="email" name="email" class="form-control" placeholder="Email" required>
        <span class="form-icon"><i class="fas fa-envelope"></i></span>
      </div>

      <div class="form-group">
        <input type="password" name="password" class="form-control" placeholder="Password" required>
        <span class="form-icon"><i class="fas fa-key"></i></span>
      </div>

      <div class="form-group">
        <input type="password" name="repeat_password" class="form-control" placeholder="Repeat Password" required>
        <span class="form-icon"><i class="fas fa-check-circle"></i></span>
      </div>

      <button type="submit" class="btn btn-signup">Sign Up</button>
      <p class="alternate-option">
        Already have an account?
        <a href="login_form.php">Log in</a>
      </p>
    </form>
  </div>
  <img src="/assets/pictures/iligan.png" class="iligan-logo" alt="Iligan Logo">
</div>
</div>
</body>

</html>