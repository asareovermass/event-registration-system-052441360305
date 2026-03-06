<?php
session_start();

$success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (strlen($name) < 2) {
        $errors[] = "Name must be at least 2 characters.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }

    $usersFile = 'users.json';
    $users = file_exists($usersFile)
        ? json_decode(file_get_contents($usersFile), true)
        : [];

    if (!is_array($users)) $users = [];

    foreach ($users as $user) {
        if ($user['email'] === $email) {
            $errors[] = "Email already registered.";
            break;
        }
    }

    if (empty($errors)) {

        $newUser = [
            'id' => time(),
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'user',
            'profile_pic' => 'https://ui-avatars.com/api/?name=' . urlencode($name),
            'registered_events' => []
        ];

        $users[] = $newUser;
        file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));

        $_SESSION['user'] = $newUser;
        $success = true;

        header("refresh:1.5;url=dashboard.php");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign Up • EventReg Ghana</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-900 via-black to-purple-900 p-6">

<div class="w-full max-w-md">

    <div class="backdrop-blur-xl bg-white/5 border border-white/10 rounded-3xl p-10 shadow-2xl">

        <!-- Logo -->
        <div class="flex justify-center mb-8">
            <div class="w-16 h-16 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-3xl flex items-center justify-center text-white text-3xl font-bold shadow-lg">
                E
            </div>
        </div>

        <h1 class="text-4xl font-bold text-center text-white mb-3">
            Create Account
        </h1>

        <p class="text-zinc-400 text-center mb-8">
            Join Ghana’s fastest growing event platform
        </p>

        <?php if ($success): ?>
            <div class="bg-emerald-500/20 border border-emerald-400 text-emerald-300 p-4 rounded-2xl mb-6 text-center">
                Registration successful! Redirecting...
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-500/20 border border-red-400 text-red-300 p-4 rounded-2xl mb-6">
                <?= implode("<br>", $errors) ?>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form method="POST" class="space-y-6">

            <div>
                <label class="block text-sm text-zinc-400 mb-2">Full Name</label>
                <input type="text" name="name" required
                    class="w-full bg-white/10 border border-white/20 rounded-2xl px-5 py-4 text-white placeholder-zinc-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500/40 outline-none transition">
            </div>

            <div>
                <label class="block text-sm text-zinc-400 mb-2">Email</label>
                <input type="email" name="email" required
                    class="w-full bg-white/10 border border-white/20 rounded-2xl px-5 py-4 text-white placeholder-zinc-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500/40 outline-none transition">
            </div>

            <div>
                <label class="block text-sm text-zinc-400 mb-2">Password</label>
                <input type="password" name="password" required minlength="6"
                    class="w-full bg-white/10 border border-white/20 rounded-2xl px-5 py-4 text-white placeholder-zinc-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500/40 outline-none transition">
            </div>

            <button type="submit"
                class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 py-4 rounded-2xl font-bold text-lg text-white hover:opacity-90 transition shadow-lg">
                Sign Up
            </button>
        </form>

        <p class="text-center text-zinc-400 mt-8">
            Already have an account?
            <a href="login.php" class="text-indigo-400 hover:underline">Sign in</a>
        </p>

    </div>
</div>

</body>
</html>