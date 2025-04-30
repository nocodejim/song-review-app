<?php // public/index.php (Final v1) ?>
<?php
// Ensure errors are displayed during development (from docker-compose env)
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// Include the database configuration file.
// Path is relative to this file's location inside the container.
require_once __DIR__ . '/../config/database.php';

// --- Configuration ---
// In a larger app, this might come from a config file or database.
$song_id = 'help_me'; // Unique identifier for the song being reviewed
$song_title = "Help Me"; // Display title for the song
$version3_file = 'assets/audio/help_me_v3.mp3'; // Path relative to public/
$version4_file = 'assets/audio/help_me_v4.mp3'; // Path relative to public/
$background_video = 'assets/video/bg-video.mp4'; // Path relative to public/

// --- Status Message Handling ---
// Check for status messages passed via GET parameters after form submission/redirect.
$status_message = '';
$status_type = ''; // Used for CSS styling ('success' or 'error')

if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'success':
            $status_message = 'Thank you! Your feedback has been submitted successfully.';
            $status_type = 'success';
            break;
        case 'error':
            $error_code = $_GET['code'] ?? 'unknown'; // Get specific error code if provided
            // Basic user message - avoid revealing too much detail.
            $status_message = 'Sorry, there was an error submitting your feedback. Please try again. (Code: ' . htmlspecialchars($error_code) . ')';
            $status_type = 'error';
            // Log the error code server-side for debugging.
            error_log("Feedback submission failed. Status: error, Code received via GET: " . $error_code);
            break;
        case 'invalid':
            $status_message = 'Invalid input. Please make sure you select a song version.';
            $status_type = 'error';
            break;
        // Add more cases if needed
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Song Review: <?php echo htmlspecialchars($song_title); // Escape title for security ?></title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">

    <style>
        /* Apply Inter font globally */
        body {
            font-family: 'Inter', sans-serif;
        }

        /* Style for the background video */
        #bg-video {
            position: fixed; /* Fix position relative to viewport */
            right: 0;
            bottom: 0;
            min-width: 100%; /* Ensure video covers width */
            min-height: 100%; /* Ensure video covers height */
            width: auto; /* Maintain aspect ratio */
            height: auto; /* Maintain aspect ratio */
            z-index: -100; /* Place behind all other content */
            background-size: cover; /* Cover the area */
            /* Fallback background color/image if video fails */
            background: black url('assets/video/fallback-image.jpg') no-repeat center center;
            /* Mute video by default */
            /* `playsinline` helps with autoplay on mobile */
        }

        /* Semi-transparent overlay to improve text readability over video */
        .video-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6); /* Adjust opacity as needed */
            z-index: -50; /* Place overlay between video and content */
        }

        /* Ensure main content is above the overlay */
        .content-container {
            position: relative;
            z-index: 1;
        }

        /* Style audio players for consistency */
        audio {
            width: 100%; /* Make players responsive */
            margin-top: 0.5rem; /* Add some space above */
            border-radius: 0.375rem; /* Match Tailwind's rounded-md */
            /* Consider custom styling for audio player controls if desired */
        }

        /* Custom styles for status messages (using Tailwind color palette) */
        .status-success {
            background-color: #dcfce7; /* Tailwind green-100 */
            color: #166534; /* Tailwind green-800 */
            border-color: #86efac; /* Tailwind green-300 */
        }
        .status-error {
            background-color: #fee2e2; /* Tailwind red-100 */
            color: #991b1b; /* Tailwind red-800 */
            border-color: #fca5a5; /* Tailwind red-300 */
        }
        .status-message {
            padding: 1rem; /* Tailwind p-4 */
            margin-bottom: 1.5rem; /* Tailwind mb-6 */
            border-left-width: 4px; /* Tailwind border-l-4 */
            border-radius: 0.375rem; /* Tailwind rounded-md */
            font-weight: 500; /* Medium weight */
        }

    </style>
</head>
<body class="bg-black text-gray-100">

    <video autoplay muted loop playsinline id="bg-video">
        <source src="<?php echo htmlspecialchars($background_video); ?>" type="video/mp4">
        Your browser does not support the video tag. Consider using a modern browser.
        </video>
    <div class="video-overlay"></div>

    <div class="content-container container mx-auto px-4 py-8 min-h-screen flex flex-col items-center justify-center">

        <div class="bg-white bg-opacity-10 backdrop-filter backdrop-blur-lg p-6 sm:p-8 md:p-10 rounded-xl shadow-2xl w-full max-w-2xl">

            <h1 class="text-3xl sm:text-4xl font-bold text-center mb-6 text-white">
                Review Song: "<?php echo htmlspecialchars($song_title); ?>"
            </h1>

            <?php if ($status_message): ?>
                <div class="status-message <?php echo ($status_type === 'success' ? 'status-success' : 'status-error'); ?>" role="alert">
                    <?php echo htmlspecialchars($status_message); // Escape message content ?>
                </div>
            <?php endif; ?>

            <form action="submit_feedback.php" method="POST" class="space-y-6">
                <input type="hidden" name="song_id" value="<?php echo htmlspecialchars($song_id); ?>">

                <fieldset class="space-y-4">
                    <legend class="text-xl font-semibold mb-4 text-gray-200">Listen to the versions:</legend>
                    <div>
                        <label for="version3" class="block text-lg font-medium text-gray-300">Version 3</label>
                        <audio id="version3" controls preload="metadata">
                            <source src="<?php echo htmlspecialchars($version3_file); ?>" type="audio/mpeg">
                            Your browser does not support the audio element.
                        </audio>
                    </div>
                    <div>
                        <label for="version4" class="block text-lg font-medium text-gray-300">Version 4</label>
                        <audio id="version4" controls preload="metadata">
                            <source src="<?php echo htmlspecialchars($version4_file); ?>" type="audio/mpeg">
                            Your browser does not support the audio element.
                        </audio>
                    </div>
                </fieldset>

                <fieldset class="space-y-2">
                    <legend class="text-xl font-semibold mb-3 text-gray-200">Which version do you prefer?</legend>
                    <div class="flex items-center space-x-6"> <div class="flex items-center">
                            <input id="choice_v3" name="song_choice" type="radio" value="v3" required
                                   class="focus:ring-indigo-500 h-5 w-5 text-indigo-400 border-gray-500 rounded bg-gray-700 bg-opacity-50 cursor-pointer">
                            <label for="choice_v3" class="ml-3 block text-lg font-medium text-gray-300 cursor-pointer">
                                Version 3
                            </label>
                        </div>
                         <div class="flex items-center">
                            <input id="choice_v4" name="song_choice" type="radio" value="v4" required
                                   class="focus:ring-indigo-500 h-5 w-5 text-indigo-400 border-gray-500 rounded bg-gray-700 bg-opacity-50 cursor-pointer">
                            <label for="choice_v4" class="ml-3 block text-lg font-medium text-gray-300 cursor-pointer">
                                Version 4
                            </label>
                        </div>
                    </div>
                     <div id="song-choice-error" class="text-red-400 text-sm mt-1" aria-live="polite"></div>
                </fieldset>

                <div>
                    <label for="feedback_text" class="block text-xl font-semibold mb-2 text-gray-200">Feedback (Optional):</label>
                    <textarea id="feedback_text" name="feedback_text" rows="4"
                              class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border border-gray-600 rounded-md bg-gray-700 bg-opacity-50 text-gray-100 placeholder-gray-400 p-3 transition duration-150 ease-in-out"
                              placeholder="Explain why you prefer that version..."></textarea>
                </div>

                <div>
                    <button type="submit"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-lg font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 focus:ring-offset-gray-800 transition duration-150 ease-in-out">
                        Submit Feedback
                    </button>
                </div>
            </form>
        </div> <footer class="text-center text-gray-400 mt-8 pb-4">
            <p>&copy; <?php echo date("Y"); ?> Your Band/Project Name</p>
        </footer>

    </div> <script>
        // Simple client-side check to guide user if they haven't selected a radio button.
        // This improves UX but server-side validation in submit_feedback.php is ESSENTIAL for security.
        const form = document.querySelector('form');
        const radioError = document.getElementById('song-choice-error');

        if (form) { // Ensure form exists before adding listener
            form.addEventListener('submit', function(event) {
                const selectedChoice = document.querySelector('input[name="song_choice"]:checked');
                if (!selectedChoice) {
                    radioError.textContent = 'Please select your preferred version.';
                    event.preventDefault(); // Stop form submission if no choice is made
                } else {
                    radioError.textContent = ''; // Clear error message if choice is made
                }
            });
        }

        // Attempt to ensure video plays on various devices/browsers.
        // The `autoplay muted loop playsinline` attributes handle most cases.
        document.addEventListener('DOMContentLoaded', (event) => {
            const video = document.getElementById('bg-video');
            if (video && video.paused) { // Check if video element exists and is paused
                 // A user interaction might still be required on some mobile browsers
                 // for autoplay to work reliably due to browser policies.
                 // video.play().catch(error => console.warn("Video autoplay might be blocked by browser policy.", error));
            }
        });
    </script>

</body>
</html>
