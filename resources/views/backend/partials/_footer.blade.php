@php
    $year = 2020;
    $curYear = date('Y');
    if($curYear > $year){
        $year = "$year - $curYear";
    }
@endphp
<footer class="main-footer">
    <strong>Copyright &copy; {{$year}}.</strong> All rights reserved.
</footer>
