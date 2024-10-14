<?php

namespace Peach\Visualizers;

class MaskVisualizer implements VisualizerInterface
{

    public function visualize()
    {
        return require __DIR__ . "/../views/mask/index.php";
    }
}