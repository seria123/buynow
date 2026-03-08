<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  
  @if(auth()->check())
    <meta name="user-id" content="{{ auth()->id() }}">
  @endif

  @vite('resources/js/app.js')  <!-- ✅ ONLY Vite -->

  @inertiaHead
  @routes <!-- Optional, for Ziggy -->
</head>
<body>
  @inertia
</body>
</html>