<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">

        <meta name="application-name" content="{{ config('app.name') }}">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name') }}</title>

        <style>
            [x-cloak] {
                display: none !important;
            }
            html {
                scroll-behavior: smooth;
            }
        </style>

        <!-- <script src="https://cdn.tailwindcss.com"></script> -->
    </head>

    <body class="antialiased">
        {{ $slot }}
    </body>
</html>
