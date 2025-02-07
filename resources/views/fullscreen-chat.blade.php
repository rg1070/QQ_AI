<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot</title>
    <link rel="stylesheet" href="/css/full-chatbot.css">


    <style>
        /* Hide scrollbar for Chrome, Safari and Opera */
        *::-webkit-scrollbar {
            display: none;
        }

        /* Hide scrollbar for IE, Edge and Firefox */
        * {
            -ms-overflow-style: none;
            /* IE and Edge */
            scrollbar-width: none;
            /* Firefox */
        }

        ._chatbot-typing {
            position: relative;
            min-width: 40px;
            min-height: 10px;
        }

        ._chatbot-typing span {
            content: '';
            animation: __blink 1.5s infinite;
            animation-fill-mode: both;
            height: 10px;
            width: 10px;
            position: absolute;
            left: 0;
            top: 0;
            border-radius: 50%;
            background-color: black;
        }

        ._chatbot-typing span:nth-child(2) {
            animation-delay: 0.2s;
            margin-left: 15px;
        }

        ._chatbot-typing span:nth-child(3) {
            animation-delay: 0.4s;
            margin-left: 30px;
        }

        @keyframes __blink {
            0% {
                opacity: 0.1;
            }

            20% {
                opacity: 1;
            }

            100% {
                opacity: 0.1;
            }
        }

        .w-30px{
            min-width: 60px;
            max-width: 60px;
        }
         .prose p{
            margin-top: 15px;
            margin-bottom: 15px;
        }
        .prose h3, .prose h1, .prose h2, .prose h4, .prose h5, .prose h6{
            font-size: 18px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="w-screen h-screen bg-white hidden" id="mainContainer">

        <div class="messages w-full overflow-y-auto bg-white" id="_chatbot-messages">

        </div>

        <div id="_chatbotExtraMsg" style="min-height: 120px;"></div>


        <div style="bottom:24px;" class="fixed max-w-6xl w-full left-1/2 px-2 transform -translate-x-1/2">

            <div class="w-full bg-white flex items-center justify-center rounded-xl border border-slate-200 px-3 shadow-lg">
                <textarea onkeydown="_chatbotEnterClicked(event)" oninput="_autoExpand(this)" id="_chatbotUserInput" style="max-height: 150px;" rows="1" placeholder="Type your message..." type="text" class="w-11/12 outline-none border-none focus:ring-0 resize-none" data-gramm="false" data-gramm_editor="false" data-enable-grammarly="false"></textarea>
                <div onclick="_chatbotAddUserMsg()" class="bg-gray-100 hover:bg-gray-200 cursor-pointer rounded-xl p-1 my-2 ml-3">
                    <img src="/images/arrow-up.svg" width="30" height="30" alt="">
                </div>
            </div>

        </div>


    </div>

    <div onclick="_chatbotReset()" class="bg-gray-200 hover:bg-gray-300 cursor-pointer rounded-full p-1 fixed right-4 top-4">
        <img src="/images/reset.svg" width="25" height="25" alt="">
    </div>

</body>
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script id="_chatbotScript" data-url="{{ env('CHATBOT_URL') }}"></script>

<script>
    let _autoExpandHeight = 0;

    let __blockChatbot = false;
    let __firstAIMsg = "";
    let __firstTimeChatbot = true;
    let __aiImage;
    let __aiName;

    let _chatbotURL;
    let _chatbotInitMsg;

    //chatbot url
    document.addEventListener('DOMContentLoaded', function() {

        _chatbotURL = document.getElementById("_chatbotScript");
        _chatbotURL = _chatbotURL.getAttribute('data-url');
        _chatbotInit();

        _chatbotInitMsg = document.getElementById('_chatbotInitMsg');
        

    });


    function _autoExpand(textarea) {

        const _chatbotExtraMsg = document.getElementById('_chatbotExtraMsg');

        // Reset the height to 1px to get the scrollHeight
        textarea.style.height = '1px';
        //_chatbotExtraMsg.style.minHeight = '0px';

        // Set the height to the scrollHeight (actual height needed)
        textarea.style.height = `${textarea.scrollHeight}px`;
        _autoExpandHeight = textarea.scrollHeight;


        // if (_autoExpandHeight < 500) {
        //     _chatbotExtraMsg.style.minHeight = `${textarea.scrollHeight}px`;
        // } else {
        //     _chatbotExtraMsg.style.minHeight = '500px';
        // }


    }

    const _chatbotAddUserMsg = () => {

        const chatbotUserInput = document.getElementById('_chatbotUserInput');
        const msg = chatbotUserInput.value;

        if (msg === "" || __blockChatbot === true) return;

        const msgContainer = document.querySelector('#_chatbot-messages');

        msgContainer.innerHTML += `<div class="user-msg py-10 border-slate-200 border-b"><div class="flex max-w-6xl sm:px-3 sm:px-0 mx-3 lg:mx-auto"><div class="rounded-full p-3 pt-0 mr-3 w-30px"><img src="/images/user.png" alt=""></div><div class="flex flex-col justify-center"><div class="font-bold">You</div><div class="msg mt-2">${msg}</div></div></div></div>`;

        chatbotUserInput.value = "";
        chatbotUserInput.value = chatbotUserInput.value.replace(/[\n\r]/g, '');
        chatbotUserInput.style.removeProperty('height');

        _addMsgToLocalStorage('user', msg);

        _getChatBotResponse(msg);

        setTimeout(() => {
            window.scrollTo(0, document.body.scrollHeight);
        }, 100);

    }

    const _chatbotEnterClicked = (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            // Your code to handle the "Enter" key press
            _chatbotAddUserMsg();
        }
    }


    function _createAIMsgElem() {
        // Create main div element
        let mainDiv = document.createElement("div");
        mainDiv.className = "ai-msg py-10 border-slate-200 border-b bg-slate-50";

        // Create inner div for flex layout
        let innerDiv = document.createElement("div");
        innerDiv.className = "flex max-w-6xl sm:px-3 sm:px-0 lg:mx-auto";

        // Create rounded icon div
        let iconDiv = document.createElement("div");
        iconDiv.className = "rounded-full p-3 pt-0 mr-3 w-30px";

        // Create image element for the icon
        let iconImg = document.createElement("img");
        // iconImg.width = 30;
        // iconImg.height = 30;
        iconImg.src = __aiImage;
        iconImg.alt = "";

        // Append image to the icon div
        iconDiv.appendChild(iconImg);

        // Create div for AI name and message
        let textDiv = document.createElement("div");
        textDiv.className = "flex flex-col justify-center";

        // Create div for AI name (font-bold)
        let nameDiv = document.createElement("div");
        nameDiv.className = "font-bold";
        nameDiv.innerText = __aiName;

        // Create div for the message (msg)
        let messageDiv = document.createElement("div");
        messageDiv.className = "msg mt-2 prose";

        // Append name and message divs to the text div
        textDiv.appendChild(nameDiv);
        textDiv.appendChild(messageDiv);

        // Append icon div and text div to the inner div
        innerDiv.appendChild(iconDiv);
        innerDiv.appendChild(textDiv);

        // Append inner div to the main div
        mainDiv.appendChild(innerDiv);

        // Return the created main div
        return {
            mainDiv: mainDiv,
            msgDiv: messageDiv
        };
    }

    const _chatbotReset = () => {
        const msgContainer = document.querySelector('#_chatbot-messages');
        msgContainer.innerHTML = "";

        const aiMsg = _createAIMsgElem();
        aiMsg.msgDiv.innerText = __firstAIMsg;
        msgContainer.appendChild(aiMsg.mainDiv);

        localStorage.removeItem('__chatbot-msgs-full');
    }

    async function _getChatBotResponse(message) {

        __blockChatbot = true;

        const msgContainer = document.querySelector('#_chatbot-messages');

        const postData = {
            'message': message,
        }

        let response;

        const aiMsg = _createAIMsgElem();
        aiMsg.msgDiv.innerHTML = `<div class="_chatbot-typing mt-2"><span></span><span></span><span></span></div>`;
        msgContainer.appendChild(aiMsg.mainDiv);

        try {
            response = await fetch(_chatbotURL + '/api/chatbot/chat', {
                method: 'POST',
                body: JSON.stringify(postData),
                headers: {
                    'Content-Type': 'application/json',
                },
            });

            if (!response.ok) {

                const responseData = await response.json();
                let errMsg = "";

                if (responseData && responseData.msg) {
                    throw new Error(responseData.msg);
                } else {
                    throw new Error("Something went wrong, please try later.");
                }
            }

        } catch (error) {

            aiMsg.msgDiv.innerHTML = error.message;
            __blockChatbot = false;
        }

        const reader = response.body.getReader();
        const textDecoder = new TextDecoder();
        let markedText = "";

        while (true) {
            const {
                value,
                done
            } = await reader.read();
            if (done) break;

            const resultString = textDecoder.decode(value);

            markedText += resultString;

            aiMsg.msgDiv.innerHTML = marked.parse(markedText);

            window.scrollTo(0, document.body.scrollHeight);

        }

        //store msg on localstorage
        _addMsgToLocalStorage('ai', aiMsg.msgDiv.innerHTML);
        __blockChatbot = false;


    }

    const _addMsgToLocalStorage = (type, msg) => {

        let msgs = localStorage.getItem('__chatbot-msgs-full');

        if (msgs !== null) {
            msgs = JSON.parse(msgs);
        } else {
            msgs = [];
        }

        msgs.push({
            type: type,
            msg: msg
        });

        localStorage.setItem('__chatbot-msgs-full', JSON.stringify(msgs));
    }


    const _chatbotInit = () => {

        let msgs = localStorage.getItem('__chatbot-msgs-full');

        if (msgs !== null) {

            let data = JSON.parse(msgs);

            const msgContainer = document.querySelector('#_chatbot-messages');

            for (const x of data) {

                if (x.type === 'user') {
                    msgContainer.innerHTML += `<div class="user-msg py-10 border-slate-200 border-b"><div class="flex max-w-6xl sm:px-3 sm:px-0 mx-3 lg:mx-auto"><div class="rounded-full p-3 pt-0 mr-3 w-30px"><img src="/images/user.png" alt=""></div><div class="flex flex-col justify-center"><div class="font-bold">You</div><div class="msg mt-2">${x.msg}</div></div></div></div>`;
                } else {
                    msgContainer.innerHTML += `<div class="ai-msg py-10 border-slate-200 border-b bg-slate-50"><div class="flex max-w-6xl sm:px-3 sm:px-0 lg:mx-auto"><div class="rounded-full p-3 pt-0 mr-3 w-30px"><img class="_ai-img-icon" src="" alt=""></div><div class="flex flex-col justify-center"><div class="font-bold ai-name"></div><div class="msg mt-2 prose">${x.msg}</div></div></div></div>`;
                }

            }

            __firstTimeChatbot = false;

           setTimeout(() => {
            window.scrollTo(0, document.body.scrollHeight);
           }, 500);
        }

        fetch(_chatbotURL + "/api/chatbot/info")
            .then(response => {
                // Check if the request was successful (status code 200)
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }

                // Parse the response as JSON
                return response.json();
            })
            .then(data => {
                // Handle the JSON data

                __aiName = data.data.displayName;

                let aiNames = document.querySelectorAll('.ai-name');

                aiNames.forEach((e) => {
                    e.innerText = __aiName;
                });

                //setting first ai msg
                __firstAIMsg = data.data.initMsg;

                //setting ai image src
                __aiImage = data.data.chatIcon;


                //select all ai images and run loop and update their src
                const aiImages = document.querySelectorAll('._ai-img-icon');

                aiImages.forEach((e) => {
                    e.src = __aiImage;
                });

                //add init msg
                const msgContainer = document.querySelector('#_chatbot-messages');

                const aiMsg = _createAIMsgElem();
                aiMsg.msgDiv.innerText = data.data.initMsg;
                msgContainer.insertBefore(aiMsg.mainDiv, msgContainer.firstChild);

                document.getElementById('mainContainer').classList.remove('hidden');



            })
            .catch(error => {
                console.log(error);
            });


    }
</script>

</html>
