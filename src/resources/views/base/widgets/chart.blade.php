@php
  // -----------------------
  // Backpack ChartJS Widget
  // -----------------------
  // Uses:
  // - https://github.com/ConsoleTVs/Charts
  // - https://github.com/chartjs/Chart.js

  // $chart = new \App\Charts\Chart;

  // if (isset($widget['content']['configuration']) && is_callable($widget['content']['configuration'])) {
  //   $chart = $widget['content']['configuration']($chart);
  // } else {
  //   abort(500, 'Chart widget not configured.');
  // }

  if (isset($widget['content']['chart'])) {
    $chart = new $widget['content']['chart'];
  } else {
    abort(500, 'Chart widget not configured.');
  }

@endphp

<div class="{{ $widget['wrapperClass'] ?? 'col-sm-6 col-md-4' }}">
  <div class="{{ $widget['class'] ?? 'card mb-2' }}">
    @if (isset($widget['content']['header']))
    <div class="card-header">{!! $widget['content']['header'] !!}</div>
    @endif
    <div class="card-body">

      {!! $widget['content']['body'] ?? '' !!}

      <div class="card-wrapper">
        {!! $chart->container() !!}
      </div>

    </div>
  </div>
</div>

@push('after_scripts')
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.min.js" charset="utf-8"></script>
  {!! $chart->script() !!}

@endpush