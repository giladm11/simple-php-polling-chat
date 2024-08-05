const { getMessages, sendMessage } = require("./chatApi");
const { bots } = require("./propmts");
const { getNextMessage, formatMessages } = require("./groqApi");

let messages = [];
let currentBots = [];

        
function getRandomBot() {
    return currentBots[Math.floor(Math.random() * currentBots.length)];
}

function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

module.exports = {
    async manageBots(numberOfBots = 7) {
        currentBots = Object.keys(bots).slice(0, numberOfBots);

        while (true) {
            try {
                messages = (await getMessages()).slice(-20);

                let bot = getRandomBot();
                let nextMessage = await getNextMessage(bot, formatMessages(messages), currentBots);
                await sleep(6000);        
                await sendMessage(bot, nextMessage);    
            } catch (error) {
                console.error(error);
            }
        }
    },
}