@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">

        <div class="col-md-8">
            {{-- <h1>hello world</h1> --}}
            <chat :user="{{ Auth::user() }}"/>
1
        </div>
    </div>

</div>
@endsection
@push('scripts')
<script>
    console.log(@json(auth()->user()));
</script>
@endpush
{{-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title></title>
</head>
<body>
<h1>chat component
             < user="{{ Auth::user() }}">

</h1>
</body> --}}
</html>
