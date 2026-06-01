<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

  <div class="bg-white p-8 rounded-xl shadow-lg w-96">
    <h2 class="text-2xl font-bold text-center mb-6">Login</h2>
<form action="{{ route('loginMatch') }}" method="POST">
  @csrf
    <input type="email" placeholder="Email" name="email"
      class="w-full mb-4 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">

               @error('email')
     <h1>{{$message}}</h1>

     @enderror

    <input type="password" placeholder="Password" name="password"
      class="w-full mb-6 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">

               @error('password')
     <h1>{{$message}}</h1>

     @enderror
    <button class="w-full bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700">
      Login
    </button>
</form>
    <p class="text-center text-sm text-gray-500 mt-4">
      Don’t have an account?
      <a href="{{route('register')}}" class="text-indigo-600 font-semibold">Sign up</a>
    </p>
  </div>

</body>
</html>
