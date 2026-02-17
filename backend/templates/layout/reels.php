<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Reels - WeLinked</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Prevent pull-to-refresh on iOS */
        body {
            overscroll-behavior-y: contain;
            margin: 0;
            padding: 0;
        }
        
        /* Hide scrollbar but keep functionality */
        #reels-feed::-webkit-scrollbar {
            display: none;
        }
        #reels-feed {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        
        /* Smooth snap scrolling */
        .reel-item {
            scroll-snap-align: start;
            scroll-snap-stop: always;
        }
    </style>
</head>
<body class="bg-black overflow-hidden">
    <?= $this->fetch('content') ?>
</body>
</html>
