<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

$success = '';
$error   = '';

// ── Fetch Admin ────────────────────────────────────
$admin = $pdo->query("SELECT * FROM admin WHERE id = 1")->fetch();

// ── Update Profile ─────────────────────────────────
if (isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name']);
    $email     = trim($_POST['email']);
    $status    = $_POST['status'];

    if (empty($full_name)) {
        $error = "Full name cannot be empty!";
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $full_name)) {
        $error = "Full name must contain letters only!";
    } else {
        $profile_pic = $admin['profile_pic'];

        // Handle image upload
        if (!empty($_FILES['profile_pic']['name'])) {
            $allowed  = ['jpg', 'jpeg', 'png', 'gif'];
            $ext      = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
            $max_size = 2 * 1024 * 1024;

            if (!in_array($ext, $allowed)) {
                $error = "Only JPG, PNG, GIF images allowed!";
            } elseif ($_FILES['profile_pic']['size'] > $max_size) {
                $error = "Image must be under 2MB!";
            } else {
                $filename    = 'profile_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['profile_pic']['tmp_name'], 'assets/' . $filename);
                $profile_pic = $filename;
            }
        }

        if (empty($error)) {
            $pdo->prepare("UPDATE admin SET full_name = ?, email = ?, status = ?, profile_pic = ? WHERE id = 1")
                ->execute([$full_name, $email, $status, $profile_pic]);

            $pdo->prepare("INSERT INTO notifications (title, message, type) VALUES (?, ?, ?)")
                ->execute(['Profile Updated', 'Your profile has been updated successfully.', 'success']);

            $success = "Profile updated successfully!";
            $admin   = $pdo->query("SELECT * FROM admin WHERE id = 1")->fetch();
        }
    }
}

// ── Change Password ────────────────────────────────
if (isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new     = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if (!password_verify($current, $admin['password'])) {
        $error = "Current password is incorrect!";
    } elseif (strlen($new) < 6) {
        $error = "New password must be at least 6 characters!";
    } elseif ($new !== $confirm) {
        $error = "New passwords do not match!";
    } else {
        $pdo->prepare("UPDATE admin SET password = ? WHERE id = 1")
            ->execute([password_hash($new, PASSWORD_DEFAULT)]);

        $pdo->prepare("INSERT INTO notifications (title, message, type) VALUES (?, ?, ?)")
            ->execute(['Password Changed', 'Your password was changed successfully.', 'success']);

        $success = "Password changed successfully!";
    }
}

// ── Reset Password via Secret Key ─────────────────
if (isset($_POST['reset_password'])) {
    $secret_key  = trim($_POST['secret_key']);
    $new_pass    = $_POST['reset_new_password'];
    $confirm_pass = $_POST['reset_confirm_password'];

    // Secret key is stored in a config — default is 'jrsphere2024'
    $valid_key = 'jrsphere2024';

    if ($secret_key !== $valid_key) {
        $error = "Invalid secret key!";
    } elseif (strlen($new_pass) < 6) {
        $error = "Password must be at least 6 characters!";
    } elseif ($new_pass !== $confirm_pass) {
        $error = "Passwords do not match!";
    } else {
        $pdo->prepare("UPDATE admin SET password = ? WHERE id = 1")
            ->execute([password_hash($new_pass, PASSWORD_DEFAULT)]);

        $success = "Password reset successfully! You can now login with your new password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | JRS OfficeTrack</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

    <?php include 'includes/navbar.php'; ?>
    <?php include 'includes/sidebar.php'; ?>

    <div style="margin-left:256px; margin-top:64px;" class="p-8">

        <!-- Page Header -->
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Settings</h2>
            <p class="text-gray-500 text-sm mt-1">Manage your profile, password and account preferences</p>
        </div>

        <!-- Alerts -->
        <?php if ($success): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6 flex items-center gap-2">
            ✅ <?= htmlspecialchars($success) ?>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 flex items-center gap-2">
            ❌ <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- ── Profile Card ───────────────────────── -->
            <div class="bg-white rounded-2xl shadow p-6 text-center h-fit">

                <!-- Avatar -->
                <?php if (!empty($admin['profile_pic'])): ?>
                <img src="assets/<?= htmlspecialchars($admin['profile_pic']) ?>"
                    class="h-24 w-24 rounded-full object-cover border-4 border-indigo-100 mx-auto mb-4">
                <?php else: ?>
                <div class="h-24 w-24 rounded-full bg-indigo-600 flex items-center justify-center text-white text-4xl font-bold mx-auto mb-4 border-4 border-indigo-100">
                    <?= strtoupper(substr($admin['full_name'] ?? 'A', 0, 1)) ?>
                </div>
                <?php endif; ?>

                <h3 class="text-xl font-bold text-gray-800"><?= htmlspecialchars($admin['full_name'] ?? 'Admin') ?></h3>
                <p class="text-gray-500 text-sm mt-1"><?= htmlspecialchars($admin['email'] ?? 'No email set') ?></p>

                <!-- Status Badge -->
                <span class="inline-block mt-3 px-3 py-1 rounded-full text-xs font-semibold
                    <?= $admin['status'] === 'Active' ? 'bg-green-100 text-green-700' :
                       ($admin['status'] === 'Busy'   ? 'bg-red-100 text-red-700' :
                                                        'bg-yellow-100 text-yellow-700') ?>">
                    ● <?= htmlspecialchars($admin['status'] ?? 'Active') ?>
                </span>

                <div class="mt-5 pt-4 border-t text-left space-y-2">
                    <p class="text-xs text-gray-500">Username:
                        <span class="font-semibold text-gray-700"><?= htmlspecialchars($admin['username']) ?></span>
                    </p>
                    <p class="text-xs text-gray-500">Member since:
                        <span class="font-semibold text-gray-700"><?= date('M d, Y', strtotime($admin['created_at'])) ?></span>
                    </p>
                </div>

                <!-- Tabs Switcher -->
                <div class="mt-5 pt-4 border-t space-y-2">
                    <button onclick="showTab('profile')"
                        class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium text-indigo-600 bg-indigo-50 hover:bg-indigo-100 transition">
                        ✏️ Edit Profile
                    </button>
                    <button onclick="showTab('password')"
                        class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 transition">
                        🔒 Change Password
                    </button>
                    <button onclick="showTab('forgot')"
                        class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 transition">
                        🔑 Forgot Password
                    </button>
                </div>
            </div>

            <!-- ── Right Panel ────────────────────────── -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Edit Profile Tab -->
                <div id="tab-profile" class="bg-white rounded-2xl shadow p-6">
                    <h3 class="text-lg font-bold text-gray-700 mb-5 pb-3 border-b">✏️ Edit Profile</h3>
                    <form method="POST" enctype="multipart/form-data">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-400">*</span></label>
                                <input type="text" name="full_name"
                                    value="<?= htmlspecialchars($admin['full_name'] ?? '') ?>"
                                    required pattern="[a-zA-Z\s]+"
                                    class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="Your full name">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                <input type="email" name="email"
                                    value="<?= htmlspecialchars($admin['email'] ?? '') ?>"
                                    class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="your@email.com">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select name="status"
                                    class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="Active" <?= ($admin['status'] ?? '') === 'Active' ? 'selected' : '' ?>>🟢 Active</option>
                                    <option value="Busy"   <?= ($admin['status'] ?? '') === 'Busy'   ? 'selected' : '' ?>>🔴 Busy</option>
                                    <option value="Away"   <?= ($admin['status'] ?? '') === 'Away'   ? 'selected' : '' ?>>🟡 Away</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Profile Picture</label>
                                <input type="file" name="profile_pic" accept="image/*"
                                    class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <p class="text-xs text-gray-400 mt-1">Max 2MB · JPG, PNG, GIF</p>
                            </div>
                        </div>

                        <button type="submit" name="update_profile"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl transition">
                            💾 Save Profile
                        </button>
                    </form>
                </div>

                <!-- Change Password Tab -->
                <div id="tab-password" class="bg-white rounded-2xl shadow p-6 hidden">
                    <h3 class="text-lg font-bold text-gray-700 mb-5 pb-3 border-b">🔒 Change Password</h3>
                    <form method="POST">

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                            <input type="password" name="current_password" required
                                class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="Enter your current password">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                <input type="password" name="new_password" required minlength="6"
                                    class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="Min 6 characters">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                                <input type="password" name="confirm_password" required
                                    class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="Repeat new password">
                            </div>
                        </div>

                        <button type="submit" name="change_password"
                            class="bg-gray-800 hover:bg-gray-900 text-white font-semibold px-6 py-2.5 rounded-xl transition">
                            🔒 Update Password
                        </button>
                    </form>
                </div>

                <!-- Forgot Password Tab -->
                <div id="tab-forgot" class="bg-white rounded-2xl shadow p-6 hidden">
                    <h3 class="text-lg font-bold text-gray-700 mb-2 pb-3 border-b">🔑 Forgot / Reset Password</h3>
                    <p class="text-sm text-gray-500 mb-5">
                        Enter the secret key provided by your developer to reset your password.
                        Contact <span class="text-indigo-600 font-medium">JRSphere</span> if you don't have the key.
                    </p>
                    <form method="POST">

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Secret Key</label>
                            <input type="password" name="secret_key" required
                                class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="Enter secret recovery key">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                <input type="password" name="reset_new_password" required minlength="6"
                                    class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="Min 6 characters">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                                <input type="password" name="reset_confirm_password" required
                                    class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="Repeat new password">
                            </div>
                        </div>

                        <button type="submit" name="reset_password"
                            class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-6 py-2.5 rounded-xl transition">
                            🔑 Reset Password
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <!-- Footer -->
    <div style="margin-left:256px;" class="text-center text-xs text-gray-400 py-4 border-t">
        Built by <a href="about.php" target="_blank" class="text-indigo-500 font-semibold">JRSphere&#8482;</a>
    </div>

    <script>
        function showTab(tab) {
            // Hide all tabs
            document.getElementById('tab-profile').classList.add('hidden');
            document.getElementById('tab-password').classList.add('hidden');
            document.getElementById('tab-forgot').classList.add('hidden');

            // Show selected tab
            document.getElementById('tab-' + tab).classList.remove('hidden');
        }
    </script>

</body>
</html>