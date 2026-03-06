<?php
session_start();

/*
| Load Events (Create Sample If Not Exists)
*/
$eventsFile = 'events.json';

if (!file_exists($eventsFile)) {
    $sampleEvents = [
        [
            "id" => 1,
            "title" => "Kumasi Tech Summit 2026",
            "description" => "Biggest tech & startup gathering in Ashanti. 500+ attendees, investors, workshops.",
            "date" => "2026-04-15",
            "location" => "KICC, Kumasi",
            "image" => "https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800",
            "capacity" => 500
        ],
        [
            "id" => 2,
            "title" => "Adinkra Cultural Festival",
            "description" => "Traditional music, dance, fashion & food celebrating Ashanti heritage.",
            "date" => "2026-03-20",
            "location" => "Manhyia Palace",
            "image" => "https://images.unsplash.com/photo-1517457373958-b7bdd4587205?w=800",
            "capacity" => 1500
        ],
        [
            "id" => 3,
            "title" => "Ghana Startup Weekend",
            "description" => "54-hour startup building event. Pitch, build, win funding.",
            "date" => "2026-05-08",
            "location" => "KNUST IdeaSpace",
            "image" => "https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=800",
            "capacity" => 120
        ]
    ];

    file_put_contents($eventsFile, json_encode($sampleEvents, JSON_PRETTY_PRINT));
    $events = $sampleEvents;
} else {
    $events = json_decode(file_get_contents($eventsFile), true) ?? [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventReg Ghana • Home</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', system_ui, sans-serif; }
        .hero-bg {
            background: linear-gradient(rgba(0,0,0,0.75), rgba(0,0,0,0.85)), url('https://picsum.photos/id/1015/1920/1080') center/cover no-repeat;
        }
        .event-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .event-card:hover {
            transform: translateY(-12px) scale(1.02);
            box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.4);
        }
    </style>
</head>
<body class="bg-zinc-950 text-white antialiased">

<!-- ================= NAVBAR ================= -->
<nav class="bg-zinc-900/80 backdrop-blur-md border-b border-zinc-800 sticky top-0 z-50">
<div class="max-w-7xl mx-auto px-6 py-5 flex items-center justify-between">

<div class="flex items-center gap-3">
<div class="w-10 h-10 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-2xl flex items-center justify-center font-bold text-2xl">E</div>
<span class="text-2xl font-bold tracking-tight">EventReg</span>
</div>

<div class="hidden md:flex items-center gap-10 text-sm font-medium">
<a href="index.php" class="hover:text-indigo-400 transition">Home</a>
<a href="#events" class="hover:text-indigo-400 transition">Events</a>
<a href="#" class="hover:text-indigo-400 transition">About</a>
</div>

<div class="flex items-center gap-4">
<?php if (isset($_SESSION['user'])): ?>
    <a href="dashboard.php" class="px-6 py-2.5 bg-white/10 hover:bg-white/20 border border-white/20 rounded-2xl flex items-center gap-2">
        <img 
        src="<?= htmlspecialchars($_SESSION['user']['profile_pic'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['user']['name'])) ?>"
        class="w-7 h-7 rounded-full object-cover">
        <span><?= htmlspecialchars($_SESSION['user']['name']) ?></span>
    </a>
    <a href="logout.php" class="px-6 py-2.5 bg-red-600/20 hover:bg-red-600/30 border border-red-500/30 rounded-2xl">
        Logout
    </a>
<?php else: ?>
    <a href="login.php" class="px-6 py-2.5 hover:bg-white/10 rounded-2xl transition">Login</a>
    <a href="register.php" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 rounded-2xl font-semibold transition">
        Sign Up
    </a>
<?php endif; ?>
</div>

</div>
</nav>

<!-- ================= HERO ================= -->
<section class="hero-bg h-screen flex items-center">
<div class="max-w-5xl mx-auto px-6 text-center">

<div class="inline-flex items-center gap-3 bg-white/10 px-6 py-2.5 rounded-full text-sm mb-8 border border-white/10">
<span class="w-2.5 h-2.5 bg-emerald-400 rounded-full animate-pulse"></span>
Kumasi • Ashanti • Ghana
</div>

<h1 class="text-6xl md:text-7xl font-extrabold leading-tight mb-8">
Discover & Join the Best Events in
<span class="bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">
Ghana
</span>
</h1>

<p class="text-xl text-zinc-300 max-w-3xl mx-auto mb-12">
From cultural festivals to tech summits — your next unforgettable experience is waiting.
</p>

<a href="#events"
class="px-10 py-5 bg-white text-zinc-900 rounded-2xl font-bold text-lg hover:scale-105 transition">
Explore Events
</a>

</div>
</section>

<!-- ================= EVENTS ================= -->
<section id="events" class="py-28 bg-gradient-to-b from-black via-zinc-950 to-black">
<div class="max-w-7xl mx-auto px-6">

    <!-- Section Header -->
    <div class="text-center mb-20">
        <h2 class="text-5xl md:text-6xl font-bold bg-gradient-to-r from-indigo-400 to-purple-500 bg-clip-text text-transparent">
            Upcoming Events
        </h2>
        <p class="text-zinc-400 mt-6 max-w-2xl mx-auto">
            Discover Ghana’s most exciting conferences, concerts, and experiences.
        </p>
    </div>

    <?php if (empty($events)): ?>
        <!-- Empty State -->
        <div class="text-center text-zinc-500 py-20">
            <i class="fa-solid fa-calendar-xmark text-5xl mb-6"></i>
            <p class="text-lg">No upcoming events yet.</p>
        </div>
    <?php else: ?>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-10">

    <?php foreach ($events as $event): 

        $eventDate = isset($event['date']) ? strtotime($event['date']) : null;
        $eventTitle = $event['title'] ?? 'Untitled Event';
        $eventDesc  = $event['description'] ?? 'No description available.';
        $eventImage = $event['image'] ?? 'https://via.placeholder.com/800x400';
        
        $registered = isset($_SESSION['registered_events']) &&
                      in_array($event['id'] ?? 0, $_SESSION['registered_events']);
    ?>

    <div class="group bg-white/5 backdrop-blur-xl border border-white/10 rounded-3xl overflow-hidden shadow-xl hover:shadow-indigo-500/20 hover:-translate-y-2 transition duration-300">

        <!-- Image -->
        <div class="relative overflow-hidden">
            <img src="<?= htmlspecialchars($eventImage) ?>"
                 class="w-full h-64 object-cover group-hover:scale-110 transition duration-500"
                 alt="<?= htmlspecialchars($eventTitle) ?>">

            <!-- Date Badge -->
            <div class="absolute top-4 left-4 bg-black/60 backdrop-blur-md text-white text-xs px-4 py-2 rounded-full border border-white/10">
                <?= $eventDate ? date('d M Y', $eventDate) : 'TBA' ?>
            </div>
        </div>

        <div class="p-8">

            <!-- Title -->
            <h3 class="text-xl font-bold text-white mb-4 line-clamp-2">
                <?= htmlspecialchars($eventTitle) ?>
            </h3>

            <!-- Description -->
            <p class="text-zinc-400 mb-8 line-clamp-3">
                <?= htmlspecialchars($eventDesc) ?>
            </p>

            <?php if (isset($_SESSION['user'])): ?>
                <form method="POST" action="dashboard.php">
                    <input type="hidden" name="event_id" value="<?= $event['id'] ?? 0 ?>">

                    <?php if ($registered): ?>
                        <button type="submit" name="action" value="cancel_registration"
                                class="w-full py-4 bg-emerald-500/20 border border-emerald-400 text-emerald-300 rounded-2xl font-semibold hover:bg-emerald-500/30 transition">
                            ✓ Registered
                        </button>
                    <?php else: ?>
                        <button type="submit" name="action" value="register_event"
                                class="w-full py-4 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl font-semibold text-white hover:opacity-90 transition shadow-lg">
                            Register Now
                        </button>
                    <?php endif; ?>
                </form>
            <?php else: ?>
                <a href="login.php"
                   class="block w-full py-4 bg-white/10 border border-white/20 hover:bg-white/20 rounded-2xl font-semibold text-center text-white transition">
                   Login to Register
                </a>
            <?php endif; ?>

        </div>
    </div>

    <?php endforeach; ?>
    </div>

    <?php endif; ?>

</div>
</section>

<!-- ================= FOOTER ================= -->
<footer class="bg-black py-20 border-t border-white/5 text-center text-zinc-500 text-sm">
    <div class="mb-4 text-white font-semibold">
        EventReg Ghana
    </div>
    © <?= date('Y') ?> All rights reserved • Built with passion in Kumasi
</footer>