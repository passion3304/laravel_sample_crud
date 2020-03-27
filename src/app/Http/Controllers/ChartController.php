<?php

namespace Backpack\CRUD\app\Http\Controllers;

class ChartController
{
    public $chart;

    protected $library;

    public function __construct()
    {
        $this->setup();
        $this->setLibraryFilePathFromDatasetType($this->chart->dataset);
    }

    /**
     * Respond to AJAX requests with the datasets in the chart.
     *
     * @return JSON All dataset information the chart needs, if called through AJAX.
     */
    public function response()
    {
        // call the data() method, if present
        if (method_exists($this, 'data')) {
            $this->data();
        }

        if ($this->chart) {
            return $this->chart->api();
        }

        return $this->api();
    }

    /**
     * Returns the path to the charting JavaScript library.
     *
     * @return string Full URL to the minified javascript file.
     */
    public function getLibraryFilePath()
    {
        return $this->library;
    }

    /**
     * Set the path where the chart widget will find the JS file (or files)
     * needeed to set up the charting.
     *
     * @param string|array $path Full URL to the JS file of the charting library. Or array.
     */
    protected function setLibraryFilePath($path)
    {
        $this->library = $path;
    }

    /**
     * Set the path to the JS file needed, depending on the chart dataset.
     * Because the dataset always includes the name of the charting library,
     * we can use that to determine which JS file we should be loading from the CDN.
     *
     * @param string $dataset Class name of the dataset of the current chart.
     */
    protected function setLibraryFilePathFromDatasetType($dataset)
    {
        // depending on which Class was used,
        // load the appropriate JS Library from CDN
        switch ($dataset) {
            case 'ConsoleTVs\Charts\Classes\Chartjs\Dataset':
                $this->library = 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.min.js';
                break;

            case 'ConsoleTVs\Charts\Classes\Highcharts\Dataset':
                $this->library = 'https://cdnjs.cloudflare.com/ajax/libs/highcharts/6.0.6/highcharts.js';
                break;

            case 'ConsoleTVs\Charts\Classes\Fusioncharts\Dataset':
                $this->library = 'https://cdn.jsdelivr.net/npm/fusioncharts@3.12.2/fusioncharts.js';
                break;

            case 'ConsoleTVs\Charts\Classes\Echarts\Dataset':
                $this->library = 'https://cdnjs.cloudflare.com/ajax/libs/echarts/4.0.2/echarts-en.min.js';
                break;

            case 'ConsoleTVs\Charts\Classes\Frappe\Dataset':
                $this->library = 'https://cdn.jsdelivr.net/npm/frappe-charts@1.1.0/dist/frappe-charts.min.iife.js';
                break;

            case 'ConsoleTVs\Charts\Classes\C3\Dataset':
                $this->library = [
                    'https://cdnjs.cloudflare.com/ajax/libs/d3/5.7.0/d3.min.js',
                    'https://cdnjs.cloudflare.com/ajax/libs/c3/0.6.7/c3.min.js',
                ];
                break;

            default:
                $this->library = 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.min.js';
                break;
        }
    }
}
