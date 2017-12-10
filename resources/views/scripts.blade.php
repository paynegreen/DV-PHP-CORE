{{-- @if(Request::secure()) --}}
<!-- Placed js at the end of the document so the pages load faster -->
<script src="{{ Request::secure(Request::path().'/js/jquery-1.10.2.min.js' }}"></script>
<script src="{{ Request::secure(Request::path().'/js/jquery-migrate.js' }}"></script>
<script src="{{ Request::secure(Request::path().'/js/bootstrap.min.js' }}"></script>
<!--notification pan-->
<script src="{{ Request::secure(Request::path().'/js/modernizr.min.js' }}"></script>

<!-- datatable -->
<script src="https://cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/select/1.2.2/js/dataTables.select.min.js"></script>

<!--Nice Scroll-->
<script src="{{ Request::secure(Request::path().'/js/jquery.nicescroll.js' }}" type="text/javascript"></script>

<!--right slidebar-->
<script src="{{ Request::secure(Request::path().'/js/slidebars.min.js' }}"></script>

{{--  <!--switchery-->
<script src="{{ Request::secure(Request::path().'/js/switchery/switchery.min.js' }}"></script>
<script src="{{ Request::secure(Request::path().'/js/switchery/switchery-init.js' }}"></script>  --}}

{{--  <!--Sparkline Chart-->
<script src="{{ Request::secure(Request::path().'/js/sparkline/jquery.sparkline.js' }}"></script>
<script src="{{ Request::secure(Request::path().'/js/sparkline/sparkline-init.js' }}"></script>  --}}

<!--Form Validation-->
<script src="{{ Request::secure(Request::path().'/js/bootstrap-validator.min.js' }}" type="text/javascript"></script>

<!--Form Wizard-->
<script src="{{ Request::secure(Request::path().'/js/jquery.steps.min.js' }}" type="text/javascript"></script>
<script src="{{ Request::secure(Request::path().'/js/jquery.validate.min.js' }}" type="text/javascript"></script>

<!--wizard initialization-->
<script src="{{ Request::secure(Request::path().'/js/wizard-init.js' }}" type="text/javascript"></script>


<!--common scripts for all pages-->
<script src="{{ Request::secure(Request::path().'/js/scripts.js' }}"></script>
<!-- Ace Editor -->
@if(\Request::path() != 'console')
<script src="{{ Request::secure(Request::path().'/js/ace/ace.js' }}" type="text/javascript" ></script>
<script src="{{ Request::secure(Request::path().'/js/ace/theme-github.js' }}" type="text/javascript" ></script>

<script src="{{ Request::secure(Request::path().'/js/ace/mode-php.js' }}" type="text/javascript" ></script>
<script src="{{ Request::secure(Request::path().'/js/ace/jquery-ace.min.js' }}" type="text/javascript" ></script>
@endif

{{-- @else
<!-- Placed js at the end of the document so the pages load faster -->
<script src="{{ asset('/js/jquery-1.10.2.min.js') }}"></script>
<script src="{{ asset('/js/jquery-migrate.js') }}"></script>
<script src="{{ asset('/js/bootstrap.min.js') }}"></script>
<!--notification pan-->
<script src="{{ asset('/js/modernizr.min.js') }}"></script>

<!-- datatable -->
<script src="https://cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/select/1.2.2/js/dataTables.select.min.js"></script>

<!--Nice Scroll-->
<script src="{{ asset('/js/jquery.nicescroll.js') }}" type="text/javascript"></script>

<!--right slidebar-->
<script src="{{ asset('/js/slidebars.min.js') }}"></script>

{{--  <!--switchery-->
<script src="{{ asset('/js/switchery/switchery.min.js') }}"></script>
<script src="{{ asset('/js/switchery/switchery-init.js') }}"></script>  --}}

{{--  <!--Sparkline Chart-->
<script src="{{ asset('/js/sparkline/jquery.sparkline.js') }}"></script>
<script src="{{ asset('/js/sparkline/sparkline-init.js') }}"></script>  --}}

<!--Form Validation-->
<script src="{{ asset('/js/bootstrap-validator.min.js') }}" type="text/javascript"></script>

<!--Form Wizard-->
<script src="{{ asset('/js/jquery.steps.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('/js/jquery.validate.min.js') }}" type="text/javascript"></script>

<!--wizard initialization-->
<script src="{{ asset('/js/wizard-init.js') }}" type="text/javascript"></script>


<!--common scripts for all pages-->
<script src="{{ asset('/js/scripts.js') }}"></script>
<!-- Ace Editor -->
@if(\Request::path() != 'console')
<script src="{{ asset('/js/ace/ace.js') }}" type="text/javascript" ></script>
<script src="{{ asset('/js/ace/theme-github.js') }}" type="text/javascript" ></script>

<script src="{{ asset('/js/ace/mode-php.js') }}" type="text/javascript" ></script>
<script src="{{ asset('/js/ace/jquery-ace.min.js') }}" type="text/javascript" ></script>
@endif

@endif --}}
