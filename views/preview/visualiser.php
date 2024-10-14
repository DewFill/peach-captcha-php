<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Point Visualizer</title>
    <style>
        canvas {
            border: 1px solid black;
        }
    </style>
</head>
<body>
<canvas id="canvas" width="800" height="600"></canvas>

<script>
    // Your updated list of points with paths
    let paths
    const queryString = window.location.search;
    const searchParams = new URLSearchParams(queryString);

    // Вызов функции отрисовки
    console.log(searchParams.get('attempt_id'))
    fetch(`/api/attempt?attempt_id=${searchParams.get('attempt_id')}`)
        .then(r => r.json())
        .then(data => {
            paths = JSON.parse(data.points);
            // normalizePoints(paths)
            drawPoints()
        })

    // const normalizePoints = (paths) => {
    //     let minX = Infinity;
    //     let minY = Infinity
    //     console.log(paths)
    //     for (const path of paths) {
    //             if (path.x < minX) minX = path.x;
    //             if (path.y < minY) minY = path.y;
    //     }
    //
    //     for (const path of paths) {
    //             path.x -= minX;
    //             path.y -= minY;
    //     }
    //
    //     console.log("minX:", minX, "minY:", minY)
    // }

    // normalizePoints(paths)
    // Get the canvas and context
    const canvas = document.getElementById('canvas');
    const ctx = canvas.getContext('2d');

    // Function to draw points and lines
    function drawPoints() {
        ctx.fillStyle = 'blue'; // Set the point color
        ctx.strokeStyle = 'red'; // Set the line color
        ctx.lineWidth = 2; // Set the line width

        // Loop through each path in the points array
        paths.forEach(path => {
            // Loop through the points in the current path
            path.forEach((point, index) => {
                // Draw a line to the next point
                if (index < path.length - 1) { // Ensure we don't go out of bounds
                    ctx.beginPath();
                    ctx.moveTo(point.x, point.y); // Move to the current point
                    ctx.lineTo(path[index + 1].x, path[index + 1].y); // Draw a line to the next point
                    ctx.stroke(); // Actually draw the line
                    ctx.closePath();
                }

                // Draw the point
                ctx.beginPath();
                ctx.arc(point.x, point.y, 3, 0, Math.PI * 2); // Draw a circle at the point
                ctx.fill(); // Fill the circle
                ctx.closePath();
            });
        });
    }
</script>
</body>
</html>