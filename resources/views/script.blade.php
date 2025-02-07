@extends('layout.app')

@section('title', 'Script Tag - Chatbot')

@section('main')


<div class="flex justify-center items-center flex-wrap p-5 max-w-4xl mx-auto my-3">


    <div class="my-3">
        <h5 class="text-xl font-bold mt-2 mb-5">Bubble Chat (Copy paste in the header of the website)</h5>

        <div id="bubbleLink" style="font-family: 'Courier New', Courier, monospace;" class="font-courier bg-gray-200 rounded-lg p-5 mb-5">

            &lt;link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet"&gt;<br>
            &lt;script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"&gt;&lt;/script&gt;<br>
            &lt;script src="{{ env('CHATBOT_URL') }}/script.js" id="_chatbotScript" data-url="{{ env('CHATBOT_URL') }}"&gt;&lt;/script&gt;


        </div>

        <button onclick="copy('bubble')" type="button" class="cursor-pointer w-18 sm:w-auto text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center ">Copy</button>

    </div>

    <div class="my-3">

        <h5 class="text-xl font-bold mt-2 mb-5">Chatbot Widget (This is iframe, copy paste this like a youtube video)</h5>


        <div id="fullLink" style="font-family: 'Courier New', Courier, monospace;" class="font-courier bg-gray-200 rounded-lg p-5 mb-2">

            &lt;iframe src="{{ env('CHATBOT_URL') }}/chatbot-widget" width="900" height="700" frameborder="0"&gt;&lt;/iframe&gt;

        </div>

        <p class="text-sm text-gray-800 mb-5">Adjust the iframe width and height as per your needs.</p>


        <button onclick="copy('full')" type="button" class="cursor-pointer w-18 sm:w-auto text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center ">Copy</button>
    </div>



</div>

@endsection

@section('js')

<script>
    const copy = (type) => {

        if (type === 'bubble') {
            navigator.clipboard.writeText(bubbleLink.innerText);
        }

        if (type === 'full') {
            navigator.clipboard.writeText(fullLink.innerText);
        }


    }
</script>
@endsection