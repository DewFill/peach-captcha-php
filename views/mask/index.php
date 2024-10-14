<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/views/captcha/style.css">
    <script defer src="/views/mask/index.js"></script>
    <title>Drawing app</title>
    <style>
        canvas {
            border: 1px solid black;
        }
    </style>
</head>
<body>
<section class="container">
    <div id="toolbar">
        <h1>MASK</h1>

        <label for="point_tolerance">Point tolerance (px)</label>
        <input id="point_tolerance" name='point_tolerance' type="number" value="10" min="1" step="1">

        <label for="min_percentage_match_1">Min checked points (0..1)%</label>
        <input id="min_percentage_match_1" name='min_percentage_match_1' type="number" value="0.8" min="0" max="1" step="0.01">
        <label for="max_percentage_not_match_2">Max wrong points (0..1)%</label>
        <input id="max_percentage_not_match_2" name='max_percentage_not_match_2' type="number" value="" min="0" max="1" step="0.01">
        <button id="clearButton">Clear</button>
        <?php if (!empty($_GET["mask_id"])): ?>
            <button id="updateButton">Update Mask</button>
        <?php else: ?>
        <button id="uploadButton">Create Mask</button>
        <input type="file" id="imageInput" accept="image/*">
        <?php endif; ?>

        <span>
            The green line under the point indicates the actual error diameter of that point. If the user falls within this diameter, the point will be considered validated.
        </span>
    </div>
    <div class="drawing-board">
        <canvas id="drawing-board" width="800" height="600"></canvas>
    </div>
</section>
</body>
</html>