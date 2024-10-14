const canvas = document.getElementById('canvas-el');
const context = canvas.getContext('2d');

// Используем getBoundingClientRect для точного определения позиции канваса
let canvasRect = canvas.getBoundingClientRect();

let isPainting = false;
let lineWidth = 5;
let startX;
let startY;
let paths = []; // Массив для хранения путей рисования
const tryAgainButton = document.getElementById('tryAgainLink');


const urlParams = new URLSearchParams(window.location.search);

function loadImage() {
    const imageUrl = `/api/captcha/image?captcha_uuid=${urlParams.get("captcha_uuid")}`;
    const image = new Image();
    image.src = imageUrl;

    image.onload = function () {
        canvas.width = image.width;
        canvas.height = image.height;
        context.drawImage(image, 0, 0);

        // Обновляем координаты канваса при изменении размера
        canvasRect = canvas.getBoundingClientRect();
    };
}

loadImage();

const validateCaptchaButton = document.getElementById('validate-captcha');
validateCaptchaButton.addEventListener('click', (e) => {
    fetch(`/api/validate/captcha?captcha_uuid=${urlParams.get("captcha_uuid")}`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(paths),
    })
        .then(r => r.json())
        .then(r => {
            tryAgainButton.style.display = 'block'; // Меняем стиль на 'block' или 'inline'
            console.log(tryAgainButton.firstChild)
            if (r.status === "success") {
                tryAgainButton.firstChild.style.backgroundColor = "green"
            } else {
                tryAgainButton.firstChild.style.backgroundColor = "red"
            }

            captchaMessage(r.message)
        })
});

const draw = (e) => {
    if (isPainting === false) return;

    context.lineWidth = lineWidth;
    context.lineCap = 'round';

    const x = e.clientX - canvasRect.left;
    const y = e.clientY - canvasRect.top;

    const lastPoint = paths[paths.length - 1];
    if (!lastPoint || (lastPoint.x !== x || lastPoint.y !== y)) {
        paths[paths.length - 1].push({x, y});
    }

    context.lineTo(x, y);
    context.stroke();
};

canvas.addEventListener('mousedown', (e) => {
    isPainting = true;
    startX = e.clientX - canvasRect.left;
    startY = e.clientY - canvasRect.top;

    context.beginPath();
    context.moveTo(startX, startY);

    paths.push([{x: startX, y: startY}]);
});

canvas.addEventListener('mouseup', () => {
    isPainting = false;
    context.stroke();
    context.beginPath();
});

canvas.addEventListener('mousemove', draw);

// Останавливаем рисование, когда мышь покидает канвас
canvas.addEventListener('mouseleave', () => {
    if (isPainting) {
        isPainting = false;
        context.stroke();
        context.beginPath(); // Заканчиваем текущий путь
    }
});

// Обновляем положение канваса при изменении размеров окна
window.addEventListener('resize', () => {
    canvasRect = canvas.getBoundingClientRect();
});

function captchaMessage(message) {
    // Устанавливаем фон (если нужно)
    context.fillStyle = '#f0f0f0';
    context.fillRect(0, 0, canvas.width, canvas.height);

// Рисуем текст
    context.font = '24px sans-serif';
    context.fillStyle = 'black'; // Цвет текста
    context.fillText(message, 20, 100);
}

tryAgainButton.addEventListener('click', function () {
    window.location.replace("/")
})