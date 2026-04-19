<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About JRSphere</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-950 text-white min-h-screen">

    <!-- Navbar -->
    <nav class="bg-gray-900 px-8 py-4 flex justify-between items-center border-b border-gray-800">
        <div class="flex items-center gap-3">
            <img src="assets/jrs.png" alt="JRSphere Logo" class="h-10 w-10 rounded-full">
            <span class="text-xl font-bold text-blue-400">JRSphere</span>
        </div>
        <a href="login.php" class="text-sm text-gray-400 hover:text-white transition">Back to App</a>
    </nav>

    <!-- Hero Section -->
    <section class="flex flex-col items-center justify-center text-center py-20 px-6">
        <img src="assets/jrs.png" alt="JRSphere" class="h-32 w-32 rounded-full mb-6 shadow-lg shadow-blue-500/30">
        <h1 class="text-5xl font-extrabold text-blue-400 mb-4">JRSphere&#8482;</h1>
        <p class="text-gray-300 text-lg max-w-xl leading-relaxed">
            We are JRSphere &#8212; a next-gen tech startup from Bangladesh, turning ideas into powerful software products. We build fast, we build smart.
        </p>
        <a href="https://www.linkedin.com/company/jrsphere%E2%84%A2/" target="_blank"
            class="mt-6 inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-full transition duration-200">
            Follow us on LinkedIn
        </a>
    </section>

    <!-- Divider -->
    <div class="border-t border-gray-800 mx-12"></div>

    <!-- Co-Founders Section -->
    <section class="py-16 px-6">
        <h2 class="text-3xl font-bold text-center text-white mb-12">Meet the Co-Founders</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">

            <!-- Junayed -->
            <div class="bg-gray-900 rounded-2xl p-6 text-center border border-gray-800 hover:border-blue-500 transition duration-200">
                <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">J</div>
                <h3 class="text-xl font-bold text-white">Junayed Hossain</h3>
                <p class="text-gray-400 text-sm mt-1 mb-4">Co-Founder</p>
                <a href="https://www.linkedin.com/in/junayed95-" target="_blank"
                    class="inline-block bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-full transition duration-200">
                    LinkedIn
                </a>
            </div>

            <!-- Eaftekhirul -->
            <div class="bg-gray-900 rounded-2xl p-6 text-center border border-gray-800 hover:border-blue-500 transition duration-200">
                <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">E</div>
                <h3 class="text-xl font-bold text-white">Md Eaftekhirul Islam</h3>
                <p class="text-gray-400 text-sm mt-1 mb-4">Co-Founder</p>
                <a href="https://www.linkedin.com/in/eaftekhirul/" target="_blank"
                    class="inline-block bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-full transition duration-200">
                    LinkedIn
                </a>
            </div>

            <!-- Rayhan -->
            <div class="bg-gray-900 rounded-2xl p-6 text-center border border-gray-800 hover:border-blue-500 transition duration-200">
                <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">R</div>
                <h3 class="text-xl font-bold text-white">Rayhan Ebne Zahir</h3>
                <p class="text-gray-400 text-sm mt-1 mb-4">Co-Founder</p>
                <a href="https://www.linkedin.com/in/rayhan-ebna-zahir-1b8471316/" target="_blank"
                    class="inline-block bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-full transition duration-200">
                    LinkedIn
                </a>
            </div>

        </div>
    </section>

    <!-- Divider -->
    <div class="border-t border-gray-800 mx-12"></div>

    <!-- Project Section -->
    <section class="py-16 px-6 text-center">
        <h2 class="text-3xl font-bold text-white mb-4">Our First Project</h2>
        <p class="text-gray-400 max-w-xl mx-auto">
            JRS OfficeTrack is JRSphere's first real client project &#8212; a local office management system built for a home-service business in Bangladesh.
        </p>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 border-t border-gray-800 text-center py-6">
        <p class="text-gray-500 text-sm">&#169; 2026 JRSphere&#8482; &#8212; All rights reserved.</p>
    </footer>

</body>
</html>