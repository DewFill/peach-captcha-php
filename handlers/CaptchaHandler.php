<?php

namespace Peach\Handlers;

use Exception;
use JetBrains\PhpStorm\ArrayShape;
use Peach\Controllers\AttemptsController;
use Peach\Controllers\CaptchaController;
use Peach\Controllers\ImageController;
use Peach\Controllers\MaskController;
use Peach\Repositories\RequestRepository;
use Peach\Visualizers\CaptchaVisualizer;

class CaptchaHandler
{
    const POINT_TOLERANCE = 10;

    public function __construct(private CaptchaController $captchaController, private CaptchaVisualizer $visualizer, private AttemptsController $attemptController, private ImageController $imageController, private MaskController $maskController, private RequestRepository $requestRepository)
    {
    }

    function handleViewGenerateCaptcha()
    {
        return function () {
            $attemptRepository = $this->captchaController->generateCaptcha();
            header('Location: /captcha?captcha_uuid=' . $attemptRepository->getUuid());
        };
    }


    public function handleViewCaptcha(): callable
    {
        return function () {
            if (empty($_GET["captcha_uuid"])) throw new Exception("Captcha ID not provided");

            return $this->visualizer->visualize();
        };
    }

    public function handleApiGetImage(): callable
    {
        return function () {
            if (empty($_GET["captcha_uuid"])) throw new Exception("Captcha ID not provided");
            $attemptRepository = $this->attemptController->getAttemptByUuid($_GET["captcha_uuid"]);

            if ($attemptRepository === false) throw new Exception("Attempt not found");

            $maskRepository = $this->maskController->getMask($attemptRepository->getMaskId());

            if ($maskRepository === false) throw new Exception("Mask not found");

            $imageRepository = $this->imageController->getImage($maskRepository->getImageId());

            header("Content-Type: {$imageRepository->getMineType()}");
            echo $imageRepository->getImage();
        };
    }

    public function handleApiValidateCaptcha()
    {
        return function () {
            if (empty($_GET["captcha_uuid"]) or !is_string($_GET["captcha_uuid"])) throw new Exception("Captcha ID not provided");

            if (!json_validate($this->requestRepository->getBody())) throw new Exception("Invalid request body");
            $points = $this->requestRepository->getBody();

            if (!$this->captchaController->isCaptchaAvailableToSolve($_GET["captcha_uuid"])) {
                echo json_encode([
                    "status" => "failed",
                    "message" => "Captcha is not available to solve"
                ]);
                return;
            }

            $isSaved = $this->captchaController->submitCaptcha($_GET["captcha_uuid"], $points);
            if ($isSaved === false) throw new Exception("Failed to save captcha");

            $attemptRepository = $this->attemptController->getAttemptByUuid($_GET["captcha_uuid"]);
            if ($attemptRepository === false) throw new Exception("Attempt not found");

            $maskRepository = $this->maskController->getMask($attemptRepository->getMaskId());

            if ($maskRepository === false) throw new Exception("Mask not found");

            $maskPoints = json_decode($maskRepository->getPoints(), true);
            $userPoints = json_decode($this->requestRepository->getBody(), true);

            $validatePercentages = $this->calculatePathSimilarity($maskPoints, $userPoints);

//            var_dump($maskRepository->getMinPercentageMatch1());
//            var_dump($maskRepository->getMaxPercentageNotMatch2());
//            var_dump($validatePercentages);
//            var_dump($maskRepository->getMinPercentageMatch1() < $validatePercentages["percentageMatched1"]);
//            var_dump($maskRepository->getMaxPercentageNotMatch2() > $validatePercentages["percentageNotMatched2"]);
            $isSolved = ($maskRepository->getMinPercentageMatch1() < $validatePercentages["percentageMatched1"] and $maskRepository->getMaxPercentageNotMatch2() > $validatePercentages["percentageNotMatched2"]);
            if ($isSolved) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Captcha validation succeeded"
                ]);
            } else {
                echo json_encode([
                    "status" => "failed",
                    "message" => "Captcha validation failed"
                ]);
            }

            $isSaved = $this->captchaController->setCaptchaSolved($_GET["captcha_uuid"], $isSolved);
            if ($isSaved === false) throw new Exception("Failed to save captcha");
        };
    }

    /**
     * Вычисляет процент совпадения точек между маскированными путями и пользовательскими путями.
     *
     * Функция возвращает массив из двух чисел:
     * 1. **percentageMatched1**: процент уникальных точек из маски, которые были затронуты пользователем.
     *    - Если одна точка маски была затронута несколько раз, то учитывается только одно совпадение.
     *
     * 2. **percentageNotMatched2**: процент точек пользователя, которые не совпали с точками маски.
     *    - Если несколько точек пользователя имеют одинаковые координаты (например, если пользователь нарисовал несколько линий в одним и тем же путем),
     *      то такие точки не игнорируются и увеличивают процент непопадания.
     *
     * @param array $maskPaths Массив маскированных путей, каждый из которых содержит массив точек.
     * @param array $userPaths Массив пользовательских путей, каждый из которых также содержит массив точек.
     * @return float[]|int[] Возвращает массив с двумя элементами:
     *     - `percentageMatched1`: Процент совпавших точек из маски (float|int).
     *     - `percentageNotMatched2`: Процент точек пользователя, не попавших в маску (float|int).
     */
    #[ArrayShape(['percentageMatched1' => "float|int", 'percentageNotMatched2' => "float|int"])]
    private function calculatePathSimilarity(array $maskPaths, array $userPaths): array
    {
        $matchedPoints1 = []; // Массив для хранения уникальных точек первого массива
        $matchedPoints2 = []; // Массив для хранения уникальных точек второго массива

        // Проверка точек каждого маскированного пути на совпадение с пользователем
        foreach ($maskPaths as $maskPath) {
            foreach ($maskPath as $point1) {
                foreach ($userPaths as $userPath) {
                    foreach ($userPath as $point2) {
                        if ($this->isWithinTolerance($point1, $point2)) {
                            // Если точка из маскированного пути найдена, добавляем в массив уникальных точек
                            $matchedPoints1[] = $point1; // Сохраняем точку
                            break 2; // Точка из маскированного пути найдена, переходим к следующему маскированному пути
                        }
                    }
                }
            }
        }

        // Проверка точек каждого пользовательского пути на совпадение с маскированными
        foreach ($userPaths as $userPath) {
            foreach ($userPath as $point2) {
                foreach ($maskPaths as $maskPath) {
                    foreach ($maskPath as $point1) {
                        if ($this->isWithinTolerance($point2, $point1)) {
                            // Если точка из пользовательского пути найдена, добавляем в массив уникальных точек
                            $matchedPoints2[] = $point2; // Сохраняем точку
                            break 2; // Точка из пользовательского пути найдена, переходим к следующему пользовательскому пути
                        }
                    }
                }
            }
        }

        // Получение общего количества точек
        $totalMaskPoints = $this->getTotalPoints($maskPaths);
        $totalUserPoints = $this->getTotalPoints($userPaths);

        // Проверка на ноль, чтобы избежать деления на ноль
        $percentageMatched1 = $totalMaskPoints > 0 ? (count($matchedPoints1) / $totalMaskPoints) : 0;

        // Вычисляем количество непопавших точек из userPaths
        $notMatchedCount2 = $totalUserPoints - count($matchedPoints2);
        $percentageNotMatched2 = $totalUserPoints > 0 ? ($notMatchedCount2 / $totalUserPoints) : 0;

        return [
            'percentageMatched1' => $percentageMatched1,
            'percentageNotMatched2' => $percentageNotMatched2
        ];
    }

    private function getTotalPoints(array $paths) {
        $total = 0;
        foreach ($paths as $path) {
            if (is_array($path)) { // Убедитесь, что это массив
                $total += count($path); // Считаем количество точек в каждом пути
            }
        }
        return $total; // Возвращаем общее количество точек
    }


    private function isWithinTolerance($point1, $point2) {
        // Вычисляем Евклидово расстояние между двумя точками
        $distance = sqrt(pow($point1['x'] - $point2['x'], 2) + pow($point1['y'] - $point2['y'], 2));
        return $distance <= self::POINT_TOLERANCE; // Проверяем, меньше ли расстояние допустимой погрешности
    }
}