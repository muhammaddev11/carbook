<?php
session_start();
ob_start();
include('db.php'); // Ensure this contains the correct database connection

$alertMessage = ""; // Initialize alert message
$formData = ['name' => '', 'email' => '']; // Store form data for repopulation

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Handle Registration
if (isset($_POST['register'])) {
    $formData['name'] = trim($_POST['name']);
    $formData['email'] = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $role = 'user'; // Default role for new registrations

    // Validate inputs
    if (empty($formData['name']) || empty($formData['email']) || empty($password) || empty($confirm_password)) {
        $alertMessage = '<div class="alert alert-warning" role="alert">All fields are required!</div>';
        $_SESSION['form'] = 'signup';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $alertMessage = '<div class="alert alert-warning" role="alert">Invalid email format!</div>';
        $_SESSION['form'] = 'signup';
    } elseif ($password !== $confirm_password) {
        $alertMessage = '<div class="alert alert-warning" role="alert">Passwords do not match!</div>';
        $_SESSION['form'] = 'signup';
    } elseif (strlen($password) < 8) {
        $alertMessage = '<div class="alert alert-warning" role="alert">Password must be at least 8 characters!</div>';
        $_SESSION['form'] = 'signup';
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $formData['email']);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $alertMessage = '<div class="alert alert-warning" role="alert">Email already registered!</div>';
            $_SESSION['form'] = 'signup';
        } else {
            // Insert new user with default 'user' role
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $formData['name'], $formData['email'], $hashed_password, $role);
            
            if ($stmt->execute()) {
                $alertMessage = '<div class="alert alert-success" role="alert">Registration successful! Please login.</div>';
                $_SESSION['form'] = 'login';
                $formData = ['name' => '', 'email' => '']; // Clear form after successful registration
            } else {
                $alertMessage = '<div class="alert alert-danger" role="alert">Registration failed. Please try again.</div>';
                $_SESSION['form'] = 'signup';
            }
        }
        $stmt->close();
    }
}

// Handle Login
if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $name, $hashed_password, $role);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $name;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = $role; // Store user role in session

            // Redirect to appropriate page based on role
            if ($role === 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $alertMessage = '<div class="alert alert-danger" role="alert">Invalid email or password!</div>';
            $_SESSION['form'] = 'login';
        }
    } else {
        $alertMessage = '<div class="alert alert-danger" role="alert">Invalid email or password!</div>';
        $_SESSION['form'] = 'login';
    }
    $stmt->close();
}

ob_end_flush();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login and Registration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/login.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<?php 
// Display alert message if set
if (!empty($alertMessage)) {
    echo $alertMessage;
}
?>

<div class="container">
    <input type="checkbox" id="flip" <?php echo (isset($_SESSION['form']) && $_SESSION['form'] == 'signup') ? 'checked' : ''; ?>>
    <div class="cover">
        <div class="front">
            <img src="images/travel.jpg" alt="Travel Image">
            <div class="text">
                <span class="text-1">Every new friend is a new adventure</span>
                <span class="text-2">Let's get connected</span>
            </div>
        </div>
        <div class="back">
            <img class="backImg" src="images/love.jpg" alt="Love Image">
            <div class="text">
                <span class="text-1">Complete miles of journey with one step</span>
                <span class="text-2">Let's get started</span>
            </div>
        </div>
    </div>
    
    <div class="forms">
        <div class="form-content">
            <!-- Login Form -->
            <div class="login-form">
                <div class="title">Login</div>
                <form action="" method="POST">
                    <div class="input-boxes">
                        <div class="input-box">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" placeholder="Enter your email" required value="<?php echo isset($_POST['login']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                        <div class="input-box">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" placeholder="Enter your password" required>
                        </div>
                        <div class="text"><a href="#">Forgot password?</a></div>
                        <div class="button input-box">
                            <input type="submit" name="login" value="Login">
                        </div>
                        <div class="text sign-up-text">Don't have an account? <label for="flip">Sign up now</label></div>
                    </div>
                </form>
            </div>

            <!-- Signup Form -->
            <div class="signup-form">
                <div class="title">Signup</div>
                <form action="" method="POST">
                    <div class="input-boxes">
                        <div class="input-box">
                            <i class="fas fa-user"></i>
                            <input type="text" name="name" placeholder="Enter your name" required value="<?php echo htmlspecialchars($formData['name']); ?>">
                        </div>
                        <div class="input-box">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" placeholder="Enter your email" required value="<?php echo htmlspecialchars($formData['email']); ?>">
                        </div>
                        <div class="input-box">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" placeholder="Enter your password" required>
                        </div>
                        <div class="input-box">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="confirm_password" placeholder="Repeat your password" required>
                        </div>
                        <div class="button input-box">
                            <input type="submit" name="register" value="Register">
                        </div>
                        <div class="text sign-up-text">Already have an account? <label for="flip">Login now</label></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>