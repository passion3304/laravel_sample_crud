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
            $response = $this->chart->api();
        } else {
            $response = $this->api();
        }

        return response($response)->withHeaders([
            'Content-Type' => 'application/json',
        ]);
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
     * @param  string|array  $path  Full URL to the JS file of the charting library. Or array.
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
     * @param  string  $dataset  Class name of the dataset of the current chart.
     */
    protected function setLibraryFilePathFromDatasetType($dataset)
    {
        // depending on which Class was used,
        // load the appropriate JS Library from CDN
        switch ($dataset) {
            case 'ConsoleTVs\Charts\Classes\Chartjs\Dataset':
                $this->library = 'https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js';
                break;

            case 'ConsoleTVs\Charts\Classes\Highcharts\Dataset':
                $this->library = 'https://cdn.jsdelivr.net/npm/highcharts@6.2.0/highcharts.min.js';
                break;

            case 'ConsoleTVs\Charts\Classes\Fusioncharts\Dataset':
                $this->library = 'https://cdn.jsdelivr.net/npm/fusioncharts@3.18.0/fusioncharts.min.js';
                break;

            case 'ConsoleTVs\Charts\Classes\Echarts\Dataset':
                $this->library = 'https://cdn.jsdelivr.net/npm/echarts@4.9.0/dist/echarts-en.min.js';
                break;

            case 'ConsoleTVs\Charts\Classes\Frappe\Dataset':
                $this->library = 'https://cdn.jsdelivr.net/npm/frappe-charts@1.6.2/dist/frappe-charts.min.umd.min.js';
                break;

            case 'ConsoleTVs\Charts\Classes\C3\Dataset':
                $this->library = [
                    'https://cdn.jsdelivr.net/npm/d3@5.16.0/dist/d3.min.js',
                    'https://cdn.jsdelivr.net/npm/c3@0.7.20/c3.min.js',
                ];
                break;

            default:
                $this->library = 'https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js';
                break;
        }
    }
}
