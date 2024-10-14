<?php

namespace Peach\Visualizers;

use Peach\Repositories\AttemptRepository;

class PreviewVisualizer implements VisualizerInterface
{

    public function __construct(private AttemptRepository $attemptRepository)
    {
    }

    public function visualize()
    {
        $attemptRepository = $this->attemptRepository;
        return require __DIR__ . "/../views/preview/visualiser.php";
    }
}