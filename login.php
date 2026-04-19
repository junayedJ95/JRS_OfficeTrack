
<?php
session_start();
require_once 'includes/db.php';

// If already logged in, go to dashboard
if(isset($_SESSION['admin'])){
    header("Location: dashboard.php");
    exit();
}

$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if($admin && password_verify($password, $admin['password'])){
        $_SESSION['admin'] = $admin['username'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | JRS OfficeTrack</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

    <div class="bg-white rounded-2xl shadow-lg p-8 w-full max-w-md">

        <!-- Logo / Title -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-indigo-600">JRS OfficeTrack</h1>
            <p class="text-gray-500 mt-1">Sign in to your account</p>
        </div>

        <!-- Error Message -->
        <?php if($error): ?>
        <div class="bg-red-100 text-red-600 px-4 py-3 rounded-lg mb-6 text-sm">
            <?= $error ?>
        </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="POST">
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <input 
                    type="text" 
                    name="username" 
                    required
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Enter username"
                >
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input 
                    type="password" 
                    name="password" 
                    required
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Enter password"
                >
            </div>

            <button 
                type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 rounded-lg transition duration-200">
                Sign In
            </button>
        </form>

        <!-- Footer Credit -->
<footer class="text-center text-xs text-gray-400 py-4">
    Built by <a href="about.php" target="_blank" class="text-indigo-500 hover:text-indigo-700 font-semibold">JRSphere&#8482;</a>
</footer>    </div>

</body>
</html>