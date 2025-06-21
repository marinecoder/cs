<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Error - Courier App</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/public/assets/css/tailwind.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full">
            <div class="text-center">
                <div class="mx-auto h-24 w-24 text-red-500 mb-8">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-full h-full">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h1 class="text-6xl font-bold text-gray-900 mb-4">500</h1>
                <h2 class="text-2xl font-semibold text-gray-700 mb-4">Server Error</h2>
                <p class="text-gray-600 mb-8">
                    Something went wrong on our end. We're working to fix the issue. Please try again later.
                </p>
                <div class="space-y-3">
                    <button onclick="location.reload()" 
                            class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium">
                        Try Again
                    </button>
                    <br>
                    <a href="/" class="text-blue-600 hover:text-blue-800 font-medium">
                        Return to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
