<?php

namespace Peach\Http;

use Exception;
use Peach\Controllers\AttemptsController;
use Peach\Controllers\CaptchaController;
use Peach\Controllers\DatabaseController;
use Peach\Controllers\ImageController;
use Peach\Controllers\MaskController;
use Peach\Handlers\AttemptsHandler;
use Peach\Handlers\CaptchaHandler;
use Peach\Handlers\ImageHandler;
use Peach\Handlers\MainPageHandler;
use Peach\Handlers\MaskHandler;
use Peach\Repositories\RequestRepository;
use Peach\Visualizers\CaptchaVisualizer;
use Peach\Visualizers\MaskVisualizer;

class HttpHandler
{
    /**
     * @var RequestUriHandler[]
     */
    private array $request_handlers;

    public function __construct(private RequestRepository $request_repository)
    {
    }

    /**
     * @throws Exception
     */
    function execute()
    {
        foreach ($this->request_handlers as $request_handler) {
            if ($request_handler->getMethod() === $this->request_repository->getMethod()) {
                $handler = $request_handler->getHandler($this->request_repository->getPath());

                if ($handler === false) {
                throw new Exception("Requested URL {$this->request_repository->getMethod()} {$this->request_repository->getPath()} not allowed");
                }

                return $handler();
            }
        }

        throw new Exception("Method {$this->request_repository->getMethod()} not allowed in {$this->request_repository->getPath()}");
    }


    function register(RequestUriHandler $request_handlers): void
    {
        $this->request_handlers[] = $request_handlers;
    }


    function registerStandardHandlers(DatabaseController $database_controller): void
    {
        $main_page_visualizer = new CaptchaVisualizer();
//        $main_page_handler = new MainPageHandler($main_page_visualizer);
        $attempts_controller = new AttemptsController($database_controller);
        $attempts_handler = new AttemptsHandler($attempts_controller, $this->request_repository);
        $mask_visualizer = new MaskVisualizer();
        $mask_controller = new MaskController($database_controller);
        $image_controller = new ImageController($database_controller);
        $mask_handler = new MaskHandler($mask_visualizer, $mask_controller, $this->request_repository, $image_controller);
        $image_handler = new ImageHandler($image_controller);
        $captcha_controller = new CaptchaController($database_controller, $attempts_controller, $mask_controller);
        $captcha_visualizer = new CaptchaVisualizer();
        $captcha_handler = new CaptchaHandler($captcha_controller, $captcha_visualizer, $attempts_controller, $image_controller, $mask_controller, $this->request_repository);

        $post_handler = new RequestUriHandler("post");
        $put_handler = new RequestUriHandler("put");
        $get_handler = new RequestUriHandler("get");

        $get_handler->handle("/", $captcha_handler->handleViewGenerateCaptcha());
        $get_handler->handle("/preview", $attempts_handler->handleViewPreview());
        $get_handler->handle("/mask", $mask_handler->handleViewMask());
        $get_handler->handle("/image", $image_handler->handleViewImage());

        $get_handler->handle("/api/attempt", $attempts_handler->handleApiGetAttempt());
        $get_handler->handle("/api/mask", $mask_handler->handleApiGetMask());
        $post_handler->handle("/attempt", $attempts_handler->handleSubmitAttempt());
        $post_handler->handle("/api/mask", $mask_handler->handleApiCreateMask());


        $put_handler->handle("/api/mask", $mask_handler->handleApiEditMask());


        $get_handler->handle("/captcha", $captcha_handler->handleViewCaptcha());
        $get_handler->handle("/api/captcha/image", $captcha_handler->handleApiGetImage());
        $post_handler->handle("/api/validate/captcha", $captcha_handler->handleApiValidateCaptcha());

        $this->register($post_handler);
        $this->register($get_handler);
        $this->register($put_handler);
    }
}