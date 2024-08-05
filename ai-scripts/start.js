const { manageBots } = require("./src/botsManager");

async function main() {
    await manageBots();
}

main().then(a => {
    console.log('--- DONE ---');
});
