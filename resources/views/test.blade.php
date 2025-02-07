@extends('layout.app')

@section('title', 'Test - Chatbot')

@section('main')


<div class="flex justify-center items-center flex-wrap p-5 max-w-4xl mx-auto my-3">

    <iframe src="{{ env('CHATBOT_URL') }}/chatbot-widget" width="900" height="700" frameborder="0"></iframe>
</div>
@endsection

@section('js')
<link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script src="{{ env('CHATBOT_URL') }}/script.js" id="_chatbotScript" data-url="{{ env('CHATBOT_URL') }}"></script>

@endsection