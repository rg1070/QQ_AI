@extends('layout.app')

@section('title', 'Training - Chatbot')


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
            <span class="font-medium">Success !</span> {!! session("status")[1] !!}
        </div>
        @endif

        @endif
    </div>

    <form action="?" method="post" enctype="multipart/form-data">

        @csrf


        <div class="mb-5">
            <h5 class="text-xl font-bold mt-2 mb-5">Train Chatbot</h5>

            <div class="">

                <label class="block mb-2 text-sm font-medium text-gray-900 " for="file_input">Upload file (PDF , TXT or CSV)</label>
                <input name="file" accept=".pdf, .txt, .csv" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none" id="file_input" type="file">
                <div class="text-purple-800 text-sm mt-3">Use CSV only if you want to train chatbot for word-meanings or translation related data.</div>
            </div>

            <div class="flex items-center my-5 h-8 w-full">
                <div class="flex-1 border-t border-gray-300"></div>
                <div class="mx-4 text-gray-500">OR</div>
                <div class="flex-1 border-t border-gray-300"></div>
            </div>

        </div>

        <div class="mb-5">

            <div class="mb-5 flex items-center w-full">
                <div class="w-11/12">
                    <label class="block mb-2 text-sm font-medium text-gray-900 ">Website Link</label>
                    <input name="mainWeb" id="websiteLink" type="text" class="mb-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 " placeholder="https://example.com">
                    <span class="text-xs text-gray-700">Note : This will only scrape the links that exist on the specified URL.</span>
                </div>

                <button onclick="scrapeLinks(this)" type="button" class="cursor-pointer ml-3 w-18 sm:w-auto text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center ">Scrape</button>
            </div>


            <div class="mb-5">


                <label class="block mb-2 text-sm font-medium text-gray-900 ">Website pages - one link per line</label>
                <textarea id="links" name="webLinks" rows="8" class="mb-1 block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 " placeholder="https://example.com/home&#10;https://example.com/how-to-do&#10;https://example.com/privacy-policy"></textarea>
                <span class="text-xs text-gray-700">Please remove garbage links, duplicate links and the links which are useless for the training for better quality. One bad link can make difference in the quality.</span>

            </div>

        </div>


        <button type="submit" class="mb-5 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 ">Train</button>

    </form>

    <div class="mb-5">
        <h5 class="text-xl font-bold my-5">Reset Chatbot And Delete All Trainings</h5>

        <button onclick="reset()" type="button" class="mb-3 w-full focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 ">Reset All Trainings</button>

       
    </div>

    <div class="mb-5 w-full">

        <div class="my-5">
            <h5 class="text-xl font-bold">Training History</h5>

            <span class="text-xs text-gray-700">Refreshing the page is required to see changes in the status</span>

        </div>

        <div class="relative overflow-x-auto">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 ">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 ">
                    <tr>
                        <th scope="col" class="px-6 py-3">
                            Name
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Type
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Date
                        </th>
                    </tr>
                </thead>
                <tbody>

                    @foreach($data as $d)

                    <tr class="bg-white border-b ">
                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap ">
                            {{$d->name}}
                        </th>
                        <td class="px-6 py-4">
                            <span class="bg-gray-100 text-gray-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded dark:bg-gray-700 dark:text-gray-300">{{ strtoupper($d->extension) }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @if($d->status === 'pending')
                            <span class="bg-blue-100 text-blue-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded ">Pending</span>

                            @elseif($d->status === 'success')
                            <span class="bg-green-100 text-green-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded ">Success</span>

                            @elseif($d->status === 'failed')
                            <span class="bg-red-100 text-red-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded ">Failed</span>

                            @endif
                        </td>
                        <td class="px-6 py-4">
                            {{date('M j, Y', strtotime($d->created_at))}}
                        </td>

                    </tr>

                    @endforeach


                </tbody>
            </table>
        </div>


    </div>
</div>

@endsection

@section('js')

<script>
    const scrapeLinks = (btn) => {

        if (websiteLink.value.length === 0) {
            return alert('Please write a website link.')
        }

        btn.innerText = 'Scraping...';
        btn.disabled = true;

        axios.get('/scrape?url=' + websiteLink.value)
            .then((e) => {

                for (const x of e.data.links) {
                    links.value += x + "\n";
                }

            })
            .catch((err) => {
                alert(err.response.data.msg);
            })
            .finally((e) => {
                btn.innerText = 'Scrape';
                btn.disabled = false;
            })
    }

    const reset = () => {

        if (confirm('Are you sure you want to reset all trainings ? This will remove all trained data and training history.')) {
            // Get the current URL
            let currentUrl = window.location.href;

            // Add "?reset=true" to the URL
            let newUrl = currentUrl + (currentUrl.includes("?") ? "&" : "?") + "reset=true";

            // Redirect to the new URL
            window.location.href = newUrl;
        }
    }
</script>


@endsection