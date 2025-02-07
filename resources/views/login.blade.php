<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.1.1/flowbite.min.css" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: 'Manrope', sans-serif;
        }
    </style>

</head>

<body>

    <div class="flex justify-center items-center h-screen w-screen">
        <div class="justify-center flex items-center p-14 m-2 border border-gray-200 rounded-xl">
            <form action="?" method="post">
                @csrf
                <div class="mb-6">
                    <label class="block mb-2 text-sm font-medium text-gray-900 ">Enter Password</label>
                    <input name="password" type="password" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 " required>
                </div>

                @if(session()->has("status"))
                  <div class="mb-6"> <span class="text-sm font-medium text-red-600">{{ session("status")[1] }}</span></div>
                @endif


                <button type="submit" class="mb-5 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 w-full">Login</button>

            </form>
        </div>
    </div>

</body>

</html>