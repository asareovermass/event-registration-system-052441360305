<?php
session_start();

/* ────────────────────────────────────────────────
   AUTH CHECK
──────────────────────────────────────────────── */
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

/* INITIALIZE VARIABLES */
$_SESSION['registered_events'] = $_SESSION['registered_events'] ?? [];
$isAdmin = ($_SESSION['user']['role'] ?? '') === 'admin';
$eventsFile = 'events.json';
$events = file_exists($eventsFile) ? json_decode(file_get_contents($eventsFile), true) : [];
if (!is_array($events)) $events = [];

$msg = '';
$err = '';

/* ────────────────────────────────────────────────
   HANDLE POST ACTIONS
──────────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    // Register Event
    if ($action === 'register_event' && isset($_POST['event_id'])) {
        $eid = (int) $_POST['event_id'];
        if (!in_array($eid, $_SESSION['registered_events'])) {
            $_SESSION['registered_events'][] = $eid;
            $msg = "Registered successfully!";
        }
    }

    // Cancel Registration
    if ($action === 'cancel_registration' && isset($_POST['event_id'])) {
        $eid = (int) $_POST['event_id'];
        $_SESSION['registered_events'] = array_values(array_diff($_SESSION['registered_events'], [$eid]));
        $msg = "Registration cancelled.";
    }

    // Update Profile Picture
    if ($action === 'update_profile' && isset($_FILES['profile_pic'])) {

        if ($_FILES['profile_pic']['error'] === 0) {

            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $fileName = $_FILES['profile_pic']['name'];
            $fileSize = $_FILES['profile_pic']['size'];
            $fileTmp  = $_FILES['profile_pic']['tmp_name'];
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if (in_array($ext, $allowed) && $fileSize <= 5 * 1024 * 1024) {

                $uploadDir = 'profiles/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                $newFileName = $_SESSION['user']['id'] . '_' . time() . '.' . $ext;
                $uploadPath = $uploadDir . $newFileName;

                if (move_uploaded_file($fileTmp, $uploadPath)) {
                    $_SESSION['user']['profile_pic'] = $uploadPath; // relative path
                    $msg = "Profile picture updated successfully!";
                } else {
                    $err = "Failed to save the uploaded image.";
                }

            } else {
                $err = "Invalid file. Only JPG, PNG, GIF allowed (max 5MB).";
            }

        } else {
            $err = "No file uploaded or upload error occurred.";
        }
    }

    // Admin: Save Event
    if ($isAdmin && $action === 'save_event') {
        $newEvent = [
            'id' => !empty($_POST['id']) ? (int) $_POST['id'] : time(),
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'date' => $_POST['date'] ?? '',
            'location' => trim($_POST['location'] ?? ''),
            'capacity' => (int) ($_POST['capacity'] ?? 100),
            'image' => $_POST['current_image'] ?? 'https://via.placeholder.com/800x400'
        ];

        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === 0) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','gif'])) {
                $dir = 'uploads/';
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                $filename = time() . '_' . basename($_FILES['image']['name']);
                $path = $dir . $filename;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $path)) {
                    $newEvent['image'] = $path;
                }
            }
        }

        $found = false;
        foreach ($events as &$ev) {
            if ($ev['id'] === $newEvent['id']) {
                $ev = $newEvent;
                $found = true;
                break;
            }
        }
        if (!$found) $events[] = $newEvent;

        file_put_contents($eventsFile, json_encode($events, JSON_PRETTY_PRINT));
        $msg = "Event saved!";
    }

    // Admin: Delete Event
    if ($isAdmin && $action === 'delete_event' && isset($_POST['id'])) {
        $deleteId = (int) $_POST['id'];
        $events = array_values(array_filter($events, fn($e) => $e['id'] !== $deleteId));
        file_put_contents($eventsFile, json_encode($events, JSON_PRETTY_PRINT));
        $msg = "Event deleted.";
    }
}

/* ────────────────────────────────────────────────
   MY EVENTS
──────────────────────────────────────────────── */
$myEvents = array_filter($events, fn($e) => in_array($e['id'], $_SESSION['registered_events']));

/* EDIT MODE */
$editEvent = null;
if ($isAdmin && isset($_GET['edit'])) {
    $eid = (int) $_GET['edit'];
    foreach ($events as $e) {
        if ($e['id'] === $eid) { $editEvent = $e; break; }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard • EventReg Ghana</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-zinc-950 text-white min-h-screen">

<div class="flex">

    <!-- SIDEBAR -->
    <aside class="hidden lg:block w-72 bg-zinc-900 border-r border-zinc-800 p-8 fixed h-screen overflow-y-auto">

        <!-- Logo -->
        <div class="flex items-center gap-3 mb-12">
            <div class="w-12 h-12 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-3xl flex items-center justify-center text-3xl font-bold">E</div>
            <span class="text-3xl font-bold">EventReg</span>
        </div>

        <!-- Navigation -->
        <nav class="space-y-2 mb-12">
            <a href="dashboard.php" class="flex items-center gap-4 px-6 py-4 bg-zinc-800 rounded-2xl font-medium">
                <i class="fa-solid fa-house w-5"></i> Dashboard
            </a>
            <a href="index.php" class="flex items-center gap-4 px-6 py-4 hover:bg-zinc-800 rounded-2xl transition">
                <i class="fa-solid fa-compass w-5"></i> Discover
            </a>
            <?php if ($isAdmin): ?>
            <a href="#admin-section" class="flex items-center gap-4 px-6 py-4 hover:bg-zinc-800 rounded-2xl transition">
                <i class="fa-solid fa-crown w-5"></i> Admin Panel
            </a>
            <?php endif; ?>
        </nav>

        <!-- Profile -->
        <div class="border-t border-zinc-800 pt-8 text-center">
            <div class="relative group mb-4">
                <img src="<?= htmlspecialchars($_SESSION['user']['profile_pic'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['user']['name'])) ?>"
                     class="w-24 h-24 rounded-3xl object-cover border-4 border-zinc-800 shadow-xl">
                <label for="profile-upload" class="absolute inset-0 flex items-center justify-center bg-black/60 rounded-3xl opacity-0 group-hover:opacity-100 transition cursor-pointer">
                    <i class="fa-solid fa-camera text-3xl"></i>
                </label>
                <form method="POST" enctype="multipart/form-data">
                    <input type="file" id="profile-upload" name="profile_pic" accept="image/*" class="hidden" onchange="this.form.submit()">
                    <input type="hidden" name="action" value="update_profile">
                </form>
            </div>

            <div class="font-bold text-xl"><?= htmlspecialchars($_SESSION['user']['name']) ?></div>
            <div class="text-sm text-zinc-400 mb-2"><?= htmlspecialchars($_SESSION['user']['email']) ?></div>
            <div class="text-xs px-4 py-1 rounded-full <?= $isAdmin ? 'bg-amber-500/20 text-amber-300' : 'bg-emerald-500/20 text-emerald-300' ?>">
                <?= $isAdmin ? 'Administrator' : 'Member' ?>
            </div>
            <a href="logout.php" class="mt-10 block py-3 border border-red-600/30 text-red-400 rounded-2xl hover:bg-red-600/10 transition">Sign Out</a>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 lg:ml-72 p-6 lg:p-12">
        <div class="max-w-6xl mx-auto">

            <?php if ($msg): ?>
                <div class="bg-emerald-500/10 border border-emerald-600 text-emerald-300 p-6 rounded-2xl mb-10"><?= htmlspecialchars($msg) ?></div>
            <?php endif; ?>

            <?php if ($err): ?>
                <div class="bg-red-500/10 border border-red-600 text-red-300 p-6 rounded-2xl mb-10"><?= htmlspecialchars($err) ?></div>
            <?php endif; ?>

            <h1 class="text-5xl font-bold mb-3">Dashboard</h1>
            <p class="text-zinc-400 mb-12">Good to see you, <?= explode(' ', $_SESSION['user']['name'])[0] ?>!</p>

            <!-- MY EVENTS -->
            <section class="mb-12">
                <h2 class="text-3xl font-bold mb-6">My Events</h2>
                <?php if ($myEvents): ?>
                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                        <?php foreach ($myEvents as $e): ?>
                            <div class="bg-zinc-900 rounded-3xl overflow-hidden border border-zinc-800 group hover:border-indigo-600/50 transition-all duration-300">
                                <img src="<?= htmlspecialchars($e['image']) ?>" class="w-full h-48 object-cover">
                                <div class="p-5">
                                    <h3 class="text-xl font-bold mb-2"><?= htmlspecialchars($e['title']) ?></h3>
                                    <p class="text-zinc-400 mb-4"><?= htmlspecialchars($e['location']) ?> | <?= date('d M Y', strtotime($e['date'])) ?></p>
                                    <form method="POST">
                                        <input type="hidden" name="action" value="cancel_registration">
                                        <input type="hidden" name="event_id" value="<?= $e['id'] ?>">
                                        <button type="submit" class="w-full py-3 bg-red-600/20 hover:bg-red-600/30 rounded-2xl text-red-300 font-semibold transition">Cancel Registration</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-zinc-400">You have not registered for any events yet.</p>
                <?php endif; ?>
            </section>

            <!-- ADMIN PANEL -->
            <?php if ($isAdmin): ?>
            <section id="admin-section" class="mb-12">
                <h2 class="text-3xl font-bold mb-6">Admin Panel</h2>
                <p class="text-zinc-400 mb-6">Add or edit events.</p>
                <!-- Add your admin form and event list here -->
            </section>
            <?php endif; ?>

        </div>
    </main>

</div>

</body>
</html>