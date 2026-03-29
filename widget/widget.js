(function(){let e=document.createElement(`link`).relList;if(e&&e.supports&&e.supports(`modulepreload`))return;for(let e of document.querySelectorAll(`link[rel="modulepreload"]`))n(e);new MutationObserver(e=>{for(let t of e)if(t.type===`childList`)for(let e of t.addedNodes)e.tagName===`LINK`&&e.rel===`modulepreload`&&n(e)}).observe(document,{childList:!0,subtree:!0});function t(e){let t={};return e.integrity&&(t.integrity=e.integrity),e.referrerPolicy&&(t.referrerPolicy=e.referrerPolicy),e.crossOrigin===`use-credentials`?t.credentials=`include`:e.crossOrigin===`anonymous`?t.credentials=`omit`:t.credentials=`same-origin`,t}function n(e){if(e.ep)return;e.ep=!0;let n=t(e);fetch(e.href,n)}})();var e=document.createElement(`link`);e.rel=`stylesheet`,e.href=`https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500&display=swap`,document.head.appendChild(e);var t=class{baseUrl=`https://lead.ahmetcotur.com`;websiteUuid=null;siteKey=null;shadowRoot;config={chatbot_name:`Asistan`,chatbot_color:`#000000`,chatbot_welcome_message:`Merhaba! Size nasıl yardımcı olabilirim?`,chatbot_representative_name:``,chatbot_representative_role:``,chatbot_avatar:``,chatbot_typing_speed:1500,chatbot_notification_active:!0,chatbot_typing_sound:null,chatbot_notification_sound:null,chatbot_error_sound:null,chatbot_hint_title:`Selam! 👋`,chatbot_hint_message:null,chatbot_error_message:`Üzgünüm, şu an bağlantı kuramıyorum. Lütfen daha sonra tekrar deneyin.`,chatbot_placeholder:`Bir mesaj yazın...`,chatbot_finish_message:`Görüşme tamamlandı.`};messages=[];isTyping=!1;isFinished=!1;audio=new Audio(`https://lead.ahmetcotur.com/widget/notification.mp3`);constructor(){let e=document.currentScript||document.querySelector(`script[data-site-key]`)||document.querySelector(`script[data-website-uuid]`);this.siteKey=e?.getAttribute(`data-site-key`)||null,this.websiteUuid=e?.getAttribute(`data-website-uuid`)||null,console.log(`VoynWidget v5.5 initialized:`,{siteKey:this.siteKey,websiteUuid:this.websiteUuid}),this.boot()}async boot(){try{let e=new URLSearchParams;this.websiteUuid&&e.append(`website_uuid`,this.websiteUuid),this.siteKey&&e.append(`site_key`,this.siteKey),e.append(`_cb`,Date.now().toString());let t=await fetch(`${this.baseUrl}/api/leads/config?${e.toString()}`);if(t.ok){let e=await t.json();e.chatbot_name&&(this.config.chatbot_name=e.chatbot_name),e.chatbot_color&&(this.config.chatbot_color=e.chatbot_color),e.chatbot_welcome_message&&(this.config.chatbot_welcome_message=e.chatbot_welcome_message),e.chatbot_representative_name&&(this.config.chatbot_representative_name=e.chatbot_representative_name),e.chatbot_representative_role&&(this.config.chatbot_representative_role=e.chatbot_representative_role),e.chatbot_avatar&&(this.config.chatbot_avatar=e.chatbot_avatar),e.chatbot_typing_speed&&(this.config.chatbot_typing_speed=e.chatbot_typing_speed),e.chatbot_error_message&&(this.config.chatbot_error_message=e.chatbot_error_message),e.chatbot_placeholder&&(this.config.chatbot_placeholder=e.chatbot_placeholder),e.chatbot_finish_message&&(this.config.chatbot_finish_message=e.chatbot_finish_message),this.config.chatbot_notification_active=e.chatbot_notification_active===void 0?!0:e.chatbot_notification_active,this.config.chatbot_typing_sound=e.chatbot_typing_sound||null,this.config.chatbot_notification_sound=e.chatbot_notification_sound||null,this.config.chatbot_error_sound=e.chatbot_error_sound||null,this.config.chatbot_hint_title=e.chatbot_hint_title||`Selam! 👋`,this.config.chatbot_hint_message=e.chatbot_hint_message||null,this.config.chatbot_notification_sound&&(this.audio=new Audio(this.config.chatbot_notification_sound)),this.messages.length>0&&this.messages[0].role===`assistant`&&(this.messages[0].content=this.config.chatbot_welcome_message)}}catch(e){console.error(`VoynWidget Error: Failed to load config`,e)}this.messages.push({role:`assistant`,content:this.config.chatbot_welcome_message}),this.initDOM()}initDOM(){if(document.getElementById(`voyn-widget-host`))return;let e=document.createElement(`div`);e.id=`voyn-widget-host`,document.body.appendChild(e),this.shadowRoot=e.attachShadow({mode:`open`});let t=document.createElement(`div`);t.id=`voyn-widget-container`,t.innerHTML=this.getHTML(),t.style.touchAction=`manipulation`,t.addEventListener(`touchstart`,e=>{e.touches.length>1&&e.preventDefault()},{passive:!1});let n=0;t.addEventListener(`touchend`,e=>{let t=new Date().getTime();t-n<=300&&e.preventDefault(),n=t},!1);let r=document.currentScript?.src;if(!r){let e=document.querySelectorAll(`script`);for(let t of Array.from(e))if(t.src.includes(`widget.js`)){r=t.src;break}}let i=r?r.replace(`widget.js`,`widget.css`):`/widget/widget.css`,a=document.createElement(`link`);a.rel=`stylesheet`,a.href=i,this.shadowRoot.appendChild(a);let o=document.createElement(`style`);o.textContent=`
            * {
                font-family: 'DM Sans', sans-serif !important;
                touch-action: manipulation !important;
                -ms-touch-action: manipulation !important;
            }
            .msg-row {
                display: flex;
                gap: 8px;
                align-items: flex-end;
                margin-bottom: 10px;
            }
            .msg-row.user {
                flex-direction: row-reverse;
            }
            .msg-avatar {
                width: 26px;
                height: 26px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 10px;
                color: #fff;
                font-weight: 500;
                flex-shrink: 0;
                margin-bottom: 2px;
                border: 1px solid rgba(255,255,255,0.1);
            }
            .bubble {
                max-width: 78%;
                padding: 9px 13px;
                border-radius: 16px;
                font-size: 13.5px;
                line-height: 1.55;
            }
            .bubble.bot {
                background: #f3f4f6;
                color: #1a1a2e;
                border-bottom-left-radius: 4px;
            }
            .bubble.user {
                background: #1a1a2e;
                color: #fff;
                border-bottom-right-radius: 4px;
            }
            .msg-appear {
                animation: msgIn 0.25s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
            }
            @keyframes msgIn {
                from { opacity: 0; transform: translateY(8px) scale(0.97); }
                to { opacity: 1; transform: none; }
            }
            .typing-dots {
                display: flex;
                gap: 4px;
                padding: 4px 0;
                align-items: center;
            }
            .dot {
                width: 7px;
                height: 7px;
                border-radius: 50%;
                background: #6b7280;
                opacity: 0.5;
                animation: bounce 1.2s infinite;
            }
            .dot:nth-child(2) { animation-delay: 0.2s; }
            .dot:nth-child(3) { animation-delay: 0.4s; }
            @keyframes bounce {
                0%, 60%, 100% { transform: translateY(0); opacity: 0.5; }
                30% { transform: translateY(-5px); opacity: 1; }
            }
            #voyn-widget-tooltip {
                font-family: 'DM Sans', sans-serif !important;
            }
            .chat-header h3 {
                font-size: 14px !important;
                font-weight: 500 !important;
            }
            .chat-header p {
                font-size: 12px !important;
            }
            textarea {
                font-size: 16px !important; /* iOS zoom prevention */
            }
            textarea::placeholder {
                font-size: 14px !important;
            }
            .hidden-panel {
                display: none !important;
            }
        `,this.shadowRoot.appendChild(o),this.shadowRoot.appendChild(t),this.hydrateConfig(),this.injectHostStyles(),this.bindEvents(),this.renderMessages()}hydrateConfig(){if(!this.shadowRoot)return;this.shadowRoot.querySelectorAll(`[data-config-color]`).forEach(e=>{e.style.backgroundColor=this.config.chatbot_color});let e=this.shadowRoot.querySelector(`#chatbot-display-name`);e&&(e.textContent=this.config.chatbot_representative_name||this.config.chatbot_name);let t=this.shadowRoot.querySelector(`#chatbot-display-role`);t&&(t.textContent=this.config.chatbot_representative_role||`Çevrimiçi`);let n=this.shadowRoot.querySelector(`#chatbot-avatar-container`);n&&(n.innerHTML=this.config.chatbot_avatar?`<img src="${this.config.chatbot_avatar}" class="w-full h-full object-cover">`:`<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8V4H8"/><rect width="16" height="12" x="4" y="8" rx="2"/><path d="M2 14h2"/><path d="M20 14h2"/><path d="M15 13v2"/><path d="M9 13v2"/></svg>`)}injectHostStyles(){let e=document.querySelector(`meta[name="viewport"]`);e||(e=document.createElement(`meta`),e.name=`viewport`,document.head.appendChild(e)),(e.getAttribute(`content`)||``).includes(`user-scalable=no`)||e.setAttribute(`content`,`width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no`);let t=document.createElement(`style`);t.textContent=`
            html, body {
                touch-action: manipulation !important;
                -ms-touch-action: manipulation !important;
            }
        `,document.head.appendChild(t)}bindEvents(){let e=this.shadowRoot.getElementById(`voyn-widget-button`),t=this.shadowRoot.getElementById(`voyn-widget-close`),n=this.shadowRoot.getElementById(`voyn-widget-panel`),r=this.shadowRoot.getElementById(`voyn-widget-tooltip`);e&&(e.style.backgroundColor=this.config.chatbot_color);let i=this.shadowRoot.querySelector(`#voyn-widget-panel .header-bg`);i&&(i.style.backgroundColor=this.config.chatbot_color),e?.addEventListener(`click`,()=>{n?.classList.contains(`hidden-panel`)?(n?.classList.remove(`hidden-panel`),r?.classList.add(`hidden-panel`),this.scrollToBottom()):n?.classList.add(`hidden-panel`)}),t?.addEventListener(`click`,()=>{n?.classList.add(`hidden-panel`)}),this.shadowRoot.getElementById(`chat-form`)?.addEventListener(`submit`,e=>{e.preventDefault(),this.handleUserSubmit()});let a=this.shadowRoot.getElementById(`chat-input`);a?.addEventListener(`input`,()=>{a.style.height=`auto`,a.style.height=a.scrollHeight+`px`}),a?.addEventListener(`keydown`,e=>{e.key===`Enter`&&!e.shiftKey&&(e.preventDefault(),this.handleUserSubmit())}),setTimeout(()=>{if(n?.classList.contains(`hidden-panel`)&&r){let e=document.title.split(` - `)[0].split(` | `)[0].trim();r.innerHTML=`
                    <div class="font-bold mb-1">${this.config.chatbot_hint_title}</div>
                    ${this.config.chatbot_hint_message||(e?`<strong>${e}</strong> hakkında bilgi almak ister misin?`:`Sana nasıl yardımcı olabilirim?`)}
                    <div style="position:absolute; bottom:-6px; right:15px; width:12px; height:12px; background:black; transform:rotate(45deg);"></div>
                `,r.classList.remove(`hidden-panel`),this.audio.play().catch(()=>{})}},5e3),e?.addEventListener(`mousedown`,()=>{r?.classList.add(`hidden-panel`)})}getHTML(){return`
            <div id="voyn-widget-button" data-config-color class="shadow-lg text-white flex items-center justify-center cursor-pointer transition-transform hover:scale-110" style="width:56px; height:56px; border-radius:50%; position:fixed; bottom:24px; right:24px; z-index:999999; background-color: ${this.config.chatbot_color}">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            </div>
            <div id="voyn-widget-tooltip" class="hidden-panel shadow-md bg-black text-white p-3 text-xs mb-2 transition-all msg-appear" style="position:fixed; bottom:90px; right:24px; border-radius:12px; width:180px; z-index:999998; border: 1px solid rgba(255,255,255,0.1); line-height:1.4;">
                <div class="font-bold mb-1">Merhaba! 👋</div>
                Canlı destekten bilgi alabilir ve talep oluşturabilirsin.
                <div style="position:absolute; bottom:-6px; right:15px; width:12px; height:12px; background:black; transform:rotate(45deg);"></div>
            </div>
            <div id="voyn-widget-panel" class="hidden-panel flex flex-col shadow-2xl bg-white" style="position:fixed; bottom:90px; right:24px; width:360px; height:540px; border-radius:16px; overflow:hidden; z-index:999999; border: 1px solid #eee;">
                <div data-config-color class="header-bg p-4 flex justify-between items-center text-white" style="background-color: ${this.config.chatbot_color}">
                    <div class="flex items-center gap-3">
                        <div id="chatbot-avatar-container" class="avatar-wrap w-9 h-9 bg-white/20 rounded-full flex items-center justify-center relative overflow-hidden border border-white/30">
                            ${this.config.chatbot_avatar?`<img src="${this.config.chatbot_avatar}" class="w-full h-full object-cover">`:`<div class="avatar text-white font-medium" style="font-size: 13px;">${(this.config.chatbot_representative_name||this.config.chatbot_name).charAt(0)}</div>`}
                            <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-400 border-2 border-white rounded-full"></span>
                        </div>
                        <div class="header-info">
                            <h3 id="chatbot-display-name" class="font-medium text-sm m-0" style="font-size: 14px !important;">${this.config.chatbot_representative_name||this.config.chatbot_name}</h3>
                            <p id="chatbot-display-role" class="text-xs text-white/80 m-0" style="font-size: 12px !important;">${this.config.chatbot_representative_role||`Çevrimiçi`}</p>
                        </div>
                    </div>
                    <button id="voyn-widget-close" class="text-white/80 hover:text-white bg-transparent border-0 cursor-pointer p-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
                
                <div id="chat-messages" class="flex-1 overflow-y-auto p-4 flex flex-col bg-gray-50/50">
                    <!-- Messages go here -->
                </div>

                <div id="chat-input-area" class="p-3 border-t border-gray-100 bg-white">
                    <form id="chat-form" class="flex items-center gap-2">
                        <textarea id="chat-input" class="flex-1 border-0 bg-gray-100 rounded-2xl px-4 py-2.5 outline-none focus:ring-1 focus:ring-black/5 transition-shadow" placeholder="${this.config.chatbot_placeholder||`Bir mesaj yazın...`}" rows="1" style="font-size: 13px !important; resize: none; overflow-y: auto; max-height: 80px;"></textarea>
                        <button type="submit" id="chat-submit" data-config-color class="w-9 h-9 rounded-full flex items-center justify-center text-white border-0 cursor-pointer transition-opacity hover:opacity-80" style="background-color: ${this.config.chatbot_color}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left: -2px;"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        `}escapeHTML(e){return e?e.replace(/[&<>'"]/g,e=>({"&":`&amp;`,"<":`&lt;`,">":`&gt;`,"'":`&#39;`,'"':`&quot;`})[e]||e):``}renderMessages(){let e=this.shadowRoot.getElementById(`chat-messages`);e&&(e.innerHTML=``,this.messages.forEach(e=>this.addMessageToDOM(e)),this.isTyping&&!this.shadowRoot.getElementById(`typing-indicator`)&&this.showTypingIndicator())}addMessageToDOM(e){let t=this.shadowRoot.getElementById(`chat-messages`);if(!t)return;let n=this.config.chatbot_avatar?`<img src="${this.config.chatbot_avatar}" class="w-full h-full object-cover rounded-full">`:`<div class="w-full h-full bg-black text-white rounded-full flex items-center justify-center font-bold" style="font-size: 10px;">${(this.config.chatbot_representative_name||this.config.chatbot_name).charAt(0)}</div>`,r=e.role===`user`?`
            <div class="msg-row user msg-appear">
                <div class="bubble user">${this.escapeHTML(e.content)}</div>
            </div>
        `:`
            <div class="msg-row msg-appear">
                <div class="msg-avatar">${n}</div>
                <div class="bubble bot">${this.escapeHTML(e.content)}</div>
            </div>
        `;t.insertAdjacentHTML(`beforeend`,r),this.scrollToBottom()}showTypingIndicator(){let e=this.shadowRoot.getElementById(`chat-messages`);if(!e||this.shadowRoot.getElementById(`typing-indicator`))return;let t=`
            <div class="msg-row msg-appear" id="typing-indicator">
                <div class="msg-avatar">${this.config.chatbot_avatar?`<img src="${this.config.chatbot_avatar}" class="w-full h-full object-cover rounded-full">`:`<div class="w-full h-full bg-black text-white rounded-full flex items-center justify-center font-bold" style="font-size: 10px;">${(this.config.chatbot_representative_name||this.config.chatbot_name).charAt(0)}</div>`}</div>
                <div class="bubble bot">
                    <div class="typing-dots">
                        <div class="dot"></div>
                        <div class="dot"></div>
                        <div class="dot"></div>
                    </div>
                </div>
            </div>
        `;e.insertAdjacentHTML(`beforeend`,t),this.scrollToBottom()}hideTypingIndicator(){this.shadowRoot.getElementById(`typing-indicator`)?.remove()}playNotification(){if(!this.config.chatbot_notification_active)return;let e=this.config.chatbot_notification_sound||`https://assets.mixkit.co/active_storage/sfx/2358/2358-preview.mp3`;this.playSound(e)}playTypingSound(){!this.config.chatbot_notification_active||!this.config.chatbot_typing_sound||this.playSound(this.config.chatbot_typing_sound)}playErrorSound(){!this.config.chatbot_notification_active||!this.config.chatbot_error_sound||this.playSound(this.config.chatbot_error_sound)}playSound(e){try{let t=new Audio(e);t.volume=.4,t.play().catch(()=>{})}catch{}}scrollToBottom(){let e=this.shadowRoot.getElementById(`chat-messages`);e&&setTimeout(()=>{e.scrollTop=e.scrollHeight},50)}async handleUserSubmit(){if(this.isFinished||this.isTyping)return;let e=this.shadowRoot.getElementById(`chat-input`),t=e.value.trim();if(!t)return;e.value=``,e.style.height=`auto`;let n={role:`user`,content:t};this.messages.push(n),this.addMessageToDOM(n),this.isTyping=!0,this.showTypingIndicator(),this.playTypingSound();try{let t=await fetch(`https://n8n.ahmetcotur.com/webhook/chat`,{method:`POST`,headers:{"Content-Type":`application/json`,Accept:`application/json`},body:JSON.stringify({website_uuid:this.websiteUuid,site_key:this.siteKey,messages:this.messages.slice(1),page_url:window.location.href})});if(t.ok){let n=await t.json();if(n.role&&n.content?await this.addAssistantMessageWithStreaming(n.content):n.chatbot_error_message&&await this.addAssistantMessageWithStreaming(n.chatbot_error_message),n.lead_saved){this.isFinished=!0,e.disabled=!0,e.placeholder=this.config.chatbot_finish_message||`Görüşme tamamlandı.`;let t=this.shadowRoot.getElementById(`chat-submit`);t&&(t.disabled=!0,t.style.opacity=`0.5`)}}else this.hideTypingIndicator(),this.isTyping=!1,await this.addAssistantMessageWithStreaming(this.config.chatbot_error_message||`Üzgünüm, şu an bağlantı kuramıyorum.`),this.playErrorSound()}catch(e){console.error(`Chat error:`,e),this.hideTypingIndicator(),this.isTyping=!1,await this.addAssistantMessageWithStreaming(`Ağ hatası oluştu. Lütfen bağlantınızı kontrol edin.`),this.playErrorSound()}finally{this.isTyping=!1}}async addAssistantMessageWithStreaming(e){this.isTyping=!0,this.showTypingIndicator(),await new Promise(e=>setTimeout(e,600+Math.random()*400)),this.hideTypingIndicator(),this.isTyping=!1;let t=this.messages.length;this.messages.push({role:`assistant`,content:``});let n=this.config.chatbot_avatar?`<img src="${this.config.chatbot_avatar}" class="w-full h-full object-cover rounded-full">`:`<div class="w-full h-full bg-black text-white rounded-full flex items-center justify-center font-bold" style="font-size: 10px;">${(this.config.chatbot_representative_name||this.config.chatbot_name).charAt(0)}</div>`,r=`msg-${Date.now()}`,i=`
            <div class="msg-row msg-appear" id="${r}">
                <div class="msg-avatar">${n}</div>
                <div class="bubble bot"></div>
            </div>
        `;this.shadowRoot.getElementById(`chat-messages`)?.insertAdjacentHTML(`beforeend`,i);let a=this.shadowRoot.querySelector(`#${r} .bubble`),o=e.split(` `);for(let e=0;e<o.length;e++){let n=(e===0?``:` `)+o[e];this.messages[t].content+=n,a&&a.append(n),this.scrollToBottom(),this.playTypingSound();let r=45+Math.random()*50;await new Promise(e=>setTimeout(e,r))}this.playNotification()}};window.addEventListener(`load`,()=>{setTimeout(()=>{new t},2e3)});