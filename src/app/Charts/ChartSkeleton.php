<?php

namespace Backpack\CRUD\app\Charts;

use ConsoleTVs\Charts\Classes\Chartjs\Chart as ChartJs;

class ChartSkeleton extends ChartJs
{
	public function __construct()
	{
		// construct the Chart using standard LaravelCharts
        parent::__construct();

        // call the setup method (where we instruct users to configure their charts)
		$this->setup();
	}
}
