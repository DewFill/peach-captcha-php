<?php

namespace Peach\Visualizers;
class CaptchaVisualizer implements VisualizerInterface
{

    public function visualize()
    {
        return require __DIR__ . "/../views/captcha/index.html";
    }
}