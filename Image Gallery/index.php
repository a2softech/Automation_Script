<?php
$dir = './'; // Image folder
$images = array_values(array_filter(scandir($dir), function($file) {
    return !in_array($file, ['.', '..', 'index.php']);
}));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Photo Gallery</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
            padding: 20px;
        }
        .image-box {
            background: white;
            padding: 10px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: 0.3s;
            overflow: hidden;
            position: relative;
        }
        .image-box:hover {
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.2);
            transform: translateY(-5px);
        }
        .image-box img {
            width: 100%;
            height: auto;
            cursor: pointer;
            border-radius: 12px;
            transition: transform 0.3s ease, filter 0.3s ease;
        }
        .image-box:hover img {
            transform: scale(1.1);
            filter: brightness(90%);
        }
        .image-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
            padding: 8px;
            background: #f3f4f6;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            color: #333;
        }
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .overlay img {
            max-width: 95%;
            max-height: 95%;
            border-radius: 12px;
            display: block;
            margin: auto;
        }
        .overlay.active {
            display: flex;
        }
        .share-icon {
            background: rgba(0, 0, 0, 0.6);
            color: white;
            padding: 6px;
            border-radius: 50%;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
        }
        .share-icon:hover {
            background: rgba(0, 0, 0, 0.8);
        }
    </style>
</head>
<body class="bg-gray-100">
    <h1 class="text-center text-3xl font-bold my-6 text-gray-800">Photo Gallery</h1>
    
    <div class="gallery">
        <?php foreach ($images as $image): ?>
            <?php $imageName = pathinfo($image, PATHINFO_FILENAME); ?>
            <div class="image-box">
                <img src="<?= $dir . $image ?>" alt="<?= $imageName ?>" class="open-image">
                <div class="image-footer">
                    <span class="share-icon" onclick="shareImage('<?= $dir . $image ?>')">🔗</span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="overlay" id="overlay">
        <button id="prevBtn" class="absolute left-4 text-white text-3xl bg-black/50 px-4 py-2 rounded-full hover:bg-black/70 z-50">&#8592;</button>
        <img id="fullImage" src="" alt="">
        <button id="nextBtn" class="absolute right-4 text-white text-3xl bg-black/50 px-4 py-2 rounded-full hover:bg-black/70 z-50">&#8594;</button>
    </div>

    <script>
        let images = <?php echo json_encode(array_values($images)); ?>;
        let currentIndex = 0;

        $(document).ready(function () {
            $('.open-image').click(function () {
                let src = $(this).attr('src');
                currentIndex = images.findIndex(img => src.includes(img));
                showImage(currentIndex);
            });

            $('#overlay').click(function (e) {
                if (e.target.id === 'overlay') {
                    $(this).removeClass('active');
                }
            });

            $('#prevBtn').click(function (e) {
                e.stopPropagation();
                currentIndex = (currentIndex - 1 + images.length) % images.length;
                showImage(currentIndex);
            });

            $('#nextBtn').click(function (e) {
                e.stopPropagation();
                currentIndex = (currentIndex + 1) % images.length;
                showImage(currentIndex);
            });

            $(document).keydown(function (e) {
                if (!$('#overlay').hasClass('active')) return;

                if (e.key === 'ArrowRight') {
                    $('#nextBtn').click();
                } else if (e.key === 'ArrowLeft') {
                    $('#prevBtn').click();
                } else if (e.key === 'Escape') {
                    $('#overlay').removeClass('active');
                }
            });
        });

        function showImage(index) {
            $('#fullImage').attr('src', './' + images[index]);
            $('#overlay').addClass('active');
        }

        function shareImage(imageUrl) {
            if (navigator.share) {
                navigator.share({
                    title: 'Check out this image',
                    url: imageUrl
                }).catch(console.error);
            } else {
                prompt('Copy this link to share:', imageUrl);
            }
        }
    </script>
</body>
</html>
