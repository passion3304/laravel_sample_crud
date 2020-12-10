@if (backpack_auth()->check())
    <!-- Left side column. contains the sidebar -->
    <div class="{{ config('backpack.base.sidebar_class') }}">
      <!-- sidebar: style can be found in sidebar.less -->
      <nav class="sidebar-nav overflow-hidden">
        <!-- sidebar menu: : style can be found in sidebar.less -->
        <ul class="nav">
          <!-- <li class="nav-title">{{ trans('backpack::base.administration') }}</li> -->
          <!-- ================================================ -->
          <!-- ==== Recommended place for admin menu items ==== -->
          <!-- ================================================ -->

          @include(backpack_view('inc.sidebar_content'))

          <!-- ======================================= -->
          <!-- <li class="divider"></li> -->
          <!-- <li class="nav-title">Entries</li> -->
        </ul>
      </nav>
      <!-- /.sidebar -->
    </div>
@endif

@push('before_scripts')
  <script type="text/javascript">
    /* Recover sidebar state */
    var body = document.getElementsByTagName('body')[0];
    var sidebarDefault = body.classList.contains("sidebar-lg-show");
    if (Boolean(sessionStorage.getItem('sidebar-collapsed')) && sidebarDefault) {
      body.classList.remove("sidebar-lg-show");
    }
    if (!Boolean(sessionStorage.getItem('sidebar-collapsed')) && !sidebarDefault) {
      body.classList.add("sidebar-lg-show");
    }
  </script>
@endpush

@push('after_scripts')
  <script>
      /* Store sidebar state */
      $(function() {
        $('.sidebar-toggler').click(function() {
          sessionStorage.setItem('sidebar-collapsed', body.classList.contains("sidebar-lg-show") ? '1' : '');
        });
      });
      // Set active state on menu element
      var full_url = "{{ Request::fullUrl() }}";
      var $navLinks = $(".sidebar-nav li a, .app-header li a");

      // First look for an exact match including the search string
      var $curentPageLink = $navLinks.filter(
          function() { return $(this).attr('href') === full_url; }
      );

      // If not found, look for the link that starts with the url
      if(!$curentPageLink.length > 0){
          $curentPageLink = $navLinks.filter( function() {
            if ($(this).attr('href').startsWith(full_url)) {
              return true;
            }

            if (full_url.startsWith($(this).attr('href'))) {
              return true;
            }

            return false;
          });
      }

      // for the found links that can be considered current, make sure 
      // - the parent item is open
      $curentPageLink.parents('li').addClass('open');
      // - the actual element is active
      $curentPageLink.each(function() {
        $(this).addClass('active');
      });
  </script>
@endpush
