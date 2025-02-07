@extends('layout.app')

@section('title', 'Settings - Chatbot')

@section('main')


<div class="flex flex-col justify-center flex-wrap p-5 w-full sm:max-w-4xl sm:mx-auto my-3">

    <div>
        @if(session()->has("status"))

        @if(session('status')[0] === 'error')
        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 " role="alert">
            <span class="font-medium">Error !</span> {{ session("status")[1] }}
        </div>
        @else
        <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 " role="alert">
            <span class="font-medium">Success !</span> {{ session("status")[1] }}
        </div>
        @endif

        @endif
    </div>

    <h5 class="text-xl font-bold mt-2 mb-5">Settings</h5>


    <form action="?" method="post" enctype="multipart/form-data">

        @csrf


        <div class="mb-6">
            <label class="block mb-2 text-sm font-medium text-gray-900 ">Your OpenAI API Key</label>
            <input value="{{ $data->openaiKey }}" name="openaiKey" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 " placeholder="sk-xxxxxxxxx" required>
        </div>

        <div class="mb-6">
            <label class="block mb-2 text-sm font-medium text-gray-900 ">GPT System Message</label>
            <textarea name="systemMsg" rows="8" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500" placeholder="You are a chatbot ....." required>{{ $data->systemMsg }}</textarea>
        </div>

        <div class="mb-6">

            <div class="flex justify-between">
                <label class="block mb-2 text-sm font-medium text-gray-900 ">GPT Temperature</label>
                <span id="tempValue" class="text-sm font-medium"></span>
            </div>
            <input value="{{ $data->temperature }}" id="tempInput" oninput="tempChanged(this)" name="temperature" type="range" max="20" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer ">

        </div>

        <div class="mb-6">

            <label class="block mb-2 text-sm font-medium text-gray-900 ">Model</label>
            <select name="model" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 ">
                <option value="gpt-3.5-turbo" {{ $data->model === "gpt-3.5-turbo" ? 'selected' : '' }}>GPT 3.5 Turbo (16K + 4K)</option>
                <option value="gpt-4" {{ $data->model === "gpt-4" ? 'selected' : '' }}>GPT-4 (8K)</option>
                <option value="gpt-4o" {{ $data->model === "gpt-4o" ? 'selected' : '' }}>GPT-4o</option>
                <option value="gpt-4-32k" {{ $data->model === "gpt-4-32k" ? 'selected' : '' }}>GPT-4 (32K) [Not available for everyone]</option>
                <option value="gpt-4-turbo-preview" {{ $data->model === "gpt-4-turbo-preview" ? 'selected' : '' }}>GPT-4 (128K)</option>
            </select>

        </div>

        <div class="mb-6">
            <label class="block mb-2 text-sm font-medium text-gray-900 ">Password for this panel</label>
            <input value="" name="password" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 ">
        </div>

        <div class="mb-6">
            <label class="block mb-2 text-sm font-medium text-gray-900 ">Telegram Bot Token (Optional)</label>
            <input value="{{ $data->tgBotId }}" name="tgBotId" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 " placeholder="">
        </div>


        <hr class="my-10">


        <div class="mb-6">
            <label class="block mb-2 text-sm font-medium text-gray-900 ">Display Name</label>
            <input value="{{ $data->displayName }}" name="displayName" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 " placeholder="My Chatbot" required>
        </div>



        <div class="mb-6">
            <label class="block mb-2 text-sm font-medium text-gray-900 ">Initial Message</label>
            <textarea name="initMsg" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500" placeholder="Hello, how may I help you ?" required>{{ $data->initMsg }}</textarea>
        </div>


        <div class="">
            <label class="block mb-2 text-sm font-medium text-gray-900 ">Allowed Domains</label>
            <textarea name="allowedDomains" rows="8" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500" placeholder="You are a chatbot .....">{{ $data->allowedDomains }}</textarea>
        </div>

        <div class="mb-6">
            <span class="text-sm">Enter domains where chatbot should work, one domain per line. Leave empty to allow the chatbot to work on all domains.</span>
        </div>

        <hr class="my-10">

        <div class="my-7">
            <p class="block mb-2 text-sm text-gray-900 mb-5 font-bold">Bubble Chatbot Colors</p>

            <div class="mb-6 flex items-center justify-between">
                <label class="mr-3 block mb-2 text-sm font-medium text-gray-900 ">User Message Background Color</label>
                <input name="userMsgColor" class="mb-1" type="color" value="{{ $data->userMsgColor }}" />
            </div>

            <div class="mb-6 flex items-center justify-between">
                <label class="mr-3 block mb-2 text-sm font-medium text-gray-900 ">AI Message Background Color</label>
                <input name="aiMsgColor" class="mb-1" type="color" value="{{ $data->aiMsgColor }}" />
            </div>

            <div class="mb-6 flex items-center justify-between">
                <label class="mr-3 block mb-2 text-sm font-medium text-gray-900 ">Chat Bubble Background Color</label>
                <input name="chatBubbleColor" class="mb-1" type="color" value="{{ $data->chatBubbleColor }}" />
            </div>
        </div>

        <hr class="my-10">


        <div class="mb-6">

            <label class="block mb-2 text-sm font-medium text-gray-900 " for="file_input">Upload Logo</label>
            <input accept=".svg,.png,.jpg,.jpeg" onchange="preview()" name="chatIcon" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 " type="file">
            <img src="{{ asset('storage/' . $data->chatIcon) }}" id="imgPreview" class="my-3" src="" width="100px" height="100px" />

        </div>





        <button type="submit" class="mb-5 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 ">Save</button>

    </form>


</div>

@endsection

@section('js')

<script>
    tempValue.innerText = tempInput.value / 10;

    const tempChanged = (elem) => {

        tempValue.innerText = elem.value / 10;
    }

    function preview() {
        imgPreview.classList.remove('hidden');
        imgPreview.src = URL.createObjectURL(event.target.files[0]);
    }
</script>


@endsection
