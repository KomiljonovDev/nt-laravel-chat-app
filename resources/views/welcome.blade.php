<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laravel + Vue Chat</title>
    @vite(['resources/js/app.js'])
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }

        #app {
            height: 100vh;
            width: 100vw;
        }
    </style>
</head>
<body>
<div id="app" class="min-h-screen">
    @if (Route::has('login'))
        @auth
            <app :is-auth="{{ json_encode(auth()->check()) }}"
                 :user="{{ auth()->check() ? auth()->user() : 'null' }}">
            </app>
            <!-- Logout form can be accessed via dropdown menu -->
        @else
            <div class="flex items-center justify-center min-h-screen bg-gray-100">
                <div class="bg-white p-8 rounded-lg shadow-md">
                    <h2 class="text-2xl font-bold mb-6 text-center">Chat</h2>
                    <div class="flex flex-col space-y-4">
                        <a href="{{ route('login') }}"
                           class="px-4 py-2 bg-blue-500 text-white text-center rounded hover:bg-blue-600 transition-colors">
                            Log in
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                               class="px-4 py-2 border border-gray-300 text-gray-700 text-center rounded hover:bg-gray-50 transition-colors">
                                Register
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endauth
    @endif
</div>
</body>
</html>
