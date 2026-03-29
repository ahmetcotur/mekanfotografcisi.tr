(function(){let e=document.createElement(`link`).relList;if(e&&e.supports&&e.supports(`modulepreload`))return;for(let e of document.querySelectorAll(`link[rel="modulepreload"]`))n(e);new MutationObserver(e=>{for(let t of e)if(t.type===`childList`)for(let e of t.addedNodes)e.tagName===`LINK`&&e.rel===`modulepreload`&&n(e)}).observe(document,{childList:!0,subtree:!0});function t(e){let t={};return e.integrity&&(t.integrity=e.integrity),e.referrerPolicy&&(t.referrerPolicy=e.referrerPolicy),e.crossOrigin===`use-credentials`?t.credentials=`include`:e.crossOrigin===`anonymous`?t.credentials=`omit`:t.credentials=`same-origin`,t}function n(e){if(e.ep)return;e.ep=!0;let n=t(e);fetch(e.href,n)}})();var e=document.createElement(`link`);e.rel=`stylesheet`,e.href=`https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500&display=swap`,document.head.appendChild(e);var t=class{baseUrl=`https://n8n.ahmetcotur.com`;websiteUuid=null;siteKey=null;shadowRoot;config={chatbot_name:`Asistan`,chatbot_color:`#000000`,chatbot_welcome_message:`Anlık olarak size nasıl yardımcı olabilirim?`,chatbot_icon:`https://lead.ahmetcotur.com/uploads/bot_icons/default.png`,chatbot_powered_by_text:`Powered by Voyn`,chatbot_powered_by_link:`https://voyn.tr`,chatbot_position:`right`,chatbot_font_family:`DM Sans, sans-serif`};state={isOpen:!1,messages:[],sessionId:null,isTyping:!1};constructor(){this.state.sessionId=localStorage.getItem(`voyn_session_id`)||this.generateUUID(),localStorage.setItem(`voyn_session_id`,this.state.sessionId),this.websiteUuid=document.currentScript.getAttribute(`data-website-uuid`),this.siteKey=document.currentScript.getAttribute(`data-site-key`),this.init()}async init(){try{let e=await fetch(`${this.baseUrl}/webhook/c5c84df9-6be5-4672-97fa-4f2425667746?websiteUuid=${this.websiteUuid}`);if(e.ok){let t=await e.json();this.config={...this.config,...t}}this.createWidget()}catch(e){console.error(`Voyn Widget initialization failed:`,e),this.createWidget()}}createWidget(){let e=document.createElement(`div`);e.id=`voyn-widget-container`,document.body.appendChild(e),this.shadowRoot=e.attachShadow({mode:`open`}),this.render()}toggleChat(){this.state.isOpen=!this.state.isOpen,this.render()}generateUUID(){return`xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx`.replace(/[xy]/g,function(e){var t=Math.random()*16|0;return(e==`x`?t:t&3|8).toString(16)})}async sendMessage(e){if(!e.trim())return;let t={role:`user`,content:e,timestamp:new Date};this.state.messages.push(t),this.state.isTyping=!0,this.render();try{let t=await fetch(`${this.baseUrl}/webhook/c5c84df9-6be5-4672-97fa-4f2425667746`,{method:`POST`,headers:{"Content-Type":`application/json`},body:JSON.stringify({message:e,sessionId:this.state.sessionId,websiteUuid:this.websiteUuid,metadata:{url:window.location.href,title:document.title}})});if(t.ok){let e=await t.json();this.state.messages.push({role:`assistant`,content:e.output||e.message||`Üzgünüm, şu an yanıt veremiyorum.`,timestamp:new Date})}else throw new Error(`HTTP error! status: ${t.status}`)}catch(e){console.error(`Error sending message:`,e),this.state.messages.push({role:`assistant`,content:`Üzgünüm, bir hata oluştu. Lütfen tekrar deneyin.`,timestamp:new Date})}finally{this.state.isTyping=!1,this.render()}}render(){this.shadowRoot.innerHTML=`
            <style>
                :host {
                    --primary-color: ${this.config.chatbot_color};
                    --font-family: ${this.config.chatbot_font_family};
                }
                .widget-launcher {
                    position: fixed;
                    bottom: 20px;
                    ${this.config.chatbot_position}: 20px;
                    width: 60px;
                    height: 60px;
                    border-radius: 50%;
                    background: var(--primary-color);
                    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    transition: transform 0.3s ease;
                    z-index: 999999;
                }
                .widget-launcher:hover { transform: scale(1.05); }
                .widget-launcher img { width: 35px; height: 35px; }
                
                .chat-window {
                    position: fixed;
                    bottom: 90px;
                    ${this.config.chatbot_position}: 20px;
                    width: 380px;
                    height: 600px;
                    max-height: calc(100vh - 120px);
                    background: white;
                    border-radius: 16px;
                    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
                    display: ${this.state.isOpen?`flex`:`none`};
                    flex-direction: column;
                    overflow: hidden;
                    z-index: 999999;
                    font-family: var(--font-family);
                }
                @media (max-width: 480px) {
                    .chat-window {
                        width: calc(100% - 40px);
                        height: calc(100% - 110px);
                    }
                }
                .chat-header {
                    padding: 20px;
                    background: var(--primary-color);
                    color: white;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                }
                .bot-info { display: flex; align-items: center; gap: 12px; }
                .bot-info img { width: 40px; height: 40px; border-radius: 50%; background: white; }
                .bot-name { font-weight: 500; font-size: 16px; }
                .close-btn { cursor: pointer; opacity: 0.8; transition: 0.2s; }
                .close-btn:hover { opacity: 1; }
                
                .messages-container {
                    flex: 1;
                    overflow-y: auto;
                    padding: 20px;
                    display: flex;
                    flex-direction: column;
                    gap: 12px;
                    background: #f8f9fa;
                }
                .message {
                    max-width: 85%;
                    padding: 12px 16px;
                    border-radius: 12px;
                    font-size: 14px;
                    line-height: 1.5;
                }
                .message.user {
                    align-self: flex-end;
                    background: var(--primary-color);
                    color: white;
                    border-bottom-right-radius: 4px;
                }
                .message.assistant {
                    align-self: flex-start;
                    background: white;
                    color: #333;
                    border-bottom-left-radius: 4px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
                }
                .typing-indicator {
                    font-style: italic;
                    font-size: 12px;
                    color: #666;
                    margin-top: 4px;
                }
                
                .input-container {
                    padding: 20px;
                    background: white;
                    border-top: 1px solid #eee;
                    display: flex;
                    gap: 10px;
                }
                .input-container input {
                    flex: 1;
                    border: 1px solid #ddd;
                    padding: 10px 15px;
                    border-radius: 20px;
                    outline: none;
                    font-family: inherit;
                }
                .input-container button {
                    background: var(--primary-color);
                    color: white;
                    border: none;
                    padding: 8px 15px;
                    border-radius: 50%;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                .powered-by {
                    padding: 10px;
                    text-align: center;
                    font-size: 11px;
                    color: #999;
                    background: #f8f9fa;
                }
                .powered-by a { color: inherit; text-decoration: none; font-weight: 500; }
            </style>
            
            <div class="widget-launcher" id="voyn-launcher">
                <img src="${this.config.chatbot_icon}" alt="Chat">
            </div>
            
            <div class="chat-window">
                <div class="chat-header">
                    <div class="bot-info">
                        <img src="${this.config.chatbot_icon}" alt="Bot">
                        <span class="bot-name">${this.config.chatbot_name}</span>
                    </div>
                    <div class="close-btn" id="voyn-close">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 6L6 18M6 6l12 12"></path>
                        </svg>
                    </div>
                </div>
                
                <div class="messages-container" id="voyn-messages">
                    <div class="message assistant">
                        ${this.config.chatbot_welcome_message}
                    </div>
                    ${this.state.messages.map(e=>`
                        <div class="message ${e.role}">
                            ${e.content}
                        </div>
                    `).join(``)}
                    ${this.state.isTyping?`<div class="typing-indicator">Asistan yazıyor...</div>`:``}
                </div>
                
                <form class="input-container" id="voyn-form">
                    <input type="text" placeholder="Mesajınızı yazın..." id="voyn-input" autocomplete="off">
                    <button type="submit">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"></path>
                        </svg>
                    </button>
                </form>
                
                <div class="powered-by">
                    ${this.config.chatbot_powered_by_text} <a href="${this.config.chatbot_powered_by_link}" target="_blank">Voyn</a>
                </div>
            </div>
        `;let e=this.shadowRoot.getElementById(`voyn-launcher`),t=this.shadowRoot.getElementById(`voyn-close`),n=this.shadowRoot.getElementById(`voyn-form`),s=this.shadowRoot.getElementById(`voyn-input`),o=this.shadowRoot.getElementById(`voyn-messages`);e.onclick=()=>this.toggleChat(),t.onclick=()=>this.toggleChat(),n.onsubmit=e=>{e.preventDefault();let t=s.value;t.trim()&&(this.sendMessage(t),s.value=``)},o.scrollTop=o.scrollHeight}};new t;
