
const Groq = require("groq-sdk");
const prompts = require('./propmts');

require('dotenv').config()


function getCreateData(system, message) {
    return {
            "messages": [
              {
                "role": "system",
                "content": system,

              },
              {
                "role": "user",
                "content": message,
              }
            ],
            "model": "llama3-70b-8192",
            "temperature": 1,
            "max_tokens": 1024,
            "top_p": 1,
            "stream": true,
            "stop": null
    };
}

function getSystemForBot(botName, otherBots = []) {
    let result = prompts.bots[botName] + prompts.base;
    result = result.replace('$names', otherBots.join(', '));

    return result;
}

const groq = new Groq(process.env.GROQ_API_KEY);

module.exports = {
    async getNextMessage(bot, messages, otherBots = []) {
        let result = '';
        let createData = getCreateData(getSystemForBot(bot, otherBots), messages);
        const chatCompletion = await groq.chat.completions.create(createData);
        
          for await (const chunk of chatCompletion) {
            result += chunk.choices[0]?.delta?.content || '';
          }

        return result;
    },
    formatMessages(messages) {
        return messages.map(m => m.name + ': ' + m.message).join('\n');
    }
};
