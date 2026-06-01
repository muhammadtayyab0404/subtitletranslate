<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Welcome</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-indigo-500 to-purple-600 min-h-screen flex items-center justify-center">

  <div class="bg-white p-10 rounded-2xl shadow-xl text-center w-96">
    <h1 class="text-3xl font-bold text-gray-800 mb-4">Welcome </h1>
    <p class="text-gray-500 mb-8">Login or create a new account</p>

    <a href="{{route('register')}}" class="block bg-indigo-600 text-white py-3 rounded-lg mb-4 hover:bg-indigo-700">
      Register
    </a>

    <a href="{{route('login')}}" class="block border border-indigo-600 text-indigo-600 py-3 rounded-lg hover:bg-indigo-50">
      Login
    </a>
  </div>
@if ($errors->any())
    <ul>
        @foreach ($errors->all() as $error)
            <li class="text-red-500">{{ $error }}</li>
        @endforeach
    </ul>
@endif

</body>
</html>
