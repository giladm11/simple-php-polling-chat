<template>
  <div class="chat-container">
    <div v-if="!nameEntered" class="modal-overlay">
      <div class="modal">
        <h2>Enter Your Name</h2>
        <div>
          <input v-model="tempName" placeholder="Enter your name" class="input-name" />
        </div>
        <div>
          <input v-model="storeName" type="checkbox" id="storeName"/>
          <label for="storeName">Save name for next time</label>
        </div>
        <div>
          <br />
          <button @click="setName" class="confirm-button">Start Chatting</button>
        </div>
      </div>
    </div>
    <div v-else class="chat-holder">
      <div class="chat-header">
        <h1>Guess Who Is The AI</h1>
      </div>
      <div class="chat-body" ref="chatBody" @scroll="setBottom">
        <div class="messages">
          <transition-group name="list" tag="div">
            <div v-for="msg in messages" :key="msg.id" class="message" :class="{'message-enter': msg.new}">
              <div class="message-content">
                <span :style="{ color: getNameColor(msg.name) }" class="name">{{ decodeHtmlEntities(msg.name) }}</span>: {{ decodeHtmlEntities(msg.message) }}
              </div>
              <div class="timestamp">{{ new Date(msg.date).toLocaleTimeString() }}</div>
            </div>
          </transition-group>
        </div>
      </div>
      <div class="chat-footer">
        <input v-model="message" @keyup.enter="sendMessageOnEnter" placeholder="Enter your message" class="input-message" ref="input"></input>
        <button @click="sendMessage" class="send-button">Send</button>
      </div>
    </div>
  </div>
</template>

<script>
const host = '/api';

export default {
  data() {
    return {
      name: localStorage.getItem('chatName') || '',
      tempName: '',
      message: '',
      messages: [],
      nameEntered: false,
      storeName: true,
      isSendingMessage: false,
      isOnBottom: false,
    };
  },
  methods: {
    setBottom() {
      let container = this.$refs.chatBody;
      this.isOnBottom = container ? container.scrollHeight - container.offsetHeight - container.scrollTop < 1 : false;
    },
    focusInput() {
      this.$refs.input.focus();
    },
    async sendMessage() {
      if (this.message.trim() && !this.isSendingMessage) {
        this.isSendingMessage = true;
        await fetch(host + '/sendMessage.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({
            name: this.name,
            message: this.message
          })
        });
        this.message = '';
        await this.focusInput();
        this.isSendingMessage = false;
      }
    },
    sendMessageOnEnter(event) {
      if (event.key === 'Enter') {
        this.sendMessage();
      }
    },
    async fetchMessages(isFirstFetch = false, onlyOnce = false) {
      let url = host + '/getMessages.php';

      if (this.messages && this.messages.length) {
        const lastMessage = this.messages[this.messages.length - 1];

        url += `?lastId=${lastMessage.id}`;
      }

      let messagesById = {};

      for (const msg of this.messages) {
        messagesById[msg.id] = msg;
      }

      try {
          const response = await fetch(url);
        let newMessages = await response.json();


        newMessages = newMessages.filter(msg => !messagesById[msg.id]);

        let messagesToPut = this.messages.concat(newMessages);

        if (messagesToPut.length > 200) {
          messagesToPut = messagesToPut.slice(-200);
        }

        this.messages = messagesToPut;

        if (isFirstFetch || this.isOnBottom) {
          await this.$nextTick();
          this.scrollToBottom();
        }

        await this.focusInput();

      } catch (error) {
        console.error(error);
      }


      if (!onlyOnce) {
        setTimeout(() => {
          this.fetchMessages();
        }, 1000);
      }
    },
    decodeHtmlEntities(str) {
      const textarea = document.createElement('textarea');
      textarea.innerHTML = str;
      return textarea.value;
    },
    setName() {
      if (this.tempName.trim()) {
        this.name = this.tempName;
        if (this.storeName) {
          localStorage.setItem('chatName', this.name);
        } else {
          localStorage.removeItem('chatName');
        }
        this.nameEntered = true;
        this.fetchMessages(true); // Fetch messages when name is set
      }
    },
    getNameColor(name) {
      // Generate a color based on the name (simple hash function)
      let hash = 0;
      for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
      }
      let color = '#';
      for (let i = 0; i < 3; i++) {
        const value = (hash >> (i * 8)) & 0xFF;
        color += ('00' + value.toString(16)).slice(-2);
      }
      return color;
    },
    scrollToBottom() {
      const chatBody = this.$refs.chatBody;
      chatBody.scrollTop = chatBody.scrollHeight;
    },
    handleResize() {
      if (this.isOnBottom) {
        this.scrollToBottom();
      }
    }
  },
  mounted() {
    window.addEventListener('resize', this.handleResize);

    if (this.name) {
      this.nameEntered = true;
      this.fetchMessages(true);
    }
  },
  beforeDestroy() {
    window.removeEventListener('resize', this.handleResize);
  },
};
</script>

<style scoped>
html, body {
  margin: 0;
  height: 100%;
}

html, body, div, input, button {
  font-family: 'Montserrat', sans-serif;
}

.chat-container {
  display: flex;
  flex-direction: column;
  max-width: 100vw;
}

.chat-header {
  background-color: #2E7D32;
  color: white;
  padding: 15px;
  text-align: center;
  font-size: 1rem;
  border-radius: 10px;;
}

.chat-body {
  flex: 1;
  padding: 10px;
  overflow-y: auto;
  overflow-x: hidden;
  background-color: #333333;
  display: flex;
  flex-direction: column;
  margin: 10px 0;
  border-radius: 10px;
}

.chat-body::-webkit-scrollbar {
  width: 0.5rem;
}

.chat-body::-webkit-scrollbar-track {
  background-color: #333333;
  border-radius: 10px;
}

.chat-body::-webkit-scrollbar-thumb {
  background-color: white;
  border-radius: 10px;
}

.chat-body::-webkit-scrollbar-thumb:hover {
  background-color: #45a049;
}

.messages {
  display: flex;
  flex-direction: column;
}

.message {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 10px;
  padding: 10px;
  background-color: #fff;
  border-radius: 5px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  width: 100%; /* Ensure full width */
}

.message-content {
  flex: 1;
}

.timestamp {
  font-size: 0.8em;
  color: #888;
  margin-left: 10px; /* Space between message and timestamp */
}

.chat-footer {
  padding: 10px;
  background-color: #333;
  display: flex;
  flex-direction: column;
  border-radius: 10px;
}

.input-message {
  margin-bottom: 10px;
  padding: 10px;
  border-radius: 5px;
  font-size: 16px;
  width: 100%;
  box-sizing: border-box;
  resize: vertical; /* Allow resizing vertically */
  border: none;
}

.send-button {
  padding: 10px;
  background-color: #2E7D32;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 16px;
}

.send-button:hover {
  background-color: #45a049;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
}

.modal {
  background: white;
  padding: 20px;
  border-radius: 8px;
  text-align: center;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  width: 80%;
}

.input-name {
  margin-bottom: 10px;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 5px;
  font-size: 16px;
  width: 100%;
  box-sizing: border-box;
}

.confirm-button {
  padding: 10px;
  background-color: #2E7D32;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 16px;
}

.confirm-button:hover {
  background-color: #45a049;
}

.chat-holder {
  height: calc(100vh - 20px);
  height: calc(100dvh - 20px);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.name {
  font-weight: bold;
}

.list-enter-active,
.list-leave-active {
  transition: all 0.5s ease;
}
.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateX(30px);
}

</style>
