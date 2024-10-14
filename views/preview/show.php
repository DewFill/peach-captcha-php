<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Line Visualizer</title>
    <style>
        canvas {
            border: 1px solid #000;
            display: block;
            margin: 0 auto;
        }
    </style>
</head>
<body>
<canvas id="canvas">
</canvas>
<script>
    const canvas = document.getElementById('canvas');
    const ctx = canvas.getContext('2d');

    // Установка размеров canvas
    canvas.width = 800;
    canvas.height = 600;

    // Функция для отрисовки линий
    function drawLines(lines) {
        ctx.clearRect(0, 0, canvas.width, canvas.height); // Очистка canvas перед новой отрисовкой
        ctx.lineCap = 'round'; // Устанавливаем округлые концовки линий

        lines.forEach(line => {
            ctx.beginPath();
            ctx.moveTo(line.x, line.y);
            ctx.lineTo(line.x +2, line.y+2);
            ctx.strokeStyle = '#000000'; // Цвет линий
            ctx.lineWidth = 2; // Ширина линий
            ctx.stroke();
        });
    }

    const queryString = window.location.search;
    const searchParams = new URLSearchParams(queryString);

    // Вызов функции отрисовки
    console.log(searchParams.get('attempt_id'))
    fetch(`/api/attempt?attempt_id=${searchParams.get('attempt_id')}`)
        .then(r => r.json())
        .then(data => {
            drawLines(JSON.parse(data.points));
        })

</script>
</body>
</html>
