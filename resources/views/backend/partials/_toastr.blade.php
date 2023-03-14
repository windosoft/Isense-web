<script>
    @if(Session::has('error'))
    toastr.error("{{Session::get('error')}}");
    @endif

    @if(Session::has('success'))
    toastr.success("{{Session::get('success')}}");
    @endif

    @if(Session::has('warning'))
    toastr.warning("{{Session::get('warning')}}");
    @endif
</script>
