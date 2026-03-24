(function(){let e=document.createElement(`link`).relList;if(e&&e.supports&&e.supports(`modulepreload`))return;for(let e of document.querySelectorAll(`link[rel="modulepreload"]`))n(e);new MutationObserver(e=>{for(let t of e)if(t.type===`childList`)for(let e of t.addedNodes)e.tagName===`LINK`&&e.rel===`modulepreload`&&n(e)}).observe(document,{childList:!0,subtree:!0});function t(e){let t={};return e.integrity&&(t.integrity=e.integrity),e.referrerPolicy&&(t.referrerPolicy=e.referrerPolicy),e.crossOrigin===`use-credentials`?t.credentials=`include`:e.crossOrigin===`anonymous`?t.credentials=`omit`:t.credentials=`same-origin`,t}function n(e){if(e.ep)return;e.ep=!0;let n=t(e);fetch(e.href,n)}})(),new class{baseUrl=`https://lead.ahmetcotur.com`;siteKey;shadowRoot;config={chatbot_name:`Asistan`,chatbot_color:`#000000`,chatbot_welcome_message:`Merhaba! Size nasıl yardımcı olabilirim?`};messages=[];isTyping=!1;isFinished=!1;constructor(){this.siteKey=this.getSiteKey(),this.boot()}getSiteKey(){return(document.currentScript||document.querySelector(`script[data-site-key]`))?.getAttribute(`data-site-key`)||`TEST_KEY`}async boot(){try{let e=await fetch(`${this.baseUrl}/api/leads/config?site_key=${this.siteKey}`);if(e.ok){let t=await e.json();t.chatbot_name&&(this.config.chatbot_name=t.chatbot_name),t.chatbot_color&&(this.config.chatbot_color=t.chatbot_color),t.chatbot_welcome_message&&(this.config.chatbot_welcome_message=t.chatbot_welcome_message)}}catch(e){console.error(`VoynWidget Error: Failed to load config`,e)}this.messages.push({role:`assistant`,content:this.config.chatbot_welcome_message}),this.initDOM()}initDOM(){if(document.getElementById(`voyn-widget-host`))return;let e=document.createElement(`div`);e.id=`voyn-widget-host`,document.body.appendChild(e),this.shadowRoot=e.attachShadow({mode:`open`});let t=document.createElement(`div`);t.id=`voyn-widget-container`,t.innerHTML=this.getHTML();let n=document.currentScript?.src;if(!n){let e=document.querySelectorAll(`script`);for(let t of Array.from(e))if(t.src.includes(`widget.js`)){n=t.src;break}}let r=n?n.replace(`widget.js`,`widget.css`):`/widget/widget.css`,i=document.createElement(`link`);i.rel=`stylesheet`,i.href=r,this.shadowRoot.appendChild(i),this.shadowRoot.appendChild(t),this.bindEvents(),this.renderMessages()}bindEvents(){let e=this.shadowRoot.getElementById(`voyn-widget-button`),t=this.shadowRoot.getElementById(`voyn-widget-close`),n=this.shadowRoot.getElementById(`voyn-widget-panel`);e&&(e.style.backgroundColor=this.config.chatbot_color);let r=this.shadowRoot.querySelector(`#voyn-widget-panel .header-bg`);r&&(r.style.backgroundColor=this.config.chatbot_color),e?.addEventListener(`click`,()=>{n?.classList.toggle(`hidden-panel`),this.scrollToBottom()}),t?.addEventListener(`click`,()=>{n?.classList.add(`hidden-panel`)}),this.shadowRoot.getElementById(`chat-form`)?.addEventListener(`submit`,e=>{e.preventDefault(),this.handleUserSubmit()})}getHTML(){return`
            <div id="voyn-widget-button" class="shadow-lg text-white flex items-center justify-center cursor-pointer transition-transform hover:scale-110" style="width:56px; height:56px; border-radius:50%; position:fixed; bottom:24px; right:24px; z-index:999999;">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            </div>
            <div id="voyn-widget-panel" class="hidden-panel flex flex-col shadow-2xl bg-white" style="position:fixed; bottom:90px; right:24px; width:360px; height:520px; border-radius:16px; overflow:hidden; z-index:999999; border: 1px solid #eee;">
                <div class="header-bg p-4 flex justify-between items-center text-white">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center relative">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8V4H8"/><rect width="16" height="12" x="4" y="8" rx="2"/><path d="M2 14h2"/><path d="M20 14h2"/><path d="M15 13v2"/><path d="M9 13v2"/></svg>
                            <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-400 border-2 border-white rounded-full"></span>
                        </div>
                        <div>
                            <h3 class="font-bold text-sm m-0">${this.config.chatbot_name}</h3>
                            <p class="text-xs text-white/80 m-0">Çevrimiçi</p>
                        </div>
                    </div>
                    <button id="voyn-widget-close" class="text-white/80 hover:text-white bg-transparent border-0 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
                
                <div id="chat-messages" class="flex-1 overflow-y-auto p-4 flex flex-col gap-4 bg-gray-50 text-sm">
                    <!-- Messages go here -->
                </div>

                <div id="chat-input-area" class="p-3 border-t border-gray-100 bg-white">
                    <form id="chat-form" class="flex items-center gap-2">
                        <input type="text" id="chat-input" class="flex-1 border-0 bg-gray-100 rounded-full px-4 py-3 outline-none focus:ring-2 focus:ring-black/10 transition-shadow" placeholder="Bir mesaj yazın..." autocomplete="off">
                        <button type="submit" id="chat-submit" class="w-10 h-10 rounded-full flex items-center justify-center text-white border-0 cursor-pointer transition-opacity hover:opacity-80" style="background-color: ${this.config.chatbot_color}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left: -2px;"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        `}escapeHTML(e){return e.replace(/[&<>'"]/g,e=>({"&":`&amp;`,"<":`&lt;`,">":`&gt;`,"'":`&#39;`,'"':`&quot;`})[e]||e)}renderMessages(){let e=this.shadowRoot.getElementById(`chat-messages`);if(!e)return;let t=``;this.messages.forEach(e=>{e.role===`user`?t+=`
                    <div class="flex justify-end">
                        <div class="bg-black text-white px-4 py-2 rounded-2xl rounded-tr-sm max-w-[85%] leading-relaxed shadow-sm">
                            ${this.escapeHTML(e.content)}
                        </div>
                    </div>
                `:e.role===`assistant`&&(t+=`
                    <div class="flex justify-start">
                        <div class="bg-white border border-gray-100 text-gray-800 px-4 py-3 rounded-2xl rounded-tl-sm max-w-[85%] leading-relaxed shadow-sm">
                            ${this.escapeHTML(e.content)}
                        </div>
                    </div>
                `)}),this.isTyping&&(t+=`
                <div class="flex justify-start" id="typing-indicator">
                    <div class="bg-white border border-gray-100 px-4 py-3 rounded-2xl rounded-tl-sm shadow-sm flex gap-1 items-center h-10">
                        <div class="w-2 h-2 bg-gray-300 rounded-full animate-bounce" style="animation-delay: 0s"></div>
                        <div class="w-2 h-2 bg-gray-300 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                        <div class="w-2 h-2 bg-gray-300 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
                    </div>
                </div>
            `),e.innerHTML=t,this.scrollToBottom()}scrollToBottom(){let e=this.shadowRoot.getElementById(`chat-messages`);e&&setTimeout(()=>{e.scrollTop=e.scrollHeight},50)}async handleUserSubmit(){if(this.isFinished||this.isTyping)return;let e=this.shadowRoot.getElementById(`chat-input`),t=this.shadowRoot.getElementById(`chat-submit`),n=e.value.trim();if(n){e.value=``,this.messages.push({role:`user`,content:n}),this.isTyping=!0,this.renderMessages();try{let n=await fetch(`${this.baseUrl}/api/leads/chat`,{method:`POST`,headers:{"Content-Type":`application/json`,Accept:`application/json`},body:JSON.stringify({site_key:this.siteKey,messages:this.messages})});if(n.ok){let r=await n.json();this.isTyping=!1,r.role&&r.content&&this.messages.push({role:r.role,content:r.content}),r.lead_saved&&(this.isFinished=!0,e.disabled=!0,e.placeholder=`Görüşme tamamlandı.`,t.disabled=!0,t.style.opacity=`0.5`),this.renderMessages()}else this.isTyping=!1,this.messages.push({role:`assistant`,content:`Üzgünüm, şu an bağlantı kuramıyorum. Lütfen daha sonra tekrar deneyin.`}),this.renderMessages()}catch(e){console.error(`Chat error:`,e),this.isTyping=!1,this.messages.push({role:`assistant`,content:`Ağ hatası oluştu. Lütfen bağlantınızı kontrol edip tekrar deneyin.`}),this.renderMessages()}}}};