module.exports = {
    getMessages(lastId = null) {
        const url = 'https://aichat.zestfulswift.com/api/getMessages.php' + (lastId ? '?lastId=' + lastId : '');
        return fetch(url)
            .then(response => response.json())
            .catch(error => {
                console.error('Error fetching messages:', error);
                return [];
            });
    },
    sendMessage(name, message) {
        const url = 'https://aichat.zestfulswift.com/api/sendMessage.php';
        const data = new URLSearchParams({
            name: name.charAt(0).toUpperCase() + name.slice(1),
            message: message
        });
        return fetch(url, { method: 'POST', body: data, headers: { 'Content-Type': 'application/x-www-form-urlencoded' } })
            .then(response => response.json())
            .catch(error => {
                console.error('Error sending message:', error);
            });
    },
    // Wait for it to look real as human send it
    async waitForLogicalAmountOfTime(message) {
        const speed = 50; // chars per minute
        const word = message.split(' ')[0];
        const words = message.split(' ').length;
        const timePerWord = 60 / speed;
        const time = words * timePerWord;
        const chars = message.length;
        const timePerChar = time / chars;
        return new Promise(resolve => {
            setTimeout(resolve, time * 1000);
        });
    }
}