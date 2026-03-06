<?php
session_start();

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $message = "Please fill in all fields.";
    } else {

        /* ================= ADMIN LOGIN ================= */
        if ($email === 'admin@eventreg.gh' && $password === 'admin2026') {

            $_SESSION['user'] = [
                'id' => 999999,
                'name' => 'Admin',
                'email' => $email,
                'role' => 'admin',
                'profile_pic' => 'https://ui-avatars.com/api/?name=Admin&background=4f46e5&color=fff'
            ];

            header("Location: dashboard.php");
            exit;
        }

        /* ================= USER LOGIN ================= */
        $usersFile = 'users.json';

        if (file_exists($usersFile)) {

            $users = json_decode(file_get_contents($usersFile), true);

            if (is_array($users)) {

                foreach ($users as $user) {

                    if (
                        strtolower($user['email']) === strtolower($email) &&
                        password_verify($password, $user['password'])
                    ) {

                        $_SESSION['user'] = $user;
                        header("Location: dashboard.php");
                        exit;
                    }
                }
            }
        }

        $message = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign In • EventReg Ghana</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-950 via-indigo-950 to-purple-950 p-6">

<div class="w-full max-w-md">

    <div class="bg-white/5 backdrop-blur-2xl border border-white/10 rounded-3xl p-10 shadow-2xl">

        <!-- Logo -->
        <div class="flex justify-center mb-8">
            <div class="w-16 h-16 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-3xl flex items-center justify-center text-white text-3xl font-bold shadow-lg">
                E
            </div>
        </div>

        <h1 class="text-4xl font-bold text-white text-center mb-2">
            Welcome Back
        </h1>

        <p class="text-zinc-400 text-center mb-8">
            Sign in to your account
        </p>

        <?php if (!empty($message)): ?>
            <div class="bg-red-500/20 border border-red-400 text-red-300 p-4 rounded-2xl mb-6 text-center">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">

            <div>
                <label class="block text-sm text-zinc-400 mb-2">Email</label>
                <input type="email" name="email" required
                    class="w-full bg-white/10 border border-white/20 rounded-2xl px-5 py-4 text-white placeholder-zinc-500 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500/40 outline-none transition">
            </div>

            <div>
                <label class="block text-sm text-zinc-400 mb-2">Password</label>
                <div class="relative">
                    <input type="password" name="password" id="password" required
                        class="w-full bg-white/10 border border-white/20 rounded-2xl px-5 py-4 text-white placeholder-zinc-500 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500/40 outline-none transition">

                    <i class="fa-solid fa-eye absolute right-5 top-5 text-zinc-400 cursor-pointer"
                       onclick="togglePassword()"></i>
                </div>
            </div>

            <button type="submit"
                class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 py-4 rounded-2xl font-bold text-lg text-white hover:opacity-90 transition shadow-lg">
                Sign In
            </button>

        </form>

        <p class="text-center text-zinc-400 mt-8">
            Don’t have an account?
            <a href="register.php" class="text-indigo-400 hover:underline">
                Create one
            </a>
        </p>

        <div class="mt-10 text-center text-xs text-zinc-600">
            Admin demo: admin@eventreg.gh / admin2026
        </div>

    </div>
</div>

<script>
function togglePassword() {
    const input = document.getElementById("password");
    input.type = input.type === "password" ? "text" : "password";
}
</script>

</body>
</html>