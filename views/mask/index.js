const canvas = document.getElementById('drawing-board');
const context = canvas.getContext('2d');

const canvasOffsetX = canvas.offsetLeft;
const canvasOffsetY = canvas.offsetTop;

let isPainting = false;
const pointColor = "blue"
let lineWidth = 10
let pointRadius = lineWidth / 7; // Радиус круга для точек
const lineColor = 'rgb(144, 238, 144)'
let startX;
let startY;
let paths = []; // Массив путей для хранения точек рисования

const urlParams = new URLSearchParams(window.location.search);

let min_percentage_match_1 = document.getElementById('min_percentage_match_1');
let max_percentage_not_match_2 = document.getElementById('max_percentage_not_match_2');
let point_tolerance = document.getElementById('point_tolerance');

function loadImage(mask_id, with_points_from_server = true) {
    // Создаем объект URLSearchParams из строки запроса
    fetch(`/api/mask?mask_id=${urlParams.get("mask_id")}`, {
        method: "GET",
    })
        .then(r => r.json())
        .then(data => {
            // load image
            console.log(data)
            const imageUrl = `/image?image_id=${data.image_id}`; // URL твоего изображения
            // Создаем новый объект изображения
            const image = new Image();
            image.src = imageUrl;

            // Когда изображение загружено
            image.onload = function () {
                // Устанавливаем размеры канваса под изображение
                canvas.width = image.width;
                canvas.height = image.height;

                // Рисуем изображение на канвасе
                context.drawImage(image, 0, 0);
                // Устанавливаем красный фон изначально
                setCanvasBackground();

                paths = JSON.parse(data.points);
                min_percentage_match_1.value = data.min_percentage_match_1
                max_percentage_not_match_2.value = data.max_percentage_not_match_2
                point_tolerance.value = data.point_tolerance


                lineWidth = data.point_tolerance
                pointRadius = data.point_tolerance / 7

                drawPoints()
            };
        })
}

if (urlParams.get("mask_id") !== null) {
    loadImage(urlParams.get("mask_id"))
}

// Function to draw points and lines
function drawPoints() {
    context.fillStyle = pointColor; // Set the point color
    context.strokeStyle = lineColor; // Set the line color
    context.lineWidth = lineWidth; // Set the line width

    // Loop through each path in the points array
    paths.forEach(path => {
        // Loop through the points in the current path
        path.forEach((point, index) => {
            // Draw a line to the next point
            if (index < path.length - 1) { // Ensure we don't go out of bounds
                context.beginPath();
                context.moveTo(point.x, point.y); // Move to the current point
                context.lineTo(path[index + 1].x, path[index + 1].y); // Draw a line to the next point
                context.stroke(); // Actually draw the line
                context.closePath();
            }

            // Draw the point
            context.beginPath();
            context.arc(point.x, point.y, pointRadius, 0, Math.PI * 2); // Draw a circle at the point
            context.fill(); // Fill the circle
            context.closePath();
        });
    });
}

// Устанавливаем красный фон канваса
const setCanvasBackground = () => {
    context.fillStyle = 'rgba(255, 0, 0, 0.2)';
    context.fillRect(0, 0, canvas.width, canvas.height); // Заполняем весь канвас
};


const draw = (e) => {
    if (!isPainting) return; // Прекращаем, если не рисуем

    const x = e.clientX - canvasOffsetX;
    const y = e.clientY - canvasOffsetY;

    const lastPath = paths[paths.length - 1];

    // Если линия не пересекает уже нарисованную и координаты не совпадают, добавляем точку в массив
    if (lastPath.length < 2) {
        if (!lastPath) {
            // Создаем новый путь
            paths.push([{x, y}]);
        } else {
            lastPath.push({x, y}); // Добавляем точку в последний путь
            drawPoint(x, y); // Рисуем круг в новой точке
        }
        return;
    }

    if (!lastPath || (lastPath[lastPath.length - 2].x !== x || lastPath[lastPath.length - 2].y !== y)) {
        // Проверяем, совпадают ли координаты с предыдущей точкой
        if (lastPath) {
            const lastPoint = lastPath[lastPath.length - 1];
            if (lastPoint.x === x && lastPoint.y === y) {
                return; // Не рисуем точку, если координаты совпадают
            }

            // Рисуем линию между последней и текущей точкой
            drawLine(lastPoint, {x, y});
        }

        // Добавляем новую точку в последний путь
        if (!lastPath) {
            // Создаем новый путь
            paths.push([{x, y}]);
        } else {
            lastPath.push({x, y}); // Добавляем точку в последний путь
            drawPoint(x, y); // Рисуем круг в новой точке
        }
    }
};

// Функция для рисования круга
const drawPoint = (x, y) => {
    context.beginPath();
    context.arc(x, y, pointRadius, 0, Math.PI * 2); // Рисуем круг
    context.fillStyle = pointColor;
    context.fill();
};

// Функция для рисования линии между двумя точками
const drawLine = (start, end) => {
    context.beginPath();
    context.moveTo(start.x, start.y); // Начальная точка
    context.lineTo(end.x, end.y); // Конечная точка
    context.strokeStyle = lineColor; // Цвет линии изменен на зеленый
    context.lineWidth = lineWidth; // Толщина линии
    context.stroke(); // Рисуем линию
};


canvas.addEventListener('mousedown', (e) => {
    isPainting = true;
    startX = e.clientX - canvasOffsetX;
    startY = e.clientY - canvasOffsetY;

    // Начинаем новый путь для нового рисования
    context.beginPath();
    context.moveTo(startX, startY);

    // Создаем новый путь
    paths.push([{x: startX, y: startY}]);
    drawPoint(startX, startY); // Рисуем круг в начальной точке
});

canvas.addEventListener('mouseup', e => {
    isPainting = false; // Завершаем рисование
    context.beginPath(); // Начинаем новый путь
});

canvas.addEventListener('mousemove', draw);


const updateButton = document.getElementById('updateButton');
const uploadButton = document.getElementById('uploadButton');
const clearButton = document.getElementById('clearButton');
const imageInput = document.getElementById('imageInput');

clearButton.addEventListener('click', (e) => {
    paths = []
    loadImage(false)
});


if (updateButton !== null) {
    console.log(point_tolerance.value)
    updateButton.addEventListener('click', (e) => {
        fetch("/api/mask", {
            method: "PUT",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                "mask_id": urlParams.get("mask_id"),
                "points": JSON.stringify(paths),
                "min_percentage_match_1": parseFloat(min_percentage_match_1.value),
                "max_percentage_not_match_2": parseFloat(max_percentage_not_match_2.value),
                "point_tolerance": parseInt(point_tolerance.value)
            }),
        })
            .then(r => r.json())
            .then(function (array) {
                console.log(array);
                window.location.reload()
            });
    });
}

// Клик по кнопке открывает диалог выбора файла
if (uploadButton !== null) {
    uploadButton.addEventListener('click', () => {
        imageInput.click();
    });
}


if (imageInput !== null) {
    // Когда файл выбран, сразу загружаем его
    imageInput.addEventListener('change', () => {
        const file = imageInput.files[0];

        if (!file) {
            alert('Please select an image first.');
            return;
        }

        const formData = new FormData();
        formData.append('image', file);

        // Создаем новую маску и загружаем изображение
        fetch('/api/mask', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                console.log('Success:', data);
                window.location.replace(`/mask?mask_id=${data.mask_id}`);
            })
            .catch((error) => {
                console.error('Error:', error);
            });
    });
}