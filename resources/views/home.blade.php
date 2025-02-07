@extends('layout.app')

@section('title', 'Home - Chatbot')

@section('main')


<div class="flex justify-center items-center flex-wrap p-5 max-w-6xl mx-auto my-3">

    <a href="{{ route('training') }}" class="sm:w-52 w-full sm:m-6">
        <div class="flex justify-center items-center flex-col py-8 px-14 my-5 rounded bg-gray-100 hover:bg-gray-200 cursor-pointer transition-all duration-300 ease-in-out">
            <img src="https://i.ibb.co/cyFBvQq/book.png" width="70" class="my-3">
            <p class="text-sm font-medium text-gray-900 mt-3 px-4 py-1 bg-blue-600 rounded-full text-white">Training</p>
        </div>
    </a>

    <a href="{{ route('chatbot.script') }}" class="sm:w-52 w-full sm:m-6">
        <div class="flex justify-center items-center flex-col py-8 px-14 my-5 rounded bg-gray-100 hover:bg-gray-200 cursor-pointer transition-all duration-300 ease-in-out">
            <img src="https://i.ibb.co/7bx4kzv/link.png" width="70" class="my-3">
            <p class="text-sm font-medium text-gray-900 mt-3 px-4 py-1 bg-blue-600 rounded-full text-white">Script</p>
        </div>
    </a>



    <a href="{{ route('settings') }}" class="sm:w-52 w-full sm:m-6">
        <div class="flex justify-center items-center flex-col py-8 px-14 my-5 rounded bg-gray-100 hover:bg-gray-200 cursor-pointer transition-all duration-300 ease-in-out">
            <img src="https://i.ibb.co/7W9F4LK/settings-1.png" width="70" class="my-3">
            <p class="text-sm font-medium text-gray-900 mt-3 px-4 py-1 bg-blue-600 rounded-full text-white">Settings</p>
        </div>
    </a>

    <a href="{{ route('test') }}" class="sm:w-52 w-full sm:m-6">
        <div class="flex justify-center items-center flex-col py-8 px-14 my-5 rounded bg-gray-100 hover:bg-gray-200 cursor-pointer transition-all duration-300 ease-in-out">
            <img src="https://i.ibb.co/ykFkCyY/chatbot-1.png" width="70" class="my-3">
            <p class="text-sm font-medium text-gray-900 mt-3 px-4 py-1 bg-blue-600 rounded-full text-white">Test</p>
        </div>
    </a>




</div>


@endsection
