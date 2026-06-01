<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div class="flex min-h-screen">

    <!-- Sidebar -->
    <aside class="w-64 bg-indigo-600 text-white">
        <div class="p-6 text-2xl font-bold">
            Welcome {{ Auth::user()->name }}
        </div>

        <nav class="mt-6">
            <a href="#" class="block px-6 py-3 bg-indigo-700">Dashboard</a>
            <a href="#" class="block px-6 py-3 hover:bg-indigo-700">Users</a>
            <a href="#" class="block px-6 py-3 hover:bg-indigo-700">Settings</a>

            <form method="POST" action="{{route('logout')}}" class="mt-6 px-6">
                @csrf
                <button class="w-full bg-red-500 py-2 rounded hover:bg-red-600">
                    Logout
                </button>
            </form>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-8">

        <!-- Top Bar -->
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">Dashboard</h1>
            <div class="text-gray-600">
                Welcome, <span class="font-semibold">{{ auth()->user()->name ?? 'User' }}</span>
            </div>
        </div>

        <!-- Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-gray-500">Users</h2>
                <p class="text-3xl font-bold mt-2">120</p>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-gray-500">Sales</h2>
                <p class="text-3xl font-bold mt-2">$4,500</p>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-gray-500">Orders</h2>
                <p class="text-3xl font-bold mt-2">89</p>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b">
                <h2 class="text-xl font-semibold">Recent Users</h2>
            </div>

            <table class="w-full text-left">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-4">Name</th>
                        <th class="p-4">Email</th>
                        <th class="p-4">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-t">
                        <td class="p-4">Ali</td>
                        <td class="p-4">ali@gmail.com</td>
                        <td class="p-4 text-green-600">Active</td>
                    </tr>
                    <tr class="border-t">
                        <td class="p-4">Ahmed</td>
                        <td class="p-4">ahmed@gmail.com</td>
                        <td class="p-4 text-red-500">Inactive</td>
                    </tr>
                </tbody>
            </table>
        </div>

    </main>
</div>

</body>
</html>
